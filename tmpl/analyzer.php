<?php
/**
 * A menu page in wp-admin
 */

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$reports = Reports::get_all();
$tables = $wpdb->get_results( "SHOW TABLES" );

?>

<div class="wrap">
    <h2>WP Database Analyzer</h2>

    <?php

    $html_table_data = array_map( function( $row ) use( $reports ){

        global $wpdb;

        $table = $row && is_object( $row ) ? array_values( get_object_vars( $row ) )[0] : null;

        $_reports = array_filter( $reports, function( $r ) use( $table ){
            $tables = isset( $r['tables'] ) && is_array( $r['tables'] ) ? $r['tables'] : [];
            return in_array( $table, $tables );
        } );

        $_reports_html = implode( ", ", array_map( function( $r ){
            return @$r['title'];
        }, $_reports ) );

        return [
            'name' => $table,
            'records' => (int) $wpdb->get_var( "SELECT count(*) AS count FROM " . esc_sql( $table ) . ";" ),
            'available_reports' => $_reports_html,
        ];

    }, is_array( $tables ) ? $tables : [] );

    echo render_table( null, $html_table_data );

    foreach ( $reports as $report ) {

        ?>

        <div class="wpdba-report" data-name="<?= esc_attr( $report['id'] ); ?>">
            <?= Reports::render( $report ); ?>
        </div>

        <?php
    }

    ?>

</div>
