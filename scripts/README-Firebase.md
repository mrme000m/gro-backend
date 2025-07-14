# 🔥 Firebase Authentication Setup for Restaurant Bazar

This directory contains scripts to configure Firebase Phone Authentication for your Restaurant Bazar system.

## 📋 Prerequisites

1. **gcloud CLI installed**
   ```bash
   # Install gcloud CLI
   curl https://sdk.cloud.google.com | bash
   exec -l $SHELL
   ```

2. **Google Cloud Project Access**
   - Access to project: `rb00-1948e`
   - Firebase project already created
   - Proper IAM permissions

## 🚀 Quick Setup

### 1. Run the Setup Script
```bash
./scripts/setup-firebase-auth.sh
```

This script will:
- ✅ Authenticate with Google Cloud
- ✅ Set the correct project
- ✅ Enable required APIs
- ✅ Guide you through Firebase Console configuration
- ✅ Provide the Web API Key for Restaurant Bazar

### 2. Configure in Restaurant Bazar Admin
1. Go to: `http://localhost:8000/admin/business-settings/web-app/third-party/firebase-otp-verification`
2. Enable "Firebase OTP Verification Status"
3. Enter Web API Key: `AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg`
4. Click "Submit"

## 🔧 Management Operations

### Run Management Script
```bash
./scripts/firebase-management.sh
```

This provides:
- 📊 Usage statistics
- 📱 Test phone number configuration
- 💰 Billing information
- 🔧 Troubleshooting tools
- 🔗 Integration status checks

## 📱 Firebase Console Tasks

### Enable Phone Authentication
1. Go to: [Firebase Console - Authentication](https://console.firebase.google.com/project/rb00-1948e/authentication/providers)
2. Click on "Phone" provider
3. Enable phone authentication
4. Configure reCAPTCHA settings

### Add Test Phone Numbers (Optional)
1. In Phone provider settings
2. Scroll to "Phone numbers for testing"
3. Add test numbers:
   - `+1 555-555-5555` → OTP: `123456`
   - `+1 555-555-5556` → OTP: `654321`

## 💰 Pricing Information

### Free Tier
- **10,000 phone verifications/month** - FREE
- Unlimited email verifications
- Unlimited social logins

### Paid Tier
- **$0.006 per phone verification** (0.6 cents)
- No monthly minimums
- Pay only for what you use

## 🧪 Testing

### Test Phone Authentication
1. Use Restaurant Bazar mobile app
2. Try customer registration with phone number
3. Verify OTP delivery and validation
4. Check admin panel for verification logs

### Monitor Usage
- Firebase Console: [Usage Dashboard](https://console.firebase.google.com/project/rb00-1948e/authentication/usage)
- Set up billing alerts
- Monitor monthly verification counts

## 🔧 Troubleshooting

### Common Issues

#### API Key Not Valid
- Verify API key in Firebase Console
- Check if Identity Toolkit API is enabled
- Ensure project ID matches

#### Phone Authentication Not Enabled
- Go to Authentication > Sign-in method
- Enable Phone provider
- Configure reCAPTCHA

#### SMS Not Received
- Check phone number format (+country code)
- Verify phone provider is enabled
- Check Firebase quotas
- Use test numbers for development

#### Billing Account Required
- Set up billing in Google Cloud Console
- Required for production usage
- Free tier still available

## 📁 File Structure

```
scripts/
├── setup-firebase-auth.sh      # Initial setup script
├── firebase-management.sh      # Management operations
└── README-Firebase.md          # This documentation
```

## 🎯 Next Steps

1. **Run setup script**: `./scripts/setup-firebase-auth.sh`
2. **Configure in admin panel**: Enable Firebase OTP
3. **Test with mobile app**: Try phone registration
4. **Monitor usage**: Check Firebase Console
5. **Set up billing alerts**: For production use

## 📞 Support

- **Firebase Documentation**: https://firebase.google.com/docs/auth
- **Restaurant Bazar Issues**: Check application logs
- **Billing Questions**: Google Cloud Support

---

**🎉 Your Restaurant Bazar system will have secure, reliable phone authentication powered by Firebase!**
