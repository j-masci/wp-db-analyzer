<?php
/**
 * A menu page in wp-admin
 */

use WP_DB_Analyzer\SQL;

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

?>

<div class="wrap">
    <h2>WP Database Analyzer</h2>
    <h2>Posts Table (Top: post_status, Left: post_type)</h2>

    <h2>Post Type / Status</h2>

    <?= WP_DB_Analyzer\render_table( null, SQL::posts_report()->convert_to_record_set_with_headings(), [
        'skip_header' => true,
    ] ); ?>

    <h2>Meta Keys / Post Type</h2>

    <?= WP_DB_Analyzer\render_table( null, SQL::post_meta_report()->convert_to_record_set_with_headings(), [
        'skip_header' => true,
    ] ); ?>

    <h2>Post Date / Post Type</h2>

    <?= WP_DB_Analyzer\render_table( null, SQL::post_date_report()->convert_to_record_set_with_headings(), [
        'skip_header' => true,
    ] ); ?>

    <h2>Transients report...</h2>

    <?= WP_DB_Analyzer\render_table( null, SQL::transients_report()->convert_to_record_set_with_headings( "Hours Until Expiry" ), [
        'skip_header' => true,
    ] ); ?>

</div>
