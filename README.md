# Fuse
> This is still in active development. Use with certain pre-caution.

> Requires an intermediate knowledge in Laravel and Software Design Patterns

Collection of Laravel utilities

## Installation
```bash
composer require exylon/fuse
```

### Artisan Helpers
#### `php artisam make:subscriber`

Creates a new Subscriber class based on [Laravel Subscriber](https://laravel.com/docs/master/events#event-subscribers)

| Parameters                | Description                                | Example                           |
| ------------------------- |-------------------------------------------:| :--------------------------------:|
| `name`     | Subscriber class name     | `AuthEventsSubscriber` |

| Options                | Description                                | Example                           |
| `-e|--event=`     | The event class/es being listened for.     | `Illuminate\\Auth\\Events\\Login` |

Basic Usage:

```bash
$ php artisan make:subscriber AuthEventsSubscriber --event="Illuminate\\Auth\\Events\\Login" --event="Illuminate\\Auth\\Events\\Logout" 
```


#### `php artisan make:repository`

Creates a new [repository](https://martinfowler.com/eaaCatalog/repository.html) class

| Parameters                | Description                                | Example                           |
| ------------------------- |-------------------------------------------:| :--------------------------------:|
| `model`     |  Target model     | `User` |

| Options                | Description                                | Example                           |
| ------------------------- |-------------------------------------------:| :--------------------------------:|
| `--no-interface` | By default, a repository interface and an Eloquent implementation will be created. If you wish to use Eloquent implementation solely, add this as option |  |

Basic Usage:
```bash
$ php artisan make:repository User --no-interface 
```

#### `php artisan make:service`

Creates a new [service](https://martinfowler.com/eaaCatalog/serviceLayer.html) class.

| Parameters                | Description                                | Example                           |
| ------------------------- |-------------------------------------------:| :--------------------------------:|
| `name`     |  Service class name     | `UserService` |

| Options                | Description                                | Example                           |
| ------------------------- |-------------------------------------------:| :--------------------------------:|
| `-r|--repository=` | Provides the repository to be used by the service | `UserRepository` |
| `--crud` | Adds create, update and delete methods |  |

Basic Usage:
```bash
$ php artisan make:service UserService -r=UserRepository --crud
```
