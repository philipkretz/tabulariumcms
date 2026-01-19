# TabulariumCMS Docker Setup

## Docker Environment Setup

This Docker setup provides a complete development environment for TabulariumCMS with all necessary services.

## Quick Start

### Option 1: Web-Based Setup Wizard (Recommended)
```bash
git clone <repository-url>
cd tabulariumcms
docker-compose up -d
```

Then open your browser and navigate to:
```
http://localhost:8080/setup
```

The setup wizard will guide you through database configuration, admin account creation, and initial data loading.

### Option 2: Automated Script
```bash
git clone <repository-url>
cd tabulariumcms
chmod +x docker-setup.sh
./docker-setup.sh
```

### Option 3: Manual Setup
```bash
# Copy environment file
cp .env.docker .env.local

# Start services
docker-compose up -d --build

# Install dependencies
docker-compose exec php composer install
docker-compose run --rm node npm install
docker-compose run --rm node npm run build

# Run migrations
docker-compose exec php php bin/console doctrine:migration:migrate --no-interaction
```

## Services Included

### Core Services
- **Nginx** (Port 8080) - Web server with SSL support
- **PHP-FPM 8.2** - Application server with all required extensions
- **MariaDB 10.11** (Port 3306) - Database server
- **Redis 7** (Port 6379) - Cache and session storage

### Development Tools
- **MailHog** (Ports 1025/8025) - Email testing
- **Node.js 18** - Asset compilation and hot reload
- **PhpMyAdmin** (Port 8081) - Database management
- **Elasticsearch** (Ports 9200/9300) - Search functionality

## Configuration

### Environment Variables
Edit `.env.local` to customize:
- Database credentials
- Payment keys (Stripe/PayPal)
- Application settings

### Database Connection
```
mysql://root:tabulariumcms123@mariadb:3306/tabulariumcms
```

### Access Points
- **Main Site**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin
- **MailHog**: http://localhost:8025
- **PhpMyAdmin**: http://localhost:8081
- **Elasticsearch**: http://localhost:9200

## Development Workflow

### Asset Development
```bash
# Start asset watcher
docker-compose --profile development up -d node

# Build assets once
docker-compose run --rm node npm run build
```

### Database Operations
```bash
# Run migrations
docker-compose exec php php bin/console doctrine:migration:migrate

# Access database
docker-compose exec mariadb mysql -u root -p tabulariumcms
```

### Debugging
```bash
# View logs
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f mariadb

# Access PHP container
docker-compose exec php bash
```

## Directory Structure

```
docker/
├── nginx/
│   ├── default.conf      # Nginx configuration
│   └── logs/           # Web server logs
├── php/
│   ├── php.ini          # PHP configuration
│   └── logs/           # PHP error logs
├── mysql/
│   ├── my.cnf          # MariaDB configuration
│   └── dumps/          # Database dumps
├── redis/
│   └── redis.conf      # Redis configuration
└── ssl/               # SSL certificates
```

## Troubleshooting

### Common Issues

1. **Port conflicts**: Change ports in docker-compose.yml
2. **Permission issues**: Ensure proper file ownership
3. **Database connection**: Wait for MariaDB to fully start
4. **Asset compilation**: Run `docker-compose run --rm node npm install`

### Reset Environment
```bash
# Stop all services
docker-compose down

# Remove volumes (WARNING: deletes data)
docker-compose down -v

# Rebuild from scratch
./docker-setup.sh
```

## Production Deployment

### Production Dockerfile
```bash
# Build production image
docker build -t tabulariumcms:latest .

# Run with production settings
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Production Considerations
- Use production environment variables
- Enable HTTPS with SSL certificates
- Configure proper backup strategy
- Set up monitoring and logging
- Use secrets management for sensitive data

## Additional Features

### Xdebug Support
Enable in development by adding to PHP service:
```yaml
php:
  environment:
    - XDEBUG_MODE=develop
    - XDEBUG_CONFIG=client_host=host.docker.internal
```

### SSL Development
Generate self-signed certificates:
```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/ssl/nginx.key \
  -out docker/ssl/nginx.crt
```

### Performance Optimization
- Enable Redis for caching
- Configure Nginx gzip compression
- Use PHP OPcache settings
- Enable CDN for static assets

## Support

For Docker-specific issues:
1. Check container logs: `docker-compose logs`
2. Verify configuration files
3. Ensure all required ports are available
4. Check Docker and Docker Compose versions

---

**Ready to develop with TabulariumCMS in Docker!**
