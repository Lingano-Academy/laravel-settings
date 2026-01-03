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
use Vocabia\LaravelSettings\Services\SettingService;

$settingService = app('setting');

// Get a setting with default value
$value = $settingService->get('app_name', 'My App');

// Get a setting without default
$value = $settingService->get('api_key');
```

### Creating a Setting

```php
use Vocabia\LaravelSettings\Models\Settings;

// Store a simple string value
Settings::create([
    'key' => 'app_name',
    'value' => 'My Application',
    'type' => 'string',
    'description' => 'Application name',
]);

// Store a JSON value
Settings::create([
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
// config/settings.php
'cache' => [
    'enabled' => false, // Disable caching
    // ...
],
```

### Using Different Cache Stores

```php
// config/settings.php
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

## Troubleshooting

### Settings Not Updating
Ensure cache is cleared after updates:
```php
$settingService->clearCache('your_key');
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

