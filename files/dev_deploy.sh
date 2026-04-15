#!/bin/bash
set -e

# =============================================================
# DEV DEPLOY — локальний запуск NoName LMS через Docker
#
# Використання:
#   ./dev_deploy.sh          # повний setup (перший раз)
#   ./dev_deploy.sh update   # оновлення після git pull
#   ./dev_deploy.sh fresh    # знести БД і почати з нуля
#   ./dev_deploy.sh down     # зупинити всі контейнери
#   ./dev_deploy.sh logs     # показати логи
# =============================================================

MODE="${1:-install}"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

log()  { echo -e "${GREEN}▶ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠ $1${NC}"; }
err()  { echo -e "${RED}✗ $1${NC}"; }
info() { echo -e "${CYAN}ℹ $1${NC}"; }

# -------------------------------------------------------------
# Допоміжні функції
# -------------------------------------------------------------

wait_for_postgres() {
    log "Очікую готовність PostgreSQL..."
    for i in {1..60}; do
        if docker compose exec -T pgsql pg_isready -U "${DB_USERNAME:-lms_user}" -d "${DB_DATABASE:-lms_local_db}" &>/dev/null; then
            if docker compose exec -T pgsql psql -U postgres -c "SELECT 1" &>/dev/null; then
                log "PostgreSQL готовий (спроба $i)"
                sleep 2
                return 0
            fi
        fi
        sleep 1
    done
    err "PostgreSQL не піднявся за 60 сек"
    docker compose logs --tail 30 pgsql
    exit 1
}

ensure_env_is_local() {
    if grep -qE "^APP_ENV=production" .env; then
        err "У .env вказано APP_ENV=production!"
        err "Це prod-конфіг. Для локалки треба APP_ENV=local"
        exit 1
    fi
    if grep -qE "^APP_URL=https://" .env; then
        warn "У .env: APP_URL=https://... — виправляю на http://"
        sed -i 's|^APP_URL=https://|APP_URL=http://|' .env
    fi
    if ! grep -qE "^DB_HOST=pgsql" .env; then
        warn "DB_HOST має бути 'pgsql', виправляю..."
        sed -i 's|^DB_HOST=.*|DB_HOST=pgsql|' .env
    fi
    if ! grep -qE "^REDIS_HOST=redis" .env; then
        warn "REDIS_HOST має бути 'redis', виправляю..."
        sed -i 's|^REDIS_HOST=.*|REDIS_HOST=redis|' .env
    fi
}

create_roles_and_admin() {
    log "Створюю ролі..."
    docker compose exec -T laravel.test php artisan tinker --execute="
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Teacher']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Student']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Manager']);
\Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Curator']);
" 2>&1 | grep -v "^$" || true

    log "Перевіряю наявність суперадміна..."
    ADMIN_COUNT=$(docker compose exec -T laravel.test php artisan tinker --execute="echo \App\Models\User::where('email', 'admin@local')->count();" 2>/dev/null | tail -1 | tr -d '[:space:]')

    if [ "$ADMIN_COUNT" = "0" ] || [ -z "$ADMIN_COUNT" ]; then
        log "Створюю суперадміна admin@local / password..."
        docker compose exec -T laravel.test php artisan tinker --execute="
\$u = \App\Models\User::create(['name'=>'Boss','email'=>'admin@local','password'=>bcrypt('password')]);
\$u->assignRole('Super Admin');
" 2>&1 | grep -v "^$" || true
    else
        info "Суперадмін admin@local вже існує"
    fi
}

# =============================================================
# DOWN / LOGS
# =============================================================
if [ "$MODE" = "down" ]; then
    log "Зупиняю контейнери..."
    docker compose down
    log "Готово."
    exit 0
fi

if [ "$MODE" = "logs" ]; then
    docker compose logs -f --tail 50
    exit 0
fi

# =============================================================
# Перевірки
# =============================================================
log "Перевірка оточення..."

if ! command -v docker &> /dev/null; then
    err "Docker не встановлено"
    exit 1
fi

if [ ! -f compose.yaml ] && [ ! -f docker-compose.yml ]; then
    err "Не знайдено compose.yaml або docker-compose.yml"
    exit 1
fi

if [ -f compose.yaml ] && [ -f docker-compose.yml ]; then
    warn "Знайдено І compose.yaml, І docker-compose.yml — Docker може взяти не той"
    warn "Рекомендую видалити docker-compose.yml (залишити compose.yaml)"
fi

if [ ! -f .env ]; then
    err "Файл .env відсутній"
    err "Створіть його з .env.dev.example або інструкції в LOCAL_DEPLOY.md"
    exit 1
fi

if [ ! -f docker/8.4/Dockerfile ]; then
    err "docker/8.4/Dockerfile не знайдено"
    exit 1
