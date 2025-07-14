#!/bin/bash

# Simple Railway entrypoint script for GroFresh Laravel application
set -e

echo "=== Starting Railway GroFresh Deployment ==="

# Configure Apache for Railway's PORT environment variable
configure_apache() {
    if [ -n "$PORT" ]; then
        echo "Configuring Apache to listen on port $PORT"
        sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
        sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-available/000-default.conf
    else
        echo "No PORT environment variable found, using default port 80"
    fi
}

# Create basic .env file if it doesn't exist
setup_basic_env() {
    if [ ! -f /var/www/html/.env ]; then
        echo "Creating basic .env file..."
        cp /var/www/html/.env.example /var/www/html/.env

        # Set basic configuration
        sed -i 's/APP_ENV=.*/APP_ENV=production/' /var/www/html/.env
        sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' /var/www/html/.env
        sed -i 's/LOG_CHANNEL=.*/LOG_CHANNEL=errorlog/' /var/www/html/.env

        # Configure database if DATABASE_URL is available
        if [ -n "$DATABASE_URL" ]; then
            echo "DATABASE_URL=$DATABASE_URL" >> /var/www/html/.env
        fi

        # Configure Redis if REDIS_URL is available
        if [ -n "$REDIS_URL" ]; then
            echo "REDIS_URL=$REDIS_URL" >> /var/www/html/.env
            sed -i 's/CACHE_DRIVER=.*/CACHE_DRIVER=redis/' /var/www/html/.env
            sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=redis/' /var/www/html/.env
        fi

        # Set Railway-specific URLs
        if [ -n "$RAILWAY_STATIC_URL" ]; then
            sed -i "s|APP_URL=.*|APP_URL=https://$RAILWAY_STATIC_URL|" /var/www/html/.env
        fi
    fi
}

# Generate application key
generate_app_key() {
    echo "Generating application key..."
    php artisan key:generate --force || echo "Warning: Could not generate app key"
}

# Set basic permissions
set_permissions() {
    echo "Setting file permissions..."
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
}

# Clear caches
clear_caches() {
    echo "Clearing Laravel caches..."
    php artisan config:clear || true
    php artisan cache:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
}

# Run migrations (with error handling)
run_migrations() {
    if [ "$RAILWAY_SERVICE_NAME" != "worker" ] && [ "$RAILWAY_SERVICE_NAME" != "cron" ]; then
        echo "Checking database configuration..."
        if [ -n "$DATABASE_URL" ]; then
            echo "DATABASE_URL found, running migrations..."
            php artisan migrate --force || echo "Warning: Migration failed, continuing..."
        else
            echo "No DATABASE_URL found. To connect database:"
            echo "1. Add MySQL/PostgreSQL service in Railway"
            echo "2. DATABASE_URL will be automatically provided"
            echo "Skipping migrations for now..."
        fi
    fi
}

# Create storage link
create_storage_link() {
    echo "Creating storage link..."
    php artisan storage:link || echo "Warning: Could not create storage link"
}

# Main execution
main() {
    echo "Starting initialization..."

    configure_apache
    setup_basic_env
    generate_app_key
    set_permissions
    clear_caches
    create_storage_link
    run_migrations

    echo "=== Initialization Complete ==="
    echo "Starting Apache server on port ${PORT:-80}..."

    # Start Apache in foreground
    exec apache2-foreground
}

# Execute main function
main "$@"
