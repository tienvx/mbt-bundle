<?php

/* @Twig/layout.html.twig */
class __TwigTemplate_709c4d8090a4a04c3e0c104631a71d73533c9f6294f167394ea38ec67f5ecc1c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'head' => array($this, 'block_head'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_02c9b8339e93e14358d0bf3abb6d4b3cb8d6fd1b2bf7e88b4d5e26b0a97bdd2c = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_02c9b8339e93e14358d0bf3abb6d4b3cb8d6fd1b2bf7e88b4d5e26b0a97bdd2c->enter($__internal_02c9b8339e93e14358d0bf3abb6d4b3cb8d6fd1b2bf7e88b4d5e26b0a97bdd2c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Twig/layout.html.twig"));

        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"";
        // line 4
        echo twig_escape_filter($this->env, $this->env->getCharset(), "html", null, true);
        echo "\" />
        <meta name=\"robots\" content=\"noindex,nofollow\" />
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
        <title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        <link rel=\"icon\" type=\"image/png\" href=\"";
        // line 8
        echo twig_include($this->env, $context, "@Twig/images/favicon.png.base64");
        echo "\">
        <style>";
        // line 9
        echo twig_include($this->env, $context, "@Twig/exception.css.twig");
        echo "</style>
        ";
        // line 10
        $this->displayBlock('head', $context, $blocks);
        // line 11
        echo "    </head>
    <body>
        <header>
            <div class=\"container\">
                <h1 class=\"logo\">";
        // line 15
        echo twig_include($this->env, $context, "@Twig/images/symfony-logo.svg");
        echo " Symfony Exception</h1>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/doc\">
                        <span class=\"icon\">";
        // line 19
        echo twig_include($this->env, $context, "@Twig/images/icon-book.svg");
        echo "</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Docs
                    </a>
                </div>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/support\">
                        <span class=\"icon\">";
        // line 26
        echo twig_include($this->env, $context, "@Twig/images/icon-support.svg");
        echo "</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Support
                    </a>
                </div>
            </div>
        </header>

        ";
        // line 33
        $this->displayBlock('body', $context, $blocks);
        // line 34
        echo "        ";
        echo twig_include($this->env, $context, "@Twig/base_js.html.twig");
        echo "
    </body>
</html>
";
        
        $__internal_02c9b8339e93e14358d0bf3abb6d4b3cb8d6fd1b2bf7e88b4d5e26b0a97bdd2c->leave($__internal_02c9b8339e93e14358d0bf3abb6d4b3cb8d6fd1b2bf7e88b4d5e26b0a97bdd2c_prof);

    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
        $__internal_6e349796d49a88f57d0af57e8b3cffd21c6c955ebef2474ae073e3639666311b = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_6e349796d49a88f57d0af57e8b3cffd21c6c955ebef2474ae073e3639666311b->enter($__internal_6e349796d49a88f57d0af57e8b3cffd21c6c955ebef2474ae073e3639666311b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        
        $__internal_6e349796d49a88f57d0af57e8b3cffd21c6c955ebef2474ae073e3639666311b->leave($__internal_6e349796d49a88f57d0af57e8b3cffd21c6c955ebef2474ae073e3639666311b_prof);

    }

    // line 10
    public function block_head($context, array $blocks = array())
    {
        $__internal_7d5a7dee2cb5c585601c797405a27bae94f7d2c767fcca719c33422f82648656 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_7d5a7dee2cb5c585601c797405a27bae94f7d2c767fcca719c33422f82648656->enter($__internal_7d5a7dee2cb5c585601c797405a27bae94f7d2c767fcca719c33422f82648656_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        
        $__internal_7d5a7dee2cb5c585601c797405a27bae94f7d2c767fcca719c33422f82648656->leave($__internal_7d5a7dee2cb5c585601c797405a27bae94f7d2c767fcca719c33422f82648656_prof);

    }

    // line 33
    public function block_body($context, array $blocks = array())
    {
        $__internal_180bfc40b6e25ec1e72aa98f4f97c24278601e4763b18161cc649228780d3328 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_180bfc40b6e25ec1e72aa98f4f97c24278601e4763b18161cc649228780d3328->enter($__internal_180bfc40b6e25ec1e72aa98f4f97c24278601e4763b18161cc649228780d3328_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        
        $__internal_180bfc40b6e25ec1e72aa98f4f97c24278601e4763b18161cc649228780d3328->leave($__internal_180bfc40b6e25ec1e72aa98f4f97c24278601e4763b18161cc649228780d3328_prof);

    }

    public function getTemplateName()
    {
        return "@Twig/layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  119 => 33,  108 => 10,  97 => 7,  85 => 34,  83 => 33,  73 => 26,  63 => 19,  56 => 15,  50 => 11,  48 => 10,  44 => 9,  40 => 8,  36 => 7,  30 => 4,  25 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"{{ _charset }}\" />
        <meta name=\"robots\" content=\"noindex,nofollow\" />
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />
        <title>{% block title %}{% endblock %}</title>
        <link rel=\"icon\" type=\"image/png\" href=\"{{ include('@Twig/images/favicon.png.base64') }}\">
        <style>{{ include('@Twig/exception.css.twig') }}</style>
        {% block head %}{% endblock %}
    </head>
    <body>
        <header>
            <div class=\"container\">
                <h1 class=\"logo\">{{ include('@Twig/images/symfony-logo.svg') }} Symfony Exception</h1>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/doc\">
                        <span class=\"icon\">{{ include('@Twig/images/icon-book.svg') }}</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Docs
                    </a>
                </div>

                <div class=\"help-link\">
                    <a href=\"https://symfony.com/support\">
                        <span class=\"icon\">{{ include('@Twig/images/icon-support.svg') }}</span>
                        <span class=\"hidden-xs-down\">Symfony</span> Support
                    </a>
                </div>
            </div>
        </header>

        {% block body %}{% endblock %}
        {{ include('@Twig/base_js.html.twig') }}
    </body>
</html>
", "@Twig/layout.html.twig", "/home/tien/Projects/mbt-bundle/vendor/symfony/twig-bundle/Resources/views/layout.html.twig");
    }
}
