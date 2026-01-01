<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Certificate;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Http;

class SyncMetadata extends Command
{
    protected $signature = 'sync:metadata {--id=}';
    protected $description = 'Sync local certificate metadata from IPFS';

    public function handle(BlockchainService $blockchainService)
    {
        $id = $this->option('id');
        $query = Certificate::query();
        
        if ($id) {
            $query->where('token_id', $id);
        } else {
            $query->whereNotNull('token_id');
        }

        $certs = $query->get();
        $this->info("Syncing metadata for " . $certs->count() . " certificates...");

        foreach ($certs as $cert) {
            $this->info("Processing Token ID: {$cert->token_id}");
            
            // 1. Get URI from Chain
            $uri = $blockchainService->getTokenURI($cert->token_id);
            if (!$uri) {
                $this->warn(" - No URI found on chain.");
                continue;
            }

            // 2. Extract CID
            $cid = str_replace('ipfs://', '', $uri);
            if ($cid !== $cert->ipfs_cid) {
                $this->warn(" - CID Mismatch! Local: {$cert->ipfs_cid}, Chain: {$cid}");
                $cert->ipfs_cid = $cid;
            }

            // 3. Fetch Metadata
            $url = "https://gateway.pinata.cloud/ipfs/" . $cid;
            try {
                $res = Http::timeout(10)->get($url);
                if ($res->successful()) {
                    $meta = $res->json();
                    
                    $cert->title = $meta['title'] ?? $meta['name'] ?? $cert->title;
                    $cert->description = $meta['description'] ?? $cert->description;
                    $cert->recipient_name = $meta['recipient_name'] ?? $cert->recipient_name;
                    
                    $cert->save();
                    $this->info(" - Synced.");
                } else {
                    $this->error(" - Failed to fetch IPFS.");
                }
            } catch (\Exception $e) {
                $this->error(" - Error: " . $e->getMessage());
            }
        }
    }
}
