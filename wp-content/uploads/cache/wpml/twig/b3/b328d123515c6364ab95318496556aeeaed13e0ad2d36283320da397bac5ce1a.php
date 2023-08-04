<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* languages-notice.twig */
class __TwigTemplate_fcb08db77e41c52f845f873a7626c7f85d31d056e55067af81b467e84bbbcfc3 extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<div id=\"wcml_translations_message\" class=\"message error\">
    <p>";
        // line 2
        echo $this->getAttribute(($context["strings"] ?? null), "trnsl_available", []);
        echo "</p>

    <p>
        ";
        // line 5
        if (($context["is_multisite"] ?? null)) {
            // line 6
            echo "            <a href=\"";
            echo $this->getAttribute(($context["nonces"] ?? null), "debug_action", []);
            echo "\" class=\"button-primary\">
                ";
            // line 7
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "update_trnsl", []), "html", null, true);
            echo "
            </a>
        ";
        } else {
            // line 10
            echo "            <a href=\"";
            echo $this->getAttribute(($context["nonces"] ?? null), "upgrade_translations", []);
            echo "\" class=\"button-primary\">
                ";
            // line 11
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "update_trnsl", []), "html", null, true);
            echo "
            </a>
        ";
        }
        // line 14
        echo "        <a href=\"\" class=\"button\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "hide", []), "html", null, true);
        echo "</a>
        <input type=\"hidden\" id=\"wcml_hide_languages_notice\" value=\"";
        // line 15
        echo $this->getAttribute(($context["nonces"] ?? null), "hide_notice", []);
        echo "\" />
    </p>
</div>";
    }

    public function getTemplateName()
    {
        return "languages-notice.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  70 => 15,  65 => 14,  59 => 11,  54 => 10,  48 => 7,  43 => 6,  41 => 5,  35 => 2,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "languages-notice.twig", "/home/customer/www/nextgates.se/public_html/education/wp-content/plugins/woocommerce-multilingual/templates/languages-notice.twig");
    }
}
