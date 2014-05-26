<?php

/* main/index.twig */
class __TwigTemplate_3579fa1f31893a47339de4f2578dd193751efcbbd919b88d940e80794ad3558f extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("layouts/main.twig");

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "layouts/main.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_content($context, array $blocks = array())
    {
        // line 3
        echo "Hello world! ";
        echo twig_escape_filter($this->env, ((1 + 2) + 3), "html", null, true);
        echo "
";
    }

    public function getTemplateName()
    {
        return "main/index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  31 => 3,  28 => 2,);
    }
}
