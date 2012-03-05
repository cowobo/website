<?php 
get_header();?>

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
include(TEMPLATEPATH.'/templates/newbox.php');
foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true;
	//if on a single page load the main post and hide the rest
	if($postid == $post->ID):
		include(TEMPLATEPATH.'/templates/postbox.php');
	else: 	
		include(TEMPLATEPATH.'/templates/postholder.php');
	endif;
endforeach;

get_footer();?>