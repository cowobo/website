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
<b>Experience:</b><br/><?php

the_content();
			
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')) include(TEMPLATEPATH.'/templates/editfeeds.php');
// Include Related Locations
if(file_exists(TEMPLATEPATH.'/templates/editlocations.php')) include(TEMPLATEPATH.'/templates/editlocations.php');
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/editsubscriptions.php')) include(TEMPLATEPATH.'/templates/editsubscriptions.php');
// Include Related Comments
$withcomments = true;
comments_template();
?>
		