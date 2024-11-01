=== YT Tree Menu ===
Contributors: ytruly
Donate link: http://ytruly.net/
Tags: wordpress, widget, plugin, sidebar, menu, tree, cms, page, post, pages, posts, ancestor, parent, child, children, subpage, sub-page, sub, blog, news, filter, folder
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 0.4.2

This plugin is designed to be a page menu for people using WordPress as a CMS

== Description ==

'YT Tree Menu' is a widget plugin that displays a menu of all pages in relation to the current page in a CMS tree style layout. The menu is filtered to only list specific pages including child pages. The blog page and posts are supported and it also has the option to exclude items and sort the menu by different columns.

== Installation ==

1. Upload the directory `yt-tree-menu` to the `/wp-content/plugins/` directory or install the plugin directly with the 'Install' function in the 'Plugins' menu in WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the sidebar widget through the 'Design' menu in WordPress

== Screenshots ==

1. Widget on Static Home Page
2. Widget on Sub Page
3. Widget on Static Blog Page
4. Widget on Search Page
5. Widget on Empty Page

== Changelog ==

= 0.4.2 =
* Fixed: Exclude Top-Level Pages option

= 0.4.1 =
* Fixed: Incorrect Closing Tags
* Fixed: Validation
* Added support for 404 Page
* Renamed file correctly to yt-tree-menu.php

= 0.4 =
* Blog as static child page now shows full menu
* Added Search, Tag, Category and Date Support
* Increased Post / Level Limit numbers
* Search/Date/Cat/Tag results limited to post number
* Fixed bug: Search has no results
* Fixed Bug: Order of pages by ID if its the same publish date
* Removed spans - added current item class to <A> tags
* Added class for path item (parent or ancestor of current)
* Added non-level classes for current / path pages

= 0.3.1 =
* Style fixes
* Bug Fixes

= 0.3 =
* Added blog page and recent posts
* Added non-static blog home page
* Added option for menu levels
* Added option for post count
* Style fixes
* Bug Fixes

= 0.2 =
* Added sort option.
* Bug Fixes.

= 0.1 =
* First stable version.

== Upgrade Notice ==

= 0.4.1 =
Bug Fixes, Validation and 404 Support.

== Frequently Asked Questions ==

= How can I give you feedback or development ideas? =
I would love to hear from anyone using this plugin. If you have found bugs, or have ideas, please let me know! Contact me here: ytwebdev@gmail.com

= Future Development =
I am looking at adding functions for theme integration - including breadcrumbs.  

= Do you accept donations? =
Not yet.

== CSS Styles ==

Most default Wordpress classes have been added to the widget, but some level aware classes have been added for further styling. Level numbers start at 1 (top level pages) and increase depending on how far down the tree structure the pages go. Below are some of the IDs and classes that can be used to customise the menu layout:

* `ul#yttreemenu` - This is the main unordered list ID
* `li.yttml_[LEVEL]` - Level class for list items. For example: `li.yttml_1`, `li.yttml_2`, etc
* `a.yttma_[LEVEL]` - Level class for page links. For example: `a.yttma_1`, `a.yttma_2`, etc
* `a.yttm_current` - Class for current open page
* `a.yttm_current_[LEVEL]` - Level class for current open
 page. For example: `a.yttm_current_1`, `a.yttm_current_2`, etc
* `a.yttm_path` - Class for current path (parent / ancestor) page
* `a.yttm_path_[LEVEL]` - Level class for current path (parent / ancestor) page. For example: `a.yttm_path_1`, `a.yttm_path_2`, etc

To test the path / current item classes, simply add the following to your CSS stylesheet:
a.yttm_path {color:red;}
a.yttm_current {color:orange;}