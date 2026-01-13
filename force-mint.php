<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Certificate;
use App\Services\BlockchainService;

$cert = Certificate::latest()->first();

if (!$cert) {
    die("No certificates found.\n");
}

echo "Working on Cert ID: {$cert->id}\n";

// Clear previous ghost state if any
$cert->token_id = null;
$cert->transaction_hash = null;
$cert->save();

echo "Minting via Backend Wallet...\n";

$service = new BlockchainService();
$recipient = $cert->recipient_address ?: '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266'; 
$cid = $cert->ipfs_cid;

try {
    // 1. Send Transaction (this calculates ID but doesn't save to DB object property)
    $result = $service->mintCertificateNFT($cert, $cid, $recipient);
    
    echo "Minted! Hash: " . $result['transaction_hash'] . "\n";
    echo "Token ID from Service: " . $result['token_id'] . "\n";

    // 2. Explicitly Save to DB
    $cert->refresh(); // Reload to get hash
    $cert->token_id = $result['token_id']; // Set ID from result
    $cert->on_chain_id = $result['token_id'];
    $cert->save();

    echo "SUCCESS: Database Updated to Token ID {$cert->token_id}\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
