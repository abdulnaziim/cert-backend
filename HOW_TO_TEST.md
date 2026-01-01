# How to Test Certificate Creation - Simple Guide

There are **2 easy ways** to test creating a certificate. Choose whichever you prefer!

---

## Method 1: Using the Frontend (Visual, Easy) ⭐ RECOMMENDED

### Step-by-Step:

1. **Open your browser** and go to:
   ```
   http://localhost:3000
   ```

2. **Connect your wallet:**
   - Look for a **"Connect"** or **"Connect Wallet"** button (usually in the top right)
   - Click it
   - Choose your wallet (MetaMask, WalletConnect, etc.)
   - **Important:** Make sure your wallet is connected to the Localhost network

3. **Look for certificate creation form:**
   - On the homepage, you might see a form to create certificates
   - OR look for a page/tab that says "Create Certificate" or "Issue Certificate"
   - OR use the admin panel if you have admin access

4. **Fill out the form:**
   - **Recipient Name:** (e.g., "John Doe")
   - **Recipient Email:** (e.g., "john@example.com")
   - **Recipient Address:** (your wallet address or another address)
   - **Title:** (e.g., "Web Development Certificate")
   - **Description:** (optional, e.g., "Completed web development course")

5. **Submit the form:**
   - Click the submit/issue button
   - Confirm the transaction in your wallet if prompted
   - Wait for confirmation

6. **Check the result:**
   - You should see a success message
   - The certificate should appear in the list
   - You'll see an IPFS CID and transaction hash

---

## Method 2: Using the API (Command Line, Fast)

This method uses your terminal to send a request directly to the backend.

### Step-by-Step:

1. **Open your terminal** (any terminal)

2. **Copy and paste this command:**

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

3. **Press Enter**

4. **What to expect:**
   - You should see a JSON response with certificate details
   - Look for:
     - `"ipfs_cid"` - The IPFS address
     - `"transaction_hash"` - The blockchain transaction
     - `"id"` - The certificate ID

5. **Check if it worked:**
   ```bash
   # List all certificates
   curl http://localhost:8000/api/certificates
   ```

---

## Which Address Should I Use?

For `recipient_address`, you can use:

### Option A: Use the first Hardhat account (Easiest)
The Hardhat node shows accounts when it starts. Use the first one:
```
0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266
```

### Option B: Use your wallet address
1. Open your wallet (MetaMask, etc.)
2. Copy your address (it starts with `0x...`)
3. Use that address

### Option C: Check Hardhat accounts
When you started Hardhat node, it showed a list of accounts. Any of those will work!

---

## Quick Test Commands

### 1. Check if backend is working:
```bash
curl http://localhost:8000/api/certificates
```
**Expected:** Should return `{"data":[], ...}` (empty list is fine!)

### 2. Create a certificate:
```bash
curl -X POST http://localhost:8000/api/certificates \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_name": "Test User",
    "recipient_email": "test@example.com",
    "recipient_address": "0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266",
    "title": "Test Certificate",
    "description": "Testing the system"
  }'
```

### 3. List certificates:
```bash
curl http://localhost:8000/api/certificates
```
**Expected:** Should show your created certificate in the list

### 4. View in browser:
Open: `http://localhost:8000/api/certificates`
You'll see the JSON in your browser (easier to read!)

---

## What Should Happen?

When you create a certificate:

1. ✅ **Database:** Certificate saved in database
2. ✅ **IPFS:** Metadata uploaded to IPFS (or mock CID if not configured)
3. ✅ **Blockchain:** Transaction sent to Cert.sol contract
4. ✅ **Response:** You get back certificate with:
   - ID
   - IPFS CID
   - Transaction hash
   - All the details you provided

---

## Troubleshooting

### "Connection refused" error
- **Check:** Is backend running? Should be on port 8000
- **Fix:** Start it: `cd cert-backend && php artisan serve`

### "Failed to create certificate" error
- **Check:** Is Hardhat node running? Should be on port 8545
- **Fix:** Start it: `cd cert-contracts && npx hardhat node`

### Transaction hash looks fake (starts with random characters)
- **This is normal** if blockchain connection isn't working
- Check that Hardhat node is running
- Check contract addresses in backend `.env`

### IPFS CID looks fake
- **This is normal** if IPFS isn't configured
- For production, add Pinata API keys to backend `.env`
- For development, mock CIDs are fine

---

## Visual Guide - Frontend Method

```
1. Open Browser → http://localhost:3000
2. Click "Connect" button
3. Choose your wallet
4. Find "Create Certificate" form
5. Fill in:
   - Name: John Doe
   - Email: john@example.com
   - Address: 0x...
   - Title: My Certificate
6. Click "Submit" or "Issue"
7. ✅ Success! Certificate created
```

## Visual Guide - API Method

```
1. Open Terminal
2. Copy the curl command
3. Paste and press Enter
4. See JSON response with certificate
5. ✅ Done! Certificate created
```

---

## Recommendation

**Start with Method 1 (Frontend)** - it's more visual and easier to understand.

If the frontend doesn't have a clear form, use **Method 2 (API)** - it's faster and always works.

Both methods create the same certificate and save it the same way!

---

## Need More Help?

- Check `NEXT_STEPS.md` for overall setup
- Check `cert-backend/README.md` for API details
- Check browser console (F12) for frontend errors
- Check backend logs in terminal where `php artisan serve` is running





