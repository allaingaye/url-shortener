<div align="center">

# 🔗 URL Shortener

**A full-stack URL shortener with real-time analytics, QR codes, and a modern React dashboard.**

Built with **Laravel 13** · **React 18** · **TypeScript** · **Redis 7** · **Laravel Reverb** · **Docker**

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![React](https://img.shields.io/badge/React-18-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://react.dev/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.6-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![Tailwind](https://img.shields.io/badge/Tailwind-3.4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)
[![WebSocket](https://img.shields.io/badge/Reverb-WebSocket-4A90E2?style=for-the-badge&logo=websocket&logoColor=white)](https://reverb.laravel.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docs.docker.com/compose/)

[![Tests](https://github.com/allaingaye/url-shortener/actions/workflows/tests.yml/badge.svg)](https://github.com/allaingaye/url-shortener/actions/workflows/tests.yml)
[![Code Style](https://github.com/allaingaye/url-shortener/actions/workflows/pint.yml/badge.svg)](https://github.com/allaingaye/url-shortener/actions/workflows/pint.yml)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=for-the-badge)](LICENSE)

[Features](#-features) · [Quick Start](#-quick-start) · [Frontend](#-frontend-react--typescript) · [API Reference](#-api-reference) · [Architecture](#-architecture) · [Testing](#-testing)

</div>

---

## ✨ Features

<table>
<tr>
<td width="50%" valign="top">

### 🔗 Core
- URL shortening with **7-character codes** (62⁷ ≈ 3.5 trillion combinations)
- **Custom aliases** with reserved-word protection
- **URL expiration** — auto-disable after a date
- **Activation toggle** — pause without deleting

### 📊 Analytics
- Click tracking with **device**, **browser**, **platform**, **referrer**
- **Aggregated stats**: total clicks, unique visitors, top referrers
- **Per-URL analytics** page with Chart.js visualizations
- **All-URLs overview** dashboard
- **Async recording** via Redis queue

</td>
<td width="50%" valign="top">

### 📱 QR Codes
- **PNG and SVG** output
- **Configurable size** (64–1024 px)
- **Redis-cached** for sub-millisecond repeat access
- **Download button** in dashboard modal

### ⚡ Real-Time
- **Laravel Reverb** WebSocket server
- **`ClickRecorded` broadcast event** on private user channel
- **Live click counter** updates in dashboard without refresh
- **Nginx WebSocket proxy** at `/app` and `/apps`

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 🎨 Frontend (React 18 + TypeScript)
- **Modern SaaS dashboard** with blue gradient sidebar
- **Login/Register** pages with error handling
- **Dashboard** with stats cards, create form, and URLs table
- **Per-URL analytics** with daily chart + breakdowns
- **All-URLs analytics** overview page
- **Toast notifications** for user feedback
- **Custom 404** page
- **Responsive** design with Tailwind CSS

</td>
<td width="50%" valign="top">

### 🔐 Security
- **Sanctum** authentication (Bearer tokens + SPA cookies)
- **Ownership policies** — users manage only their own URLs
- **Redis-backed rate limiting** with 5 named limiters
- **SecurityHeaders middleware** (CSP, HSTS, Permissions-Policy)
- **Fast `/up`** health endpoint (0.007s response)

### 🛠 DevOps
- **Multi-stage Docker build** (~95 MB backend image)
- **7 containers** — app, web, queue, scheduler, reverb, redis, frontend
- **Healthchecks** on all containers
- **GitHub Actions CI** — 71 Pest tests + Pint code style
- **Structured logging** — separate channels for clicks, auth, errors
- **Scheduled jobs** — auto-prune expired URLs daily

</td>
</tr>
</table>

---

## 🛠 Tech Stack

<div align="center">

### Backend
| Layer | Technology | Purpose |
|:------|:-----------|:--------|
| **Framework** | Laravel 13 | MVC, routing, ORM, validation |
| **Language** | PHP 8.4 | Typed properties, attributes, match expressions |
| **Database** | SQLite | Zero-config, file-based persistence |
| **Cache / Queue** | Redis 7 | URL cache, rate limiter, queue backend, AOF persistence |
| **WebSocket** | Laravel Reverb | Real-time event broadcasting |
| **Web Server** | Nginx 1.27 (Alpine) | Reverse proxy, static assets, WebSocket proxy |
| **API Docs** | L5-Swagger | OpenAPI 3.0 + interactive UI |
| **Auth** | Laravel Sanctum | Token + SPA cookie authentication |

### Frontend
| Layer | Technology | Purpose |
|:------|:-----------|:--------|
| **Framework** | React 18 | Component-based UI |
| **Language** | TypeScript 5.6 | Type safety, better DX |
| **Build Tool** | Vite 6 | Fast dev server + HMR |
| **Styling** | Tailwind CSS 3.4 | Utility-first CSS |
| **Routing** | React Router v6 | Client-side navigation |
| **HTTP** | Axios | API client with interceptors |
| **WebSocket** | Laravel Echo + Pusher JS | Reverb client |
| **Charts** | Chart.js 4 + react-chartjs-2 | Analytics visualizations |

### Testing & CI
| Tool | Purpose |
|:-----|:--------|
| **Pest 4** | 71 feature tests |
| **GitHub Actions** | Tests + Pint on every push |
| **Laravel Pint** | PSR-12 code style enforcement |

</div>

---

## 🚀 Quick Start

### Prerequisites

- **Docker** + **Docker Compose**
- **Node.js 20+** + **npm** (for frontend dev)
- **Git**

### Backend

```bash
# 1. Clone the repository
git clone https://github.com/allaingaye/url-shortener.git
cd url-shortener

# 2. Set up environment
cp .env.example .env

# 3. Build and launch the backend stack
docker compose build
docker compose up -d

# 4. Initialize the application
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan l5-swagger:generate
```

### Frontend

```bash
# 5. Set up frontend
cd frontend
cp .env.example .env
npm install

# 6. Start the frontend container
docker compose up -d
```

**Or for development (hot reload):**

```bash
cd frontend
npm run dev
# → http://localhost:5173
```

### Verify the installation

```bash
# Backend health
curl http://localhost:8080/up
# → ok  (in ~7ms)

# Frontend (in browser)
open http://localhost:5173
```

### Access the services

| Service | URL |
|:--------|:----|
| 🚀 **API** | http://localhost:8080/api/v1 |
| 📖 **Swagger UI** | http://localhost:8080/api/documentation |
| 💚 **Health check** | http://localhost:8080/up |
| 🎨 **Frontend (dev)** | http://localhost:5173 |
| 🎨 **Frontend (Docker)** | http://localhost:3000 |
| 🔌 **Reverb WebSocket** | ws://localhost:8080/app/{key} |

> ⚠️ **Important:** Always run Composer commands **inside** the backend container:
> ```bash
> docker compose exec app composer require some/package
> ```
> `vendor/` lives in a Docker named volume for performance.

---

## 🎨 Frontend (React + TypeScript)

### Pages

| Route | Purpose |
|:------|:--------|
| `/login` | Login form with error handling |
| `/register` | Register form with password confirmation |
| `/dashboard` | Main overview: stats, create form, URLs table, live clicks |
| `/dashboard/analytics` | Aggregated analytics across all URLs |
| `/dashboard/urls/:id` | Per-URL analytics (chart + breakdowns) |
| `/dashboard/settings` | Account settings (placeholder) |

### Features

- **Sanctum SPA auth** — cookie-based, works with WebSocket private channels
- **Real-time updates** — new clicks appear immediately via WebSocket
- **Toast notifications** — copy, create, delete, toggle feedback
- **QR code modal** — view + download PNG/SVG
- **Responsive** — works on desktop and mobile
- **TypeScript** — full type safety with shared `types/` folder

### Development workflow

```bash
cd frontend
npm run dev        # Vite dev server on :5173 (hot reload)
npm run build      # Production build → dist/
npm run preview    # Preview production build locally
```

The Vite dev proxy routes API calls to the backend:

| Frontend request | Proxied to |
|:-----------------|:-----------|
| `/api/*` | `http://localhost:8080/api/*` |
| `/sanctum/*` | `http://localhost:8080/sanctum/*` |
| `/broadcasting/*` | `http://localhost:8080/broadcasting/*` |
| `/app/*` | `ws://localhost:8080/app/*` |

---

## 📚 API Reference

### 🔐 Authentication

| Method | Endpoint | Auth | Purpose |
|:------:|:---------|:----:|:--------|
| `POST` | `/api/v1/auth/register` | — | Create account, returns token |
| `POST` | `/api/v1/auth/login` | — | Authenticate (token + session) |
| `POST` | `/api/v1/auth/logout` | ✅ | Revoke token + destroy session |
| `GET`  | `/api/v1/auth/me` | ✅ | Current authenticated user |
| `GET`  | `/sanctum/csrf-cookie` | — | Set CSRF cookie (SPA auth) |

### 🔗 URLs

| Method | Endpoint | Auth | Purpose |
|:------:|:---------|:----:|:--------|
| `POST`   | `/api/v1/urls` | Optional | Shorten a URL (guests allowed) |
| `GET`    | `/api/v1/urls` | ✅ | List own URLs (search, filter, sort, paginate) |
| `GET`    | `/api/v1/urls/{id}` | ✅ | Get one URL |
| `PATCH`  | `/api/v1/urls/{id}` | ✅ | Update alias, expiry, or activation |
| `DELETE` | `/api/v1/urls/{id}` | ✅ | Delete URL |

### 📊 Analytics

| Method | Endpoint | Auth | Purpose |
|:------:|:---------|:----:|:--------|
| `GET` | `/api/v1/urls/{id}/stats` | ✅ | Aggregated click analytics (per URL) |

### 📱 QR Codes

| Method | Endpoint | Auth | Purpose |
|:------:|:---------|:----:|:--------|
| `GET` | `/api/v1/urls/{id}/qr` | ✅ | QR code (PNG or SVG) |

**Query parameters:**
- `format=png\|svg` (default: `png`)
- `size=64..1024` (default: `300`, PNG only)

### 🌐 Redirect & WebSocket

| Method | Endpoint | Purpose |
|:------:|:---------|:--------|
| `GET` | `/{code}` | Redirect to original URL (`302 Found`) |
| `POST` | `/broadcasting/auth` | WebSocket channel authorization |

### Example — Shorten a URL

```bash
curl -X POST http://localhost:8080/api/v1/urls \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"original_url": "https://laravel.com"}'
```

**Response** `201 Created`

```json
{
  "data": {
    "id": 1,
    "original_url": "https://laravel.com",
    "short_code": "aB3xK9m",
    "custom_alias": null,
    "public_code": "aB3xK9m",
    "short_url": "http://localhost:8080/aB3xK9m",
    "clicks_count": 0,
    "is_active": true,
    "expires_at": null,
    "created_at": "2026-11-08T00:15:57+00:00",
    "updated_at": "2026-11-08T00:15:57+00:00"
  }
}
```

---

## 🔒 Rate Limiting

All limits enforced via **Redis** with `429 Too Many Requests`, `Retry-After`, and `X-RateLimit-*` headers.

| Endpoint | Limit | Key | Purpose |
|:---------|:-----:|:----|:--------|
| `POST /auth/login` | **5**/min | IP + email | Brute-force protection |
| `POST /auth/register` | **5**/min | IP | Spam-account prevention |
| **Public API** | **60**/min | IP | Abuse protection |
| **Authenticated API** | **120**/min | User ID | Fair use per account |
| **Redirects** | **300**/min | IP | Hot-link flood protection |

---

## 🏗 Architecture

<div align="center">

```
                    ┌──────────────────────┐
                    │  React Frontend      │
                    │  (Vite / Nginx :3000)│
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │      Nginx           │
                    │  (port 8080)         │
                    │  HTTP + WebSocket    │
                    └──────────┬───────────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
   ┌────▼─────┐          ┌─────▼─────┐          ┌────▼──────┐
   │ PHP-FPM  │          │ PHP-FPM   │          │ Reverb    │
   │ app      │          │ queue     │          │ WebSocket │
   │ :9000    │          │ worker    │          │ :6001     │
   └────┬─────┘          └─────┬─────┘          └────┬──────┘
        │                      │                      │
        └──────────┬───────────┴──────────────────────┘
                   │
        ┌──────────┴───────────┐
        │                      │
   ┌────▼─────┐          ┌─────▼─────┐
   │ Redis 7  │          │ SQLite    │
   │ cache    │          │ database/ │
   │ queue    │          │           │
   │ pubsub   │          │           │
   └──────────┘          └───────────┘
```

**7 containers** — app, web, queue, scheduler, reverb, redis, frontend

</div>

### Design decisions

| Decision | Rationale |
|:---------|:----------|
| **SQLite** | Zero-config, ideal for portfolio demos |
| **Redis (DB 1)** for cache | Isolates app cache from queue |
| **URL ID caching**, not model | Eloquent models serialize poorly |
| **302, not 301** | Keeps click analytics accurate |
| **Async click recording** | Queue-dispatch keeps redirects <100ms |
| **`ShouldBroadcastNow`** | Already in queue worker — no second hop |
| **Two REVERB_HOST values** | PHP uses `reverb:6001`, browser uses `localhost:8080` |
| **Sanctum SPA mode** | Cookie auth required for WebSocket private channels |
| **Vite proxy Referer forwarding** | Sanctum needs Referer header to recognize stateful requests |

---

## 📁 Project Structure

```
url-shortener/
├── app/                                   # Laravel backend
│   ├── Console/Commands/
│   │   └── PruneExpiredUrls.php
│   ├── Events/
│   │   └── ClickRecorded.php              # Broadcastable event
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── QrCodeController.php
│   │   │   │   ├── UrlController.php
│   │   │   │   └── UrlStatsController.php
│   │   │   └── RedirectController.php
│   │   ├── Middleware/SecurityHeaders.php
│   │   ├── Requests/
│   │   └── Resources/UrlResource.php
│   ├── Jobs/RecordClick.php
│   ├── Models/                            # Url, Click, User
│   ├── OpenApi/                           # Swagger annotations
│   ├── Policies/UrlPolicy.php
│   ├── Providers/AppServiceProvider.php
│   └── Services/
│       ├── AnalyticsService.php
│       ├── QrCodeService.php
│       ├── ShortCodeGenerator.php
│       └── UrlService.php
├── frontend/                              # React + TypeScript
│   ├── src/
│   │   ├── components/
│   │   │   ├── Layout.tsx                 # Sidebar shell
│   │   │   ├── Sidebar.tsx                # Blue gradient nav
│   │   │   ├── ProtectedRoute.tsx
│   │   │   ├── StatsCards.tsx
│   │   │   ├── CreateUrlForm.tsx
│   │   │   ├── UrlsTable.tsx
│   │   │   └── Toast.tsx
│   │   ├── hooks/
│   │   │   ├── useAuth.tsx
│   │   │   ├── useUrls.ts
│   │   │   ├── useUrlStats.ts
│   │   │   └── useLiveClicks.ts
│   │   ├── lib/
│   │   │   ├── api.ts                     # Axios client
│   │   │   ├── auth.ts
│   │   │   ├── echo.ts                    # WebSocket client
│   │   │   ├── stats.ts
│   │   │   └── urls.ts
│   │   ├── pages/
│   │   │   ├── Login.tsx
│   │   │   ├── Register.tsx
│   │   │   ├── Dashboard.tsx
│   │   │   ├── UrlAnalytics.tsx
│   │   │   ├── AllAnalytics.tsx
│   │   │   ├── Settings.tsx
│   │   │   └── NotFound.tsx
│   │   ├── types/
│   │   │   ├── api.ts
│   │   │   └── stats.ts
│   │   ├── App.tsx
│   │   ├── main.tsx
│   │   └── index.css
│   ├── package.json
│   ├── vite.config.ts
│   ├── tailwind.config.js
│   ├── tsconfig.json
│   ├── Dockerfile                         # Multi-stage build
│   ├── nginx.conf                         # SPA + API proxy
│   └── docker-compose.yml                 # Frontend service
├── bootstrap/app.php
├── config/                                # Laravel config
├── database/                              # Migrations + factories
├── docker/                                # Nginx + PHP configs
├── routes/                                # api, web, channels, console
├── tests/                                 # Pest feature tests
├── .github/workflows/                     # CI
├── docker-compose.yml                     # 6 backend services
├── Dockerfile                             # Backend image
└── README.md
```

---

## 🧪 Testing

### Backend (Pest 4)

```bash
docker compose exec app ./vendor/bin/pest
docker compose exec app ./vendor/bin/pest tests/Feature/Api
docker compose exec app ./vendor/bin/pest --filter="throttles login"
```

**Test coverage:**
- ✅ **71 tests passing, 1 skipped**
- ✅ **190+ assertions**
- ✅ 2 CI workflows on every push

| Suite | Coverage |
|:------|:---------|
| `Api/AuthTest` | Register, login, logout, /me |
| `Api/UrlShorteningTest` | Create, validate, aliases |
| `Api/UrlOwnershipTest` | Policy enforcement |
| `Api/RateLimitingTest` | 429 responses |
| `Api/AnalyticsTest` | Click stats |
| `Api/QueuedAnalyticsTest` | Async job dispatch |
| `Api/QrCodeTest` | PNG/SVG generation |
| `Public/RedirectTest` | 302 + 404 cases |
| `Console/PruneExpiredUrlsTest` | Cleanup command |
| `Logging/LoggingTest` | Log channels |
| `Middleware/SecurityHeadersTest` | CSP, HSTS |

### Frontend

```bash
cd frontend
npm run build      # TypeScript + Vite build check
```

---

## 🐳 Docker Commands Cheatsheet

### Backend (from repo root)

```bash
docker compose up -d                       # start 6 services
docker compose down
docker compose build --no-cache app
docker compose logs -f app                 # also: queue, scheduler, reverb, web
docker compose exec app sh
docker compose exec app php artisan migrate
docker compose ps
```

### Frontend (from `frontend/`)

```bash
docker compose up -d                       # start frontend container
docker compose build
docker compose ps
```

### Full stack (both)

```bash
# Terminal 1: Backend
cd url-shortener && docker compose up -d

# Terminal 2: Frontend
cd url-shortener/frontend && docker compose up -d
```

**Access:**
- API: http://localhost:8080
- Frontend: http://localhost:3000
- Swagger: http://localhost:8080/api/documentation

---

## 🗺 Roadmap

- [x] **Phase 1** — MVP: shortening, redirects, API, Swagger
- [x] **Phase 2** — Authentication (Sanctum), ownership policies
- [x] **Phase 3** — Rate limiting with Redis
- [x] **Phase 4** — Click analytics with device/browser tracking
- [x] **Phase 5** — Custom aliases, expiration, search, filtering, pagination
- [x] **Phase 6** — Production-grade Docker (multi-stage, healthchecks)
- [x] **Phase 7** — Queues, tests, CI/CD, scheduled jobs, structured logging, security headers
- [x] **Phase 8** — QR code generation with Redis caching
- [x] **Phase 9** — Real-time WebSocket events with Laravel Reverb
- [x] **Phase 10** — React SPA frontend (auth, dashboard, analytics, real-time)
- [ ] **Phase 11** — Production deployment (Railway / Fly.io / VPS)

### Possible next steps

- [ ] API versioning (`/v2`)
- [ ] Multi-tenant support
- [ ] Kubernetes manifests
- [ ] Laravel Horizon for queue monitoring
- [ ] GeoIP enrichment for clicks
- [ ] Link password protection
- [ ] Custom domains
- [ ] Team collaboration

---

## 🏆 Project highlights

This project demonstrates:

- **Full-stack proficiency** — Laravel 13 backend + React 18 frontend
- **Real-time architecture** — Reverb WebSocket + queue workers
- **Docker orchestration** — 7 containers with healthchecks
- **Sanctum SPA authentication** — cookie + CSRF + WebSocket auth
- **Testing discipline** — 71 Pest tests, CI green on every push
- **Modern UI/UX** — Tailwind CSS, responsive, accessible
- **Production practices** — rate limiting, security headers, structured logging, scheduled jobs
- **Clean architecture** — service layer, policies, form requests, API resources

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

**Before submitting:**
```bash
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/pest
cd frontend && npm run build
```

---

## 📄 License

MIT License — see [LICENSE](LICENSE) for details.

---

<div align="center">

**Built with ❤️ using Laravel, React, TypeScript, Redis, Reverb, and Docker**

⭐ If this project helped you, consider giving it a star!

[⬆ Back to top](#-url-shortener)

</div>