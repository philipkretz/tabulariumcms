#!/bin/bash

# TabulariumCMS Docker Setup Script

echo "Setting up TabulariumCMS with Docker..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create necessary directories
echo "Creating directories..."
mkdir -p docker/php/logs
mkdir -p docker/nginx/logs
mkdir -p docker/mysql/dumps
mkdir -p docker/ssl
mkdir -p var/log

# Copy environment file
if [ ! -f .env.local ]; then
    echo "Creating .env.local from Docker template..."
    cp .env.docker .env.local
fi

# Build and start containers
echo "Building and starting containers..."
docker compose up -d --build

# Wait for database to be ready
echo "Waiting for database to be ready..."
sleep 30

# Install Composer dependencies inside container
echo "Installing Composer dependencies..."
docker compose exec php composer install --optimize-autoloader

# Install AssetMapper dependencies
echo "Installing AssetMapper dependencies..."
docker compose exec php php bin/console importmap:install

# Generate and run database migrations
echo "Generating database migrations..."
docker compose exec php php bin/console doctrine:migrations:diff --no-interaction

echo "Running database migrations..."
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
echo "Clearing cache..."
docker compose exec php php bin/console cache:clear

# Set permissions
echo "Setting permissions..."
docker compose exec php chown -R www-data:www-data /var/www/html/var

echo "TabulariumCMS is now running!"
echo ""
echo "Access your application:"
echo "   • Main Site: http://localhost:8080"
echo "   • Admin Panel: http://localhost:8080/admin"
echo "   • MailHog (Email): http://localhost:8025"
echo ""
echo "Development Tools:"
echo "   • PhpMyAdmin: http://localhost:8081"
echo "   • Database: mariadb:3306"
echo "   • Redis: localhost:6379"
echo ""
echo "Useful Commands:"
echo "   • View logs: docker-compose logs -f [service-name]"
echo "   • Stop containers: docker-compose down"
echo "   • Rebuild containers: docker-compose up -d --build"
echo ""
echo "Default Admin:"
echo "   • Email: admin@tabulariumcms.com"
echo "   • Password: admin123"
echo ""
echo "To enable development tools:"
echo "   • Elasticsearch: docker-compose --profile search up -d"
echo "   • Node.js Watcher: docker-compose --profile development up -d node"
echo "   • PhpMyAdmin: docker-compose --profile tools up -d phpmyadmin"
