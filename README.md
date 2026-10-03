# URL Shortener API

A production-grade REST API for shortening URLs — built with **Laravel 12**, **Redis**, and **Docker**.

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Features

| Category | Feature |
|----------|---------|
| **Core** | URL shortening with random 7-character codes (62⁷ combinations) |
| **Customization** | User-chosen custom aliases with reserved-word protection |
| **Lifecycle** | URL expiration + activation toggle |
| **Analytics** | Click tracking with device, browser, platform, and referrer |
| **Stats** | Aggregated views: total clicks, unique visitors, top referrers, daily counts |
| **Auth** | Laravel Sanctum Bearer tokens — register, login, logout, me |
| **Authorization** | Ownership policies — users manage only their own URLs |
| **Rate Limiting** | Redis-backed: 5 named limiters protecting all critical endpoints |
| **API Docs** | Interactive Swagger UI at `/api/documentation` |
| **DevOps** | Multi-stage Docker build, healthchecks, entrypoint scripting |

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Language | PHP 8.4 |
| Database | SQLite (file-based, zero-config) |
| Cache / Queue | Redis 7 (AOF persistence) |
| Web Server | Nginx 1.27 (Alpine) |
| Runtime | PHP-FPM (Alpine) |
| API Docs | L5-Swagger (OpenAPI 3.0) |
| User-Agent Parser | jenssegers/agent |
| Auth | Laravel Sanctum |

---

## 🚀 Quick Start

### Prerequisites

- Docker + Docker Compose
- Git

### Installation

```bash
# Clone the repo
git clone https://github.com/YOUR_USERNAME/url-shortener.git
cd url-shortener

# Configure environment
cp .env.example .env

# Build & launch the stack
docker compose build
docker compose up -d

# Initialize the application
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan l5-swagger:generate
Verify
bash
curl http://localhost:8080/up
# → "ok" in ~7ms (no PHP boot)
Access
Service	URL
API	http://localhost:8080/api/v1
Swagger UI	http://localhost:8080/api/documentation
Health check	http://localhost:8080/up
📚 API Reference
Authentication
Method	Endpoint	Auth	Purpose
POST	/api/v1/auth/register	—	Create account, returns token
POST	/api/v1/auth/login	—	Authenticate, returns token
POST	/api/v1/auth/logout	✅	Revoke current token
GET	/api/v1/auth/me	✅	Current user profile
URLs
Method	Endpoint	Auth	Purpose
POST	/api/v1/urls	Optional	Shorten a URL (guests allowed)
GET	/api/v1/urls	✅	List own URLs (search, filter, sort, paginate)
GET	/api/v1/urls/{id}	✅	Get one URL
PATCH	/api/v1/urls/{id}	✅	Update URL (alias, expiry, active)
DELETE	/api/v1/urls/{id}	✅	Delete URL
Analytics
Method	Endpoint	Auth	Purpose
GET	/api/v1/urls/{id}/stats	✅	Aggregated click analytics
Redirect
Method	Endpoint	Purpose
GET	/{code}	Redirect to original URL (302)
🔒 Rate Limiting
All limits backed by Redis with proper 429 Too Many Requests responses and Retry-After headers.

Endpoint	Limit	Key	Purpose
POST /auth/login	5/min	IP + email	Brute-force protection
POST /auth/register	5/min	IP	Spam-account prevention
Public API	60/min	IP	Abuse protection
Authenticated API	120/min	User ID	Fair use per account
Redirects	300/min	IP	Hot-link flood protection
🐳 Docker Architecture
                    ┌──────────────────────┐
                    │      Internet        │
                    └──────────┬───────────┘
                               │ :8080
                    ┌──────────▼───────────┐
                    │    Nginx (web)       │
                    │  Static + PHP-FPM    │
                    └──────────┬───────────┘
                               │ :9000
                    ┌──────────▼───────────┐
                    │   PHP-FPM (app)      │
                    │    Laravel 12        │
                    └─────┬──────────┬─────┘
                          │          │
              ┌───────────▼──┐   ┌───▼────────────┐
              │ Redis 7      │   │  SQLite (file) │
              │ cache+queue  │   │  database/     │
              └──────────────┘   └────────────────┘
              All three containers have healthchecks and start in dependency order.


📁 Project Structure

.
├── app/
│   ├── Http/Controllers/Api/     # API controllers
│   ├── Http/Requests/            # Form validation
│   ├── Http/Resources/           # JSON shaping
│   ├── Models/                   # Eloquent models
│   ├── OpenApi/                  # Swagger annotations
│   ├── Policies/                 # Authorization
│   └── Services/                 # Business logic
├── docker/
│   ├── nginx/default.conf        # Nginx config
│   └── php/                      # php.ini, opcache, FPM, entrypoint
├── routes/
│   ├── api.php                   # API routes
│   └── web.php                   # Redirect route
├── database/migrations/          # Schema
├── docker-compose.yml
├── Dockerfile                    # Multi-stage build
└── README.md
🧪 Testing
docker compose exec app php artisan test
📄 License
MIT © Allaingaye Lucien

Replace:
- `YOUR_USERNAME` with your GitHub username
- `Your Name` with your name

---

## Step 2 — Verify `.gitignore` includes `.env` and SQLite

Run:

```bash
docker compose exec app cat .gitignore

Confirm it has:

text
.env
/vendor
/node_modules

Add these lines at the end if missing:
# Local SQLite database
/database/*.sqlite
/database/*.sqlite-journal
/database/*.sqlite-wal
/database/*.sqlite-shm

# Generated Swagger spec
/storage/api-docs/*.json
/storage/api-docs/*.yaml

# Diagnostic scripts
/scripts

# Docker overrides
docker-compose.override.yml