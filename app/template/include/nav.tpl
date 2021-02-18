<ul class="nav navbar-nav mr-auto">

    {$menuList = $menu}

    {foreach $menuList as $key => $menu}

        {if !isset($menu['title']) || !isset($menu['href'])}
            {continue}
        {/if}

        {$hasSubMenu = (isset($menu['__']) && !empty($menu['__']))}
        <li class="nav-item {if $hasSubMenu}dropdown{/if} {if isset($menu.active) && $menu.active === true}active{/if}
                                {if isset($menu.disabled) && $menu.disabled === true}hidden{/if}">

            <a class="nav-link {if $hasSubMenu}dropdown-toggle{/if}"
               href="{if $hasSubMenu}#{else}{$menu.href}{/if}"
               {if $hasSubMenu}id="navbarDropdownMenuLink{$key}"
               role="button" data-toggle="dropdown"{/if}>
                {if isset($menu.icon)}<i class="{$menu.icon} mr-2"></i>{/if}
                {$menu.title}
            </a>

            {if $hasSubMenu}
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink{$key}">
                    {foreach $menu['__'] as $subKey => $subMenu}
                        <li class="dropdown-item {if isset($subMenu.active) && $subMenu.active === true}active{/if}
                                        {if isset($subMenu.disabled) && $subMenu.disabled === true}hidden{/if}">
                            <a href="{$subMenu.href}">
                                {if isset($subMenu.icon)}<i class="{$subMenu.icon} mr-2"></i>{/if}
                                {$subMenu.title}
                            </a></li>
                    {/foreach}
                </ul>
            {/if}

        </li>
    {/foreach}
</ul>