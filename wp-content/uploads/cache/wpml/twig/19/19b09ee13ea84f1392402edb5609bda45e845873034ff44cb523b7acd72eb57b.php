<?php

/* troubleshooting.twig */
class __TwigTemplate_02d336233e75c9e6accb90c4d47cd09398803258b7efe571234f273793ef1514 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<div class=\"wrap wcml_trblsh\">
    <h2>";
        // line 2
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "troubl", array()), "html", null, true);
        echo "</h2>
    <div class=\"wcml_trbl_warning\">
        <h3>";
        // line 4
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "backup", array()), "html", null, true);
        echo "</h3>
    </div>
    <div class=\"trbl_variables_products\">
        <h3>";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync", array()), "html", null, true);
        echo "</h3>
        <ul>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_update_product_count\" />
                    ";
        // line 12
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "upd_prod_count", array()), "html", null, true);
        echo "
                    <span class=\"var_status\">";
        // line 13
        echo twig_escape_filter($this->env, ($context["prod_with_variations"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 14
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "prod_var", array()), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_product_variations\" checked=\"checked\" />
                    ";
        // line 20
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_var", array()), "html", null, true);
        echo "
                    <span class=\"var_status\">";
        // line 21
        echo twig_escape_filter($this->env, ($context["prod_with_variations"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 22
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", array()), "html", null, true);
        echo "</span>
                </label>

            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_gallery_images\" />
                    ";
        // line 29
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_gallery", array()), "html", null, true);
        echo "
                    <span class=\"gallery_status\">";
        // line 30
        echo twig_escape_filter($this->env, ($context["prod_count"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 31
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", array()), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_categories\" />
                    ";
        // line 37
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_cat", array()), "html", null, true);
        echo "
                    <span class=\"cat_status\">";
        // line 38
        echo twig_escape_filter($this->env, ($context["prod_categories_count"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 39
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", array()), "html", null, true);
        echo "</span>
                </label>

            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_duplicate_terms\" ";
        // line 45
        if (twig_test_empty(($context["all_products_taxonomies"] ?? null))) {
            echo "disabled=\"disabled\"";
        }
        echo " />
                    ";
        // line 46
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "dup_terms", array()), "html", null, true);
        echo "
                    <select id=\"attr_to_duplicate\" ";
        // line 47
        if (twig_test_empty(($context["all_products_taxonomies"] ?? null))) {
            echo "disabled=\"disabled\"";
        }
        echo " >
                        ";
        // line 48
        if (twig_test_empty(($context["all_products_taxonomies"] ?? null))) {
            // line 49
            echo "                            <option value=\"0\" >";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "none", array()), "html", null, true);
            echo "</option>
                        ";
        }
        // line 51
        echo "
                        ";
        // line 52
        $context["terms_count"] = 0;
        // line 53
        echo "                        ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["all_products_taxonomies"] ?? null));
        $context['loop'] = array(
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        );
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["tax"]) {
            // line 54
            echo "                            ";
            if ($this->getAttribute($context["loop"], "first", array())) {
                // line 55
                echo "                                ";
                $context["terms_count"] = $this->getAttribute($context["tax"], "terms_count", array());
                // line 56
                echo "                            ";
            }
            // line 57
            echo "
                            <option value=\"";
            // line 58
            echo twig_escape_filter($this->env, $this->getAttribute($context["tax"], "tax_key", array()));
            echo "\" rel=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["tax"], "terms_count", array()), "html", null, true);
            echo "\">
                                ";
            // line 59
            echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, $this->getAttribute($this->getAttribute($context["tax"], "labels", array()), "name", array())), "html", null, true);
            echo "
                            </option>
                        ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['tax'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 62
        echo "                    </select>
                    <span class=\"attr_status\">";
        // line 63
        echo twig_escape_filter($this->env, ($context["terms_count"] ?? null), "html", null, true);
        echo "</span>&nbsp;
                    <span>";
        // line 64
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", array()), "html", null, true);
        echo "</span>
                </label>

            </li>
            <li>
                <label>
                    <input type=\"checkbox\" id=\"wcml_sync_stock\" />
                    ";
        // line 71
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "sync_stock", array()), "html", null, true);
        echo "
                    <span class=\"stock_status\">";
        // line 72
        echo twig_escape_filter($this->env, ($context["sync_stock_count"] ?? null), "html", null, true);
        echo "</span>
                    <span>";
        // line 73
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "left", array()), "html", null, true);
        echo "</span>
                </label>
            </li>
            <li>
                <button type=\"button\" class=\"button-secondary\" id=\"wcml_trbl\">";
        // line 77
        echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "start", array()), "html", null, true);
        echo "</button>
                <input id=\"count_prod_variat\" type=\"hidden\" value=\"";
        // line 78
        echo twig_escape_filter($this->env, ($context["prod_with_variations"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_prod\" type=\"hidden\" value=\"";
        // line 79
        echo twig_escape_filter($this->env, ($context["prod_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_galleries\" type=\"hidden\" value=\"";
        // line 80
        echo twig_escape_filter($this->env, ($context["prod_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_categories\" type=\"hidden\" value=\"";
        // line 81
        echo twig_escape_filter($this->env, ($context["prod_categories_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_terms\" type=\"hidden\" value=\"<";
        // line 82
        echo twig_escape_filter($this->env, ($context["terms_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"count_stock\" type=\"hidden\" value=\"<";
        // line 83
        echo twig_escape_filter($this->env, ($context["sync_stock_count"] ?? null), "html", null, true);
        echo "\"/>
                <input id=\"sync_galerry_page\" type=\"hidden\" value=\"0\"/>
                <input id=\"sync_category_page\" type=\"hidden\" value=\"0\"/>
                <span class=\"spinner\"></span>
                ";
        // line 87
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_update_count", array());
        echo "
                ";
        // line 88
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_sync_variations", array());
        echo "
                ";
        // line 89
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_gallery_images", array());
        echo "
                ";
        // line 90
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_sync_categories", array());
        echo "
                ";
        // line 91
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_duplicate_terms", array());
        echo "
                ";
        // line 92
        echo $this->getAttribute(($context["nonces"] ?? null), "trbl_sync_stock", array());
        echo "
            </li>
        </ul>
        ";
        // line 95
        if (($context["product_type_sync_needed"] ?? null)) {
            // line 96
            echo "            <h3>";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "delete_terms", array()), "html", null, true);
            echo "</h3>
            <div>
                <button type=\"button\" class=\"button-secondary\" id=\"wcml_product_type_trbl\">";
            // line 98
            echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "start", array()), "html", null, true);
            echo "</button>
                <span class=\"product_type_spinner\"></span>
                <span class=\"product_type_fix_done\" style=\"display: none\">";
            // line 100
            echo twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "product_type_fix_done", array()), "html", null, true);
            echo "</span>
                ";
            // line 101
            echo $this->getAttribute(($context["nonces"] ?? null), "trbl_product_type_terms", array());
            echo "
            </div>
        ";
        }
        // line 104
        echo "    </div>
</div>";
    }

    public function getTemplateName()
    {
        return "troubleshooting.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  304 => 104,  298 => 101,  294 => 100,  289 => 98,  283 => 96,  281 => 95,  275 => 92,  271 => 91,  267 => 90,  263 => 89,  259 => 88,  255 => 87,  248 => 83,  244 => 82,  240 => 81,  236 => 80,  232 => 79,  228 => 78,  224 => 77,  217 => 73,  213 => 72,  209 => 71,  199 => 64,  195 => 63,  192 => 62,  175 => 59,  169 => 58,  166 => 57,  163 => 56,  160 => 55,  157 => 54,  139 => 53,  137 => 52,  134 => 51,  128 => 49,  126 => 48,  120 => 47,  116 => 46,  110 => 45,  101 => 39,  97 => 38,  93 => 37,  84 => 31,  80 => 30,  76 => 29,  66 => 22,  62 => 21,  58 => 20,  49 => 14,  45 => 13,  41 => 12,  33 => 7,  27 => 4,  22 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "troubleshooting.twig", "/home/customer/www/nextgates.se/public_html/educations/wp-content/plugins/woocommerce-multilingual/templates/troubleshooting.twig");
    }
}
