<?php
/**
 * Renders a report.
 */

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/** @var array $report */
/** @var array $request */

// $date_format is what we will actually use. $preset_format and $custom_format both come from form elements.
list( $effective_date_format, $preset_format, $custom_format ) = call_user_func( function () use ( $report, $request ) {

    // I did not want to sanitize date format strings until after using them to convert
    // to a date string, but, w/e. Most not too weird date formats should not be affected
    // by this.

    // ie. <select>
    $_preset = sanitize_text_field( @$request[ 'preset_format' ] );

    // ie. <input>
    $_custom = sanitize_text_field( @$request[ 'custom_format' ] );

    $_default = $report[ 'default_date_format' ];

    if ( $_preset ) {
        return [ $_preset, $_preset, '' ];
    } else if ( $_custom ) {
        // this ensures the <select> shows the "no value" option
        return [ $_custom, '', $_custom ];
    } else {

        if ( in_array( $_preset, $report[ 'preset_date_formats' ] ) ) {
            // ensures the <select> has the default date selected.
            return [ $_preset, $_preset, '' ];
        } else {
            // in case the default date format is not one of the select options, populate the
            // input field instead.
            return [ $_default, '', $_default ];
        }
    }
} );

// echo '<pre>' . print_r( [ $effective_date_format, $preset_format, $custom_format ], true ) . '</pre>';

// does not escape.
$get_date_format_example = function ( $format ) {

    // todo: deprecated in wp 5.something? (current_time)
    $now = current_time( 'timestamp' );
    $_now = date( $format, $now );
    return "[$format]: $_now";
};

echo Reports::render_settings_form( $report, function () use ( $report, $request, $effective_date_format, $preset_format, $custom_format, $get_date_format_example ) {

    ?>
    <table class="form-table">
        <tr>
            <th>
                <h2>Preset Date Formats</h2>
                <p>In most cases, choose one of these.</p>
            </th>
            <td>
                <?php

                echo '<select name="preset_format">';

                echo '<option value="">Custom</option>';

                array_map( function ( $format ) use ( $preset_format, $get_date_format_example ) {

                    $selected = $preset_format == $format;
                    $_selected = $selected ? ' selected="selected"' : '';
                    echo '<option value="' . esc_attr( $format ) . '"' . $_selected . '>' . sanitize_text_field( $get_date_format_example( $format )  ) . '</option>';
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
                    Use at your own risk. Also, this value is ignored unless you select "Custom" above.</p>
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

echo '<h3>Selected date format example (using the current time): <strong>' . sanitize_text_field( $get_date_format_example( $effective_date_format ) ) . '</strong></h3>';
echo '<p>Note: dates shown below may not be in your current timezone.</p>';

echo Html_Table::render( null, SQL::post_date_report( $effective_date_format )->convert_to_record_set_with_headings(), [
    'skip_header' => true,
] );