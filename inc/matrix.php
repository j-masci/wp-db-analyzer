<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * A class to build a matrix dynamically.
 *
 * ie.
 *
 * Origin  | Column 1  | Column 2  | Column 3
 * Row 1   | Point 1-1 | Point 1-2 | Point 1-3
 * Row 2   | Point 2-1 | Point 2-2 | Point 2-3
 *
 * todo: make fluent?
 *
 * todo: Add validation to the set_matrix method (possibly mutating input instead of throwing an error if some rows are missing some columns).
 *
 * To display your data in an HTML table with row and column headings, @see self::convert_to_record_set_with_headings().
 *
 * Class Matrix_Alternate
 * @package WP_DB_Analyzer
 */
Class Matrix{

    const DEFAULT_HEADING_KEY = "__heading";
    const DEFAULT_TOTAL_KEY = "__total";

    /**
     * An array of arrays.
     *
     * Likely an associative array of associative arrays (depends
     * on what you pass into $this->set()).
     *
     * All methods (except for $this->set_matrix()) will ensure
     * that every column always has the same array keys.
     *
     * $matrix is an array of "rows". Each "row" is an array containing
     * columns.
     *
     * See the example method at the bottom if you need clarity on
     * the structure of this.
     *
     * @var array
     */
    private $matrix = [];

    /**
     * Example of the structure of our data.
     */
//    private static $example_matrix = [
//        'row_key_1' => [
//            'column_key_1' => "Point 1-1",
//            'column_key_2' => 12,
//            'column_key_3' => 13,
//        ],
//        'row_key_2' => [
//            'column_key_1' => 21,
//            'column_key_2' => 22,
//            'column_key_3' => 23,
//        ],
//        'row_key_3' => [
//            'column_key_1' => 31,
//            'column_key_2' => 32,
//            'column_key_3' => 33,
//        ],
//        'row_key_4' => [
//            'column_key_1' => 41,
//            'column_key_2' => 42,
//            'column_key_3' => 43,
//        ]
//    ];

    /**
     * @param $row_key
     * @param $column_key
     * @param null $default
     * @return mixed|null
     */
    public function get( $row_key, $column_key, $default = null ) {
        return isset( $this->matrix[$row_key][$column_key] ) ? $this->matrix[$row_key][$column_key] : $default;
    }

    /**
     * @param $row_key
     * @param $column_key
     * @param $value
     */
    public function set( $row_key, $column_key, $value ) {

        // if we properly register rows and columns, then there is no need for isset checks below.
        $this->register_row( $row_key );
        $this->register_column( $column_key );

        // set the value according to a function or value passed in
        if ( is_object( $value ) && is_callable( $value ) ) {
            $this->matrix[$row_key][$column_key] = call_user_func( $value, $this->matrix[$row_key][$column_key] );
        } else {
            $this->matrix[$row_key][$column_key] = $value;
        }
    }

    /**
     *
     * todo: its unlikely but possible we'll have numeric array keys. If so, we may need to re-index the array after deletion.
     *
     * @param $row_key
     */
    public function delete_row( $row_key ) {
        unset( $this->matrix[$row_key] );
    }

    /**
     * @param $column_key
     */
    public function delete_column( $column_key ) {
        foreach ( $this->matrix as $row_key => $vector ) {
            // do not check isset because then we'll fail to remove null values.
            unset( $this->matrix[$row_key][$column_key] );
        }
    }

    /**
     * Adds a row to the matrix with null values.
     *
     * @param $row_key
     */
    private function register_row( $row_key ) {

        if ( isset( $this->matrix[$row_key] ) ) {
            return;
        }

        $empty_row = [];

        // nested loop to assemble column indexes from all columns
        foreach ( $this->matrix as $r => $vector ) {
            foreach ( $vector as $c => $p ) {
                if ( ! array_key_exists( $c, $empty_row ) ) {
                    $empty_row[$c] = null;
                }
            }
        }

        $this->matrix[$row_key] = $empty_row;
    }

    /**
     * Adds a column to the matrix with null values.
     *
     * @param $column_key
     */
    private function register_column( $column_key ) {
        foreach ( $this->matrix as $r => $vector ) {
            if ( ! array_key_exists( $column_key, $vector ) ) {
                $this->matrix[$r][$column_key] = null;
            }
        }
    }

    /**
     * @return array
     */
    public function get_column_keys(){
        return array_keys( $this->get_first_row() );
    }

    /**
     * @return array
     */
    public function get_row_keys(){
        return array_keys( $this->get_first_column() );
    }

    /**
     * @param $row_key
     * @return array|mixed
     */
    public function get_row( $row_key ) {
        return isset( $this->matrix[$row_key] ) ? $this->matrix[$row_key] : [];
    }

    /**
     * @param $column_key
     * @return array
     */
    public function get_column($column_key ) {

        $ret = [];

        foreach ( $this->matrix as $row_key => $vector ) {
            // array_key_exists MUST be used over isset()
            if ( array_key_exists( $column_key, $vector ) ) {
                $ret[$row_key] = $vector[$column_key];
            }
        }

        // ensure the return value is not something unexpected.
        if ( count( $this->matrix ) !== count( $ret ) ) {
            // this will not occur under normal circumstances.
            return [];
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function get_first_column(){

        $ret = [];

        // we can do this using a combination of other functions such as get_row_keys,
        // but then we run into infinite loop scenarios.
        foreach ( $this->matrix as $row_index => $vector ) {
            foreach ( $vector as $column_index => $point ) {
                // get the first column and then break;
                $ret[$row_index] = $point;
                break 1;
            }
        }

        return $ret;
    }

    /**
     * @return array|mixed
     */
    public function get_first_row(){

        foreach ( $this->matrix as $row_key => $vector ) {
            return $vector;
        }

        return [];
    }

    /**
     * @return array
     */
    public function get_matrix(){
        return $this->matrix;
    }

    /**
     * I will allow this public method in case there are things which my methods do not cover.
     *
     * P.s. you could use this to re-order the matrix.
     *
     * @param array $matrix
     */
    public function set_matrix( array $matrix ){
        $this->matrix = $matrix;
    }

    /**
     * Gives you the entire row which you can use to generate a total.
     *
     * Once the totals are generated they are more or less no different
     * from the other data points, except for their key. Be aware of this
     * if you call this more than once for some reason.
     *
     * @param callable $callback
     * @param string $column_key
     */
    public function set_row_totals( callable $callback, $column_key = self::DEFAULT_TOTAL_KEY ){

        // run the callback for each row
        foreach ( $this->get_row_keys() as $key ) {
            $value = call_user_func_array( $callback, [ $this->get_row( $key ), $key ] );
            $this->set( $key, $column_key, $value );
        }

        // put the new column at the end
        $this->sort_columns( function( $keys ) use( $column_key ){
            unset( $keys[$column_key] );
            return array_merge( $keys, [ $column_key ] );
        });
    }

    /**
     * @param callable $callback
     * @param string $row_key
     */
    public function set_column_totals( callable $callback, $row_key = self::DEFAULT_TOTAL_KEY ){

        // run the callback for each column
        foreach ( $this->get_column_keys() as $key ) {
            $value = $callback( $this->get_column( $key ), $key );
            $this->set( $row_key, $key, $value );
        }

        // put the new row at the bottom
        $this->sort_rows( function( $keys ) use( $row_key ){
            unset( $keys[$row_key] );
            return array_merge( $keys, [ $row_key ] );
        });
    }

    /**
     * @return array
     */
    public function get_dimensions(){
        return [ count( $this->get_row_keys()), count($this->get_column_keys()) ];
    }

    /**
     * The callback accepts a numerically indexed array of keys
     * and should return an array of the same format (but possibly
     * in a different order).
     *
     * Passing in empty array -> no-op.
     *
     * @param callable $callback
     */
    public function sort_rows( callable $callback ){
        // passing in a non-array will result in an error.
        // For now, not failing silently.
        $this->apply_row_sort( call_user_func( $callback, $this->get_row_keys() ) );
    }

    /**
     * @param callable $callback
     */
    public function sort_columns( callable $callback ){
        $this->apply_column_sort( call_user_func( $callback, $this->get_column_keys() ) );
    }

    /**
     * Applies a sort order to the rows according to what you pass in.
     *
     * ie. $keys = [ 'key_1', 'key_2', 'key_3' ].
     *
     * Keys not passed in will be appended with their current order maintained.
     *
     * Keys passed in that are not in the data are ignored.
     *
     * @param array $keys
     */
    public function apply_row_sort( array $keys ){
        $this->matrix = self::apply_sort_order_to_data( $keys, $this->matrix );
    }

    /**
     * @see apply_row_sort
     *
     * @param array $keys
     */
    public function apply_column_sort( array $keys ) {
        foreach ( $this->matrix as $r => $vector ) {
            $this->matrix[$r] = self::apply_sort_order_to_data( $keys, $vector );
        }
    }

    /**
     * @param array $ordered_keys
     * @param array $data
     * @return array
     */
    private static function apply_sort_order_to_data( array $ordered_keys, array $data ) {

        $k = array_keys( $data );

        // strips invalid keys
        $_ordered_keys = array_intersect( $ordered_keys, $k );

        // append missing keys from $_ordered_keys onto the end.
        $_ordered_keys = array_unique( array_merge( array_values( $_ordered_keys ), $k ) );

        assert( count( $_ordered_keys ) === count( $k ) );

        $ret = [];

        foreach ( $_ordered_keys as $key ) {
            $ret[$key] = $data[$key];
        }

        return $ret;
    }

    /**
     * Adds row and column headings and returns an array of arrays.
     *
     * This might be the format you need to pass into a function that
     * renders an HTML table (for me it is anyways).
     *
     * Is this better off static since it returns an array and does not return nor mutate $this?
     *
     * @param string $origin_label
     * @param array $row_labels
     * @param array $column_labels
     * @param string $row_heading_key
     * @param string $column_heading_key
     * @return array
     */
    public function convert_to_record_set_with_headings( $origin_label = "", $row_labels = [], $column_labels = [], $row_heading_key = self::DEFAULT_HEADING_KEY, $column_heading_key = self::DEFAULT_HEADING_KEY ) {

        // we could do this with a bunch of foreach loops and some interesting looking array merges,
        // or, we can use a complicated mix of the methods we already built. To be honest, it's not
        // super easy to follow using the latter method, but, that's what I did.

        $self = clone $this;

        // insert the header row (at the end)
        foreach ( $self->get_column_keys() as $column_key ) {
            $self->set( $row_heading_key, $column_key, isset( $column_labels[$column_key] ) ? $column_labels[$column_key] : $column_key );
        }

        // put the header row at the start of all rows.
        $self->sort_rows( function() use( $row_heading_key ){
            return [ $row_heading_key ];
        });

        // insert the column heading into each column (at the end)
        foreach ( $self->get_row_keys() as $row_key ) {
            $self->set( $row_key, $column_heading_key, isset( $row_labels[$row_key] ) ? $row_labels[$row_key] : $row_key );
        }

        // put the column headings at the start of each column.
        $self->sort_columns( function() use( $column_heading_key ){
            return [ $column_heading_key ];
        });

        // now fix the (0,0) coordinate.
        $self->set( $row_heading_key, $column_heading_key, $origin_label );

        // return an array not the instance for now.
        return $self->matrix;
    }

    /**
     * Sometimes we use this as one of the arguments for $this->set().
     *
     * @param int $plus_equals
     * @return \Closure
     */
    public static function get_incrementer( $plus_equals = 1 ){
        return function( $prev ) use( $plus_equals ) {
            $prev = $prev ? $prev : 0;
            $prev += $plus_equals;
            return $prev;
        };
    }

    /**
     * Returns a function that sums an array and accepts the arguments
     * given in the callback for set_row_totals/set_column_totals.
     *
     * @return \Closure
     */
    public static function get_array_summer(){
        return function( $arr, $key ) {
            return array_sum( $arr );
        };
    }
}