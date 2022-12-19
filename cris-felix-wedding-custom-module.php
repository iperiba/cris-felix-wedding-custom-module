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
use CrisFelixWeddingCustomModule\Actions\Implementations\Checker;
use CrisFelixWeddingCustomModule\Actions\Implementations\EntityGenerator;
use CrisFelixWeddingCustomModule\Actions\Implementations\GuestUploader;
use CrisFelixWeddingCustomModule\Actions\Implementations\SendMailDatabaseGestor;
use CrisFelixWeddingCustomModule\Actions\Implementations\SpotifyKeyGetter;
use CrisFelixWeddingCustomModule\Actions\Implementations\SpotifyRequestHandler;
use CrisFelixWeddingCustomModule\Actions\Implementations\SpotifyKeyRenewal;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

global $cris_felix_wedding_db_version;
$cris_felix_wedding_db_version = '1.1';

function crisFelixWedding_install() {
    global $wpdb;
    global $cris_felix_wedding_db_version;

    $table_name = $wpdb->prefix . 'send_email';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
		form_instance_hash varchar(40) NOT NULL,
		send_email BOOLEAN NOT NULL DEFAULT 1,
		PRIMARY KEY  (id)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );


    $table_name = $wpdb->prefix . 'spotify_authorization';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
		spotify_authorization TEXT NOT NULL,
		spotify_authorization_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) $charset_collate;";

    dbDelta( $sql );

    add_option( 'cris_felix_wedding_db_version', $cris_felix_wedding_db_version );
}
register_activation_hook( __FILE__, 'crisFelixWedding_install' );

function cris_felix_wedding_custom_module_update_db_check() {
    global $cris_felix_wedding_db_version;
    if ( get_site_option( 'cris_felix_wedding_db_version' ) != $cris_felix_wedding_db_version) {
        crisFelixWedding_install();
    }
}
add_action( 'plugins_loaded', 'cris_felix_wedding_custom_module_update_db_check' );

function cris_felix_wedding_custom_module_insert_guest($cf7) {
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__.'/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());

    $checker = new Checker($logger);
    $entityGenerator = new EntityGenerator($logger);
    $guestUploader = new GuestUploader($logger);
    $obtainer = new Obtainer($logger);
    $sendMailDatabaseGestor = new SendMailDatabaseGestor($logger);
    $currentHashForm = getCurrentHashForm();
    $loader = new Loader($checker, $entityGenerator, $guestUploader, $obtainer, $sendMailDatabaseGestor, $currentHashForm);

    try {
        $loader->loadCustomGuestType();
    } catch (Exception $e) {
        $logger->error($e->getMessage());
    }
}
add_action("wpcf7_before_send_mail", "cris_felix_wedding_custom_module_insert_guest");

function cris_felix_wedding_custom_module_avoid_mail($f){
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__.'/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());

    $currentHashForm = getCurrentHashForm();
    $sendMailDatabaseGestor = new SendMailDatabaseGestor($logger);
    $sendEmailRegister = ($sendMailDatabaseGestor->obtainSendEmailRegister($currentHashForm));

    if (!$sendEmailRegister){
        return true; // DO NOT SEND E-MAIL
    }
}
add_filter('wpcf7_skip_mail','cris_felix_wedding_custom_module_avoid_mail');

function cris_felix_wedding_custom_module_change_response_status($contact_form) {
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__.'/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());

    $currentHashForm = getCurrentHashForm();
    $sendMailDatabaseGestor = new SendMailDatabaseGestor($logger);
    $spotifyKeyGetter = new SpotifyKeyGetter($logger);
    $entityGenerator = new EntityGenerator($logger);
    $guestUploader = new GuestUploader($logger);
    $spotifyKeyRenewal = new SpotifyKeyRenewal($logger);

    $sendEmailRegister = ($sendMailDatabaseGestor->obtainSendEmailRegister($currentHashForm));

    if (!$sendEmailRegister) {
        $submission = WPCF7_Submission::get_instance();
        $submission->set_status("dni_guest_already_registered");
        $submission->set_response("El dni que ha introducido en el formulario ya se encuentra registrado");
    }

    $spotifyAuthorizationKey = "";
    $spotifyAuthorizationCodeColumnName= 'spotify_authorization';
    $spotifyDatetimeColumnName = 'spotify_authorization_datetime';

    try {
        $spotifyAuthorizationResultArray = $spotifyKeyGetter->getSpotifyAuthorizationKeyAndDatetime();
        $spotifyAuthorizationKey = $spotifyAuthorizationResultArray[$spotifyAuthorizationCodeColumnName];
        $spotifyAuthorizationDatetime = $spotifyAuthorizationResultArray[$spotifyDatetimeColumnName];

        if (checkCurrentDatetimeGreaterThanSpotifyDatetimeCode($spotifyAuthorizationDatetime)) {
            $spotifyAuthorizationKey = $spotifyKeyRenewal->renewSpotifyCode();
        }
    } catch (Exception $e) {
        $logger->error($e->getMessage());
    }

    if (!empty($spotifyAuthorizationKey)) {
        $obtainer = new Obtainer($logger);
        $entityGenerator = new EntityGenerator($logger);

        $arrayFromPost = $obtainer->obtainArrayFromPostPetition();
        $guestEntity = $entityGenerator->generateGuestEntity($arrayFromPost);

        //$spotifySongsIdArray = obtainSpotifySongs($guestEntity);

        //if (!empty($spotifySongsIdArray)) {
            $spotifyApi = new SpotifyWebAPI\SpotifyWebAPI();
            $spotifyApi->setAccessToken($spotifyAuthorizationKey);

        try {
            $prueba = $spotifyApi->addPlaylistTracks(WP_PLAYLIST_ID, [
                '6ytRc4KTeeOMyM4R2RqCVv'
            ]);
        } catch (Exception $e) {
            $logger->error($e->getMessage());
        }
    }
}
add_action( 'wpcf7_mail_sent', 'cris_felix_wedding_custom_module_change_response_status', 10, 2 );

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

function getCurrentHashForm() {
    $formInstance = WPCF7_Submission::get_instance();
    return $formInstance->get_posted_data_hash();
}

function obtainSpotifySongs($guestEntity) {
    $spotifySongsIdArray = array();
    $functions = array('getSpotifySong', 'getSpotifySong02', 'getSpotifySong03');
    foreach ($functions as $function) {
        $originalUrlSong = $guestEntity->$function();
        if (!empty($originalUrlSong)) {
            $treatedUrlSong = treatSpotifyLinks($originalUrlSong);
            array_push($spotifySongsIdArray, $treatedUrlSong);
        }
    }
    return array();
}

function treatSpotifyLinks($originalSpotifyLink) {
}

function checkCurrentDatetimeGreaterThanSpotifyDatetimeCode($spotifyDatetime) {
    $datetimeNow = time();
    $convertedSpotifyDatetime = strtotime($spotifyDatetime)+3600;

    return $datetimeNow > $convertedSpotifyDatetime;
}

