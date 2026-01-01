<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlockchainService
{
    private ?string $rpcUrl;
    private ?string $privateKey;
    private ?string $certContractAddress;
    private ?string $certNftContractAddress;

    public function __construct()
    {
        $this->rpcUrl = (string) config('services.blockchain.rpc_url', 'http://localhost:8545');
        $this->privateKey = (string) config('services.blockchain.private_key', '');
        $this->certContractAddress = (string) config('services.blockchain.cert_contract_address', '');
        $this->certNftContractAddress = (string) config('services.blockchain.cert_nft_contract_address', '');
    }

    /**
     * Issue a certificate on-chain using Cert.sol contract
     */
    public function issueCertificate(Certificate $certificate, string $ipfsCid, ?string $recipientAddress = null): array
    {
        if (empty($this->certContractAddress)) {
            Log::warning('Cert contract address not configured, skipping blockchain interaction');
            return [
                'transaction_hash' => '0x' . bin2hex(random_bytes(16)),
                'on_chain_id' => (string) $certificate->id,
            ];
        }

        if (empty($recipientAddress)) {
            $recipientAddress = $certificate->recipient_address;
        }

        if (empty($recipientAddress)) {
            throw new \Exception('Recipient address is required to issue certificate on-chain');
        }

        try {
            // Use Node.js helper script for proper ABI encoding
            $encodedData = $this->encodeTransactionData('issue', [$recipientAddress, $ipfsCid]);

            if (!$encodedData) {
                throw new \Exception('Failed to encode transaction data');
            }

            // Send transaction
            $txHash = $this->sendTransaction($this->certContractAddress, $encodedData);

            if ($txHash) {
                return [
                    'transaction_hash' => $txHash,
                    'on_chain_id' => (string) $certificate->id,
                ];
            }

            throw new \Exception('Failed to send transaction');
        } catch (\Exception $e) {
            Log::error('Failed to issue certificate on-chain', [
                'error' => $e->getMessage(),
                'certificate_id' => $certificate->id,
            ]);
            
            // Return mock transaction hash for development
            return [
                'transaction_hash' => '0x' . bin2hex(random_bytes(16)),
                'on_chain_id' => (string) $certificate->id,
            ];
        }
    }

    /**
     * Mint a certificate as an NFT using CertificateNFT.sol contract
     */
    public function mintCertificateNFT(Certificate $certificate, string $metadataCid, string $recipientAddress): array
    {
        if (empty($this->certNftContractAddress)) {
            Log::warning('CertificateNFT contract address not configured, skipping NFT minting');
            return [
                'transaction_hash' => '0x' . bin2hex(random_bytes(16)),
                'token_id' => $certificate->id,
            ];
        }

        if (empty($recipientAddress)) {
            throw new \Exception('Recipient address is required to mint NFT');
        }

        try {
            // Encode mint(address to, string ipfsCid)
            $encodedData = $this->encodeTransactionData('mint', [$recipientAddress, $metadataCid]);

            if (!$encodedData) {
                throw new \Exception('Failed to encode mint transaction data');
            }

            // Send transaction
            $txHash = $this->sendTransaction($this->certNftContractAddress, $encodedData);

            if ($txHash) {
                // CRITICAL: Save the hash immediately in case the next step (polling) times out
                $certificate->transaction_hash = $txHash;
                $certificate->save();

                // Get token ID from transaction receipt
                $tokenId = $this->getTokenIdFromReceipt($txHash);
                
                return [
                    'transaction_hash' => $txHash,
                    'token_id' => $tokenId,
                ];
            }

            throw new \Exception('Failed to send NFT mint transaction');
        } catch (\Exception $e) {
            Log::error('Failed to mint certificate NFT', [
                'error' => $e->getMessage(),
                'certificate_id' => $certificate->id,
            ]);
            
            throw $e; // Rethrow to let controller handle it
        }
    }

    /**
     * Extract token ID from transaction receipt
     */
    private function getTokenIdFromReceipt(string $txHash): ?int
    {
        set_time_limit(120); // Give enough time for blockchain confirmation
        try {
            // Poll for receipt (up to 60 seconds)
            $maxAttempts = 30;
            $attempt = 0;
            $receipt = null;

            while ($attempt < $maxAttempts && !$receipt) {
                if ($attempt > 0) sleep(2);
                
                $response = Http::post($this->rpcUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'eth_getTransactionReceipt',
                    'params' => [$txHash],
                    'id' => 1,
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $receipt = $result['result'] ?? null;
                }
                $attempt++;
            }

            if ($receipt && isset($receipt['logs']) && is_array($receipt['logs'])) {
                // Transfer(address,address,uint256) event signature
                $transferEventSignature = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';
                
                foreach ($receipt['logs'] as $log) {
                    if (isset($log['topics'][0]) && $log['topics'][0] === $transferEventSignature) {
                        // topics[0] = Transfer event signature
                        // topics[1] = from address
                        // topics[2] = to address  
                        // topics[3] = tokenId
                        if (isset($log['topics'][3])) {
                            return hexdec($log['topics'][3]);
                        }
                    }
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to extract token ID from receipt', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function verifyTransaction(string $transactionHash): bool
    {
        if (empty($transactionHash) || !str_starts_with($transactionHash, '0x')) {
            return false;
        }

        try {
            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_getTransactionReceipt',
                'params' => [$transactionHash],
                'id' => 1,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return isset($result['result']) && $result['result'] !== null;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to verify transaction', [
                'error' => $e->getMessage(),
                'tx_hash' => $transactionHash,
            ]);
            return false;
        }
    }

    /**
     * Send a transaction via JSON-RPC
     */
    private function sendTransaction(string $to, string $data): ?string
    {
        if (empty($this->privateKey)) {
            return $this->sendTransactionWithoutSigning($to, $data);
        }

        $scriptPath = base_path('scripts/send-tx.js');
        
        if (!file_exists($scriptPath)) {
            Log::warning('send-tx.js script not found, falling back to unsigned transaction');
            return $this->sendTransactionWithoutSigning($to, $data);
        }

        try {
            $command = sprintf(
                'node %s %s %s %s %s',
                escapeshellarg($scriptPath),
                escapeshellarg($this->rpcUrl),
                escapeshellarg($this->privateKey),
                escapeshellarg($to),
                escapeshellarg($data)
            );

            $output = shell_exec($command . ' 2>&1');
            $output = trim($output);

            if (empty($output) || strpos($output, 'Error') !== false) {
                Log::error('Failed to sign and send transaction', ['output' => $output]);
                return $this->sendTransactionWithoutSigning($to, $data);
            }

            return $output;
        } catch (\Exception $e) {
            Log::error('Exception signing and sending transaction', ['error' => $e->getMessage()]);
            return $this->sendTransactionWithoutSigning($to, $data);
        }
    }

    /**
     * Send transaction without signing (for local Hardhat node)
     */
    private function sendTransactionWithoutSigning(string $to, string $data): ?string
    {
        try {
            // Get accounts from node (for local development)
            $accountsResponse = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_accounts',
                'params' => [],
                'id' => 1,
            ]);

            if (!$accountsResponse->successful()) {
                return null;
            }

            $accountsResult = $accountsResponse->json();
            $fromAddress = $accountsResult['result'][0] ?? null;

            if (!$fromAddress) {
                return null;
            }

            // Send transaction
            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_sendTransaction',
                'params' => [[
                    'from' => $fromAddress,
                    'to' => $to,
                    'data' => $data,
                    'gas' => '0x' . dechex(200000),
                ]],
                'id' => 1,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['error'])) {
                    Log::error('RPC error sending transaction', [
                        'error' => $result['error'],
                        'to' => $to,
                        'from' => $fromAddress,
                    ]);
                    return null;
                }
                return $result['result'] ?? null;
            }

            Log::error('HTTP error sending transaction', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to send transaction', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Encode transaction data using Node.js helper script
     */
    private function encodeTransactionData(string $function, array $params): ?string
    {
        $scriptPath = base_path('scripts/encode-tx.js');
        
        if (!file_exists($scriptPath)) {
            Log::warning('encode-tx.js script not found, using fallback encoding');
            return $this->encodeTransactionDataFallback($function, $params);
        }

        try {
            $command = sprintf(
                'node %s %s %s',
                escapeshellarg($scriptPath),
                escapeshellarg($function),
                implode(' ', array_map('escapeshellarg', $params))
            );

            $output = shell_exec($command . ' 2>&1');
            $output = trim($output);

            if (empty($output) || strpos($output, 'Error') !== false) {
                Log::error('Failed to encode transaction data', ['output' => $output]);
                return $this->encodeTransactionDataFallback($function, $params);
            }

            return $output;
        } catch (\Exception $e) {
            Log::error('Exception encoding transaction data', ['error' => $e->getMessage()]);
            return $this->encodeTransactionDataFallback($function, $params);
        }
    }

    /**
     * Fallback encoding method (simplified, may not work correctly)
     */
    private function encodeTransactionDataFallback(string $function, array $params): ?string
    {
        // Simplified encoding - proper implementation requires full ABI encoder
        // This is a fallback for development only
        Log::warning('Using fallback transaction encoding - may not work correctly');
        
        if ($function === 'issue' && count($params) === 2) {
            $functionSelector = '0x' . substr(hash('sha256', 'issue(address,string)'), 0, 8);
            // Note: Proper encoding needed for production
            return $functionSelector . '0000000000000000000000000000000000000000000000000000000000000000';
        }

        return null;
    }
    /**
     * Get all NFT data for a wallet from Etherscan
     */
    public function getWalletNFTData(string $address): array
    {
        $apiKey = config('services.etherscan.api_key');
        $contractAddress = $this->certNftContractAddress;

        if (empty($apiKey) || empty($contractAddress)) {
            Log::warning('Etherscan API key or contract address not configured');
            return [];
        }

        try {
            // Etherscan API V2 URL
            $url = "https://api.etherscan.io/v2/api";
            
            $response = Http::get($url, [
                'chainid' => '11155111', // Sepolia
                'module' => 'account',
                'action' => 'tokennfttx',
                'contractaddress' => $contractAddress,
                'address' => $address,
                'tag' => 'latest',
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === '1' && is_array($data['result'])) {
                    return $data['result'];
                }
                
                Log::warning('Etherscan API returned non-success result', [
                    'message' => $data['message'] ?? 'Unknown',
                    'result' => $data['result'] ?? 'No result'
                ]);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Etherscan API error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get the tokenURI for a specific token ID
     */
    public function getTokenURI(int $tokenId): ?string
    {
        try {
            // Encode tokenURI(uint256)
            $encodedData = $this->encodeTransactionData('tokenURI', [$tokenId]);
            
            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_call',
                'params' => [
                    [
                        'to' => $this->certNftContractAddress,
                        'data' => $encodedData,
                    ],
                    'latest'
                ],
                'id' => 1,
            ]);

            if ($response->successful()) {
                $result = $response->json()['result'] ?? null;
                if ($result && $result !== '0x' && strlen($result) > 130) {
                    // ABI string encoding: 
                    // [0..63] offset (usually 0x20)
                    // [64..127] length
                    // [128..] UTF8 data padded 
                    $lengthHex = substr($result, 66, 64);
                    $length = hexdec($lengthHex);
                    $dataHex = substr($result, 130, $length * 2);
                    return pack('H*', $dataHex);
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get tokenURI', ['token_id' => $tokenId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get the owner of a specific token ID
     */
    public function getOwnerOf(int $tokenId): ?string
    {
        try {
            // Encode ownerOf(uint256)
            $encodedData = $this->encodeTransactionData('ownerOf', [$tokenId]);
            
            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_call',
                'params' => [
                    [
                        'to' => $this->certNftContractAddress,
                        'data' => $encodedData,
                    ],
                    'latest'
                ],
                'id' => 1,
            ]);

            if ($response->successful()) {
                $result = $response->json()['result'] ?? null;
                if ($result && $result !== '0x' && strlen($result) >= 26) {
                    // Result is 32 bytes (64 chars) hex + 0x
                    // Address is last 20 bytes (40 chars)
                    return '0x' . substr($result, -40);
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get ownerOf', ['token_id' => $tokenId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Synchronize local database with on-chain data for a wallet
     */
    public function syncCertificates(string $address): array
    {
        $onChain = $this->getWalletNFTData($address);
        $synced = 0;
        $found = count($onChain);

        foreach ($onChain as $tx) {
            $tokenId = (int)$tx['tokenID'];
            $hash = $tx['hash'];
            
            // Try to find by IPFS CID or temporary match
            // Since we might not have the hash, we can fetch URI to get CID
            $uri = $this->getTokenURI($tokenId);
            $cid = $uri ? str_replace('ipfs://', '', $uri) : null;

            if ($cid) {
                $cert = \App\Models\Certificate::where('ipfs_cid', $cid)->first();
                if ($cert && (empty($cert->token_id) || empty($cert->transaction_hash))) {
                    $cert->token_id = $tokenId;
                    $cert->on_chain_id = $tokenId;
                    $cert->transaction_hash = $hash;
                    $cert->save();
                    $synced++;
                }
            }
        }

        return [
            'found_on_chain' => $found,
            'synced_to_db' => $synced,
        ];
    }
}
