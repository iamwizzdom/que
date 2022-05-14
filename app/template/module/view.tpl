<!DOCTYPE html>
<html lang="en">
{include file="../include/head.tpl"}

<body data-gr-c-s-loaded="true">
    {include file="../include/header-alert.tpl"}
    <div class="flex-center position-ref full-height">

        <div class="content">
            <div class="title m-b-md">{$data.hello}</div>

            <div class="links">
                <a href="{base_url('/api')}">Que Docs</a>
                <a href="https://github.com/iamwizzdom/que">GitHub</a>
            </div>
        </div>
    </div>
</body>

{include '../include/footer.tpl'}

</html>