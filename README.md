# Certification dApp Monorepo

This repository contains:
- `cert-contracts`: Solidity smart contracts (`Cert.sol`, `CertificateNFT.sol`)
- `cert-frontend`: Next.js dApp UI with RainbowKit/Wagmi integration
- `cert-backend`: Laravel backend that handles certificate creation, IPFS storage, and blockchain interactions.

## Quick Start

1) Install
```bash
# contracts
cd cert-contracts && npm install
# frontend
cd ../cert-frontend && npm install
# backend
cd ../cert-backend && composer install
```

2) Configure environment
- Frontend (`cert-frontend/.env.local`):
```
NEXT_PUBLIC_WC_PROJECT_ID=<your_walletconnect_project_id>
NEXT_PUBLIC_CERT_ADDRESS=0x...            # Deployed Cert.sol
NEXT_PUBLIC_CERTNFT_ADDRESS=0x...         # Deployed CertificateNFT.sol
```

3) Run local blockchain (optional)
```bash
cd cert-contracts
npx hardhat node
```

4) Deploy contracts
```bash
# localhost
npx hardhat run scripts/deploy.ts --network localhost
# or sepolia/mainnet (requires RPC + PRIVATE_KEY)
# npx hardhat run scripts/deploy.ts --network sepolia
```
Copy the printed addresses into `cert-frontend/.env.local`.

5) Start frontend
```bash
cd ../cert-frontend
npm run dev
```
Open http://localhost:3000, connect your wallet, and interact.

## Packages

### cert-contracts
- Build: `npx hardhat compile`
- Test: `npx hardhat test`
- Deploy: `npx hardhat run scripts/deploy.ts --network <network>`
- Roles (CertificateNFT):
  - `ISSUER_ROLE`: can mint
  - `DEFAULT_ADMIN_ROLE`: can revoke and grant roles

See `cert-contracts/README.md` for role grant examples and details.

### cert-frontend
- Tech: Next.js, RainbowKit, Wagmi, React Query, react-hot-toast
- Features:
  - Connect wallet, multi-network (mainnet, sepolia, localhost)
  - Cert.sol: read CIDs and issue new CID
  - CertificateNFT.sol: mint with IPFS CID, revoke by tokenId
- Run: `npm run dev`

See `cert-frontend/README.md` for step-by-step usage.

### cert-backend (Laravel API)

Laravel backend API for the certification dApp that handles certificate creation, IPFS storage, and blockchain interactions.

**Features:**
- REST API for certificate management
- Automatic IPFS upload of certificate metadata
- Blockchain integration for on-chain certificate issuance
- Transaction verification

**Setup:**
1. Configure `.env` in `cert-backend`:
   - Set `BLOCKCHAIN_RPC_URL` (e.g., `http://localhost:8545`)
   - Set `CERT_CONTRACT_ADDRESS` and `CERTNFT_CONTRACT_ADDRESS`
   - Set IPFS credentials (Pinata or local node)
2. Run migrations: `php artisan migrate`
3. Start server: `php artisan serve`

The API will be available at `http://localhost:8000`.
See `cert-backend/README.md` or the `ENV_SETUP.md` for more details.

## Repository Layout
```
cert-backend/
cert-contracts/
cert-frontend/
```

## Notes
- Use real WalletConnect Project ID in production.
- Ensure contract addresses match your selected network in the wallet.
- Localhost flow: run Hardhat node, deploy, set env addresses, then start the frontend.
