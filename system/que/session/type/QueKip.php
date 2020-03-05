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

class QueKip
{
    private $session_id;

    /**
     * @var QueKip
     */
    private static $instance;

    /**
     * @var array
     */
    private $data = [];

    protected function __construct($session_id)
    {
        $this->session_id = $session_id;

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
        if (!isset($this->data[$key])) return $default;
        $data = $this->data[$key];
        if (is_int($data['expire']) && APP_TIME > $data['expire']) {
            $this->unset($key);
            return $default;
        }
        return $data['data'];
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
        $this->data[$key] = [
            'data' => $value,
            'expire' => is_int($expire) ? (APP_TIME + $expire) : null
        ];
        return $this->write_data() !== false;
    }

    /**
     * This method is used to unset data from the session
     * @param mixed ...$keys
     * @return int
     */
    public function unset(...$keys): int {
        $count = 0;
        foreach ($keys as $key) {
            unset($this->data[$key]);
            $count++;
        }
        $this->write_data();
        return $count;
    }

    /**
     * This method is used to reset/retrieve the session id
     * The session id will be reset if a new session id is passed
     * in the $session_id param, while the current session id
     * will be returned if a new session_id is not passed
     *
     * @param null $session_id
     * @return mixed
     */
    public function session_id($session_id = null) {
        if (is_null($session_id)) return $this->session_id;
        else {
            $this->session_id = $session_id;
            $this->fetch_data();
            return $this->session_id;
        }
    }

    /**
     * This method is used to reset the session id
     * and is also an alias of the session_id() method
     * @param $session_id
     * @return mixed
     */
    public function reset_session_id($session_id) {
        $this->delete($this->session_id);
        $this->session_id = $session_id;
        $this->write_data();
        $this->fetch_data();
        return $this->session_id;
    }

    /**
     * This method is used to delete the entire session cache file using the session id
     * @param $session_id
     * @return bool
     */
    public function delete($session_id) {
        $fileName = sha1($session_id);
        $filePath = session_save_path() . "/quekip/que_session_{$fileName}.tmp";
        if (!file_exists($filePath)) return false;
        $this->data = [];
        return unlink($filePath);
    }

    private function fetch_data() {

        $filePath = session_save_path() . "/quekip";

        if (!is_dir($filePath)) {

            try {
                $this->mk_dir($filePath);
            } catch (QueException $e) {
                throw new QueRuntimeException($e->getMessage(), "Session Error",
                    E_USER_ERROR, 0, PreviousException::getInstance(2));
            }
        }

        $fileName = sha1($this->session_id);

        if (!file_exists("{$filePath}/que_session_{$fileName}.tmp"))
            $this->create_file("{$filePath}/que_session_{$fileName}.tmp");

        if (($cache = @file_get_contents("{$filePath}/que_session_{$fileName}.tmp")) === false)
            throw new QueRuntimeException("Unable to read from quekip cache file!", "Session Error",
                E_USER_ERROR, 0, PreviousException::getInstance(2));

        $this->data = !empty($cache) && strlen($cache) > 0 ? unserialize($cache) : [];

    }

    /**
     * @return bool|int
     */
    private function write_data() {

        $filePath = session_save_path() . "/quekip";

        if (!is_dir($filePath)) {

            try {
                $this->mk_dir($filePath);
            } catch (QueException $e) {
                throw new QueRuntimeException($e->getMessage(), "Session Error",
                    E_USER_ERROR, 0, PreviousException::getInstance(2));
            }
        }

        $fileName = sha1($this->session_id);

        if (!file_exists("{$filePath}/que_session_{$fileName}.tmp"))
            $this->create_file("{$filePath}/que_session_{$fileName}.tmp");

        if (($status = @file_put_contents("{$filePath}/que_session_{$fileName}.tmp",
            serialize($this->data))) === false)
            throw new QueRuntimeException("Unable to write to quekip cache file!", "Session Error",
                E_USER_ERROR, 0, PreviousException::getInstance(2));

        return $status;
    }

    /**
     * @param $dir
     * @return bool
     * @throws QueException
     */
    private function mk_dir($dir) {

        if (!is_dir($dir) && !mkdir($dir, 0777, true))
            throw new QueException("System cache directory could not be created", "Session Error");

        if ($dir === null || !is_dir($dir) || !is_writable($dir))
            throw new QueException("System cache directory not writable", "Session Error");

        return true;
    }

    /**
     * @param $path
     * @return bool
     */
    private function create_file($path) {
        return fclose(fopen($path, 'a'));
    }

}