# ✅ Setup Complete - Next Steps

## What's Already Done

I've completed the following steps for you:

1. ✅ **Hardhat Node Started** - Running on port 8545
2. ✅ **Contracts Deployed**:
   - Cert: `0x5FbDB2315678afecb367f032d93F642f64180aa3`
   - CertificateNFT: `0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512`
3. ✅ **Frontend Configured** - Contract addresses updated in `.env.local`
4. ✅ **Backend Configured** - Contract addresses added to `.env`
5. ✅ **Database Migrations** - All migrations run successfully
6. ✅ **Helper Scripts** - Installed and ready
7. ✅ **Backend Server Started** - Running on port 8000
8. ✅ **Frontend Server Running** - Already was running on port 3000

## Current System Status

```
✅ Hardhat Node:      http://localhost:8545 (blockchain)
✅ Backend Server:    http://localhost:8000 (API)
✅ Frontend Server:   http://localhost:3000 (web app)
```

---

## What You Need to Do Now

### Step 1: Restart Frontend (Important!)

The frontend needs to be restarted to pick up the new contract addresses:

1. Go to the terminal where frontend is running
2. Press `Ctrl+C` to stop it
3. Run again: `npm run dev`

**OR** just refresh your browser at `http://localhost:3000`

### Step 2: Connect Your Wallet

1. Open `http://localhost:3000` in your browser
2. Click the **"Connect"** button in the header
3. Choose your wallet (MetaMask, etc.)

### Step 3: Add Localhost Network to MetaMask (If Needed)

If your wallet doesn't recognize localhost:

1. Open MetaMask
2. Go to Settings → Networks → Add Network
3. Enter:
   - **Network Name:** `Localhost 8545`
   - **RPC URL:** `http://localhost:8545`
   - **Chain ID:** `31337`
   - **Currency Symbol:** `ETH`
4. Save and switch to this network

### Step 4: Test Certificate Creation

You have **3 ways** to create a certificate:

#### Option A: Via Frontend UI
1. Connect your wallet
2. Use the certificate creation form on the homepage
3. Enter certificate details
4. Submit

#### Option B: Via Backend API (using curl)
```bash
curl -X POST http://localhost:8000/api/certificates \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_name": "John Doe",
    "recipient_email": "john@example.com",
    "recipient_address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
    "title": "Test Certificate",
    "description": "My first certificate"
  }'
```

**Important:** Replace `0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb` with a real address from your Hardhat node accounts (they're shown when you started the node).

#### Option C: Via Frontend API Component
- The frontend has a component that connects to the backend API
- Look for API-related sections in the UI

### Step 5: Verify Everything Works

1. **Check Certificates List:**
   - Visit `http://localhost:8000/api/certificates`
   - Should show your created certificates

2. **Check Frontend:**
   - View certificates in the frontend
   - Verify IPFS CIDs are generated
   - Check transaction hashes exist

3. **Verify Blockchain:**
   - Check that transaction hashes are real (not mock)
   - Verify certificates on-chain

---

## Quick Test Commands

```bash
# Test backend is running
curl http://localhost:8000/api/certificates

# Test blockchain connection
curl -X POST http://localhost:8545 \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"eth_blockNumber","params":[],"id":1}'

# Create a test certificate
curl -X POST http://localhost:8000/api/certificates \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_name": "Test User",
    "recipient_email": "test@example.com",
    "recipient_address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
    "title": "Test Cert",
    "description": "Testing"
  }'
```

**Note:** Use the first account address from your Hardhat node for `recipient_address` (shown when Hardhat started).

---

## Troubleshooting

### Frontend shows old contract addresses
- **Solution:** Restart the frontend server (Ctrl+C, then `npm run dev`)

### Can't connect to backend
- **Check:** Is backend running on port 8000?
- **Verify:** `curl http://localhost:8000/api/certificates`

### Blockchain transaction fails
- **Check:** Is Hardhat node still running?
- **Verify:** Contract addresses are correct in `.env` files

### IPFS shows mock CID
- **This is normal** if Pinata credentials aren't set
- For production, add Pinata API keys to backend `.env`

---

## What's Next?

1. ✅ **Test certificate creation** (see above)
2. ✅ **Test certificate viewing**
3. ✅ **Test certificate verification**
4. 🔄 **Grant CertificateNFT roles** (if you want to use NFT minting):
   ```bash
   cd cert-contracts
   npx hardhat run scripts/grant-roles.ts --network localhost 0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512 0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266
   ```

---

## Important Files Reference

- Frontend config: `cert-frontend/.env.local`
- Backend config: `cert-backend/.env`
- Contracts deployed at: See addresses above
- Step-by-step guide: `STEP_BY_STEP_GUIDE.md`
- API docs: `cert-backend/README.md`

---

## Summary

Everything is set up and running! 🎉

**Just restart your frontend** and you're ready to test. All servers are running:
- ✅ Blockchain (Hardhat)
- ✅ Backend API
- ✅ Frontend App

Open `http://localhost:3000` and start creating certificates!

