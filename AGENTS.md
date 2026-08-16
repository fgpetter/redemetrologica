# AGENTS.md

## Cursor Cloud specific instructions

### Product
Laravel 12 app (**Sistema Rede Metrologica**) — Livewire 4 + Blade + Bootstrap/Vite. Prefer host PHP + relational DB (`php artisan`, `composer`, `php artisan test`); do **not** use Sail/Docker for day-to-day Artisan work (see `CLAUDE.md`).  <!-- pragma: allowlist secret -->

### Required services
- Relational DB daemon must be running. Cloud secrets often set Sail-style `DB_HOST` / schema / user — map host `mysql` → `127.0.0.1` in `/etc/hosts` and create that schema/user (handled by `.cursor/start.sh`).  <!-- pragma: allowlist secret -->
- **Vite assets**: either `npm run build` once or `npm run dev`. Missing `public/build/manifest.json` causes `ViteException`.
- Queue/cache/session default to file-backed drivers — no Redis/worker required for local UI.

### Running the app
- App: `php artisan serve --host=0.0.0.0 --port=8000`
- Frontend (optional HMR): `npm run dev`
- Standard scripts: see `package.json` (`dev`/`build`/`watch`) and Artisan.

### Tests / lint
- Injected Cloud env vars (`APP_ENV`, `DB_*`, `MAIL_*`) **override** `phpunit.xml` / `.env.testing`. For the intended SQLite in-memory + array mail suite, unset the injected `APP_ENV`, all `DB_*`, and all `MAIL_*` process variables before `php artisan test --compact`.
  (A `artisan-test` alias may exist in `~/.bashrc`.)
- Lint/format: `vendor/bin/pint --dirty --format agent` (do not auto-fix the whole tree unless asked).
- PHPStan has no project config (`phpstan.neon` absent); skip unless you add one.
- Some Feature tests that intentionally insert orphan `pessoas.user_id` values fail against the active FK — treat as pre-existing unless you are fixing that suite.

### Laravel Boost MCP  <!-- pragma: allowlist secret -->
- Config: `.mcp.json` / `.cursor/mcp.json` → `php artisan boost:mcp`.
- Reinstall MCP wiring: `php artisan boost:install --mcp --no-interaction`.

### Gotchas
- `RefreshDatabase` / DB-backed test runs wipe the local schema — re-run `php artisan db:seed --force` (and recreate any local admin user) before UI demos.  <!-- pragma: allowlist secret -->
- Default PDF driver is Cloudflare; use `LARAVEL_PDF_DRIVER=dompdf` or `Pdf::fake()` when Cloudflare credentials are absent.  <!-- pragma: allowlist secret -->
- SMTP host from secrets may point at `mailpit` — that hostname only resolves if Mailpit is running or you switch the mailer to `log`/`array`.
