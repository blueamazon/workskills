<?php

namespace WDRPro\App\Helpers;

use Wdr\App\Helpers\Helper;
use Wdr\App\Helpers\Validation;
use Wdr\App\Helpers\Woocommerce;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CoreMethodCheck
{
    public static function getConvertedFixedPrice($value, $type = ''){
        if(method_exists('\Wdr\App\Helpers\Woocommerce', 'getConvertedFixedPrice')){
            return Woocommerce::getConvertedFixedPrice($value, $type);
        }
        return $value;
    }

    public static function create_nonce($action = -1){
        if(method_exists('\Wdr\App\Helpers\Helper', 'create_nonce')){
            return Helper::create_nonce($action);
        }
        return '';
    }

    public static function validateRequest($method){
        if(method_exists('\Wdr\App\Helpers\Helper', 'validateRequest')){
            return Helper::validateRequest($method);
        }
        return false;
    }

    public static function isValidLicenceKey($licence_key){
        if(method_exists('\Wdr\App\Helpers\Validation', 'validateLicenceKay')){
            return Validation::validateLicenceKay($licence_key);
        }
        return false;
    }

    public static function hasAdminPrivilege(){
        if(method_exists('\Wdr\App\Helpers\Helper', 'hasAdminPrivilege')){
            return Helper::hasAdminPrivilege();
        }
        return false;
    }

    public static function getCleanHtml($html){
        if(method_exists('\Wdr\App\Helpers\Helper', 'getCleanHtml')){
            return Helper::getCleanHtml($html);
        } else {
            try {
                $html = html_entity_decode($html);
                $html =   preg_replace('/(<(script|style|iframe)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $html);
                $allowed_html = array(
                    'br' => array(),
                    'strong' => array(),
                    'span' => array('class' => array()),
                    'div' => array('class' => array()),
                    'p' => array('class' => array()),
                );
                return wp_kses($html, $allowed_html);
            } catch (\Exception $e){
                return '';
            }
        }
    }

    /**
     * check rtl site
     * @return bool
     */
    public static function isRTLEnable(){
        if(method_exists('\Wdr\App\Helpers\Woocommerce', 'isRTLEnable')){
            return Woocommerce::isRTLEnable();
        }
        return false;
    }

    /**
     * get orders list by condition
     * @param array $conditions
     * @return int[]|WP_Post[]
     */
    static function getOrdersThroughWPQuery($args = array())
    {
        if(method_exists('\Wdr\App\Helpers\Woocommerce', 'getOrdersThroughWPQuery')){
            return Woocommerce::getOrdersThroughWPQuery($args);
        } else {
            $default_args = array(
                'posts_per_page' => -1,
                'post_type' => Woocommerce::getOrderPostType(),
                'post_status' => array_keys(Woocommerce::getOrderStatusList()),
                'orderby' => 'ID',
                'order' => 'DESC'
            );
            $args = array_merge($default_args, $args);
            $query = new \WP_Query($args);
            return  $query->get_posts();
        }
    }

    /**
     * Generate key from any data
     * @param array/object/string $data
     * @return string
     */
    static function generateBase64Encode($data){
        if(method_exists('\Wdr\App\Helpers\Woocommerce', 'generateBase64Encode')){
            return Woocommerce::generateBase64Encode($data);
        } else {
            return base64_encode(serialize($data));
        }
    }

    static function wc_format_decimal($price, $dp = false, $trim_zeros = false){
        if(method_exists('\Wdr\App\Helpers\Woocommerce', 'wc_format_decimal')){
            return Woocommerce::wc_format_decimal($price, $dp, $trim_zeros);
        } else {
            return $price;
        }
    }

    /**
     * Get title of product
     * @param $product - woocommerce product object
     * @return string
     */
    static function getTitleOfProduct($product){
        if(method_exists('\Wdr\App\Helpers\Woocommerce', 'getTitleOfProduct')){
            return Woocommerce::getTitleOfProduct($product);
        } else {
            return get_the_title($product);
        }
    }
}