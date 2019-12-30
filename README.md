Work In Progress.

A plugin to analyze your database. Doing so can help optimize your website especially if it runs slow due to a large posts or post meta table.

For starters, we can give the counts of every combination of post type and post status in the posts table. 

In addition, we can break down the post meta table, showing all the meta keys in use and counting usages of each meta key via post type. 

The post meta table is often the source of slowdown in sites with 10k+ images or products since most pages require querying through the data and its not uncommon for the post meta table to contain several hundred thousand or even more than a million rows. In these cases, hopefully the plugin will show some interesting statistics. On a basic WordPress install, there isn't too much to look at.

As a separate plugin or possibly integrated into this one, we can try to determine whether or not each image in the media library is used anywhere on the site. This simply cannot be done with 100% accuracy, but, on some sights it would be very nice to have to estimate of how many unused images you truly have, and a way of identifying images that are probably unused.

