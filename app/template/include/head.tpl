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

{include 'css.tpl'}

<!--Favicon-->
<link rel="shortcut icon" type="image/png" href="{$header.fav_icon}"/>