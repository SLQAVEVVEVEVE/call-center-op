# call-center-op — Claude guide

Hybrid Telegram-operator app (Web + Android APK). Operator receives and replies to user messages from a Telegram bot in real time.

## Stack (frozen)

- **Backend:** PHP 8.2, Laravel 12, MySQL 8
- **Real-time:** Laravel Reverb (self-hosted WebSocket, port 8080)
- **Telegram:** Nutgram + long polling (`php artisan nutgram:run`)
- **Auth:** Laravel Sanctum — **Bearer token**, not SPA cookie
- **Frontend:** Vue 3, Pinia, Vite, TypeScript
- **Mobile:** Capacitor 8 → Android APK (debug build is enough for MVP)
- **Infra:** Docker Compose, 5 services

## Monorepo layout

```
call-center-op/
├── backend/            Laravel 12
├── frontend/           Vue 3 + Capacitor
├── docker/
│   ├── Dockerfile      PHP 8.2-fpm-alpine (reused by app, reverb, telegram-poller)
│   └── nginx/default.conf
├── docker-compose.yml  app, nginx, mysql, reverb, telegram-poller
├── .env                NOT committed — source of truth for docker compose
└── .env.example        Committed, all keys with empty values
```

## Ports

- `8000` — nginx → Laravel
- `8080` — Reverb WebSocket
- `3306` — MySQL
- `5173` — Vite dev server (run from host, not container)

## Delivery contract (from ТЗ)

**5 API endpoints, all under `auth:sanctum`:**
1. `POST /api/register`
2. `POST /api/login`
3. `GET /api/chats`
4. `GET /api/chats/{chat}/messages`
5. `POST /api/chats/{chat}/messages`

**4 frontend pages:** register, login (store token), chat list, chat (history + send form + realtime updates).

**Builds:** Web (Vite build) + Android APK (Capacitor 8).

## Two critical risks — check at gates

1. **Sanctum + Reverb private-channel auth** — `/api/broadcasting/auth` must accept Bearer, not cookie. Configure in `backend/bootstrap/app.php`. Verify at Gate 02.
2. **CORS from Capacitor WebView** — on Android `localhost` ≠ host machine; need LAN-IP. Verify at Gate 04.

## Phases & gates

| Phase | Output | Gate |
|---|---|---|
| 01 Setup | `docker compose up` → 5 services healthy; `curl :8000` → 200 | Phase 01 checklist |
| 02 Backend Core | Auth, Telegram polling, Reverb events | Sanctum+Reverb auth passes |
| 03 API | 5 endpoints pass curl smoke | — |
| 04 Frontend | Full flow in browser, no reload | LAN-IP CORS works |
| 05 Android | APK installs; E2E on device | — |

## Conventions

- `.env` never committed. `.env.example` has every key with empty value.
- `composer.lock` and `package-lock.json` are committed.
- `vendor/`, `node_modules/`, `dist/`, `android/app/build/`, `*.keystore` in `.gitignore`.
- Do not write inline comments on the same line as `KEY=value` in `.env` — Docker Compose `env_file` treats everything after `=` up to newline as the value, including the `#`. Put comments on their own line above.
- Telegram bot token lives in a password manager. Rotate if it leaks into git history.
- `CACHE_STORE=file` (not `array`) — Telegram long-poll offset must survive restarts.

## Out of MVP scope (known tech debt)

Secure token storage, queue workers, pagination, HTTPS, release APK with keystore, push notifications, media attachments.

## Useful commands

```bash
# first boot
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate

# logs
docker compose logs -f reverb
docker compose logs -f telegram-poller

# frontend (host, not container)
cd frontend && npm run dev -- --host 0.0.0.0
```
