# laravel native session bridge

This library allows for bidirectional reading and writing of the Laravel application of Session management using Mysql Database and the $_SESSION object of Native php.

## Support Version

* php 5.6 or higher
* Laravel 5.3, 5.4

## Usage

set session handler.

example.

```php
$handler = new \Konkon1234\LaravelNativeSessionBridge\LaravelFormatSessionHandlerClient($pdo, $db_name, [
    // Give the same settings as Laravel

    'table' => 'sessions',
    'encrypt' => false,
    'app_key' => null,
    'cipher' => 'AES-256-CBC',
    'cookie' => 'laravel_session',
    'lifetime' => 120,
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'http_only' => true,
]);

ini_set('session.serialize_handler', 'php_serialize'); // require
session_name('laravel_session'); // require
session_set_save_handler($handler, false); // require register_shutdown is false.

session_start();

// You can get the value set in Laravel from $ _SESSION
// $request->session()->put('from_laravel', 'message from laravel');
echo $_SESSION['from_laravel'];

// Once you have set a value for the native session object, you can retrieve it with Laravel
// $request->session()->get('from_native');
$_SESSION['from_native'] = 'message from native';


```

## How to install

composer require konkon1234/laravel-native-session-bridge

## Lisence

MIT Lisence