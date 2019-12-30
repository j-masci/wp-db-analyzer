<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * todo: maybe we'll sanitize here or rename the function or something
 *
 * @param $columns
 * @param array $rows
 * @param array $args
 * @return string|void
 */
function render_table_from_clean_data( $columns, array $rows, array $args = [] ){

    $args = array_merge( [
        'show_no_results' => false,
        'no_results_message' => "No Results",
    ], $args );

    if ( $columns === null && $rows ) {
        $columns = [];

        if ( is_array( $rows[0] ) ) {
            foreach ( $rows[0] as $key => $value ) {
                $_key = sanitize_text_field( $key );
                $columns[$_key] = $_key;
            }
        } else {
            return;
        }
    }

    if ( empty( $rows ) && $args['show_no_results'] == false ) {
        return "";
    }

    ob_start();

    // wrapper div can handle overflow-x
    echo '<div class="wpda-table">';

    if ( $rows ) {

        echo '<table>';

        foreach ( $rows as $index => $row ){

            if ( is_array( $row ) ) {

                echo '<tr>';

                foreach ( $columns as $column_key => $column_label ) {

                    $cell = isset( $row[$column_key] ) ? $rows[$column_key] : "";

                    echo '<td>';
                    echo $cell;
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
