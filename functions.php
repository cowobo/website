<?php
//Definitions
define ( 'SITEURL', get_bloginfo('url') );
define ( 'PERSONALFEEDSLUG', 'personal-feed' );
define ( 'PERSONALFEEDURL', SITEURL . '/' . PERSONALFEEDSLUG );

// Libraries
include_once('lib/class-cowobo-feed.php');
include_once('lib/class-cowobo-social.php');
include_once('lib/class-related-posts.php');

global $social; 
$social = new Cowobo_Social;

// Session
if (!session_id())
    session_start();

// Remove admin bar
add_filter( 'show_admin_bar' , 'my_function_admin_bar');
function my_function_admin_bar(){
    return false;
}

// Send notification to author when comment is posted
add_action('comment_post', 'comment_notice');
function comment_notice($comment_id) {
	global $wpdb;
	$comment = get_comment($comment_id);
	$post = get_post($comment->comment_post_ID);
	$siteurl = get_option('siteurl');
	$user = get_userdata($post->post_author);
	$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "\r\n";
	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
	$notify_message .= sprintf( __('Author : %1$s'), $comment->comment_author) . "\r\n";
	$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
	$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
	$notify_message .= sprintf( __('Please visit the moderation panel:')) . "\r\n";
	$notify_message .= "$siteurl/wp-admin/moderation.php\r\n";
	$subject = sprintf( __('[%1$s] New Comment requires moderation'), get_option('blogname') );
	$notify_message = apply_filters('comment_moderation_text', $notify_message, $comment_id);
	$subject = apply_filters('comment_moderation_subject', $subject, $comment_id);
	@wp_mail($user->user_email, $subject, $notify_message);
	return true;
}

//get primal category of cat
function cwob_get_type($catid) {
	$ancestors = get_ancestors($catid,'category');
	if (empty($ancestors)):
		return get_category($catid);
	else:
		return get_category(array_pop($ancestors));
	endif;
}

//get primal category of post
function cwob_get_category($postid) {
	$cat = get_the_category($postid);
	$ancestors = get_ancestors($cat[0]->term_id,'category');
	if (empty($ancestors))
		return $cat[0];
	return get_category(array_pop($ancestors));
}

//return time passed since publish date
function time_passed($timestamp){
    $timestamp = (int) $timestamp;
    $current_time = time();
    $diff = $current_time - $timestamp;
    $intervals = array ('day' => 86400, 'hour' => 3600, 'minute'=> 60);
    //now we just find the difference
    if ($diff == 0) return 'just now';
    if ($diff < $intervals['hour']){
        $diff = floor($diff/$intervals['minute']);
        return $diff == 1 ? $diff . ' min ago' : $diff . ' mins ago';
    }
    if ($diff >= $intervals['hour'] && $diff < $intervals['day']){
        $diff = floor($diff/$intervals['hour']);
        return $diff == 1 ? $diff . ' hour ago' : $diff . ' hours ago';
    }
    if ($diff >= $intervals['day']){
        $diff = floor($diff/$intervals['day']);
        return $diff == 1 ? $diff . ' day ago' : $diff . ' days ago';
    }
}

function cwob_default_image($cat){
	$images = get_posts(array('post_type' => 'attachment', 'numberposts' => 1, 'post_mime_type' =>'image', 'name' =>'default-'.$cat->slug));
	if (empty($images)):
		$images = get_posts(array('post_type' => 'attachment', 'numberposts' => 1, 'post_mime_type' =>'image', 'name' =>'default'));
	endif;
	return $images;
}

function cwob_get_first_image($postID) {
	$images = get_children(array('post_parent' => $postID, 'numberposts' => 1, 'post_mime_type' =>'image'));
	if(empty($images)) { return cwob_default_image(get_the_category($postID)); }
	$images = current($images);
	$src = wp_get_attachment_image_src($images->ID, $size = 'thumbnail');
	return $src[0];
}

