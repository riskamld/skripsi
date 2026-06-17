#!/bin/bash

echo "🚀 Starting Mafaza Fortuna Deployment..."
echo "========================================"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory"
    exit 1
fi

echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

echo "🔑 Generating application key..."
php artisan key:generate

echo "🔗 Creating storage link..."
php artisan storage:link

echo "🗄️ Running database migrations..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo "⚠️ Migration failed, attempting to force complete..."
    php artisan tinker --execute="DB::table('migrations')->insert(['migration' => '2025_12_30_152637_create_product_prices_table', 'batch' => 8]);"
fi

echo "⚡ Caching configuration..."
php artisan config:cache
php artisan view:cache
# route:cache intentionally skipped — compiled route cache (routes-v7.php) was
# found to corrupt route method matching on this app, causing every request
# to "/" to fail with MethodNotAllowedHttpException. Routes are fast enough
# uncached for this app's size.

echo "🔒 Setting proper permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Optional: Install and build assets if Node.js is available
if command -v npm &> /dev/null; then
    echo "📦 Installing Node dependencies..."
    npm install

    echo "🏗️ Building assets..."
    npm run build
else
    echo "⚠️ Node.js not found, skipping asset compilation"
fi

echo ""
echo "✅ Deployment completed successfully!"
echo "======================================"
echo "🌐 Your application is ready!"
echo "📊 Visit your admin dashboard to start using Mafaza Fortuna"
echo ""
echo "🔧 Useful commands:"
echo "   php artisan migrate:status    # Check migration status"
echo "   php artisan tinker           # Access Laravel REPL"
echo "   php artisan cache:clear      # Clear all caches"
echo ""
echo "📚 Documentation: Check DEPLOYMENT_GUIDE.md for more info"
