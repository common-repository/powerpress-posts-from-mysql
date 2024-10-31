=== PowerPress Posts From MySQL addon ===
Contributors: machouinard, blubrry
Donate link: 
Tags: powerpress, podcasting, mysql, podcast, sql, db, database
Requires at least: 3.0
Tested up to: 4.5.2
Stable tag: 0.9.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Have a bunch of podcasts stored on your server and aren't excited about creating each post manually? This can help.

== Description ==

By populating a MySQL database table with all the information about the podcasts you can automatically create the posts needed for podcasting with the [Blubrry PowerPress plugin](http://wordpress.org/extend/plugins/powerpress/ "WordPress Podcasting"). This will allow you to post as draft or published and switch between the two as well as delete posts.  Probably not useful to actual Podcasters, but it works for what I needed.

Information required from the database:

* Host
* Database Name
* Database Table Name
* Database Username
* Database Password

Also field names from the database which will be used for the following:

* Primary Key Field
* Title
* Category
* Post Body
* Featured Image(URL to an image)
* Media URL
* Media size
* Media type
* Date posted

Your categories need to be setup prior to using this.

= Example database table for importing from = 

You may use the following database table as an example template for your database.

`CREATE TABLE episodes (
  episode_id int(11) NOT NULL,
  episode_title varchar(255) NOT NULL,
  episode_category varchar(255) NOT NULL,
  episode_body text NOT NULL,
  episode_image_url varchar(4000) NOT NULL,
  episode_url varchar(4000) NOT NULL,
  episode_length int(11) NOT NULL,
  episode_content_type varchar(20) NOT NULL,
  episode_date date NOT NULL,
  PRIMARY KEY (episode_id)
) DEFAULT CHARSET=utf8;`

Note: You must have at least one record in the database table before you can enter the database column fields in this plugin.

== Installation ==

This section describes how to install the plugin and get it working.
(make sure you have created the category in your blog before running or all the podcasts will be uncategorized)

1. Upload the folder containing pfd.php and process.php to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Configure your database settings in Post From MySQL under the Tools menu in the Dashboard and click Save Changes
4. After you've saved your settings and a connection is made, the total number of records will be displayed.

<strong>NOTE! For this plugin to work correctly, it requires the MySQL table to use a primary key. * see <em>[How should I set up the database table](http://wordpress.org/extend/plugins/powerpress-posts-from-mysql/faq/ "Frequently Asked Questions")?</em>in the FAQ for more information.</strong>

== Screenshots ==
none at this time.

== Frequently Asked Questions ==

= How should I set up the database table? =

* Create fields that correspond to those on the MySQL Fields page of the plugin ( Post Title, Post Category, Post Body, Post/Featured Image URL, Podcast URL, Podcast Size, Podcast Media Type, Post Date ).
* Make sure to include a primary key.

Note: Guid uses your primary key. appended to the site's root URL.

= Does the MySQL table have to be on the same DB Host as my WordPress install? =

No, but it helps.  You will need to make sure you have remote access to the MySQL database if it's on a different host.  I have a site on DreamHost using a database on HostGator and it works great.


== Changelog ==

= 0.9.10 =
* Released 06/14/2016
* Updated plugin for WordPress version 4.5+
* Created extended class for connecting to database to bypass connect to database error page if connection failed.
* Fixed bug with `find_podcast_by_id()` function not using the primary_key column.
* Fixed bug with `does_field_exist()` function not using the table member variable (was hard coded to use ppfm database table).
* Updated the `_publish_post()` function to optionally include data if it's avaialble or skip otherwise.

v 0.9.9

* Changed the way media_handle_sideload was being used

v 0.9.8

* (note: Most of this work was done months ago.  I got busy and forgot about it)
* Rewritten from the ground up
* Learned a lot about WordPress
* Learned a lot about Git, too.

v 0.9.4

* Added check to ensure BluBrry PowerPress is installed and activated
* More CSS and HTML changes in an attempt to pretty this thing up a bit
* Changed code to allow for localization
* Used Google Translate to create .mo files for:
* French
* Spanish - Spain/Ecuador
* Italian
* Danish - Denmark
* German
* Turkish

v 0.9.2

* Included a Primary Key field in the settings page
* Added ability to set the status of posts as either Published or Draft
* Made some aesthetic changes to the settings page using some CSS and jQuery
* Removed some unused code and comments from process.php

v 0.9.1

* Added database connectivity checking.
* Added check to prevent posting same podcast twice based on the podcast/post title.
* Added ability to post from a range of records in the table based on a specific database field.
* Added display of total records in table.

== Upgrade Notice ==

= 0.9.9a =
Renamed files per standards. Cleaned up code, removed comments.

= 0.9.9 =
Improved image handling using media_handle_sideload