// Sort objects stored in array based on object property
function array_object_sort($array,$property,$dir = 'ASC') {
	foreach($array as $a_key => $a_value) {
		$sortable[$a_key] = strtolower($a_value->$property);
	}
	if ( $dir == 'DESC' ) arsort($sortable);
	else asort($sortable);
	foreach($sortable as $s_key=>$s_val) {
		$sorted[] = $array[$s_key];
	}
	return $sorted;
}

// Removes doubles. from an array containing post objects
function remove_doubles($postlist) {
	$postid_list = array();
	foreach ($postlist as $key => $post) {
		foreach ($postid_list as $postid) {
			if ($post->ID == $postid) {
				unset($postlist[$key]);
				$removed = true;
				break;
			}
		}
		if (!$removed) $postid_list[] = $post->ID;
	}
	return $postlist;
}

//sort types by number of related posts
function sort_types($a,$b) {
	return $b['posts'] - $a['posts'];
}

//AJAX CALLBACK FUNCTIONS

add_action("wp_ajax_loadlightbox", "loadlightbox_callback");
add_action("wp_ajax_nopriv_loadlightbox", "loadlightbox_callback");

add_action("wp_ajax_loadgallery", "loadgallery_callback");
add_action("wp_ajax_nopriv_loadgallery", "loadgallery_callback");

add_action("wp_ajax_savechanges", "savechanges_callback");
add_action("wp_ajax_nopriv_savechanges", "savechanges_callback");

add_action("wp_ajax_addtag", "addtag_callback");
add_action("wp_ajax_nopriv_addtag", "addtag_callback");

add_action("wp_ajax_addlocation", "addlocation_callback");
add_action("wp_ajax_nopriv_addlocation", "addlocation_callback");

add_action("wp_ajax_showshare", "showshare_callback");
add_action("wp_ajax_nopriv_showshare", "showshare_callback");

add_action("wp_ajax_feedsetter", "feedsetter_callback");
add_action("wp_ajax_nopriv_feedsetter", "feedsetter_callback");

add_action("wp_ajax_deletemsg", "deletemsg_callback");
add_action("wp_ajax_nopriv_deletemsg", "deletemsg_callback");

function loadlightbox_callback(){
	global $wp_query;
	$wp_query->is_single = true;
	$postid = $_POST["postid"];

	if($postid == 'newtype'):
		$catid = $_POST["currentcat"];
		$current_user = wp_get_current_user();
		$post_id = wp_insert_post( array(
			'post_status' => 'auto-draft',
			'post_title' => ' ',
			'post_category' => array($catid),
			'post_author' => $current_user->ID,
		));
		$loadpost = query_posts(array('post_status'=>'auto-draft', 'posts_per_page'=>1));
		$newpost = true;
	else:
		$loadpost = query_posts(array('p'=>$postid));
		$newpost = false;
	endif;

	foreach($loadpost as $post): setup_postdata($post); the_post(); $ajax = true;
	include(TEMPLATEPATH.'/templates/postbox.php');
	endforeach;
	wp_reset_query();
	die;
}

function loadgallery_callback($postid){
	if(!empty($_POST['postid'])) $postid = $_POST['postid'];
	if($postid != 'new'):
		$images = get_children(array('post_parent' => $postid, 'numberposts' => -1, 'post_mime_type' =>'image', 'order'=>'ASC'));
		if(!empty($images)):
		foreach($images as $image):?>
			<div class="slide"><?php
				$imgsrc = wp_get_attachment_image_src($image->ID, $size = 'large');?>
				<img src="<?php echo $imgsrc[0];?>" width="100%" alt=""/>
 			</div><?php
		endforeach;
		endif;
	endif;
}

