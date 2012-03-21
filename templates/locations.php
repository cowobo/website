<?php
if($newpost):?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
		<h1><?php echo $postcat->name;?></h1>
		</div>
		<div class="slide loading">Click here to add images</div>
	</div>
	<h3>Name of Location</h3>
	<input type="text" name="edittitle" class="new edittitle" value="" />
	<h3>Coordinates</h3> Search for address or <span class="relocate">click here</span> to zoom to a location
	<input tabindex="2" type="text" name="address" class="new address" value="" />
	<h3>Description:</h3> Maximum 1000 characters
	<textarea name="editcontent" rows="5" class="new editcontent"></textarea><?php
else:?>
	<div class="title"><?php if($ajax) the_title(); else echo $post->post_title;?><span class="rss icon"></span></div>
	<b>Coordinates:</b>
	<ul class="coordinates horlist">
		<li id="<?php echo $coordinates;?>"><?php 
		if(!empty($coordinates)) echo $coordinates; else echo 'Planet Earth';
		if($author):?>
			<span class="relocate button"> +Edit</span><?php
		endif;?>
		</li>
	</ul><br/><?php 
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