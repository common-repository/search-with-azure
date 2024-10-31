=== Plugin Name ===
Contributors: neilb27
Donate link: http://wpazuresearch.com
Tags: azure, search
Requires at least: 3.7
Tested up to: 4.3
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use the power of the Microsoft Cloud to reduce load on your server and have a faster and more intelligent search.

== Description ==

Use the power of the Microsoft Cloud to reduce load on your server and have a faster and more intelligent search.

This WordPress plugin is easy to install and configure to quickly give you improved search on your site.

Microsoft® Azure™ Search is a scalable search service that leverages Microsoft's deep knowledge of natural language processing.

This plugin adds all your posts and pages into the index and uses the search index to provide search suggestions and search results for your site.

Follow the four step Installation instructions to get up and running on your site.

== Installation ==

Step 1 - Create Search Service
------------------------------
* Go to the Azure Portal to create the Search service.
* Click the big + button at the top left and choose Data & Storage and then Search.
* Choose all the options and click Create. It will take a few minutes to create the service.

Step 2 - Install Plugin
-----------------------
* Login to your WordPress dashboard, go to Plugins >> Add New, search for search-with-azure, and click to install.
* Get the admin key and query key from the Azure Portal.
* In the WordPress dashboard go to the Settings page for the Search with Azure plugin and enter the two keys. Also enter the name of the search service and enter "posts" for the index name. Leave the Search Page ID blank for now.
* Click "Save Changes".
* Click "Initialize Now" to load the pages and posts into the index service.

Step 3 - Create Search Results Page
-----------------------------------
* Create a page that will be the result page when performing a search. This should be a page with the shortcode [search_with_azure]
* Enter the id of this page in Search with Azure Settings.

Step 4 - Use Search Widget
--------------------------
* You should use the widget anywhere you would normally use the default search widget, for example the sidebar.
* You may also like to edit the theme to use the [search_with_azure] shortcode in the 404.php page.

The latest version of these instructions, including screenshots, can be found at
http://wpazuresearch.azurewebsites.net/tutorial/

== Frequently Asked Questions ==

= Can I use the free version of Azure Search? =

Yes. You can use the free or the paid version of Azure Search.

= Where should I host my website? =

You can host your website where you like, but you will get better performance if it's hosted in the same Azure data center as the Azure Search service.

= Where can I see of demo of this plugin? =

http://wpazuresearch.azurewebsites.net/

== Screenshots ==

1. Search results id setting
2. Use widget in sidebar
3. Search results page

== Changelog ==

= 1.1.1 =
* Bug fixes.

= 1.1 =
* Initial version.
