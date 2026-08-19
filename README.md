# ASELCO App Workshop

Member-facing mobile app and Laravel backend for **ASELCO** (electric cooperative) — account linking, consumer ledger, notifications, and staff admin tools.

## Repository layout

| Path | Stack | Role |
|------|--------|------|
| [`aselcoph/`](aselcoph/) | Laravel 12, Jetstream, Livewire, Sanctum | Web admin + mobile API (`/api/v1`) |
| [`mobile/`](mobile/) | Ionic React, Vite, Capacitor 8 | **ASELCO Member** Android app |

```
aselco-app-workshop/
├── aselcoph/          # Laravel API & admin panel
└── mobile/            # Ionic + Capacitor member app
```

## Features

**Mobile (member)**
- Register / login with email verification (Sanctum tokens)
- Membership setup and account linking
- Dashboard summary and consumer ledger (proxied from cooperative ledger API)
- Push notifications (Firebase Cloud Messaging)
- Support, complaints, and related member flows

**Backend (staff / API)**
- Jetstream auth (web) + Sanctum personal access tokens (mobile)
- Mobile API v1: auth, membership, dashboard, ledger, devices, notifications
- Admin capabilities: customers, announcements, billing uploads, support chat, file manager, and more
- Optional Google OAuth for web staff login

---

## Prerequisites

- **PHP** 8.2+ and [Composer](https://getcomposer.org/)
- **Node.js** 20+ (LTS recommended) and npm
- **MySQL/MariaDB** or SQLite (default in `.env.example`)
- For Android builds: [Android Studio](https://developer.android.com/studio) + JDK 17

---

## 1. Backend (`aselcoph`)

```bash
cd aselcoph
cp .env.example .env
composer install
npm install
php artisan key:generate
```

Configure `.env` (database, mail, CORS, ledger, Firebase). Important mobile-related keys:

```env
APP_URL=http://127.0.0.1:8001

# Allow Vite + Capacitor WebView origins
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173,https://localhost,capacitor://localhost,http://localhost

ASELCO_LEDGER_URL=https://coop.eduisbotswave.com/AselcoCollection/GetConsumersLeger
ASELCO_LEDGER_TIMEOUT=20

FIREBASE_PROJECT_ID=aselco-demo
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
```

Place your Firebase service account JSON at `aselcoph/storage/app/firebase-credentials.json` (do not commit secrets).

Then:

```bash
php artisan migrate
# optional: php artisan db:seed

# API reachable on LAN / emulator (matches mobile .env.example)
php artisan serve --host=0.0.0.0 --port=8001
```

In another terminal (Vite for the Jetstream/admin UI):

```bash
npm run dev
```

Or run server, queue, logs, and Vite together:

```bash
composer run dev
```

> If you use `composer run dev`, adjust the serve host/port (or `APP_URL`) so they match what the mobile app expects.

### Mobile API overview

Base path: `/api/v1`

| Area | Endpoints (summary) |
|------|---------------------|
| Auth | `POST /auth/register`, `/auth/login`, `/auth/logout`, `GET /auth/user` |
| Membership | account links, status, privacy, linked accounts |
| Dashboard | `GET /dashboard/summary` |
| Ledger | `GET /ledger` |
| Devices / push | `POST|DELETE /devices` |
| Notifications | list, mark read, preferences |

Auth uses Sanctum bearer tokens. Verified email is required for most member routes.

---

## 2. Mobile app (`mobile`)

```bash
cd mobile
cp .env.example .env
npm install
```

Set the API base URL in `.env`:

```env
# Emulator / browser with adb reverse
VITE_API_BASE_URL=http://127.0.0.1:8001/api/v1

# Physical device on same Wi‑Fi (use your machine's LAN IP)
# VITE_API_BASE_URL=http://192.168.x.x:8001/api/v1
```

### Web / Ionic preview

```bash
npm run dev
```

### Android (Capacitor)

App id: `aselco.demo.starter` · App name: **ASELCO Member**

```bash
npm run build
npx cap sync android
npx cap open android
```

Notes:
- Capacitor uses `androidScheme: 'https'` (WebView origin `https://localhost`) — keep that origin in Laravel `CORS_ALLOWED_ORIGINS`.
- `cleartext: true` allows HTTP API calls during local testing.
- For the Android emulator talking to the host API: `adb reverse tcp:8001 tcp:8001`.
- Ensure `google-services.json` package name matches `appId` in `capacitor.config.ts`.

---

## Local development checklist

1. Start Laravel on `0.0.0.0:8001` with a valid `.env` and migrated DB.
2. Point `mobile/.env` → `VITE_API_BASE_URL` at that API.
3. Run `npm run dev` in `mobile` (or build + sync for a device/APK).
4. Register a user, verify email, complete membership / account link, then exercise ledger and notifications.

---

## Security notes

- Never commit `.env`, Firebase credentials, or `google-services.json` if they contain production secrets.
- Keep Sanctum token expiration and CORS origins tight for production.
- Prefer HTTPS for production `VITE_API_BASE_URL` and ledger/Firebase configuration.

---

## License

Private workshop project unless otherwise specified by ASELCO / the maintainers.
