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
use CrisFelixWeddingCustomModule\Actions\Implementations\SpotifyKeyHandler;
use CrisFelixWeddingCustomModule\Actions\Implementations\SpotifyRequestHandler;
use CrisFelixWeddingCustomModule\Actions\Implementations\SpotifyKeyRenewal;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

global $cris_felix_wedding_db_version;
$cris_felix_wedding_db_version = '1.4';

function crisFelixWedding_install()
{
    global $wpdb;
    global $cris_felix_wedding_db_version;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . 'check_in_confirmation_email_sent';

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
		mail VARCHAR(300) NOT NULL,
		name VARCHAR(200) NOT NULL,
		surname VARCHAR(700) NOT NULL,
		id_custom_post mediumint(9) NOT NULL,
		sent BOOLEAN DEFAULT 0 NOT NULL,
		datetime_sent DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) $charset_collate;";

    dbDelta($sql);

    $table_name = $wpdb->prefix . 'spotify_authorization';

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
		spotify_authorization TEXT NOT NULL,
		spotify_authorization_refresh TEXT NOT NULL,
		spotify_authorization_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) $charset_collate;";

    dbDelta($sql);

    add_option('cris_felix_wedding_db_version', $cris_felix_wedding_db_version);
}

register_activation_hook(__FILE__, 'crisFelixWedding_install');

function cris_felix_wedding_custom_module_update_db_check()
{
    global $cris_felix_wedding_db_version;
    if (get_site_option('cris_felix_wedding_db_version') != $cris_felix_wedding_db_version) {
        crisFelixWedding_install();
    }
}

add_action('plugins_loaded', 'cris_felix_wedding_custom_module_update_db_check');

function cris_felix_wedding_custom_module_insert_guest($cf7)
{
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/my_app.log', Logger::DEBUG));
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

function cris_felix_wedding_custom_module_avoid_mail($f)
{
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());

    $currentHashForm = getCurrentHashForm();
    $sendMailDatabaseGestor = new SendMailDatabaseGestor($logger);
    $sendEmailRegister = ($sendMailDatabaseGestor->obtainSendEmailRegister($currentHashForm));

    if (!$sendEmailRegister) {
        return true; // DO NOT SEND E-MAIL
    }
}

add_filter('wpcf7_skip_mail', 'cris_felix_wedding_custom_module_avoid_mail');

function cris_felix_wedding_custom_module_change_response_status($contact_form)
{
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());

    $currentHashForm = getCurrentHashForm();
    $sendMailDatabaseGestor = new SendMailDatabaseGestor($logger);

    $sendEmailRegister = ($sendMailDatabaseGestor->obtainSendEmailRegister($currentHashForm));

    if (!$sendEmailRegister) {
        $submission = WPCF7_Submission::get_instance();
        $submission->set_status("dni_guest_already_registered");
        $submission->set_response("El dni que ha introducido en el formulario ya se encuentra registrado");
    }
}

add_action('wpcf7_mail_sent', 'cris_felix_wedding_custom_module_change_response_status', 10, 2);

function cris_felix_wedding_custom_module_add_song_spotify_list($contact_form)
{
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());

    $spotifyKeyHandler = new SpotifyKeyHandler($logger);
    $obtainer = new Obtainer($logger);
    $entityGenerator = new EntityGenerator($logger);

    $spotifyIdSongsForm = obtainSpotifySongs($obtainer, $entityGenerator);

    if (empty($spotifyIdSongsForm)) {
        return;
    }

    try {
        $spotifyApiObject = obtainSpotifyApiObject($spotifyKeyHandler);
    } catch (Exception $e) {
        $logger->error($e->getMessage());
        return;
    }

    try {
        $playlistTracks = $spotifyApiObject->getPlaylistTracks(WP_PLAYLIST_ID);
    } catch (Exception $e) {
        $logger->error(__FILE__ . ": " . $e->getMessage());
        $submission = WPCF7_Submission::get_instance();
        $submission->set_status("error_retrieving_playListtrack");
        $submission->set_response("error_retrieving_playListtrack: no se han podido añadir correctamente sus canciones");
        return;
    }

    foreach ($spotifyIdSongsForm as $spotifyIdSongForm) {
        if (!alreadyAddedSpotifySong($spotifyIdSongForm, $playlistTracks)) {
            try {
                $spotifyApiObject->addPlaylistTracks(WP_PLAYLIST_ID, [
                    $spotifyIdSongForm
                ]);
            } catch (Exception $e) {
                $logger->error(__FILE__ . ": " . $e->getMessage());
                $submission = WPCF7_Submission::get_instance();
                $submission->set_status("error_uploading_songs");
                $submission->set_response("error_uploading_songs: no se han podido añadir correctamente sus canciones");
                break;
            }
        }
    }
}

