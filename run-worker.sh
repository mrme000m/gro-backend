#!/bin/bash
# Railway worker script for GroFresh Laravel application
# Make sure this file has executable permissions: chmod +x run-worker.sh

set -e

echo "=== Starting GroFresh Queue Worker ==="

# Wait a bit for the main app to initialize
sleep 10

# Run Laravel queue worker
# Using --tries=3 for automatic retry on failed jobs
# Using --timeout=60 to prevent long-running jobs from hanging
# Using --sleep=3 to reduce CPU usage when no jobs are available
php artisan queue:work --tries=3 --timeout=60 --sleep=3 --verbose
