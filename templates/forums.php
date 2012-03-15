<?php
if($newpost):?>
	<span class="title">Short title of your question:</span>
	<input type="text" class="new edittitle" value="" />
	<h3>Elaborate question:</h3>
	<textarea name="newcontent" rows="5" class="new editcontent"></textarea><?php
else:?>
	<a class="title" href="<?php the_permalink();?>"><?php the_title();?></a><br/><?php
	the_content();
endif;

if($ajax):
	// Include Comments
	$withcomments = true; comments_template();
	// Include Related Feeds
	if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')): include(TEMPLATEPATH.'/templates/editfeeds.php'); endif;
	// Include Authors
	if(file_exists(TEMPLATEPATH.'/templates/editauthors.php')): include(TEMPLATEPATH.'/templates/editauthors.php'); endif;
	// Include Related Posts
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')): include(TEMPLATEPATH.'/templates/editposts.php'); endif;
else:?>
	<h3 class="loading">Loading related posts</h3><?php
endif;?>