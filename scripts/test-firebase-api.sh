#!/bin/bash

# Firebase API Test Script
# Tests Firebase Authentication API connectivity

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

echo -e "${BLUE}🧪 Firebase API Connectivity Test${NC}"
echo -e "${BLUE}=================================${NC}"
echo ""

# Test 1: Check if Identity Toolkit API is accessible
echo -e "${YELLOW}Test 1: Identity Toolkit API${NC}"
RESPONSE1=$(curl -s -o /dev/null -w "%{http_code}" \
    "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}?key=${API_KEY}")

echo -e "URL: https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}"
echo -e "Response Code: $RESPONSE1"

if [ "$RESPONSE1" = "200" ]; then
    echo -e "${GREEN}✅ Identity Toolkit API is accessible${NC}"
elif [ "$RESPONSE1" = "403" ]; then
    echo -e "${YELLOW}⚠️  API key needs permissions (expected before phone auth is enabled)${NC}"
elif [ "$RESPONSE1" = "404" ]; then
    echo -e "${RED}❌ Project not found or API not enabled${NC}"
else
    echo -e "${RED}❌ Unexpected response: $RESPONSE1${NC}"
fi

echo ""

# Test 2: Check Firebase project configuration
echo -e "${YELLOW}Test 2: Firebase Project Config${NC}"
RESPONSE2=$(curl -s "https://firebase.googleapis.com/v1beta1/projects/${PROJECT_ID}/webApps" \
    -H "Authorization: Bearer $(gcloud auth print-access-token)" 2>/dev/null || echo "error")

if [[ "$RESPONSE2" != "error" ]] && [[ "$RESPONSE2" == *"apps"* ]]; then
    echo -e "${GREEN}✅ Firebase project configuration accessible${NC}"
else
    echo -e "${YELLOW}⚠️  Firebase project config needs verification${NC}"
fi

echo ""

# Test 3: Verify API key format
echo -e "${YELLOW}Test 3: API Key Validation${NC}"
if [[ ${#API_KEY} -eq 39 ]] && [[ $API_KEY == AIza* ]]; then
    echo -e "${GREEN}✅ API key format is correct${NC}"
else
    echo -e "${RED}❌ API key format appears invalid${NC}"
fi

echo ""

# Test 4: Check if phone authentication is enabled
echo -e "${YELLOW}Test 4: Phone Authentication Status${NC}"
echo -e "${BLUE}📋 Manual verification required:${NC}"
echo -e "1. Go to: ${BLUE}https://console.firebase.google.com/project/${PROJECT_ID}/authentication/providers${NC}"
echo -e "2. Check if Phone provider is enabled"
echo -e "3. Ensure reCAPTCHA is configured"

echo ""

# Recommendations
echo -e "${BLUE}🔧 Troubleshooting Steps:${NC}"
echo ""

if [ "$RESPONSE1" = "404" ]; then
    echo -e "${YELLOW}For 404 Error:${NC}"
    echo -e "1. Ensure Identity Toolkit API is enabled:"
    echo -e "   ${BLUE}gcloud services enable identitytoolkit.googleapis.com --project=${PROJECT_ID}${NC}"
    echo -e "2. Wait 5-10 minutes for API to propagate"
    echo -e "3. Verify project exists and you have access"
fi

if [ "$RESPONSE1" = "403" ]; then
    echo -e "${YELLOW}For 403 Error:${NC}"
    echo -e "1. Enable Phone Authentication in Firebase Console"
    echo -e "2. Configure reCAPTCHA settings"
    echo -e "3. Ensure API key has proper permissions"
fi

echo ""
echo -e "${BLUE}📱 Next Steps:${NC}"
echo -e "1. ${GREEN}Complete Firebase Console setup${NC}"
echo -e "2. ${GREEN}Enable Phone Authentication${NC}"
echo -e "3. ${GREEN}Configure reCAPTCHA${NC}"
echo -e "4. ${GREEN}Test again with this script${NC}"
echo -e "5. ${GREEN}Configure Restaurant Bazar admin${NC}"

echo ""
echo -e "${BLUE}💡 Note:${NC} The 404 error is normal until Phone Authentication is fully configured in Firebase Console."
