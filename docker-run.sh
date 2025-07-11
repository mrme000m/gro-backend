#!/bin/bash

# GroFresh Docker Management Script

case "$1" in
    "start")
        echo "Starting GroFresh application..."
        docker-compose up -d
        echo "Application started!"
        echo "Web App: http://localhost:8000"
        echo "phpMyAdmin: http://localhost:8080"
        ;;
    "stop")
        echo "Stopping GroFresh application..."
        docker-compose down
        echo "Application stopped!"
        ;;
    "restart")
        echo "Restarting GroFresh application..."
        docker-compose down
        docker-compose up -d
        echo "Application restarted!"
        ;;
    "build")
        echo "Building GroFresh application..."
        docker-compose build --no-cache
        echo "Build completed!"
        ;;
    "logs")
        echo "Showing application logs..."
        docker-compose logs -f app
        ;;
    "shell")
        echo "Opening shell in app container..."
        docker-compose exec app bash
        ;;
    "mysql")
        echo "Opening MySQL shell..."
        docker-compose exec mysql mysql -u grofresh_user -p grofresh
        ;;
    "clean")
        echo "Cleaning up Docker resources..."
        docker-compose down -v
        docker system prune -f
        echo "Cleanup completed!"
        ;;
    *)
        echo "GroFresh Docker Management"
        echo "Usage: $0 {start|stop|restart|build|logs|shell|mysql|clean}"
        echo ""
        echo "Commands:"
        echo "  start   - Start the application"
        echo "  stop    - Stop the application"
        echo "  restart - Restart the application"
        echo "  build   - Build the Docker images"
        echo "  logs    - Show application logs"
        echo "  shell   - Open shell in app container"
        echo "  mysql   - Open MySQL shell"
        echo "  clean   - Clean up Docker resources"
        ;;
esac
