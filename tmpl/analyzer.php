<?php
/**
 * A menu page in wp-admin
 */

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

?>

<div class="wrap">
    <h2>WP Database Analyzer</h2>

    <?php

    foreach ( Report_IDs::build_reports() as $report ) {
        Report::prepare_report( $report );
        echo Report::render_report( $report );
    }

    ?>

</div>
