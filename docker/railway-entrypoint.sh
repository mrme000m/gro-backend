#!/bin/bash

# Railway-optimized entrypoint script for GroFresh Laravel application
set -e

echo "Starting Railway deployment initialization..."

# Function to wait for database connection
wait_for_database() {
    if [ -n "$DATABASE_URL" ]; then
        echo "Waiting for database connection..."
        # Extract host and port from DATABASE_URL if needed
        # Railway provides DATABASE_URL in format: mysql://user:pass@host:port/db
        # or postgresql://user:pass@host:port/db
        
        # For now, we'll rely on Laravel's database connection handling
        # and let it retry automatically
        echo "Database URL detected: $DATABASE_URL"
    else
        echo "No DATABASE_URL found, using individual DB environment variables"
    fi
}

# Create .env file if it doesn't exist
setup_environment() {
    if [ ! -f /var/www/html/.env ]; then
        echo "Creating .env file from .env.example..."
        cp /var/www/html/.env.example /var/www/html/.env
    fi
    
    # Railway-specific environment variable handling
    if [ -n "$DATABASE_URL" ]; then
        echo "Configuring database from DATABASE_URL..."
        # Laravel can handle DATABASE_URL directly in newer versions
        echo "DATABASE_URL=$DATABASE_URL" >> /var/www/html/.env
    fi
    
    # Set Railway-specific configurations
    if [ -n "$RAILWAY_ENVIRONMENT" ]; then
        sed -i "s/APP_ENV=.*/APP_ENV=$RAILWAY_ENVIRONMENT/" /var/www/html/.env
    fi
    
    # Configure logging for Railway
    sed -i 's/LOG_CHANNEL=.*/LOG_CHANNEL=errorlog/' /var/www/html/.env
    
    # Set APP_URL from Railway's provided URL
    if [ -n "$RAILWAY_STATIC_URL" ]; then
        sed -i "s|APP_URL=.*|APP_URL=https://$RAILWAY_STATIC_URL|" /var/www/html/.env
        sed -i "s|ASSET_URL=.*|ASSET_URL=https://$RAILWAY_STATIC_URL|" /var/www/html/.env
    fi
    
    # Configure Redis if available
    if [ -n "$REDIS_URL" ]; then
        echo "REDIS_URL=$REDIS_URL" >> /var/www/html/.env
        sed -i 's/CACHE_DRIVER=.*/CACHE_DRIVER=redis/' /var/www/html/.env
        sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=redis/' /var/www/html/.env
        sed -i 's/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/' /var/www/html/.env
    fi
}

# Generate application key if not set
generate_app_key() {
    echo "Generating application key..."
    php artisan key:generate --force
}

# Clear and optimize Laravel caches
optimize_laravel() {
    echo "Optimizing Laravel application..."
    
    # Clear all caches
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Create storage link if it doesn't exist
    if [ ! -L /var/www/html/public/storage ]; then
        php artisan storage:link
    fi
}

# Run database migrations
run_migrations() {
    echo "Running database migrations..."
    php artisan migrate --force
}

# Cache configurations for production
cache_for_production() {
    echo "Caching configurations for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
}

# Set proper file permissions
set_permissions() {
    echo "Setting proper file permissions..."
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache
    
    # Set permissions for Modules if they exist
    if [ -d /var/www/html/Modules ]; then
        chown -R www-data:www-data /var/www/html/Modules
        chmod -R 755 /var/www/html/Modules
    fi
}

# Configure Apache for Railway's PORT environment variable
configure_apache() {
    # Railway provides PORT environment variable
    if [ -n "$PORT" ]; then
        echo "Configuring Apache to listen on port $PORT"
        sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
        sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-available/000-default.conf
    fi
}

# Main execution
main() {
    echo "=== Railway GroFresh Deployment ==="
    
    wait_for_database
    setup_environment
    generate_app_key
    optimize_laravel
    
    # Only run migrations if we're the main app service (not worker/cron)
    if [ "$RAILWAY_SERVICE_NAME" != "worker" ] && [ "$RAILWAY_SERVICE_NAME" != "cron" ]; then
        run_migrations
    fi
    
    cache_for_production
    set_permissions
    configure_apache
    
    echo "=== Initialization Complete ==="
    echo "Starting Apache server..."
    
    # Start Apache in foreground
    apache2-foreground
}

# Execute main function
main "$@"
