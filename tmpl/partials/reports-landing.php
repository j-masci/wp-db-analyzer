<?php
/**
 * landing page where you navigate to single reports pages
 */

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$reports = Report_Factory::build();
$show_tables = $wpdb->get_results( "SHOW TABLES" );

?>

<div class="wrap wpdba-admin-page">

    <h1>Database Analyzer</h1>
    <p>Select a report below to run or click "Run All Reports".</p>

    <?php

    // build the HTML table data, which displays database tables
    $html_table_data = array_map( function ( $row ) use ( $reports ) {

        global $wpdb;

        // database table name
        $table = $row && is_object( $row ) ? array_values( get_object_vars( $row ) )[ 0 ] : null;

        // the html table data
        return [
            'name' => $table,
            'records' => (int) SQL::count_rows_in_table( $table ),
            'available_reports' => Reports::link_reports( Reports::filter_by_database_table( $reports, $table ) ),
        ];

    }, is_array( $show_tables ) ? $show_tables : [] );

    // add the "other reports" (last) row to the table data
    $html_table_data[] = [
        'name' => 'Other Reports',
        'records' => 'N/A',
        'available_reports' => call_user_func( function () use ( $reports ) {

            return Reports::link_reports( array_filter( $reports, function ( $report ) {

                return empty( @$report[ 'tables' ] );
            } ) );
        } )
    ];

    // render the html table without sanitation, it has already been done.
    echo Html_Table::render( null, $html_table_data, [
        'sanitize_cell_data' => false,
    ] );

    ?>

    <br>
    <h2>Run All Reports</h2>
    <p>Note: If your database is too large then your server might not be able to run all reports at once. However,
        most websites should be fine and there is no harm in trying.</p>
    <p>Note: Some reports can have settings which can only be modified when you run the report on its own.</p>
    <p><a href="<?= Reports::get_url( [ 'id' => Reports::REPORT_ID_ALL ] ); ?>" class="button button-primary">Run All
            Reports</a></p>

</div>