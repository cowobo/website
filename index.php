<?php
get_header();
global $social; $state = $social->state; ?>

<div id="scroller" style="<?php if(is_single()) echo 'display:none';?>"><?php
	include(TEMPLATEPATH.'/templates/newthumb.php');
	foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true; $counter++;
		$typepost = cwob_get_category($post->ID);
		include(TEMPLATEPATH.'/templates/postthumb.php');
	endforeach;?>
</div>

</div><!-- end of #page defined in header-->
<?php

//Lightboxes
switch ( $state ) :
    case 1:
        /**
         * @todo edit joinbox
         */
        include(TEMPLATEPATH.'/templates/joinbox.php');
        break;
    case 4:
        include(TEMPLATEPATH.'/templates/newbox.php');
    case 2:
    case 3:
        /**
         * @todo something wrong here. postholder doesn't load profile post. perhaps due to draft status?
         */
        if ( $postid != $social->profile_id ) :

            console_log ( $social->profile_id );
            $post = get_post ( $social->profile_id );
            console_log ( $post->ID );
            setup_postdata($post);
            $wp_query->in_the_loop = true;
            console_log ( $post->ID );
            include(TEMPLATEPATH.'/templates/postholder.php');
            break;
        endif;
    endswitch;
foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true;
	//if on a single page load the main post and hide the rest
	if($postid == $post->ID):
		include(TEMPLATEPATH.'/templates/postbox.php');
	else:
		include(TEMPLATEPATH.'/templates/postholder.php');
	endif;
endforeach;

get_footer();?>
