<?php
if(current_user_can('edit_posts')):?>
<b>Coordinates: </b>
	<ul class="coordinates horlist">
	<li id="<?php echo $coordinates;?>"><?php echo $coordinates;?><span> (x)</span></li>
	</ul><span class="relocate button"> Edit</span><br/><?php
endif;?>
<b>Date Joined: </b><?php 
	if(function_exists('editable_post_meta')):
		echo editable_post_meta(get_the_ID(), 'datejoined', 'input');
	else:
		echo get_post_meta(get_the_ID(), 'datejoined', true);
	endif;?><br/>
<b>Looking for: </b><?php 			
	if(function_exists('editable_post_meta')):
		echo editable_post_meta(get_the_ID(), 'lookingfor', 'input');
	else:
		echo get_post_meta(get_the_ID(), 'lookingfor', true);
	endif;?><br/>
<b>Work Experience:</b><br/><?php

the_content();
			
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/relatedfeeds.php')) include(TEMPLATEPATH.'/templates/relatedfeeds.php');
// Include Related Posts
if(file_exists(TEMPLATEPATH.'/templates/relatedposts.php')) include(TEMPLATEPATH.'/templates/relatedposts.php');
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/subscribedfeeds.php')) include(TEMPLATEPATH.'/templates/subscribedfeeds.php');
// Include Related Comments
$withcomments = true;
comments_template();
?>
		