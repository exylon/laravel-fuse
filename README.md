# Fuse
> This is still in active development. Use with certain pre-caution.

> Requires an intermediate knowledge in Laravel and Software Design Patterns

Collection of Laravel utilities

## Installation
```bash
composer require exylon/fuse
```

## Artisan Helpers
#### `php artisan make:subscriber`

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


#### `php artisan make:repository`

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

#### `php artisan make:service`

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

<br/>

## Helper Functions

#### `str_replace_assoc(array $pairs, $subject)`
String replace using key-value pair (assoc array).

#### `validate(array $data, array $rules)`
Shorthand for `\Validator::validate($data, $rules)`

#### `random_hex_string($length)`
Generates a random hexadecimal string with fixed length.

Example: `$var = random_hex_string(10) //ffeb09ed56`

#### `random_int_string($length, $min = 0, $pad = '0')`
Generates a random numeric string. If the generated numeric string is shorter than the `$length`, it will be padded by the `$pad`.

Example: `$var = random_int_string(5) //01467`

#### `snake_to_title_case($str)`
Converts a snake-cased formatted string to title case

Example: `$var = snake_to_title('lorem_ipsum_dolor') // Lorem Ipsum Dolor`

<br/>

## The `\Exylon\Fuse\Support\Attributes` class

Associative array on steroids. 
Converts regular associative array to standard objects
```php
$arr = new Attributes([
              'red'    => 'apple',
              'orange' => 'orange',
              'yellow' => [
                  'mangoes' => 'foo',
                  'pear'    => 'bar'
              ]
          ]);

echo $arr['red']; // "apple"
echo $arr->red; // "apple"

echo $arr['yellow']['mangoes']; // "foo"
echo $arr->yellow->mangoes; // "foo"
```

Working with aliases.
*Note: Currently, aliases supports the first level keys only*

```php
$arr = new Attributes([
              'red'    => 'apple',
              'orange' => 'orange',
              'yellow' => [
                  'mangoes' => 'foo',
                  'pear'    => 'bar'
              ]
          ],[ // 'pula' as an alias for 'red'
              'pula'   => 'red'
          ]);

echo $arr['red']; // "apple"
echo $arr->red; // "apple"

echo $arr['pula']; // "apple"
echo $arr->pula; // "apple"
```

#### `Attributes::toCollection()`
Converts the attributes to `Illuminate\Support\Collection`

#### `Attributes::toJson($options=0)`
Converts the attributes to json

<br/>

## Helper Macros

#### `Request::location()`
Using [`torann/geoip`](http://lyften.com/projects/laravel-geoip/doc/),
```php
\Exylon\Fuse\Support\Attributes {
  #attributes: array [
    "ip" => "127.0.0.0"
    "iso_code" => "PH"
    "country" => "Philippines"
    "city" => "Paranaque"
    "state" => "MNL"
    "state_name" => "Manila"
    "postal_code" => "06510"
    "lat" => 14.471016
    "lon" => 121.01476
    "timezone" => "Asia/Manila"
    "continent" => "NA"
    "currency" => "USD"
    "default" => true
    "cached" => false
  ]
  #aliases: array [
    "country_code" => "iso_code"
    "latitude" => "lat"
    "longitude" => "lon"
    "zip_code" => "postal_code"
  ]
}
```

#### `Request::agent()`
Using [`jenssegers/agent`](https://github.com/jenssegers/agent)
```php
\Exylon\Fuse\Support\Attributes {
  #attributes: array [
    "agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36"
    "is_mobile" => false
    "is_phone" => false
    "is_tablet" => false
    "device" => "Macintosh"
    "is_desktop" => true
    "platform" => "OS X"
    "is_robot" => false
    "robot" => false
    "browser" => "Chrome"
    "languages" => array:2 [▶]
  ]
}
```


#### `Builder::forceMake($attributes)`

Coincides with Eloquent Models' `forceCreate`, this method creates an instance of the model without persisting and avoiding MassAssignmentException.
*NOTE: Make sure that you pre-validated the attributes.*

```php
$user = User::forceMake(['name'=>'John Doe']);
```
