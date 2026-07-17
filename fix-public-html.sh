#!/bin/bash
# Fix: Move nepal-news-portal/* to parent directory
# Run this from public_html/nepal-news-portal/

echo "🔧 Fixing public_html structure..."
echo ""

# Check if we're in the right place
if [ ! -f "index.php" ]; then
    echo "❌ Error: Run this from public_html/nepal-news-portal/"
    echo "   cd ~/public_html/nepal-news-portal && bash ../fix-public-html.sh"
    exit 1
fi

cd ..

# Backup current public_html
echo "💾 Creating backup..."
BACKUP="backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "../$BACKUP"
cp -r . "../$BACKUP/"
echo "   Backup: ../$BACKUP"

# Move all files UP to parent
echo "📦 Moving files to public_html root..."
shopt -s dotglob
mv nepal-news-portal/* .
rm -rf nepal-news-portal

echo ""
echo "✅ Done! Files are now directly in public_html/"
echo "🌐 Visit: https://akash.bandanasigdel.com.np"
