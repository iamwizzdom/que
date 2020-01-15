<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/1/2019
 * Time: 10:36 PM
 */

namespace que\user;


use ArrayAccess;
use que\common\exception\QueRuntimeException;
use que\error\RuntimeError;
use que\model\Model;

class XUser implements ArrayAccess
{

    /**
     * @var object
     */
    private $user;

    public function __construct(object $user)
    {
        $this->user = $user;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getValue($key, $default = null) {
        return $this->user->{$key} ?? $default;
    }

    /**
     * @return object
     */
    public function getUserObject(): object
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getUserArray(): array
    {
        return object_to_array($this->user);
    }

    /**
     * @return Model
     */
    public function getModel(): Model {
        return new Model($this->user, 'app_user');
    }

    public function update(array $columns) {

        $columnsToUpdate = [];
        foreach ($columns as $key => $value) {
            if (isset($this->user->{$key}) && $this->user->{$key} != $value) {
                $columnsToUpdate[$key] = $value;
            }
        }

        if (empty($columnsToUpdate)) return false;

        $update = db()->update('app_user', $columnsToUpdate, [
            'AND' => [
                'userID' => $this->user->{'userID'}
            ]
        ]);

        if ($status = $update->isSuccessful())
            foreach ($columnsToUpdate as $key => $value)
                $this->user->{$key} = $value;

        return $status;
    }

    /**
     * @return bool
     */
    public function isMe(): bool {

        if (!isset($this->user->userID))
            throw new QueRuntimeException("The key 'userID' was not found in the present user object", "User Error", E_USER_ERROR);
        
        return User::isLoggedIn() && User::getInstance()->getModel()->get('userID')->is(
            $this->getValue('userID')
        );
    }

    /**
     * @param int $userID
     * @param string $dataType = array|object|model
     * @return array|object|Model|XUser|null
     */
    public static function getUser(int $userID, string $dataType = null)
    {
        $user = db()->find('app_user', 'userID', $userID);

        if (!$user->isSuccessful()) return null;

        $xuser = new XUser($user->getQueryResponse(0));

        switch (strtolower($dataType)) {
            case 'array':
                return $xuser->getUserArray();
            case 'object':
                return $xuser->getUserObject();
            case 'model':
                return $xuser->getModel();
            default:
                return $xuser;
        }
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return isset($this->user->{$offset});
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->user->{$offset} ?? null;
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        $this->user->{$offset} = $value;

    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset($this->user->{$offset});
    }
}