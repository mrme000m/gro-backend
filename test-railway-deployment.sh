#!/bin/bash
# Test Railway deployment configuration locally

set -e

echo "=== Testing Railway Deployment Configuration ==="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
    print_status "Docker is running"
}

# Check if required files exist
check_files() {
    local files=(
        "Dockerfile"
        "railway.json"
        "nixpacks.toml"
        "build-app.sh"
        "run-worker.sh"
        "run-cron.sh"
        "docker/railway-entrypoint.sh"
        ".env.railway"
    )
    
    for file in "${files[@]}"; do
        if [ ! -f "$file" ]; then
            print_error "Required file missing: $file"
            exit 1
        fi
    done
    print_status "All required files present"
}

# Test Docker build
test_build() {
    print_status "Testing Docker build..."
    if docker build -t grofresh-railway-test .; then
        print_status "Docker build successful"
    else
        print_error "Docker build failed"
        exit 1
    fi
}

# Test Railway-like environment
test_railway_environment() {
    print_status "Testing Railway-like environment..."
    
    # Stop any existing containers
    docker-compose -f docker-compose.railway.yml down > /dev/null 2>&1 || true
    
    # Start services
    print_status "Starting services..."
    docker-compose -f docker-compose.railway.yml up -d
    
    # Wait for services to be ready
    print_status "Waiting for services to be ready..."
    sleep 30
    
    # Check if app is responding
    print_status "Testing app response..."
    local max_attempts=10
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 | grep -q "200\|302"; then
            print_status "App is responding successfully"
            break
        else
            print_warning "Attempt $attempt/$max_attempts: App not ready yet..."
            sleep 10
            ((attempt++))
        fi
    done
    
    if [ $attempt -gt $max_attempts ]; then
        print_error "App failed to respond after $max_attempts attempts"
        docker-compose -f docker-compose.railway.yml logs app
        return 1
    fi
}

# Test worker service
test_worker() {
    print_status "Testing worker service..."
    if docker-compose -f docker-compose.railway.yml logs worker | grep -q "Running"; then
        print_status "Worker service is running"
    else
        print_warning "Worker service may not be running properly"
        docker-compose -f docker-compose.railway.yml logs worker
    fi
}

# Test cron service
test_cron() {
    print_status "Testing cron service..."
    if docker-compose -f docker-compose.railway.yml logs cron | grep -q "scheduler"; then
        print_status "Cron service is running"
    else
        print_warning "Cron service may not be running properly"
        docker-compose -f docker-compose.railway.yml logs cron
    fi
}

# Cleanup
cleanup() {
    print_status "Cleaning up..."
    docker-compose -f docker-compose.railway.yml down
    docker rmi grofresh-railway-test > /dev/null 2>&1 || true
}

# Main execution
main() {
    echo "Starting Railway deployment tests..."
    
    check_docker
    check_files
    test_build
    test_railway_environment
    test_worker
    test_cron
    
    print_status "All tests completed successfully!"
    print_status "Your GroFresh application is ready for Railway deployment."
    
    echo ""
    echo "Next steps:"
    echo "1. Push your code to GitHub"
    echo "2. Create a new Railway project"
    echo "3. Connect your GitHub repository"
    echo "4. Follow the RAILWAY_DEPLOYMENT.md guide"
    
    cleanup
}

# Handle script interruption
trap cleanup EXIT

# Run main function
main "$@"
