<?php
if($newpost):?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
		<h1><?php echo $postcat->name;?></h1>
		</div>
		<div class="slide loading">Add images or screenshots..</div>
	</div>
	<h3>Short title of your question:</h3>
	<input type="text" name="edittitle" class="new edittitle" value="" />
	<h3>Elaborate question:</h3>
	<textarea name="editcontent" rows="5" class="new editcontent"></textarea><?php
else:?>
	<div class="title"><span class="postrss"></span><?php if($ajax) the_title(); else echo $post->post_title;?></div><?php
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