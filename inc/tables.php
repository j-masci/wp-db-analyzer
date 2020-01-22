<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

$fuck = function( $shit, ...$args ) {

    echo "...." . __FUNCTION__;
    $r = new \ReflectionFunction( __FUNCTION__ );

    echo '<pre>' . print_r($r, true) . '</pre>';

    echo '<pre>' . print_r(func_get_args(), true) . '</pre>';

    echo '<pre>' . print_r($args, true) . '</pre>';

};

function fuck( $shit, ...$args ) {

    echo "...." . __FUNCTION__;
    $r = new \ReflectionFunction( __FUNCTION__ );

    echo '<pre>' . print_r($r, true) . '</pre>';

    echo '<pre>' . print_r(func_get_args(), true) . '</pre>';

    echo '<pre>' . print_r($args, true) . '</pre>';
}

echo nl2br( "----------------------- \n" );

fuck( 'shit', 'shit' );
fuck( [ 1, 2, 3], [ "thing" => "asdlkjasd" ] );

echo nl2br( "----------------------- \n" );

$fuck( 'shit', 'shit' );
$fuck( [ 2, 2, 2 ], [ "thasdasding" => "asdlkjasd" ] );

echo 123;
exit;

Class Component{

    public $filters = [];
    public $callable;

    public function invoke( $args ) {

        foreach ( $this->filters as $f ) {
            $args = $f( $args );
        }

        call_user_func( $this->callable, $args );
    }
}

function template_1(){

    $t = new template();

    $t->set( 'main', function( $arg_1, $arg_2 ){
        echo 'html...';
    });

    $t->set( 'other', function( $arg_1, $arg_2 ){
        echo 'html...';
    });
}

function template_2(){

    $t = template_1();

    $t->get( 'main' )->add_filter( function( $arg_1, $arg_2 ) {
        $arg_2['fuck'] = 'shit';
        return [ $arg_1, $arg_2 ];
    });

    return $t;
}

// some functions return a matrix.
// we then export the matrix into table data and inject it into a table object along with a template.
// should we have the intermediate step of generating the table object before rendering ?? perhaps.
// otherwise, we just render the table which injects the template at renders it...

Class TemplateComponent{

    private $argument_filters = [];
    private $callable;

    public function __construct( $callable = null, array $argument_filters = [] ){

        if ( $callable ) {
            $this->set_callable( $callable);
        }

        $this->argument_filters = $argument_filters;
    }

    /**
     * @param $filter
     * @param null $priority
     */
    public function add_argument_filter( $filter, $priority = null ) {
        // todo: figure this out better.
        if ( $priority === null ) {
            $this->argument_filters[] = $filter;
        } else {
            $this->argument_filters[$priority] = $filter;
        }
    }

    public function set_callable( callable $callable ) {
        $this->callable = $callable;
    }

    public function get_callable(){
        return $this->callable;
    }

    /**
     * Pass in a function that accepts the existing callable and returns
     * a new callable (you may want to invoke the previous one).
     *
     * @param $callable_builder
     */
    public function extend_callable( callable $callable_builder ) {
        $prev = $this->get_callable();
        $this->callable = call_user_func( $callable_builder, $prev );
    }

    public static function test(){

        $self = new self( function( $arg_1, $arg_2 ){

        })

    }

    public function invoke( ...$args ){

        foreach ($this->argument_filters as $argument_filter ) {

        }

        call_user_func( $this->callable, ...$args );
    }

}

