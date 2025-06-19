#!/bin/bash

# Docker commands for ACME Basket application

case "$1" in
    "build")
        echo "Building Docker images..."
        docker-compose build
        ;;
    "start")
        echo "Starting the application..."
        docker-compose up app
        ;;
    "test")
        echo "Running tests..."
        docker-compose run --rm test
        ;;
    "phpstan")
        echo "Running PHPStan static analysis..."
        docker-compose run --rm phpstan
        ;;
    "quality")
        echo "Running quality checks (PHPStan + Tests)..."
        docker-compose run --rm quality
        ;;
    "shell")
        echo "Opening shell in app container..."
        docker-compose exec app bash
        ;;
    "stop")
        echo "Stopping all containers..."
        docker-compose down
        ;;
    "clean")
        echo "Cleaning up containers and images..."
        docker-compose down --rmi all --volumes --remove-orphans
        ;;
    *)
        echo "Usage: $0 {build|start|test|phpstan|quality|shell|stop|clean}"
        echo ""
        echo "Commands:"
        echo "  build   - Build Docker images"
        echo "  start   - Start the application on port 8000"
        echo "  test    - Run PHPUnit tests"
        echo "  phpstan - Run PHPStan static analysis"
        echo "  quality - Run both PHPStan and tests"
        echo "  shell   - Open shell in app container"
        echo "  stop    - Stop all containers"
        echo "  clean   - Clean up containers and images"
        exit 1
        ;;
esac 