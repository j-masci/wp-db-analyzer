<?php
/**
 * Renders a report.
 */

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/** @var array $report */
/** @var array $request */

// accepting user input here can result in some interesting results (ie. if it contains seconds)
// we'll have to also ensure to sanitize the resulting date strings that appear in the column headers.
$date_format = call_user_func( function() use( $report, $request ){
    $f = @$request['format'];
    $f = $f ? $f : $report['default_date_format'];
    return $f;
});

echo Report::render_settings_form( $report, function() use( $report, $request, $date_format ){
    ?>
    <table class="form-table">
        <tr>
            <th>
                <h2>Preset Date Formats</h2>
                <p>Choose a preset date format or enter a custom date format below.</p>
            </th>
            <td>
                <?php

                // todo: wp 5.something and higher says not to use this anymore.
                $now = current_time( 'timestamp' );

                array_map( function( $format ) use( $now, $date_format ){

                    $display = date( $format, $now ) . " ($format)";
                    $cls = $date_format == $format ? 'button button-primary' : 'button button-secondary';

                    ?>
                    <button class="<?= $cls; ?>" name="format" value="<?= esc_attr( $format ); ?>"><?= sanitize_text_field( $display ); ?></button>
                    <?php
                }, [
                    'Y-m-d',
                    'F d, Y',
                    'Y-m',
                    'F Y',
                ] );
                ?>
            </td>
        </tr>
        <tr>
            <th>
                <h2>Custom Date Format</h2>
                <p>Use any valid <a href="https://www.php.net/manual/en/datetime.createfromformat.php" target="_blank">PHP date format</a>. If you are unsure of what this means, you should stick with "Y-m-d". Some date formats (ie. those using minutes or seconds) can result in timeout or memory issues.</p>
            </th>
            <td>
                <p>
                    <input type="text" name="format" value="<?= esc_attr( $date_format ); ?>">
                </p>
                <p>
                    <button class="button button-primary">Submit</button>
                </p>
            </td>
        </tr>
    </table>
    <?php
} );

echo render_table(null, SQL::post_date_report( $date_format )->convert_to_record_set_with_headings(), [
    'skip_header' => true,
]);