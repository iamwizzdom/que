<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/20/2020
 * Time: 10:44 AM
 */

namespace que\database\drivers\mysql;


use DateTime;
use DateTimeZone;
use Exception;
use PDO;
use PDOException;
use PDOStatement;
use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\database\interfaces\drivers\Driver;
use que\database\interfaces\drivers\DriverQueryBuilder;
use que\database\interfaces\drivers\DriverResponse;
use que\http\HTTP;
use que\support\Config;
use que\support\Str;

class MySqlDriver implements Driver
{
    /**
     * @var PDO
     */
    private ?PDO $conn = null;

    /**
     * @var int
     */
    private int $connTime = 0;

    private static ?string $timezoneOffset = null;

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }

    /**
     * @return string
     */
    private function buildConnectionParams(): string
    {

        $config = Config::get('database.connections.mysql');
        $params = [];

        if (!isset($config['unix_socket']) || !$config['unix_socket']) {
            $params['host'] = $config['host'] ?? null;
            $params['port'] = $config['port'] ?? null;
        } else $params['unix_socket'] = $config['unix_socket'] ?? null;

        $params['dbname'] = $config['dbname'] ?? null;
        $params['charset'] = $config['charset'] ?? null;
        $params['collation'] = $config['collation'] ?? null;
        $params['engine'] = $config['engine'] ?? null;

        return serializer_recursive($params, ";", function ($value) {
            return $value !== null;
        });
    }

    /**
     * Establish PDO connection
     */
    protected function establish_connection()
    {

        $this->connTime = APP_TIME;

        try {

            $config = Config::get('database.connections.mysql');

            if (empty(($config['timezone'] ?? null)) && !self::$timezoneOffset) {
                self::$timezoneOffset = timezone_offset_get(new DateTimeZone(date_default_timezone_get()), new DateTime() );
                self::$timezoneOffset = $this->timezone_offset_string(self::$timezoneOffset);
            }

            $offset = self::$timezoneOffset;

            $options = $config['options'] ?? [];
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] ??= "SET time_zone = '" . (($config['timezone'] ?? null) ?: $offset) . "'";

            $this->conn = new PDO("mysql:{$this->buildConnectionParams()}",
                $config['username'] ?? 'root', $config['password'] ?? '', $options);

            // set the PDO options
            $options = [];
            $options[PDO::MYSQL_ATTR_SSL_KEY] = $config['ssl']['key'] ?? null;
            $options[PDO::MYSQL_ATTR_SSL_CERT] = $config['ssl']['cert'] ?? null;
            $options[PDO::MYSQL_ATTR_SSL_CA] = $config['ssl']['ca'] ?? null;
            $options[PDO::MYSQL_ATTR_SSL_CAPATH] = $config['ssl']['capath'] ?? null;
            $options[PDO::MYSQL_ATTR_SSL_CIPHER] = $config['ssl']['cipher'] ?? null;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $config['ssl']['verify_server_cert'] ?? null;
            $options[PDO::ATTR_PERSISTENT] = $config['persist'] ?? false;

            foreach ($options as $key => $option) {
                if ($option !== null) $this->conn->setAttribute($key, $option);
            }

        } catch (PDOException $e) {
            throw new QueRuntimeException("Connection failed: {$e->getMessage()}",
                "Database Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(4));
        }

    }

    private function timezone_offset_string( $offset )
    {
        return sprintf( "%s%02d:%02d", ( $offset >= 0 ) ? '+' : '-', abs( $offset / 3600 ), abs( $offset % 3600 ) );
    }

    /**
     * @return PDO
     */
    private function connect(): PDO
    {
        // re-establish a new PDO connection every 1 hour
        if (APP_TIME > ($this->connTime + TIMEOUT_ONE_HOUR))
            $this->establish_connection();

        $limit = 0;

        while (is_null($this->conn) && $limit < MAX_RETRY) {
            $this->establish_connection();
            $limit++;
        }

        if (is_null($this->conn)) throw new QueRuntimeException("Failed to establish database connection after {$limit} trials",
            "Database Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(3));

        return $this->conn;
    }

    /**
     * @return PDO
     */
    private function getConnection(): PDO
    {
        // TODO: Implement getConnection() method.
        return $this->connect();
    }

    /**
     * @inheritDoc
     */
    public function reconnect(): bool
    {
        // TODO: Implement reconnect() method.
        $this->close();
        $this->establish_connection();
        return !is_null($this->conn);
    }

    /**
     * @inheritDoc
     */
    public function changeUser(string $username, string $password): bool
    {
        // TODO: Implement changeUser() method.
        Config::set('database.connections.mysql.username', $username);
        Config::set('database.connections.mysql.password', $password);
        $this->close();
        $this->establish_connection();
        return !is_null($this->conn);
    }

    /**
     * @inheritDoc
     */
    public function changeDb(string $dbName): bool
    {
        // TODO: Implement changeDb() method.
        Config::set('database.connections.mysql.dbname', $dbName);
        $this->close();
        $this->establish_connection();
        return !is_null($this->conn);
    }

    /**
     * @inheritDoc
     */
    public function escape_string(string $string): string
    {
        // TODO: Implement escape_string() method.
        return addslashes($string);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if (!is_null($this->conn)) {
            $this->conn = null;
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function beginTransaction(): bool
    {
        // TODO: Implement beginTransaction() method.
        if ($this->conn === null) return false;
        return $this->conn->beginTransaction() ?:
            $this->conn->query('START TRANSACTION') == true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        // TODO: Implement commit() method.
        if ($this->conn === null) return false;
        return $this->conn->commit() ?:
            $this->conn->query('COMMIT') == true;

    }

    /**
     * @inheritDoc
     */
    public function rollback(): bool
    {
        // TODO: Implement rollback() method.
        if ($this->conn === null) return false;
        return $this->conn->rollBack() ?:
            $this->conn->query('ROLLBACK') == true;
    }

    /**
     * @inheritDoc
     */
    public function isInDebugMode(): bool
    {
        // TODO: Implement isInDebugMode() method.
        return Config::get('database.connections.mysql.debug', false);
    }

    /**
     * @inheritDoc
     */
    public function getQueryBuilder(): DriverQueryBuilder
    {
        // TODO: Implement getQueryBuilder() method.
        return new MySqlDriverQueryBuilder($this);
    }

    /**
     * @inheritDoc
     */
    public function exec(DriverQueryBuilder $builder): DriverResponse
    {
        // TODO: Implement exec() method.
        $conn = $this->getConnection();

        $status = false;

        $stmt = $conn->prepare($builder->getQuery(), [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);

        $attempts = 0;

        if (!$stmt instanceof PDOStatement) {
            try {
                $attempts = retry(function ($attempts) use (&$stmt, $conn, $builder) {

                    $stmt = $conn->prepare($builder->getQuery(), [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
                    return $attempts;

                }, MAX_RETRY, 0.5 * 1000, function () use (&$stmt) {
                    return $stmt instanceof PDOStatement;
                });
            } catch (Exception $e) {
            }
        }

        if (!$stmt instanceof PDOStatement)
            throw new QueRuntimeException("Error preparing SQL statement: {$builder->getQuery()} after {$attempts} trial(s)",
                "Database Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(3));

        try {

            foreach ($builder->getBindings() as $key => $value) {

                if (!str__contains($builder->getQuery(), $key)) continue;

                if (is_bool($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
                } elseif (is_integer($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } elseif (is_null($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $status = $stmt->execute();

        } catch (PDOException $e) {

            if ($this->isInDebugMode()) throw new QueRuntimeException(
                "{$e->getMessage()} | SQL: {$this->interpolateQuery($builder->getQuery(), $builder->getBindings())}",
                "Database Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(3));
        }

        $i = $builder->getQueryType();

        return match ($i) {
            DriverQueryBuilder::INSERT => new MySqlDriverResponse(
                null, $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()), $stmt->errorInfo(),
                $stmt->errorCode(), $conn->lastInsertId()
            ),
            DriverQueryBuilder::SELECT, DriverQueryBuilder::RAW_SELECT => new MySqlDriverResponse(
                $data = $stmt->fetchAll(PDO::FETCH_OBJ), $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()),
                (empty($data) && $stmt->errorCode() === "00000" ? [$this->isInDebugMode() ? "No record found in {$builder->getTable()} table" : 'No record found'] : $stmt->errorInfo()),
                $stmt->errorCode()
            ),
            DriverQueryBuilder::DELETE, DriverQueryBuilder::UPDATE => new MySqlDriverResponse(
                null, $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()),
                ($stmt->rowCount() == 0 ? [$this->isInDebugMode() ? "No record affected in {$builder->getTable()} table" : 'No record affected'] : $stmt->errorInfo()),
                $stmt->errorCode(), 0, $stmt->rowCount()
            ),
            DriverQueryBuilder::EXISTS => new MySqlDriverResponse(
                $stmt->fetch(PDO::FETCH_ASSOC)['existence'] ?? 0, $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()), $stmt->errorInfo(),
                $stmt->errorCode()
            ),
            DriverQueryBuilder::AVG, DriverQueryBuilder::SUM, DriverQueryBuilder::COUNT => new MySqlDriverResponse(
                $stmt->fetch(PDO::FETCH_ASSOC)['aggregate'] ?? 0, $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()), $stmt->errorInfo(),
                $stmt->errorCode()
            ),
            DriverQueryBuilder::RAW_OBJECT => new MySqlDriverResponse(
                $stmt->fetchAll(PDO::FETCH_OBJ), $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()), $stmt->errorInfo(),
                $stmt->errorCode()
            ),
            DriverQueryBuilder::RAW_QUERY => new MySqlDriverResponse(
                null, $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()), $stmt->errorInfo(),
                $stmt->errorCode()
            ),
            DriverQueryBuilder::SHOW_TABLE_PRIMARY_KEY => new MySqlDriverResponse(
                $stmt->fetch(PDO::FETCH_ASSOC)['Column_name'] ?? '', $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()), $stmt->errorInfo(),
                $stmt->errorCode()
            ),
            DriverQueryBuilder::SHOW_TABLE_COLUMNS => new MySqlDriverResponse(
                array_map(function ($row) {
                    return $row['Field'];
                }, $stmt->fetchAll(PDO::FETCH_ASSOC)) ?: [], $status,
                $this->interpolateQuery($builder->getQuery(), $builder->getBindings()), $stmt->errorInfo(),
                $stmt->errorCode()
            ),
            default => throw new QueRuntimeException("Database driver query builder type '{$i}' is invalid",
                "Database Driver Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(3)),
        };
    }

    /**
     * @param string $query
     * @param array $params
     * @return string|string[]
     */
    private function interpolateQuery(string $query, array $params): array|string
    {
        return Str::interpolate($query, $params);
    }
}
