# Postman Laravel

## Installation

1. Install the package into your laravel application.

    ```bash
    composer require webscientist/postman-laravel --dev
    ```

2. Publish the config file by using the following command.

    ```bash
    php artisan vendor:publish --tag=postman-laravel
    ```

3. Register the Service Provider in `config/app.php`

    ```php
     'providers' => [
        ...
        ...
        WebScientist\PostmanLaravel\PostmanLaravelServiceProvider::class,
    ],
    ```

4. Generate a Postman API key and put it in `.env` (optional)

    ```bash
    POSTMAN_API_KEY=
    ```

## Commands

The following commands can be used to get the Postman Collection

1. Create Collection on Postman workspace

    ```bash
    php artisan postman:create
    ```

    * Use `-e` for creating environment as well

2. Export Collection in `storage/app/postman/`

    ```bash
    php artisan postman:export
    ```

    * Use `-e` for exporting environment file as well

Note: You can use a `name` argument to define a custom name for Collection/File. If not specified, the `APP_NAME` will be used from `.env`

## Configuration

### Postman Environment

By default only a single variable of `BASE_URL` is used in generation of environment file. The same can be added in the `environment.variables` array.

```php
'variables' => [
    [
        'key' => 'BASE_URL',
        'value' => env('APP_URL', ''),
        'type' => 'default',
        'enabled' => true,
    ],
    // Other Variables
]
```

The type can be set to `'default'` or `'secret'`. Secret will hide the value in Postman UI.

### Request Grouping

By default the routes are grouped/nested on the basis of route names. That can be set to a custom key BY changing the `request.group_by` value.

### Route Filtering

Any route having a closure will automatically be filtered out.

#### Inclusions

By default the routes having the `api middleware` will be included. It can be overridden in the `request.inclusion.middleware` array.

#### Exclusion

Certain routes can be excluded by their prefixes by defining them in the `request.exclusion.prefix` array.

## Coventions

The following Laravel conventions are expected to be followed while building your application to get accurate results in your postman collection.

// WIP