add_action('wpcf7_mail_sent', 'cris_felix_wedding_custom_module_add_song_spotify_list', 10, 2);


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
            $days = get_post_meta($post_id, 'days', true);
            echo multiValueFieldHandling($days);
            break;
        case 'upper_age':
            echo (get_post_meta($post_id, 'upper_age', true)) ? "Sí" : "No";
            break;
        case 'menu_type':
            $menu_type = get_post_meta($post_id, 'menu_type', true);
            echo multiValueFieldHandling($menu_type);
            break;
        case 'extra_service':
            $extraService = get_post_meta($post_id, 'extra_service', true);
            echo multiValueFieldHandling($extraService);
            break;
    }
}

add_action('manage_guest_posts_custom_column', 'display_custom_columns', 10, 2);

function multiValueFieldHandling($multiValueResult)
{
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

function getCurrentHashForm()
{
    $formInstance = WPCF7_Submission::get_instance();
    return $formInstance->get_posted_data_hash();
}

function obtainSpotifySongs($obtainer, $entityGenerator)
{
    $spotifyIdSongsForm = array();
    $functions = array('getSpotifySong', 'getSpotifySong02', 'getSpotifySong03');

    $arrayFromPost = $obtainer->obtainArrayFromPostPetition();
    $guestEntity = $entityGenerator->generateGuestEntity($arrayFromPost);

    foreach ($functions as $function) {
        $originalUrlSong = $guestEntity->$function();
        if (!empty($originalUrlSong) && str_starts_with($originalUrlSong, "https://open.spotify.com/track/")) {
            $treatedUrlSong = treatSpotifyLinks($originalUrlSong);

            if (!in_array($treatedUrlSong, $spotifyIdSongsForm)) {
                array_push($spotifyIdSongsForm, $treatedUrlSong);
            }
        }
    }

    return $spotifyIdSongsForm;
}

function spotifyLinkContainsIdSession($originalSpotifyLink) {
    return str_contains($originalSpotifyLink, '?');
}

function treatSpotifyLinks($originalSpotifyLink)
{
    if (spotifyLinkContainsIdSession($originalSpotifyLink)) {
        return get_string_between($originalSpotifyLink, "track/", "?");
    } else {
        return substr($originalSpotifyLink, strrpos($originalSpotifyLink, "track/") + strlen("track/"));
    }
}

function checkCurrentDatetimeGreaterThanSpotifyDatetimeCode($spotifyDatetime)
{
    $datetimeNow = time();
    $convertedSpotifyDatetime = strtotime($spotifyDatetime) + WP_SECONDS_SPOTIFY_RENEWAL;

    return $datetimeNow > $convertedSpotifyDatetime;
}

function obtainSpotifyApiObject($spotifyKeyGetter)
{
    $spotifyAuthorizationCodeColumnName = 'spotify_authorization';
    $spotifyRefreshCodeColumnName = 'spotify_authorization_refresh';

    try {
        $spotifyAuthorizationResultArray = $spotifyKeyGetter->getSpotifyAuthorizationKeyAndDatetime();
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }

    $spotifyAuthorizationKey = $spotifyAuthorizationResultArray[$spotifyAuthorizationCodeColumnName];
    $spotifyAuthorizationRefreshKey = $spotifyAuthorizationResultArray[$spotifyRefreshCodeColumnName];

    $session = new SpotifyWebAPI\Session(
        WP_SPOTIFY_ID,
        WP_SPOTIFY_PASS
    );

    if ($spotifyAuthorizationKey) {
        $session->setAccessToken($spotifyAuthorizationKey);
        $session->setRefreshToken($spotifyAuthorizationRefreshKey);
    } else {
        // Or request a new access token
        $session->refreshAccessToken($spotifyAuthorizationRefreshKey);
    }

    $options = [
        'auto_refresh' => true,
    ];

    $api = new SpotifyWebAPI\SpotifyWebAPI($options, $session);
    $api->setSession($session);

    $newAccessToken = $session->getAccessToken();
    $newRefreshToken = $session->getRefreshToken();

    try {
        $spotifyKeyGetter->storeSpotifyCredentials($newAccessToken, $newRefreshToken);
    } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
    }

    return $api;
}

