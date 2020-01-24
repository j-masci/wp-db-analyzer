<?php

namespace WP_DB_Analyzer;
use JMasci\HtmlTable\Table;
use JMasci\ComponentTemplate\Template;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @param $columns - can be null
 * @param array $rows
 * @param array $args
 * @param null $template
 * @return mixed
 */
function render_table( $columns, array $rows, array $args = [], $template = null ){
    $template = $template === null ? get_table_template() : null;
    $table = new Table( $columns, $rows, $args, $template );
    return $table->render();
}

/**
 * Gets the empty template from which you can add components to.
 *
 * @return Template
 */
function get_table_template_empty(){
    $template = new Template( 'table' );
    return $template;
}

/**
 * @return Template
 */
function get_table_template(){

    $template = get_table_template_empty();

    $template->set( 'table', function( $table ){
        $cls = [ 'wpdba-table', 'wpdba-default', @$table->args['add_class'] ];
        ?>
        <div class="<?= esc_attr( implode( " ", array_filter( $cls ) ) ); ?>">
            <?php $this->invoke( 'table_tag' ); ?>
        </div>
        <?php
    });

    $template->set( 'table_tag', function( $table ){
        echo '<table>';

        if ( ! @$table->args['skip_header'] ) {
            $this->invoke( 'thead' );
        }

        $this->invoke( 'tbody' );
        echo '</table>';
    });

    $template->set( 'thead', function( $table ) {
        echo '<thead>';
        echo '<tr>';
        foreach ( $table->cols as $col_index => $col_label ) {
            $this->invoke( 'th', $col_index );
        }
        echo '</tr>';
        echo '</thead>';
    });

    $template->set( 'th', function( $table, $index ) {
        $value = $table->cols[$index];
        $class = 'col-' . $index;
        echo '<td class="' . esc_attr( $class ) . '">' . htmlspecialchars( $value ) . '</td>';
    });

    $template->set( 'tbody', function( $table ) {
        echo '<tbody>';
        foreach ( $table->rows as $row_index => $row ) {
            $this->invoke( 'tbody_row', $row_index );
        }
        echo '</tbody>';
    });

    $template->set( 'tbody_row', function( $table, $row_index ) {
        echo '<tr>';
        foreach ( $table->cols as $col_index => $col_label ) {
            // pass indexes only
            $this->invoke( 'td', $row_index, $col_index );
        }
        echo '</tr>';
    });

    // accepts indexes, not the value it needs to render.
    $template->set( 'td', function( $table, $row_index, $col_index ) {
        // note: $col_index is not guaranteed to bet set, unlike $row_index.
        $value = is_scalar( @$table[$row_index][$col_index] ) ? @$table[$row_index][$col_index] : "";
        $class = 'col-' . $col_index;

        echo '<td class="' . esc_attr( $class ) . '">' . htmlspecialchars( $value ) . '</td>';
    });

    return $template;
}