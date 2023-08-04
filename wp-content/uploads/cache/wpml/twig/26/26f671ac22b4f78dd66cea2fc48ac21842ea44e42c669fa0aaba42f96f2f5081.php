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

/* multi-currency.twig */
class __TwigTemplate_6b037590ed4c99abed060b930f1e866cced2e3c20dfa3537caea0ac67db6edd8 extends \WPML\Core\Twig\Template
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
        echo "
";
        // line 2
        echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('wp_do_action')->getCallable(), ["wcml_before_multi_currency_ui"]), "html", null, true);
        echo "


<form method=\"post\" action=\"";
        // line 5
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "action", []), "html", null, true);
        echo "\" id=\"wcml_mc_options\">
    ";
        // line 6
        echo $this->getAttribute(($context["form"] ?? null), "nonce", []);
        echo "
    <input type=\"hidden\" id=\"wcml_save_currency_nonce\" value=\"";
        // line 7
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "save_currency_nonce", []), "html", null, true);
        echo "\"/>
    <input type=\"hidden\" id=\"del_currency_nonce\" value=\"";
        // line 8
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "del_currency_nonce", []), "html", null, true);
        echo "\" />
    <input type=\"hidden\" id=\"currencies_list_nonce\" value=\"";
        // line 9
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "currencies_list_nonce", []), "html", null, true);
        echo "\" />
    <input type=\"hidden\" name=\"action\" value=\"save-mc-options\" />

    <div class=\"wcml-section \">
        <div class=\"wcml-section-header\">
            <h3>";
        // line 14
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "headers", []), "enable_disable", []), "html", null, true);
        echo "</h3>
        </div>
        <div class=\"wcml-section-content wcml-section-content-wide\">
            <p>
                <input type=\"checkbox\" name=\"multi_currency\" id=\"multi_currency_independent\" value=\"";
        // line 18
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "multi_currency_option", []), "html", null, true);
        echo "\"
                    ";
        // line 19
        if (($context["multi_currency_on"] ?? null)) {
            echo "checked=\"checked\"";
        }
        echo " ";
        if (($context["mco_disabled"] ?? null)) {
            echo "disabled=\"disabled\"";
        }
        echo " />
                <label for=\"multi_currency_independent\">
                    ";
        // line 21
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "label_mco", []), "html", null, true);
        echo "
                    &nbsp;
                    <a href=\"";
        // line 23
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "label_mco_learn_url", []), "html", null, true);
        echo "\">";
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "label_mco_learn_txt", []), "html", null, true);
        echo "</a>
                </label>
            </p>

            ";
        // line 27
        if (twig_test_empty(($context["wc_currency"] ?? null))) {
            // line 28
            echo "            <p>
                <i class=\"icon-warning-sign\"></i>
                ";
            // line 30
            echo ($context["wc_currency_empty_warn"] ?? null);
            echo "
            </p>
            ";
        }
        // line 33
        echo "
        </div>
    </div>

    ";
        // line 37
        if (($context["wc_currency"] ?? null)) {
            // line 38
            echo "    <div class=\"wcml-section\" id=\"multi-currency-per-language-details\" ";
            if (($this->getAttribute(($context["wcml_settings"] ?? null), "enable_multi_currency", []) != $this->getAttribute(($context["form"] ?? null), "multi_currency_option", []))) {
                echo "style=\"display:none\"";
            }
            echo ">

        <div class=\"wcml-section-header\">
            <h3>";
            // line 41
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "headers", []), "currencies", []), "html", null, true);
            echo "</h3>
        </div>

        <div class=\"wcml-section-content wcml-section-content-wide\">
            <div>
                <div class=\"currencies-table-content\">

                    <div class=\"tablenav top clearfix\">
                        <button type=\"button\" class=\"button-secondary wcml_add_currency alignright js-wcml-dialog-trigger\"
                                data-dialog=\"wcml_currency_options_\" data-content=\"wcml_currency_options_\" data-width=\"480\" data-height=\"580\">
                            <i class=\"otgs-ico-add otgs-ico-sm\"></i>
                            ";
            // line 52
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "add_currency_button", []), "html", null, true);
            echo "
                        </button>
                    </div>
                    <input type=\"hidden\" id=\"update_currency_lang_nonce\" value=\"";
            // line 55
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "update_currency_lang_nonce", []), "html", null, true);
            echo "\"/>

                        <table class=\"widefat currency_table\" id=\"currency-table\">
                            <thead>
                            <tr>
                                <th class=\"wcml-col-currency\">";
            // line 60
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "head_currency", []), "html", null, true);
            echo "</th>
                                <th class=\"wcml-col-rate\">";
            // line 61
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "head_rate", []), "html", null, true);
            echo "</th>
                            </tr>
                            </thead>
                            <tbody>
                            ";
            // line 65
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["currencies"] ?? null));
            foreach ($context['_seq'] as $context["code"] => $context["currency"]) {
                // line 66
                echo "                                <tr id=\"currency_row_";
                echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                echo "\" class=\"wcml-row-currency\">
                                    <td class=\"wcml-col-currency\">
                                        <span class=\"truncate\">";
                // line 68
                echo $this->getAttribute(($context["wc_currencies"] ?? null), $context["code"]);
                echo "</span>
                                        <small>";
                // line 69
                echo $this->getAttribute(($context["currencies_positions"] ?? null), $context["code"]);
                echo "</small>
                                    </td>
                                    ";
                // line 71
                if ((($context["wc_currency"] ?? null) == $context["code"])) {
                    // line 72
                    echo "                                        <td class=\"wcml-col-rate\">
                                            <span class=\"truncate\">";
                    // line 73
                    echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "default", []), "html", null, true);
                    echo "</span>
                                        </td>
                                    ";
                } else {
                    // line 76
                    echo "                                        <td class=\"wcml-col-rate\">
                                            1 ";
                    // line 77
                    echo \WPML\Core\twig_escape_filter($this->env, ($context["wc_currency"] ?? null), "html", null, true);
                    echo " = <span class=\"rate\">";
                    echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["currency"], "rate", []), "html", null, true);
                    echo "</span> ";
                    echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                    echo "
                                        </td>
                                    ";
                }
                // line 80
                echo "                                </tr>
                            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['code'], $context['currency'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 82
            echo "
                            <tr class=\"default_currency\">
                                <td colspan=\"3\">";
            // line 84
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "default_currency", []), "html", null, true);
            echo "
                                    <i class=\"wcml-tip otgs-ico-help\"
                                       data-tip=\"";
            // line 86
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "default_cur_tip", []), "html", null, true);
            echo "\"></i>

                                </td>
                            </tr>

                            </tbody>
                        </table>

                        <div class=\"currency_wrap\">
                            <div class=\"currency_inner\">
                                <table class=\"widefat currency_lang_table\" id=\"currency-lang-table\">
                                    <thead>
                                    <tr>
                                        <td colspan=\"";
            // line 99
            echo \WPML\Core\twig_escape_filter($this->env, \WPML\Core\twig_length_filter($this->env, ($context["active_languages"] ?? null)), "html", null, true);
            echo "\">";
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "help_title", []), "html", null, true);
            echo "</td>
                                    </tr>
                                    <tr class=\"currency-lang-flags\">
                                        ";
            // line 102
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["active_languages"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
                // line 103
                echo "                                            <th>
                                                <img src=\"";
                // line 104
                echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('get_flag_url')->getCallable(), [$this->getAttribute($context["language"], "code", [])]), "html", null, true);
                echo "\" width=\"18\" height=\"12\"/>
                                            </th>
                                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['language'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 107
            echo "                                    </tr>
                                    </thead>
                                    <tbody>

                                    ";
            // line 111
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["currencies"] ?? null));
            foreach ($context['_seq'] as $context["code"] => $context["currency"]) {
                // line 112
                echo "                                        <tr id=\"currency_row_langs_";
                echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                echo "\" class=\"wcml-row-currency-lang\">
                                            ";
                // line 113
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["active_languages"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
                    // line 114
                    echo "                                                <td class=\"currency_languages\">
                                                    <ul>
                                                        ";
                    // line 116
                    $context["title_yes"] = sprintf($this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "disable_for", []), call_user_func_array($this->env->getFunction('get_currency_name')->getCallable(), [$context["code"]]), $this->getAttribute($context["language"], "display_name", []));
                    // line 117
                    echo "                                                        ";
                    $context["title_no"] = sprintf($this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "enable_for", []), call_user_func_array($this->env->getFunction('get_currency_name')->getCallable(), [$context["code"]]), $this->getAttribute($context["language"], "display_name", []));
                    // line 118
                    echo "                                                        <li class=\"on\" data-lang=\"";
                    echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["language"], "code", []), "html", null, true);
                    echo "\">
                                                            <a class=\"";
                    // line 119
                    if (call_user_func_array($this->env->getFunction('is_currency_on')->getCallable(), [$context["code"], $this->getAttribute($context["language"], "code", [])])) {
                        echo "otgs-ico-yes";
                    } else {
                        echo " otgs-ico-no";
                    }
                    echo "\"
                                                               data-language=\"";
                    // line 120
                    echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["language"], "code", []), "html", null, true);
                    echo "\"
                                                               data-currency=\"";
                    // line 121
                    echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                    echo "\" href=\"#\"
                                                                    ";
                    // line 122
                    if (call_user_func_array($this->env->getFunction('is_currency_on')->getCallable(), [($context["wc_currency"] ?? null), $this->getAttribute($context["language"], "code", [])])) {
                        // line 123
                        echo "                                                                        title=\"";
                        echo \WPML\Core\twig_escape_filter($this->env, ($context["title_yes"] ?? null), "html", null, true);
                        echo "\" data-title-alt=\"";
                        echo \WPML\Core\twig_escape_filter($this->env, ($context["title_no"] ?? null), "html", null, true);
                        echo "\"
                                                                    ";
                    } else {
                        // line 125
                        echo "                                                                        title=\"";
                        echo \WPML\Core\twig_escape_filter($this->env, ($context["title_no"] ?? null), "html", null, true);
                        echo "\" data-title-alt=\"";
                        echo \WPML\Core\twig_escape_filter($this->env, ($context["title_yes"] ?? null), "html", null, true);
                        echo "\"
                                                                    ";
                    }
                    // line 127
                    echo "                                                            ></a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['language'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 132
                echo "                                        </tr>
                                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['code'], $context['currency'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 134
            echo "
                                    <tr class=\"wcml-row-currency-lang empty-currency-language-row hidden\">
                                        ";
            // line 136
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["active_languages"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
                // line 137
                echo "                                            <td class=\"currency_languages\">
                                                <ul>
                                                    <li class=\"on\" data-lang=\"";
                // line 139
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["language"], "code", []), "html", null, true);
                echo "\">
                                                        <a class=\"otgs-ico-yes\"
                                                           data-language=\"";
                // line 141
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["language"], "code", []), "html", null, true);
                echo "\" data-currency=\"\" href=\"#\"
                                                           title=\"";
                // line 142
                echo \WPML\Core\twig_escape_filter($this->env, sprintf($this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "disable_for", []), "%code%", $this->getAttribute($context["language"], "display_name", [])), "html", null, true);
                echo "\"
                                                           data-title-alt=\"";
                // line 143
                echo \WPML\Core\twig_escape_filter($this->env, sprintf($this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "enable_for", []), "%code%", $this->getAttribute($context["language"], "display_name", [])), "html", null, true);
                echo "\"></a>
                                                    </li>
                                                </ul>
                                            </td>
                                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['language'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 148
            echo "                                    </tr>

                                    <tr class=\"default_currency\">
                                        ";
            // line 151
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["active_languages"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
                // line 152
                echo "                                            <td align=\"center\">
                                                <select rel=\"";
                // line 153
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($context["language"], "code", []), "html", null, true);
                echo "\">
                                                    <option value=\"0\"
                                                            ";
                // line 155
                if ((call_user_func_array($this->env->getFunction('get_language_currency')->getCallable(), [$this->getAttribute($context["language"], "code", [])]) == 0)) {
                    echo "selected=\"selected\"";
                }
                echo ">";
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "keep_currency", []), "html", null, true);
                echo "</option>
                                                    ";
                // line 156
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["currencies"] ?? null));
                foreach ($context['_seq'] as $context["code"] => $context["currency"]) {
                    // line 157
                    echo "                                                        ";
                    if (call_user_func_array($this->env->getFunction('is_currency_on')->getCallable(), [$context["code"], $this->getAttribute($context["language"], "code", [])])) {
                        // line 158
                        echo "                                                            <option value=\"";
                        echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                        echo "\"
                                                                    ";
                        // line 159
                        if ((call_user_func_array($this->env->getFunction('get_language_currency')->getCallable(), [$this->getAttribute($context["language"], "code", [])]) == $context["code"])) {
                            echo "selected=\"selected\"";
                        }
                        echo ">";
                        echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                        echo "</option>
                                                        ";
                    }
                    // line 161
                    echo "                                                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['code'], $context['currency'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 162
                echo "                                                </select>
                                            </td>
                                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['language'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 165
            echo "                                    </tr>

                                    </tbody>
                                </table>
                                <input type=\"hidden\" id=\"wcml_update_default_currency_nonce\"
                                       value=\"";
            // line 170
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "wpdate_default_cur_nonce", []), "html", null, true);
            echo "\"/>

                            </div>
                        </div>

                        <table class=\"widefat currency_settings_table\" id=\"currency-settings-table\">
                            <thead>
                            <tr>
                                <th colspan=\"2\">";
            // line 178
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "settings", []), "html", null, true);
            echo "</th>
                            </tr>
                            </thead>
                            <tbody>

                            ";
            // line 183
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["currencies"] ?? null));
            foreach ($context['_seq'] as $context["code"] => $context["currency"]) {
                // line 184
                echo "                                <tr id=\"wcml-row-currency-actions-";
                echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                echo "\" class=\"wcml-row-currencies-actions\">
                                    <td class=\"wcml-col-edit\">
                                        <a href=\"#\" title=\"";
                // line 186
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "edit", []), "html", null, true);
                echo "\"
                                           class=\"edit_currency js-wcml-dialog-trigger\"
                                           data-currency=\"";
                // line 188
                echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                echo "\" data-content=\"wcml_currency_options_";
                echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                echo "\"
                                           data-dialog=\"wcml_currency_options_";
                // line 189
                echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                echo "\"
                                           data-height=\"530\" data-width=\"480\">
                                            <i class=\"otgs-ico-edit\" title=\"";
                // line 191
                echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "edit", []), "html", null, true);
                echo "\"></i>
                                        </a>
                                    </td>
                                    ";
                // line 194
                if ((($context["wc_currency"] ?? null) != $context["code"])) {
                    // line 195
                    echo "                                        <td class=\"wcml-col-delete\">
                                            <a title=\"";
                    // line 196
                    echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "delete", []), "html", null, true);
                    echo "\" class=\"delete_currency\"
                                               data-currency_name=\"";
                    // line 197
                    echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["wc_currencies"] ?? null), $context["code"]), "html", null, true);
                    echo "\"
                                               data-currency_symbol=\"";
                    // line 198
                    echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('get_currency_symbol')->getCallable(), [$context["code"]]), "html", null, true);
                    echo "\"
                                               data-currency=\"";
                    // line 199
                    echo \WPML\Core\twig_escape_filter($this->env, $context["code"], "html", null, true);
                    echo "\" href=\"#\">
                                                <i class=\"otgs-ico-delete\"
                                                   title=\"";
                    // line 201
                    echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["strings"] ?? null), "currencies_table", []), "delete", []), "html", null, true);
                    echo "\"></i>
                                            </a>
                                        </td>
                                    ";
                }
                // line 205
                echo "                                </tr>
                            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['code'], $context['currency'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 207
            echo "
                            <tr class=\"default_currency\">
                                <td colspan=\"2\"></td>
                            </tr>
                            </tbody>
                        </table>
                </div>

                <ul id=\"display_custom_prices_select\">
                    <li>
                        <input type=\"checkbox\" name=\"display_custom_prices\" id=\"display_custom_prices\"
                           value=\"1\" ";
            // line 218
            if ($this->getAttribute($this->getAttribute(($context["form"] ?? null), "custom_prices_select", []), "checked", [])) {
                echo " checked=\"checked\"";
            }
            echo ">
                        <label for=\"display_custom_prices\">";
            // line 219
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "custom_prices_select", []), "label", []), "html", null, true);
            echo "</label>
                        <i class=\"otgs-ico-help wcml-tip\" data-tip=\"";
            // line 220
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "custom_prices_select", []), "tip", []), "html", null, true);
            echo "\"></i>
                    </li>
                </ul>

            </div>
        </div>

    </div>

    ";
            // line 229
            $this->loadTemplate("exchange-rates.twig", "multi-currency.twig", 229)->display(twig_array_merge($context, ($context["exchange_rates"] ?? null)));
            // line 230
            echo "
    ";
            // line 231
            echo \WPML\Core\twig_escape_filter($this->env, call_user_func_array($this->env->getFunction('wp_do_action')->getCallable(), ["wcml_before_currency_switcher_options"]), "html", null, true);
            echo "

    ";
            // line 233
            $this->loadTemplate("currency-switcher-options.twig", "multi-currency.twig", 233)->display($context);
            // line 234
            echo "
    <input type=\"hidden\" id=\"wcml_warn_message\" value=\"";
            // line 235
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "navigate_warn", []), "html", null, true);
            echo "\" />
    <input type=\"hidden\" id=\"wcml_warn_disable_language_massage\" value=\"";
            // line 236
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "cur_lang_warn", []), "html", null, true);
            echo "\" />

    <p class=\"wpml-margin-top-sm\">
        <input id=\"wcml_mc_options_submit\" type='submit' value='";
            // line 239
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "submit", []), "html", null, true);
            echo "' class='button-primary'/>
    </p>

    ";
        }
        // line 243
        echo "
