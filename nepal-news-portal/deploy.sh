#!/bin/bash
# Nepal News Portal Deployment Script
# Usage: ./deploy.sh

echo "📦 Nepal News Portal - Deployment Script"
echo "========================================"

# Check if public_html exists
if [ ! -d "../public_html" ]; then
    echo "❌ Error: public_html folder not found!"
    echo "   Create it in parent directory or modify DEPLOY_DIR"
    exit 1
fi

DEPLOY_DIR="../public_html"

echo "📁 Source: $(pwd)"
echo "📁 Target: $(realpath $DEPLOY_DIR)"
echo ""

# Create backup
if [ -d "$DEPLOY_DIR/assets" ]; then
    BACKUP_DIR="../backup_$(date +%Y%m%d_%H%M%S)"
    echo "💾 Creating backup at $BACKUP_DIR..."
    cp -r "$DEPLOY_DIR" "$BACKUP_DIR"
fi

# Files to deploy (exclude git, cache, etc)
echo "📤 Syncing files..."
rsync -av --exclude='.git' --exclude='.gitignore' --exclude='.htaccess.backup' \
    --exclude='node_modules' --exclude='*.log' \
    ./ "$DEPLOY_DIR/"

echo ""
echo "✅ Deployment complete!"
echo "🌐 Visit: https://akash.bandanasigdel.com.np"
echo ""
echo "⚠️  If issues, check:"
echo "   1. chmod 755 $DEPLOY_DIR/data/"
echo "   2. Check PHP error logs"
echo "   3. Clear browser cache"
