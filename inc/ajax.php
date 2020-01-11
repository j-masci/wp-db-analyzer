<?php
/**
 * ajax utility functions
 */

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * When using jQuery ajax on the client side, use this to transmit
 * data from server to client. Calling this function exits the script.
 *
 * @param $status
 * @param array $msgs
 * @param array $response
 */
function ajax_response( $status, array $msgs = [], array $response = [] ) {

    echo json_encode( array_merge( [
        'status' => $status,
        'msgs' => $msgs,
    ], $response ) );

    exit;
}
