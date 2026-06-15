<?php
namespace App\Core;

class App
{
    private static ?App $instance = null;
    private array $config;
    private ?Database $db = null;
    private ?Router $router = null;

    private function __construct()
    {
        $this->config = require APP_PATH . '/Config/config.php';
        date_default_timezone_set($this->config['app']['timezone']);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        return $value;
    }

    public function db(): Database
    {
        if ($this->db === null) {
            $this->db = new Database($this->config['database']);
        }
        return $this->db;
    }

    public function router(): Router
    {
        if ($this->router === null) {
            $this->router = new Router();
        }
        return $this->router;
    }

    public function run(): void
    {
        session_name($this->config['session']['name']);
        session_start();

        $this->router()->dispatch();
    }
}
