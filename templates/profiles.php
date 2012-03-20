<?php
//load form for post
if($newpost):?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
		<h1>Profiles</h1>
		</div>
		<div class="slide addimage">Click here to add images</div>
	</div>
	<h3>Your Full Name:</h3> Please keep it real..
	<input tabindex="1" type="text" name="edittitle" class="new edittitle" value="" />
	<h3>Location (optional):</h3> Enter address or <span class="relocate">click here</span> to use our map
	<input tabindex="2" type="text" name="address" class="new address" value="" />
	<ul class="coordinates horlist"></ul>
	<h3>Work Experience:</h3> Projects, Jobs, etc
	<input tabindex="2" type="text" name="workexperience" class="new workexperience" value="" />
	<h3>Looking for:</h3> Coders, Fundings, etc
	<input tabindex="3" type="text" name="searchingfor" class="new searchingfor" value="" />
	<h3>More about you:</h3> Maximum 1000 characters
	<textarea tabindex="4" name="editcontent" rows="5" class="new editcontent"></textarea>
	<?php
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
	<div class="title"><?php if($ajax) the_title(); else echo $post->post_title;?><span class="rss icon"></span></div>
	<b>Location: </b>
	<ul class="coordinates horlist">
		<li id="<?php echo $coordinates;?>"><?php 
		if(!empty($coordinates)) echo $coordinates; else echo 'Planet Earth';
		if($author):?>
			<span class="edit relocate button"> +Edit</span><?php
		endif;?>
		</li>
	</ul><br/>
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
endif;

if($ajax):
	// Include Comments
	$withcomments = true; comments_template();
	// Include Tags
	if(file_exists(TEMPLATEPATH.'/templates/edittags.php')): include(TEMPLATEPATH.'/templates/edittags.php'); endif;
	// Include Linked Posts
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')): include(TEMPLATEPATH.'/templates/editposts.php'); endif;
endif;?>
		