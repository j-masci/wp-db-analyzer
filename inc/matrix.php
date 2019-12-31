<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * A matrix like object for holding the x and y axis labels,
 * and the data associated with 1 x and 1 y value.
 *
 * The class will help you build the data and then export
 * it to a record set which you can use to render an HTML
 * table with.
 *
 * In the context of rendering an HTML table, the x-axis values
 * are the labels in the top row. The y-axis values are the labels
 * in the left column. The origin is the top left of the table (unlike
 * a traditional graph in mathematics where the origin is bottom left).
 *
 * Ie.
 *
 * Origin, X Label 1, X Label 2,
 * Y Label 1, Point 1-1, Point 1-2
 * Y Label 2, Point 2-1, Point 2-2
 * Y Label 3, Point 3-1, Point 3-3
 *
 * Point 1-1 === $this->get_point( "X Label 1", "Y Label 1" )
 *
 * Class Matrix
 */
Class Matrix{

    /**
     * ie. [ "x1", "x2", "x3" ]
     *
     * @var array
     */
    public $x_axis = [];

    /**
     * ie. [ "y1", "y2", "y3" ]
     *
     * @var array
     */
    public $y_axis = [];

    /**
     * A matrix who's dimension is the count of the x and y axis respectively.
     *
     * ie. [ "y1" => [ "x1" => "v11", "x2" => "v21", ... ], "x2" => [ ... ] ... ]
     *
     * It's worth noting that every array in the matrix is indexed by
     * an x-axis or y-axis value, and not a number. Doing it this way allows
     * us to order the x and y axis even after registering all of our data.
     *
     * @var array
     */
    public $matrix = [];

    /**
     * if this becomes slow, we can store values in a flipped array and
     * array flip upon retrieval. isset is faster than in_array.
     *
     * @param $value
     */
    public function register_x_axis( $value ){
        if ( ! in_array( $value, $this->x_axis ) ) {
            $this->x_axis[] = $value;
        }
    }

    /**
     * @param $value
     */
    public function register_y_axis( $value ){
        if ( ! in_array( $value, $this->y_axis ) ) {
            $this->y_axis[] = $value;
        }
    }

    /**
     * If you pass in a closure for $value, we'll provide you the previous
     * value and let your function determine the next value.
     *
     * We do not force the x and y axis values to be registered before you
     * put them here. So, you can register all the data you want, but if you
     * do not register the corresponding x and y values, then the data will
     * be largely ignored when you export to a record set.
     *
     * @param $x
     * @param $y
     * @param $value
     * @param bool $register_axises
     */
    public function set_point( $x, $y, $value, $register_axises = false ) {

        if ( $register_axises ) {
            $this->register_x_axis( $x );
            $this->register_y_axis( $y );
        }

        // checking instanceof \Closure would be similar...
        if ( is_object( $value ) && is_callable( $value ) ) {
            $this->matrix[$x][$y] = call_user_func( $value, $this->get_point( $x, $y, null ) );
        } else {
            $this->matrix[$x][$y] = $value;
        }
    }

    /**
     * @param $x
     * @param $y
     * @param null $default
     * @return mixed|null
     */
    public function get_point( $x, $y, $default = null ) {
        return isset( $this->matrix[$x][$y] ) ? $this->matrix[$x][$y] : $default;
    }

    /**
     * Sometimes you can use this as the 3rd param of set_point, which may
     * save you a line or two of code.
     *
     * @param int $increment_by
     * @return \Closure
     */
    public function get_incrementor( $increment_by = 1 ) {
        return function( $prev ) use( $increment_by ) {
            $prev = $prev ? $prev : 0;
            $prev += $increment_by;
            return $prev;
        };
    }

    /**
     * Export to an array of arrays which includes the x and y axis values.
     *
     * @param string $origin
     * @return array
     */
    public function export_record_set( $origin = "" ) {

        $ret = [];

        foreach( $this->y_axis as $y ) {

            $row = [];

            foreach ( $this->x_axis as $x ) {
                $row[] = $this->get_point( $x, $y );
            }

            $ret[] = array_merge( [ $y ], $row );
        }

        // prepend the x-axis
        return array_merge( [ array_merge( [ $origin ], $this->x_axis ) ], $ret );
    }

    /**
     * @param $key
     * @return array|mixed
     */
    public function get_row( $y ) {
        return isset( $this->matrix[$y] ) ? $this->matrix[$y] : [];
    }

    /**
     * @param $key
     * @return array
     */
    public function get_column( $x ){

        $ret = [];

        foreach ( $this->matrix as $_y => $arr ) {
            foreach ( $arr as $_x => $point ) {
                if ( $x == $_x ) {
                    $ret[$_y] = $point;
                }
            }
        }

        return $ret;
    }

    /**
     * Invert x/y axis because we can...
     *
     * // todo: test
     */
    public function invert(){

        $self = new static();
        $self->x_axis = $this->y_axis;
        $self->y_axis = $this->x_axis;

        foreach ( $this->matrix as $x_axis => $y_values ) {
            foreach ( $y_values as $y_axis => $point ) {
                $self->set_point( $y_axis, $x_axis, $point, false );
            }
        }

        $this->x_axis = $self->x_axis;
        $this->y_axis = $self->y_axis;
        $this->matrix = $self->matrix;
        return $this;
    }
}