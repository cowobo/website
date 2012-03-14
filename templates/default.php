<?php
//load form for post
if($newpost):?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
		<h1>Choose type of post:</h1><?php
		wp_dropdown_categories();?>
		</div>
	</div>
	<input type="text" class="newtitle" value="Click here to add a title.." />
	<div class="newlocation relocate">
		<b>Click here to add a location on the map (optional) </b>
		<ul class="coordinates horlist"></ul>
	</div>
	<textarea name="newcontent" class="newcontent">Click here to add text to your post</textarea><?php
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
	<a class="title" href="<?php the_permalink();?>"><?php the_title();?></a><br/>
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
	// Include Related Feeds
	if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')): include(TEMPLATEPATH.'/templates/editfeeds.php'); endif;
	// Include Authors
	if(file_exists(TEMPLATEPATH.'/templates/editauthors.php')): include(TEMPLATEPATH.'/templates/editauthors.php'); endif;
	// Include Related Posts
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')): include(TEMPLATEPATH.'/templates/editposts.php'); endif;
else:?>
	<h3 class="loading">Loading related posts</h3><?php
endif;?>
