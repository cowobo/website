<?php
//load form for post
if($newpost):?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
		<h1><?php echo $postcat->name;?></h1>
		</div>
		<div class="slide addimage">Click here to add images</div>
	</div>
	<h3>Title:</h3> Keep it short and sweet
	<input tabindex="1" type="text" name="edittitle" class="new edittitle" value="" />
	<h3>Address:</h3> Enter City,Country or <span class="relocate">click here</span> to zoom to a location
	<ul class="coordinates new">
		<li id="geocode"><input tabindex="2" type="text" name="address" class="address" value="" /></li>
	</ul>
	
	<h3>Text:</h3> Maximum 1000 characters
	<textarea tabindex="3" name="editcontent" rows="5" class="new editcontent"></textarea><?php
//load content of post
else:?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
			<h1><?php echo $postcat->name;?><div class="prev">< </div><div class="next"> ></div></h1>
		</div><?php
		if($ajax):
			loadgallery_callback();
			if(!empty($pano_id)):?><div class="streetview" id="<?php echo $pano_id;?>">Streetview!</div><?php endif;
		else:?>
			<div class="slide"><div class="loadinggallery">Loading images</div></div><?php
		endif;?>
	</div>
	<div class="title"><?php if($ajax) the_title(); else echo $post->post_title;?></div>
	<b>Location: </b>
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
