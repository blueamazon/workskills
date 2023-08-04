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

/* custom-currency-options.twig */
class __TwigTemplate_d932ccf7bf85f4bd09fa63a686d1d080e4661d4429239dc117454dd9bb54c3a3 extends \WPML\Core\Twig\Template
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
        echo "<div class=\"wcml-dialog hidden\" id=\"wcml_currency_options_";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
        echo "\" title=\"";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "title", []), "html", null, true);
        echo "\">

    <div class=\"wcml_currency_options wcml-co-dialog\">

        <form id=\"wcml_currency_options_form_";
        // line 5
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
        echo "\" method=\"post\" action=\"\">

            ";
        // line 7
        if ((twig_test_empty($this->getAttribute(($context["args"] ?? null), "currency_code", [])) && (($context["current_currency"] ?? null) != $this->getAttribute(($context["args"] ?? null), "default_currency", [])))) {
            // line 8
            echo "                <div class=\"wpml-form-row currency_code\">
                    <label for=\"wcml_currency_options_code_";
            // line 9
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "select", []), "html", null, true);
            echo "</label>
                    <select name=\"currency_options[code]\" id=\"wcml_currency_options_code_";
            // line 10
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">
                        ";
            // line 11
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["args"] ?? null), "wc_currencies", []));
            foreach ($context['_seq'] as $context["code"] => $context["name"]) {
                // line 12
                echo "                        ";
                if (((null === $this->getAttribute($this->getAttribute(($context["args"] ?? null), "currencies", []), $context["code"])) && ($context["code"] != $this->getAttribute(($context["args"] ?? null), "default_currency", [])))) {
                    // line 13
                    echo "                        <option value=\"";
                    echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                    echo "\" ";
                    if (($context["code"] == $this->getAttribute(($context["args"] ?? null), "currency_code", []))) {
                        echo "selected=\"selected\"";
                    }
                    echo " data-symbol=\"";
                    echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('get_currency_symbol')->getCallable(), [$context["code"]]), "html", null, true);
                    echo "\" >";
                    echo $context["name"];
                    echo " (";
                    echo call_user_func_array($this->env->getFunction('get_currency_symbol')->getCallable(), [$context["code"]]);
                    echo " )</option>
                        ";
                }
                // line 15
                echo "                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['code'], $context['name'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 16
            echo "                    </select>
                </div>
                ";
        } else {
            // line 19
            echo "                <input type=\"hidden\" name=\"currency_options[code]\" value=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\" />
                ";
        }
        // line 21
        echo "
                ";
        // line 22
        if ((($context["current_currency"] ?? null) != $this->getAttribute(($context["args"] ?? null), "default_currency", []))) {
            // line 23
            echo "                <div class=\"wpml-form-row wcml-co-exchange-rate\">
                    <label for=\"wcml_currency_options_rate_";
            // line 24
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rate", []), "label", []), "html", null, true);
            echo "</label>
                    <div class=\"wcml-co-set-rate\">
                        1 ";
            // line 26
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "default_currency", []), "html", null, true);
            echo " = <input name=\"currency_options[rate]\" size=\"5\" type=\"number\"
                                                               class=\"wcml-exchange-rate";
            // line 27
            if (($context["automatic_rates"] ?? null)) {
                echo " wcml-tip";
            }
            echo "\"
                                                               min=\"";
            // line 28
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rate", []), "min", []), "html", null, true);
            echo "\" step=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rate", []), "step", []), "html", null, true);
            echo "\"  value=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rate", []), "html", null, true);
            echo "\" data-message=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rate", []), "only_numeric", []), "html", null, true);
            echo "\"
                                                               id=\"wcml_currency_options_rate_";
            // line 29
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\"
                                                                ";
            // line 30
            if (($context["automatic_rates"] ?? null)) {
                echo "readonly=\"readonly\" data-tip=\"";
                echo \WPML\Core\twig_escape_filter($this->env, ($context["automatic_rates_tip"] ?? null), "html", null, true);
                echo "\"";
            }
            echo "/>
                        <span class=\"this-currency\">";
            // line 31
            echo \WPML\Core\twig_escape_filter($this->env, ($context["current_currency"] ?? null), "html", null, true);
            echo "</span>
                        <span class=\"wcml-error\" style=\"display: none\">";
            // line 32
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "number_error", []), "html", null, true);
            echo "</span>
                        ";
            // line 33
            if ($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "updated", [])) {
                // line 34
                echo "                        <small>
                            ";
                // line 35
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rate", []), "set_on", []), "html", null, true);
                echo " <i>";
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rate", []), "previous", []), "html", null, true);
                echo "</i>
                        </small>
                        ";
            }
            // line 38
            echo "                    </div>
                </div>

                <hr>

                <div class=\"wpml-form-row wcml-co-preview\">
                    <label><strong>";
            // line 44
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "preview", []), "label", []), "html", null, true);
            echo "</strong></label>
                    <p class=\"wcml-co-preview-value\">
                        ";
            // line 46
            echo $this->getAttribute($this->getAttribute(($context["form"] ?? null), "preview", []), "value", []);
            echo "
                    </p>
                </div>

                <div class=\"wpml-form-row\">
                    <label for=\"wcml_currency_options_position_";
            // line 51
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "position", []), "label", []), "html", null, true);
            echo "</label>
                    <select class=\"currency_option_position\" name=\"currency_options[position]\" id=\"wcml_currency_options_position_";
            // line 52
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">
                        <option value=\"left\" ";
            // line 53
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "position", []) == "left")) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "position", []), "left", []), "html", null, true);
            echo "</option>
                        <option value=\"right\" ";
            // line 54
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "position", []) == "right")) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "position", []), "right", []), "html", null, true);
            echo "</option>
                        <option value=\"left_space\" ";
            // line 55
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "position", []) == "left_space")) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "position", []), "left_space", []), "html", null, true);
            echo "</option>
                        <option value=\"right_space\" ";
            // line 56
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "position", []) == "right_space")) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "position", []), "right_space", []), "html", null, true);
            echo "</option>
                    </select>
                </div>

                <div class=\"wpml-form-row\">
                    <label for=\"wcml_currency_options_thousand_";
            // line 61
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "thousand_sep", []), "label", []), "html", null, true);
            echo "</label>
                    <select class=\"currency_option_thousand_sep\" name=\"currency_options[thousand_sep]\" id=\"wcml_currency_options_thousand_";
            // line 62
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">
                        <option value=\".\" ";
            // line 63
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "thousand_sep", []) == ".")) {
                echo "selected=\"selected\"";
            }
            echo ">.</option>
                        <option value=\",\" ";
            // line 64
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "thousand_sep", []) == ",")) {
                echo "selected=\"selected\"";
            }
            echo ">,</option>
                    </select>
                </div>

                <div class=\"wpml-form-row\">
                    <label for=\"wcml_currency_options_decimal_";
            // line 69
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "decimal_sep", []), "label", []), "html", null, true);
            echo "</label>
                    <select class=\"currency_option_decimal_sep\" name=\"currency_options[decimal_sep]\" id=\"wcml_currency_options_decimal_";
            // line 70
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">
                        <option value=\".\" ";
            // line 71
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "decimal_sep", []) == ".")) {
                echo "selected=\"selected\"";
            }
            echo ">.</option>
                        <option value=\",\" ";
            // line 72
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "decimal_sep", []) == ",")) {
                echo "selected=\"selected\"";
            }
            echo ">,</option>
                    </select>
                </div>

                <div class=\"wpml-form-row\">
                    <label for=\"wcml_currency_options_decimals_";
            // line 77
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "num_decimals", []), "label", []), "html", null, true);
            echo "</label>
                    <input name=\"currency_options[num_decimals]\" type=\"number\" class=\"currency_option_decimals\"
                        value=\"";
            // line 79
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "num_decimals", []), "html", null, true);
            echo "\" min=\"0\" step=\"1\" data-message=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "num_decimals", []), "only_numeric", []), "html", null, true);
            echo "\" id=\"wcml_currency_options_numbers_of_decimals_";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\"/>
                </div>

                <hr/>

                <div class=\"wpml-form-row\">
                    <label for=\"wcml_currency_options_rounding_";
            // line 85
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "label", []), "html", null, true);
            echo " <i class=\"wcml-tip otgs-ico-help\" data-tip=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "rounding_tooltip", []), "html", null, true);
            echo "\"></i></label>
                    <select name=\"currency_options[rounding]\" id=\"wcml_currency_options_rounding_";
            // line 86
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">
                        <option value=\"disabled\" ";
            // line 87
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding", []) == "disabled")) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "disabled", []), "html", null, true);
            echo "</option>
                        <option value=\"up\" ";
            // line 88
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding", []) == "up")) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "up", []), "html", null, true);
            echo "</option>
                        <option value=\"down\" ";
            // line 89
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding", []) == "down")) {
                echo "selected=\"selected\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "down", []), "html", null, true);
            echo "</option>
                        <option value=\"nearest\" ";
            // line 90
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding", []) == "nearest")) {
                echo "selected=\"nearest\"";
            }
            echo ">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "nearest", []), "html", null, true);
            echo "</option>
                    </select>
                </div>

                <div class=\"wpml-form-row\">
                    <label for=\"wcml_currency_options_increment_";
            // line 95
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "increment", []), "html", null, true);
            echo " <i class=\"wcml-tip otgs-ico-help\" data-tip=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "increment_tooltip", []), "html", null, true);
            echo "\"></i></label>
                    <select name=\"currency_options[rounding_increment]\" id=\"wcml_currency_options_increment_";
            // line 96
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">
                        <option value=\"1\" ";
            // line 97
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding_increment", []) == "1")) {
                echo "selected=\"selected\"";
            }
            echo ">1</option>
                        <option value=\"10\" ";
            // line 98
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding_increment", []) == "10")) {
                echo "selected=\"selected\"";
            }
            echo ">10</option>
                        <option value=\"100\" ";
            // line 99
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding_increment", []) == "100")) {
                echo "selected=\"selected\"";
            }
            echo ">100</option>
                        <option value=\"1000\" ";
            // line 100
            if (($this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "rounding_increment", []) == "1000")) {
                echo "selected=\"selected\"";
            }
            echo ">1000</option>
                    </select>
                </div>

                <div class=\"wpml-form-row\">
                    <label for=\"wcml_currency_options_subtract_";
            // line 105
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "autosubtract", []), "label", []), "html", null, true);
            echo " <i class=\"wcml-tip otgs-ico-help\" data-tip=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "rounding", []), "autosubtract_tooltip", []), "html", null, true);
            echo "\"></i></label>

                    <input name=\"currency_options[auto_subtract]\" class=\"abstract_amount\"
                        value=\"";
            // line 108
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["args"] ?? null), "currency", []), "auto_subtract", []), "html", null, true);
            echo "\" type=\"number\" data-message=\"";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "autosbtract", []), "only_numeric", []), "html", null, true);
            echo "\"
                        id=\"wcml_currency_options_subtract_";
            // line 109
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["args"] ?? null), "currency_code", []), "html", null, true);
            echo "\"/>
                </div>

                <hr/>
            ";
        }
        // line 114
        echo "
            <label class=\"label-header\"><strong>";
        // line 115
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "payment_gateways", []), "label", []), "html", null, true);
        echo "</strong></label>

            <label class=\"wcml-gateways-switcher\">
                <input name=\"currency_options[gateways_enabled]\" type=\"checkbox\" class=\"wcml-gateways-enabled otgs-switcher-input\" ";
        // line 118
        if (($context["payment_gateways_enabled"] ?? null)) {
            echo "checked=\"checked\"";
        }
        echo " />
                <span class=\"otgs-switcher wpml-theme\" data-on=\"ON\" data-off=\"OFF\">";
        // line 119
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "payment_gateways", []), "settings_label", []), "html", null, true);
        echo "</span>
                <a class=\"wpml-external-link\" href=\"";
        // line 120
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "payment_gateways", []), "learn_url", []), "html", null, true);
        echo "\" target=\"_blank\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "payment_gateways", []), "learn_txt", []), "html", null, true);
        echo "</a>
            </label>

            <div class=\"wcml-gateways\" ";
        // line 123
        if ( !($context["payment_gateways_enabled"] ?? null)) {
            echo "style=\"display: none;\"";
        }
        echo ">
                ";
        // line 124
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["payment_gateways"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["gateway"]) {
            // line 125
            echo "                    ";
            echo $this->getAttribute($context["gateway"], "get_settings_output", [0 => ($context["current_currency"] ?? null), 1 => ($context["active_currencies"] ?? null)], "method");
            echo "
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['gateway'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 127
        echo "            </div>

            <footer class=\"wpml-dialog-footer\">
                <input type=\"button\" class=\"cancel wcml-dialog-close-button wpml-dialog-close-button alignleft\"
                    value=\"";
        // line 131
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "cancel", []), "html", null, true);
        echo "\" data-currency=\"";
        echo \WPML\Core\twig_escape_filter($this->env, ($context["current_currency"] ?? null), "html", null, true);
        echo "\" data-symbol=\"";
        echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('get_currency_symbol')->getCallable(), [($context["current_currency"] ?? null)]), "html", null, true);
        echo "\" />&nbsp;
                <input type=\"submit\" class=\"wcml-dialog-close-button wpml-dialog-close-button button-primary currency_options_save alignright\"
                    value=\"";
        // line 133
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "save", []), "html", null, true);
        echo "\" data-currency=\"";
        echo \WPML\Core\twig_escape_filter($this->env, ($context["current_currency"] ?? null), "html", null, true);
        echo "\" data-symbol=\"";
        echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('get_currency_symbol')->getCallable(), [($context["current_currency"] ?? null)]), "html", null, true);
        echo "\" data-stay=\"1\" />
            </footer>
        </form>

    </div>

