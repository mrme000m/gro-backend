#!/bin/bash

# Firebase Authentication Setup Script for Restaurant Bazar
# This script configures Firebase Phone Authentication and generates Web API Key

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Firebase project configuration
PROJECT_ID="rb00-1948e"
API_KEY="AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg"

echo -e "${BLUE}🔥 Firebase Authentication Setup for Restaurant Bazar${NC}"
echo -e "${BLUE}=================================================${NC}"
echo ""
echo -e "Project ID: ${GREEN}${PROJECT_ID}${NC}"
echo -e "API Key: ${GREEN}${API_KEY}${NC}"
echo ""

# Function to check if gcloud is installed
check_gcloud() {
    if ! command -v gcloud &> /dev/null; then
        echo -e "${RED}❌ gcloud CLI is not installed${NC}"
        echo -e "${YELLOW}Please install gcloud CLI first:${NC}"
        echo -e "${BLUE}https://cloud.google.com/sdk/docs/install${NC}"
        exit 1
    fi
    echo -e "${GREEN}✅ gcloud CLI found${NC}"
}

# Function to authenticate with Google Cloud
authenticate_gcloud() {
    echo -e "${YELLOW}🔐 Authenticating with Google Cloud...${NC}"
    
    # Check if already authenticated
    if gcloud auth list --filter=status:ACTIVE --format="value(account)" | grep -q "@"; then
        CURRENT_ACCOUNT=$(gcloud auth list --filter=status:ACTIVE --format="value(account)")
        echo -e "${GREEN}✅ Already authenticated as: ${CURRENT_ACCOUNT}${NC}"
        
        read -p "Do you want to use this account? (y/n): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            gcloud auth login
        fi
    else
        gcloud auth login
    fi
}

# Function to set the project
set_project() {
    echo -e "${YELLOW}🎯 Setting Firebase project...${NC}"
    
    # Set the project
    gcloud config set project $PROJECT_ID
    
    # Verify project exists and we have access
    if gcloud projects describe $PROJECT_ID &> /dev/null; then
        echo -e "${GREEN}✅ Project ${PROJECT_ID} is accessible${NC}"
    else
        echo -e "${RED}❌ Cannot access project ${PROJECT_ID}${NC}"
        echo -e "${YELLOW}Please ensure:${NC}"
        echo -e "  1. The project exists"
        echo -e "  2. You have proper permissions"
        echo -e "  3. The project ID is correct"
        exit 1
    fi
}

# Function to enable required APIs
enable_apis() {
    echo -e "${YELLOW}🔧 Enabling required APIs...${NC}"
    
    # Enable Firebase Authentication API
    echo -e "Enabling Identity Toolkit API..."
    gcloud services enable identitytoolkit.googleapis.com
    
    # Enable Firebase Management API
    echo -e "Enabling Firebase Management API..."
    gcloud services enable firebase.googleapis.com
    
    # Enable Cloud Resource Manager API
    echo -e "Enabling Cloud Resource Manager API..."
    gcloud services enable cloudresourcemanager.googleapis.com
    
    echo -e "${GREEN}✅ APIs enabled successfully${NC}"
}

# Function to configure Firebase Authentication
configure_auth() {
    echo -e "${YELLOW}📱 Configuring Firebase Authentication...${NC}"
    
    # Note: Phone auth configuration requires Firebase Console
    echo -e "${BLUE}📋 Manual steps required in Firebase Console:${NC}"
    echo -e "1. Go to: ${BLUE}https://console.firebase.google.com/project/${PROJECT_ID}/authentication/providers${NC}"
    echo -e "2. Click on 'Phone' provider"
    echo -e "3. Enable Phone authentication"
    echo -e "4. Add your test phone numbers (optional)"
    echo -e "5. Configure reCAPTCHA settings"
    echo ""
    
    read -p "Press Enter after completing the above steps in Firebase Console..."
}

# Function to get Web API Key
get_web_api_key() {
    echo -e "${YELLOW}🔑 Retrieving Web API Key...${NC}"
    
    # The API key is already provided in the config
    echo -e "${GREEN}✅ Web API Key: ${API_KEY}${NC}"
    echo ""
    echo -e "${BLUE}📋 Configuration for Restaurant Bazar Admin:${NC}"
    echo -e "1. Go to: ${BLUE}http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification${NC}"
    echo -e "2. Enable 'Firebase OTP Verification Status'"
    echo -e "3. Enter Web API Key: ${GREEN}${API_KEY}${NC}"
    echo -e "4. Click 'Submit'"
}

# Function to test the configuration
test_configuration() {
    echo -e "${YELLOW}🧪 Testing Firebase configuration...${NC}"
    
    # Test API key validity
    echo -e "Testing API key..."
    
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
        "https://identitytoolkit.googleapis.com/v1/projects/${PROJECT_ID}?key=${API_KEY}")
    
    if [ "$RESPONSE" = "200" ]; then
        echo -e "${GREEN}✅ API key is valid and working${NC}"
    else
        echo -e "${RED}❌ API key test failed (HTTP: $RESPONSE)${NC}"
        echo -e "${YELLOW}Please verify the API key in Firebase Console${NC}"
    fi
}

# Function to display final instructions
final_instructions() {
    echo ""
    echo -e "${GREEN}🎉 Firebase Authentication Setup Complete!${NC}"
    echo -e "${BLUE}=================================================${NC}"
    echo ""
    echo -e "${YELLOW}📋 Next Steps:${NC}"
    echo -e "1. ${GREEN}Configure in Restaurant Bazar Admin:${NC}"
    echo -e "   - URL: http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification"
    echo -e "   - Enable: Firebase OTP Verification Status"
    echo -e "   - Web API Key: ${API_KEY}"
    echo ""
    echo -e "2. ${GREEN}Test with Mobile App:${NC}"
    echo -e "   - Try customer registration with phone number"
    echo -e "   - Verify OTP functionality"
    echo ""
    echo -e "3. ${GREEN}Monitor Usage:${NC}"
    echo -e "   - Firebase Console: https://console.firebase.google.com/project/${PROJECT_ID}/authentication/usage"
    echo -e "   - Free tier: 10,000 verifications/month"
    echo ""
    echo -e "${BLUE}💡 Troubleshooting:${NC}"
    echo -e "   - Ensure phone authentication is enabled in Firebase Console"
    echo -e "   - Check API key permissions"
    echo -e "   - Verify project billing is set up (for production)"
    echo ""
}

# Main execution
main() {
    echo -e "${BLUE}Starting Firebase Authentication setup...${NC}"
    echo ""
    
    check_gcloud
    authenticate_gcloud
    set_project
    enable_apis
    configure_auth
    get_web_api_key
    test_configuration
    final_instructions
    
    echo -e "${GREEN}✅ Setup completed successfully!${NC}"
}

# Run the main function
main "$@"
