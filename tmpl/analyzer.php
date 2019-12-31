<?php
/**
 * The menu page to access the plugin.
 */

use WP_DB_Analyzer\SQL;

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

?>

<div class="wrap">
    <h2>WP Database Analyzer</h2>
    <p>Blah...</p>


    <?php

    call_user_func( function(){
        $matrix = SQL::posts_report();
        echo \WP_DB_Analyzer\render_table( null, $matrix->export_record_set() );
    });

    call_user_func( function(){
        $matrix = SQL::post_meta_report();
        echo \WP_DB_Analyzer\render_table( null, $matrix->export_record_set() );
    });

    ?>

</div>
