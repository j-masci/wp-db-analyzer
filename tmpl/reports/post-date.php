<?php
/**
 * Renders a report.
 */

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/** @var array $report */
/** @var array $request */

// accepting user input here can result in some interesting results (ie. if it contains seconds)
// we'll have to also ensure to sanitize the resulting date strings that appear in the column headers.
list( $date_format, $preset_format, $custom_format ) = call_user_func( function () use ( $report, $request ) {

    // pretty ugly logic we have to do but what choice do we have
    $c = @$request[ 'custom_format' ];
    $p = @$request[ 'preset_format' ];
    $d = $report[ 'default_date_format' ];

    if ( $p ) {
        return [ $p, $p, '' ];
    } else if ( $c ) {
        return [ $c, '', $c ];
    } else {

        if ( in_array( $d, $report[ 'preset_date_formats' ] ) ) {
            return [ $d, $d, '' ];
        } else {
            return [ $d, '', $d ];
        }
    }
} );

$get_date_format_example = function ( $format, $sanitize = true ) {

    // todo: I think this is deprecated in wp 5.something (current_time)
    $now = current_time( 'timestamp' );
    $_now = date( $format, $now );
    $v = "[$format]: $_now";

    if ( $sanitize ) {
        return sanitize_text_field( $v );
    } else {
        return $v;
    }
};

echo Reports::render_settings_form( $report, function () use ( $report, $request, $date_format, $preset_format, $custom_format, $get_date_format_example ) {

    ?>
    <table class="form-table">
        <tr>
            <th>
                <h2>Preset Date Formats</h2>
                <p>In most cases, choose one of these.</p>
            </th>
            <td>
                <?php

                echo '<select name="format">';

                echo '<option value="">Custom</option>';

                array_map( function ( $format ) use ( $preset_format, $get_date_format_example ) {

                    $selected = $preset_format == $format;
                    $_selected = $selected ? ' selected="selected"' : '';
                    echo '<option value="' . esc_attr( $format ) . '"' . $_selected . '>' . $get_date_format_example( $format ) . '</option>';
                }, $report[ 'preset_date_formats' ] );

                echo '</select>';

                ?>
            </td>
        </tr>
        <tr>
            <th>
                <h2>Custom Date Format</h2>
                <p>Enter a valid <a href="https://www.php.net/manual/en/datetime.createfromformat.php" target="_blank">PHP
                        date format</a>. Some formats may result in your server not being able to complete the request.
                    Use at your own risk.</p>
            </th>
            <td>
                <p>
                    <input type="text" name="custom_format" value="<?= esc_attr( $custom_format ); ?>">
                </p>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <p>
                    <button class="button button-primary">Submit</button>
                </p>
            </td>
        </tr>
    </table>
    <?php
} );

echo '<h3>The selected date format is: <strong>' . $get_date_format_example( $date_format ) . '</strong></h3>';

echo Html_Table::render( null, SQL::post_date_report( $date_format )->convert_to_record_set_with_headings(), [
    'skip_header' => true,
] );