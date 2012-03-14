<div class="gallery<?php if($author):?> editable<?php endif;?>">
	<div class="topshadow">
		<h1><?php echo $postcat->name;?><div class="prev">< </div><div class="next"> ></div></h1>
	</div><?php 
	if($ajax):
		loadgallery_callback();
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

if($ajax):		
	// Include Comments
	$withcomments = true; comments_template();
	// Include Related Feeds
	if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')): include(TEMPLATEPATH.'/templates/editfeeds.php'); endif;
	// Include Related Posts
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')): include(TEMPLATEPATH.'/templates/editposts.php'); endif;
else:?>
	<h3 class="loading">Loading related posts</h3><?php
endif;?>
		