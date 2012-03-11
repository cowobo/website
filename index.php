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
	endforeach;?>
</div>

</div><!-- end of #page defined in header-->
<?php

//Lightboxes
switch ( $social->state ) :
    case 1:
        /**
         * @todo edit joinbox
         */
        include(TEMPLATEPATH.'/templates/joinbox.php');
        break;
    case 4:
        include(TEMPLATEPATH.'/templates/newbox.php');
        if ( $profile_in_feed ) break;
    case 2:
    case 3:
        /**
         * @todo something wrong here. postholder doesn't load profile post. perhaps due to draft status?
         */
        console_log ( "Postid: $postid; Profileid: {$social->profile_id}");
        if ($postid != $social->profile_id ) :
            $post = get_post ( $social->profile_id );
            setup_postdata($post);
            $wp_query->in_the_loop = true;
            console_log ( "Postid profile: {$post->ID}");
            include(TEMPLATEPATH.'/templates/postbox.php');
            break;
        endif;
endswitch;

foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true;
	include(TEMPLATEPATH.'/templates/postbox.php');
endforeach;

get_footer();?>
