# Call Center Operator

Гибридное приложение (Web + Android APK) для операторов поддержки. Оператор получает сообщения от пользователей через Telegram-бота и отвечает им в реальном времени через веб-интерфейс или мобильное приложение.

Real-time обновления через WebSocket (Laravel Reverb). Авторизация через Bearer-токен (Sanctum). Android APK собирается с Capacitor 8.

---

## Stack

| Слой | Технология |
|------|-----------|
| Backend | PHP 8.2, Laravel 12, MySQL 8 |
| Real-time | Laravel Reverb (WebSocket, порт 8080) |
| Telegram | Nutgram + long polling |
| Auth | Laravel Sanctum — Bearer token |
| Frontend | Vue 3, Pinia, Vite, TypeScript, Tailwind CSS v4 |
| Mobile | Capacitor 8 → Android APK |
| Infra | Docker Compose (5 сервисов) |

---

## Быстрый старт

### 1. Переменные окружения

```bash
cp .env.example .env
```

Заполнить в `.env`:
```
TELEGRAM_TOKEN=       # токен из @BotFather
REVERB_APP_ID=        # openssl rand -hex 8
REVERB_APP_KEY=       # openssl rand -hex 16
REVERB_APP_SECRET=    # openssl rand -hex 16
DB_PASSWORD=          # любой пароль
DB_ROOT_PASSWORD=     # любой пароль
```

### 2. Запуск backend

```bash
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Проверка: `curl http://localhost:8000/up` → `{"status":"up"}`

### 3. Frontend (dev)

```bash
cd frontend
npm install
npm run dev -- --host 0.0.0.0
# http://localhost:5173
```

---

## Android сборка

```bash
cd frontend

# 1. Собрать dist
npm run build

# 2. Синхронизировать в Android-проект
npx cap sync android

# 3. Собрать APK
cd android
./gradlew assembleDebug
# APK: android/app/build/outputs/apk/debug/app-debug.apk

# 4. Установить на устройство
adb install app/build/outputs/apk/debug/app-debug.apk
```

> Перед сборкой под Android замени `localhost` на LAN-IP хоста в `frontend/.env` и `frontend/capacitor.config.ts`.

---

## Переменные окружения

### Корневой `.env` (Docker Compose)

| Переменная | Описание |
|-----------|---------|
| `TELEGRAM_TOKEN` | Токен бота из @BotFather |
| `REVERB_APP_ID` | ID приложения Reverb (hex 8) |
| `REVERB_APP_KEY` | Ключ Reverb (hex 16) |
| `REVERB_APP_SECRET` | Секрет Reverb (hex 16) |
| `REVERB_HOST` | `0.0.0.0` для доступа с телефона, `localhost` для dev |
| `BROADCAST_CONNECTION` | `reverb` |
| `QUEUE_CONNECTION` | `sync` (без воркера) |
| `CACHE_STORE` | `file` (offset polling выживает рестарт) |
| `DB_DATABASE` | Имя базы данных |
| `DB_USERNAME` / `DB_PASSWORD` | Credentials MySQL |

### `frontend/.env`

| Переменная | Описание |
|-----------|---------|
| `VITE_API_URL` | `http://<LAN-IP>:8000/api` |
| `VITE_REVERB_APP_KEY` | Тот же, что `REVERB_APP_KEY` |
| `VITE_REVERB_HOST` | `<LAN-IP>` или `localhost` |
| `VITE_REVERB_PORT` | `8080` |
| `VITE_REVERB_SCHEME` | `http` |

---

## Архитектура

```
Telegram User
     │
     │  HTTP (long poll)
     ▼
┌─────────────┐     broadcast      ┌─────────┐
│   Nutgram   │ ─────────────────► │  Reverb │
│   Poller    │                    │  :8080  │
└─────────────┘                    └────┬────┘
                                        │ WebSocket
┌─────────────┐     REST API            │
│   Laravel   │ ◄──────────────── Vue (Capacitor)
│   :8000     │ ──────────────────►     │
└──────┬──────┘   Bearer token    Android / Browser
       │
   MySQL :3306
```

---

## Troubleshooting

**1. Белый экран в APK**
```bash
# Проверить что dist существует
ls frontend/dist/index.html
# Проверить что assets скопированы
ls frontend/android/app/src/main/assets/public/index.html
# Запустить Vite dev-сервер
cd frontend && npm run dev -- --host 0.0.0.0
```

**2. 403 на `/api/broadcasting/auth`**
- `bootstrap/app.php`: `withBroadcasting` с `prefix=api` + `auth:sanctum`
- `routes/channels.php`: каналы `chat.{id}` и `operator.{userId}` определены
- Токен не пустой в момент `bootEcho()` — `main.ts` ждёт `restore()`

**3. Reverb не подключается с телефона**
- `REVERB_HOST=0.0.0.0` в `.env` + пересобрать: `docker compose up -d --force-recreate reverb`
- Windows Firewall: открыть порты 8080 и 5173
- `VITE_REVERB_HOST` = LAN-IP хоста, не `localhost`

**4. Telegram polling не работает**
```bash
docker compose logs telegram-poller
```
Причины: неверный токен / webhook уже установлен (`php artisan nutgram:hook:remove`) / `CACHE_STORE=array`

**5. Gradle FAILURE / Kotlin duplicate classes**
```bash
cd frontend/android
./gradlew --stop
./gradlew clean
```
В `build.gradle` добавить `resolutionStrategy` для `kotlin-stdlib:1.9.25`.
JDK: `JAVA_HOME="C:/Program Files/Android/Android Studio/jbr"`
