<?php
/**
 * Most likely, all ajax requests for the plugin will channel through this file.
 *
 * Then if needed, individual actions will have their own file, which should be
 * included from here.
 */

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

$plugin = WP_DB_Analyzer_Plugin::get_instance();

if ( ! wp_verify_nonce( @$_REQUEST['_nonce'], $plugin->settings['nonce_secret'] ) ){
    ajax_response( "error", [ "Nonce Error." ] );
}

// shouldn't get to here
ajax_response( "error", [ "Hit end of file" ] );
