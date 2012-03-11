<?php
get_header();
global $social; $profile_in_feed = false; ?>

<div id="scroller" style="<?php if(is_single()) echo 'display:none';?>"><?php
	include(TEMPLATEPATH.'/templates/newthumb.php');
	foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true; $counter++;
        // Check if the current user's profile is in the feed already
        if ( $social->state == 4 && $post->ID == $social->profile_id ) $profile_in_feed = true;
		$typepost = cwob_get_category($post->ID);
		include(TEMPLATEPATH.'/templates/postthumb.php');
		$postids[] = $post->ID; //to determine whether or not to load the profile post
	endforeach;?>
</div>

</div><!-- end of #page defined in header-->
<?php

//Lightboxes
include(TEMPLATEPATH.'/templates/newbox.php');

if ($state > 1 && !in_array($social->profile_id, $postids)) :
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

foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true;
	include(TEMPLATEPATH.'/templates/postbox.php');
endforeach;

get_footer();?>
