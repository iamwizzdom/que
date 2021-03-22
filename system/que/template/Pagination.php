<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/10/2018
 * Time: 8:34 PM
 */

namespace que\template;

use que\common\exception\PreviousException;
use que\common\exception\QueRuntimeException;
use que\http\HTTP;

class Pagination
{

    /**
     * @var Pagination
     */
    private static Pagination $instance;

    /**
     * @var Paginator[]
     */
    private static array $paginators = [];

    /**
     * Pagination constructor.
     */
    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @return Pagination
     */
    public static function getInstance(): Pagination
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param Paginator $paginator
     * @param string $tag
     */
    public function add(Paginator $paginator, string $tag)
    {
        self::$paginators[$tag] = $paginator;
    }

    /**
     * @param string $tag
     * @return mixed|Paginator|null
     */
    public function getPaginator(string $tag)
    {
        return self::$paginators[$tag] ?? null;
    }

    /**
     * @param string $tag
     * @param bool $returnUrl
     * @return string
     */
    public function getNextPage(string $tag, bool $returnUrl = false)
    {
        if (!isset(self::$paginators[$tag]))
            throw new QueRuntimeException("Undefined Tag: No Database Query was found paginated with the tag '{$tag}'",
                "Pagination Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return self::$paginators[$tag]->_show_next($returnUrl);
    }

    /**
     * @param string $tag
     * @param bool $returnUrl
     * @return string
     */
    public function getPreviousPage(string $tag, bool $returnUrl = false)
    {
        if (!isset(self::$paginators[$tag]))
            throw new QueRuntimeException("Undefined Tag: No Database Query was found paginated with the tag '{$tag}'",
                "Pagination Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return self::$paginators[$tag]->_show_previous($returnUrl);
    }

    /**
     * @param $tag
     * @return int
     */
    public function getTotalPages($tag): int
    {
        if (!isset(self::$paginators[$tag]))
            throw new QueRuntimeException("Undefined Tag: No Database Query was found paginated with the tag '{$tag}'",
                "Pagination Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return self::$paginators[$tag]->getTotalPages();
    }

    /**
     * @return array
     */
    public function getTotalPagesFlat(): array
    {
        $totalPages = [];
        foreach (self::$paginators as $tag => $paginator)
            $totalPages[$tag] = $paginator->getTotalPages();
        return $totalPages;
    }

    /**
     * @param $tag
     * @return int
     */
    public function getTotalRecords($tag): int
    {
        if (!isset(self::$paginators[$tag]))
            throw new QueRuntimeException("Undefined Tag: No Database Query was found paginated with the tag '{$tag}'",
                "Pagination Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        return self::$paginators[$tag]->getTotalRecords();
    }

    /**
     * @return array
     */
    public function getTotalRecordsFlat(): array
    {
        $totalRecords = [];
        foreach (self::$paginators as $tag => $paginator)
            $totalRecords[$tag] = $paginator->getTotalRecords();
        return $totalRecords;
    }

    /**
     * @param $tag
     * @return string
     */
    public function getLinks($tag): string
    {
        if (!isset(self::$paginators[$tag]))
            throw new QueRuntimeException("Undefined Tag: No Database Query was found paginated with the tag '{$tag}'",
                "Pagination Error", E_USER_ERROR, HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));

        try {
            return self::$paginators[$tag]->render();
        } catch (\Exception $e) {
            throw new QueRuntimeException($e->getMessage(), "Pagination Error", E_USER_ERROR,
                HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
        }
    }

    /**
     * @return array
     */
    public function getLinksFlat(): array
    {
        $pagination = [];
        foreach (self::$paginators as $tag => $paginator) {
            try {
                $pagination[$tag] = $paginator->render();
            } catch (\Exception $e) {
                throw new QueRuntimeException($e->getMessage(), "Pagination Error", E_USER_ERROR,
                    HTTP::INTERNAL_SERVER_ERROR, PreviousException::getInstance(1));
            }
        }
        return $pagination;
    }
}
