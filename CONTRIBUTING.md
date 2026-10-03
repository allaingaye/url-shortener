# Contributing to URL Shortener API

Thanks for considering contributing! This document outlines the process.

## Code of Conduct

By participating, you agree to uphold a welcoming, inclusive environment. Be kind, be constructive.

## How to Contribute

### Reporting Bugs

Open an issue with:
- **Clear title** — describe the problem
- **Steps to reproduce** — exact commands
- **Expected behavior** — what you expected
- **Actual behavior** — what happened
- **Environment** — OS, Docker version, PHP version

### Suggesting Features

Open an issue with:
- **Use case** — why this feature matters
- **Proposed solution** — how you'd implement it
- **Alternatives considered** — what else you thought of

### Submitting Pull Requests

1. **Fork** the repository
2. **Create a branch** — `git checkout -b feature/amazing-feature`
3. **Make changes** — follow the coding standards below
4. **Test** — ensure all tests pass
5. **Lint** — run Pint to fix style issues
6. **Commit** — use [conventional commits](https://www.conventionalcommits.org/)
7. **Push** — to your fork
8. **Open a PR** — against `main`

## Development Setup

```bash
git clone https://github.com/YOUR_USERNAME/url-shortener.git
cd url-shortener
cp .env.example .env
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan l5-swagger:generate
```

## Coding Standards

### PHP

- PSR-12 compliant (enforced by Laravel Pint)
- Strict types where reasonable
- Type hints on all parameters and returns
- Docblocks on public methods with complex logic

### Style enforcement

```bash
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/pint
```

### Naming conventions

| Type | Convention | Example |
|------|-----------|---------|
| Classes | PascalCase | UrlController |
| Methods | camelCase | resolveUrl() |
| Variables | camelCase | $urlId |
| DB tables | snake_case plural | personal_access_tokens |
| DB columns | snake_case | custom_alias |
| Constants | SCREAMING_SNAKE | CACHE_TTL_SECONDS |

### Commit messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add QR code generation endpoint
fix: prevent expired URLs from resolving via cache
docs: update API reference with new fields
test: add coverage for custom alias validation
refactor: extract URL resolution to service
chore: bump PHP to 8.4.1
```

## Testing

All PRs must include tests for new functionality.

```bash
docker compose exec app ./vendor/bin/pest
docker compose exec app ./vendor/bin/pest tests/Feature/Api/UrlShorteningTest.php
docker compose exec app ./vendor/bin/pest --filter="it lets a guest shorten"
```

### Test requirements

- Feature tests for HTTP endpoints
- Unit tests for services and complex logic
- Coverage target — 80%+ for new code
- Test both happy path and edge cases

### Test style

We use Pest:

```php
it('lets a guest shorten a URL', function () {
    $response = $this->postJson('/api/v1/urls', [
        'original_url' => 'https://laravel.com',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.original_url', 'https://laravel.com');
});
```

## Architecture Guidelines

### Controllers

Thin controllers — delegate to services:

```php
public function store(StoreUrlRequest $request): JsonResponse
{
    $url = $this->urlService->create($request->validated(), auth()->id());

    return (new UrlResource($url))->response()->setStatusCode(201);
}
```

### Services

Business logic lives in app/Services/:

- UrlService — creation, resolution, cache invalidation
- AnalyticsService — click recording, UA parsing
- ShortCodeGenerator — code generation with collision handling

### Validation

All input validation in Form Requests (app/Http/Requests/).

### Authorization

All ownership checks in Policies (app/Policies/).

### Responses

All JSON shaping in Resources (app/Http/Resources/).

## Security

### Never commit

- .env files
- API keys or secrets
- Passwords

### Report vulnerabilities

Do not open a public issue for security vulnerabilities. Email the maintainer directly.

## Questions?

Open a [Discussion](https://github.com/allaingaye/url-shortener/discussions) or ask on the issue.

---

Thank you for contributing!