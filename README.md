# Certification Backend API

Laravel backend API for the certification dApp that handles certificate creation, IPFS storage, and blockchain interactions.

## Features

- REST API for certificate management
- Automatic IPFS upload of certificate metadata
- Blockchain integration for on-chain certificate issuance
- Transaction verification

## API Endpoints

### GET /api/certificates
List all certificates with pagination.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "recipient_name": "John Doe",
      "recipient_email": "john@example.com",
      "recipient_address": "0x...",
      "title": "Certificate Title",
      "description": "Certificate description",
      "ipfs_cid": "bafybei...",
      "ipfs_url": "https://gateway.pinata.cloud/ipfs/bafybei...",
      "transaction_hash": "0x...",
      "issued_at": "2025-11-28T12:00:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 1
}
```

### GET /api/certificates/{id}
Get a specific certificate by ID.

### POST /api/certificates
Create a new certificate.

**Request:**
```json
{
  "recipient_name": "John Doe",
  "recipient_email": "john@example.com",
  "recipient_address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb", // Optional
  "title": "Certificate Title",
  "description": "Certificate description" // Optional
}
```

**Response:**
```json
{
  "id": 1,
  "recipient_name": "John Doe",
  "recipient_email": "john@example.com",
  "title": "Certificate Title",
  "description": "Certificate description",
  "ipfs_cid": "bafybei...",
  "ipfs_url": "https://gateway.pinata.cloud/ipfs/bafybei...",
  "transaction_hash": "0x...",
  "on_chain_id": "1",
  "issued_at": "2025-11-28T12:00:00.000000Z"
}
```

**Workflow:**
1. Creates certificate record in database
2. Generates metadata JSON
3. Uploads metadata to IPFS
4. Issues certificate on blockchain (Cert.sol contract) with IPFS CID
5. Returns certificate with all details

### POST /api/certificates/verify
Verify a certificate transaction on-chain.

**Request:**
```json
{
  "transaction_hash": "0x..."
}
```

**Response:**
```json
{
  "transaction_hash": "0x...",
  "verified": true
}
```

## Setup

See [ENV_SETUP.md](./ENV_SETUP.md) for detailed environment configuration.

### Quick Start

1. Install dependencies:
   ```bash
   composer install
   ```

2. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Set up database:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

4. Configure `.env`:
   - Set `BLOCKCHAIN_RPC_URL` (e.g., `http://localhost:8545`)
   - Set `CERT_CONTRACT_ADDRESS` and `CERTNFT_CONTRACT_ADDRESS`
   - Set IPFS credentials (Pinata or local node)

5. Start server:
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000`

## Configuration

### Blockchain

- `BLOCKCHAIN_RPC_URL`: RPC endpoint (localhost or testnet)
- `DEPLOYER_PRIVATE_KEY`: Private key for issuing certificates (optional for localhost)
- `CERT_CONTRACT_ADDRESS`: Deployed Cert.sol address
- `CERTNFT_CONTRACT_ADDRESS`: Deployed CertificateNFT.sol address

### IPFS

**Pinata (Production):**
- `PINATA_API_KEY`: Your Pinata API key
- `PINATA_API_SECRET`: Your Pinata API secret

**Local Node (Development):**
- `IPFS_NODE_URL`: Local IPFS node URL (default: `http://localhost:5001`)

## Development

The backend integrates with:
- **Frontend**: Next.js app at `cert-frontend/`
- **Contracts**: Solidity contracts at `cert-contracts/`
- **IPFS**: Pinata or local IPFS node
- **Blockchain**: Ethereum-compatible network (localhost, Sepolia, etc.)

## Notes

- For local development with Hardhat node, `DEPLOYER_PRIVATE_KEY` is optional
- IPFS upload will use mock CIDs if not configured (for development)
- Certificate creation requires `recipient_address` for on-chain issuance
- Certificates are created with IPFS metadata even without blockchain interaction
