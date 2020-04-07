<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 4/6/2020
 * Time: 4:27 PM
 */

namespace que\utility\password;


use que\utility\hash\Hash;

class Password
{
    private $password;

    public function __construct($password)
    {
        $this->password = trim($password);
    }

    public function raw()
    {
        return $this->password;
    }

    /**
     * @return false|string|null
     */
    public function hash_bcrypt()
    {
        return Hash::bcrypt($this->password);
    }

    /**
     * @return false|string|null
     */
    public function hash_argon2i()
    {
        return Hash::argon2i($this->password);
    }

    /**
     * @param $hash
     * @return bool
     */
    public function verify_bcrypt(string $hash): bool
    {
        return password_verify($this->password, $hash);
    }

    /**
     * @param $hash
     * @return bool
     */
    public function verify_argon2i(string $hash): bool
    {
        return password_verify($this->password, $hash);
    }

    /**
     * @param string $algo
     * @return string
     */
    public function hash_sha(string $algo = "sha256")
    {
        return Hash::sha($this->password, $algo);
    }

    /**
     * @param string $hash
     * @param string $algo
     * @return bool
     */
    public function verify_sha(string $hash, string $algo = "sha256"): bool
    {
        return strcmp($this->hash_sha($algo), $hash) == 0;
    }

}