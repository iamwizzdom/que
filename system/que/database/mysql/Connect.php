<?php

namespace que\database\mysql;

use mysqli;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;

abstract class Connect
{
    /**
     * @var mysqli
     */
    private $conn = null;

    /**
     * @var int
     */
    private $conn_time;

    /**
     * @var string
     */
    private $db_name;

    /**
     * @var string
     */
    private $db_user;

    /**
     * @var string
     */
    private $db_pass;

    /**
     * @var string
     */
    private $db_host;

    /**
     * @var int
     */
    private $db_port;

    /**
     * @var
     */
    private $db_socket;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var bool
     */
    private $transEnabled = true;

    /**
     * @var bool
     */
    private $transSuccessful = true;

    /**
     * @var int
     */
    protected $transDepth = 0;

    /**
     * Connect constructor.
     * @param bool $persist
     */
    protected function __construct(bool $persist = false)
    {
        $this->setDbHost(($persist === true ? "p:" : "") . (CONFIG['database']['mysql']['host'] ?? null));
        $this->setDbName(CONFIG['database']['mysql']['name'] ?? null);
        $this->setDbUser(CONFIG['database']['mysql']['user'] ?? null);
        $this->setDbPass(CONFIG['database']['mysql']['pass'] ?? null);
        $this->setDbPort(CONFIG['database']['mysql']['port'] ?? null);
        $this->setDbSocket(CONFIG['database']['mysql']['socket'] ?? null);
        $this->setDebug((CONFIG['database']['mysql']['debug'] ?? LIVE) && !LIVE);

        // establish connection
        $this->establish_connection();
    }

    /**
     * Establish MySQL connection
     */
    private function establish_connection()
    {

        $this->conn_time = APP_TIME;
        $this->conn = new mysqli($this->getDbHost(), $this->getDbUser(),
            $this->getDbPass(), $this->getDbName(), $this->getDbPort(), $this->getDbSocket());
    }

    /**
     * @return string
     */
    protected function getDbName(): string
    {
        return $this->db_name;
    }

    /**
     * @param string $db_name
     */
    private function setDbName(string $db_name): void
    {
        $this->db_name = $db_name;
    }

    /**
     * @return string
     */
    protected function getDbUser(): string
    {
        return $this->db_user;
    }

    /**
     * @param string $db_user
     */
    protected function setDbUser(string $db_user): void
    {
        $this->db_user = $db_user;
    }

    /**
     * @return string
     */
    protected function getDbPass(): string
    {
        return $this->db_pass;
    }

    /**
     * @param string $db_pass
     */
    protected function setDbPass(string $db_pass): void
    {
        $this->db_pass = $db_pass;
    }

    /**
     * @return string
     */
    protected function getDbHost(): string
    {
        return $this->db_host;
    }

    /**
     * @param string $db_host
     */
    protected function setDbHost(string $db_host): void
    {
        $this->db_host = $db_host;
    }

    /**
     * @return int
     */
    protected function getDbPort(): ?int
    {
        return $this->db_port;
    }

    /**
     * @param int $db_port
     */
    protected function setDbPort(?int $db_port): void
    {
        $this->db_port = $db_port;
    }

    /**
     * @return mixed
     */
    protected function getDbSocket()
    {
        return $this->db_socket;
    }

    /**
     * @param mixed $db_socket
     */
    protected function setDbSocket($db_socket): void
    {
        $this->db_socket = $db_socket;
    }

    /**
     * @return bool
     */
    protected function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    private function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @param string $user
     * @param string $password
     * @param string $database
     * @return bool
     */
    public function changeUser(string $user, string $password, string $database): bool {
        $this->setDbUser($user);
        $this->setDbPass($password);
        $this->setDbName($database);
        return $this->conn->change_user($user, $password, $database);
    }

    /**
     * @param string $dbName
     * @return bool
     */
    public function changeDb(string $dbName): bool {
        $this->setDbName($dbName);
        return $this->conn->select_db($dbName);
    }

    /**
     * @return mysqli
     */
    protected function connect(): mysqli
    {

        // re-establish a new MySQL connection every 1 hour
        if (APP_TIME > ($this->conn_time + TIMEOUT_TRACK))
            $this->establish_connection();

        // check connection error
        if ($this->conn->connect_error)
            throw new QueRuntimeException("Connection failed: {$this->conn->connect_error}",
                "Database Error", E_USER_ERROR, 0, PreviousException::getInstance());

        $limit = 0;

        while (!($ping = @$this->conn->ping()) && $limit < MAX_RETRY) {
            $this->establish_connection();
            $limit++;
        }

        if (!$ping) throw new QueRuntimeException("Failed to establish database connection after {$limit} trials",
            "Database Error", E_USER_ERROR, 0, PreviousException::getInstance());

        return $this->conn;
    }

    /**
     * @param string|null $host
     * @param string|null $username
     * @param string|null $passwd
     * @param string|null $dbname
     * @param int|null $port
     * @param null $socket
     * @return bool
     */
    public function reconnect(
        string $host = null,
        string $username = null,
        string $passwd = null,
        string $dbname = null,
        int $port = null,
        $socket = null
    ): bool {

        $this->setDbHost($host ?? $this->getDbHost());
        $this->setDbName($dbname ?? $this->getDbName());
        $this->setDbUser($username ?? $this->getDbUser());
        $this->setDbPass($passwd ?? $this->getDbPass());
        $this->setDbPort($port ?? $this->getDbPort());
        $this->setDbSocket($socket ?? $this->getDbSocket());
        $this->establish_connection();
        return $this->conn->ping();
    }

    /**
     * @return mysqli|null
     */
    public function getConnection(): ?mysqli {
        return $this->conn ?? null;
    }

    /**
     * @param string $string
     * @return string
     */
    public function escape_string(string $string) {
        return $this->conn->escape_string($string);
    }

    /**
     * @return int
     */
    protected function getTransDepth(): int
    {
        return $this->transDepth;
    }

    /**
     * @return bool
     */
    public function isTransEnabled(): bool
    {
        return $this->transEnabled;
    }

    /**
     * @param bool $transEnabled
     */
    public function setTransEnabled(bool $transEnabled): void
    {
        $this->transEnabled = $transEnabled;
    }

    /**
     * @return bool
     */
    public function isTransSuccessful(): bool
    {
        return $this->transSuccessful;
    }

    /**
     * @param bool $transSuccessful
     */
    protected function setTransSuccessful(bool $transSuccessful): void
    {
        $this->transSuccessful = $transSuccessful;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if (!is_null($this->conn)) {
            $this->conn->close();
            $this->conn = null;
            return true;
        }
        return false;
    }

}