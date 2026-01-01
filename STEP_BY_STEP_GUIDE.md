# Step-by-Step Setup Guide

Follow these steps in order to get your certification dApp running.

## Step 1: Start Local Blockchain (Hardhat Node)

Open **Terminal 1** and run:

```bash
cd /Users/mac/Desktop/project/cert-contracts
npx hardhat node
```

**What to expect:**
- You'll see a list of accounts with private keys
- The node will keep running (don't close this terminal)
- It's running on `http://localhost:8545`

**Keep this terminal open!**

---

## Step 2: Deploy Contracts

Open **Terminal 2** (new terminal) and run:

```bash
cd /Users/mac/Desktop/project/cert-contracts
npx hardhat run scripts/deploy.ts --network localhost
```

**What to expect:**
- You'll see output like:
```
Deploying contracts with account: 0x...
Cert deployed to: 0x5FbDB2315678afecb367f032d93F642f64180aa3
CertificateNFT deployed to: 0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512

=== Deployment Summary ===
Cert Address: 0x5FbDB2315678afecb367f032d93F642f64180aa3
CertificateNFT Address: 0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512
```

**IMPORTANT:** Copy both addresses! You'll need them in the next steps.

---

## Step 3: Configure Frontend Environment

1. Open `cert-frontend/.env.local` in your editor
2. Update it with the addresses from Step 2:

```env
NEXT_PUBLIC_WC_PROJECT_ID=demo
NEXT_PUBLIC_CERT_ADDRESS=0x5FbDB2315678afecb367f032d93F642f64180aa3
NEXT_PUBLIC_CERTNFT_ADDRESS=0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512
NEXT_PUBLIC_BACKEND_URL=http://localhost:8000
```

Replace the addresses with the ones from your deployment!

---

## Step 4: Install Backend Dependencies

In **Terminal 2** (or open Terminal 3), run:

```bash
cd /Users/mac/Desktop/project/cert-backend
composer install
```

**What to expect:**
- This may take a few minutes
- You'll see packages being downloaded

Then install helper scripts:

```bash
cd /Users/mac/Desktop/project/cert-backend/scripts
npm install
cd ..
```

---

## Step 5: Configure Backend Environment

1. Check if `cert-backend/.env` exists. If not, copy from example:
   ```bash
   cd /Users/mac/Desktop/project/cert-backend
   cp .env.example .env  # Only if .env doesn't exist
   ```

2. Open `cert-backend/.env` in your editor

3. Update these lines with your contract addresses from Step 2:

```env
BLOCKCHAIN_RPC_URL=http://localhost:8545
CERT_CONTRACT_ADDRESS=0x5FbDB2315678afecb367f032d93F642f64180aa3
CERTNFT_CONTRACT_ADDRESS=0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512
```

Replace the addresses with YOUR deployment addresses!

**Optional:** Add IPFS credentials (leave empty for now - will use mock CIDs):
```env
PINATA_API_KEY=
PINATA_API_SECRET=
```

4. Generate application key:
   ```bash
   cd /Users/mac/Desktop/project/cert-backend
   php artisan key:generate
   ```

---

## Step 6: Set Up Backend Database

In the same terminal (cert-backend directory):

```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate
```

**What to expect:**
- You'll see migration messages
- Database tables will be created

---

## Step 7: Start Backend Server

In **Terminal 3** (or keep using Terminal 2), run:

```bash
cd /Users/mac/Desktop/project/cert-backend
php artisan serve
```

**What to expect:**
- Server starts on `http://localhost:8000`
- Keep this terminal open!

---

## Step 8: Start Frontend Server

Open **Terminal 4** (new terminal) and run:

```bash
cd /Users/mac/Desktop/project/cert-frontend
npm run dev
```

**What to expect:**
- Server starts on `http://localhost:3000`
- Keep this terminal open!

---

## Step 9: Test Your Setup

1. **Open your browser** and go to: `http://localhost:3000`

2. **Connect your wallet:**
   - Click "Connect" button in the header
   - Choose MetaMask (or your wallet)
   - Add Localhost network to MetaMask if needed:
     - Network Name: `Localhost 8545`
     - RPC URL: `http://localhost:8545`
     - Chain ID: `31337`
     - Currency Symbol: `ETH`

3. **Switch to Localhost network** in your wallet

4. **Test certificate creation:**
   - Use the frontend interface to create a certificate
   - Or test the API directly (see below)

---

## Step 10: Test Backend API (Optional)

Test if the backend is working:

```bash
# List certificates (should return empty array at first)
curl http://localhost:8000/api/certificates

# Create a test certificate
curl -X POST http://localhost:8000/api/certificates \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_name": "Test User",
    "recipient_email": "test@example.com",
    "recipient_address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
    "title": "Test Certificate",
    "description": "This is a test"
  }'
```

**Note:** For the certificate to be issued on-chain, you need to provide a valid `recipient_address` (an Ethereum address).

---

## What You Should Have Running

You should now have **4 terminals** running:

1. **Terminal 1:** Hardhat node (blockchain)
2. **Terminal 2:** (Can close after deployment)
3. **Terminal 3:** Backend server (`php artisan serve`)
4. **Terminal 4:** Frontend server (`npm run dev`)

---

## Troubleshooting

### "Connection refused" errors
- Make sure Hardhat node is running (Terminal 1)
- Make sure backend is running (Terminal 3)
- Check that addresses match in `.env` files

### Frontend can't connect to backend
- Verify backend is running on port 8000
- Check `NEXT_PUBLIC_BACKEND_URL` in frontend `.env.local`

### Contract address not found
- Make sure you copied the addresses correctly from deployment
- Addresses should start with `0x` and be 42 characters long

### Migration errors
- Make sure SQLite file was created: `touch database/database.sqlite`
- Check file permissions

---

## Next Steps After Setup

1. **Create certificates** via the frontend or API
2. **View certificates** - they'll appear in the frontend
3. **Check IPFS CIDs** - each certificate has an IPFS CID
4. **Verify transactions** - check transaction hashes on the blockchain

---

## Quick Reference: Terminal Commands

```bash
# Terminal 1: Blockchain
cd cert-contracts && npx hardhat node

# Terminal 2: Deploy (one time)
cd cert-contracts && npx hardhat run scripts/deploy.ts --network localhost

# Terminal 3: Backend
cd cert-backend && php artisan serve

# Terminal 4: Frontend
cd cert-frontend && npm run dev
```

---

## Need Help?

- Check `QUICK_START_CHECKLIST.md` for a checklist version
- See `cert-backend/README.md` for API documentation
- See `HOW_TO_ACCESS.md` for contract interaction details





