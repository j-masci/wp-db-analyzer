<?php

namespace Database_Analyzer;

use JMasci\HtmlTable\Table;
use JMasci\ComponentTemplate\Template;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Static methods for html table rendering (and building templates
 * to render those tables).
 *
 * Class Html_Table
 * @package WP_DB_Analyzer
 */
Class Html_Table {

    /**
     * Renders an HTMl table from data, arguments, and a rendering template.
     *
     * @param $columns
     * @param array $rows
     * @param array $args
     * @param null $template
     * @return mixed
     */
    public static function render( $columns, array $rows, array $args = [], $template = null ) {

        $template = $template === null ? self::get_default_template() : $template;
        $table = new Table( $columns, $rows, $args, $template );
        return $table->render();
    }

    /**
     * Empty template instance without any components.
     *
     * @return Template
     */
    public static function get_base_template() {

        return new Template( 'table' );
    }


    /**
     * Gets the default "html table rendering template" instance
     *
     * @return Template
     */
    public static function get_default_template() {

        $template = self::get_base_template();

        $template->set( 'table', function ( $table ) {

            $cls = [ 'wpdba-table', 'wpdba-default', @$table->args[ 'add_class' ] ];
            ?>
            <div class="<?= esc_attr( implode( " ", array_filter( $cls ) ) ); ?>">
                <?php $this->invoke( 'table_tag', $table ); ?>
            </div>
            <?php
        } );

        $template->set( 'table_tag', function ( $table ) {

            echo '<table>';

            if ( ! @$table->args[ 'skip_header' ] ) {
                $this->invoke( 'thead', $table );
            }

            $this->invoke( 'tbody', $table );
            echo '</table>';
        } );

        $template->set( 'thead', function ( $table ) {

            echo '<thead>';
            echo '<tr>';
            foreach ( $table->cols as $col_index => $col_label ) {
                $this->invoke( 'th', $table, $col_index );
            }
            echo '</tr>';
            echo '</thead>';
        } );

        $template->set( 'th', function ( $table, $index ) {

            $value = $table->cols[ $index ];
            $class = 'col-' . $index;
            echo '<td class="' . esc_attr( $class ) . '">' . htmlspecialchars( $value ) . '</td>';
        } );

        $template->set( 'tbody', function ( $table ) {

            echo '<tbody>';
            foreach ( $table->rows as $row_index => $row ) {
                $this->invoke( 'tbody_row', $table, $row_index );
            }
            echo '</tbody>';
        } );

        $template->set( 'tbody_row', function ( $table, $row_index ) {

            echo '<tr>';
            foreach ( $table->cols as $col_index => $col_label ) {
                // pass indexes only
                $this->invoke( 'td', $table, $row_index, $col_index );
            }
            echo '</tr>';
        } );

        // accepts indexes, not the value it needs to render.
        $template->set( 'td', function ( $table, $row_index, $col_index ) {

            // note: $col_index is not guaranteed to bet set, unlike $row_index.
            $value = is_scalar( @$table->rows[ $row_index ][ $col_index ] ) ? @$table->rows[ $row_index ][ $col_index ] : "";
            $class = 'col-' . $col_index;

            $sanitize = isset( $table->args[ 'sanitize_cell_data' ] ) ? $table->args[ 'sanitize_cell_data' ] : true;
            $_value = $sanitize ? htmlspecialchars( $value ) : $value;

            echo '<td class="' . esc_attr( $class ) . '">' . $_value . '</td>';
        } );

        return $template;
    }
}