# Fuse
> This is still in active development. Use with certain pre-caution.

> Requires an intermediate knowledge in Laravel and Software Design Patterns

Collection of Laravel utilities

## Installation
```bash
composer require exylon/fuse
```

## Artisan Helpers
### `php artisan make:subscriber`

Creates a new Subscriber class based on [Laravel Subscriber](https://laravel.com/docs/master/events#event-subscribers)

| Parameters                | Description                                | Example                           |
| ------------------------- |:-------------------------------------------| :--------------------------------:|
| `name`     | Subscriber class name     | `AuthEventsSubscriber` |

| Options                | Description                                | Example                           |
| ------------------------- | :------------------------------------------- | :--------------------------------:|
| <code>-e&#124;--event=</code>     | The event class/es being listened for.   | `Illuminate\\Auth\\Events\\Login` |

Basic Usage:

```bash
$ php artisan make:subscriber AuthEventsSubscriber --event="Illuminate\\Auth\\Events\\Login" --event="Illuminate\\Auth\\Events\\Logout" 
```


### `php artisan make:repository`

Creates a new [repository](https://martinfowler.com/eaaCatalog/repository.html) class

| Parameters                | Description                                | Example                           |
| ------------------------- | :-------------------------------------------| :--------------------------------:|
| `model`     |  Target model     | `User` |

| Options                | Description                                | Example                           |
| ------------------------- | :-------------------------------------------| :--------------------------------:|
| `--no-interface` | By default, a repository interface and an Eloquent implementation will be created. If you wish to use Eloquent implementation solely, add this as option |  |

Basic Usage:
```bash
$ php artisan make:repository User --no-interface 
```

### `php artisan make:service`

Creates a new [service](https://martinfowler.com/eaaCatalog/serviceLayer.html) class.

| Parameters                | Description                                | Example                           |
| ------------------------- | :-------------------------------------------| :--------------------------------:|
| `name`     |  Service class name     | `UserService` |

| Options                | Description                                | Example                           |
| ------------------------- |:-------------------------------------------| :--------------------------------:|
| <code>-r&#124;--repository=</code> | Provides the repository to be used by the service | `UserRepository` |
| `--crud` | Adds create, update and delete methods |  |

Basic Usage:
```bash
$ php artisan make:service UserService -r=UserRepository --crud
```

## Helper Functions

### `str_replace_assoc(array $pairs, $subject)`
String replace using key-value pair (assoc array).

### `validate(array $data, array $rules)`
Shorthand for `\Validator::validate($data, $rules)`

### `random_hex_string($length)`
Generates a random hexadecimal string with fixed length.

Example: `$var = random_hex_string(10) //ffeb09ed56`

### `random_int_string($length, $min = 0, $pad = '0')`
Generates a random numeric string. If the generated numeric string is shorter than the `$length`, it will be padded by the `$pad`.

Example: `$var = random_int_string(5) //01467`

### `snake_to_title_case($str)`
Converts a snake-cased formatted string to title case

Example: `$var = snake_to_title('lorem_ipsum_dolor') // Lorem Ipsum Dolor`
