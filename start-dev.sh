#!/bin/bash

# start-dev.sh
# Automates the startup of the Certification App
# 1. Starts Local Blockchain (Hardhat)
# 2. Deploys Smart Contracts
# 3. Updates Environment Variables (Frontend & Backend)
# 4. Resets Backend Database (to match new blockchain state)
# 5. Starts Backend & Frontend Servers

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Starting Certification dApp Dev Environment ===${NC}"

# 0. Kill existing processes
echo -e "${BLUE}[0/5] Cleaning up ports...${NC}"
kill -9 $(lsof -t -i:8545) 2>/dev/null
kill -9 $(lsof -t -i:8000) 2>/dev/null
kill -9 $(lsof -t -i:3000) 2>/dev/null

# 1. Start Hardhat Node
echo -e "${BLUE}[1/5] Starting Local Blockchain...${NC}"
cd cert-contracts
npx hardhat node > ../node.log 2>&1 &
NODE_PID=$!
cd ..

# Wait for node to initialise
echo "Waiting for blockchain to start..."
sleep 5

# Check if node is running
if ! lsof -i:8545 > /dev/null; then
    echo -e "${RED}Failed to start Hardhat Node. Check node.log${NC}"
    exit 1
fi
echo -e "${GREEN}Blockchain running on port 8545${NC}"

# 2. Deploy Contracts
echo -e "${BLUE}[2/5] Deploying Contracts...${NC}"
cd cert-contracts
npx hardhat run scripts/deploy.ts --network localhost > ../deploy_output.txt
cd ..

# Check deployment success
if [ $? -ne 0 ]; then
    echo -e "${RED}Deployment failed. Check deploy_output.txt${NC}"
    kill $NODE_PID
    exit 1
fi

cat deploy_output.txt

# Extract Addresses
CERT_ADDR=$(grep "NEXT_PUBLIC_CERT_ADDRESS=" deploy_output.txt | cut -d= -f2 | tr -d '[:space:]')
NFT_ADDR=$(grep "NEXT_PUBLIC_CERTNFT_ADDRESS=" deploy_output.txt | cut -d= -f2 | tr -d '[:space:]')

if [ -z "$CERT_ADDR" ] || [ -z "$NFT_ADDR" ]; then
    echo -e "${RED}Failed to extract contract addresses${NC}"
    kill $NODE_PID
    exit 1
fi

echo -e "${GREEN}Captured Addresses:${NC}"
echo "Cert: $CERT_ADDR"
echo "NFT:  $NFT_ADDR"

# 2b. Grant Issuer Role
echo -e "${BLUE}[2.5/5] Granting Issuer Role...${NC}"
cd cert-contracts
npx hardhat run scripts/grant-issuer-role.js --network localhost
cd ..

# 3. Update Environment Variables
echo -e "${BLUE}[3/5] Updating Configuration...${NC}"

# Updates cert-frontend/.env.local
FRONTEND_ENV="cert-frontend/.env.local"
if [ -f "$FRONTEND_ENV" ]; then
    # Use sed depending on OS (Mac requires '')
    sed -i '' "s/NEXT_PUBLIC_CERT_ADDRESS=.*/NEXT_PUBLIC_CERT_ADDRESS=$CERT_ADDR/" "$FRONTEND_ENV"
    sed -i '' "s/NEXT_PUBLIC_CERTNFT_ADDRESS=.*/NEXT_PUBLIC_CERTNFT_ADDRESS=$NFT_ADDR/" "$FRONTEND_ENV"
    echo -e "${GREEN}Updated frontend config${NC}"
else
    echo -e "${RED}Frontend .env.local not found${NC}"
fi

# Updates cert-backend/.env
BACKEND_ENV="cert-backend/.env"
if [ -f "$BACKEND_ENV" ]; then
    sed -i '' "s/CERT_CONTRACT_ADDRESS=.*/CERT_CONTRACT_ADDRESS=$CERT_ADDR/" "$BACKEND_ENV"
    sed -i '' "s/CERTNFT_CONTRACT_ADDRESS=.*/CERTNFT_CONTRACT_ADDRESS=$NFT_ADDR/" "$BACKEND_ENV"
    echo -e "${GREEN}Updated backend config${NC}"
else
    echo -e "${RED}Backend .env not found${NC}"
fi

# 4. Setup Backend
echo -e "${BLUE}[4/5] Starting Backend...${NC}"
cd cert-backend
# Reset DB to match fresh blockchain
php artisan migrate:fresh --force > /dev/null 2>&1
php -S 127.0.0.1:8000 -t public > ../backend.log 2>&1 &
BACKEND_PID=$!
cd ..
echo -e "${GREEN}Backend running on port 8000${NC}"

# 5. Start Frontend
echo -e "${BLUE}[5/5] Starting Frontend...${NC}"
cd cert-frontend
npm run dev > ../frontend.log 2>&1 &
FRONTEND_PID=$!
cd ..
echo -e "${GREEN}Frontend running on port 3000${NC}"

echo -e "\n${GREEN}=== SYSTEM READY ===${NC}"
echo "Access Frontend: http://localhost:3000"
echo "Access Backend:  http://localhost:8000/api/certificates"
echo "Logs: node.log, backend.log, frontend.log"
echo "To stop: Press CTRL+C (This script will trap and cleanup)"

# Cleanup function
cleanup() {
    echo -e "\n${BLUE}Shutting down services...${NC}"
    kill $NODE_PID 2>/dev/null
    kill $BACKEND_PID 2>/dev/null
    kill $FRONTEND_PID 2>/dev/null
    exit 0
}

# Trap SIGINT (Ctrl+C)
trap cleanup SIGINT

# Wait indefinitely
wait
