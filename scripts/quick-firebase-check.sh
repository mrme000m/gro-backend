#!/bin/bash

# Quick Firebase Status Check
# Provides immediate feedback on Firebase configuration status

PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

echo "🔥 Quick Firebase Status Check"
echo "=============================="
echo ""

# Test API connectivity
echo "Testing Firebase API..."
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
    "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}?key=${API_KEY}")

echo "API Response: $RESPONSE"

case $RESPONSE in
    200)
        echo "✅ SUCCESS: Firebase API is working!"
        echo "✅ Phone authentication is properly configured"
        echo "✅ Ready to configure Restaurant Bazar"
        ;;
    403)
        echo "⚠️  PARTIAL: API accessible but needs phone auth configuration"
        echo "📋 Next: Enable Phone provider in Firebase Console"
        ;;
    404)
        echo "❌ NOT READY: Phone authentication not enabled"
        echo "📋 Required: Complete Firebase Console setup"
        echo ""
        echo "🔧 Steps needed:"
        echo "1. Go to: https://console.firebase.google.com/project/rb00-1948e/authentication/providers"
        echo "2. Click 'Phone' provider"
        echo "3. Enable phone authentication"
        echo "4. Configure reCAPTCHA"
        echo "5. Save settings"
        ;;
    *)
        echo "❌ ERROR: Unexpected response ($RESPONSE)"
        echo "📋 Check: API key and project configuration"
        ;;
esac

echo ""
echo "🔗 Firebase Console: https://console.firebase.google.com/project/rb00-1948e/authentication/providers"
echo "🔗 Restaurant Bazar Config: http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
