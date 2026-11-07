# Galamoon Notes Service

Сервис на Bitrix для управления личными заметками с REST API

## Быстрый старт

### 1. Создание .env

```bash
cd docker
cp .env.template .env
```

Откройте `.env` и при необходимости измените переменные (настройки БД, пути, UID/GID).

### 2. Сборка и запуск

```bash
cd docker
./build.sh
```

Скрипт `build.sh` автоматически:
- Проверяет наличие `.env`
- Собирает и запускает Docker-контейнеры
- Устанавливает Composer-зависимости
- Восстанавливает базу данных из последнего бекапа в `docker/backups/`

### 3. Вход в систему

| Параметр | Значение |
|----------|----------|
| URL | http://localhost (HTTP) или https://localhost (HTTPS) |
| Логин | `bxadmin` |
| Пароль | `bxadmin` |

Сайт защищён — неавторизованные пользователи перенаправляются на страницу авторизации.

## Возможности

- CRUD-операции для заметок (создание, чтение, обновление, удаление)
- REST API с OpenAPI/Swagger документацией
- XSS-защита на уровне санитизации входных данных
- Пагинация списка заметок
- Мягкое удаление (soft delete)

## Стек

- **PHP** 8.3+
- **Bitrix Framework** — ORM, кэширование, авторизация
- **OpenAPI 3.0** — генерация Swagger-документации через `zircote/swagger-php`
- **PHPUnit 11** — юнит-тесты
- **Mockery** — моки для тестирования

## Структура

```
galamoon.notes/
├── lib/
│   ├── Controller/
│   │   └── NoteController.php      # REST API контроллер
│   ├── Services/
│   │   └── NoteService.php         # Бизнес-логика
│   ├── Dto/
│   │   └── NoteDto.php             # DTO для ответов API
│   ├── Orm/
│   │   └── NoteTable.php           # Bitrix ORM-таблица
│   └── Helpers/
│       └── Sanitizer.php           # XSS-санитизация
├── tests/
│   ├── bootstrap.php               # Bootstrap для PHPUnit
│   ├── Unit/
│   │   ├── SanitizerTest.php       # Тесты санитизации (14 тестов)
│   │   ├── NoteDtoTest.php         # Тесты DTO (5 тестов)
│   │   └── NoteServiceTest.php     # Тесты сервиса (10 тестов)
│   └── phpunit.xml.dist            # Конфигурация PHPUnit
├── swagger/
│   └── swagger.json                # Сгенерированная OpenAPI-документация
├── cli/
│   └── swagger.php                 # CLI-скрипт для генерации Swagger
├── composer.json
└── README.md
```

## REST API

| Метод | Endpoint | Описание |
|-------|----------|----------|
| `GET` | `/api/v1/notes` | Список активных заметок (limit, page) |
| `GET` | `/api/v1/notes/{id}` | Получить заметку по ID |
| `POST` | `/api/v1/notes` | Создать заметку (`title`, `text`) |
| `PUT` | `/api/v1/notes/{id}` | Обновить заметку |
| `DELETE` | `/api/v1/notes/{id}` | Мягко удалить заметку |

### Пример запроса

```bash
curl -X POST https://localhost/api/v1/notes \
  -H "Content-Type: application/json" \
  -d '{"title": "Meeting notes", "text": "Discuss project timeline"}'
```

## Swagger / OpenAPI

Документация генерируется из аннотаций в `NoteController` через `swagger-php`.

```bash
# Генерация swagger.json
docker compose -f docker-compose.yml exec php-fpm php \
  /var/www/html/local/modules/galamoon.notes/cli/swagger.php generate
```

Результат: `swagger/swagger.json`

## Тесты

Запуск из контейнера PHP-FPM:

```bash
docker compose -f docker-compose.yml exec php-fpm php \
  /var/www/html/local/modules/galamoon.notes/vendor/bin/phpunit \
  --bootstrap /var/www/html/local/modules/galamoon.notes/tests/bootstrap.php \
  /var/www/html/local/modules/galamoon.notes
```

### Результаты

| Тестовый файл | Тестов | Assertions |
|---------------|--------|------------|
| `SanitizerTest.php` | 14 | 31 |
| `NoteDtoTest.php` | 5 | 10 |
| `NoteServiceTest.php` | 10 | 21 |
| **Итого** | **29** | **62** |

### Что тестируется

- **Sanitizer** — XSS-защита, двойное кодирование, null-байты, Unicode, спецсимволы
- **NoteDto** — форматирование DB-строк в API-ответы, конвертация типов
- **NoteService** — валидация входных данных, пагинация, soft delete, контроль доступа

## Установка

```bash
# Установка зависимостей
composer install

# Генерация Swagger
php cli/swagger.php generate

# Запуск тестов
./vendor/bin/phpunit --bootstrap tests/bootstrap.php .
```
