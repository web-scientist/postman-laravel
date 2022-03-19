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

1. Create Collection and Environment on Postman workspace

    ```bash
    php artisan postman:create
    ```

2. Export Collection and Environment in `storage/app/postman/`

    ```bash
    php artisan postman:export
    ```

Note: You can use a `--name=` argument to define a custom name for Collection/File. If not specified, the `APP_NAME` will be used from `.env`

## Coventions

The following Laravel conventions are expected to be followed while building your application to get accurate results in your postman collection.
