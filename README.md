# Laravel Settings

A powerful, flexible global settings package for Laravel microservices. Manage application configurations with database persistence and optional caching support.

[![PHP Version](https://img.shields.io/badge/PHP-%5E8.1-777BB4?logo=php)](https://www.php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-%5E10.0%7C%5E11.0%7C%5E12.0-FF2D20?logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## Overview

Laravel Settings provides a centralized way to manage application-wide settings in Laravel applications. Perfect for microservices architectures, it stores settings in a database table with built-in support for:

- **Database Persistence**: Store settings securely in your database
- **Optional Caching**: Leverage Redis or file caching for improved performance
- **JSON Support**: Store complex data structures as JSON
- **Type Flexibility**: Support for multiple data types (string, json, etc.)
- **Configuration Management**: Easily customizable table names and cache behavior

## Features

✨ **Key Capabilities:**

- Store and retrieve global application settings
- Support for both simple string values and complex JSON data
- Configurable caching with multiple store options (Redis, File, etc.)
- Automatic cache invalidation
- Easy integration with Laravel's service container
- ULID primary keys for modern database design
- Full Laravel 10, 11, and 12 compatibility

## Requirements

- PHP 8.1 or higher
- Laravel 10.0, 11.0, or 12.0
- A relational database (MySQL, PostgreSQL, SQLite, etc.)

## Installation

### Via Composer

```bash
composer require vocabia/laravel-settings
```

The package will auto-register with Laravel's service provider discovery.

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=settings-config
```

Publish the migration:

```bash
php artisan vendor:publish --tag=settings-migrations
```

Run migrations:

```bash
php artisan migrate
```

## Artisan Commands

### Setup Commands

#### Publish Configuration Files

```bash
# Publish the settings configuration file
php artisan vendor:publish --tag=settings-config

# This creates config/vocabia_settings.php in your application
```

#### Publish and Run Migrations

```bash
# Publish database migrations
php artisan vendor:publish --tag=settings-migrations

# Run all pending migrations (including settings table)
php artisan migrate

# Run migrations with step-by-step feedback
php artisan migrate --step

# Rollback the last batch of migrations
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Rollback all migrations and run them again
php artisan migrate:refresh

# Rollback and re-run all migrations with seeders
php artisan migrate:refresh --seed
```

### Database Interaction via Tinker

```bash
# Open the interactive shell
php artisan tinker

# Inside tinker, you can manage settings:
>>> $setting = new \Vocabia\LaravelSettings\Models\VocabiaSettings();
>>> $setting->key = 'app_name';
>>> $setting->value = 'My Application';
>>> $setting->type = 'string';
>>> $setting->description = 'Application name';
>>> $setting->save();

# Or use create method:
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::create([
...     'key' => 'api_key',
...     'value' => 'secret-key-123',
...     'type' => 'string',
...     'description' => 'API Key'
... ]);

# Query settings:
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::all();
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::where('key', 'app_name')->first();

# Clear cache for a setting:
>>> app('setting')->clearCache('app_name');
```

### Cache Management Commands

```bash
# Clear all cache
php artisan cache:clear

# Clear specific cache tags (settings)
php artisan cache:clear --tags

# Forget a specific cache key
php artisan cache:forget setting_app_name

# Flush all cache (Redis, File, etc.)
php artisan cache:flush
```

### Development Commands

```bash
# Check the database status
php artisan db:show

# List all tables
php artisan db:table settings

# Create a backup before making changes
php artisan db:wipe

# View pending migrations
php artisan migrate:status
```

### Model Inspection

```bash
# Open tinker to inspect the Settings model
php artisan tinker

# Show all columns in settings table
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::first()?->getAttributes();

# Count all settings
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::count();

# Get column information
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::all()->map(fn($s) => $s->only(['key', 'type', 'created_at']));
```

### Practical Artisan Workflows

#### Initial Setup

```bash
# Install package
composer require vocabia/laravel-settings

# Publish configuration and migrations
php artisan vendor:publish --tag=settings-config
php artisan vendor:publish --tag=settings-migrations

# Run migrations to create settings table
php artisan migrate

# Verify table creation
php artisan db:table settings
```

#### Add New Settings via Tinker

```bash
php artisan tinker

# Add API rate limit setting
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::create([
...     'key' => 'api.rate_limit',
...     'value' => '1000',
...     'type' => 'string',
...     'description' => 'API requests per hour'
... ]);

# Add feature flags
>>> \Vocabia\LaravelSettings\Models\VocabiaSettings::create([
...     'key' => 'features.new_ui',
...     'json_value' => ['enabled' => true, 'beta' => false],
...     'type' => 'json',
...     'description' => 'New UI feature toggle'
... ]);

# Exit tinker
>>> exit
```

#### Update Settings and Clear Cache

```bash
php artisan tinker

# Update a setting
>>> $setting = \Vocabia\LaravelSettings\Models\VocabiaSettings::where('key', 'api.rate_limit')->first();
>>> $setting->update(['value' => '2000']);
>>> app('setting')->clearCache('api.rate_limit');

# Exit tinker
>>> exit

# Clear all settings cache
php artisan cache:clear
```

#### Export/Backup Settings

```bash
# Inside tinker, export all settings to JSON
php artisan tinker

>>> $settings = \Vocabia\LaravelSettings\Models\VocabiaSettings::all()
...     ->map(fn($s) => ['key' => $s->key, 'value' => $s->value, 'type' => $s->type])
...     ->toJson();
>>> file_put_contents('settings_backup.json', $settings);
```

## Configuration

The configuration file is located at `config/settings.php`. Here's what you can customize:

```php
return [
    // The database table name for storing settings
    'table_name' => 'settings',

    // Cache configuration
    'cache' => [
        'enabled' => true,           // Enable/disable caching
        'store'   => 'redis',        // Cache store (redis, file, etc.)
        'ttl'     => 3600,           // Time to live in seconds (1 hour)
        'prefix'  => 'setting_',     // Cache key prefix
    ],
];
```

## Usage

### Getting a Setting

```php
use Vocabia\LaravelSettings\Services\VocabiaSettingService;

$settingService = app('setting');

// Get a setting with default value
$value = $settingService->get('app_name', 'My App');

// Get a setting without default
$value = $settingService->get('api_key');
```

### Creating a Setting

```php
use Vocabia\LaravelSettings\Models\VocabiaSettings;

// Store a simple string value
VocabiaSettings::create([
    'key' => 'app_name',
    'value' => 'My Application',
    'type' => 'string',
    'description' => 'Application name',
]);

// Store a JSON value
VocabiaSettings::create([
    'key' => 'feature_flags',
    'json_value' => [
        'new_ui' => true,
        'beta_api' => false,
    ],
    'type' => 'json',
    'description' => 'Feature flags configuration',
]);
```

### Updating a Setting

```php
$setting = Settings::where('key', 'app_name')->first();
$setting->update([
    'value' => 'Updated App Name',
]);

// Clear cache after update
$settingService->clearCache('app_name');
```

### Deleting a Setting

```php
Settings::where('key', 'app_name')->delete();

// Clear cache
$settingService->clearCache('app_name');
```

### Facade Usage (Optional)

Create a facade for easier access:

```php
// In your service provider or config/app.php aliases
'Setting' => Vocabia\LaravelSettings\Facades\SettingFacade::class,

// Usage
Setting::get('app_name');
```

## Database Schema

The `settings` table includes the following columns:

| Column | Type | Description |
|--------|------|-------------|
| `id` | ULID | Primary key |
| `key` | String | Unique setting identifier (indexed) |
| `value` | Text | String value for the setting |
| `json_value` | JSONB | JSON value for complex data |
| `description` | String | Setting description |
| `type` | String | Data type (string, json, etc.) |
| `created_at` | Timestamp | Creation timestamp |
| `updated_at` | Timestamp | Update timestamp |

## Cache Configuration

### Enable/Disable Caching

```php
// config/vocabia_settings.php
'cache' => [
    'enabled' => false, // Disable caching
    // ...
],
```

### Using Different Cache Stores

```php
// config/vocabia_settings.php
'cache' => [
    'enabled' => true,
    'store' => 'redis',    // Use Redis
    'ttl' => 3600,
    'prefix' => 'setting_',
],

// Or use file cache
'store' => 'file',
```

### Cache Prefix

Customize the cache key prefix to avoid conflicts:

```php
'cache' => [
    'prefix' => 'app_settings_',
],
```

## Advanced Usage

### Retrieving JSON Settings

```php
$settings = app('setting');

// Get JSON value as array
$flags = $settings->get('feature_flags');
// Returns: ['new_ui' => true, 'beta_api' => false]

// Update JSON value
$setting = Settings::where('key', 'feature_flags')->first();
$setting->json_value = ['new_ui' => true, 'beta_api' => true];
$setting->save();
$settings->clearCache('feature_flags');
```

### Querying Settings

```php
// Get all settings
$allSettings = Settings::all();

// Filter by type
$jsonSettings = Settings::where('type', 'json')->get();

// Search by key
$setting = Settings::where('key', 'like', '%api%')->first();

// Paginate settings
$paginated = Settings::paginate(15);
```

## Best Practices

1. **Use Descriptive Keys**: Use clear, hierarchical naming for settings keys
   ```php
   'mail.smtp.host'
   'features.new_dashboard'
   'api.rate_limit'
   ```

2. **Document Your Settings**: Always include descriptions
   ```php
   Settings::create([
       'key' => 'api.rate_limit',
       'value' => '1000',
       'description' => 'API rate limit per hour',
   ]);
   ```

3. **Cache Strategic Settings**: Cache frequently accessed settings
   ```php
   'cache' => [
       'enabled' => true,
       'ttl' => 86400, // Cache for 24 hours
   ]
   ```

4. **Invalidate Cache Appropriately**: Always clear cache when updating
   ```php
   $setting->update(['value' => $newValue]);
   $settingService->clearCache($setting->key);
   ```

5. **Use Type Field**: Indicate the setting type for clarity
   ```php
   'type' => 'string', 'json', 'boolean', etc.
   ```

## Performance Considerations

- **Caching**: Enable caching with Redis for optimal performance in production
- **TTL**: Adjust cache TTL based on how frequently settings change
- **Database Indexes**: The `key` column is indexed for fast lookups
- **ULID Keys**: Uses ULID for better database performance vs UUID

## Quick Command Reference

| Task | Command |
|------|---------|
| **Installation & Setup** | |
| Install package | `composer require vocabia/laravel-settings` |
| Publish config | `php artisan vendor:publish --tag=settings-config` |
| Publish migrations | `php artisan vendor:publish --tag=settings-migrations` |
| Run migrations | `php artisan migrate` |
| **Database & Migrations** | |
| Migrate with steps | `php artisan migrate --step` |
| Rollback last batch | `php artisan migrate:rollback` |
| Migration status | `php artisan migrate:status` |
| View table schema | `php artisan db:table settings` |
| **Settings Management (via Tinker)** | |
| Open interactive shell | `php artisan tinker` |
| Create new setting | `\Vocabia\LaravelSettings\Models\VocabiaSettings::create([...])` |
| Get all settings | `\Vocabia\LaravelSettings\Models\VocabiaSettings::all()` |
| Query by key | `\Vocabia\LaravelSettings\Models\VocabiaSettings::where('key', 'name')->first()` |
| Count settings | `\Vocabia\LaravelSettings\Models\VocabiaSettings::count()` |
| Delete setting | `\Vocabia\LaravelSettings\Models\VocabiaSettings::where('key', 'name')->delete()` |
| **Cache Management** | |
| Clear all cache | `php artisan cache:clear` |
| Flush cache completely | `php artisan cache:flush` |
| Forget specific key | `php artisan cache:forget setting_app_name` |
| Clear cache with tags | `php artisan cache:clear --tags` |

## Troubleshooting

### Settings Not Updating
Ensure cache is cleared after updates:
```php
$settingService->clearCache('your_key');
```

Or via artisan:
```bash
php artisan cache:clear
```

### Wrong Cache Store
Verify the cache store exists in your `config/cache.php`:
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'connection' => 'default',
],
```

### Migration Issues
Ensure migrations are published and run:
```bash
php artisan vendor:publish --tag=settings-migrations
php artisan migrate
```

Check migration status:
```bash
php artisan migrate:status
```

### Table Not Found
If you get a "table not found" error when running migrations:
```bash
# Publish the migrations if not already done
php artisan vendor:publish --tag=settings-migrations

# Run pending migrations
php artisan migrate

# Verify the table was created
php artisan db:table settings
```

### Tinker Command Not Found
Ensure Laravel Tinker is installed:
```bash
composer require laravel/tinker
php artisan tinker
```

### Configuration Not Loaded
If the configuration is not being loaded properly:
```bash
# Clear config cache
php artisan config:clear

# Republish the configuration
php artisan vendor:publish --tag=settings-config --force

# Clear application cache
php artisan cache:clear
```

### Settings Cached Incorrectly
To clear all settings-related cache entries:
```bash
php artisan tinker
>>> app('setting')->clearCache('*')
>>> exit

# Or use artisan
php artisan cache:flush
```

## API Reference

### SettingService

#### `get($key, $default = null)`
Retrieves a setting value with optional default.

**Parameters:**
- `$key` (string): The setting key
- `$default` (mixed): Default value if not found

**Returns:** The setting value or default

#### `clearCache($key)`
Clears the cache for a specific setting.

**Parameters:**
- `$key` (string): The setting key

**Returns:** void

### Settings Model

Standard Laravel Eloquent model with:
- ULID primary key
- JSON cast for `json_value` attribute
- Configurable table name

## Contributing

We welcome contributions! Please feel free to submit issues or pull requests.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For issues, questions, or suggestions, please open an issue on the [GitHub repository](https://github.com/vocabia/laravel-settings).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for all version changes and updates.

---

**Built by [Vocabia](https://vocabia.com)** | Made for modern Laravel applications

