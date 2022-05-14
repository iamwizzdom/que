<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:title" content="{$header.title}">
<meta property="og:site_name" content="{$header.name}">
<meta property="og:description" content="{$header.desc}">
<meta property="og:locale" content="en-ng">
<meta property="og:url" content="{$header.domain}">
<meta name="description" content="{$header.desc}"/>
<meta name="keywords" content="{$header.keywords}"/>
<meta name="robots" content="{$header.robots}"/>
<title>{$header.title}</title>

<!-- twitter meta data -->
<meta name="twitter:title" content="{$header.title}">
<meta name="twitter:description" content="{$header.desc}">
<meta name="twitter:image" content="{base_url($header.logo.large.origin)}">
<meta name="twitter:card" content="summary">

<!-- Styles -->
<style>
    html, body {
        background-color: #fff;
        color: #636b6f;
        font-family: 'Nunito', sans-serif;
        font-weight: 200;
        height: 100vh;
        margin: 0;
    }

    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 84px;
    }

    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
</style>

{include './css.tpl'}

<!--Favicon-->
<link rel="shortcut icon" type="image/png" href="{$header.favicon}"/>