<div align="center">

# 🔗 URL Shortener API

**A production-grade REST API for shortening URLs, tracking clicks, and managing links.**

Built with **Laravel 12** · **PHP 8.4** · **Redis 7** · **Docker**

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![Tests](https://github.com/allaingaye/url-shortener/actions/workflows/tests.yml/badge.svg)](https://github.com/allaingaye/url-shortener/actions/workflows/tests.yml)
[![Code Style](https://github.com/allaingaye/url-shortener/actions/workflows/pint.yml/badge.svg)](https://github.com/allaingaye/url-shortener/actions/workflows/pint.yml)
[![License](https://img.shields.io/badge/License-MIT-22c55e?style=for-the-badge)](LICENSE)

[Features](#-features) · [Quick Start](#-quick-start) · [API Reference](#-api-reference) · [Architecture](#-architecture) · [Testing](#-testing) · [Rate Limiting](#-rate-limiting)

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
- Aggregated stats: **total clicks**, **unique visitors**, **top referrers**
- **Daily time-series** for charts
- **Async recording** via Redis queue

</td>
<td width="50%" valign="top">

### 🔐 Security
- **Sanctum** Bearer token authentication
- **Ownership policies** — users manage only their own URLs
- **Redis-backed rate limiting** with 5 named limiters
- **SecurityHeaders middleware** (CSP, HSTS, Permissions-Policy)
- **Fast `/up`** health endpoint (0.007s response)

### 🛠 DevOps
- **Multi-stage Docker build** (~95 MB final image)
- **5 services** — app, web, queue, scheduler, redis
- **Healthchecks** on all containers
- **GitHub Actions CI** — tests + code style on every push
- **Structured logging** — separate channels for clicks, auth, errors
- **Scheduled jobs** — auto-prune expired URLs daily
- **Interactive Swagger UI** at `/api/documentation`

</td>
</tr>
</table>

---

## 🛠 Tech Stack

<div align="center">

| Layer | Technology | Purpose |
|:------|:-----------|:--------|
| **Framework** | Laravel 12 | MVC, routing, ORM, validation |
| **Language** | PHP 8.4 | Typed properties, attributes, match expressions |
| **Database** | SQLite | Zero-config, file-based persistence |
| **Cache / Queue** | Redis 7 | URL cache, rate limiter, queue backend, AOF persistence |
| **Web Server** | Nginx 1.27 (Alpine) | Reverse proxy, static assets, gzip |
| **Runtime** | PHP-FPM (Alpine) | Optimized request handling |
| **API Docs** | L5-Swagger | OpenAPI 3.0 + interactive UI |
| **User-Agent** | jenssegers/agent | Device / browser / platform detection |
| **Auth** | Laravel Sanctum | Token-based API authentication |
| **Testing** | Pest 4 | Modern PHP test framework |
| **CI** | GitHub Actions | Test + code style on every push |

</div>

---

## 🚀 Quick Start

### Prerequisites

- **Docker** + **Docker Compose**
- **Git**

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/allaingaye/url-shortener.git
cd url-shortener

# 2. Set up environment
cp .env.example .env

# 3. Build and launch the stack
docker compose build
docker compose up -d

# 4. Initialize the application
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan l5-swagger:generate
```

### Verify the installation

```bash
curl http://localhost:8080/up
# → ok  (in ~7ms, no PHP boot)
```

### Access the services

| Service | URL |
|:--------|:----|
| 🚀 **API** | http://localhost:8080/api/v1 |
| 📖 **Swagger UI** | http://localhost:8080/api/documentation |
| 💚 **Health check** | http://localhost:8080/up |

> ⚠️ **Important:** Always run Composer commands **inside** the container:
> ```bash
> docker compose exec app composer require some/package
> ```
> `vendor/` lives in a Docker named volume for performance — the host's vendor is ignored.

---

## 📚 API Reference

### 🔐 Authentication

| Method | Endpoint | Auth | Purpose |
|:------:|:---------|:----:|:--------|
| `POST` | `/api/v1/auth/register` | — | Create account, returns Bearer token |
| `POST` | `/api/v1/auth/login` | — | Authenticate, returns Bearer token |
| `POST` | `/api/v1/auth/logout` | ✅ | Revoke current access token |
| `GET`  | `/api/v1/auth/me` | ✅ | Current authenticated user |

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
| `GET` | `/api/v1/urls/{id}/stats` | ✅ | Aggregated click analytics |

### 🌐 Redirect

| Method | Endpoint | Purpose |
|:------:|:---------|:--------|
| `GET` | `/{code}` | Redirect to original URL (`302 Found`) |

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
    "created_at": "2026-10-03T00:15:57+00:00",
    "updated_at": "2026-10-03T00:15:57+00:00"
  }
}
```

---

## 🔒 Rate Limiting

All limits are enforced via **Redis** with proper `429 Too Many Requests` responses, `Retry-After`, and `X-RateLimit-*` headers.

| Endpoint | Limit | Key | Purpose |
|:---------|:-----:|:----|:--------|
| `POST /auth/login` | **5**/min | IP + email | Brute-force protection |
| `POST /auth/register` | **5**/min | IP | Spam-account prevention |
| **Public API** | **60**/min | IP | Abuse protection |
| **Authenticated API** | **120**/min | User ID | Fair use per account |
| **Redirects** | **300**/min | IP | Hot-link flood protection |

**Example 429 response:**

```http
HTTP/1.1 429 Too Many Requests
Retry-After: 47
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 0
Content-Type: application/json

{"message":"Too many login attempts. Please try again later."}
```

---

## 🏗 Architecture

<div align="center">

```
                          ┌──────────────────────┐
                          │      Internet        │
                          └──────────┬───────────┘
                                     │ :8080
                          ┌──────────▼───────────┐
                          │      Nginx           │
                          │  static + PHP-FPM    │
                          └──────────┬───────────┘
                                     │ :9000
                ┌────────────────────┴────────────────────┐
                │                                         │
     ┌──────────▼───────────┐                  ┌──────────▼───────────┐
     │      PHP-FPM         │                  │    PHP-FPM           │
     │    Laravel 12        │                  │  queue:work          │
     │    (web requests)    │                  │  (background jobs)   │
     └─────┬──────────┬─────┘                  └──────────┬───────────┘
           │          │                                   │
           │          │  ┌──────────────────┐             │
           │          └──▶    Scheduler     ◀─────────────┘
           │             │  schedule:work   │
           │             └────────┬─────────┘
           │                      │
  ┌────────▼────────┐   ┌─────────▼───────┐   ┌──────────────────┐
  │   Redis 7       │   │  SQLite (file)  │   │  Laravel Logs    │
  │ cache + queue   │   │  database/      │   │  clicks/auth/    │
  │ + rate limiter  │   │                 │   │  errors          │
  └─────────────────┘   └─────────────────┘   └──────────────────┘
```

**5 containers** — app, web, queue, scheduler, redis — all with healthchecks.

</div>

### Design decisions

| Decision | Rationale |
|:---------|:----------|
| **SQLite** | Zero-config, ideal for portfolio demos and low-concurrency workloads |
| **Redis (DB 1)** for cache | Isolates app cache from queue — easy to flush independently |
| **URL ID caching**, not model | Eloquent models serialize poorly across cache drivers |
| **302, not 301** | Keeps click analytics accurate even if destination changes |
| **Async click recording** | Queue-dispatch keeps redirect latency under 100ms |
| **Policy-based auth** | Enforces ownership at the framework level, not in controllers |
| **SecurityHeaders middleware** | Defense-in-depth, single source of truth (Nginx defers) |

---

## 📁 Project Structure

```
url-shortener/
├── app/
│   ├── Console/Commands/
│   │   └── PruneExpiredUrls.php         # Scheduled cleanup command
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                     # AuthController, UrlController, UrlStatsController
│   │   │   └── RedirectController.php
│   │   ├── Middleware/
│   │   │   └── SecurityHeaders.php      # CSP, HSTS, Permissions-Policy
│   │   ├── Requests/                    # Form request validation
│   │   └── Resources/
│   │       └── UrlResource.php
│   ├── Jobs/
│   │   └── RecordClick.php              # Queued click recording
│   ├── Models/                          # Url, Click, User
│   ├── OpenApi/                         # Swagger annotations
│   ├── Policies/
│   │   └── UrlPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php       # 5 named rate limiters
│   └── Services/
│       ├── AnalyticsService.php         # UA parsing + click logging
│       ├── ShortCodeGenerator.php
│       └── UrlService.php               # Cached resolution
├── bootstrap/
│   └── app.php                          # Middleware + routing + exception logging
├── config/
│   └── l5-swagger.php
├── database/
│   ├── factories/                       # UserFactory, UrlFactory, ClickFactory
│   └── migrations/                      # users, urls, clicks, tokens
├── docker/
│   ├── nginx/default.conf               # Static + PHP-FPM proxy
│   └── php/
│       ├── entrypoint.sh                # Redis wait, cache clear, migrations
│       ├── opcache.ini
│       ├── php.ini
│       └── www.conf
├── routes/
│   ├── api.php                          # /api/v1 endpoints
│   ├── web.php                          # /{code} redirect
│   └── console.php                      # Scheduled tasks
├── storage/
│   └── logs/                            # laravel, clicks, auth, errors
├── tests/
│   ├── Feature/
│   │   ├── Api/                         # Auth, URLs, ownership, throttling, analytics
│   │   ├── Console/                     # PruneExpiredUrls
│   │   ├── Logging/                     # Log channel tests
│   │   ├── Middleware/                  # SecurityHeaders
│   │   └── Public/                      # Redirect
│   └── Pest.php
├── .github/workflows/
│   ├── tests.yml                        # Runs Pest on push/PR
│   └── pint.yml                         # Code style check
├── docker-compose.yml
├── Dockerfile                           # Multi-stage Alpine build
├── .dockerignore
├── .env.example
└── README.md
```

---

## 🧪 Testing

This project uses **[Pest 4](https://pestphp.com/)** — a modern testing framework.

```bash
# Run all tests
docker compose exec app ./vendor/bin/pest

# Run a specific suite
docker compose exec app ./vendor/bin/pest tests/Feature/Api

# Run a specific file
docker compose exec app ./vendor/bin/pest tests/Feature/Api/UrlShorteningTest.php

# Run a single test by name
docker compose exec app ./vendor/bin/pest --filter="throttles login"

# With coverage (requires Xdebug)
docker compose exec app ./vendor/bin/pest --coverage
```

**Current test coverage:**
- ✅ **61 tests** passing, 1 skipped
- ✅ **160+ assertions**
- ✅ 3 CI workflows on every push

### What's tested

| Suite | Coverage |
|:------|:---------|
| `Api/AuthTest` | Register, login, logout, /me, invalid credentials |
| `Api/UrlShorteningTest` | Create, validate, aliases, reserved words |
| `Api/UrlOwnershipTest` | Policy enforcement, cross-user access |
| `Api/RateLimitingTest` | 429 responses, retry-after headers |
| `Api/AnalyticsTest` | Click stats, device breakdown |
| `Api/QueuedAnalyticsTest` | Async job dispatch |
| `Public/RedirectTest` | 302 redirects, 404 for expired/inactive |
| `Console/PruneExpiredUrlsTest` | Cleanup command + cache busting |
| `Logging/LoggingTest` | Auth, clicks, errors channels |
| `Middleware/SecurityHeadersTest` | CSP, X-Frame-Options, HSTS |

---

## 🐳 Docker Commands Cheatsheet

```bash
# Start / stop the stack
docker compose up -d
docker compose down

# Rebuild the app image
docker compose build --no-cache app

# View logs (any service)
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f scheduler
docker compose logs -f web

# Open a shell in a container
docker compose exec app sh

# Run artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan urls:prune-expired --dry-run

# Check health status (5 services)
docker compose ps
```

### Queue & Scheduler

The stack includes dedicated containers:

- **`laravel_queue`** — runs `php artisan queue:work` for background jobs (click analytics)
- **`laravel_scheduler`** — runs `php artisan schedule:work` for cron-like tasks (prune expired URLs)

Both restart automatically on failure and have healthchecks.

---

## 🗺 Roadmap

- [x] **Phase 1** — MVP: shortening, redirects, API, Swagger
- [x] **Phase 2** — Authentication (Sanctum), ownership policies
- [x] **Phase 3** — Rate limiting with Redis
- [x] **Phase 4** — Click analytics with device/browser tracking
- [x] **Phase 5** — Custom aliases, expiration, search, filtering, pagination
- [x] **Phase 6** — Production-grade Docker (multi-stage, healthchecks)
- [x] **Phase 7** — Queues, tests, CI/CD, scheduled jobs, structured logging, security headers

### Possible next steps

- [ ] Redis-backed sessions
- [ ] API versioning (`/v2`)
- [ ] Multi-tenant support
- [ ] Kubernetes manifests
- [ ] Laravel Horizon for queue monitoring
- [ ] GeoIP enrichment for clicks
- [ ] Real-time analytics dashboard
- [ ] QR code generation
- [ ] Link password protection

---

## 🤝 Contributing

Contributions are welcome. Please open an issue or submit a pull request.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

**Before submitting:**
```bash
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/pest
```

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

<div align="center">

**Built with ❤️ using Laravel, Redis, and Docker**

⭐ If this project helped you, consider giving it a star!

[⬆ Back to top](#-url-shortener-api)

</div>