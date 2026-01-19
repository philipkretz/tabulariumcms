#!/bin/bash

# Script to fix template editing permissions
# This makes all templates editable in the admin panel

echo "==================================="
echo "Template Permissions Fix Script"
echo "==================================="
echo ""

# Step 1: Fix log file permissions
echo "Step 1: Fixing log file permissions..."
chmod -R 777 var/log/ 2>/dev/null && echo "✓ Log permissions fixed" || echo "⚠ Could not fix log permissions (may need sudo)"
echo ""

# Step 2: Clear Symfony cache
echo "Step 2: Clearing Symfony cache..."
rm -rf var/cache/* 2>/dev/null
php bin/console cache:clear --no-warmup 2>/dev/null && echo "✓ Cache cleared" || echo "⚠ Cache clear had warnings (this is usually OK)"
echo ""

# Step 3: Check database connection
echo "Step 3: Checking database connection..."
php bin/console dbal:run-sql "SELECT COUNT(*) as template_count FROM frontend_template" 2>/dev/null && echo "✓ Database connection OK" || echo "✗ Database connection failed"
echo ""

# Step 4: Show current template status
echo "Step 4: Checking current template status..."
echo "Running query to show locked templates..."
php bin/console dbal:run-sql "SELECT id, name, template_key, is_editable FROM frontend_template WHERE is_editable = 0" 2>/dev/null
echo ""

# Step 5: Update database
echo "Step 5: Making all templates editable..."
echo "Do you want to make ALL templates editable? (y/n)"
read -r response

if [[ "$response" =~ ^[Yy]$ ]]; then
    php bin/console dbal:run-sql "UPDATE frontend_template SET is_editable = 1 WHERE is_editable = 0" 2>/dev/null
    echo "✓ All templates are now editable!"
    echo ""
    echo "Verification:"
    php bin/console dbal:run-sql "SELECT COUNT(*) as editable_count FROM frontend_template WHERE is_editable = 1" 2>/dev/null
else
    echo "Skipped database update"
fi

echo ""
echo "==================================="
echo "Done! You can now edit templates at:"
echo "http://your-domain/admin/app/template/list"
echo "==================================="
