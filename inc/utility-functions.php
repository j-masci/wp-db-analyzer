<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets wp_options rows for transient VALUES, but not the rows for transient timeouts.
 *
 * The return value adds a property for, and, is indexed by the transient name. The transient
 * name is used in set_transient(), and is a substring of option_name.
 *
 * The option value is not included in the return (for performance reasons).
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
            $transient_name = substr( $trans->option_name, $prefix_length, strlen( $trans->option_name ) - $prefix_length );
            $trans->transient_name = $transient_name;
            $ret[$transient_name] = $trans;
        }
    }

    return $ret;
}

/**
 * Gets wp_options rows for transient TIMEOUTS, but not the rows that store the values.
 *
 * The return value adds a property for, and, is indexed by the transient name. The transient
 * name is used in set_transient(), and is a substring of option_name.
 *
 * The expiry is found in the form of a timestamp in the option_value column.
 *
 * @return array
 */
function get_transient_timeouts(){

    global $wpdb;

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

/**
 * Simply wraps try/catch around new DateTime, and returns false
 * on errors.
 *
 * Additionally returns false if the string you pass in is empty.
 *
 * @param $date_time_string
 * @param null $timezone
 * @return bool|\DateTime
 */
function get_date_time_or_false( $date_time_string, $timezone = null ){

    // todo: is it better to have this check or should we leave it out? Does this prevent getting a datetime with timestamp 0?
    if ( ! $date_time_string ) {
        return false;
    }

    try{
        return new \DateTime( $date_time_string, $timezone );
    } catch( \Exception $e ) {
        return false;
    }
}
