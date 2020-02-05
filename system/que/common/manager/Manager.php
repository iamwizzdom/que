<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/10/2018
 * Time: 12:18 PM
 */

namespace que\common\manager;

use que\common\exception\QueException;
use que\database\mysql\Query;
use que\common\exception\AlertException;
use que\common\exception\BaseException;
use que\common\exception\BulkException;
use que\common\validate\Validator;
use que\http\Http;
use que\http\input\Input;
use que\mail\Mailer;
use que\model\Model;
use que\common\time\Time;
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
     * @param bool $persist
     * @return Query
     */
    protected function db(bool $persist = false): Query {
        return Query::getInstance($persist);
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
     *
     * @return Composer
     */
    protected function composer(): Composer {
        return Composer::getInstance();
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
     * @param object $table_row
     * @param string $table_name
     * @return Model
     */
    protected function model(object $table_row, string $table_name): Model {
        return new Model($table_row, $table_name);
    }

    /**
     * @return Http
     */
    protected function http(): Http {
        return Http::getInstance();
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