</form>
";
    }

    public function getTemplateName()
    {
        return "multi-currency.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  609 => 243,  602 => 239,  596 => 236,  592 => 235,  589 => 234,  587 => 233,  582 => 231,  579 => 230,  577 => 229,  565 => 220,  561 => 219,  555 => 218,  542 => 207,  535 => 205,  528 => 201,  523 => 199,  519 => 198,  515 => 197,  511 => 196,  508 => 195,  506 => 194,  500 => 191,  495 => 189,  489 => 188,  484 => 186,  478 => 184,  474 => 183,  466 => 178,  455 => 170,  448 => 165,  440 => 162,  434 => 161,  425 => 159,  420 => 158,  417 => 157,  413 => 156,  405 => 155,  400 => 153,  397 => 152,  393 => 151,  388 => 148,  377 => 143,  373 => 142,  369 => 141,  364 => 139,  360 => 137,  356 => 136,  352 => 134,  345 => 132,  335 => 127,  327 => 125,  319 => 123,  317 => 122,  313 => 121,  309 => 120,  301 => 119,  296 => 118,  293 => 117,  291 => 116,  287 => 114,  283 => 113,  278 => 112,  274 => 111,  268 => 107,  259 => 104,  256 => 103,  252 => 102,  244 => 99,  228 => 86,  223 => 84,  219 => 82,  212 => 80,  202 => 77,  199 => 76,  193 => 73,  190 => 72,  188 => 71,  183 => 69,  179 => 68,  173 => 66,  169 => 65,  162 => 61,  158 => 60,  150 => 55,  144 => 52,  130 => 41,  121 => 38,  119 => 37,  113 => 33,  107 => 30,  103 => 28,  101 => 27,  92 => 23,  87 => 21,  76 => 19,  72 => 18,  65 => 14,  57 => 9,  53 => 8,  49 => 7,  45 => 6,  41 => 5,  35 => 2,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "multi-currency.twig", "/home/customer/www/nextgates.se/public_html/education/wp-content/plugins/woocommerce-multilingual/templates/multi-currency/multi-currency.twig");
    }
}
