<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Prepares and renders reports.
 *
 * An example report is something that renders an HTML table with some useful data in it.
 *
 * This class mainly exists so that we can register all reports in a well-defined
 * format, and then pick and choose which ones to render on a given page. We don't
 * register the report as HTML, but as one or more callbacks to do the heavy lifting
 * and generate the HTML.
 *
 * It's worth noting that a valid report is any object (of any class) which contains
 * a $report_id and a callable $_render property. I'm not forcing myself or anyone
 * else to use this class to build a report object.
 *
 * Class Report
 * @package WP_DB_Analyzer
 */
Final Class Report{

    /**
     * Unique report ID. Ie. so that we can register all reports,
     * then pick a report via its ID and render it.
     *
     * @var string|int
     */
    public $report_id;

    /**
     * When true, $_prepare is called before any reports are rendered.  When
     * false, $_prepare is called immediately before $_render. There's
     * a decent chance we won't need this.
     *
     * @var bool
     */
    public $prepare_early = true;

    /**
     * An array of args passed into the $_prepare callback,
     * and should still be available in the $_render callback.
     *
     * @var array
     */
    public $prepare_args = [];

    /**
     * The return value of the $_prepare callback, which will
     * be passed in to the $_render callback.
     *
     * @var array
     */
    public $render_args = [];

    /**
     * User specified callback to prepare the object for rendering.
     *
     * The return value of this will be passed into $_render.
     *
     * @var callable|null
     */
    public $_prepare;

    /**
     * User specified callback to render the HTML.
     *
     * @var callable|null
     */
    public $_render;

    /**
     * Report constructor.
     * @param $report_id
     */
    public function __construct( $report_id ) {
        $this->report_id = $report_id;
    }

    /**
     * Returns an instance of self.
     *
     * @param $report_id
     * @return Report
     */
    public static function build( $report_id ) {
        return new self( $report_id );
    }

    /**
     * @param callable $_prepare
     * @return $this
     */
    public function set_prepare_callback( callable $_prepare ){
        if ( is_callable( $_prepare ) ) {
            $this->_prepare = $_prepare;
        }
        return $this;
    }

    /**
     * @param $_render
     * @return $this
     */
    public function set_render_callback( $_render ){
        if ( is_callable( $_render ) ) {
            $this->_render = $_render;
        }
        return $this;
    }

    /**
     * "Prepare" the report. (you may want to use this run queries,
     * but, you can also run queries in $_render if you prefer).
     *
     * $args are injected on the page where we are going to render
     * the report and only after we know of the intention to display it.
     *
     * @param $args
     */
    public static function prepare_report( $obj, $args = [] ) {

        $obj->prepare_args = $args;

        // note that if the prepare callback is not set, render_args will default to an array,
        // but if it is set, it will be whatever you return (array or not an array).
        if ( isset( $obj->_prepare ) && is_callable( $obj->_prepare ) ) {
            $obj->render_args = call_user_func_array( $obj->_prepare, [ $obj->prepare_args, $obj ] );
        }
    }

    /**
     * Use a static function so that we don't force $obj to be an instance of self.
     *
     * @param $obj
     * @return mixed|null
     */
    public static function render_report( $obj ){
        if ( isset( $obj->_render ) && is_callable( $obj->_render  ) ) {
            ob_start();
            call_user_func_array( $obj->_render, [ $obj->render_args, $obj ] );
            return ob_get_clean();
        }
        return null;
    }
}
