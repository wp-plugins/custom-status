=== Custom Status ===
Contributors: lucdecri, carminericco
Tags: custom status, custom posttype, new status, status, publish action  
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 1.1

Add new statuses to post, page and custom-post.

New statuses (created by user interface or from register_post_status() function) are listed in "edit-status" menu for post.



== Description ==
Add new statuses to post, page and custom-post, by simple user interface.

New statuses are listed in drop-down list for "edit-status" menu for post.


In the drop-down list you can view all custom status (status created from register_post_status() function too) 

This plugin add 3 new hook :

- {$post_type}_save_action : add/edit "save" actions in minor-publish section for publish metabox

- {$post_type}_available_statuses : change/remove statuses in status dropdown list in minor-publish section for publish metabox

- {$post_type}_publish_action : edit publish action label in publish section for publish metabox


== Installation ==

Use standard wordpress installation.


== Frequently Asked Questions ==

nothing yet!


