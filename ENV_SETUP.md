# Backend Environment Configuration

## Required Environment Variables

Create a `.env` file in `cert-backend/` with the following variables:

### Application
```env
APP_NAME=CertBackend
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Database
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Or for MySQL/PostgreSQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cert_db
DB_USERNAME=root
DB_PASSWORD=
```

### Blockchain Configuration
```env
# RPC endpoint for blockchain
BLOCKCHAIN_RPC_URL=http://localhost:8545

# Private key of the account that will issue certificates (optional for localhost)
DEPLOYER_PRIVATE_KEY=0x...

# Contract addresses
CERT_CONTRACT_ADDRESS=0x...
CERTNFT_CONTRACT_ADDRESS=0x...
```

### IPFS Configuration

**Option 1: Pinata (Recommended for Production)**
```env
PINATA_API_KEY=your_pinata_api_key
PINATA_API_SECRET=your_pinata_api_secret
IPFS_GATEWAY_URL=https://gateway.pinata.cloud/ipfs/
```

**Option 2: Local IPFS Node**
```env
IPFS_NODE_URL=http://localhost:5001
IPFS_GATEWAY_URL=http://localhost:8080/ipfs/
```

**Option 3: Infura IPFS**
```env
INFURA_IPFS_PROJECT_ID=your_project_id
INFURA_IPFS_PROJECT_SECRET=your_project_secret
IPFS_GATEWAY_URL=https://infura-ipfs.io/ipfs/
```

### CORS Configuration (for frontend)
```env
FRONTEND_URL=http://localhost:3000
```

## Setup Steps

1. Copy the base `.env` file:
   ```bash
   cd cert-backend
   cp .env.example .env
   ```

2. Generate application key:
   ```bash
   php artisan key:generate
   ```

3. Configure database (if using SQLite):
   ```bash
   touch database/database.sqlite
   ```

4. Run migrations:
   ```bash
   php artisan migrate
   ```

5. Install helper scripts dependencies (optional but recommended):
   ```bash
   cd scripts
   npm install
   cd ..
   ```

6. Update the `.env` file with your blockchain and IPFS credentials.

## Getting Started

1. **For Local Development**:
   - Start Hardhat node: `cd cert-contracts && npx hardhat node`
   - Set `BLOCKCHAIN_RPC_URL=http://localhost:8545`
   - Deploy contracts and set addresses in `.env`

2. **For Production/Testnet**:
   - Set `BLOCKCHAIN_RPC_URL` to your RPC endpoint (e.g., Sepolia)
   - Set `DEPLOYER_PRIVATE_KEY` to your funded account
   - Set `CERT_CONTRACT_ADDRESS` and `CERTNFT_CONTRACT_ADDRESS`

3. **IPFS Setup**:
   - Sign up at https://pinata.cloud for production
   - Or install and run local IPFS node for development
   - Update `.env` with your IPFS configuration

## Notes

- For local development, `DEPLOYER_PRIVATE_KEY` is optional if using Hardhat node with unlocked accounts
- IPFS credentials are optional - the system will use mock CIDs if not configured
- Always keep private keys secure and never commit them to version control
- The backend uses a Node.js helper script for proper ABI encoding (see `scripts/README.md`)
- If the helper script is not available, a simplified encoding is used (may not work correctly)

