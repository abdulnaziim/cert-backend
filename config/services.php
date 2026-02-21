<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ipfs' => [
        'pinata_api_key' => env('PINATA_API_KEY'),
        'pinata_api_secret' => env('PINATA_API_SECRET'),
        'node_url' => env('IPFS_NODE_URL', 'http://localhost:5001'),
        'gateway_url' => env('IPFS_GATEWAY_URL', 'https://gateway.pinata.cloud/ipfs/'),
    ],

    'blockchain' => [
        'rpc_url' => env('BLOCKCHAIN_RPC_URL', 'http://localhost:8545'),
        'sepolia_rpc_url' => env('SEPOLIA_RPC_URL', 'https://ethereum-sepolia-rpc.publicnode.com'),
        'private_key' => env('DEPLOYER_PRIVATE_KEY', ''),
        'cert_contract_address' => env('CERT_CONTRACT_ADDRESS', ''),
        'cert_nft_contract_address' => env('CERTNFT_CONTRACT_ADDRESS', ''),
        'sepolia_nft_contract_address' => env('SEPOLIA_CERTNFT_ADDRESS', ''),
    ],

    'etherscan' => [
        'api_key' => env('ETHERSCAN_API_KEY', ''),
    ],

];
