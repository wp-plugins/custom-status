<?php
/*
Plugin Name: Custom Status
Plugin URI: #
Description: This plugin allow to manage custom statuses
Version: 1.3
Author: Carmine Ricco
Author URI: http://www.syriusweb.com
License: GPL2
*/
	

define('CS_DOMAIN','custom_status');
define('CS_OPT_STATUSES','_extra_status');

	global $hidden_field_name;
	global $data_field_name;
	global $data_field_slug;
	global $default_field_name;
	global $count_statuses_name;
	global $data_field_count_singular;
	global $data_field_count_plural;
	global $data_field_public; 
	
	global $label;	
	global $label_slug;
	global $label_count_singular;
	global $label_count_plural;
	global $label_public;

	$hidden_field_name = 'mt_submit_hidden';
	$data_field_name = 'extra_status';	
	$data_field_slug = 'extra_status_slug';	
	$default_field_name = 'default_status';	
	$count_statuses_name = "count_statuses";
	$data_field_count_singular = "count_singular";
	$data_field_count_plural = "count_plural";	
	$data_field_public = "is_public";
	
	$label = __("Label", CS_DOMAIN );
	$label_slug = __("Slug", CS_DOMAIN );		
	$label_count_singular = __("Label Count Singular", CS_DOMAIN );
	$label_count_plural = __("Label Count Plural", CS_DOMAIN );
	$label_public = __("Public", CS_DOMAIN );


	add_action('init', 'custom_status_init');
	function custom_status_init() {
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters('plugin_locale', get_locale(), CS_DOMAIN);
	 
	    load_textdomain(CS_DOMAIN, WP_LANG_DIR.'/'.CS_DOMAIN.'/'.CS_DOMAIN.'-'.$locale.'.mo');
	    load_plugin_textdomain(CS_DOMAIN, FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
	    
	}
	



	add_action('admin_head', 'cs_add_custom_status_javascript');	
	// aggiune un javascript per la pagina di creazione degli stati
	function cs_add_custom_status_javascript() {
	?>
	<script type="text/javascript" >
	function cs_add_custom_status_javascript(i) {
	
		jQuery("#add_status_button").hide();	
		jQuery("input[id = count_statuses]").val(i);
		
		var data = {
			action: 'add_custom_status',			
			i: i
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#append_fields').html(response).hide().fadeIn('slow');
			
		});
	}
	</script>
	<?php
	}
	
	
	add_action('wp_ajax_add_custom_status', 'cs_add_custom_status_callback');		
        // necessario per gestire l'ajax quando creo gli stati
	function cs_add_custom_status_callback() {		
		echo cs_add_custom_status( $_POST[ 'i' ] );				
		die();		
	}
	
	// aggiunge la riga dello stato $i
	function cs_add_custom_status($i) {		
		
		$opt_val_array = maybe_unserialize(get_option( CS_OPT_STATUSES ));	
		$opt_val = @$opt_val_array[$i-1]['label'];
		$slug_val = @$opt_val_array[$i-1]['slug'];
		$count_singular_val = @$opt_val_array[$i-1]['singular'];
		$count_plural_val = @$opt_val_array[$i-1]['plural'];
		$public_val = @$opt_val_array[$i-1]['public'];		
		
		return cs_print_custom_status($i, $opt_val, $slug_val, $count_singular_val, $count_plural_val, $public_val);		
		
		
	}
	
	// visualizza la riga per editare gli stati
	function cs_print_custom_status($i, $opt_val, $slug_val, $count_singular_val, $count_plural_val, $public_val){
		
		global $data_field_name;
		
		global $label;
		global $label_slug;
		global $data_field_slug;
		global $label_count_singular;
		global $data_field_count_singular;
		global $label_count_plural;
		global $data_field_count_plural;	
		global $data_field_public;	
		global $label_public;
		
		
		if ($public_val == "true"){
			$checked = 'checked="checked"';
		} else {			
			$checked = '';
		}		
		
		$field = '<p id="p_extra_status_'.$i.'">' . $label . '<input type="text" id="' . $data_field_name.'_'.$i.'" name="' . $data_field_name.'_'.$i . '" value="' . $opt_val . '" size="20">' .
		$label_slug . '<input type="text" id="' . $data_field_slug.'_'.$i.'" name="' . $data_field_slug.'_'.$i . '" value="' . $slug_val . '" size="20">' .
		$label_count_singular . '<input type="text" id="' . $data_field_count_singular.'_'.$i.'" name="' . $data_field_count_singular.'_'.$i . '" value="' . $count_singular_val . '" size="20">' .
		$label_count_plural . '<input type="text" id="' . $data_field_count_plural.'_'.$i.'" name="' . $data_field_count_plural.'_'.$i . '" value="' . $count_plural_val . '" size="20">' .
		$label_public . '<input type="checkbox" name="' . $data_field_public.'_'.$i . '" value="true" '. $checked .' />
		<a href="#" onclick="cs_del_custom_status_javascript('.$i.')">X</a>
		</p>';
		
		return $field;	
		
	}
	
	// visualizza la riga per gli stati non modificabili (creati da codice o da plugin)
	function cs_print_statuses() {			
		
		$label_default = __("Default Status", CS_DOMAIN );	
		$statuses = get_post_stati(null, 'objects' );		
		
		$editable_statuses_array = maybe_unserialize(get_option( CS_OPT_STATUSES, true ));
		if ($editable_statuses_array=='' || !is_array($editable_statuses_array)) $editable_statuses_array=array();
		$editable_statuses = array();
		
		foreach($editable_statuses_array as $ed) {
			$editable_statuses[$ed['slug']] = $ed['slug'];
		}
		
		$field = "";
		$i = 1;
		foreach($statuses as $status) {
			
			$opt_val = $status->label;
			$slug_val = $status->name;
			$label_count = $status->label_count;
			if (isset($label_count['singular']) && isset($label_count['plural'])) {
				list($count_singular_val,$dummy) = split(' ',$label_count['singular'],2);
				list($count_plural_val,$dummy) = split(' ',$label_count['plural'],2);
				$public_val = $status->public;	
				if (in_array($status->name,$editable_statuses)) {
					echo cs_print_custom_status($i, $opt_val, $slug_val, $count_singular_val, $count_plural_val, $public_val);		
					$i++;			
				} else {
					if (('inherit'!=$status->name) && ('auto-draft'!=$status->name)) 
					$field .= '<p>' . $label_default . ' <input type="text" readonly="readonly" id="fixed_status_'.$slug_val .'" name="fixed_status_'.$slug_val . '" value="' . $status->label . '" size="20"></p>';
				}
			}
				
		}
		return $field;
			
	}
	
	
	add_action('admin_head', 'cs_del_custom_status_javascript');	
	// aggiunge un javascript per eliminare la riga dello stato
	function cs_del_custom_status_javascript() {
	?>
	<script type="text/javascript" >
	function cs_del_custom_status_javascript(i) {	
		
		jQuery("input[id = count_statuses]").val(i-1);
		
		var data = {
			action: 'del_custom_status',			
			i: i
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#p_extra_status_'+parseInt(response)).css('background-color', '#FF8080')
			jQuery('#p_extra_status_'+parseInt(response)).fadeOut();	
		});
	}
	</script>
	<?php
	}
	
	
	add_action('wp_ajax_del_custom_status', 'cs_del_custom_status_callback');		
	// elimina uno stato in base alla chiamata ajax
	function cs_del_custom_status_callback() {				

		
		$result = __("Status erased", CS_DOMAIN );
		$i = $_POST[ 'i' ];
		$opt_val_array = maybe_unserialize(get_option( CS_OPT_STATUSES ));
		
		array_splice ( $opt_val_array , $i-1, 1 );		
		
		update_option(CS_OPT_STATUSES, serialize($opt_val_array));		
		
		echo $i;				
		die();		
	}
	
	
		
	 // add the admin options page
	add_action('admin_menu', 'cs_plugin_custom_statuses_add_page');		
	//Aggiunge la pagina per modificaare gli stati
	function cs_plugin_custom_statuses_add_page() {
		add_options_page('Custom Statuses Page', 'Custom Statuses', 'manage_options', 'custom_statuses_page', 'cs_settings_page');
	}	
	// la pagina di modifica degli stati	
	function cs_settings_page() {
		
		global $hidden_field_name;
		global $data_field_name;
		global $data_field_slug;
		global $default_field_name;
		global $count_statuses_name;
		global $data_field_count_singular;
		global $data_field_count_plural;	
		global $data_field_public;		
	
	    //must check that the user has the required capability 
	    if (!current_user_can('manage_options'))
	    {
	      wp_die( __('You do not have sufficient permissions to access this page.', CS_DOMAIN) );
	    }
	
	    
	 
	    if( isset($_POST[ $count_statuses_name ]) ) {	    	
	    	
	       	for($j=1; $j<=$_POST[ $count_statuses_name ]; $j++) {	       		
	    		
	       		
	       		
	    		$opt_val = $_POST[ $data_field_name."_".$j ];
	    		$slug_val = $_POST[ $data_field_slug."_".$j ];
	    		$count_singular_val = $_POST[ $data_field_count_singular."_".$j ];	 
	    		$count_plural_val = $_POST[ $data_field_count_plural."_".$j ];   		
	    		$public_val = $_POST[ $data_field_public."_".$j ];	    		
	    		
		        if ($opt_val != '') {	
		        	
		        	if ($slug_val != '') {
		        		$slug = sanitize_title($slug_val);
		        	} else {
		        		$slug = sanitize_title($opt_val);
		        	}

		        	if ($count_singular_val != '') {
		        		$count_singular = $count_singular_val;
		        	} else {
		        		$count_singular = $opt_val;
		        	}
		        	
		        	if ($count_plural_val != '') {
		        		$count_plural = $count_plural_val;
		        	} else {
		        		$count_plural = $opt_val;
		        	}
		        	
		        	if ($public_val == "true") {
		        		$public = $public_val;
		        	} else {
		        		$public = "false";
		        	}
			        
		    		$opt_val_array[] = array(
							 'label' => $opt_val,
							 'slug' => $slug,
							 'singular' => $count_singular,
							 'plural' => $count_plural,
							 'public' => $public);	
		        }	    		
	    	}     	
	    	
	        update_option(CS_OPT_STATUSES, serialize($opt_val_array));	      
		cs_custom_post_status();
	        
			?>
			<div class="updated">
				<p><strong><?php _e('Statuses saved', CS_DOMAIN ); ?></strong></p>
			</div>
			<?php			
			
		}
		
		$opt_val_array = maybe_unserialize(get_option( CS_OPT_STATUSES ));
		$count_statuses = count($opt_val_array);
	    
	    if (!$count_statuses) {
	    	$count_statuses = 0;
	    }
	  
	    
		?>	
				
	    <div class="wrap">		
	    
			<?php
		    echo "<h2>" . __( 'Manage your own Custom Statuses', 'custom_status' ) . "</h2>";
		    echo "<p>" . __( 'Options relating to the Custom Statuses', 'custom_status' ) . "</p>";						
			?>
		
			<form name="form_extra_status" id="form_extra_status" method="post" action="">
				<input type="hidden" id="count_statuses" name="count_statuses" value="<?php echo $count_statuses; ?>">
				
				<?php 
				echo cs_print_statuses();
				
				/*for ($i=1; $i<=$count_statuses; $i++) {						
					echo cs_add_custom_status($i);						
				}*/
				?>					
				
				<div id="append_fields">
				
				</div>
				
				<p id="add_status_button"><a href="#" onclick="cs_add_custom_status_javascript('<?php echo $count_statuses+1 ?>')">Add new status</a></p>				
									
				<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', CS_DOMAIN ) ?>" />
				</p>
				
			</form>
		
		</div>		
	<?php	 
	}	
	
	
	
	add_action( 'init', 'cs_custom_post_status' );
        // registra gli stati salvati nelle options
	function cs_custom_post_status(){
		

		$opt_val_array = maybe_unserialize(get_option( CS_OPT_STATUSES));
		
		$count_statuses = count($opt_val_array);
		
		for($i=0; $i<$count_statuses; $i++ ) {
			register_post_status( $opt_val_array[$i]['slug'], array(
				'label' => __( $opt_val_array[$i]['label'], CS_DOMAIN ),
				'public' => $opt_val_array[$i]['public'],
				'exclude_from_search' => false,
				'protected'   => true,
				'_builtin'    => true, /* internal use only. */
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( $opt_val_array[$i]['singular'].' <span class="count">(%s)</span>', $opt_val_array[$i]['plural'].' <span class="count">(%s)</span>' ),
			) );		
		}
	}
		
	add_action( 'current_screen', 'cs_add_meta_boxes');
        // elimina il box classico per pubblicare e lo costituisce con quello custom
	function cs_add_meta_boxes($screen) {
		if ($screen->base == 'post' && $screen->post_type != '' ) {
			remove_meta_box('submitdiv', $screen->post_type, 'normal');
			add_meta_box( 'submitdiv', __( 'Publish Custom', CS_DOMAIN ), 'cs_custom_post_submit_box', $screen->post_type , 'side', 'high' );	
		}
		
	
	}
	

	
	// sostituisce submitdiv (il form per fare il submit di un post) con un nuovo che permette di gestire i customstatus
	function cs_custom_post_submit_box($post) {
		global $action;
	
		if ($post==NULL) return;
		
		$post_type = $post->post_type;
		$post_status = $post->post_status;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);	
		
		?>
		<div class="submitbox" id="submitpost">
		<div id="minor-publishing">
		
		<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
		<div style="display:none;">
		<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
		</div>
		
		<div id="minor-publishing-actions">
		<div id="save-action">
		<?php
			// questo deve essere usato per salvare con lo stato attuale
                        //@FIXME qualche volta riporta in stato bozza. Perchè?
                        $save_action = array('label' => 'Save Draft');
                        $save_action = apply_filters('save_action',$save_action);
                        $save_action = apply_filters($post_type.'_save_action',$save_action);
                        
		 	if ($save_action['label']!='') {
				echo '<input type="submit" name="save" id="save-post" value="'. esc_attr__($save_action['label']) . '" tabindex="4" class="button button-highlighted" />';
			}
		?>	
			
		<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="draft-ajax-loading" alt="" />
		</div>
		
		<div id="preview-action">
		<?php
		if ( 'publish' == $post_status ) {
			$preview_link = esc_url( get_permalink( $post->ID ) );
			$preview_button = __( 'Preview Changes' );
		} else {
			$preview_link = get_permalink( $post->ID );
			if ( is_ssl() )
				$preview_link = str_replace( 'http://', 'https://', $preview_link );
			$preview_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) ) );
			$preview_button = __( 'Preview' );
		}
		?>
		<a class="preview button" href="<?php echo $preview_link; ?>" target="wp-preview" id="post-preview" tabindex="4"><?php echo $preview_button; ?></a>
		<input type="hidden" name="wp-preview" id="wp-preview" value="" />
		</div>
		
		<div class="clear"></div>
		</div><?php // /minor-publishing-actions ?>
		
		<div id="misc-publishing-actions">
		<div class="misc-pub-section<?php if ( !$can_publish ) { echo ' misc-pub-section-last'; } ?>"><label for="post_status"><?php _e('Status:') ?></label>
		<span id="post-status-display">
		<?php
		switch ( $post_status ) {
			case 'private':
				_e('Privately Published');
				break;
			case 'publish':
				_e('Published');
				break;
			case 'future':
				_e('Scheduled');
				break;
			case 'pending':
				_e('Pending Review');
				break;
			case 'draft':
			case 'auto-draft':
				_e('Draft');
			break;
			default :
				//@TODO qui dovrei passare il status_label e non status_name
				_e($post->post_status);
			break;
		}
		?>
		</span>
		<?php
		if ( 'publish' == $post_status || 'private' == $post_status || $can_publish ) { ?>
		<a href="#post_status" <?php if ( 'private' == $post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js" tabindex='4'><?php _e('Edit') ?></a>
		
		<div id="post-status-select" class="hide-if-js">
		<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
		
		<select name='post_status' id='post_status' tabindex='4'>
			<?php		
			$statuses = get_post_stati(null, 'object');
			$statuses = apply_filters($post_type.'_available_statuses',$statuses);
			foreach($statuses as $status) {	
				if ($status->name != "auto-draft" && $status->name != "inherit" && $status->name != "recurrent") {		
				?>
				<option<?php selected( $post_status, $status->name ); ?> value='<?php echo $status->name ?>'><?php echo __($status->label, CS_DOMAIN ) ?></option>
				<?php
				}
			}		
			?>
		</select>	
		
		
		 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
		 <a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
		</div>
		
		<?php } ?>
		</div><?php // /misc-pub-section ?>
		
		<div class="misc-pub-section " id="visibility">
		<?php _e('Visibility:'); ?> <span id="post-visibility-display"><?php
		//@TODO qua ci starebbe bene un bel filtro '$customtype_visibility_options'
		if ( 'private' == $post_status ) {
			$post->post_password = '';
			$visibility = 'private';
			$visibility_trans = __('Private');
		} elseif ( !empty( $post->post_password ) ) {
			$visibility = 'password';
			$visibility_trans = __('Password protected');
		} elseif ( $post_type == 'post' && is_sticky( $post->ID ) ) {
			$visibility = 'public';
			$visibility_trans = __('Public, Sticky');
		} else {
			$visibility = 'public';
			$visibility_trans = __('Public');
		}
		
		echo esc_html( $visibility_trans ); ?></span>
		<?php if ( $can_publish ) { ?>
		<a href="#visibility" class="edit-visibility hide-if-no-js"><?php _e('Edit'); ?></a>
		
		<div id="post-visibility-select" class="hide-if-js">
		<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="<?php echo esc_attr($post->post_password); ?>" />
		<?php if ($post_type == 'post'): ?>
		<input type="checkbox" style="display:none" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky" <?php checked(is_sticky($post->ID)); ?> />
		<?php endif; ?>
		<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>" />
		
		
		<input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php checked( $visibility, 'public' ); ?> /> <label for="visibility-radio-public" class="selectit"><?php _e('Public'); ?></label><br />
		<?php if ( $post_type == 'post' && current_user_can( 'edit_others_posts' ) ) : ?>
		<span id="sticky-span"><input id="sticky" name="sticky" type="checkbox" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> tabindex="4" /> <label for="sticky" class="selectit"><?php _e( 'Stick this post to the front page' ); ?></label><br /></span>
		<?php endif; ?>
		<input type="radio" name="visibility" id="visibility-radio-password" value="password" <?php checked( $visibility, 'password' ); ?> /> <label for="visibility-radio-password" class="selectit"><?php _e('Password protected'); ?></label><br />
		<span id="password-span"><label for="post_password"><?php _e('Password:'); ?></label> <input type="text" name="post_password" id="post_password" value="<?php echo esc_attr($post->post_password); ?>" /><br /></span>
		<input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php checked( $visibility, 'private' ); ?> /> <label for="visibility-radio-private" class="selectit"><?php _e('Private'); ?></label><br />
		
		<p>
		 <a href="#visibility" class="save-post-visibility hide-if-no-js button"><?php _e('OK'); ?></a>
		 <a href="#visibility" class="cancel-post-visibility hide-if-no-js"><?php _e('Cancel'); ?></a>
		</p>
		</div>
		<?php } ?>
		
		</div><?php // /misc-pub-section ?>
		
		<?php
		// translators: Publish box date format, see http://php.net/date
		$datef = __( 'M j, Y @ G:i' );
		if ( 0 != $post->ID ) {
			if ( 'future' == $post->post_status ) { // scheduled for publishing at a future date
				$stamp = __('Scheduled for: <b>%1$s</b>');
			} else if ( 'publish' == $post->post_status || 'private' == $post->post_status ) { // already published
				$stamp = __('Published on: <b>%1$s</b>');
			} else if ( '0000-00-00 00:00:00' == $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
				$stamp = __('Publish <b>immediately</b>');
			} else if ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // draft, 1 or more saves, future date specified
				$stamp = __('Schedule for: <b>%1$s</b>');
			} else { // draft, 1 or more saves, date specified
				$stamp = __('Publish on: <b>%1$s</b>');
			}
			$date = date_i18n( $datef, strtotime( $post->post_date ) );
		} else { // draft (no saves, and thus no date specified)
			$stamp = __('Publish <b>immediately</b>');
			$date = date_i18n( $datef, strtotime( current_time('mysql') ) );
		}
		
		if ( $can_publish ) : // Contributors don't get to choose the date of publish ?>
		<div class="misc-pub-section curtime misc-pub-section-last">
			<span id="timestamp">
			<?php printf($stamp, $date); ?></span>
			<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" tabindex='4'><?php _e('Edit') ?></a>
			<div id="timestampdiv" class="hide-if-js"><?php touch_time(($action == 'edit'),1,4); ?></div>
		</div><?php // /misc-pub-section ?>
		<?php endif; ?>
		
		<?php do_action('post_submitbox_misc_actions'); ?>
		</div>
		<div class="clear"></div>
		</div>
		
		<div id="major-publishing-actions">
		<?php do_action('post_submitbox_start'); ?>
		<div id="delete-action">
		<?php
		if ( current_user_can( "delete_post", $post->ID ) ) {
			if ( !EMPTY_TRASH_DAYS )
				$delete_text = __('Delete Permanently');
			else
				$delete_text = __('Move to Trash');
			?>
		<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
		} ?>
		</div>
		
		<div id="publishing-action">
		<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="" />
		<?php
		// @TODO qui va verificato se funziona sempre
                        $next_action = array('label' => 'Publish', 'action' => 'publish');
                        $next_action = apply_filters('publish_action', $next_action);
			$next_action = apply_filters($post_type.'_publish_action', $next_action);
		 	echo '<input name="original_publish" type="hidden" id="original_publish" value="'. esc_attr__($next_action['label']) .'" />';
			submit_button( __( $next_action['label'] ), 'primary', $next_action['action'], false, array( 'tabindex' => '5', 'accesskey' => 'p' ) ); 
		?>
		</div>
		<div class="clear"></div>
		</div>
		</div>
	
	<?php
	}
		
	add_filter('post_save_action','cs_post_save_action');
	function cs_post_save_action($action) {
	global $post;
		if ( NULL == $post )
			$action['label'] = 'Save as Draft';
		elseif ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status )   
			$action['label'] = 'Save as Draft';
		elseif ( 'pending' == $post->post_status && $can_publish )
			$action['label'] = 'Save as Pending';
		else
			$action['label']='';
		return $action;
	}
		
	add_filter('post_publish_action','cs_post_publish_action');
        // Indica che azione e label deve avere il pulsante "publish" (major_publish)
	function cs_post_publish_action($action) {
	global $post;

		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);		

		if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
			if ( $can_publish ) {
				if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) {
					$action['name'] = 'Schedule';
					$action['action'] = 'publish';
				} else {
					$action['name'] = 'Publish';
					$action['action'] = 'publish';
				}
			} else {
				$action['name'] = 'Submit for Review';
				$action['action'] = 'publish';
			}
		} else { 
			$action['name'] = 'Update';
			$action['action'] = 'save';
		} 
		return $action;

		
	}
	
	
	add_filter('post_available_statuses','cs_post_filter_status');
        // ripristina i soli stati standard per il post? da verificare se serve
	function cs_post_filter_status($statuses) {
	global $post;
	
	
	return $statuses;

	}
	
	add_filter('page_available_statuses','cs_page_filter_status');
	// ripristina i soli stati standard per la pagina? da verificare se serve
	function cs_page_filter_status($statuses) {
	global $post;
		
		
	
	return $statuses;

	}
	