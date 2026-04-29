#!/bin/bash
set -e

echo "=== [1/5] Pulling latest code ==="
git pull origin master

echo "=== [2/5] Building frontend ==="
# Extract REVERB_APP_KEY from .env and pass it into the Vite build as an env var.
# Vite exposes VITE_* process env vars into the bundle, overriding .env.production.
REVERB_APP_KEY=$(grep '^REVERB_APP_KEY=' .env | cut -d'=' -f2 | tr -d '[:space:]')
if [ -z "$REVERB_APP_KEY" ]; then
  echo "ERROR: REVERB_APP_KEY is empty in .env — fill it in before deploying."
  exit 1
fi

docker run --rm \
  -v "$(pwd)/frontend:/app" \
  -w /app \
  -e VITE_REVERB_APP_KEY="$REVERB_APP_KEY" \
  node:22-alpine \
  sh -c "npm ci && npm run build"

echo "=== [3/5] Building & starting containers ==="
docker compose -f docker-compose.prod.yml up -d --build

echo "=== [4/5] Running migrations ==="
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

echo "=== [5/5] Caching config & routes ==="
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache

echo ""
echo "Deploy complete! Open http://$(curl -s ifconfig.me 2>/dev/null || echo '<VPS_IP>')"
