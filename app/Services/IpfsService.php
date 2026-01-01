<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpfsService
{
    private ?string $apiKey;
    private ?string $apiSecret;
    private ?string $gatewayUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.ipfs.pinata_api_key', '');
        $this->apiSecret = (string) config('services.ipfs.pinata_api_secret', '');
        $this->gatewayUrl = (string) config('services.ipfs.gateway_url', 'https://gateway.pinata.cloud/ipfs/');
    }

    /**
     * Upload JSON data to IPFS via Pinata
     */
    /**
     * Upload to local IPFS node (fallback) acts as local storage
     */
    private function uploadToLocalNode(array $data, bool $isFile = false): ?string
    {
        // Ensure directory exists
        $path = public_path('mock_ipfs');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if ($isFile) {
             // $data is basically the args, but if isFile is true, we expect the caller 
             // to have passed the content in the first arg of uploadFile.
             // Wait, uploadFile passes ($fileContent, $fileName).
             // But uploadToLocalNode signature is (array $data, bool $isFile).
             // I need to adjust how uploadToLocalNode is called or what it accepts.
             // Let's look at uploadFile calls.
             return null; // Should not happen with current logic, see below fix.
        }
        
        // This method signature was designed to match uploadJson usage.
        // I should refactor uploadFile to handle local storage directly or change this signature.
        
        return null;
    }

    /* Refactored uploadToLocalNode to handle both file content string and array data */
    private function saveToLocalMock($content, string $extension): string
    {
        $path = public_path('mock_ipfs');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Generate a mock CID-like filename
        $cid = 'bafymock' . bin2hex(random_bytes(16));
        $filename = $cid . '.' . $extension;
        
        file_put_contents($path . '/' . $filename, $content);
        
        return $filename;
    }

    public function uploadJson(array $data): ?string
    {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
             $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
             return $this->saveToLocalMock($jsonContent, 'json');
        }
        // ... (existing real upload logic)
        try {
            $response = Http::withHeaders([
                'pinata_api_key' => $this->apiKey,
                'pinata_secret_api_key' => $this->apiSecret,
            ])->post('https://api.pinata.cloud/pinning/pinJSONToIPFS', [
                'pinataContent' => $data,
                'pinataMetadata' => [
                    'name' => 'certificate-' . ($data['title'] ?? time()),
                ],
            ]);

            if ($response->successful() && isset($response->json()['IpfsHash'])) {
                return $response->json()['IpfsHash'];
            }
            Log::error('IPFS upload failed, falling back to local', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);
            $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
            return $this->saveToLocalMock($jsonContent, 'json');
        } catch (\Exception $e) {
            Log::error('IPFS upload exception, falling back to local', ['error' => $e->getMessage()]);
            $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
            return $this->saveToLocalMock($jsonContent, 'json');
        }
    }

    public function uploadFile($fileContent, string $fileName): ?string
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?: 'pdf';

        if (empty($this->apiKey) || empty($this->apiSecret)) {
             return $this->saveToLocalMock($fileContent, $extension);
        }

        try {
            $response = Http::withHeaders([
                'pinata_api_key' => $this->apiKey,
                'pinata_secret_api_key' => $this->apiSecret,
            ])
            ->attach('file', $fileContent, $fileName)
            ->post('https://api.pinata.cloud/pinning/pinFileToIPFS');

            if ($response->successful() && isset($response->json()['IpfsHash'])) {
                return $response->json()['IpfsHash'];
            }
            Log::error('IPFS file upload failed, falling back to local', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return $this->saveToLocalMock($fileContent, $extension);
        } catch (\Exception $e) {
            Log::error('IPFS file upload exception, falling back to local', ['error' => $e->getMessage()]);
            return $this->saveToLocalMock($fileContent, $extension);
        }
    }

    /**
     * Get IPFS gateway URL for a CID
     */
    public function getGatewayUrl(string $cid): string
    {
        if (str_starts_with($cid, 'bafymock')) {
            return asset('mock_ipfs/' . $cid);
        }
        return rtrim($this->gatewayUrl, '/') . '/' . $cid;
    }

    /**
     * Create certificate metadata JSON
     */
    public function createCertificateMetadata(array $certificateData): array
    {
        $base = [
            'name' => $certificateData['title'],
            'description' => $certificateData['description'] ?? '',
            'recipient_name' => $certificateData['recipient_name'],
            'recipient_email' => $certificateData['recipient_email'],
            'issued_at' => $certificateData['issued_at'] ?? now()->toIso8601String(),
            'certificate_type' => 'certification',
            'version' => '1.0',
        ];

        // Add optional PDF/Image fields if present
        if (isset($certificateData['image'])) {
            $base['image'] = $certificateData['image'];
        }
        if (isset($certificateData['external_url'])) {
            $base['external_url'] = $certificateData['external_url'];
        }
        if (isset($certificateData['properties'])) {
            $base['properties'] = $certificateData['properties'];
        }

        return $base;
    }
}





