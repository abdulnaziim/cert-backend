#!/usr/bin/env node

/**
 * Helper script to sign and send Ethereum transactions
 * Usage: node send-tx.js <rpcUrl> <privateKey> <to> <data>
 */

const { ethers } = require('ethers');

const rpcUrl = process.argv[2];
const privateKey = process.argv[3];
const to = process.argv[4];
const data = process.argv[5];

if (!rpcUrl || !privateKey || !to || !data) {
    console.error('Usage: node send-tx.js <rpcUrl> <privateKey> <to> <data>');
    process.exit(1);
}

async function main() {
    try {
        const provider = new ethers.JsonRpcProvider(rpcUrl);
        const wallet = new ethers.Wallet(privateKey, provider);

        const tx = {
            to: to,
            data: data,
            gasLimit: 200000, // Reasonable limit for a mint
        };

        const response = await wallet.sendTransaction(tx);
        console.log(response.hash);
    } catch (error) {
        console.error('Error sending transaction:', error.message);
        process.exit(1);
    }
}

main();
