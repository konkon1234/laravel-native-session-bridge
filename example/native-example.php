<?php
/**
 * Example of Native application handling Laravel format session.
 *
 */
ini_set('display_errors', 1);

require_once(__DIR__.'/../vendor/autoload.php');

$db_host = 'DB_HOST';
$db_name = 'DB_NAME';
$db_port = 3306;
$db_user = 'DB_USER';
$db_pass = 'DB_PASS';

// same set value laravel .env APP_KEY value
$laravel_app_key = 'base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

$pdo = new \PDO("mysql:host={$db_host};dbname={$db_name};port={$db_port};charset=utf8", $db_user, $db_pass);
$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


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
// $request->session()->get('from_native') // message from native!!!!!
$_SESSION['from_native'] = 'message from native!!!!!';
