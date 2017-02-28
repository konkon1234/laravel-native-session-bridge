<?php

namespace Konkon1234\LaravelNativeSessionBridge;

use Illuminate\Database\MySqlConnection;
use Illuminate\Encryption\Encrypter;
use Illuminate\Session\EncryptedStore;
use Illuminate\Session\Store;
use Illuminate\Support\Str;
use SessionHandlerInterface;

/**
 * Class LaravelFormatSessionHandlerClient
 * @package Konkon1234\LaravelNativeSessionBridge
 */
class LaravelFormatSessionHandlerClient implements SessionHandlerInterface
{
    /**
     * @var LaravelFormatSessionHandler
     */
    private $handler;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $database_name;

    /**
     * @var \Illuminate\Session\Store
     */
    private $store;

    /**
     * @var array
     */
    private $config;

    /**
     * @var bool
     */
    private $ready = false;

    /**
     * LaravelStandaloneDbSession constructor.
     * @param \PDO $pdo conntected pdo object.
     * @param string $database_name Name of the database containing the session table
     * @param array $config override default config setting
     */
    public function __construct($pdo, $database_name, $config = [])
    {
        $this->pdo = $pdo;
        $this->database_name = $database_name;

        $this->config = array_replace($this->getDefaultConfig(), array_filter($config, 'strlen'));

        $this->setup();
    }

    /**
     * @return array default config values
     */
    protected function getDefaultConfig()
    {
        return [
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
        ];
    }

    /**
     * create encrypter.
     * require app_key setting.
     *
     * @return Encrypter
     */
    protected function getEncrypter()
    {
        if (is_null($this->config['app_key'])) {
            throw new \LogicException("If encrypt is true, please set app_key");
        }

        $config_key = $this->config['app_key'];
        $cipher = $this->config['cipher'];

        if (Str::startsWith($key = $config_key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return new Encrypter($key, $cipher);
    }

    /**
     * @return \Illuminate\Database\MySqlConnection
     */
    private function createConnection()
    {
        return new MySqlConnection($this->pdo, $this->database_name);
    }

    /**
     * Setup instance. called from constractor.
     */
    protected function setup()
    {
        if ($this->ready) {
            return;
        }
        $this->ready = true;

        $cookie_name = $this->config['cookie'];
        $this->handler = new LaravelFormatSessionHandler($this->createConnection(), $this->config['table'], $this->config['lifetime']);

        $this->handler->gc($this->config['lifetime'] * 60);

        if ($this->config['encrypt']) {
            $this->store = new EncryptedStore($cookie_name, $this->handler, $this->getEncrypter());
        } else {
            $this->store = new Store($cookie_name, $this->handler);
        }

        $session_id = $this->getSessionId();

        if (is_null($session_id)) {
            $session_id = $this->store->getId();
            setcookie(
                $cookie_name,
                $this->getEncrypter()->encrypt($session_id),
                0,
                $this->config['path'],
                $this->config['domain'],
                $this->config['secure'],
                $this->config['http_only']
            );
        } else {
            $this->store->setId($session_id);
        }

        $this->store->start();
        register_shutdown_function([$this->store, 'save']);
    }

    /**
     * @return null|string Null if the session is inactive
     */
    private function getSessionId()
    {
        $cookie_name = $this->config['cookie'];

        if (!isset($_COOKIE[$cookie_name])) {
            return null;
        }

        $session_id = $this->getEncrypter()->decrypt($_COOKIE[$cookie_name]);
        $session = $this->createConnection()->table($this->config['table'])->find($session_id);
        if (!isset($session->payload)) {
            return null;
        }

        return $session_id;
    }

    /**
     * close session event handler
     * @return mixed
     */
    public function close() {
        return $this->transferHandlerEvent(__FUNCTION__);
    }

    /**
     * destroy session event handler
     * @param string $session_id
     * @return mixed
     */
    public function destroy($session_id) {
        return $this->transferHandlerEvent(__FUNCTION__, [$session_id]);
    }

    /**
     * gc session event handler
     * @param int $maxlifetime
     * @return mixed
     */
    public function gc($maxlifetime) {
        return $this->transferHandlerEvent(__FUNCTION__, [$maxlifetime]);
    }

    /**
     * open session event handler
     * @param string $save_path
     * @param string $session_name
     * @return mixed
     */
    public function open($save_path, $session_name) {
        return $this->transferHandlerEvent(__FUNCTION__, [$save_path, $session_name]);
    }

    /**
     * read session event handler
     * @param string $session_id
     * @return mixed
     */
    public function read($session_id) {
        $result = $this->transferHandlerEvent(__FUNCTION__, [$session_id]);

        foreach($this->store->all() as $key => $value) {
            $_SESSION[$key] = $value;
        }

        return $result;
    }

    /**
     * write session event handler
     * @param string $session_id
     * @param string $session_data
     * @return mixed
     */
    public function write($session_id, $session_data) {
        return $this->transferHandlerEvent(__FUNCTION__, [$this->getEncrypter()->decrypt($session_id), $session_data]);
    }

    /**
     * @param $func
     * @param array $args
     * @return mixed
     */
    private function transferHandlerEvent($func, $args = []) {
        return call_user_func_array([$this->handler, $func], $args);
    }
}