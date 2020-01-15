<?php

namespace que\database\mysql;

use mysqli;
use que\common\exception\QueRuntimeException;

abstract class Connect
{
    /**
     * @var mysqli
     */
    private $conn;

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
     * Connect constructor.
     */
    protected function __construct()
    {

        $this->setDbHost(CONFIG['database']['mysql']['host'] ?? null);
        $this->setDbName(CONFIG['database']['mysql']['name'] ?? null);
        $this->setDbUser(CONFIG['database']['mysql']['user'] ?? null);
        $this->setDbPass(CONFIG['database']['mysql']['pass'] ?? null);

        // establish connection
        $this->establish_connection();
    }

    /**
     * Establish MySQL connection
     */
    private function establish_connection()
    {

        $this->conn_time = APP_TIME;
        $this->conn = new mysqli($this->getDbHost(), $this->getDbUser(), $this->getDbPass(), $this->getDbName());
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
    public function setDbName(string $db_name): void
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
    public function setDbUser(string $db_user): void
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
    public function setDbPass(string $db_pass): void
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
    public function setDbHost(string $db_host): void
    {
        $this->db_host = $db_host;
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
                "Database Error", E_USER_ERROR);

        $limit = 0;

        while (!($ping = @$this->conn->ping()) && $limit < MAX_RETRY) {
            $this->establish_connection();
            $limit++;
        }

        if (!$ping) throw new QueRuntimeException("Failed to establish database connection after {$limit} trials",
            "Database Error", E_USER_ERROR);

        return $this->conn;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if (isset($this->conn)) {
            $this->conn->kill($this->conn->thread_id);
            $this->conn->close();
            $this->conn = null;
            return true;
        }
        return false;
    }

}

?>