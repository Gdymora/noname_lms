# 🚀 NoName LMS: Инструкция по установке (Production)

Эта инструкция описывает процесс развертывания платформы на чистом сервере (VPS) под управлением **Ubuntu 22.04 / 24.04** с использованием **Docker** и **Docker Compose** для Production среды.

## 📋 Требования

  * **OS:** Ubuntu 22.04 LTS или новее.
  * **CPU/RAM:** Минимум **2 vCPU / 2 GB RAM** (для комфортной работы Docker).
  * **Домен:** Привязанный к IP-адресу сервера (**A-запись**).

-----

## 🛠 Этап 1: Подготовка сервера

Зайдите на сервер по SSH (`ssh root@ваш-ip`) и выполните следующие команды по очереди.

### 1\. Установка Docker и Git

Мы используем официальный скрипт Docker для установки самой свежей версии.

```bash
# Обновляем списки пакетов
apt-get update

# Устанавливаем Git и Curl
apt-get install -y git curl

# Скачиваем скрипт установки Docker
curl -fsSL https://get.docker.com -o get-docker.sh

# Запускаем установку
sh get-docker.sh

# Удаляем скрипт установки
rm get-docker.sh

# Проверяем установку (должно вывести версию Docker)
docker --version
```

### 2\. Клонирование проекта

```bash
# Клонируем репозиторий (замените ссылку на вашу)
git clone https://github.com/homeonfire/lms-core.git

# Переходим в папку проекта
cd lms-core
```

> **Примечание:** Если репозиторий приватный, вам потребуется ввести логин GitHub и **Personal Access Token** вместо пароля.

-----

## ⚙️ Этап 2: Конфигурация (.env)

Создадим файл окружения с настройками для продакшена.

```bash
# Копируем пример
cp .env.example .env

# Открываем редактор
nano .env
```

ОБЯЗАТЕЛЬНО измените/добавьте следующие параметры:

```ini
APP_NAME="Название школы"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domain

# === НАСТРОЙКИ ДЛЯ DOCKER И SSL ===
# Эти переменные используются в docker-compose.prod.yml
APP_DOMAIN=domain
App_EMAIL_ADMIN=admin@domain
WWWGROUP=1000
WWWUSER=1000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# === БАЗА ДАННЫХ (PostgreSQL) ===
# Хост 'pgsql' соответствует имени сервиса в docker-compose.prod.yml
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=lms_prod_db
DB_USERNAME=lms_prod_user
DB_PASSWORD=Xy9mZ2SecureLMSPass2025

# === ДРАЙВЕРЫ И ОЧЕРЕДИ ===
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
# Очереди храним в базе (у нас создана таблица jobs)
QUEUE_CONNECTION=database

# Кэш и сессии лучше хранить в Redis для скорости
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# === REDIS ===
# Хост 'redis' соответствует имени сервиса
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# === ПОЧТА (SMTP) ===
# ВАЖНО: Сюда нужно вставить реальные данные от твоего почтового провайдера
MAIL_MAILER=smtp
MAIL_HOST=smtp.beget.com
MAIL_PORT=465
MAIL_USERNAME=info@domain
MAIL_PASSWORD=smtp_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="info@domain"
MAIL_FROM_NAME="${APP_NAME}"

# === ФРОНТЕНД ===
VITE_APP_NAME="${APP_NAME}"

APP_LOCALE=ru
APP_FALLBACK_LOCALE=ru
APP_FAKER_LOCALE=ru_RU
```

> **Сохранение:** В редакторе `nano` нажмите **Ctrl + O**, затем **Enter**, и **Ctrl + X** для выхода.

-----

## 🚀 Этап 3: Запуск установки

Мы подготовили скрипт `deploy.sh`, который автоматически собирает контейнеры, устанавливает зависимости и запускает проект.

```bash
# Даем права на запуск
chmod +x deploy.sh

# Запускаем
./deploy.sh
```

Процесс займет **3-5 минут**. Дождитесь сообщения `✅ DEPLOY SUCCESSFUL!`.

-----

## 🔧 Этап 4: Финальная настройка (Один раз)

Эти команды нужно выполнить только при самой первой установке на чистый сервер.

### 1\. Генерация ключа шифрования

Без этого сайт будет выдавать ошибку 500.

```bash
docker compose -f docker-compose.prod.yml exec laravel.test php artisan key:generate
docker compose -f docker-compose.prod.yml exec laravel.test php artisan config:clear
```

### 2\. Создание Супер-Админа

База данных чистая, нужно создать первого пользователя.

```bash
# Заходим в консоль Tinker
docker compose -f docker-compose.prod.yml exec laravel.test php artisan tinker
```

Вставьте этот код (замените `email` на свой):

```php
$u = \App\Models\User::create([
    'name' => 'Boss',
    'email' => 'i@pochta',
    'password' => bcrypt('password') // Пароль: password
]);
$u->assignRole('Super Admin');
exit
```

-----

## ✅ Готово\!

Теперь проект доступен по адресу: `https://domain`
Админка: `https://domain/admin`

  * **Логин:** `i@pochta`
  * **Пароль:** `password`

-----

## 🔄 Как обновлять проект?

Когда автор внес изменения в код и отправил их в Git, на сервере для обновления нужно выполнить всего одну команду:

```bash
cd ~/lms-core
./deploy.sh
```

<br>