<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Certificate;
use App\Services\BlockchainService;

echo "Syncing pending certificates...\n";

$service = new BlockchainService();
$pending = Certificate::whereNotNull('transaction_hash')->whereNull('token_id')->get();

echo "Found " . $pending->count() . " pending certificates.\n";

foreach ($pending as $cert) {
    echo "Checking Cert ID {$cert->id} (Hash: {$cert->transaction_hash})...\n";
    
    // Attempt to resolve
    $tokenId = $service->getTokenIdFromReceipt($cert->transaction_hash);
    
    if ($tokenId) {
        $cert->token_id = $tokenId;
        $cert->on_chain_id = $tokenId;
        $cert->save();
        echo "  [SUCCESS] Resolved Token ID: $tokenId\n";
    } else {
        echo "  [FAIL] Could not resolve Token ID. (Tx might be failed or not mined yet)\n";
    }
}

echo "Done.\n";
