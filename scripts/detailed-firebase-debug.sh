#!/bin/bash

# Detailed Firebase Debug Script
# Comprehensive testing of Firebase configuration

PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

echo "🔍 Detailed Firebase Debug Analysis"
echo "==================================="
echo ""

# Test 1: Basic API connectivity
echo "Test 1: Basic API Connectivity"
echo "------------------------------"
RESPONSE1=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}?key=${API_KEY}")

HTTP_CODE1=$(echo $RESPONSE1 | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
RESPONSE_BODY1=$(echo $RESPONSE1 | sed -e 's/HTTPSTATUS:.*//g')

echo "URL: https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}"
echo "HTTP Code: $HTTP_CODE1"
echo "Response: $RESPONSE_BODY1"
echo ""

# Test 2: Alternative API endpoint
echo "Test 2: Alternative API Endpoint"
echo "--------------------------------"
RESPONSE2=$(curl -s -w "HTTPSTATUS:%{http_code}" \
    "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}/config?key=${API_KEY}")

HTTP_CODE2=$(echo $RESPONSE2 | tr -d '\n' | sed -e 's/.*HTTPSTATUS://')
RESPONSE_BODY2=$(echo $RESPONSE2 | sed -e 's/HTTPSTATUS:.*//g')

echo "URL: https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}/config"
echo "HTTP Code: $HTTP_CODE2"
echo "Response: $RESPONSE_BODY2"
echo ""

# Test 3: Check if APIs are enabled via gcloud
echo "Test 3: API Enablement Status"
echo "-----------------------------"
echo "Checking if Identity Toolkit API is enabled..."
API_STATUS=$(gcloud services list --enabled --filter="name:identitytoolkit.googleapis.com" --format="value(name)" --project=$PROJECT_ID 2>/dev/null)

if [[ -n "$API_STATUS" ]]; then
    echo "✅ Identity Toolkit API is enabled"
else
    echo "❌ Identity Toolkit API is NOT enabled"
    echo "🔧 Fix: gcloud services enable identitytoolkit.googleapis.com --project=$PROJECT_ID"
fi

# Test 4: Check Firebase project status
echo ""
echo "Test 4: Firebase Project Status"
echo "-------------------------------"
FIREBASE_STATUS=$(gcloud firebase projects list --filter="projectId:$PROJECT_ID" --format="value(projectId)" 2>/dev/null)

if [[ -n "$FIREBASE_STATUS" ]]; then
    echo "✅ Firebase project is accessible via gcloud"
else
    echo "❌ Firebase project not accessible via gcloud"
fi

# Test 5: API Key format validation
echo ""
echo "Test 5: API Key Validation"
echo "--------------------------"
if [[ ${#API_KEY} -eq 39 ]] && [[ $API_KEY == AIza* ]]; then
    echo "✅ API key format is correct (39 chars, starts with AIza)"
else
    echo "❌ API key format appears invalid"
    echo "Expected: 39 characters starting with 'AIza'"
    echo "Actual: ${#API_KEY} characters starting with '${API_KEY:0:4}'"
fi

# Analysis and recommendations
echo ""
echo "🔍 Analysis & Recommendations"
echo "============================="

if [[ "$HTTP_CODE1" == "404" ]] && [[ "$HTTP_CODE2" == "404" ]]; then
    echo "❌ Both API endpoints return 404"
    echo ""
    echo "🔧 Most likely causes:"
    echo "1. reCAPTCHA not configured in Firebase Console"
    echo "2. Phone authentication not fully saved"
    echo "3. API key restrictions"
    echo "4. Configuration propagation delay"
    echo ""
    echo "📋 Action items:"
    echo "1. In Firebase Console, ensure reCAPTCHA is configured"
    echo "2. Add domains: localhost, 127.0.0.1, localhost:8000"
    echo "3. Wait 5-10 minutes after saving"
    echo "4. Check API key restrictions in Google Cloud Console"
    
elif [[ "$HTTP_CODE1" == "403" ]] || [[ "$HTTP_CODE2" == "403" ]]; then
    echo "⚠️  API accessible but permission denied"
    echo "🔧 This usually means phone auth is enabled but needs final configuration"
    
elif [[ "$HTTP_CODE1" == "200" ]] || [[ "$HTTP_CODE2" == "200" ]]; then
    echo "✅ Firebase API is working correctly!"
    echo "🎉 Ready to configure Restaurant Bazar"
    
else
    echo "❓ Unexpected response codes: $HTTP_CODE1, $HTTP_CODE2"
    echo "🔧 Check network connectivity and API key"
fi

echo ""
echo "🔗 Quick Links:"
echo "Firebase Console: https://console.firebase.google.com/project/$PROJECT_ID/authentication/providers"
echo "API Keys: https://console.cloud.google.com/apis/credentials?project=$PROJECT_ID"
echo "Restaurant Bazar: http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
