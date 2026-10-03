# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-10-03

### 🎉 Initial Release

The complete URL Shortener API, built through 7 development phases.

### Added — Phase 1: MVP
- URL shortening with random 7-character codes
- Public `GET /{code}` redirect endpoint (302)
- Full CRUD API at `/api/v1/urls`
- Interactive Swagger UI at `/api/documentation`
- Redis-cached URL resolution for hot-path performance
- SQLite database with `urls` table

### Added — Phase 2: Authentication
- Laravel Sanctum Bearer token authentication
- Register, login, logout, and me endpoints
- `UrlPolicy` for ownership-based authorization
- Users can only view/update/delete their own URLs
- Guests can still create anonymous URLs

### Added — Phase 3: Rate Limiting
- 5 Redis-backed rate limiters:
  - `login` — 5/min per IP+email
  - `register` — 5/min per IP
  - `api` — 60/min per IP
  - `api-user` — 120/min per user
  - `redirect` — 300/min per IP
- Proper `429 Too Many Requests` responses with `Retry-After` header
- `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers

### Added — Phase 4: Analytics
- Click tracking with `clicks` table
- Device / browser / platform detection via `jenssegers/agent`
- Referrer tracking
- `GET /api/v1/urls/{id}/stats` — aggregate analytics endpoint
- Unique visitor count, top referrers, daily time-series

### Added — Phase 5: Advanced Features
- Custom URL aliases with validation:
  - Reserved-word protection
  - Duplicate detection
  - Regex enforcement
- URL expiration with validation
- Activation toggle via `PATCH /api/v1/urls/{id}`
- Full update endpoint (original_url, alias, expiry, activation)
- Search across original_url, short_code, custom_alias
- Filtering by `active` and `expired` status
- Sorting with whitelisted columns
- Configurable pagination (1–100 per page)

### Added — Phase 6: Docker Polish
- Multi-stage Alpine Dockerfile (~95 MB final image)
- Non-root user permissions baked in
- Custom PHP, OPcache, and PHP-FPM configs
- Custom entrypoint with Redis wait + cache clearing
- Tini as PID 1 for clean signal handling
- Healthchecks on all services
- Named `vendor` volume for performance
- Nginx with gzip, static caching, and `/up` fast health endpoint

### Added — Phase 7: Production Quality
- **Testing** — 61 Pest tests, 160+ assertions
- **CI/CD** — GitHub Actions workflows for tests and code style
- **Queues** — `RecordClick` job with dedicated worker container
- **Scheduler** — daily `urls:prune-expired` command
- **Structured logging** — dedicated channels for `clicks`, `auth`, `errors`
- **SecurityHeaders middleware** — CSP, HSTS, Permissions-Policy, X-Frame-Options
- **README** — complete documentation with architecture and examples

### Security
- CSRF protection on web routes
- Password hashing via bcrypt
- SQL injection prevention via Eloquent ORM
- Rate limiting on all public endpoints
- Authorization via policies
- Security headers on every response
- HTTPS-only HSTS in production

### Performance
- Redis-cached URL resolution (sub-millisecond on cache hit)
- Async click recording (<100ms redirect latency)
- OPcache enabled with `validate_timestamps=0`
- Gzip compression for all text responses
- Static asset caching with 30-day expiration

---

## [Unreleased]

### Planned
- GeoIP enrichment for click tracking
- QR code generation endpoint
- Real-time analytics dashboard
- Link password protection
- API v2 with breaking changes
- Kubernetes manifests
- Laravel Horizon for queue monitoring