function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function alreadyAddedSpotifySong($spotifyIdSongForm, $playlistTracks)
{
    $result = false;

    if (!empty($playlistTracks)) {
        foreach ($playlistTracks->items as $playlistSong) {
            if ($playlistSong->track->id === "$spotifyIdSongForm") {
                $result = true;
                break;
            }
        }
    }

    return $result;
}

add_action( 'admin_menu', 'send_emails_page' );
function send_emails_page() {
    $hookName =  add_menu_page(
        'Send email to guests',
        'Email guests',
        'manage_options',
        'email_guests',
        'send_emails_page_html',
        plugin_dir_url(__FILE__) . 'images/mail.png',
        30
    );

    add_action( 'load-' . $hookName, 'email_guests_page_submit' );
}

function send_emails_page_html() {
    global $wpdb;
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());
    $guestTableName = $wpdb->prefix . 'posts';
    $checkInMailTableName = $wpdb->prefix . 'check_in_confirmation_email_sent';
    $postType = 'guest';

    ?>
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php

    $query = "SELECT A.ID 
    FROM " . $guestTableName . " A 
    LEFT JOIN " . $checkInMailTableName . " B 
    ON A.ID = B.id_custom_post 
    WHERE A.post_type = '" . $postType . "' 
    AND B.id_custom_post IS NULL";

    try {
        $guests = $wpdb->get_results($query, ARRAY_N);
    } catch (Exception $e) {
        $logger->error($e->getMessage());
        return;
    }

    if (empty($guests)) {
        echo '<div class="updated"><p>' . __('No pending guest emails to be sent') . '</p></div>';
        return;
    }

    if (isset($_GET['saved'])) {
        if ($_GET['saved']) {
            echo '<div class="updated"><p>' . __('Success! Emails were sent') . '</p></div>';
        } else {
            echo '<div class="error"><p>' . __('There were some errores. Check the logs') . '</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <div class="select-deselect_buttons-block">
            <button id="guest-email-select-all-button" type="button">Select all</button>
            <button id="guest-email-deselect-all-button" type="button">Remove selection</button>
        </div>
        <div class="select-email-guests-form">
            <form action="<?php menu_page_url('email_guests') ?>" method="post">
                <fieldset>
                    <div class="select-email-guests-legend">
                        <legend>Choose guests to send them an email:</legend>
                    </div>
                    <?php
                        foreach ($guests as $guest) {
                            $guestId = $guest[0];
                            $guestEmail = get_post_meta( $guestId, 'email', true);
                            $guestName = get_post_meta( $guestId, 'guest_name', true);
                            $guestSurname = get_post_meta( $guestId, 'surname', true);
                            if (empty($guestEmail)) {
                                continue;
                            }
                    ?>
                        <div class="select-email-guests-option_block">
                            <input class="guest-email-input" type="checkbox" name="guests_to_send_email[]" value="<?php echo $guestId ?>" />
                            <?php echo $guestName . " " . $guestSurname . " - " . $guestEmail ?>
                            <br>
                        </div>
                    <?php
                        }
                    ?>
                    <input type="submit" value="Send email" />
                </fieldset>
            </form>
        </div>
    </div>
    <?php
}

function email_guests_page_submit() {
    global $wpdb;
    $logger = new Logger('cris-felix-plugin-logger');
    $logger->pushHandler(new StreamHandler(__DIR__ . '/my_app.log', Logger::DEBUG));
    $logger->pushHandler(new FirePHPHandler());
    $table_name = $wpdb->prefix . 'check_in_confirmation_email_sent';
    $insertValuesArray = array();
    $somethingHasFailed = FALSE;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!array_key_exists('guests_to_send_email', $_POST)) {
        return;
    }

    if (empty($guestIdArray = $_POST['guests_to_send_email'])) {
        return;
    }

    $batch_of = 10;
    $batch = array_chunk($guestIdArray, $batch_of);

    foreach($batch as $b) {
        $args = array(
            'post_type' => 'guest',
            'post__in' => $b
        );

        $guestsArray = get_posts($args);

        foreach ($guestsArray as $guest) {
            $guestId = $guest->ID;
            $guestEmail = str_replace(array('\'', '"'), '',  sanitize_email(get_post_meta( $guestId, 'email', true)));
            $guestName = str_replace(array('\'', '"'), '',  sanitize_text_field(get_post_meta( $guestId, 'guest_name', true)));
            $guestSurname = str_replace(array('\'', '"'), '',  sanitize_text_field(get_post_meta( $guestId, 'surname', true)));

            $to = $guestEmail;
            $subject = 'check-in Cris&Felix boda';

            $tpl = file_get_contents(__DIR__ . '/template/email_template.html');
            $tpl = str_replace('{{guestName}}', totitle($guestName), $tpl);

            $headers = array('Content-Type: text/html; charset=UTF-8');

            try {
                $emailSentResult = wp_mail( $to, $subject, $tpl, $headers );
            } catch (Exception $e) {
                $somethingHasFailed = TRUE;
                $logger->error($e->getMessage());
                continue;
            }

            if ($emailSentResult) {
                $insertValuesArray[] = array($guestEmail, $guestName, $guestSurname, $guestId, TRUE);
            }
        }

        if (empty($insertValuesArray)) {
            continue;
        }

        $valueText = "('";

        foreach ($insertValuesArray as $key => $element) {
            $valueText .= implode("', '", $element);

            if ($key === array_key_last($insertValuesArray)) {
                $valueText .= "')";
            } else {
                $valueText .= "'),('";
            }
        }

        $query = "INSERT INTO " . $table_name . " (mail, name, surname, id_custom_post, sent) VALUES " . $valueText;

        try {
            //$wpdb->query($query);
        } catch (Exception $e) {
            $somethingHasFailed = TRUE;
            $logger->error($e->getMessage());
            continue;
        }
    }

    $redirectUrl = menu_page_url('email_guests');

    if ($somethingHasFailed) {
        $redirectUrl .= '&saved=0';
    } else {
        $redirectUrl .= '&saved=1';
    }

    wp_redirect($redirectUrl);
    exit;
}

if ( !function_exists( 'custom_module_enqueue_js_scripts' ) ) {
    $prueba = plugin_dir_url(__FILE__);
    function custom_module_enqueue_js_scripts() {
        wp_enqueue_script( 'custom-module-js', plugin_dir_url(__FILE__) . '/assets/js/custom.js', array( 'jquery' ), false, true );
    }

    add_action( 'admin_enqueue_scripts', 'custom_module_enqueue_js_scripts' );
}

function totitle($string){
    return ucfirst(strtolower($string));
}

