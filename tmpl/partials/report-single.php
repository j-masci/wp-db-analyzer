<?php
/**
 * displays one report, via $_GET['report']
 */

namespace WP_DB_Analyzer;

if (!defined('ABSPATH')) exit;

$plugin = Plugin::get_instance();
$reports = Reports::get_all();
$report_id = @$_GET['report'];

$by_table = @$_GET['table'];

// put zero, one, or all reports into an array
if (isset($reports[$report_id])) {
    $reports_to_render = [$reports[$report_id]];
} else if ($report_id === Reports::REPORT_ID_ALL) {
    $reports_to_render = $reports;
} else {
    $reports_to_render = [];
}

$get_report_request = function( $report_id, $total_number_of_reports ) {

    if ( $total_number_of_reports <= 1 ) {
        return $_GET;
    }

    return isset( $_GET['report_settings'][$report_id] ) && is_array( $_GET['report_settings'][$report_id] ) ? $_GET['report_settings'][$report_id] : [];
}

?>

<div class="wrap">

    <br>
    <h1>Database Analyzer Report(s) Page</h1>
    <p>Running <strong><?= count( $reports_to_render ); ?></strong> Report(s).</p>
    <p>Some reports may have settings, which will be ignored or use default settings if you run more than 1 report at a time.</p>
    <p><a href="<?= $plugin->get_reports_url(); ?>">Go back</a></p>
    <br>
    <hr>
    <br>

    <?php

    if ($reports_to_render) {

        $count = 0;
        foreach ($reports_to_render as $report) {

            echo Report::render_extended($report, $get_report_request( @$report['id'], count( $reports_to_render ) ) );

            // this is ugly, sorry.
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