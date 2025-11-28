# Quick Start Checklist

Follow these steps to get your certification dApp fully functional:

## Prerequisites
- [ ] Node.js 18+ installed
- [ ] PHP 8.2+ installed
- [ ] Composer installed
- [ ] npm installed

## Step 1: Deploy Contracts

1. Start local Hardhat node:
   ```bash
   cd cert-contracts
   npx hardhat node
   ```
   Keep this terminal open.

2. In another terminal, deploy contracts:
   ```bash
   cd cert-contracts
   npx hardhat run scripts/deploy.ts --network localhost
   ```

3. Copy the printed contract addresses.

## Step 2: Configure Frontend

1. Update `cert-frontend/.env.local`:
   ```env
   NEXT_PUBLIC_WC_PROJECT_ID=demo
   NEXT_PUBLIC_CERT_ADDRESS=<from deployment>
   NEXT_PUBLIC_CERTNFT_ADDRESS=<from deployment>
   NEXT_PUBLIC_BACKEND_URL=http://localhost:8000
   ```

## Step 3: Configure Backend

1. Install dependencies:
   ```bash
   cd cert-backend
   composer install
   ```

2. Install helper scripts:
   ```bash
   cd scripts
   npm install
   cd ..
   ```

3. Set up environment:
   ```bash
   cp .env.example .env  # If .env doesn't exist
   php artisan key:generate
   ```

4. Update `cert-backend/.env`:
   ```env
   BLOCKCHAIN_RPC_URL=http://localhost:8545
   CERT_CONTRACT_ADDRESS=<from deployment>
   CERTNFT_CONTRACT_ADDRESS=<from deployment>
   
   # Optional: IPFS (uses mock CIDs if not set)
   PINATA_API_KEY=
   PINATA_API_SECRET=
   ```

5. Run migrations:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

## Step 4: Grant CertificateNFT Roles (Optional)

If you want to use CertificateNFT minting:

```bash
cd cert-contracts
npx hardhat run scripts/grant-roles.ts --network localhost <nftAddress> <issuerAddress1> [issuerAddress2] ...
```

## Step 5: Start Services

1. **Backend** (Terminal 1):
   ```bash
   cd cert-backend
   php artisan serve
   ```
   Should be running on http://localhost:8000

2. **Frontend** (Terminal 2):
   ```bash
   cd cert-frontend
   npm run dev
   ```
   Should be running on http://localhost:3000

3. **Hardhat Node** (Terminal 3 - already running):
   ```bash
   # Should already be running from Step 1
   ```

## Step 6: Test

1. Open http://localhost:3000 in your browser
2. Connect your wallet (MetaMask or other)
3. Switch to Localhost network in wallet
4. Test certificate creation:
   - Use the frontend to create a certificate
   - Or use the API directly: `POST http://localhost:8000/api/certificates`
5. Verify certificate appears in the list
6. Check IPFS CID is generated
7. Check blockchain transaction hash exists

## Troubleshooting

### Backend won't connect to blockchain
- Make sure Hardhat node is running
- Check `BLOCKCHAIN_RPC_URL` is set to `http://localhost:8545`
- Verify contract addresses are correct

### IPFS upload fails
- This is okay for development - mock CIDs will be used
- For production, set up Pinata credentials

### Frontend can't reach backend
- Check `NEXT_PUBLIC_BACKEND_URL` is set correctly
- Verify backend is running on port 8000
- Check browser console for CORS errors (add CORS middleware if needed)

### Helper script encoding fails
- Make sure `npm install` was run in `cert-backend/scripts/`
- Check Node.js is available: `node --version`
- The system will fall back to simplified encoding (may not work)

## Next Steps

- Set up Pinata for IPFS (recommended for production)
- Deploy to Sepolia testnet
- Configure proper authentication/authorization
- Add rate limiting
- Set up monitoring and logging

## See Also

- `cert-backend/README.md` - Backend API documentation
- `cert-backend/ENV_SETUP.md` - Detailed environment setup
- `HOW_TO_ACCESS.md` - How to access contracts
- `cert-frontend/FRONTEND_ACCESS.md` - Frontend development guide

