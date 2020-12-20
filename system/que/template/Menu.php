<?php


namespace que\template;

use que\route\Route;
use que\route\RouteEntry;
use que\security\interfaces\RoutePermission;

class Menu
{
    /**
     * @var array
     */
    private array $menus;

    /**
     * @var \Menu
     */
    private \Menu $menuInstance;

    /**
     * @var bool
     */
    private $checkPermission;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        $this->menuInstance = new \Menu();
        $this->checkPermission = in_array(RoutePermission::class, class_implements($this->menuInstance) ?: []);
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
                    $this->filter($menu);
                    continue;
                }

                if (isset($menu['disable']) && $menu['disable'] === true) {
                    unset($menus[$key]);
                    continue;
                }

                if (str__contains($menu['href'], $host = server_host()))
                    $menu['href'] = str_start_from($menu['href'], $host);

                if ($menu['href']) $menu['href'] = trim($menu['href'], '/');

                $children = [];

                if (isset($menu['__']))
                    $children = $this->filter($menu['__']);

                if ($this->checkPermission) {
                    $entry = $this->getRouteEntry($menu['href']);
                    if (!($menu['override-permission'] ?? false) &&
                        ((!$entry || empty($entry)) || !$this->menuInstance->hasPermission($entry))) {
                        unset($menus[$key]);
                        continue;
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
        return preg_replace("/{(.*?)}/", "-", $href);
    }

    /**
     * @param string $uri
     * @return RouteEntry|null
     */
    private function getRouteEntry(string $uri): ?RouteEntry {
        return Route::getRouteEntryFromUri($uri);
    }

}