
### Report example

Note: A "report" is an array containing data and anonymous functions. They could have been objects,
but I decided to use arrays instead, which allows for overriding "methods" and for building
reports dynamically. If you modify any existing reports or add any reports please ensure
that all heavy lifting is in the render callback or in the template file. 

```php

use Database_Analyzer\Plugin;

function example_report(){

    global $wpdb;
    
    $report = [];
    
    // require unique id
    $report['id'] = 'example-report-id';
    
    // an optional array of related database tables. this determines where the report shows
    // up on the reports landing page in the table showing all database tables.
    $report['tables'] = [ $wpdb->posts ];
    
    // shows up below the title when printing the report and likely in the title attribute
    // of the single report link on the all reports landing page.
    // Ideally this is just a short description. You can include more details in
    // the report rendering function.
    $report['get_desc'] = function( $report ){
        return "Example report description.";
    };
    
    // The callback to render the report. This should also retrieve all report data.
    // When the report is rendered, a report generation time will be displayed which
    // is essentially the time it takes to invoke this callback.
    // please do not reference $_GET directly in the report. Instead,
    // use $request which is passed in, and may or may not be $_GET.
    $report['render'] = function( $report, $request ){
        return "Example report description.";
    };
    
    // an optional PHP file which is included and replaces the render callback.
    // Do not include both a render callback and a template parameter.
    $report['template'] = Plugin::get_instance()->settings['path'] . '/tmpl/reports/example-report.php';
    
    return $report;
}
```

### Modifying Reports

There is a hook to modify the reports. I doubt very many people will need to use this,
but it's there anyways.

```php

use Database_Analyzer\Report_IDs;

// reports is just an array of arrays. ensure you always return the array if you use this.
add_filter( 'wp_db_analyzer/reports', function( $reports ){
    
    // delete...
    unset( $reports[Report_IDs::POST_DATES] );
    
    // update...
    if ( isset( $reports[Report_IDs::TERMS] ) ) {
        $reports[Report_IDs::TERMS]['get_desc'] = function( $report ) {
            return '...';            
        };       
    }   
    
    // add your own...
    $reports['your-report-id'] = your_report_builder();
    
    return $reports;
});
```