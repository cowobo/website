<b>Coordinates: </b>
	<ul class="coordinates horlist">
		<li id="<?php echo $coordinates;?>"><?php echo $coordinates;
		if($author):?>
			<span class="relocate button"> Edit</span><?php
		endif;?>
		</li>
	</ul><?php 
the_content();
			
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')) include(TEMPLATEPATH.'/templates/editfeeds.php');
// Include Related Locations
if(file_exists(TEMPLATEPATH.'/templates/editposts.php')) include(TEMPLATEPATH.'/templates/editposts.php');
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/editsubscriptions.php')) include(TEMPLATEPATH.'/templates/editsubscriptions.php');
// Include Related Comments
$withcomments = true;
comments_template();
?>
		