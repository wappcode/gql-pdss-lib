High-signal notes for OpenCode sessions working on this repository.

Keep this file short — only things an agent would probably miss.

Quick facts
- Language: PHP (>= 8.2). Use composer for dependency/tasks: `composer install`, `composer test`, `composer cs-fix`.
- This is a library + example/dev app. Primary code lives under `GPDCore/` and `GraphqlModule/`.

Important commands (exact)
- Install deps: `composer install` (run from repository root).
- Run unit+integration tests: `composer test` (runs phpunit with the repo phpunit.xml and bootstrap `dev/test/bootstrap.php`).
- Run phpunit directly: `./vendor/bin/phpunit` (or `./vendor/bin/phpunit --testsuite Unit` etc.).
- Run php built-in server for manual local testing: `php -S localhost:8000 public/index.php` (but see Tests section below).
- Doctrine CLI shipped as `./bin/doctrine` (composer script `doctrine` points there). Example: `./bin/doctrine orm:schema-tool:update --dump-sql`.
- PHP CS Fixer via composer scripts: `composer cs-fix` (dry-run: `composer run cs-fix-dry` or `composer run cs-check`).

Tests / dev-server gotchas (must read)
- PHPUnit bootstrap is `dev/test/bootstrap.php`. It creates an EntityManager from `dev/config/doctrine.local.php` and a GQL client instance pointing to exactly: `http://localhost/index.php/api`.
  - Integration tests therefore expect the application reachable at that exact base URL (no port in the URL → default HTTP port 80).
  - Common pitfall: running `php -S localhost:8000 public/index.php` will make the app available on port 8000 and integration tests will fail because the bootstrap hits port 80. Either run a server accessible on port 80 or run the server inside Docker mapped to host port 80 (recommended for dev).

Docker (recommended for integration tests)
- Docker compose files and helper scripts are under `dockerfiles_gqlpdsslib_php_apache2_mysql8/`.
- Start the dev environment from that directory: `cd dockerfiles_gqlpdsslib_php_apache2_mysql8 && docker compose up`.
- If you need specific ports or envs, the folder README shows examples. Ensure the web service is reachable at `http://localhost/index.php/api` (map container port to host port 80 or adjust tests/bootstrap accordingly).

Database / Doctrine local config
- Dev Doctrine config: `dev/config/doctrine.local.php`. It reads environment variables named PDSSLIB_DBUSER, PDSSLIB_DBPASSWORD, PDSSLIB_DBNAME, PDSSLIB_DBHOST and falls back to defaults. Note: the `port` value in that file is the internal MySQL container port (comment: "Puerto interno de MySQL en Docker"), not a host mapping env.
- CLI helper `cli-config.php` is present for Doctrine console helpers and expects the same EntityManager factory usage as the app.

Autoload / modules
- Main package namespaces are declared in `composer.json`: PSR-4 mapping for `GPDCore\` and `GraphqlModule\`.
- Dev example module autoload is in `autoload-dev`: `AppModule\` -> `dev/modules/AppModule/src/`.

Files worth checking first when you need context
- README.md (root) — high-level usage, example AppModule and GraphQL examples.
- GPDCore/STRUCTURE.md — explains where core pieces live.
- phpunit.xml & dev/test/bootstrap.php — how tests wire EntityManager and the GQL endpoint.
- dockerfiles_gqlpdsslib_php_apache2_mysql8/README.md — exact docker commands and how to expose Xdebug.

Avoid
- Do not expect tests to be runnable without a database-backed server reachable at the bootstrap URL. Don't change tests to point to a different host/port without understanding test intent.
- Don't edit vendor/ files.

If something is unclear
- If you need a different test host/port mapping, change `dev/test/bootstrap.php` (it hardcodes the GQL client URL) or run the app in Docker mapped to host port 80.
