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

    foreach ( Reports::get_all() as $report ) {
        echo Reports::render( $report );
    }

    ?>

</div>
