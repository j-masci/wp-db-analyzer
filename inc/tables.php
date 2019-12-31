<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @param $columns
 * @param array $rows
 * @param array $args
 * @return string|void
 */
function render_table( $columns, array $rows, array $args = [] ){

    $defaults = [
        'show_no_results' => false,
        'no_results_message' => "No Results",
        'add_class' => [],
        'sanitize_column_key' => function( $in ) {
            return esc_attr( $in );
        },
        'sanitize_column_label' => function( $in ) {
            return sanitize_text_field( $in );
        },
        'sanitize_cell' => function( $cell, $key, $row ) {
            return sanitize_text_field( $cell );
        }
    ];

    // Merge the default arguments. There are easier ways to do this, but this is explicit.
    // If the array element is set but equal to null, the default is used.
    foreach ( $defaults as $d1 => $d2 ) {
        if ( ! isset( $args[$d1] ) ) {
            $args[$d1] = $d2;
        }
    }

    // I shouldn't have to do this for you (/me), but I will anyways.
    $rows = array_values( $rows );

    // Grab the columns from the rows if the columns are not provided,
    // and sanitize the column keys and values regardless.
    $columns = call_user_func( function() use( $columns, $rows, $args ) {

        $ret = [];

        if ( $columns === null ) {

            // extract the columns from the first row.
            if ( isset( $rows[0] ) && is_array( $rows[0] ) ) {
                foreach ( array_keys( $rows[0] ) as $key ) {
                    $ret[$key] = $key;
                }
            } else {
                $ret = [];
            }

        } else if ( is_array( $columns ) ) {
            // use the user defined columns
            $ret = $columns;
        } else{
            $ret = [];
        }

        $_ret = [];

        foreach ( $ret as $r1 => $r2 ) {
            $_ret[ $args['sanitize_column_key']( $r1 )] = $args['sanitize_column_label']( $r2 );
        }

        // return the sanitized columns
        return $_ret;
    });

    if ( empty( $rows ) && $args['show_no_results'] == false ) {
        return "";
    }

    ob_start();

    // wrapper div can handle overflow-x
    $cls = [ 'wpda-table' ];
    $cls[] = $args['add_class'];

    echo '<div class="' . esc_attr( implode( " ", array_filter( $cls ) ) ) . '">';

    if ( $rows ) {

        echo '<table>';

        echo '<th>';

        // note: columns were already sanitized
        foreach ( $columns as $column_key => $column_label ) {
            echo '<td class="col-' . $column_key . '">' . $column_label . '</td>';
        }

        echo '</th>';

        foreach ( $rows as $index => $row ){

            if ( is_array( $row ) ) {

                echo '<tr>';

                foreach ( $columns as $column_key => $column_label ) {

                    $cell = isset( $row[$column_key] ) ? $rows[$column_key] : "";

                    // column key has already been sanitized
                    echo '<td class="col-' . $column_key . '">';
                    echo $args['sanitation_callback']( $cell, $column_key, $row );
                    echo '</td>';
                }

                echo '</tr>';
            }
        }

        echo '</table>';
    } else {
        echo '<p class="no-results">' . $args['no_results_msg'] . '</p>';
    }

    echo '</div>';

    return ob_get_clean();
}
