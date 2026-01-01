# Simple Test Guide - Just Follow These Steps! 

## Choose Your Method

### 🎯 Method 1: Test with Command Line (FASTEST - 30 seconds)

Just copy and paste this command in your terminal:

```bash
curl -X POST http://localhost:8000/api/certificates \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_name": "John Doe",
    "recipient_email": "john@example.com",
    "recipient_address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
    "title": "Test Certificate",
    "description": "My first test certificate"
  }'
```

**That's it!** Press Enter and you'll see the certificate created.

Then check it worked:
```bash
curl http://localhost:8000/api/certificates
```

---

### 🖥️ Method 2: Test with Browser (EASIEST - Visual)

1. **Open this URL in your browser:**
   ```
   http://localhost:8000/api/certificates
   ```
   You should see: `{"data":[],"current_page":1,...}` (empty is fine!)

2. **Create a certificate:**
   - Open this URL:
   ```
   http://localhost:8000/api/certificates
   ```
   - Actually, you need to use a tool to POST data. Let's use the command line method instead, or...

3. **Use the Frontend:**
   - Go to: `http://localhost:3000`
   - Connect your wallet
   - The homepage shows certificates you've received
   - You can also use the contract directly to issue CIDs

---

## What Each Method Does

### Method 1 (API/Command Line):
- ✅ Creates certificate in database
- ✅ Uploads metadata to IPFS (or creates mock CID)
- ✅ Issues certificate on blockchain
- ✅ Returns all details (ID, IPFS CID, transaction hash)

### Method 2 (Frontend):
- ✅ Shows certificates from blockchain
- ✅ Can issue CIDs directly to contracts
- ✅ Visual interface with wallet connection

---

## Recommended: Use Method 1 First

**Copy this exact command:**

```bash
curl -X POST http://localhost:8000/api/certificates \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_name": "Test User",
    "recipient_email": "test@example.com",
    "recipient_address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
    "title": "My Test Certificate",
    "description": "Testing the system"
  }'
```

**Expected Response:**
```json
{
  "id": 1,
  "recipient_name": "Test User",
  "recipient_email": "test@example.com",
  "recipient_address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
  "title": "My Test Certificate",
  "description": "Testing the system",
  "ipfs_cid": "bafybei...",
  "ipfs_url": "https://gateway.pinata.cloud/ipfs/bafybei...",
  "transaction_hash": "0x...",
  "on_chain_id": "1",
  "issued_at": "2025-11-28T..."
}
```

---

## Verify It Worked

### Check in Database/API:
```bash
curl http://localhost:8000/api/certificates
```

### Check in Browser:
Open: `http://localhost:8000/api/certificates`

### Check in Frontend:
1. Go to: `http://localhost:3000`
2. Connect wallet
3. Your certificate should appear (if it was issued to your address)

---

## Quick Troubleshooting

**"Connection refused"**
- Is backend running? Check port 8000

**"Failed to create"**
- Is Hardhat node running? Check port 8545

**No transaction hash**
- Check contract addresses in backend `.env` are correct

---

## That's All!

Start with **Method 1** (the curl command) - it's the fastest way to test everything is working!





