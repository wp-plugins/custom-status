=== Custom Status ===
Contributors: lucdecri, carminericco
Tags: custom status, custom posttype, new status, status, publish action  
Requires at least: 3.1
Tested up to: 3.5.1
Stable tag: 1.3
License: GPLv2

Add new statuses (created by user interface or from register_post_status() function) to post, page and custom-post.



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

== Upgrade Notice ==
Follow wordpress link.
 
No status are lost. 

== ChangeLog ==
*1.3*
- some bug fix
- fix using Event+

*1.2*
- fix hook to labeling save button
- fix hook to labeling publish button 

Fix some bug

== Frequently Asked Questions ==
- Q: When click "publish" button, my custom status is lost.

- A: You use "save" button to save post preserving custom status. Else use save_post hook to set correct status when publish


- Q: Some custom status are not modifiable. Why?

- A: Coded defined custom status are not modifiable from user interface. 

- Q: I want publish post in a custom status. 

- A: You can use save_post Hook. This function to do this, and define new flow for your posts.
<PRE>
function my_personal_flow($post_id) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )   return;
 
  // more security check...   

  if (($_POST['original_post_status']=='draft') && ($_POST['post_status']=='publish') )
	$new_post['post_status']='planned';
  if (($_POST['original_post_status']=='planned') && ($_POST['post_status']=='publish') )
	$new_post['post_status']='executed';
  if (($_POST['original_post_status']=='executed') && ($_POST['post_status']=='publish') ) 	$new_post['post_status']='approved';
  if (($_POST['original_post_status']=='approved') && ($_POST['post_status']=='publish') ) 	$new_post['post_status']='approved';
   remove_action( 'save_post', 'my_personal_flow' ); // to remove loop!! 
   wp_update_post($new_post);
   add_action( 'save_post', 'my_personal_flow' ); // to add filter for next save!!
 }
add_action('save_post','my_personal_flow');
</PRE>
