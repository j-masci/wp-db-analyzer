<?php
/**
 * landing page where you navigate to single reports pages
 */

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$reports = Reports::get_all();
$show_tables = $wpdb->get_results( "SHOW TABLES" );

?>

<div class="wrap">

    <h1>Database Analyzer</h1>

    <br>
    <hr>
    <br>

    <h2>Run Reports One At a Time</h2>
    <p>Click on a report in the table below to run it.</p>
    <p>The table also shows the number of rows in each table in your database.</p>

    <?php

    // build the HTML table data, which displays database tables
    $html_table_data = array_map( function( $row ) use( $reports ){

        global $wpdb;

        // database table name
        $table = $row && is_object( $row ) ? array_values( get_object_vars( $row ) )[0] : null;

        // the html table data
        return [
            'name' => $table,
            'records' => (int) $wpdb->get_var( "SELECT count(*) AS count FROM " . esc_sql( $table ) . ";" ),
            'available_reports' => Reports::link_reports( Reports::filter_by_database_table( $reports, $table ) ),
        ];

    }, is_array( $show_tables ) ? $show_tables : [] );

    // add the "other reports" (last) row to the table data
    $html_table_data[] = [
        'name' => 'Other Reports',
        'records' => 'N/A',
        'available_reports' => call_user_func( function() use( $reports ){
            return Reports::link_reports( array_filter( $reports, function( $report ) {
                return empty( @$report['tables'] );
            }) );
        })
    ];

    echo render_table( null, $html_table_data, [
        'raw_html_keys' => [ 'available_reports' ],
    ] );

    ?>

    <br>
    <h2>Run All Reports</h2>
    <p>If your database is small enough and/or your server is powerful enough, you can run all reports in one go. On most websites, this is probably not an issue. If some reports have settings, they probably do not apply when running all reports at once.</p>
    <p><a href="<?= Report::get_link( [ 'id' => Reports::REPORT_ID_ALL ] ); ?>" class="button button-primary">Run All Reports</a></p>

</div>