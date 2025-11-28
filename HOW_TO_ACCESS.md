# How to Access the Smart Contracts

This guide explains how to access and interact with the Cert and CertificateNFT smart contracts from different contexts.

## Table of Contents

1. [Deploy the Contracts](#1-deploy-the-contracts)
2. [Access from Frontend (Next.js/Wagmi)](#2-access-from-frontend-nextjswagmi)
3. [Access from Backend/Server](#3-access-from-backendserver)
4. [Access from Hardhat Console](#4-access-from-hardhat-console)
5. [Access from Node.js Script](#5-access-from-nodejs-script)
6. [Contract Functions Reference](#6-contract-functions-reference)

---

## 1. Deploy the Contracts

First, you need to deploy the contracts to get their addresses.

### Local Development

```bash
cd cert-contracts

# Start a local Hardhat node in one terminal
npx hardhat node

# In another terminal, deploy the contracts
npx hardhat run scripts/deploy.ts --network localhost
```

The deploy script will output both contract addresses:
```
Cert deployed to: 0x...
CertificateNFT deployed to: 0x...
```

### Testnet/Mainnet Deployment

Create a `.env` file in `cert-contracts/`:
```
SEPOLIA_RPC_URL=https://sepolia.infura.io/v3/YOUR_PROJECT_ID
PRIVATE_KEY=0xYOUR_PRIVATE_KEY
```

Update `hardhat.config.ts` to include network configuration, then:
```bash
npx hardhat run scripts/deploy.ts --network sepolia
```

---

## 2. Access from Frontend (Next.js/Wagmi)

The frontend uses Wagmi hooks to interact with contracts.

### Setup Environment Variables

Create `cert-frontend/.env.local`:
```env
NEXT_PUBLIC_WC_PROJECT_ID=your_walletconnect_project_id
NEXT_PUBLIC_CERT_ADDRESS=0x...          # From deployment
NEXT_PUBLIC_CERTNFT_ADDRESS=0x...       # From deployment
NEXT_PUBLIC_ADMIN_WALLETS=0xAdmin1,0xAdmin2
```

### Read Contract Data

```tsx
import { useReadContract } from "wagmi";
import { CERT_ABI, getCertAddress } from "../lib/contracts";

function MyComponent() {
  const contractAddress = getCertAddress();
  const { data: cids, isLoading } = useReadContract({
    abi: CERT_ABI,
    address: contractAddress,
    functionName: "getCIDs",
    args: ["0x..."], // address to query
  });

  return <div>{cids?.join(", ")}</div>;
}
```

### Write to Contract

```tsx
import { useWriteContract } from "wagmi";
import { CERT_ABI, getCertAddress } from "../lib/contracts";

function MyComponent() {
  const { writeContractAsync, isPending } = useWriteContract();
  const contractAddress = getCertAddress();

  async function issueCertificate(to: string, cid: string) {
    await writeContractAsync({
      abi: CERT_ABI,
      address: contractAddress,
      functionName: "issue",
      args: [to, cid],
    });
  }

  return <button onClick={() => issueCertificate("0x...", "bafy...")}>
    Issue Certificate
  </button>;
}
```

### Available Components

- `CertClient.tsx` - Issue and view certificates from Cert.sol
- `CertNftClient.tsx` - Mint and revoke CertificateNFT tokens
- `AdminPanel.tsx` - Admin-only functions

See `cert-frontend/src/components/` for full examples.

---

## 3. Access from Backend/Server

### Using Ethers.js

Install dependencies:
```bash
npm install ethers
```

Example:
```javascript
import { ethers } from "ethers";

// Connect to network
const provider = new ethers.JsonRpcProvider("http://localhost:8545");
// Or for public networks:
// const provider = new ethers.JsonRpcProvider("https://sepolia.infura.io/v3/YOUR_KEY");

// Connect wallet (for write operations)
const wallet = new ethers.Wallet(process.env.PRIVATE_KEY, provider);

// Get contract instance
const certAddress = "0x..."; // Your deployed address
const certABI = [
  "function issue(address to, string calldata cid) external",
  "function getCIDs(address owner) external view returns (string[] memory)"
];

const certContract = new ethers.Contract(certAddress, certABI, provider);

// Read data
const cids = await certContract.getCIDs("0x...");

// Write data (requires wallet)
const certContractWithSigner = certContract.connect(wallet);
await certContractWithSigner.issue("0x...", "bafy...");
```

### Using Web3.js

```javascript
import Web3 from "web3";

const web3 = new Web3("http://localhost:8545");
const certAddress = "0x...";
const certABI = [/* ABI array */];

const contract = new web3.eth.Contract(certABI, certAddress);

// Read
const cids = await contract.methods.getCIDs("0x...").call();

// Write
const accounts = await web3.eth.getAccounts();
await contract.methods.issue("0x...", "bafy...").send({
  from: accounts[0],
  gas: 100000
});
```

---

## 4. Access from Hardhat Console

Connect to a network and interact directly:

```bash
cd cert-contracts
npx hardhat console --network localhost
# or
npx hardhat console --network sepolia
```

In the console:
```javascript
// Get contract factory
const Cert = await ethers.getContractFactory("Cert");
const cert = await Cert.attach("0x..."); // Your deployed address

// Read
const cids = await cert.getCIDs("0x...");
console.log(cids);

// Write
const [signer] = await ethers.getSigners();
await cert.connect(signer).issue("0x...", "bafy...");

// For CertificateNFT - grant issuer role
const CertificateNFT = await ethers.getContractFactory("CertificateNFT");
const nft = await CertificateNFT.attach("0x...");
const issuerRole = await nft.ISSUER_ROLE();
await nft.grantRole(issuerRole, "0xIssuerAddress");
```

---

## 5. Access from Node.js Script

Create a script in `cert-contracts/scripts/`:

```typescript
import { ethers } from "hardhat";

async function main() {
  // Get deployed contract address
  const certAddress = "0x..."; // Your deployed address
  
  // Get contract instance
  const Cert = await ethers.getContractFactory("Cert");
  const cert = await Cert.attach(certAddress);

  // Read
  const cids = await cert.getCIDs("0x...");
  console.log("CIDs:", cids);

  // Write
  const [signer] = await ethers.getSigners();
  const tx = await cert.connect(signer).issue("0x...", "bafy...");
  await tx.wait();
  console.log("Transaction confirmed:", tx.hash);
}

main().catch(console.error);
```

Run with:
```bash
npx hardhat run scripts/your-script.ts --network localhost
```

---

## 6. Contract Functions Reference

### Cert.sol

**Functions:**
- `issue(address to, string calldata cid)` - Issue a certificate CID to an address
- `getCIDs(address owner) view returns (string[] memory)` - Get all CIDs issued to an address

**Events:**
- `Issued(address indexed to, string cid)` - Emitted when a certificate is issued

**Access:** Anyone can call `issue()` - no access control.

### CertificateNFT.sol

**Functions:**
- `mint(address to, string calldata ipfsCid) returns (uint256)` - Mint an NFT (requires `ISSUER_ROLE`)
- `revoke(uint256 tokenId)` - Revoke a certificate (requires `DEFAULT_ADMIN_ROLE`)
- `tokenURI(uint256 tokenId) view returns (string)` - Get token URI (reverts if revoked)
- `supportsInterface(bytes4 interfaceId) view returns (bool)` - ERC165 support

**Roles:**
- `ISSUER_ROLE` - Can mint new certificates
- `DEFAULT_ADMIN_ROLE` - Can revoke and manage roles

**Events:**
- `CertificateMinted(uint256 indexed tokenId, address indexed to, string cid)`
- `CertificateRevoked(uint256 indexed tokenId)`

**To grant issuer role:**
```javascript
const issuerRole = await nft.ISSUER_ROLE();
await nft.grantRole(issuerRole, "0xIssuerAddress");
```

---

## Quick Reference: Contract ABIs

The ABIs are defined in `cert-frontend/src/lib/contracts.ts`. You can also generate them:

```bash
cd cert-contracts
npx hardhat compile
# ABIs are in artifacts/contracts/Cert.sol/Cert.json
# and artifacts/contracts/CertificateNFT.sol/CertificateNFT.json
```

---

## Troubleshooting

**"Contract address not set"**
- Make sure environment variables are set correctly
- Restart the dev server after changing `.env.local`

**"Transaction failed"**
- Check you're on the correct network
- Verify contract address matches the network
- Ensure wallet has sufficient gas/ETH

**"Access denied" for CertificateNFT**
- Verify roles are granted correctly
- Check you're calling from an authorized address

**"Invalid token" or "Revoked"**
- Token ID doesn't exist or has been revoked
- Check token status before calling functions

