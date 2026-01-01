<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Certificate;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Http;

class RestoreRegistry extends Command
{
    protected $signature = 'restore:registry {--start=1} {--end=20}';
    protected $description = 'Restore certificate registry from blockchain';

    public function handle(BlockchainService $blockchainService)
    {
        $start = (int) $this->option('start');
        $end = (int) $this->option('end');

        $this->info("Starting recovery for tokens $start to $end...");

        for ($id = $start; $id <= $end; $id++) {
            $this->info("Checking Token ID: $id");

            // 1. Check if exists locally
            $exists = Certificate::where('token_id', $id)->orWhere('on_chain_id', (string)$id)->exists();
            if ($exists) {
                $this->info("  - Exists widely in DB. Skipping.");
                continue;
            }

            // 2. Fetch Token URI
            $uri = $blockchainService->getTokenURI($id);
            if (!$uri) {
                $this->warn("  - No Token URI found on-chain. Token might not exist.");
                continue;
            }
            $this->info("  - Found URI: $uri");

            // 3. Fetch Owner
            $owner = $blockchainService->getOwnerOf($id);
            if (!$owner) {
                $this->warn("  - Could not fetch owner. Skipping.");
                continue;
            }
            $this->info("  - Owner: $owner");

            // 4. Fetch IPFS Metadata
            $ipfsUrl = str_replace('ipfs://', 'https://gateway.pinata.cloud/ipfs/', $uri);
            try {
                $response = Http::timeout(10)->get($ipfsUrl);
                if (!$response->successful()) {
                    $this->error("  - Failed to fetch IPFS metadata.");
                    continue;
                }
                $metadata = $response->json();
            } catch (\Exception $e) {
                $this->error("  - IPFS Fetch Error: " . $e->getMessage());
                continue;
            }

            // 5. Create Record
            $cid = str_replace('ipfs://', '', $uri);
            
            $cert = new Certificate();
            $cert->title = $metadata['name'] ?? 'Restored Certificate';
            $cert->description = $metadata['description'] ?? 'Recovered from blockchain';
            $cert->recipient_name = $metadata['recipient_name'] ?? 'Unknown Recipient';
            $cert->recipient_email = $metadata['attributes'][0]['value'] ?? 'recovered@example.com'; // Trying to guess email from attributes if stored there, else dummy
            // Actually, our standard metadata doesn't enforce email in attributes usually, but let's check. 
            // Standard metadata: name, description, image, properties...
            // If email is missing, we use a placeholder or extract from description?
            
            $cert->recipient_address = $owner;
            $cert->token_id = $id;
            $cert->on_chain_id = (string)$id;
            $cert->ipfs_cid = $cid;
            // ipfs_url is an accessor, not a column
            $cert->issued_at = now(); // We don't have original issue date easily without block time
            $cert->transaction_hash = null; // Cannot easily recover hash
            
            $cert->save();
            $this->info("  - [SUCCESS] Restored Certificate ID: {$cert->id} for $cert->recipient_name");
        }

        $this->info("Recovery complete.");
    }
}
