<?php

/* layouts/main.twig */
class __TwigTemplate_cdfac8a643f5ecad4efabdec63248498e638ceb7d09e04842cc5d64d7f03e453 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Pixelf</title>
</head>
    <body>
        <header>
            <h1>Pixelf</h1>
        </header>
        <section>
            ";
        // line 11
        $this->displayBlock('content', $context, $blocks);
        // line 12
        echo "        </section>
    </body>
</html>";
    }

    // line 11
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "layouts/main.twig";
    }

    public function getDebugInfo()
    {
        return array (  40 => 11,  34 => 12,  32 => 11,  20 => 1,  31 => 3,  28 => 2,);
    }
}
