# laravel native session bridge

This library allows for bidirectional reading and writing of the Laravel application of Session management using Database and the $ _SESSION object of Native php.

## how to use

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

// get session value from laravel
echo $_SESSION['from_laravel'];

// set session value.
// $request->session()->get('from_native') // message from native
$_SESSION['from_native'] = 'message from native';

```