# Laravel Winmax4

A Laravel package (compatible with Laravel 10, 11, 12 and 13) that wraps the Winmax4 API. It ships configuration for connecting to Winmax4, console commands and queued jobs that sync Winmax4 data (articles, entities, families, taxes, currencies, warehouses, payment types, document types and documents) into your own database, a set of Eloquent models mapped to that data, and HTTP controllers for interacting with the API. The package also supports two multi-tenancy strategies — license-scoped rows in a single database, and fully separated per-tenant databases — which are designed to be used together (see [Multi-tenancy](#multi-tenancy)).

## Installation

```bash
composer require controlink/laravel-winmax4
```

The package's service provider, `Controlink\LaravelWinmax4\Winmax4ServiceProvider`, is registered automatically via the `extra.laravel.providers` entry in the package's `composer.json` (Laravel package auto-discovery) — no manual registration is needed.

## Publishing

The service provider exposes the following publishable tags (from `Winmax4ServiceProvider::boot()`):

| Tag | Publishes from | Publishes to |
|---|---|---|
| `winmax4-config` | `src/config/winmax4.php` | `config/winmax4.php` |
| `winmax4-migrations` | `src/database/migrations` | `database/migrations` |
| `winmax4-models` | `src/app/Models` | `app/Models/Winmax4` |
| `winmax4-lang` | `src/resources/lang` | `resources/lang` |
| `winmax4-commands` | `src/app/Console/Commands` | `app/Console/Commands/Winmax4` |
| `winmax4-jobs` | `src/app/Jobs` | `app/Jobs/Winmax4` |

Publish everything you need, for example:

```bash
php artisan vendor:publish --tag=winmax4-config
php artisan vendor:publish --tag=winmax4-migrations
php artisan vendor:publish --tag=winmax4-models
php artisan vendor:publish --tag=winmax4-lang
php artisan vendor:publish --tag=winmax4-commands
php artisan vendor:publish --tag=winmax4-jobs
```

### Rewriting the namespace of published files

Because the models, commands and jobs are published as plain PHP files copied into your application, their namespace still points at the package (`Controlink\LaravelWinmax4\...`). Use the `winmax4:namespace-update` command to rewrite it to your application's namespace after publishing the models:

```bash
php artisan winmax4:namespace-update {oldNamespace} {newNamespace}
```

For example:

```bash
php artisan winmax4:namespace-update "Controlink\LaravelWinmax4\app\Models" "App\Models\Winmax4"
```

The command recurses into every subdirectory under `app/Models/Winmax4` (including `Concerns/`), rewriting the namespace in each `.php` file it finds.

### Upgrading an existing install

`vendor:publish` only copies files the first time — `composer update` never touches files that were already published into your application. If you published the models before this package version and are upgrading, your `app/Models/Winmax4` copies still have the old inline `booted()` logic and are missing the new `Concerns/HasWinmax4Connection.php` and `Concerns/HasLicenseScope.php` traits, so the `use_separated_databases` and `connection_name` config keys will silently do nothing even after the package upgrade. To pick up the new traits, re-publish the models with `--force` and re-run the namespace rewrite:

```bash
php artisan vendor:publish --tag=winmax4-models --force
php artisan winmax4:namespace-update {oldNamespace} {newNamespace}
```

Use the same `oldNamespace`/`newNamespace` arguments you originally used (see the example above). `--force` overwrites your published models directory, so commit or diff any local customizations first.

## Configuration reference

All configuration lives in `config/winmax4.php` after publishing (or the package default at `src/config/winmax4.php` if unpublished):

| Key | Env var | Default | What it controls |
|---|---|---|---|
| `use_license` | `WINMAX4_USE_LICENSE` | `false` | Whether the package operates in license-scoped, single-database multi-tenancy mode. When `true`, a license id is required/expected when creating Winmax4 records, and `HasLicenseScope` becomes active on models that use it. |
| `license_session_key` | `WINMAX4_LICENSE_SESSION_KEY` | `licenseID` | The session key used to read the current tenant's license id (via `session(...)`) when auto-filling new records and when scoping queries. |
| `license_is_uuid` | `WINMAX4_LICENSE_IS_UUID` | `false` | Whether the license identifier is a UUID rather than an integer id. |
| `license_column` | `WINMAX4_LICENSE_COLUMN` | `license_id` | The column name used on Winmax4 tables to store the owning license/tenant id, and used by `LicenseScope` to filter queries. |
| `licenses_table` | `WINMAX4_LICENSES_TABLE` | `licenses` | The table that holds your application's licenses/tenants. |
| `licenses_model` | `WINMAX4_LICENSES_MODEL` | `App\Models\License` | The Eloquent model class for licenses/tenants, used by relationships such as `Winmax4Setting::tenant()`. |
| `use_soft_deletes` | `WINMAX4_USE_SOFT_DELETES` | `false` | Whether sync commands soft-delete (deactivate) records that no longer exist upstream instead of force-deleting them. |
| `use_separated_databases` | `WINMAX4_USE_SEPARATED_DATABASES` | `false` | Whether every package model should resolve its connection from `connection_name` instead of the application's default connection (per-tenant database mode). Also disables the `HasLicenseScope` global scope and its creating-hook auto-fill. Requires `connection_name` to also be set (see below), and works best with `use_license` left `true` so tenant tables still have the `license_id` column (see [Multi-tenancy](#multi-tenancy)). |
| `connection_name` | `WINMAX4_CONNECTION_NAME` | `null` | The name of the database connection (as configured in `config/database.php`) that package models should use when `use_separated_databases` is `true`. **Required when `use_separated_databases` is `true`**: `HasWinmax4Connection::getConnectionName()` throws a `RuntimeException` if `use_separated_databases` is `true` and `connection_name` is empty/null, instead of silently falling back to the application's default connection. |
| `verify_ssl_guzzle` | `WINMAX4_VERIFY_SSL_GUZZLE` | `true` | Whether the Guzzle HTTP client verifies the Winmax4 API's SSL certificate. |
| `queue` | `WINMAX4_QUEUE` | `winmax4` | The queue name that sync jobs are dispatched to. |

## Multi-tenancy

The package supports two multi-tenancy strategies, license scoping and separated databases, and they are meant to be combined rather than treated as alternatives. **When using `use_separated_databases`, keep `use_license` set to `true` as well.** The migrations only add the `license_id` column (named by `license_column`) to package tables when `use_license` is `true` at migration time; every package controller filters its queries by that column (`Winmax4Setting::where(config('winmax4.license_column'), ...)`), so if the column is missing you get a missing-column SQL error on every package route. Turning on `use_separated_databases` does not remove the need for that column — it only disables the `HasLicenseScope` global scope and its creating-hook auto-fill (see below), because tenant isolation is already handled at the connection level once separated databases are in use.

### License scoping (single database)

When `use_license` is `true`, every model that uses the `HasLicenseScope` trait (`Controlink\LaravelWinmax4\app\Models\Concerns\HasLicenseScope`) automatically:

- Registers a global scope (`LicenseScope`) that filters every query by `where(config('winmax4.license_column'), session(config('winmax4.license_session_key')))`.
- Fills the license column on new records from `session(config('winmax4.license_session_key'))` on creation.
- Skips this behavior entirely when running in the console (`app()->runningInConsole()`), so Artisan commands and jobs — which have no HTTP session — can manage all tenants' data explicitly (typically via an explicit `license_id` column filter or option, e.g. `--license_id`).

Example:

```php
// config/winmax4.php (or .env)
// WINMAX4_USE_LICENSE=true

// In an HTTP request/controller, once the tenant is known:
session(['licenseID' => $tenantId]);

// Automatically scoped to the current tenant's license id:
$articles = Winmax4Article::all();
```

### Separated databases (per-tenant database)

When `use_separated_databases` is `true`, every package model uses the `HasWinmax4Connection` trait (`Controlink\LaravelWinmax4\app\Models\Concerns\HasWinmax4Connection`) to resolve its database connection from `config('winmax4.connection_name')` instead of the application's default connection. `connection_name` must be set in this mode: `getConnectionName()` throws a `RuntimeException` if `use_separated_databases` is `true` and `connection_name` is empty/null, so a missing connection name fails fast instead of silently querying the wrong (central) database.

In this mode, `HasLicenseScope` automatically skips registering the license global scope and its creating-hook auto-fill (they only activate when `use_separated_databases` is `false`), since tenant isolation is already handled at the connection level. **This does not remove the need for the `license_id` column itself** — keep `use_license` set to `true` too, so the migrations still create that column on your tenant databases (package controllers query it directly and error on a missing column). In short: for separated databases, run with `use_license=true`, `use_separated_databases=true`, and a valid `connection_name`.

> **Important:** Leaving `connection_name` unset while `use_separated_databases` is `true` throws a `RuntimeException` from `HasWinmax4Connection::getConnectionName()` — the package fails fast rather than silently falling back to the default connection with tenant isolation only partially in effect. Set both config keys together, and keep `use_license` set to `true` so the `license_id` column exists on tenant tables (see above).

Example — the host application resolves the tenant and configures a connection before touching any `Winmax4*` model:

```php
config(['database.connections.tenant' => [
    'driver' => 'mysql',
    'host' => $tenant->db_host,
    'database' => $tenant->db_database,
    'username' => $tenant->db_username,
    'password' => $tenant->db_password,
    // ...other connection options
]]);

config(['winmax4.connection_name' => 'tenant']);

// Queries the tenant connection configured above:
$articles = Winmax4Article::all();
```

## Models

Package models live under `Controlink\LaravelWinmax4\app\Models`. All models use `HasWinmax4Connection`; models whose rows belong to a specific tenant also use `HasLicenseScope`.

**Settings**
- `Winmax4Setting` — table `winmax4_settings`. Holds per-license Winmax4 API credentials/config. Relationship: `tenant()` (belongs to the configured `licenses_model`).

**Articles**
- `Winmax4Article` — table `winmax4_articles`. Relationships: `family()`, `subFamily()`, `subSubFamily()`, `saleTaxes()`, `purchaseTaxes()`, `prices()`, `stocks()`, `details()`.
- `Winmax4ArticleSaleTaxes` — table `winmax4_articles_sale_taxes`. Relationship: `article()`.
- `Winmax4ArticlePurchaseTaxes` — table `winmax4_articles_purchase_taxes`. Relationship: `article()`.
- `Winmax4ArticlePrices` — table `winmax4_articles_prices`. Relationship: `article()`.
- `Winmax4ArticleStocks` — table `winmax4_articles_stocks`. Relationship: `article()`.

**Entities**
- `Winmax4Entity` — table `winmax4_entities`. Customers/suppliers and other Winmax4 entities.

**Families**
- `Winmax4Family` — table `winmax4_families`. Relationship: `subFamilies()`.
- `Winmax4SubFamily` — table `winmax4_sub_families`. Relationships: `family()`, `subSubFamilies()`.
- `Winmax4SubSubFamily` — table `winmax4_sub_sub_families`. Relationship: `subFamily()`.

**Taxes**
- `Winmax4Tax` — table `winmax4_taxes`. Relationship: `taxRates()`.
- `Winmax4TaxRates` — table `winmax4_taxes_rates`. Relationship: `tax()`.

**Currencies**
- `Winmax4Currency` — table `winmax4_currencies`.

**Warehouses**
- `Winmax4Warehouse` — table `winmax4_warehouses`.

**Payment Types**
- `Winmax4PaymentType` — table `winmax4_payment_types`.

**Documents**
- `Winmax4DocumentType` — table `winmax4_document_types`.
- `Winmax4Document` — table `winmax4_documents`. Relationships: `documentType()`, `currency()`, `sourceWarehouse()`, `targetWarehouse()`, `entity()`, `documentTax()`, `paymentTypes()`.
- `Winmax4DocumentDetail` — table `winmax4_document_details`. Relationships: `document()`, `article()`, `tax()`, `taxRate()`.
- `Winmax4DocumentDetailTax` — table `winmax4_document_details_taxes`. Relationship: `documentDetail()`.
- `Winmax4DocumentTax` — table `winmax4_document_taxes`. Relationship: `document()`.
- `Winmax4DocumentPaymentTypes` — table `winmax4_document_payments`. Relationship: `document()`.
- `Winmax4DocumentRelation` — table `winmax4_documents_relation`. Relationships: `document()`, `relatedDocument()`.

**Sync status/errors**
- `Winmax4SyncStatus` — table `winmax4_sync_statuses`. Tracks the last-synced timestamp per model (and per license, when licensing is enabled).
- `Winmax4SyncErrors` — table `winmax4_sync_errors`. Stores errors raised while syncing (uses `HasWinmax4Connection` only; its own scoping logic mirrors `HasLicenseScope`, adding the license global scope directly in `booted()`).

## Usage examples

Reading settings:

```php
use Controlink\LaravelWinmax4\app\Models\Winmax4Setting;

$setting = Winmax4Setting::first();
```

Reading an article with its relations:

```php
use Controlink\LaravelWinmax4\app\Models\Winmax4Article;

$article = Winmax4Article::with(['family', 'saleTaxes', 'prices', 'stocks'])->find($id);
```

Dispatching a sync job directly:

```php
use Controlink\LaravelWinmax4\app\Jobs\SyncArticlesJob;

SyncArticlesJob::dispatch($apiArticlePayload, $licenseId);
```

### Sync console commands

Each command loops over every `Winmax4Setting` (or a single one, via `--license_id`) and syncs the corresponding Winmax4 data:

```bash
# Sync articles. --fullSync also deactivates/deletes local articles no longer present in Winmax4.
php artisan winmax4:sync-articles {--license_id=} {--fullSync}
php artisan winmax4:sync-articles --license_id=1 --fullSync

# Sync entities (customers/suppliers). --fullSync also deactivates/deletes local entities no longer present in Winmax4.
php artisan winmax4:sync-entities {--license_id=} {--fullSync}

# Sync families/sub-families/sub-sub-families.
php artisan winmax4:sync-families {--license_id=}

# Sync currencies.
php artisan winmax4:sync-currencies {--license_id=}

# Sync taxes and their rates.
php artisan winmax4:sync-taxes {--license_id=}

# Sync warehouses.
php artisan winmax4:sync-warehouses {--license_id=}

# Sync payment types.
php artisan winmax4:sync-payment-types {--license_id=}

# Sync document types.
php artisan winmax4:sync-document-types {--license_id=}

# Sync documents, their details, taxes and payments.
php artisan winmax4:sync-documents {--license_id=}
```

Passing `--license_id` while `use_license` is `false` makes the command error out. Omitting `--license_id` syncs every `Winmax4Setting` row (i.e. every configured license/tenant).

### Full table reference

| Table |
|---|
| `winmax4_settings` |
| `winmax4_articles` |
| `winmax4_articles_sale_taxes` |
| `winmax4_articles_purchase_taxes` |
| `winmax4_articles_prices` |
| `winmax4_articles_stocks` |
| `winmax4_families` |
| `winmax4_sub_families` |
| `winmax4_sub_sub_families` |
| `winmax4_taxes` |
| `winmax4_taxes_rates` |
| `winmax4_currencies` |
| `winmax4_entities` |
| `winmax4_warehouses` |
| `winmax4_payment_types` |
| `winmax4_document_types` |
| `winmax4_documents` |
| `winmax4_document_details` |
| `winmax4_document_details_taxes` |
| `winmax4_document_taxes` |
| `winmax4_document_payments` |
| `winmax4_documents_relation` |
| `winmax4_sync_statuses` |
| `winmax4_sync_errors` |

## Queues

Only `winmax4:sync-articles`, `winmax4:sync-entities` and `winmax4:sync-families` dispatch queued jobs (`SyncArticlesJob`, `SyncEntitiesJob`, `SyncFamiliesJob`, batched via `Bus::batch(...)`) — the other sync commands (`sync-currencies`, `sync-taxes`, `sync-warehouses`, `sync-payment-types`, `sync-document-types`, `sync-documents`) write to the database synchronously and don't need a worker. The three job-dispatching commands send their batches to the queue configured by `config('winmax4.queue')` (default `winmax4`), so a worker must be listening on that queue for those jobs to be processed:

```bash
php artisan queue:work --queue=winmax4
```

## License

Proprietary — see `composer.json`.
