<div class="gallery<?php if($author):?> editable<?php endif;?>">
	<div class="topshadow">
	<h1><?php echo $postcat->name;?><div class="prev">< </div><div class="next"> ></div></h1>
	</div><?php
	if($newpost):?>
		<div class="slide loading">Click here to add a profile image</div><?php
	elseif($ajax):
		loadgallery_callback();
	else:?>
		<div class="slide loading"><span class="loadicon">Loading profile..</span></div><?php
	endif;?>
</div><?php

if($newpost or $author && $ajax):?>
<div class="postform <?php if (!$newpost) echo 'hide';?>">
	<h3>Your Full Name:</h3> Please keep it real..
	<input tabindex="1" type="text" name="edittitle" class="new edittitle" value="<?php the_title();?>" />
	<h3>Work Experience:</h3> Projects, Jobs, etc
	<input tabindex="2" type="text" name="workexperience" class="new workexperience" value="<?php echo get_post_meta(get_the_ID(), 'workexperience', true);?>" />
	<h3>Looking for:</h3> Coders, Fundings, etc
	<input tabindex="3" type="text" name="searchingfor" class="new searchingfor" value="<?php echo get_post_meta(get_the_ID(), 'searchingfor', true);?>" />
	<h3>More about you:</h3> Maximum 1000 characters
	<textarea tabindex="4" name="editcontent" rows="5" class="new richtext"><?php the_content();?></textarea>
</div><?php
endif;

if(!$newpost):?>
<div class="postbox"><?php
	if($author && $ajax):?><div class="editpost right button">+ Edit Post</div><?php endif;?>
	<div class="title"><span class="postrss"></span><?php if($ajax) the_title(); else echo $post->post_title;?></div>
	<b>Date Joined: </b><?php
	if($datejoined = get_post_meta(get_the_ID(), 'datejoined', true))
	echo $datejoined; else echo 'not specified yet';?>
	<br/><b>Looking for: </b><?php 			
	if($lookingfor = get_post_meta(get_the_ID(), 'lookingfor', true))
	echo $lookingfor; else echo 'not specified yet';?>
	<br/><b>Experience:</b><br/><?php
	the_content();?>
</div><?php
endif;

if($ajax):
	// Include Comments
	$withcomments = true; comments_template();
	// Include Tags
	if(file_exists(TEMPLATEPATH.'/templates/edittags.php')): include(TEMPLATEPATH.'/templates/edittags.php'); endif;
	// Include Linked Posts
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')): include(TEMPLATEPATH.'/templates/editposts.php'); endif;
endif;?>