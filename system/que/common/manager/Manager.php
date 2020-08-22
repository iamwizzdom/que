<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/10/2018
 * Time: 12:18 PM
 */

namespace que\common\manager;

use que\common\exception\PreviousException;
use que\common\exception\QueException;
use que\common\exception\QueRuntimeException;
use que\database\DB;
use que\common\exception\AlertException;
use que\common\exception\BaseException;
use que\common\exception\BulkException;
use que\common\validator\Validator;
use que\http\HTTP;
use que\http\input\Input;
use que\mail\Mailer;
use que\common\time\Time;
use que\database\interfaces\model\Model;
use que\security\Attempt;
use que\security\Captcha;
use que\session\Session;
use que\template\Composer;
use que\mail\Mail;
use que\user\User;
use que\user\XUser;
use que\utility\Converter;
use que\utility\FlatList;
use que\utility\pattern\heap\Heap;
use que\utility\pattern\ObjectPool;
use que\utility\structure\lists\DoublyLinkedList;
use que\utility\structure\lists\SingleLinkedList;
use que\utility\structure\Stack;
use Throwable;

abstract class Manager
{

    /**
     * @return DB
     */
    protected function db(): DB {
        return DB::getInstance();
    }

    /**
     * @param string|null $key
     * @return User|mixed|null
     */
    protected function user(string $key = null) {
        $user = User::getInstance();
        return is_null($key) ? $user : $user->getValue($key, null);
    }

    /**
     * @param object $user
     * @return XUser
     */
    protected function x_user(object $user): XUser {
        return new XUser($user);
    }

    /**
     * @return Time
     */
    protected function time(): Time {
        return Time::getInstance();
    }

    /**
     *
     * @return FlatList
     */
    protected function flatList(): FlatList {
        return FlatList::getInstance();
    }

    /**
     * @param Input $input
     * @return Validator
     */
    protected function validator(Input $input): Validator {
        return new Validator($input);
    }

    /**
     * @param string $message
     * @param string $title
     * @param int $code
     * @param bool $status
     * @param Throwable|null $previous
     * @return BulkException
     */
    protected function bulkException($message = "", string $title = "", int $code = 0,
                                  bool $status = false, Throwable $previous = null): BulkException {
        return new BulkException($message, $title, $code, $status, $previous);
    }

    /**
     * @param string $message
     * @param string $title
     * @param int $code
     * @param bool $status
     * @param Throwable|null $previous
     * @return BaseException
     */
    protected function baseException(string $message = "", string $title = "", int $code = 0,
                                  bool $status = false, Throwable $previous = null): BaseException {
        return new BaseException($message, $title, $code, $status, $previous);
    }

    /**
     * @param string $message
     * @param string $title
     * @param int $type
     * @param string $buttonTitle
     * @param string $buttonUrl
     * @param int $buttonOption
     * @param Throwable|null $previous
     * @return AlertException
     */
    protected function alertException(string $message = "", string $title = "", int $type = 0, string $buttonTitle = "",
                                   string $buttonUrl = "", int $buttonOption = 0, Throwable $previous = null): AlertException {
        return new AlertException($message, $title, $type, $buttonTitle, $buttonUrl, $buttonOption, $previous);
    }

    /**
     * @param bool $singleton
     * @return Composer
     */
    protected function composer(bool $singleton = true): Composer {
        return Composer::getInstance($singleton);
    }

    /**
     * @param int $long
     * @return Captcha
     */
    protected function captcha($long = 4): Captcha {
        return new Captcha($long);
    }

    /**
     *
     * @return Converter
     */
    protected function converter(): Converter {
        return Converter::getInstance();
    }

    /**
     * @param string $key
     * @return Mail
     */
    protected function mail(string $key): Mail {
        return new Mail($key);
    }

    /**
     * @return Mailer
     * @throws QueException
     */
    protected function mailer(): Mailer {
        return Mailer::getInstance();
    }

    /**
     * @param object $tableRow
     * @param string $tableName
     * @param string $primaryKey
     * @param string $model
     * @return Model
     */
    protected function model(object &$tableRow, string $tableName,
                             string $primaryKey = 'id', string $model = null): Model
    {
        $model = \model(($modelKey = $model ?? config("database.default.model")));

        if ($model === null) throw new QueRuntimeException(
            "No database model was found with the key '{$modelKey}', check your database configuration to fix this issue.",
            "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        if (!($implements = class_implements($model)) || !isset($implements[Model::class])) throw new QueRuntimeException(
            "The specified model ({$model}) with key '{$modelKey}' does not implement the Que database model interface.",
            "Que Runtime Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return new $model($tableRow, $tableName, $primaryKey);
    }

    /**
     * @return HTTP
     */
    protected function http(): HTTP {
        return HTTP::getInstance();
    }

    /**
     * @return ObjectPool
     */
    protected function object_pool(): ObjectPool {
        return ObjectPool::getInstance();
    }

    /**
     * @return Heap
     */
    protected function heap(): Heap {
        return new Heap();
    }

    /**
     * @return SingleLinkedList
     */
    protected function single_linked_list(): SingleLinkedList {
        return new SingleLinkedList();
    }

    /**
     * @return DoublyLinkedList
     */
    protected function doubly_linked_list(): DoublyLinkedList {
        return new DoublyLinkedList();
    }

    /**
     * @param int $limit
     * @return Stack
     */
    protected function stack(int $limit = 10): Stack {
        return new Stack($limit);
    }

    /**
     * @return Attempt
     */
    protected function attempt(): Attempt {
        return Attempt::getInstance();
    }

    /**
     * @return Session
     */
    protected function session(): Session {
        return Session::getInstance();
    }

}
