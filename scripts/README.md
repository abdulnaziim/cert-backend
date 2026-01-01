# Backend Helper Scripts

Helper scripts for backend operations that require Node.js (for proper Ethereum ABI encoding).

## Setup

Install dependencies:

```bash
cd scripts
npm install
```

## Scripts

### encode-tx.js

Encodes Ethereum transaction data using proper ABI encoding.

**Usage:**
```bash
node encode-tx.js <function> <param1> [param2] ...

# Example: Encode issue(address, string) call
node encode-tx.js issue 0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb bafybei123...

# Example: Encode mint(address, string) call
node encode-tx.js mint 0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb bafybei123...

# Example: Encode revoke(uint256) call
node encode-tx.js revoke 1
```

**Supported functions:**
- `issue(address, string)` - Cert.sol issue function
- `mint(address, string)` - CertificateNFT.sol mint function
- `revoke(uint256)` - CertificateNFT.sol revoke function

The script outputs the encoded transaction data to stdout, which is used by the PHP `BlockchainService`.

## Notes

- The script requires `ethers` library (installed via npm install)
- Used by `BlockchainService` for proper ABI encoding
- Falls back to simplified encoding if script is not available (may not work correctly)





