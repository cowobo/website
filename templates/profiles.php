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
	// Include Related Feeds
	if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')) include(TEMPLATEPATH.'/templates/editfeeds.php');
	// Include Related Locations
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')) include(TEMPLATEPATH.'/templates/editposts.php');
	// Include Related Feeds
	if(file_exists(TEMPLATEPATH.'/templates/editsubscriptions.php')) include(TEMPLATEPATH.'/templates/editsubscriptions.php');
	// Include Related Comments
	$withcomments = true;comments_template();
else:?>
	<span class="loading">Loading related posts</span><?php
endif;?>
		