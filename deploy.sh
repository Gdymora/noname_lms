#!/bin/bash
set -e

echo "🚀 STARTING DEPLOYMENT..."

# 1. Скачиваем код
git pull origin main

# 2. Устанавливаем PHP зависимости (Временный контейнер, чтобы появилась папка vendor)
# Используем тот же образ PHP, что и в проекте
echo "📦 Installing Composer Dependencies..."
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs --no-dev

# 3. Запускаем боевые контейнеры
echo "🐳 Starting Containers..."
docker compose -f docker-compose.prod.yml up -d --build

# 4. Ждем старта БД (на всякий случай)
sleep 5

# 5. Миграции и Роли
echo "🗄️ Database Migrations..."
docker compose -f docker-compose.prod.yml exec -T laravel.test php artisan migrate --force

echo "👮‍♂️ Ensuring Roles exist..."
docker compose -f docker-compose.prod.yml exec -T laravel.test php artisan tinker --execute="
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Teacher']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Student']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Manager']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Curator']);
"

# 6. Фронтенд (Фикс версий)
echo "🎨 Building Frontend..."
docker compose -f docker-compose.prod.yml exec -T laravel.test npm install --legacy-peer-deps
docker compose -f docker-compose.prod.yml exec -T laravel.test npm run build

# 7. Лечим права, ссылки и кэш
echo "🧹 Cleaning up..."
docker compose -f docker-compose.prod.yml exec -T -u root laravel.test chmod -R 777 storage bootstrap/cache
docker compose -f docker-compose.prod.yml exec -T laravel.test rm -rf public/storage
docker compose -f docker-compose.prod.yml exec -T laravel.test php artisan storage:link
docker compose -f docker-compose.prod.yml exec -T laravel.test php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec -T laravel.test php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T laravel.test php artisan view:clear

# 8. Перезапуск очередей
echo "🔄 Restarting Queues..."
docker compose -f docker-compose.prod.yml exec -T queue php artisan queue:restart

echo "✅ DEPLOY SUCCESSFUL!"