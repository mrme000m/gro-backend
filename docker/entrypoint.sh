#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "MySQL is ready!"

# Copy .env file if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file..."
    cp /var/www/html/.env.example /var/www/html/.env
    
    # Update database configuration
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' /var/www/html/.env
    sed -i 's/DB_DATABASE=database/DB_DATABASE=grofresh/' /var/www/html/.env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=grofresh_user/' /var/www/html/.env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=grofresh_password/' /var/www/html/.env
    sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' /var/www/html/.env

    # Update URL configuration for Docker
    sed -i 's|APP_URL=http://localhost|APP_URL=http://localhost:8000|' /var/www/html/.env

    # Add ASSET_URL if not present
    if ! grep -q "ASSET_URL=" /var/www/html/.env; then
        echo "ASSET_URL=http://localhost:8000" >> /var/www/html/.env
    fi
fi

# Generate application key if not set
php artisan key:generate --force

# Clear and cache configuration
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/Modules
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/Modules

# Start Apache
apache2-foreground
