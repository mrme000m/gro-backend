#!/bin/bash

# Firebase Management Script for Restaurant Bazar
# Additional operations for Firebase Authentication management

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Firebase project configuration
PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

# Function to show usage statistics
show_usage() {
    echo -e "${YELLOW}📊 Firebase Authentication Usage${NC}"
    echo -e "${BLUE}================================${NC}"
    
    # Note: Usage stats require Firebase Admin SDK or Console access
    echo -e "${BLUE}📋 To view detailed usage:${NC}"
    echo -e "1. Go to: ${BLUE}https://console.firebase.google.com/project/${PROJECT_ID}/authentication/usage${NC}"
    echo -e "2. View monthly verification counts"
    echo -e "3. Monitor costs and billing"
    echo ""
    
    # Test API connectivity
    echo -e "${YELLOW}🔍 Testing API connectivity...${NC}"
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
        "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}?key=${API_KEY}")
    
    if [ "$RESPONSE" = "200" ]; then
        echo -e "${GREEN}✅ Firebase API is accessible${NC}"
    else
        echo -e "${RED}❌ Firebase API test failed (HTTP: $RESPONSE)${NC}"
    fi
}

# Function to configure test phone numbers
configure_test_numbers() {
    echo -e "${YELLOW}📱 Configure Test Phone Numbers${NC}"
    echo -e "${BLUE}================================${NC}"
    echo ""
    echo -e "${BLUE}📋 Manual steps in Firebase Console:${NC}"
    echo -e "1. Go to: ${BLUE}https://console.firebase.google.com/project/${PROJECT_ID}/authentication/providers${NC}"
    echo -e "2. Click on 'Phone' provider"
    echo -e "3. Scroll to 'Phone numbers for testing'"
    echo -e "4. Add test phone numbers with fixed OTP codes"
    echo ""
    echo -e "${YELLOW}💡 Recommended test numbers:${NC}"
    echo -e "   +1 555-555-5555 → OTP: 123456"
    echo -e "   +1 555-555-5556 → OTP: 654321"
    echo ""
    echo -e "${GREEN}✅ This allows testing without SMS charges${NC}"
}

# Function to check billing and quotas
check_billing() {
    echo -e "${YELLOW}💰 Firebase Billing Information${NC}"
    echo -e "${BLUE}==============================${NC}"
    echo ""
    echo -e "${GREEN}📊 Free Tier Limits:${NC}"
    echo -e "   • Phone verifications: 10,000/month"
    echo -e "   • Email verifications: Unlimited"
    echo -e "   • Social logins: Unlimited"
    echo ""
    echo -e "${YELLOW}💵 Paid Tier Pricing:${NC}"
    echo -e "   • Phone verifications: \$0.006 each"
    echo -e "   • No monthly minimums"
    echo ""
    echo -e "${BLUE}📋 To check current usage:${NC}"
    echo -e "1. Go to: ${BLUE}https://console.firebase.google.com/project/${PROJECT_ID}/usage${NC}"
    echo -e "2. View Authentication usage"
    echo -e "3. Set up billing alerts"
}

# Function to troubleshoot common issues
troubleshoot() {
    echo -e "${YELLOW}🔧 Firebase Authentication Troubleshooting${NC}"
    echo -e "${BLUE}===========================================${NC}"
    echo ""
    
    echo -e "${RED}❌ Common Issues & Solutions:${NC}"
    echo ""
    
    echo -e "${YELLOW}1. 'API key not valid' error:${NC}"
    echo -e "   • Verify API key in Firebase Console"
    echo -e "   • Check if Identity Toolkit API is enabled"
    echo -e "   • Ensure project ID matches"
    echo ""
    
    echo -e "${YELLOW}2. 'Phone authentication not enabled':${NC}"
    echo -e "   • Go to Authentication > Sign-in method"
    echo -e "   • Enable Phone provider"
    echo -e "   • Configure reCAPTCHA"
    echo ""
    
    echo -e "${YELLOW}3. SMS not received:${NC}"
    echo -e "   • Check phone number format (+country code)"
    echo -e "   • Verify phone provider is enabled"
    echo -e "   • Check Firebase quotas"
    echo -e "   • Use test numbers for development"
    echo ""
    
    echo -e "${YELLOW}4. 'Billing account required':${NC}"
    echo -e "   • Set up billing in Google Cloud Console"
    echo -e "   • Required for production usage"
    echo -e "   • Free tier still available"
    echo ""
    
    # Test current configuration
    echo -e "${BLUE}🧪 Testing current configuration...${NC}"
    
    # Test API key
    RESPONSE=$(curl -s "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}?key=${API_KEY}")
    if echo "$RESPONSE" | grep -q "error"; then
        echo -e "${RED}❌ API configuration issue detected${NC}"
        echo -e "${YELLOW}Response: $RESPONSE${NC}"
    else
        echo -e "${GREEN}✅ API key is working${NC}"
    fi
}

# Function to show Restaurant Bazar integration status
check_integration() {
    echo -e "${YELLOW}🔗 Restaurant Bazar Integration Status${NC}"
    echo -e "${BLUE}====================================${NC}"
    echo ""
    
    # Check if Restaurant Bazar is running
    if curl -s http://localhost:8000 > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Restaurant Bazar is running${NC}"
        
        # Check Firebase OTP configuration endpoint
        echo -e "${BLUE}📋 Configuration URL:${NC}"
        echo -e "   http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
        echo ""
        
        echo -e "${YELLOW}🔧 Required Configuration:${NC}"
        echo -e "   • Enable: Firebase OTP Verification Status"
        echo -e "   • Web API Key: ${API_KEY}"
        echo ""
        
    else
        echo -e "${RED}❌ Restaurant Bazar is not running${NC}"
        echo -e "${YELLOW}Start with: docker-compose up -d${NC}"
    fi
    
    echo -e "${BLUE}📱 Mobile App Configuration:${NC}"
    echo -e "   • Ensure Firebase config is in mobile app"
    echo -e "   • Test phone authentication flow"
    echo -e "   • Verify OTP delivery"
}

# Function to show menu
show_menu() {
    echo -e "${BLUE}🔥 Firebase Management Menu${NC}"
    echo -e "${BLUE}===========================${NC}"
    echo ""
    echo -e "1. ${GREEN}Show Usage Statistics${NC}"
    echo -e "2. ${GREEN}Configure Test Phone Numbers${NC}"
    echo -e "3. ${GREEN}Check Billing & Quotas${NC}"
    echo -e "4. ${GREEN}Troubleshoot Issues${NC}"
    echo -e "5. ${GREEN}Check Restaurant Bazar Integration${NC}"
    echo -e "6. ${GREEN}Exit${NC}"
    echo ""
}

# Main menu loop
main() {
    while true; do
        show_menu
        read -p "Select an option (1-6): " choice
        echo ""
        
        case $choice in
            1)
                show_usage
                ;;
            2)
                configure_test_numbers
                ;;
            3)
                check_billing
                ;;
            4)
                troubleshoot
                ;;
            5)
                check_integration
                ;;
            6)
                echo -e "${GREEN}👋 Goodbye!${NC}"
                exit 0
                ;;
            *)
                echo -e "${RED}❌ Invalid option. Please select 1-6.${NC}"
                ;;
        esac
        
        echo ""
        read -p "Press Enter to continue..."
        echo ""
    done
}

# Run the main function
main "$@"
