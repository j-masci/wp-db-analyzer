<?php
/**
 * displays one report, via $_GET['report']
 */

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

$plugin = Plugin::get_instance();
$reports = Report_Factory::build();
$report_id = sanitize_text_field( @$_GET[ 'report' ] );
// $by_table = sanitize_text_field( @$_GET[ 'table' ] );

// put zero, one, or all reports into an array
if ( isset( $reports[ $report_id ] ) ) {
    $reports_to_render = [ $reports[ $report_id ] ];
} else if ( $report_id === Reports::REPORT_ID_ALL ) {
    $reports_to_render = $reports;
} else {
    $reports_to_render = [];
}

// the "request' to send to the report(s). we could use this to store settings for multiple reports
// in the URL, but we likely won't.
//$get_report_request = function ( $report_id, $total_number_of_reports ) {
//
//    if ( $total_number_of_reports <= 1 ) {
//        return $_GET;
//    }
//
//    return isset( $_GET[ 'report_settings' ][ $report_id ] ) && is_array( $_GET[ 'report_settings' ][ $report_id ] ) ? $_GET[ 'report_settings' ][ $report_id ] : [];
//}

?>

<div class="wrap wpdba-admin-page">

    <br>
    <h1>Database Analyzer Report(s) Page</h1>
    <p>Running <strong><?= count( $reports_to_render ); ?></strong> Report(s).</p>
    <p>Some reports may have settings, which will be ignored or use default settings if you run more than 1 report at a
        time.</p>
    <p><a href="<?= esc_url( $plugin->get_reports_url() ); ?>">Go back</a></p>
    <br>
    <hr>
    <br>

    <?php

    if ( $reports_to_render ) {

        // ie, hide $_GET when we're on the multiple reports page.
        // on a single report page, pass along $_GET as the "request".
        // $request is obviously raw data and is to be sanitized during report generation.
        $request = count( $reports_to_render ) === 1 ? $_GET : [];

        $count = 0;
        foreach ( $reports_to_render as $report ) {

            // render report and some extra details
            echo Reports::render_extended( $report, $request );

            // line between reports
            $count++;
            if ( $count < count( $reports_to_render ) ) {
                ?>
                <br>
                <hr>
                <br>
                <?php
            }
        }

    } else {
        echo '<p>The report provided was not found. Please go back and select a valid report.</p>';
    }

    ?>

</div>