<?php
/**
 * displays one report, via $_GET['report']
 */

namespace WP_DB_Analyzer;

if (!defined('ABSPATH')) exit;

$plugin = WP_DB_Analyzer_Plugin::get_instance();
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
            echo Report::render_extended($report);

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