#!/bin/bash

# Complete Firebase Phone Authentication Setup
# This script ensures all Firebase services are properly configured

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

echo -e "${BLUE}🔥 Complete Firebase Phone Authentication Setup${NC}"
echo -e "${BLUE}=============================================${NC}"
echo ""

# Step 1: Enable required APIs
echo -e "${YELLOW}Step 1: Enabling Required APIs${NC}"
echo "Enabling Identity Toolkit API..."
gcloud services enable identitytoolkit.googleapis.com --project=$PROJECT_ID

echo "Enabling Firebase Authentication API..."
gcloud services enable firebase.googleapis.com --project=$PROJECT_ID

echo "Enabling Cloud Resource Manager API..."
gcloud services enable cloudresourcemanager.googleapis.com --project=$PROJECT_ID

echo -e "${GREEN}✅ APIs enabled${NC}"
echo ""

# Step 2: Check API key permissions
echo -e "${YELLOW}Step 2: Checking API Key Configuration${NC}"
echo "Testing API key with Identity Toolkit..."

# Test the API key
RESPONSE=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}?key=${API_KEY}")

HTTP_CODE=$(echo $RESPONSE | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
RESPONSE_BODY=$(echo $RESPONSE | sed -e 's/HTTPSTATUS:.*//g')

echo "HTTP Code: $HTTP_CODE"

case $HTTP_CODE in
    200)
        echo -e "${GREEN}✅ API key is working correctly${NC}"
        ;;
    403)
        echo -e "${RED}❌ API key has restrictions${NC}"
        echo -e "${YELLOW}🔧 Fix: Remove API key restrictions or add Identity Toolkit API${NC}"
        echo -e "${BLUE}Go to: https://console.cloud.google.com/apis/credentials?project=${PROJECT_ID}${NC}"
        ;;
    404)
        echo -e "${RED}❌ Project configuration issue${NC}"
        echo -e "${YELLOW}🔧 Fix: Complete Firebase Console setup${NC}"
        ;;
    *)
        echo -e "${YELLOW}⚠️  Unexpected response: $HTTP_CODE${NC}"
        echo "Response: $RESPONSE_BODY"
        ;;
esac

echo ""

# Step 3: Test phone authentication endpoint
echo -e "${YELLOW}Step 3: Testing Phone Authentication Endpoint${NC}"

PHONE_TEST=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    -H "Content-Type: application/json" \
    -d '{"phoneNumber":"+15555555555","recaptchaToken":"test"}' \
    "https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode?key=${API_KEY}")

PHONE_HTTP_CODE=$(echo $PHONE_TEST | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
PHONE_RESPONSE=$(echo $PHONE_TEST | sed -e 's/HTTPSTATUS:.*//g')

echo "Phone auth endpoint: $PHONE_HTTP_CODE"

case $PHONE_HTTP_CODE in
    400)
        if echo "$PHONE_RESPONSE" | grep -q "INVALID_PHONE_NUMBER\|MISSING_PHONE_NUMBER\|CAPTCHA_CHECK_FAILED"; then
            echo -e "${GREEN}✅ Phone authentication is configured (expected error for test data)${NC}"
        else
            echo -e "${YELLOW}⚠️  Phone auth configured but with error: $PHONE_RESPONSE${NC}"
        fi
        ;;
    403)
        echo -e "${RED}❌ API key lacks phone authentication permissions${NC}"
        ;;
    404)
        echo -e "${RED}❌ Phone authentication not enabled${NC}"
        ;;
    500)
        echo -e "${RED}❌ Internal server error - phone auth may not be fully configured${NC}"
        ;;
    *)
        echo -e "${YELLOW}⚠️  Unexpected phone auth response: $PHONE_HTTP_CODE${NC}"
        ;;
esac

echo ""

# Step 4: Manual configuration instructions
echo -e "${YELLOW}Step 4: Manual Firebase Console Configuration${NC}"
echo -e "${BLUE}Complete these steps in Firebase Console:${NC}"
echo ""
echo -e "1. ${GREEN}Go to Firebase Console:${NC}"
echo -e "   https://console.firebase.google.com/project/${PROJECT_ID}/authentication/providers"
echo ""
echo -e "2. ${GREEN}Enable Phone Authentication:${NC}"
echo -e "   • Click on 'Phone' provider"
echo -e "   • Toggle 'Enable' to ON"
echo -e "   • Click 'Save'"
echo ""
echo -e "3. ${GREEN}Configure reCAPTCHA (for web):${NC}"
echo -e "   • Scroll to 'reCAPTCHA Enforcer'"
echo -e "   • Select appropriate settings"
echo -e "   • Add authorized domains: localhost, rb00-1948e.firebaseapp.com"
echo ""
echo -e "4. ${GREEN}Add Test Phone Numbers (optional):${NC}"
echo -e "   • Scroll to 'Phone numbers for testing'"
echo -e "   • Add: +15555555555 with OTP: 123456"
echo -e "   • Add: +15555555556 with OTP: 654321"
echo ""

# Step 5: Restaurant Bazar configuration
echo -e "${YELLOW}Step 5: Configure Restaurant Bazar${NC}"
echo -e "${BLUE}Configure in Restaurant Bazar admin panel:${NC}"
echo ""
echo -e "1. ${GREEN}Go to admin panel:${NC}"
echo -e "   http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
echo ""
echo -e "2. ${GREEN}Enable Firebase OTP:${NC}"
echo -e "   • Toggle 'Firebase OTP Verification Status' to ON"
echo -e "   • Enter Web API Key: ${API_KEY}"
echo -e "   • Click 'Submit'"
echo ""

# Step 6: Testing instructions
echo -e "${YELLOW}Step 6: Testing Instructions${NC}"
echo -e "${BLUE}Test the phone authentication:${NC}"
echo ""
echo -e "1. ${GREEN}In the Flutter web app:${NC}"
echo -e "   • Try to register with phone number"
echo -e "   • Use test numbers: +15555555555"
echo -e "   • Enter OTP: 123456"
echo ""
echo -e "2. ${GREEN}Check for errors:${NC}"
echo -e "   • Monitor browser console"
echo -e "   • Check Flutter app logs"
echo -e "   • Verify SMS delivery (for real numbers)"
echo ""

echo -e "${GREEN}🎉 Setup complete! Follow the manual steps above to finish configuration.${NC}"
echo ""
echo -e "${BLUE}📋 Quick Links:${NC}"
echo -e "Firebase Console: https://console.firebase.google.com/project/${PROJECT_ID}/authentication/providers"
echo -e "Restaurant Bazar: http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
echo -e "Flutter Web App: http://localhost:53085 (or current port)"
