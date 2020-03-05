<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 10/10/2018
 * Time: 8:34 PM
 */

namespace que\template;

class Pagination
{

    /**
     * @var Pagination
     */
    private static $instance;

    /**
     * @var array
     */
    private static $pagination = [];

    /**
     * Query constructor.
     */
    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
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
     * @param int $currentPage
     * @param int $pageStart
     * @param int $pageEnd
     * @param int $totalPages
     * @param int $totalRecords
     * @param string $tag
     */
    public function paginate(int $currentPage, int $pageStart,
                                    int $pageEnd, int $totalPages, int $totalRecords, string $tag)
    {
        $params = get();
        unset($params['p']);
        $currentUrl = serializer($params);
        $count = 0;

        $pagination = "<ul class='pagination'>";

        if ($pageStart > 10) {
            $pagination .= '<li class="page-item"><a class="page-link" href="?' .  $currentUrl . '">' .
                '<i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>';
        }

        $pagination .= '<li class="page-item ' . ($pageStart <= 10 ? 'disabled' : '') .'"><a class="page-link" href="' . ($pageStart <= 10 ? '#' :
                '?' . $currentUrl . '&p=' . ($pageStart - 1)) . '"><i class="fa fa-angle-left"></i></a></li>';

        for ($i = $pageStart; $i < ($pageEnd + 1); $i++) {

            $pagination .= '<li class="page-item ' . ($i == $currentPage ? "active" : "") .
                '"><a class="page-link" href="?' . $currentUrl . '&p=' . $i . '">' . $i . '</a></li>';

            $count++;

        }

        $pagination .= '<li class="page-item ' . (($pageEnd + 1) > $totalPages ? 'disabled' : '') .'"><a class="page-link" href="' . (($pageEnd + 1) > $totalPages ? '#' :
                '?' . $currentUrl . '&p=' . ($pageEnd + 1)) . '"><i class="fa fa-angle-right"></i></a></li>';

        if (($pageEnd + 1) < $totalPages) {
            $pagination .= '<li class="page-item"><a class="page-link" href="?' .  $currentUrl . '&p=' . $totalPages . '">' .
                '<i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i></a></li>';
        }

        $pagination .= "</ul>";

        self::$pagination[$tag] = [
            'currentPage' => $currentPage,
            'pageStart' => $pageStart,
            'pageEnd' => $pageEnd,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
            'links' => $pagination
        ];
    }

    /**
     * @param $tag
     * @return int
     */
    public function getCurrentPage($tag): int
    {
        return @self::$pagination[$tag]['currentPage'];
    }

    /**
     * @return array
     */
    public function getCurrentPageFlat(): array
    {
        $currentPage = [];
        foreach (self::$pagination as $k => $v)
            $currentPage[$k] = $v['currentPage'];
        return $currentPage;
    }

    /**
     * @param $tag
     * @return int
     */
    public function getPageStart($tag): int
    {
        return @self::$pagination[$tag]['pageStart'];
    }

    /**
     * @return array
     */
    public function getPageStartFlat(): array
    {
        $pageStart = [];
        foreach (self::$pagination as $k => $v)
            $pageStart[$k] = $v['pageStart'];
        return $pageStart;
    }

    /**
     * @param $tag
     * @return int
     */
    public function getPageEnd($tag): int
    {
        return @self::$pagination[$tag]['pageEnd'];
    }

    /**
     * @return array
     */
    public function getPageEndFlat(): array
    {
        $pageEnd = [];
        foreach (self::$pagination as $k => $v)
            $pageEnd[$k] = $v['pageEnd'];
        return $pageEnd;
    }

    /**
     * @param $tag
     * @return int
     */
    public function getTotalPages($tag): int
    {
        return @self::$pagination[$tag]['totalPages'];
    }

    /**
     * @return array
     */
    public function getTotalPagesFlat(): array
    {
        $totalPages = [];
        foreach (self::$pagination as $k => $v)
            $totalPages[$k] = $v['totalPages'];
        return $totalPages;
    }

    /**
     * @param $tag
     * @return int
     */
    public function getTotalRecords($tag): int
    {
        return @self::$pagination[$tag]['totalRecords'];
    }

    /**
     * @return array
     */
    public function getTotalRecordsFlat(): array
    {
        $totalRecords = [];
        foreach (self::$pagination as $k => $v)
            $totalRecords[$k] = $v['totalRecords'];
        return $totalRecords;
    }

    /**
     * @param $tag
     * @return string
     */
    public function getLinks($tag): string
    {
        return @self::$pagination[$tag]['links'];
    }

    /**
     * @return array
     */
    public function getLinksFlat(): array
    {
        $pagination = [];
        foreach (self::$pagination as $k => $v)
            $pagination[$k] = $v['links'];
        return $pagination;
    }
}