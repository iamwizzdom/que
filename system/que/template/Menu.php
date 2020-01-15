<?php


namespace que\template;

use que\route\Route;
use que\route\structure\RouteEntry;

class Menu
{
    /**
     * @var array
     */
    private $menus;

    /**
     * @var \Menu
     */
    private $menuInstance;

    /**
     * @var bool
     */
    private $checkPermission = false;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        $this->menuInstance = new \Menu();
        $implements = class_implements($this->menuInstance);
        if ($implements) $this->checkPermission = isset($implements['que\security\permission\RoutePermission']);
        $appMenu = $this->menuInstance->menuList();
        $this->filter($appMenu);
        $this->menus = $appMenu;
    }

    /**
     * @param array $menus
     * @return array
     */
    private function filter(array &$menus) {

        $routes = [];

        if (array_size($menus) > 0) {

            foreach ($menus as $key => &$menu) {

                if (!is_array($menu)) continue;

                if (!isset($menu['title']) || !isset($menu['href'])) {
                    @$this->filter($menu);
                    continue;
                }

                if (str_contains($menu['href'], $host = server_host()))
                    $menu['href'] = str_start_from($menu['href'], $host);

                $children = [];

                if (isset($menu['__']))
                    $children = @$this->filter($menu['__']);

                if ($this->checkPermission) {
                    $entry = $this->getRouteEntry($menu['href']);
                    if (!$entry || !$this->menuInstance->hasPermission($entry)) {
                        unset($menus[$key]);
                    }
                }

                $route = Route::getCurrentRoute();

                if (!empty($route)) {

                    array_callback($children, function ($value) {
                        return $this->replaceArgs($value);
                    });

                    $href = $this->replaceArgs($route->getUri());

                    if ($this->replaceArgs($menu['href']) == $href ||
                        in_array($href, $children)) $menu['active'] = true;

                }

                $routes[] = $menu['href'];

                $menu['href'] = base_url($menu['href']);
            }
        }

        return $routes;
    }

    /**
     * @return array
     */
    public function getMenu() {
        return $this->menus;
    }

    /**
     * @param string $href
     * @return string|string[]|null
     */
    private function replaceArgs(string $href) {
        return preg_replace("/\{(.*?)\}/", "-", $href);
    }

    /**
     * @param string $uri
     * @return RouteEntry|null
     */
    private function getRouteEntry(string $uri): ?RouteEntry {
        return Route::getRouteEntry($uri);
    }

}