function savechanges_callback(){
	global $wpdb; global $related;

	// update the main post attributes
	$postid = $_POST["postid"];
	$postdata = array('ID' => $postid, 'post_status' => 'publish','post_category' => explode(',', $_POST["tags"]));
	if($title = $_POST["edittitle"]) $postdata['post_title'] = $title;
	if($content = $_POST["editcontent"]) $postdata['post_content'] = $content;
	wp_update_post($postdata);

	//reset all custom field and relationships
	$related->cwob_delete_relationships($postid);
	delete_post_meta($postid, 'authors');
	delete_post_meta($postid, 'coordinates');
	
	//then add all the custom fields and make sure the author is listed
	foreach ($_POST as $key => $value) :
		if($value != ''):
			delete_post_meta($postid, $key);
			if($key == 'authors'):
				$authors = explode(',', $value);
				foreach ($authors as $author):
					add_post_meta($postid, 'authors', $author);
					$query = "INSERT INTO ".$wpdb->prefix."post_relationships VALUES($postid, $author)";
					$result = $wpdb->query($query);
				endforeach;
			elseif($key == 'posts'):
				$relatedpostids = explode(',' , $value);
                $related->create_relations($postid, $relatedpostids);
			else:
				update_post_meta($postid, $key, $value);
			endif;
		endif;
	endforeach;
}

function addtag_callback(){
	$catdata = array(
		'cat_name'=> $_POST["tagname"],
		'category_parent'=> $_POST["parent"],
		);
	$catid = wp_insert_category($catdata);?>
	<div class="<?php echo $catid;?> listitem">
		<div class="thumbnail"></div>
		<div class="text"><a href="<?php echo get_category_link($catid);?>"><?php echo $_POST["tagname"];?></a>
		<span class="remove button"> (x)</span><br/>Updated just now</div>
	</div><?php
	die();
}

function addlocation_callback(){
	$current_user = wp_get_current_user();
	//convert titles to
	$catslug = sanitize_title($_POST["country"]);
	$postslug = sanitize_title($_POST["city"]);

	//check if country exists and otherwise add it
	$checkcat = get_category_by_slug($catslug);
	if(!$checkcat):
		$catid = wp_insert_category(array(
			'cat_name'=> $_POST["country"],
			'category_parent'=> $_POST["parent"],
		));
	else:
		$catid = $checkcat->term_id;
	endif;

	//check if city exists and otherwise add it
	$checkpost = get_posts(array('name' => $postslug, 'cat' => $catid, 'post_status' => 'publish'));
	if(!$checkpost):
		$postid = wp_insert_post( array(
			'post_status' => 'publish',
			'post_title' => $_POST["city"],
			'post_category' => array($catid),
			'post_author' => $current_user->ID,
		));
		update_post_meta($postid, 'coordinates', $_POST["coordinates"], true);
	else:
		$postid = $checkpost->ID;
	endif;?>

	<div class="<?php echo $postid;?> listitem">
		<div class="thumbnail"></div>
		<div class="text"><a href="<?php echo get_permalink($postid);?>"><?php echo $_POST["city"];?></a>
		<span class="remove button"> (x)</span><br/>Updated just now</div>
	</div><?php
	die();
}

function showshare_callback(){
	global $social;
	$post_link = get_permalink( $_POST['postid'] );
	$post_title = get_the_title ( $_POST['postid'] ); ?>

	<div class='like_wrapper' style='display:block;text-align:center;'>
        <?php $social = new Cowobo_Social(true);
        $social->show_share($_POST['postid']); ?>
	</div><?php
	die();
}

function feedsetter_callback(){
    global $social;

	print_r ( $_REQUEST );
	$userid = $_POST['user_id'];
	if (empty($userid)) die;
	if (isset($_POST['reset'])) $social->reset_feed($userid);
	if (isset($_POST['add'])) {
		$feed_query = array ( $_POST['feed_type'] => $_POST['feed_id'] );
		$social->add_to_feed($userid,$feed_query);
	} elseif ( isset($_POST['profile'] ) ) {
		$social->add_to_profile( $userid, $_POST['post_id'] );
	}
	die();
}

function deletemsg_callback(){
	if($_POST['commentid']) wp_delete_comment($_POST['commentid']);
}

function get_published_ids() {
	global $wpdb;
	$postobjs = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_status = 'publish'");
	$postids = array();
	foreach ( $postobjs as $post ) {
		$postids[] = $post->ID;
	}
	return $postids;
}

// for easier debugging
function console_log ( $content ) {
	echo "<script>console.log('$content')</script>";
}

