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
    $prueba = plugin_dir_path(__FILE__);
    $obtainer = new Obtainer();
    $loader = new Loader($obtainer);
	$loader->loadCustomGuestType();
}



