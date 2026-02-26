<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\BlockchainService;
use App\Services\IpfsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificatesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Certificate::latest()->paginate(20));
    }

    public function show(int $id): JsonResponse
    {
        $certificate = Certificate::findOrFail($id);
        return response()->json($certificate);
    }

    public function store(
        Request $request,
        BlockchainService $blockchainService,
        IpfsService $ipfsService
    ): JsonResponse {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_email' => ['required', 'email', 'max:255'],
            'recipient_address' => ['nullable', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:10240'], // Max 10MB
            'template_type' => ['nullable', 'string', 'in:default,iedc,nss'],
            'skip_blockchain' => ['nullable', 'boolean'],
            'enrollment_number' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            // Create certificate record
            $certificateData = [
                'recipient_name' => $data['recipient_name'],
                'recipient_email' => $data['recipient_email'],
                'recipient_address' => $data['recipient_address'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'issued_at' => now(),
            ];

            if (!empty($data['enrollment_number'])) {
                $certificateData['metadata'] = [
                    'enrollment_number' => $data['enrollment_number']
                ];
            }

            $certificate = Certificate::create($certificateData);

            // Handle file upload or generation
            $fileCid = null;
            $mimeType = 'application/pdf'; // Default

            if ($request->hasFile('certificate_file')) {
                // Manual Upload
                $file = $request->file('certificate_file');
                $mimeType = $file->getClientMimeType();
                $fileCid = $ipfsService->uploadFile(
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            } else {
                // Generate PDF from Template
                $template = $data['template_type'] ?? 'default';
                $viewName = 'certificates.' . $template;
                
                // Fallback if view doesn't exist
                if (!view()->exists($viewName)) {
                    $viewName = 'certificates.default';
                }

                $pdf = \PDF::loadView($viewName, ['certificate' => $certificate]);
                $pdfContent = $pdf->output();
                $fileName = 'certificate_' . $certificate->id . '.pdf';

                $fileCid = $ipfsService->uploadFile($pdfContent, $fileName);
            }

            // Prepare metadata
            $metadataData = [
                'title' => $certificate->title,
                'description' => $certificate->description,
                'recipient_name' => $certificate->recipient_name,
                'recipient_email' => $certificate->recipient_email,
                'issued_at' => $certificate->issued_at->toIso8601String(),
            ];

            if ($fileCid) {
                $metadataData['image'] = 'ipfs://' . $fileCid;
                $metadataData['external_url'] = 'ipfs://' . $fileCid;
                $metadataData['properties'] = [
                    'files' => [
                        [
                            'uri' => 'ipfs://' . $fileCid,
                            'type' => $mimeType
                        ]
                    ]
                ];
            }

            // Create metadata and upload to IPFS
            $metadata = $ipfsService->createCertificateMetadata($metadataData);

            $ipfsCid = $ipfsService->uploadJson($metadata);

            if (!$ipfsCid) {
                throw new \Exception('Failed to upload certificate to IPFS');
            }

            // Update certificate with IPFS CID
            $certificate->ipfs_cid = $ipfsCid;
            $certificate->save();

            // Handle blockchain minting
            if (!($data['skip_blockchain'] ?? false)) {
                $recipientAddress = $certificate->recipient_address;
                if ($recipientAddress) {
                    $tx = $blockchainService->mintCertificateNFT($certificate, $ipfsCid, $recipientAddress);
                    $certificate->transaction_hash = $tx['transaction_hash'] ?? null;
                    $certificate->token_id = $tx['token_id'] ?? null;
                    $certificate->on_chain_id = $tx['token_id'] ?? null;
                    $certificate->save();
                }
            }

            // Add IPFS gateway URL to response
            $certificate->ipfs_url = $ipfsService->getGatewayUrl($ipfsCid);

            return response()->json($certificate, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create certificate', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create certificate: ' . $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request, int $id, BlockchainService $blockchainService): JsonResponse
    {
        $data = $request->validate([
            'transaction_hash' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{64}$/'],
        ]);

        $certificate = Certificate::findOrFail($id);
        $certificate->transaction_hash = $data['transaction_hash'];
        $certificate->save();

        // Automatically attempt to resolve the token_id
        try {
            $tokenId = $blockchainService->getTokenIdFromReceipt($data['transaction_hash']);
            if ($tokenId) {
                $certificate->token_id = $tokenId;
                $certificate->on_chain_id = $tokenId;
                $certificate->save();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to resolve token_id in confirm', [
                'cert_id' => $id,
                'hash' => $data['transaction_hash'],
                'error' => $e->getMessage()
            ]);
        }
        
        return response()->json([
            'message' => 'Transaction hash updated and sync attempted',
            'certificate' => $certificate,
            'resolved_token_id' => $certificate->token_id
        ]);
    }

    public function downloadPdf(int $id)
    {
        $certificate = Certificate::findOrFail($id);
        
        $pdf = \PDF::loadView('certificate', ['certificate' => $certificate]);
        
        $filename = 'certificate_' . $certificate->id . '_' . str_replace(' ', '_', $certificate->recipient_name) . '.pdf';
        
        return $pdf->download($filename);
    }

    public function verify(Request $request, BlockchainService $blockchainService): JsonResponse
    {
        $data = $request->validate([
            'transaction_hash' => ['required', 'string']
        ]);

        $verified = $blockchainService->verifyTransaction($data['transaction_hash']);
        
        $certificate = null;
        if ($verified) {
            $certificate = Certificate::where('transaction_hash', $data['transaction_hash'])->first();
        }

        return response()->json([
            'transaction_hash' => $data['transaction_hash'],
            'verified' => $verified,
            'certificate' => $certificate,
        ]);
    }

    public function getWalletNFTs(string $address, BlockchainService $blockchainService): JsonResponse
    {
        // 1. Fetch from Local DB (Source of Truth for Metadata)
        $localCerts = Certificate::whereRaw('LOWER(recipient_address) = ?', [strtolower($address)])->get();
        
        // 2. Try fetching from Etherscan (Source of Truth for Ownership Status)
        $transactions = $blockchainService->getWalletNFTData($address);
        
        $ownedTokenIds = [];
        
        if (!empty($transactions)) {
            // Calculate ownership from Etherscan history
            foreach ($transactions as $tx) {
                $tid = (string)$tx['tokenID'];
                if (strtolower($tx['to']) === strtolower($address)) {
                    $ownedTokenIds[$tid] = $tx;
                } else if (strtolower($tx['from']) === strtolower($address)) {
                    unset($ownedTokenIds[$tid]);
                }
            }
        } else {
            // Fallback: If Etherscan is empty/down, assume local DB ownership is correct 
            // (since we just restored it from chain, it should be accurate)
            foreach ($localCerts as $cert) {
                if ($cert->token_id) {
                    $ownedTokenIds[(string)$cert->token_id] = [
                        'tokenID' => (string)$cert->token_id,
                        'hash' => $cert->transaction_hash,
                        'timeStamp' => $cert->created_at->timestamp,
                        'from' => '0x0000000000000000000000000000000000000000', // Mock
                    ];
                }
            }
        }

        // 3. Match Owned Tokens to Certificate Data
        $certificates = [];
        foreach ($ownedTokenIds as $tid => $txData) {
            // Find in local DB collection
            $cert = $localCerts->firstWhere('token_id', $tid);
            
            // If not in local collection, try checking globally (e.g. if address case mismatch in DB)
            if (!$cert) {
                 $cert = Certificate::where('token_id', $tid)->first();
            }

            if ($cert) {
                $certificates[] = [
                    'token_id' => $tid,
                    'on_chain_data' => $txData,
                    'metadata' => $cert->toArray(),
                ];
            }
        }

        return response()->json([
            'wallet' => $address,
            'count' => count($certificates),
            'certificates' => $certificates,
            'raw_transactions_count' => count($transactions)
        ]);
    }
    public function sync(Request $request)
    {
        $address = $request->input('address');
        if (!$address) {
            return response()->json(['error' => 'Address is required'], 400);
        }

        $blockchainService = app(BlockchainService::class);
        $result = $blockchainService->syncCertificates($address);

        return response()->json($result);
    }

    /**
     * Mint certificate via backend (bypasses Metamask ghost transaction issues)
     */
    public function mintViaBackend(int $id): JsonResponse
    {
        $certificate = Certificate::findOrFail($id);

        if ($certificate->token_id) {
            return response()->json([
                'success' => true,
                'message' => 'Already minted',
                'token_id' => $certificate->token_id
            ]);
        }

        if (!$certificate->ipfs_cid) {
            return response()->json(['error' => 'Missing IPFS data'], 400);
        }

        try {
            $contractAddress = env('CERTNFT_ADDRESS', '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512');
            $recipient = $certificate->recipient_address ?: '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266';
            $cid = $certificate->ipfs_cid;

            // Create mint script in cert-contracts directory where ethers is installed
            $contractsDir = base_path('../cert-contracts');
            $scriptPath = "{$contractsDir}/scripts/temp-mint.js";
            
            $mintScript = <<<JS
const { ethers } = require('ethers');
const provider = new ethers.JsonRpcProvider('http://127.0.0.1:8545');
const wallet = new ethers.Wallet('0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80', provider);
const abi = ['function mint(address to, string calldata ipfsCid) external returns (uint256)', 'event Transfer(address indexed from, address indexed to, uint256 indexed tokenId)'];
const nft = new ethers.Contract('{$contractAddress}', abi, wallet);
(async () => {
    try {
        const tx = await nft.mint('{$recipient}', '{$cid}');
        const receipt = await tx.wait();
        for (const log of receipt.logs) {
            try {
                const parsed = nft.interface.parseLog(log);
                if (parsed && parsed.name === 'Transfer') {
                    console.log(JSON.stringify({ tokenId: Number(parsed.args.tokenId), hash: tx.hash }));
                    process.exit(0);
                }
            } catch {}
        }
        console.log(JSON.stringify({ error: 'No Transfer event found' }));
    } catch (e) {
        console.log(JSON.stringify({ error: e.message }));
    }
})();
JS;

            file_put_contents($scriptPath, $mintScript);

            $output = shell_exec("cd {$contractsDir} && node scripts/temp-mint.js 2>&1");
            unlink($scriptPath);

            Log::info("Mint output: " . $output);

            $result = json_decode(trim($output), true);

            if (!$result || isset($result['error'])) {
                throw new \Exception($result['error'] ?? 'Minting failed: ' . $output);
            }

            // Update certificate
            $certificate->token_id = $result['tokenId'];
            $certificate->on_chain_id = $result['tokenId'];
            $certificate->transaction_hash = $result['hash'];
            $certificate->save();

            return response()->json([
                'success' => true,
                'token_id' => $result['tokenId'],
                'transaction_hash' => $result['hash']
            ]);

        } catch (\Exception $e) {
            Log::error("Mint error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}