// Set up daily cron jobs
add_action('wp', 'activate_daily_events');
function activate_daily_events() {
	if ( !wp_next_scheduled( 'daily_events' ) ) {
		wp_schedule_event(time(), 'daily', 'daily_events' );
	}
}

// Manual Registration stuff
add_action('register_form','show_first_name_field');
function show_first_name_field(){?>
	First Name:<br/>
	<input id="user_email" class="input" type="text" style="width:100%" value="<?php echo $_POST['first']; ?>" name="first" tabindex="3"/><br/>
	Last Name:<br/>
	<input id="user_email" class="input" type="text" style="width:100%" value="<?php echo $_POST['last']; ?>" name="last" tabindex="4"/><br/>
	Organization:<br/>
	<input id="user_organization" class="input" type="text" style="width:100%" value="<?php echo $_POST['organization']; ?>" name="organization" tabindex="5"/><br/>
	<?php
}

add_action('register_post','check_fields',10,3);
function check_fields($login, $email, $errors) {
	global $firstname, $lastname;
	if ($_POST['first'] == '') {
		$errors->add('empty_realname', "<strong>ERROR</strong>: Please Enter in First Name");
	} else { $firstname = $_POST['first'];}
	if ($_POST['last'] == '') {
		$errors->add('empty_realname', "<strong>ERROR</strong>: Please Enter in Last Name");
	} else { $lastname = $_POST['last'];}
	if ($_POST['organization'] == '') {
		$errors->add('empty_organization', "<strong>ERROR</strong>: Please Enter in Organization");
	} else { $organization = $_POST['organization'];}
}

add_action('user_register', 'register_extra_fields');
function register_extra_fields($user_id, $password="", $meta=array())  {
	$userdata = array(); $userdata['ID'] = $user_id;
	$userdata['first_name'] = $_POST['first'];
	$userdata['last_name'] = $_POST['last'];
	wp_update_user($userdata);
}

// Add profile id box to user profile backend for debugging
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );
function my_show_extra_profile_fields( $user ) { ?>
	<table class="form-table">
		<tr>
			<th><label>Profile ID:</label></th>
			<td><input type="text" name="cowobo_profile" id="cowobo_profile" value="<?php echo esc_attr( get_the_author_meta( 'cowobo_profile', $user->ID ) ); ?>"/><br />
			</td>
		</tr>
	</table>
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
function my_save_extra_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
	update_usermeta( $user_id, 'cowobo_profile', $_POST['cowobo_profile'] );
}

/**
 * Return the current pagetitle
 *
 * @param obj (optional) Current category
 * @return str $pagetitle
 */
function cowobo_get_pagetitle ( $currentcat = false ) {
    if ( $userfeed = is_userfeed() )
        $pagetitle = "Favourite Feed / {$userfeed->user_nicename}";
    elseif(is_home()) $pagetitle= '<a href="'.get_bloginfo('url').'"><b>CODERS</b> WITHOUT <b>BORDERS</b></a>';
    elseif(is_search()) $pagetitle = '<b>Search Results</b>';
    elseif(is_404()) $pagetitle = '<b>Post not found</b>..is it one of these?';
    else $pagetitle = '<b>'.$currentcat->name.'</b>';

    return $pagetitle;
}

function is_userfeed() {
    global $wp_query;
    if ( isset ( $wp_query->query_vars['userfeed'] ) && $userfeed = get_user_by ( 'slug', $wp_query->query_vars['userfeed'] ) )
            return $userfeed;
    else return false;
}

/**
 * Check if the current post is a Cowobo profile
 *
 * @param obj $currentcat
 * @return boolean
 * @todo Profile slug should be programmatically defined somewhere
 */
function is_cowobo_profile( $currentcat ) {
    if ( $currentcat->name == 'Profile' ) return true;
    else return false;
}

/**
 * Returns an array with the current category (obj) and the category id (str)
 *
 * @return arr  current category (obj) and category id (str)
 */
