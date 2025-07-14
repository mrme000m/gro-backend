# Railway Deployment Guide for GroFresh (Restaurant Bazar)

This guide provides step-by-step instructions for deploying the GroFresh Laravel application to Railway.com.

## Prerequisites

1. **Railway Account**: Sign up at [railway.com](https://railway.com)
2. **GitHub Repository**: Your GroFresh code should be in a GitHub repository
3. **Railway CLI** (optional): Install from [docs.railway.com/cli](https://docs.railway.com/cli)

## Deployment Architecture

The GroFresh application follows a "majestic monolith" architecture on Railway:

- **App Service**: Main Laravel application (handles HTTP requests)
- **Worker Service**: Background job processing (queue workers)
- **Cron Service**: Scheduled tasks (Laravel scheduler)
- **Database Service**: MySQL or PostgreSQL database
- **Redis Service**: Cache and session storage

## Quick Deployment Steps

### 1. Create Railway Project

1. Go to [railway.com/new](https://railway.com/new)
2. Click "Deploy from GitHub repo"
3. Select your GroFresh repository
4. Railway will create a new project

### 2. Add Database Service

1. In your Railway project dashboard, click "Add Service"
2. Choose "Database" → "Add MySQL" (or PostgreSQL)
3. Railway will provision a database and provide connection details

### 3. Add Redis Service

1. Click "Add Service" → "Database" → "Add Redis"
2. Railway will provision Redis for caching and sessions

### 4. Configure App Service

1. Click on your app service
2. Go to "Settings" tab
3. Configure the following:

**Build Settings:**
- Builder: Dockerfile
- Dockerfile Path: `Dockerfile.railway` (optimized for Railway)
- Build Command: (leave empty, handled by Dockerfile)
- Pre-Deploy Command: `php artisan migrate --force`

**Deploy Settings:**
- Start Command: (leave empty, uses Dockerfile)
- Health Check Path: `/`
- Health Check Timeout: `300`

**Note:** We use `Dockerfile.railway` which is optimized for Railway's build environment and avoids multi-stage build issues.

### 5. Set Environment Variables

Go to the "Variables" tab and add these variables:

```env
# Application
APP_NAME=Restaurant Bazar
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_MODE=live

# Database (Railway provides DATABASE_URL automatically)
DB_CONNECTION=mysql

# Cache & Sessions
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Logging
LOG_CHANNEL=errorlog

# File Storage (configure with your cloud storage)
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name

# Mail (Resend configuration)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=your_resend_api_key
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@4restaurants.store
MAIL_FROM_NAME="Restaurant Bazar"

# Google Maps
GOOGLE_MAPS_API_KEY=AIzaSyB12rCZfqC-VZL9AbcTQ8HzZKktlLDR15o

# Firebase
FIREBASE_PROJECT_ID=rb00-1948e
FIREBASE_APP_ID=1:973551472641:web:fcce7958472337860f3000
FIREBASE_STORAGE_BUCKET=rb00-1948e.firebasestorage.app
```

### 6. Create Worker Service

1. Add a new service to your project
2. Connect the same GitHub repository
3. Name it "worker"
4. In Settings:
   - Start Command: `chmod +x ./run-worker.sh && ./run-worker.sh`
   - Add the same environment variables as the app service
   - Add: `RAILWAY_SERVICE_NAME=worker`

### 7. Create Cron Service

1. Add another service to your project
2. Connect the same GitHub repository
3. Name it "cron"
4. In Settings:
   - Start Command: `chmod +x ./run-cron.sh && ./run-cron.sh`
   - Add the same environment variables as the app service
   - Add: `RAILWAY_SERVICE_NAME=cron`

### 8. Deploy

1. Click "Deploy" on each service
2. Monitor the deployment logs
3. Once deployed, generate a domain for your app service

## Environment Variables Reference

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_KEY` | Laravel application key | `base64:...` |
| `DATABASE_URL` | Auto-provided by Railway | `mysql://user:pass@host:port/db` |
| `REDIS_URL` | Auto-provided by Railway | `redis://host:port` |

### File Storage Variables

For persistent file storage, configure one of these options:

**AWS S3:**
```env
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
```

**Cloudflare R2:**
```env
FILESYSTEM_DRIVER=r2
R2_ACCESS_KEY_ID=your_key
R2_SECRET_ACCESS_KEY=your_secret
R2_BUCKET=your_bucket
R2_ENDPOINT=https://your_account.r2.cloudflarestorage.com
```

## Troubleshooting

### Common Issues

1. **Docker Build Failures**

   **Node.js/npm build errors:**
   - If you encounter npm build failures, use `Dockerfile.simple` instead
   - Update railway.json to use `"dockerfilePath": "Dockerfile.simple"`
   - This skips frontend asset compilation for backend-only deployment

   **Composer/PHP errors:**
   - Ensure composer.lock is committed to repository
   - Check PHP version compatibility (requires PHP 8.1+)
   - Verify all required PHP extensions are available

2. **Database Connection Errors**
   - Ensure DATABASE_URL is properly set
   - Check if database service is running
   - Verify database credentials

3. **File Upload Issues**
   - Configure cloud storage (S3/R2)
   - Set proper FILESYSTEM_DRIVER
   - Verify storage credentials

4. **Queue Jobs Not Processing**
   - Check if worker service is running
   - Verify QUEUE_CONNECTION=redis
   - Check Redis connection

5. **Scheduled Tasks Not Running**
   - Verify cron service is running
   - Check Laravel scheduler configuration
   - Monitor cron service logs

### Alternative Deployment Options

If the main Dockerfile fails, try these alternatives:

1. **Backend-only deployment** (recommended for API-only usage):
   ```json
   {
     "build": {
       "dockerfilePath": "Dockerfile.simple"
     }
   }
   ```

2. **Nixpacks deployment** (Railway's automatic detection):
   - Remove railway.json temporarily
   - Let Railway auto-detect and use Nixpacks
   - May require additional configuration

### Viewing Logs

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# View app logs
railway logs --service app

# View worker logs
railway logs --service worker

# View cron logs
railway logs --service cron
```

## Production Checklist

- [ ] Environment variables configured
- [ ] Database migrations run successfully
- [ ] File storage configured (S3/R2)
- [ ] SSL certificate configured
- [ ] Custom domain configured (optional)
- [ ] Email service configured
- [ ] Payment gateways configured
- [ ] Google Maps API configured
- [ ] Firebase configured
- [ ] Monitoring and alerts set up

## Support

For Railway-specific issues:
- [Railway Documentation](https://docs.railway.com)
- [Railway Discord](https://discord.gg/railway)
- [Railway GitHub](https://github.com/railwayapp/railway)

For GroFresh application issues:
- Check application logs in Railway dashboard
- Review Laravel logs via `railway logs`
- Verify environment configuration
