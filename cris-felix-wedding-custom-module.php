<?php
/**
 * Plugin Name:     Cris and Felix wedding custom module
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Cris and Felix wedding custom module
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     cris-felix-wedding-custom-module
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Cris_Felix_Wedding_Custom_Module
 */

// Your code starts here.

require_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

use CrisFelixWeddingCustomModule\Loader;
use CrisFelixWeddingCustomModule\Actions\Implementations\Obtainer;

/*
    Prevent the email sending step for specific form
*/
add_action("wpcf7_before_send_mail", "wpcf7_do_something_else");
function wpcf7_do_something_else($cf7) {
    $wpcf7 = WPCF7_ContactForm::get_current();
    $wpcf7->skip_mail = true;
    try {
        Loader::loadCustomGuestType();
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
    }
}

function custom_columns($columns)
{
    unset($columns['title']);
    unset($columns['date']);
    return array_merge(
        $columns,
        array(
            'guest_name' => __('name'),
            'surname' => __('surname'),
            'nid' => __('nid'),
            'email' => __('email'),
            'phone' => __('phone'),
            'days' => __('days'),
            'upper_age' => __('upper_age'),
            'menu_type' => __('menu_type'),
            'extra_service' => __('extra_service')
        )
    );
}
add_filter('manage_guest_posts_columns', 'custom_columns');

function display_custom_columns($column, $post_id)
{
    switch ($column) {
        case 'guest_name':
            echo get_post_meta($post_id, 'guest_name', true);
            break;
        case 'surname':
            echo get_post_meta($post_id, 'surname', true);
            break;
        case 'nid':
            echo get_post_meta($post_id, 'nid', true);
            break;
        case 'email':
            echo get_post_meta($post_id, 'email', true);
            break;
        case 'phone':
            echo get_post_meta($post_id, 'phone', true);
            break;
        case 'days':
            $days =  get_post_meta($post_id, 'days', true);
            echo multiValueFieldHandling($days);
            break;
        case 'upper_age':
            echo (get_post_meta($post_id, 'upper_age', true))? "Sí" : "No";
            break;
        case 'menu_type':
            $menu_type =  get_post_meta($post_id, 'menu_type', true);
            echo multiValueFieldHandling($menu_type);
            break;
        case 'extra_service':
            $extraService =  get_post_meta($post_id, 'extra_service', true);
            echo multiValueFieldHandling($extraService);
            break;
    }
}
add_action('manage_guest_posts_custom_column', 'display_custom_columns', 10, 2);

function multiValueFieldHandling($multiValueResult) {
    if (!empty($multiValueResult)) {
        if (is_array($multiValueResult)) {
            return implode(", ", $multiValueResult);
        } else {
            return $multiValueResult;
        }
    } else {
        return "-";
    }
}