function cowobo_get_current_category() {
    if (is_home()) {
        $catid = 0;
        $currentcat = false;
    } elseif ($catid = get_query_var('cat')) {
        $currentcat = get_category($catid);
    } else {
        $cat = get_the_category($post->ID);
        $currentcat = $cat[0];
        $catid = $currentcat->term_id;
    }
    return array ('currentcat' => $currentcat, 'catid' => $catid );
}

// Add private tag to corresponding comment
add_action ('comment_post', 'cowobo_add_comment_meta', 1);
function cowobo_add_comment_meta($comment_id) {
	if(isset($_POST['privatemsg'])){
		$type = wp_filter_nohtml_kses($_POST['privatemsg']);
		add_comment_meta($comment_id, 'privatemsg', $type, false);
	}
}


// Add Settings Page to Reservations
add_action('admin_menu', 'template_add_page_fn');
function template_add_page_fn() {
	add_submenu_page('edit.php', 'Settings', 'Templates', 'manage_options', 'templates', 'template_page_fn');
}

add_action('admin_init', 'template_init_fn' );
function template_init_fn(){
	register_setting('template_options', 'template_options', 'template_options_validate' );
}

// Display the admin options page
function template_page_fn() {?>
	<h2>Template Settings</h2>
	<form method="post" action="options.php"><?php
	
	settings_fields('template_options');
	$settings = get_option('template_options');
	$types = get_categories(array('parent'=>0, 'exclude'=>get_cat_ID('Uncategorized'), 'hide_empty'=>false));
	
	//set default options
	if(empty($settings)):
	$settings[get_cat_ID('location')] = array(
		array('type' => 'title', 'label' =>'Name of Town or City', 'hint' => 'Check it does not exist on our site'),
		array('type' => 'coordinates', 'label' =>'Coordinates', 'hint' => 'Enter an address below and then'),
		array('type' => 'content', 'label' =>'Add a description', 'hint' => 'Keep it short and sweet'),
		array('type' => 'website', 'label' =>'Source', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
	);
	$settings[get_cat_ID('profile')] = array(
		array('type' => 'title', 'label' =>'Your Full Name', 'hint' => 'Keep it real',),
		array('type' => 'custom', 'label' =>'Looking For', 'hint' => 'ie Collaborators, Funding, etc',),
		array('type' => 'custom', 'label' =>'Experience', 'hint' => ''),
		array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.myblog.com'),	
		array('type' => 'content', 'label' =>'About You', 'hint' => 'Keep it short and sweet'),	
	);	
	$settings[get_cat_ID('forum')] = array(
		array('type' => 'title', 'label' =>'Short title of your question', 'hint' => 'Keep it short and sweet',),
		array('type' => 'content', 'label' =>'Elaborate question', 'hint' => 'Max 3000 characters'),
		array('type' => 'authors', 'label' =>'Authors'),
	);
	$settings[get_cat_ID('solution')] = array(
		array('type' => 'title', 'label' =>'Title', 'hint' => 'Keep it short and sweet',),
		array('type' => 'custom', 'label' =>'Status', 'hint' => 'ie Completed, Prototype, Under Construction'),
		array('type' => 'content', 'label' =>'Description', 'hint' => 'Max 3000 characters'),	
		array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
		array('type' => 'authors', 'label' =>'Authors'),
	);
	$settings[get_cat_ID('event')] = array(
		array('type' => 'title', 'label' =>'Short title of event', 'hint' => 'Keep it short and sweet',),
		array('type' => 'custom', 'label' =>'Start Date', 'hint' => 'ie 10am September 15th, 2012'),
		array('type' => 'custom', 'label' =>'End Date', 'hint' => 'ie 6apm September 18th, 2012'),
		array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.wikipedia.org/cowobo'),		
		array('type' => 'content', 'label' =>'Description of Event', 'hint' => 'Max 3000 characters'),	
		array('type' => 'authors', 'label' =>'Authors'),
	);
	$settings[get_cat_ID('job')] = array(
		array('type' => 'title', 'label' =>'Short title of your question', 'hint' => 'Keep it short and sweet',),
		array('type' => 'content', 'label' =>'Elaborate question', 'hint' => 'Max 3000 characters'),
		array('type' => 'website', 'label' =>'Website', 'hint' => 'ie http://www.wikipedia.org/cowobo'),		
		array('type' => 'authors', 'label' =>'Authors'),	
	);
	$settings[get_cat_ID('wiki')] = array(
		array('type' => 'title', 'label' =>'Short title of your wiki', 'hint' => 'Keep it short and sweet',),
		array('type' => 'content', 'label' =>'Text', 'hint' => 'Max 3000 characters'),	
		array('type' => 'website', 'label' =>'Source', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
		array('type' => 'authors', 'label' =>'Authors'),	
	);
	$settings[get_cat_ID('news')] = array(
		array('type' => 'title', 'label' =>'Short title of your wiki', 'hint' => 'Keep it short and sweet',),
		array('type' => 'content', 'label' =>'Text', 'hint' => 'Max 3000 characters'),	
		array('type' => 'website', 'label' =>'Source', 'hint' => 'ie http://www.wikipedia.org/cowobo'),
		array('type' => 'authors', 'label' =>'Authors'),
	);
	update_option('template_options', $settings);
	endif;
	
	foreach($types as $type):
		$template = $settings[$type->term_id];
		if(empty($template)) $template = array(array());?>
		<h3><?php echo $type->name?></h3>
		<table style="text-align:left">
		<tr><th>Label</th><th>Hint</th><th>Type</th><th>Rows</th></tr><?php
		foreach ($template as $i => $x):?>
		<tr>
			<td><input type="text" value="<?php echo $x['label'];?>" name="template_options[<?php echo $type->term_id;?>][<?php echo $i;?>][label]" size="60"/></td>
			<td><input type="text" value="<?php echo $x['hint'];?>" name="template_options[<?php echo $type->term_id;?>][<?php echo $i;?>][hint]" size="50"/></td>
			<td><select name="template_options[<?php echo $type->term_id;?>][<?php echo $i;?>][type]">
				<option></option>
				<option <?php if($x['type']=='title') _e('selected');?> value="title">Title</option>
				<option <?php if($x['type']=='content') _e('selected');?> value="content">Content</option>
				<option <?php if($x['type']=='coordinates') _e('selected');?> value="coordinates">Coordinates</option>
				<option <?php if($x['type']=='custom') _e('selected');?> value="custom">Custom Field</option>
				<option <?php if($x['type']=='website') _e('selected');?> value="website">Website</option>
				<option <?php if($x['type']=='authors') _e('selected');?> value="authors">Authors</option>
				</select>
			</td>
			<td><select name="template_options[<?php echo $type->term_id;?>][<?php echo $i;?>][rows]">
				<option></option>
				<option <?php if($x['rows']=='1') _e('selected');?> value="1">1</option>
				<option <?php if($x['rows']=='2') _e('selected');?> value="2">2</option>
				<option <?php if($x['rows']=='3') _e('selected');?> value="content">3</option>
				<option <?php if($x['rows']=='5') _e('selected');?> value="5">5</option>
				<option <?php if($x['rows']=='10') _e('selected');?> value="10">10</option>
				</select>
			</td>
			<td><input type="button" value="x" class="deleterow"></td>
		</tr><?php
		endforeach;?>
		<tr><td><input type="button" value="Add Another Section" class="clonerow"/></td></tr>
		</table><?php 
	endforeach;?>
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</form>
	
	<script>
		jQuery('.clonerow').live('click', function() {
			var row = jQuery(this).parents('tr').prev();
			var sections = row.html().split('][');
			var newnum = parseFloat(sections[1])+1;
			sections[1] = newnum;
			sections[3] = newnum;
			sections[5] = newnum;
			sections[7] = newnum;
			row.after('<tr>'+sections.join('][')+'</tr>');
		});
		jQuery('.deleterow').live('click', function() {
			if(jQuery(this).parents('tr').siblings().length<3) {
				alert('You must keep at least one section');
			} else {
				jQuery(this).parents('tr').remove();
			}
		});
	</script><?php	
}

// Validate user data for some/all of your input fields
function template_options_validate($input) {
	return $input;
}
