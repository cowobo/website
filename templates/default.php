<b>Coders: </b><a href=""><?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?></a><?php
if(current_user_can('edit_posts')):?>
	<br/><b>Coordinates: </b>
		<ul class="coordinates horlist">
		<li id="<?php echo $coordinates;?>"><?php echo $coordinates;?><span> (x)</span></li>
		</ul><span class="relocate button"> Edit</span><?php
endif;?><br/><?php

the_content();

// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/relatedfeeds.php')) include(TEMPLATEPATH.'/templates/relatedfeeds.php');
// Include Related Posts
if(file_exists(TEMPLATEPATH.'/templates/relatedposts.php')) include(TEMPLATEPATH.'/templates/relatedposts.php');	
// Include Related Comments
$withcomments = true;
comments_template();
?>
