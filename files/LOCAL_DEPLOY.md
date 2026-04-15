# LOCAL_DEPLOY.md

Локальне розгортання NoName LMS через Docker.

---

## Швидкий старт (TL;DR)

На чистій машині з Docker:

```bash
git clone https://github.com/Gdymora/noname_lms.git
cd noname_lms
cp .env.dev.example .env
chmod +x dev_deploy.sh
./dev_deploy.sh
```

Через 10–15 хвилин:
- Сайт: http://localhost
- Адмінка: http://localhost/admin (`admin@local` / `password`)
- Пошта (Mailpit): http://localhost:8025

---

## Вимоги

- Docker (+ Docker Compose v2, входить у сучасні дистрибутиви Docker Desktop / Docker Engine)
- Вільно ~5 GB на диску (образи + volumes)
- Вільні порти: `80`, `5173`, `5432`, `6379`, `1025`, `8025`

Перевірка Docker:
```bash
docker --version
docker compose version
```

---

## Архітектура

| Сервіс | Образ | Призначення |
|---|---|---|
| `laravel.test` | `sail-8.4/app` (локальний build з `docker/8.4`) | PHP + `artisan serve` |
| `queue` | `sail-8.4/app` | Воркер черг (`queue:work`) |
| `pgsql` | `postgres:18-alpine` | База даних |
| `redis` | `redis:alpine` | Кеш + сесії |
| `mailpit` | `axllent/mailpit` | SMTP-перехоплювач |

---

## Файли в репозиторії

| Файл | Що робить |
|---|---|
| `compose.yaml` | Основний compose-файл (dev) — PHP 8.4 + всі сервіси |
| `.env.dev.example` | Приклад локального `.env` (копіювати в `.env`) |
| `dev_deploy.sh` | Скрипт розгортання (install / update / fresh / down / logs) |
| `docker_cleanup.sh` | Скрипт очистки Docker (коли сміття забиває диск) |
| `docker/8.4/Dockerfile` | Образ PHP (модифікований від Sail) |
| `docker/8.4/Dockerfile.original` | Оригінал від Sail (для порівняння) |
| `DOCKER_DIAGNOSTICS.md` | Довідник команд для дебагу Docker |

**Не використовуємо** (це для бойового серверу):
- `docker-compose.prod.yml`
- `deploy.sh`

---

## Команди скрипта

```bash
./dev_deploy.sh          # повна установка (перший раз)
./dev_deploy.sh update   # після git pull (швидко, без перевстановки залежностей)
./dev_deploy.sh fresh    # знести БД і Redis, почати з чистого листа
./dev_deploy.sh down     # зупинити контейнери (дані залишаються в volumes)
./dev_deploy.sh logs     # показати логи всіх сервісів
```

---

## Що робить скрипт

1. **Перевіряє оточення**: Docker встановлено, є compose.yaml, є .env, є Dockerfile
2. **Валідує .env**: що `APP_ENV=local`, не `production`; що `APP_URL=http://`; що `DB_HOST=pgsql`, `REDIS_HOST=redis`
3. **Composer install** через одноразовий контейнер (якщо `vendor/` відсутня)
4. **Build і старт контейнерів** з `DOCKER_BUILDKIT=0` (щоб бачити помилки білду)
5. **Чекає PostgreSQL** до 60 секунд з повторними перевірками (не просто `pg_isready`, а реальний запит)
6. **APP_KEY** генерує, якщо порожній
7. **Міграції** + додаткова перевірка, що таблиця `jobs` створилась
8. **Storage link** для публічних файлів
9. **Ролі + суперадмін** `admin@local` / `password` (тільки якщо їх ще нема)
10. **npm install** + публікація Filament assets + `npm run build`
11. **Очистка кешу** + перезапуск queue worker

---

## Типові проблеми і рішення

### Browser перенаправляє на https://localhost

Chrome запам'ятав попередній https-візит (HSTS).

**Фікс:** `chrome://net-internals/#hsts` → у секції **Delete domain security policies** → введіть `localhost` → **Delete**.

Альтернатива — використовувати інший порт:
```bash
sed -i 's/^APP_PORT=80$/APP_PORT=8080/' .env
sed -i 's|^APP_URL=http://localhost$|APP_URL=http://localhost:8080|' .env
./dev_deploy.sh update
```

### Error: password authentication failed

Старий volume PostgreSQL з іншим паролем.

**Фікс:**
```bash
docker compose down
docker volume rm noname_lms_sail-pgsql
./dev_deploy.sh fresh
```

### 500 Internal Server Error при заході на / або /admin

Ймовірно не запустились міграції, нема таблиці.

**Фікс:** переглянути `storage/logs/laravel.log`:
```bash
docker compose exec laravel.test tail -50 storage/logs/laravel.log
```

Якщо видно `relation "X" does not exist` — виконати міграції:
```bash
docker compose exec laravel.test php artisan migrate --force
```

### Стилі Filament не підтягуються

```bash
docker compose exec laravel.test php artisan filament:assets
docker compose exec laravel.test npm run build
docker compose exec laravel.test php artisan optimize:clear
```

Ctrl+F5 у браузері.

### Порт 80 зайнятий

Подивитись, хто слухає:
```bash
sudo ss -tlnp | grep :80
```

Змінити порт:
```bash
sed -i 's/^APP_PORT=80$/APP_PORT=8080/' .env
sed -i 's|^APP_URL=http://localhost$|APP_URL=http://localhost:8080|' .env
docker compose down
./dev_deploy.sh update
```

