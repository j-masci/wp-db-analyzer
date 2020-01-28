<?php
/**
 * mainly functions that don't have a better place to live
 */

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The URL for a specified report.
 *
 * @param $report_id
 * @return string
 */
function get_report_url( $report_id ) {

    return add_query_arg( [
        'page' => Plugin::get_instance()->settings[ 'menu_slug' ],
        'report' => sanitize_text_field( $report_id ),
    ], admin_url( 'admin.php' ) );
}

/**
 * URL for the landing page showing all
 * reports (but not running them)
 *
 * @return string
 */
function get_reports_landing_page_url() {

    return add_query_arg( [
        'page' => Plugin::get_instance()->settings[ 'menu_slug' ]
    ], admin_url( 'admin.php' ) );
}

/**
 * The URL which which runs all reports.
 */
function get_all_reports_url() {

    return get_report_url( Reports::REPORT_ID_ALL );
}

/**
 * Renders an HTML table showing the number of records in database tables.
 *
 * @param $database_tables
 * @return mixed
 */
function render_table_counts( $database_tables ) {

    return Html_Table::render( null, SQL::count_table_records_report( $database_tables )->convert_to_record_set_with_headings(), [
        'skip_header' => true
    ] );
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
function get_date_time_or_false( $date_time_string, $timezone = null ) {

    if ( ! $date_time_string ) {
        return false;
    }

    try {
        return new \DateTime( $date_time_string, $timezone );
    } catch ( \Exception $e ) {
        return false;
    }
}
