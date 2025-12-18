#!/bin/bash

# ==============================================
# Laravel Docker Deployment Script
# ==============================================

set -e  # Exit on any error

echo "🚀 Starting deployment..."

# 1. Pull latest code from git
echo "📥 Pulling latest changes from git..."
git pull origin main  # Change 'main' to your branch name if different

# 2. Stop and remove old containers
echo "🛑 Stopping existing containers..."
docker-compose down

# 3. Rebuild Docker images (if Dockerfile changed)
echo "🔨 Building Docker images..."
docker-compose build --no-cache

# 4. Start containers in detached mode
echo "▶️  Starting containers..."
docker-compose up -d

# 5. Install/Update Composer dependencies
echo "📦 Installing Composer dependencies..."
docker-compose exec -T app composer install --optimize-autoloader --no-dev

# 6. Run database migrations
echo "🗄️  Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# 7. Clear and rebuild caches
echo "🧹 Clearing and rebuilding caches..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear
docker-compose exec -T app php artisan cache:clear

docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# 8. Generate optimized autoload files
echo "⚡ Optimizing autoload..."
docker-compose exec -T app composer dump-autoload --optimize

# 9. Set proper permissions
echo "🔐 Setting storage permissions..."
docker-compose exec -T app chmod -R 775 storage bootstrap/cache
docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache

# 10. Restart queue workers (if using)
echo "🔄 Restarting queue workers..."
docker-compose exec -T app php artisan queue:restart || true

# 11. Check container status
echo "✅ Checking container status..."
docker-compose ps

echo "🎉 Deployment completed successfully!"
echo "📊 View logs: docker-compose logs -f app"
