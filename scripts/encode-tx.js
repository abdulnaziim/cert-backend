#!/usr/bin/env node

/**
 * Helper script to encode Ethereum transaction data
 * Usage: node encode-tx.js <function> <address> <cid>
 */

const { ethers } = require('ethers');

const functionName = process.argv[2];
const params = process.argv.slice(3).map(p => p.startsWith('0x') ? p.toLowerCase() : p);

if (!functionName || params.length === 0) {
  console.error('Usage: node encode-tx.js <function> <param1> [param2] ...');
  console.error('Example: node encode-tx.js issue 0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb bafybei...');
  process.exit(1);
}

try {
  let encodedData;

  if (functionName === 'issue') {
    // Cert.sol: issue(address to, string cid)
    const iface = new ethers.Interface([
      'function issue(address to, string cid)'
    ]);
    encodedData = iface.encodeFunctionData('issue', [params[0], params[1]]);
  } else if (functionName === 'mint') {
    // CertificateNFT.sol: mint(address to, string ipfsCid)
    const iface = new ethers.Interface([
      'function mint(address to, string ipfsCid)'
    ]);
    encodedData = iface.encodeFunctionData('mint', [params[0], params[1]]);
  } else if (functionName === 'revoke') {
    // CertificateNFT.sol: revoke(uint256 tokenId)
    const iface = new ethers.Interface([
      'function revoke(uint256 tokenId)'
    ]);
    encodedData = iface.encodeFunctionData('revoke', [params[0]]);
  } else if (functionName === 'tokenURI') {
    // CertificateNFT.sol: tokenURI(uint256 tokenId)
    const iface = new ethers.Interface([
      'function tokenURI(uint256 tokenId)'
    ]);
    encodedData = iface.encodeFunctionData('tokenURI', [params[0]]);
  } else if (functionName === 'ownerOf') {
    // CertificateNFT.sol: ownerOf(uint256 tokenId)
    const iface = new ethers.Interface([
      'function ownerOf(uint256 tokenId)'
    ]);
    encodedData = iface.encodeFunctionData('ownerOf', [params[0]]);
  } else {
    console.error(`Unknown function: ${functionName}`);
    process.exit(1);
  }

  console.log(encodedData);
} catch (error) {
  console.error('Error encoding:', error.message);
  process.exit(1);
}





