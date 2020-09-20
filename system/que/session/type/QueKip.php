<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/16/2020
 * Time: 03:26 PM
 */

namespace que\session\type;

use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\common\exception\QueRuntimeException;
use que\support\Arr;

class QueKip
{
    /**
     * @var string
     */
    private string $session_id;

    /**
     * @var QueKip
     */
    private static QueKip $instance;

    /**
     * @var array
     */
    private array $pointer = [];

    /**
     * @var string
     */
    private string $sessionFilePath;

    /**
     * QueKip constructor.
     * @param $session_id
     */
    protected function __construct(string $session_id)
    {
        $this->session_id = $session_id;
        $this->sessionFilePath = session_save_path();
        $this->fetch_data();
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @param string $session_id
     * @return QueKip
     */
    public static function getInstance(string $session_id): QueKip
    {
        if (!isset(self::$instance))
            self::$instance = new self($session_id);
        return self::$instance;
    }

    /**
     * The method is used to retrieve data from the session
     * @param $key - This defines the key used to store the data being retrieved
     * @param null $default - This defines the default value to be return if the data if not found for the key used
     * @return mixed|null
     */
    public function get($key, $default = null) {
        $data = Arr::get($this->pointer, $key, $default);
        if ($data == $default) return $data;
        if (isset($data['expire']) && is_int($data['expire']) && APP_TIME > $data['expire']) {
            $this->delete($key);
            return $default;
        }
        return $data['data'] ?? $default;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isset($key): bool {
        return Arr::isset($this->pointer, $key);
    }

    /**
     * This method is used to set data to the session
     * @param $key - This defines the key to the data being saved
     * @param $value - This defines the data being saved
     * @param int|null $expire - This defines the expiration time (in seconds)
     * of the data being saved. If not set, the data will not expire
     *
     * @return bool
     */
    public function set($key, $value, int $expire = null): bool {
        Arr::set($this->pointer, $key, [
            'data' => $value,
            'expire' => is_int($expire) ? (APP_TIME + $expire) : null
        ]);
        return $this->write_data() !== false;
    }

    /**
     * This method is used to unset data from the session
     * @param mixed ...$keys
     * @return int
     */
    public function delete(...$keys): int {
        $count = 0;
        foreach ($keys as $key) {
            Arr::unset($this->pointer, $key);
            $count++;
        }
        if ($count > 0) $this->write_data();
        return $count;
    }

    /**
     * This method is used to switch the session or retrieve the current session id.
     * The session will be switched if a new session id is passed
     * with the $session_id param, while the current session id
     * will be returned if no session id is passed
     *
     * @param string $session_id
     * @return mixed
     */
    public function session_id(string $session_id = null) {
        if (is_null($session_id)) return $this->session_id;
        if ($this->session_id == $session_id) return $this->session_id;
        $this->session_id = $session_id;
        $this->fetch_data();
        return $this->session_id;
    }

    /**
     * This method is used to reset the current session id.
     *
     * @param string $session_id
     * @return string
     */
    public function reset_session_id(string $session_id) {
        if ($this->session_id == $session_id) return $this->session_id;
        $old_pointer = $this->pointer;
        $this->session_destroy();
        $this->pointer = $old_pointer;
        $this->session_id = $session_id;
        $this->write_data();
        return $this->session_id;
    }

    /**
     * This method will destroy the current session
     * @return bool
     */
    public function session_destroy(): bool {
        $fileName = sha1($this->session_id);
        $filePath = "{$this->sessionFilePath}/quekip/que_session_{$fileName}.tmp";
        if (!is_file($filePath)) return false;
        if (unlink($filePath)){
            $this->pointer = [];
            return true;
        }
        return false;
    }

    private function fetch_data() {

        if (!is_dir("{$this->sessionFilePath}/quekip")) {

            try {
                $this->mk_dir("{$this->sessionFilePath}/quekip");
            } catch (QueException $e) {
                throw new QueRuntimeException($e->getMessage(), $e->getTitle(),
                    E_USER_ERROR, 0, PreviousException::getInstance(2));
            }
        }

        $fileName = sha1($this->session_id);

        if (!is_file("{$this->sessionFilePath}/quekip/que_session_{$fileName}.tmp"))
            $this->create_file("{$this->sessionFilePath}/quekip/que_session_{$fileName}.tmp");

        if (($cache = @file_get_contents("{$this->sessionFilePath}/quekip/que_session_{$fileName}.tmp")) === false)
            throw new QueRuntimeException("Unable to read from quekip cache file!", "QueKip Error",
                E_USER_ERROR, 0, PreviousException::getInstance(5));

        $this->pointer = !empty($cache) && strlen($cache) > 0 ? unserialize($cache) : [];
    }

    /**
     * @return bool|int
     */
    private function write_data() {

        if (!is_dir($this->sessionFilePath)) {

            try {
                $this->mk_dir($this->sessionFilePath);
            } catch (QueException $e) {
                throw new QueRuntimeException($e->getMessage(), $e->getTitle(),
                    E_USER_ERROR, 0, PreviousException::getInstance(5));
            }
        }

        $fileName = sha1($this->session_id);

        if (!is_file("{$this->sessionFilePath}/quekip/que_session_{$fileName}.tmp"))
            $this->create_file("{$this->sessionFilePath}/quekip/que_session_{$fileName}.tmp");

        if (($status = file_put_contents("{$this->sessionFilePath}/quekip/que_session_{$fileName}.tmp",
            serialize($this->pointer))) === false) throw new QueRuntimeException("Unable to write to quekip cache file!",
            "QueKip Error", E_USER_ERROR, 0, PreviousException::getInstance(5));

        return $status;
    }

    /**
     * @param $dir
     * @return bool
     * @throws QueException
     */
    private function mk_dir($dir) {

        try {
            return mk_dir($dir);
        } catch (QueException $e) {
            throw new QueException("System cache " . strtolower($e->getMessage()), "QueKip Error");
        }
    }

    /**
     * @param $path
     * @return bool
     */
    private function create_file($path) {
        return fclose(fopen($path, 'a'));
    }

}