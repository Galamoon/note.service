#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# ---------------------------------------------------------------
# 0. Проверка .env
# ---------------------------------------------------------------
if [[ ! -f "$SCRIPT_DIR/.env" ]]; then
    echo "❌ Ошибка: файл .env не найден в $SCRIPT_DIR"
    echo "   Скопируйте .env.template в .env и настройте переменные:"
    echo "     cp .env.template .env"
    exit 1
fi

echo "✅ .env найден"

# Загружаем переменные из .env
set -a
source "$SCRIPT_DIR/.env"
set +a

# ---------------------------------------------------------------
# 1. Сборка и запуск контейнеров
# ---------------------------------------------------------------
echo ""
echo "🔨 Сборка и запуск контейнеров..."
cd "$SCRIPT_DIR"
docker compose -f docker-compose.yml up -d --build

echo "⏳ Ожидание готовности PHP-FPM..."
for i in $(seq 1 30); do
    if docker compose -f docker-compose.yml exec -T php-fpm php -r 'exit(0);' 2>/dev/null; then
        echo "✅ PHP-FPM готов"
        break
    fi
    sleep 1
done

# ---------------------------------------------------------------
# 2. Установка зависимостей Composer
# ---------------------------------------------------------------
echo ""
echo "📦 Установка зависимостей Composer..."

# Composer установлен на хосте, volumes смонтированы — запускаем на хосте
if command -v composer &>/dev/null; then
    composer install --no-interaction --no-progress --working-dir="$PROJECT_ROOT/public/local/modules/galamoon.notes"
    echo "✅ Composer зависимости установлены (хост)"
else
    echo "⚠️  Composer не найден на хосте, пропускаем"
fi

# ---------------------------------------------------------------
# 3. Восстановление бекапа БД
# ---------------------------------------------------------------
echo ""
echo "💾 Восстановление бекапа БД..."

BACKUP_FILE=$(ls -t "$SCRIPT_DIR/backups/"*.sql 2>/dev/null | head -1)

if [[ -z "$BACKUP_FILE" || ! -f "$BACKUP_FILE" ]]; then
    echo "⚠️  Бекапы не найдены в $SCRIPT_DIR/backups/"
    echo "    Пропускаем восстановление БД"
else
    echo "   Файл: $(basename "$BACKUP_FILE")"
    docker compose -f docker-compose.yml exec -T mariadb mysql \
        -u"$DB_USER" \
        -p"$DB_PASSWORD" \
        "$DB_NAME" \
        < "$BACKUP_FILE"
    echo "✅ Бекап восстановлен"
fi

# ---------------------------------------------------------------
# Готово
# ---------------------------------------------------------------
echo ""
echo "🎉 Build завершён успешно!"
echo ""
echo "   HTTP:  http://localhost"
echo "   HTTPS: https://localhost"
