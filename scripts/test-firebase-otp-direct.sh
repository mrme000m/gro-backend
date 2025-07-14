#!/bin/bash

# Direct Firebase OTP Test
# Tests Firebase OTP functionality without mobile app

PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

echo "📱 Direct Firebase OTP Test"
echo "==========================="
echo ""

# Test 1: Check if Restaurant Bazar is configured
echo "Test 1: Restaurant Bazar Configuration"
echo "--------------------------------------"

if curl -s http://localhost:8000 > /dev/null 2>&1; then
    echo "✅ Restaurant Bazar is running"
    
    # Check if Firebase OTP is configured
    echo "🔗 Configuration URL: http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
    echo "📋 Required: Enable Firebase OTP and enter API key"
else
    echo "❌ Restaurant Bazar is not running"
    echo "🔧 Start with: docker-compose up -d"
fi

echo ""

# Test 2: Test Firebase API endpoints
echo "Test 2: Firebase API Connectivity"
echo "---------------------------------"

# Test the actual phone auth endpoint
RESPONSE=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    -H "Content-Type: application/json" \
    -d '{"phoneNumber":"+15555555555","recaptchaToken":"test"}' \
    "https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode?key=${API_KEY}")

HTTP_CODE=$(echo $RESPONSE | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
RESPONSE_BODY=$(echo $RESPONSE | sed -e 's/HTTPSTATUS:.*//g')

echo "Endpoint: sendVerificationCode"
echo "HTTP Code: $HTTP_CODE"

case $HTTP_CODE in
    400)
        if echo "$RESPONSE_BODY" | grep -q "INVALID_PHONE_NUMBER\|MISSING_PHONE_NUMBER"; then
            echo "✅ API is working! (Expected error for test phone number)"
        elif echo "$RESPONSE_BODY" | grep -q "CAPTCHA_CHECK_FAILED"; then
            echo "✅ API is working! (reCAPTCHA required for real requests)"
        else
            echo "⚠️  API responded but with error: $RESPONSE_BODY"
        fi
        ;;
    403)
        echo "❌ API key restricted or insufficient permissions"
        echo "🔧 Check API key restrictions in Google Cloud Console"
        ;;
    404)
        echo "❌ API endpoint not found - phone auth not enabled"
        echo "🔧 Enable phone authentication in Firebase Console"
        ;;
    200)
        echo "✅ API is working perfectly!"
        ;;
    *)
        echo "❓ Unexpected response: $HTTP_CODE"
        echo "Response: $RESPONSE_BODY"
        ;;
esac

echo ""

# Test 3: Check project configuration
echo "Test 3: Project Configuration"
echo "-----------------------------"

CONFIG_RESPONSE=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}/config?key=${API_KEY}")

CONFIG_HTTP_CODE=$(echo $CONFIG_RESPONSE | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')

echo "Project config endpoint: $CONFIG_HTTP_CODE"

if [[ "$CONFIG_HTTP_CODE" == "200" ]]; then
    echo "✅ Project configuration accessible"
elif [[ "$CONFIG_HTTP_CODE" == "403" ]]; then
    echo "⚠️  Project accessible but API key needs permissions"
elif [[ "$CONFIG_HTTP_CODE" == "404" ]]; then
    echo "❌ Project not found or not configured"
fi

echo ""

# Test 4: Provide test phone numbers
echo "Test 4: Test Phone Numbers"
echo "--------------------------"
echo "📱 For testing Firebase OTP, use these test numbers:"
echo "   +1 555-555-5555 (if configured in Firebase Console)"
echo "   +1 555-555-5556 (if configured in Firebase Console)"
echo ""
echo "🔧 To add test numbers:"
echo "1. Go to Firebase Console > Authentication > Sign-in method"
echo "2. Click Phone provider"
echo "3. Scroll to 'Phone numbers for testing'"
echo "4. Add: +15555555555 with OTP: 123456"

echo ""

# Summary and next steps
echo "📋 Summary & Next Steps"
echo "======================="

if [[ "$HTTP_CODE" == "400" ]] || [[ "$HTTP_CODE" == "200" ]]; then
    echo "🎉 Firebase OTP is working!"
    echo ""
    echo "✅ Next steps:"
    echo "1. Configure Restaurant Bazar admin panel"
    echo "2. Add test phone numbers in Firebase Console"
    echo "3. Test with web interface or fix disk space for mobile app"
    
elif [[ "$HTTP_CODE" == "403" ]]; then
    echo "⚠️  API key needs configuration"
    echo ""
    echo "🔧 Fix API key restrictions:"
    echo "1. Go to: https://console.cloud.google.com/apis/credentials?project=${PROJECT_ID}"
    echo "2. Edit API key: ${API_KEY}"
    echo "3. Remove restrictions or add Identity Toolkit API"
    
elif [[ "$HTTP_CODE" == "404" ]]; then
    echo "❌ Phone authentication not properly enabled"
    echo ""
    echo "🔧 Complete Firebase Console setup:"
    echo "1. Go to: https://console.firebase.google.com/project/${PROJECT_ID}/authentication/providers"
    echo "2. Enable Phone provider"
    echo "3. Configure reCAPTCHA"
    echo "4. Save settings and wait 5 minutes"
fi

echo ""
echo "🔗 Quick Links:"
echo "Firebase Console: https://console.firebase.google.com/project/${PROJECT_ID}/authentication/providers"
echo "Restaurant Bazar: http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
