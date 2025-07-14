#!/bin/bash
# Railway cron script for GroFresh Laravel application
# Make sure this file has executable permissions: chmod +x run-cron.sh

set -e

echo "=== Starting GroFresh Scheduler ==="

# Wait a bit for the main app to initialize
sleep 15

# Run Laravel scheduler every minute
# This replaces the need for system cron jobs
while true; do
    echo "Running Laravel scheduler at $(date)"
    php artisan schedule:run --verbose --no-interaction
    sleep 60
done
