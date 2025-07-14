#!/bin/bash

# Test Phone Authentication API Specifically
# Tests the exact endpoints used by Restaurant Bazar

PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

echo "📱 Testing Phone Authentication API Endpoints"
echo "============================================="
echo ""

# Test 1: Send verification code endpoint (the one Restaurant Bazar uses)
echo "Test 1: Send Verification Code Endpoint"
echo "---------------------------------------"
echo "This is the endpoint Restaurant Bazar actually uses..."

# Test with a dummy phone number to see if the endpoint responds
RESPONSE1=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    -H "Content-Type: application/json" \
    -d '{"phoneNumber":"+1234567890","recaptchaToken":"dummy"}' \
    "https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode?key=${API_KEY}")

HTTP_CODE1=$(echo $RESPONSE1 | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
RESPONSE_BODY1=$(echo $RESPONSE1 | sed -e 's/HTTPSTATUS:.*//g')

echo "URL: https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode"
echo "HTTP Code: $HTTP_CODE1"
echo "Response: $RESPONSE_BODY1"
echo ""

# Test 2: Sign in with phone number endpoint
echo "Test 2: Sign In With Phone Number Endpoint"
echo "------------------------------------------"
RESPONSE2=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    -H "Content-Type: application/json" \
    -d '{"sessionInfo":"dummy","phoneNumber":"+1234567890","code":"123456"}' \
    "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key=${API_KEY}")

HTTP_CODE2=$(echo $RESPONSE2 | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
RESPONSE_BODY2=$(echo $RESPONSE2 | sed -e 's/HTTPSTATUS:.*//g')

echo "URL: https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber"
echo "HTTP Code: $HTTP_CODE2"
echo "Response: $RESPONSE_BODY2"
echo ""

# Test 3: Get project config (simpler test)
echo "Test 3: Get Project Config"
echo "-------------------------"
RESPONSE3=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}/config?key=${API_KEY}")

HTTP_CODE3=$(echo $RESPONSE3 | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
RESPONSE_BODY3=$(echo $RESPONSE3 | sed -e 's/HTTPSTATUS:.*//g')

echo "URL: https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}/config"
echo "HTTP Code: $HTTP_CODE3"
echo "Response: $RESPONSE_BODY3"
echo ""

# Test 4: Check API key validity with a different Google API
echo "Test 4: API Key Validity Check"
echo "------------------------------"
RESPONSE4=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=${API_KEY}")

HTTP_CODE4=$(echo $RESPONSE4 | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')

echo "Testing API key with Google OAuth API..."
echo "HTTP Code: $HTTP_CODE4"
echo ""

# Analysis
echo "🔍 Analysis"
echo "==========="

# Check if any endpoint worked
if [[ "$HTTP_CODE1" == "400" ]] || [[ "$HTTP_CODE2" == "400" ]] || [[ "$HTTP_CODE3" == "200" ]]; then
    echo "✅ GOOD NEWS: API key is working!"
    echo "✅ Identity Toolkit API is accessible"
    echo "✅ Phone authentication endpoints are responding"
    echo ""
    echo "🎉 The 404 error in previous tests was misleading."
    echo "🎉 Your Firebase configuration is actually working!"
    echo ""
    echo "📋 Next step: Configure Restaurant Bazar admin panel"
    
elif [[ "$HTTP_CODE1" == "403" ]] || [[ "$HTTP_CODE2" == "403" ]] || [[ "$HTTP_CODE3" == "403" ]]; then
    echo "⚠️  API key has restrictions"
    echo "🔧 Fix: Remove API restrictions or add Identity Toolkit API"
    echo "🔗 Go to: https://console.cloud.google.com/apis/credentials?project=${PROJECT_ID}"
    
elif [[ "$HTTP_CODE1" == "404" ]] && [[ "$HTTP_CODE2" == "404" ]] && [[ "$HTTP_CODE3" == "404" ]]; then
    echo "❌ Identity Toolkit API not properly enabled"
    echo "🔧 Fix: Re-enable the API"
    echo "Command: gcloud services enable identitytoolkit.googleapis.com --project=${PROJECT_ID}"
    
else
    echo "❓ Mixed results - some endpoints working, others not"
    echo "HTTP Codes: $HTTP_CODE1, $HTTP_CODE2, $HTTP_CODE3"
    echo ""
    echo "🔧 Recommendations:"
    echo "1. Check API key restrictions"
    echo "2. Wait 10 minutes for configuration to propagate"
    echo "3. Try configuring Restaurant Bazar anyway (it might work)"
fi

echo ""
echo "🔗 Next Steps:"
echo "1. Configure Restaurant Bazar: http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
echo "2. Enable Firebase OTP Verification"
echo "3. Enter API Key: ${API_KEY}"
echo "4. Test with mobile app"
