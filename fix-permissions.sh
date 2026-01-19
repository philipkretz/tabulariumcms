#!/bin/bash

# TabulariumCMS - Fix File Permissions for Docker
# This script fixes file permission issues that prevent Docker containers from reading files

echo "Fixing file permissions for TabulariumCMS..."

# Fix ownership - change from Docker user (82) to current user
echo "1. Fixing file ownership..."
sudo chown -R $(whoami):$(whoami) templates/ config/ src/ public/

# Fix template files
echo "2. Fixing template file permissions..."
find templates -type f -exec chmod 644 {} \;
find templates -type d -exec chmod 755 {} \;

# Fix config files
echo "3. Fixing config file permissions..."
find config -type f -exec chmod 644 {} \;
find config -type d -exec chmod 755 {} \;

# Fix source files
echo "4. Fixing source file permissions..."
find src -type f -exec chmod 644 {} \;
find src -type d -exec chmod 755 {} \;

# Fix public files (but keep executables)
echo "5. Fixing public file permissions..."
find public -type f -name "*.php" -exec chmod 644 {} \;
find public -type f -name "*.js" -exec chmod 644 {} \;
find public -type f -name "*.css" -exec chmod 644 {} \;
find public -type f -name "*.html" -exec chmod 644 {} \;
find public -type d -exec chmod 755 {} \;

# Fix var directory (cache, logs)
echo "6. Fixing var directory permissions..."
chmod -R 775 var/cache var/log 2>/dev/null || true

# Fix bin/console
echo "7. Making bin/console executable..."
chmod +x bin/console

# Fix Admin files with Docker ownership
echo "8. Fixing Admin files ownership and permissions..."
sudo chown -R $(whoami):$(whoami) src/Admin/ 2>/dev/null || echo "   Could not change Admin file ownership (may need sudo)"
find src/Admin -type f -exec chmod 644 {} \; 2>/dev/null || true

# Summary
echo ""
echo "Permission fix complete!"
echo ""
echo "Files that were changed:"
echo "  - Templates: $(find templates -type f | wc -l) files"
echo "  - Config: $(find config -type f | wc -l) files"
echo "  - Source: $(find src -type f | wc -l) files"
echo ""
echo "Next steps:"
echo "  1. Restart Docker containers: docker-compose restart"
echo "  2. Clear cache: docker-compose exec php bin/console cache:clear"
echo ""
