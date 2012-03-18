<?php
if($newpost):?>
	<div class="title">Short title of your question:</div>
	<input type="text" class="new edittitle" value="" />
	<h3>Elaborate question:</h3>
	<textarea name="newcontent" rows="5" class="new editcontent"></textarea><?php
else:?>
	<div class="title"><?php if($ajax) the_title(); else echo $post->post_title;?></div><?php
	the_content();
endif;

if($ajax):
	// Include Comments
	$withcomments = true; comments_template();
	// Include Tags
	if(file_exists(TEMPLATEPATH.'/templates/edittags.php')): include(TEMPLATEPATH.'/templates/edittags.php'); endif;
	// Include Authors
	if(file_exists(TEMPLATEPATH.'/templates/editauthors.php')): include(TEMPLATEPATH.'/templates/editauthors.php'); endif;
	// Include Linked Posts
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')): include(TEMPLATEPATH.'/templates/editposts.php'); endif;
endif;?>