### Білд падає з `exit code: 1`

BuildKit ковтає деталі помилки. Запустити без нього:
```bash
DOCKER_BUILDKIT=0 docker compose build laravel.test 2>&1 | tee build.log
tail -100 build.log
```

У лозі буде конкретна причина (apt-пакет не знайдено / npm-помилка / timeout).

---

## Журнал фіксів при першому розгортанні (квітень 2026)

Документ пережив ~6 ітерацій — записую, які граблі вже зібрано.

### #1 — Два compose-файли одночасно
Підклали `docker-compose.yml` поруч з існуючим `compose.yaml`. Docker взяв не той. **Фікс:** залишили тільки `compose.yaml`.

### #2 — Неправильна назва `.env` файлу
Файл завантажився як `env.local` (без крапки). **Фікс:** `mv env.local .env`.

### #3 — Білд падає на `npm install -g npm`
NodeSource Node.js 22 має вбудований npm. Команда `npm install -g npm` ламається посеред переписування самої себе (помилка `Cannot find module 'promise-retry'`). **Фікс:** прибрали `npm install -g npm` з Dockerfile.

### #4 — Sail-образ роздутий
Оригінальний Sail Dockerfile встановлює купу непотрібного: MongoDB, Swoole, Memcached, Imagick, LDAP, IMAP, Playwright, Bun, Yarn, pnpm. **Фікс:** полегшена версія Dockerfile, яка ставить тільки потрібне для цього проекту (PHP + pgsql/redis/gd + Composer + Node 22 + postgres-client). Розмір образу: 1.5 GB замість 3-4 GB.

### #5 — Один гігантський RUN — чорна скринька для дебагу
В оригіналі 40+ команд в одному RUN. Коли впадає на 47-му рядку, Docker викидає весь шар, і не видно де конкретно. **Фікс:** розбили на 4 логічні RUN (PHP / Composer / Node / Postgres).

### #6 — Міграції не встигали до запуску додатка
`depends_on` + `pg_isready` не гарантують, що БД готова приймати реальні запити. Міграції падали з `authentication failed`, додаток теж падав. **Фікс:** функція `wait_for_postgres()` робить і `pg_isready`, і реальний `SELECT 1`, з 60 спробами.

### #7 — Старий prod-ний .env з `APP_URL=https://domain`
Браузер отримував посилання з https і падав з `ERR_CONNECTION_REFUSED`. **Фікс:** `ensure_env_is_local()` у скрипті — автоматично виправляє `https://` → `http://`, блокує запуск якщо `APP_ENV=production`.

### #8 — Chrome HSTS тримав `localhost` на https
Навіть після виправлення .env, браузер переписував URL. **Фікс:** інструкція з очистки в `chrome://net-internals/#hsts`.

### #9 — Задавнений пароль PostgreSQL у volume
Postgres зберіг старий пароль у volume, `.env` оновили на новий → `authentication failed`. **Фікс:** або `fresh` режим скрипта (видаляє volume), або `ALTER USER ... WITH PASSWORD`.

### #10 — Queue worker падав по колу
Таблиця `jobs` не була в міграціях на момент запуску → worker сипав помилки кожну секунду. **Фікс:** скрипт перевіряє наявність `jobs` після міграцій і до-генерує при потребі; в кінці робить `docker compose restart queue`.

### #11 — Кешований prod-конфіг у `bootstrap/cache/config.php`
Laravel тримав старий config з prod-паролем навіть після зміни .env. **Фікс:** скрипт у кінці видаляє `bootstrap/cache/*.php` явно.

---

## Корисні команди

Повний довідник — у `DOCKER_DIAGNOSTICS.md`. Щоденні:

```bash
# Статус
docker compose ps
docker compose logs -f laravel.test

# В контейнер
docker compose exec laravel.test bash
docker compose exec pgsql psql -U lms_user -d lms_local_db

# Artisan
docker compose exec laravel.test php artisan migrate:status
docker compose exec laravel.test php artisan tinker
docker compose exec laravel.test php artisan optimize:clear

# Логи Laravel
docker compose exec laravel.test tail -f storage/logs/laravel.log

# Білд з живим логом (якщо впаде)
DOCKER_BUILDKIT=0 docker compose build laravel.test 2>&1 | tee build.log
tail -50 build.log
```

---

## Backup / Restore БД

```bash
# Restore з файлу в репо
docker compose exec -T pgsql psql -U lms_user -d lms_local_db < backup.sql

# Backup
docker compose exec -T pgsql pg_dump -U lms_user lms_local_db > backup_$(date +%Y%m%d).sql
```

---

## Перенесення на іншу машину

1. **Встановити Docker + Docker Compose**
2. `git clone https://github.com/Gdymora/noname_lms.git`
3. `cd noname_lms`
4. `cp .env.dev.example .env`
5. `chmod +x dev_deploy.sh docker_cleanup.sh`
6. `./dev_deploy.sh`

Все. Скрипт сам підніме 5 контейнерів, зробить міграції, створить адміна, збере фронт.

Якщо хочете перенести **дані** (курси, користувачів):

На старій машині:
```bash
docker compose exec -T pgsql pg_dump -U lms_user lms_local_db > backup.sql
tar czf storage.tgz storage/app/public
```

На новій (після `./dev_deploy.sh`):
```bash
docker compose exec -T pgsql psql -U lms_user -d lms_local_db < backup.sql
tar xzf storage.tgz
```