</div>

";
    }

    public function getTemplateName()
    {
        return "custom-currency-options.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  482 => 133,  473 => 131,  467 => 127,  458 => 125,  454 => 124,  448 => 123,  440 => 120,  436 => 119,  430 => 118,  424 => 115,  421 => 114,  413 => 109,  407 => 108,  397 => 105,  387 => 100,  381 => 99,  375 => 98,  369 => 97,  365 => 96,  357 => 95,  345 => 90,  337 => 89,  329 => 88,  321 => 87,  317 => 86,  309 => 85,  296 => 79,  289 => 77,  279 => 72,  273 => 71,  269 => 70,  263 => 69,  253 => 64,  247 => 63,  243 => 62,  237 => 61,  225 => 56,  217 => 55,  209 => 54,  201 => 53,  197 => 52,  191 => 51,  183 => 46,  178 => 44,  170 => 38,  162 => 35,  159 => 34,  157 => 33,  153 => 32,  149 => 31,  141 => 30,  137 => 29,  127 => 28,  121 => 27,  117 => 26,  110 => 24,  107 => 23,  105 => 22,  102 => 21,  96 => 19,  91 => 16,  85 => 15,  69 => 13,  66 => 12,  62 => 11,  58 => 10,  52 => 9,  49 => 8,  47 => 7,  42 => 5,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "custom-currency-options.twig", "/home/customer/www/nextgates.se/public_html/education/wp-content/plugins/woocommerce-multilingual/templates/multi-currency/custom-currency-options.twig");
    }
}
