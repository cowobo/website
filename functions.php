<?php
// Libraries
include_once('lib/class-cowobo-feed.php');
include_once('lib/class-cowobo-social.php');
include_once('lib/class-related-posts.php');

global $social;
$social = new Cowobo_Social;

// Session
if (!session_id())
    session_start();

// add styles to admin
add_action('admin_head', 'myadminstyles');
function myadminstyles() {?>
	<style>
	.mapholder{position:relative; margin-top:5px;}
	.map {width:100%; height:150px; overflow:hidden; cursor:pointer;}
	.marker {position:absolute; top:50%; left:50%; width:20px; height:20px; margin:-10px 0 0 -10px; background:url(<?php bloginfo('template_url');?>/images/location.png); z-index:100}
	.thumb {width:80px; height:55px; padding:5px;}
	</style><?php
}

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

function loadlightbox_callback(){
	global $wp_query;
	$wp_query->is_single = true;
	$postid = $_POST["postid"];

	if($postid == 'new'):
		$catid = $_POST["currentcat"];
		$current_user = wp_get_current_user();
		$post_id = wp_insert_post( array(
			'post_status' => 'auto-draft',
			'post_title' => 'New Draft',
			'post_category' => array($catid),
			'post_author' => $current_user->ID,
		));
		$loadpost = query_posts(array('post_status'=>'auto-draft', 'posts_per_page'=>1));
		$newpost = true;
	else:
		$loadpost = query_posts(array('p'=>$postid));
		$newpost = false;
	endif;
	if (class_exists('FEE_Core')) FEE_Core::add_filters();
	foreach($loadpost as $post): setup_postdata($post); the_post(); $ajax = true;?>
	<div><?php include(TEMPLATEPATH.'/templates/postbox.php');?></div><?php
	endforeach;
	wp_reset_query();
	die;
}

function loadgallery_callback(){
	if($_POST['postid'] != 'new'):
		$images = get_children(array('post_parent' => $_POST['postid'], 'numberposts' => -1, 'post_mime_type' =>'image', 'order'=>'ASC'));
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
	global $wpdb; global $social;

	// first update the main post attributes
	$postid = $_POST["postid"];
	$postdata = array('ID' => $postid, 'post_status' => 'publish','post_category' => explode(',', $_POST["tags"]));
	if($title = $_POST["edittitle"]) $postdata['post_title'] = $title;
	if($content = $_POST["editcontent"]) $postdata['post_content'] = $content;
	wp_update_post($postdata);

	//then update related posts
	$postclass  = new Cowobo_Related_Posts();
	$postclass->cwob_delete_relationships($postid);

	//then add all the custom fields and make sure the author is listed
	delete_post_meta($postid, 'author');
	delete_post_meta($postid, 'coordinates');
	foreach ($_POST as $key => $value) :
		if($value != ''):
			delete_post_meta($postid, $key);
			if($key == 'author'):
				$authors = explode(',', $value);
				foreach ($authors as $author):
					add_post_meta($postid, 'author', $author);
					$query = "INSERT INTO ".$wpdb->prefix."post_relationships VALUES($postid, $author)";
					$result = $wpdb->query($query);
				endforeach;
			elseif($key == 'posts'):
				$relatedpostids = explode(',' , $value);
				foreach($relatedpostids as $relatedpostid):
					$relatedpostid = (int) $relatedpostid;
					$type = cwob_get_category($relatedpostid);
					if($type->slug == "locations"):
						$coordinates = get_post_meta($relatedpostid, 'coordinates', true);
						add_post_meta($postid, 'coordinates', $coordinates);
					endif;
					$query = "INSERT INTO ".$wpdb->prefix."post_relationships VALUES($postid, $relatedpostid)";
					$result = $wpdb->query($query);
				endforeach;
			else:
				update_post_meta($postid, $key, $value);
			endif;
		endif;
	endforeach;
	//in case original author was deleted restore it here
	update_post_meta($postid, 'author', $social->profile_id);
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

function cowobo_pagination($pages = '', $range = 2){
     $showitems = ($range * 2)+1;
     global $paged;
     if(empty($paged)) $paged = 1;
     if($pages == ''){
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages) $pages = 1;
     }
     if(1 != $pages){
         for ($i=1; $i <= $pages; $i++){
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
                 echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
         }
         echo "<a href='".get_pagenum_link($paged + 1)."'>Next</a>";
     }
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
    if(isset($_GET['user'])) $pagetitle = "Favourite Feed";
    elseif(is_home()) $pagetitle= "<b>CODERS</b> WITHOUT <b>BORDERS</b>";
    elseif(is_search()) $pagetitle = "<b>Search Results</b>";
    elseif(is_404()) $pagetitle = "<b>Post not found</b>..is it one of these?";
    else $pagetitle = '<b>'.$currentcat->name.'</b>';

    return $pagetitle;
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