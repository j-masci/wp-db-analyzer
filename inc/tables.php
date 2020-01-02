<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @param $columns - Array of column keys mapped to column label. If null, is generated from $rows[0].
 * @param $rows - Array of arrays or stdClass objects using the keys as in $columns.
 * @param array $args
 * @return string
 */
function render_table( $columns, array $rows, array $args = [] ){

    $defaults = [
        'show_no_results' => false,
        'no_results_message' => "No Results",
        'add_class' => [],
        'skip_header' => false,
        'get_table_cell_tag' => null,
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
    // If the array element of $args is set but equal to null, the default is used.
    foreach ( $defaults as $d1 => $d2 ) {
        if ( ! isset( $args[$d1] ) ) {
            $args[$d1] = $d2;
        }
    }

    // validate $rows. Do this before $columns.
    $rows = call_user_func( function() use( $rows, $args ) {
        return array_values( array_map( function( $row ){
            if ( is_object( $row ) ) {
                return get_object_vars( $row );
            } else if ( is_array( $row ) ) {
                return $row;
            } else {
                return [];
            }
        }, $rows ) );
    } );

    // Validate $columns. Auto generate from $rows if null. Ensure that we
    // validate $rows before running this.
    $columns = call_user_func( function() use( $columns, $rows, $args ) {

        if ( $args['skip_header'] ) {
            // actually, don't do this. Allow the columns to be auto generated,
            // because that's what we loop through to print html.
            // return [];
        }

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

        if ( ! $args['skip_header'] ) {

            echo '<thead>';
            echo '<tr>';

            // note: columns were already sanitized
            foreach ( $columns as $column_key => $column_label ) {
                echo '<th class="col-' . $column_key . '">' . $column_label . '</th>';
            }

            echo '</tr>';
            echo '</thead>';
        }

        echo '<tbody>';

        foreach ( $rows as $index => $row ){

            if ( is_array( $row ) ) {

                echo '<tr>';

                foreach ( $columns as $column_key => $column_label ) {

                    $cell = isset( $row[$column_key] ) ? $row[$column_key] : "";

                    // this optional callback might be used to make the first
                    // row of the table contain header cells.
                    if ( $args['get_table_cell_tag'] ) {
                        // pass in the entire row in addition to the column key,
                        // this makes it possible to check if the $column_key is the first.
                        $tag = $args['get_table_cell_tag']( $column_key, $row );
                    } else {
                        $tag = 'td';
                    }

                    $tag = in_array( $tag, [ 'td', 'th' ] ) ? $tag : 'td';

                    // $column_key was already sanitized.
                    $col_class = $args['skip_header'] ? '' : 'col-' . $column_key;

                    echo '<' . $tag . ' class="' . $col_class . '">';
                    echo $args['sanitize_cell']( $cell, $column_key, $row );
                    echo '</' . $tag . '>';
                }

                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p class="no-results">' . $args['no_results_msg'] . '</p>';
    }

    echo '</div>';

    return ob_get_clean();
}
