<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$rpcUrl = 'http://localhost:8545';
$txHash = '0x1fdbed157cab0bbf5e5852c14386fd34e1c72f9df19807bae5b6924cd7773e21';

echo "Checking receipt for: $txHash\n";

$response = Http::post($rpcUrl, [
    'jsonrpc' => '2.0',
    'method' => 'eth_getTransactionReceipt',
    'params' => [$txHash],
    'id' => 1,
]);

if ($response->successful()) {
    $result = $response->json();
    $receipt = $result['result'] ?? null;
    
    if (!$receipt) {
        echo "Receipt not found!\n";
        exit;
    }

    echo "Receipt found.\n";
    // print_r($receipt);

    $transferEventSignature = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';
    
    foreach ($receipt['logs'] as $i => $log) {
        echo "Log #$i topics[0]: " . ($log['topics'][0] ?? 'N/A') . "\n";
        
        if (isset($log['topics'][0]) && $log['topics'][0] === $transferEventSignature) {
            echo "Match found!\n";
            print_r($log['topics']);
            
            if (isset($log['topics'][3])) {
                echo "Token ID Hex: " . $log['topics'][3] . "\n";
                echo "Token ID Dec: " . hexdec($log['topics'][3]) . "\n";
            }
        }
    }

    // Now try to run the actual service method
    $service = new \App\Services\BlockchainService();
    $tokenId = $service->getTokenIdFromReceipt($txHash);
    echo "\nService returned Token ID: " . ($tokenId ?? 'NULL') . "\n";

} else {
    echo "HTTP Error: " . $response->status() . "\n";
    echo $response->body();
}
