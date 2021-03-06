# Fuse
> This is still in active development. Use with certain pre-caution.

> Requires an intermediate knowledge in Laravel and Software Design Patterns

Collection of Laravel utilities

## Installation
```bash
composer require exylon/fuse:dev-master
```

## Artisan Helpers
#### `php artisan make:subscriber`

Creates a new Subscriber class based on [Laravel Subscriber](https://laravel.com/docs/master/events#event-subscribers)

| Parameters                | Description                                | Example                           |
| ------------------------- |:-------------------------------------------| :--------------------------------:|
| `name`     | Subscriber class name     | `AuthEventsSubscriber` |

| Options                | Description                                | Example                           |
| ------------------------- | :------------------------------------------- | :--------------------------------:|
| <code>--event=</code>     | The event class/es being listened for.   | `Illuminate\\Auth\\Events\\Login` |

Basic Usage:

```bash
$ php artisan make:subscriber AuthEventsSubscriber --event="Illuminate\\Auth\\Events\\Login" --event="Illuminate\\Auth\\Events\\Logout" 
```

<br/>

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

<br/>

#### `php artisan make:service`

Creates a new [service](https://martinfowler.com/eaaCatalog/serviceLayer.html) class.

| Parameters                | Description                                | Example                           |
| ------------------------- | :-------------------------------------------| :--------------------------------:|
| `name`     |  Service class name     | `UserService` |

| Options                | Description                                | Example                           |
| ------------------------- |:-------------------------------------------| :--------------------------------:|
| <code>--repository=</code> | Provides the repository to be used by the service | `UserRepository` |
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

#### `str_random_hex($length)`
Generates a random hexadecimal string with fixed length.

Example: `$var = str_random_hex(10) //ffeb09ed56`

#### `str_random_int($length, $min = 0, $pad = '0')`
Generates a random numeric string. If the generated numeric string is shorter than the `$length`, it will be padded by the `$pad`.

Example: `$var = str_random_int(5) //01467`

#### `proper_case($str,$delimiters = '_')`
Converts a string to proper cased string

Examples: 
```php
$var = proper_case('lorem_ipsum_dolor') // Lorem Ipsum Dolor
$var = proper_case('lorem_ipsum-dolor') // Lorem Ipsum-Dolor
$var = proper_case('lorem_ipsum-dolor',['_','-']) // Lorem Ipsum Dolor
```

<br/>

## Helper Traits and Classes

### `\Exylon\Fuse\Support\Attributes`

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

#### `Attributes::toJson($options=0)`
Converts the attributes to json

<br/>

### `\Exylon\Fuse\Support\Eloquent\CascadeDelete`
Enables cascade delete on the PHP side. Useful for polymorphic relationships.
```php
/*
 * 'Team' model can also have payment methods. That's the reason
 * PaymentMethod is poloymorphic.
 */
class Customer extends Model {
    use CascadeDelete;
    
    protected $cascade = [
        'paymentMethods'
    ];
    
    public function paymentMethods(){
        return $this->morphMany(PaymentMethod::class,'owner');
    }
}
...
$customer->delete(); // this will delete all related payment methods
```

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

<br/>

## `FuseSanitizer` Facade

Cleans up your data based on rules.

#### Inline Rules
```php

$data = [
    'email' =>  '    EXAMPLE@EXAMPLE.COM    '
];
$data = FuseSanitizer::sanitize($data,[
          '*' =>  'trim::string', // Wildcard
          'email' =>  'strtolower' // Applicable only fields named 'email'
      ]); // ['email' => 'example@example.com']

```
The 'email' rule will be only applicable to fields with name 'email'. Int this case, `strtolower` will be called if there exists an 'email' field from the data.

The `*` signifies a wildcard which makes it applicable to "any" field, but take note that the included rule is `trim::string`. The rule is divided into three parts - *function*, *parameters*, *data type* -, which is separated by colon ('`:`'); *parameters* are further divided by comma ('`,`'). By default, data type will be assumed as string. In our case, for the function `trim` we don't need any parameters but we need to supply a data type to make it applicable only to `string`-typed data. Available data types are `string`,`array`, `int`, `float`, and `double`.

#### Inline Rules with Global Rules
Sample:

```php
FuseSanitizer::setGlobalRules([
    'email' =>  'trim' 
]);
$data = [
    'email' =>  '    EXAMPLE@EXAMPLE.COM    '
];
$data = FuseSanitizer::sanitize($data,[
          'email' =>  'strtolower'
      ]); // ['email' => 'example@example.com']
```

The rules are prioritized in this manner:
1. Global Wildcard 
2. Global Rule
3. Inline Wildcard
4. Inline Rule

If you set the global rules, the inline rules are optional.
Sample:

```php
FuseSanitizer::setGlobalRules([
    'email' =>  ['trim','strtolower']
]);
$data = [
    'email' =>  '    EXAMPLE@EXAMPLE.COM    '
];
$data = FuseSanitizer::sanitize($data); // ['email' => 'example@example.com']
```

#### Sanitizing a single value
Sample:

```php
$email = FuseSanitizer::sanitizeValue('    EXAMPLE@EXAMPLE.COM    ',['trim','strtolower']); // 'example@example.com'
```

#### Rule Set Formats

##### Using pipe (`|`) separated rules
```php
$email = FuseSanitizer::sanitizeValue('    EXAMPLE@EXAMPLE.COM    ','trim|strtolower'); // 'example@example.com'
```


##### Using array
```php
$email = FuseSanitizer::sanitizeValue('    EXAMPLE@EXAMPLE.COM    ', ['trim','strtolower']); // 'example@example.com'
```

##### Using callbacks
```php
$email = FuseSanitizer::sanitizeValue('    EXAMPLE@EXAMPLE.COM    ', ['trim',function($value){
    return strtolower($value);
}]); // 'example@example.com'
```

##### Using class/method pair
```php
$email = FuseSanitizer::sanitizeValue('    EXAMPLE@EXAMPLE.COM    ', ['trim','App\\Support\\SanitizerHelper@toLower']); // 'example@example.com'
```

#### Extending callbacks via `FuseSanitizer::register`
```php
FuseSanitizer::register('tolower',function($value){
  return strtolower($value);
});
$email = FuseSanitizer::sanitizeValue('    EXAMPLE@EXAMPLE.COM    ', ['trim','tolower']); // 'example@example.com'
```
```php
FuseSanitizer::register('tolower','App\\Support\\SanitizerHelper@toLower');
$email = FuseSanitizer::sanitizeValue('    EXAMPLE@EXAMPLE.COM    ', ['trim','tolower']); // 'example@example.com'
```
