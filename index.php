<?php
get_header();
global $social;
$postids = array();

//load thumbs ?>
<div id="scroller" style="<?php if(is_single()) echo 'display:none';?>"><?php
	include(TEMPLATEPATH.'/templates/newthumb.php');
	foreach($newposts as $post): $counter++;
		setup_postdata($post);
		$wp_query->in_the_loop = true;
		$typepost = cwob_get_category($post->ID);
		include(TEMPLATEPATH.'/templates/postthumb.php');
		$postids[] = $post->ID; //to determine whether or not to load the profile post
	endforeach;?>
</div>

</div><!-- end of #page defined in header-->
<?php

//load rrs and new post box
include( TEMPLATEPATH . '/templates/newbox.php');
include(TEMPLATEPATH.'/templates/rssbox.php');

//load profile box or join box
if ($social->state > 1 && !in_array( $social->profile_id, $postids ) ) :
	$post = get_post ( $social->profile_id );
	setup_postdata($post);
	$wp_query->in_the_loop = true;
	include(TEMPLATEPATH.'/templates/postbox.php');
else:
	$joinpost = get_posts(array('name' => 'reasons-for-joining'));
	foreach ($joinpost as $post):
		setup_postdata($post);
		$wp_query->in_the_loop = true;
        include(TEMPLATEPATH.'/templates/joinbox.php');
	endforeach;
endif;

//load posts in current category
foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true;
	if($currentid == $post->ID) $ajax = true; else $ajax = false;
	include(TEMPLATEPATH.'/templates/postbox.php');
endforeach;

get_footer();