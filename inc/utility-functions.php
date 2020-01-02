<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets rows from wp_options which are used for transient VALUES, excluding transient timeouts.
 *
 * The return value both adds a property for and is indexed by the transient name. The transient
 * name is what is used in set_transient(), and is a substring of the option_name column.
 *
 * Note that this does not select nor return the option_value as of now, since its not needed
 * and could cause performance issues on misbehaving databases with millions of transients (i've seen it).
 *
 * @return array
 */
function get_transients(){

    global $wpdb;
    $transients = $wpdb->get_results("SELECT option_id, option_name, autoload FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_name NOT LIKE '_transient_timeout_%' ");

    $prefix_length = strlen( "_transient_" );

    $ret = [];

    if ( $transients && is_array( $transients ) ) {
        foreach ( $transients as $trans ) {

            var_dump( $trans->option_name );
            $transient_name = substr( $trans->option_name, $prefix_length, strlen( $trans->option_name ) - $prefix_length );
            var_dump( $transient_name );
            $trans->transient_name = $transient_name;
            $ret[$transient_name] = $trans;
        }
    }

    return $ret;
}

/**
 * Gets rows from wp_options which are used for transient TIMEOUTS, excluding transient values.
 *
 * The return value both adds a property for and is indexed by the transient name. The transient
 * name is what is used in set_transient(), and is a substring of the option_name column.
 *
 * @return array
 */
function get_transient_timeouts(){

    global $wpdb;

    // Select * here because we want the option_value unlike in other places.
    $transients = $wpdb->get_results("SELECT * FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' ORDER BY option_id ASC ");

    $prefix_length = strlen( "_transient_timeout_" );

    $ret = [];

    if ( $transients && is_array( $transients ) ) {
        foreach ( $transients as $trans ) {
            $transient_name = substr( $trans->option_name, $prefix_length, strlen( $trans->option_name ) - $prefix_length );
            $trans->transient_name = $transient_name;
            $ret[$transient_name] = $trans;
        }
    }

    return $ret;
}

/**
 * Re-format a given date time string while failing silently on any errors.
 *
 * @param $date_time_string
 * @param string $format
 * @param null $timezone
 * @return string
 */
function format_date_time_string( $date_time_string, $format = "Ymd", $timezone = null ) {
    $dt = get_date_time_or_false( $date_time_string, $timezone );
    return $dt ? $dt->format( $format ) : "";
}

function get_date_time_or_false( $date_time_string, $timezone = null ){
    try{
        return new \DateTime( $date_time_string, $timezone );
    } catch( \Exception $e ) {
        return false;
    }
}