fi

ensure_env_is_local

# =============================================================
# FRESH — повний ресет
# =============================================================
if [ "$MODE" = "fresh" ]; then
    warn "FRESH MODE — буде видалено volumes БД і Redis (всі дані)!"
    read -p "Продовжити? (yes/no): " confirm
    if [ "$confirm" != "yes" ]; then
        log "Скасовано."
        exit 0
    fi
    docker compose down -v
    rm -rf bootstrap/cache/*.php 2>/dev/null || true
fi

# =============================================================
# 1. Composer install
# =============================================================
if [ ! -d vendor ] || [ "$MODE" = "install" ] || [ "$MODE" = "fresh" ]; then
    log "Composer install (через одноразовий контейнер)..."
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php84-composer:latest \
        composer install --ignore-platform-reqs
else
    info "vendor/ існує — пропускаю composer install"
fi

# =============================================================
# 2. Build і старт контейнерів (без BuildKit — щоб видно помилки)
# =============================================================
log "Запускаю контейнери (build при потребі)..."
DOCKER_BUILDKIT=0 docker compose up -d --build

# =============================================================
# 3. Чекаємо БД
# =============================================================
wait_for_postgres

# =============================================================
# 4. APP_KEY
# =============================================================
if grep -qE '^APP_KEY=$|^APP_KEY=""$' .env; then
    log "Генерую APP_KEY..."
    docker compose exec -T laravel.test php artisan key:generate --force
fi

# =============================================================
# 5. Міграції
# =============================================================
log "Міграції..."
if [ "$MODE" = "fresh" ]; then
    docker compose exec -T laravel.test php artisan migrate:fresh --force
else
    docker compose exec -T laravel.test php artisan migrate --force
fi

# Перевірка jobs-таблиці
if ! docker compose exec -T laravel.test php artisan tinker --execute="echo Schema::hasTable('jobs') ? 'ok' : 'missing';" 2>/dev/null | grep -q "ok"; then
    warn "Таблиця 'jobs' відсутня — генерую міграцію..."
    docker compose exec -T laravel.test php artisan queue:table 2>/dev/null || true
    docker compose exec -T laravel.test php artisan migrate --force
fi

# =============================================================
# 6. Storage link
# =============================================================
log "Storage link..."
docker compose exec -T laravel.test php artisan storage:link 2>/dev/null || true

# =============================================================
# 7. Ролі + адмін
# =============================================================
create_roles_and_admin

# =============================================================
# 8. Frontend
# =============================================================
if [ ! -d node_modules ] || [ "$MODE" = "install" ] || [ "$MODE" = "fresh" ]; then
    log "npm install..."
    docker compose exec -T laravel.test npm install --legacy-peer-deps
fi

log "Публікую Filament assets..."
docker compose exec -T laravel.test php artisan filament:assets 2>/dev/null || true

log "npm run build..."
docker compose exec -T laravel.test npm run build

# =============================================================
# 9. Фінальна очистка кешу
# =============================================================
log "Очищення кешу..."
docker compose exec -T laravel.test rm -f bootstrap/cache/config.php 2>/dev/null || true
docker compose exec -T laravel.test rm -f bootstrap/cache/routes-v7.php 2>/dev/null || true
docker compose exec -T laravel.test php artisan optimize:clear

log "Перезапуск queue worker..."
docker compose restart queue

# =============================================================
# Готово
# =============================================================
APP_PORT=$(grep -E "^APP_PORT=" .env | cut -d= -f2 | tr -d '"' | tr -d "'")
APP_PORT=${APP_PORT:-80}

echo ""
echo -e "${GREEN}═══════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ DEPLOY SUCCESSFUL!${NC}"
echo -e "${GREEN}═══════════════════════════════════════════${NC}"
echo ""
echo "  Сайт:          http://localhost:${APP_PORT}"
echo "  Адмінка:       http://localhost:${APP_PORT}/admin"
echo "  Пошта Mailpit: http://localhost:8025"
echo ""
if [ "$MODE" = "install" ] || [ "$MODE" = "fresh" ]; then
    echo "  Логін:  admin@local"
    echo "  Пароль: password"
    echo ""
fi
echo "  Команди:"
echo "    ./dev_deploy.sh          — повна установка"
echo "    ./dev_deploy.sh update   — оновлення після git pull"
echo "    ./dev_deploy.sh fresh    — ресет БД з нуля"
echo "    ./dev_deploy.sh down     — зупинити"
echo "    ./dev_deploy.sh logs     — показати логи"
echo ""
warn "Якщо браузер переадресовує на https://localhost — очистіть HSTS:"
warn "  chrome://net-internals/#hsts → Delete 'localhost'"
echo ""