Class WPDBA_Table_Template_Factory{

    public static function default(){

        $template = new \JMasci\InvokableComponentTemplate();

        $template->set( 'table', function( $table ){
            $cls = [ 'wpdba-table', 'wpdba-default', @$table->args['add_class'] ];
            ?>
            <div class="<?= esc_attr( implode( " ", array_filter( $cls ) ) ); ?>">
                <?php $table->include( 'table_tag' ); ?>
            </div>
            <?php
        });

        $template->set( 'table_tag', function( $table ){
            echo '<table>';
            $table->include( 'thead' );
            $table->include( 'tbody' );
            echo '</table>';
        });

        $template->set( 'thead', function( $table ) {
            echo '<thead>';
            echo '<tr>';
            foreach ( $table->cols as $col_index => $col_label ) {
                $table->include( 'th', $col_index );
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
                $table->include( 'tbody_row', $row_index );
            }
            echo '</tbody>';
        });

        $template->set( 'tbody_row', function( $table, $row_index ) {
            echo '<tr>';
            foreach ( $table->cols as $col_index => $col_label ) {
                // pass indexes only
                $table->include( 'td', $row_index, $col_index );
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

    /**
     * Omits <thead> section. Likely the first body row of the table
     * data contains table headers.
     *
     * @return mixed
     */
    public static function headless(){

        $template = self::default();

        $template->set( 'table', $template->get( 'table', [
            'thing' => true,
        ]));

        $template->set( 'table_tag', function( $table ) {
            echo '<table>';
            $table->include( 'tbody' );
            echo '</table>';
        });

        return $template;
    }

    public static function test(){

        $temp_1 = new JMasci\InvokableComponentTemplate();

        // we need 3 (?) things:
        // - the template object?
        // - the arguments passed in when calling (the template object can be one of these)
        // some contextual sort of arguments that are dynamically bound to the function upon invoking it.
        // the contextual arguments are not bound when invoking the function but are handled in other ways.

        // so lets simplify and say 2 things instead.
        // 1. the arguments used when invoking the template.
        // 2. the dynamically bound contextual arguments.
        // the only question is, how is it best to do number 2, because there are plenty of ways
        // using global variables. We can just keep track of which component is being invoked,
        // and then make a function to get arguments for that component. however, what is the format
        // of these contextual arguments? an ordered array of variables? (ie. list( $vars ) = blah())
        // an associative array, extract( thing() )?
        // maybe the component itself just uses a getter function...
        // if we use an associative array, then the parent component should be able to also
        // modify this, so that the component itself does not have to check for potentially
        // the same arguments in different places.
        // note extract( get_contextual_args() ) could run into var name conflicts.
        // what about some components that don't have access to the template object?
        // if a component cant access its template object is it safe to access contextual args globally?
        // we have to then know which template is currently running and which component of that template,
        // and some templates might end up invoking other templates.

        // maybe $template->modify(). stores a callback which modifies the function arguments
        // which runs before the component is invoked ?

        // idk.....
        // the component would have to be setup to expect that it might be modified.
        // maybe that's ok....
        $temp_1->modify( 'main', function( $invoke, $func_args ){
            return $invoke( $func_args );
        });

        $temp_1->set( 'main', function( $arg_1, $arg_2 ){

            return $temp_1->invoke( 'main', $arg_1, $arg_2 );
        });

        $temp_1->set( 'main', function( $template, $func_args, $contextual_args ){
            extract( $func_args );

            $cls = 'something';
            $cls .= " " . $add_class;
            $cls = [ 'wpdba-table', 'wpdba-default', @$table->args['add_class'] ];
            ?>
            <div class="something"></div>
            <?php
        });

        $temp_1->set( 'comp_2', function( $template, $args ) {

        });

        $template = self::default();

        $template->set( 'main', $template->get( 'main' ), function(){
            return [ 'add_class' => 'headless' ];
        } );

        $template->set( 'main', $template->get( 'main' ), function(){
            return [ 'add_class' => 'headless' ];
        } );

        $template->set( 'thing', function( $template, $func_args ){
            extract( $func_args );


            $template->include( 'other', compact( [ 'table' ] ) );
        });

    }

}

function get_table_template( $type ) {

    $template = new JMasci\ComponentTemplates\InvokableComponentTemplate();


    return $template;
}

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
        },
        // array of keys where we don't sanitize the cell data (for rows, not columns)
        'raw_html_keys' => [],
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

                    if ( is_object( $cell ) && is_callable( $cell ) ) {
                        // todo: allow customizing both inner and full contents of each table cell? eg. need to add data attributes to <td>
                    }

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

                    if ( in_array( $column_key, $args['raw_html_keys'] ) ) {
                        echo $cell;
                    } else {
                        echo $args['sanitize_cell']( $cell, $column_key, $row );
                    }

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
