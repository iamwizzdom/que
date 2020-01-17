<?php

use Twig\Environment;
use Twig\Source;
use Twig\Template;

/* module/view.html */
class __TwigTemplate_8388736f79fc1090fc29e0a113c5fcd0e01d1d859eb2229798125808d20bc30e extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <title>Title</title>
</head>
<body>


<h3>Hello world</h3>
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "module/view.html";
    }

    public function getDebugInfo()
    {
        return array (  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "module/view.html", "C:\\xampp\\htdocs\\personal\\que\\app\\template\\module\\view.html");
    }
}
