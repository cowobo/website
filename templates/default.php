<div class="gallery<?php if($author):?> editable<?php endif;?>">
	<div class="topshadow">
	<h1><?php echo $postcat->name;?><div class="prev">< </div><div class="next"> ></div></h1>
	</div><?php
	if($newpost):?>
		<div class="slide loading">Click here to add images</div><?php
	elseif($ajax):
		loadgallery_callback();
		if(!empty($pano_id)):?><div class="streetview" id="<?php echo $pano_id;?>">Streetview!</div><?php endif;
	else:?>
		<div class="slide loading"><span class="loadicon">Loading post..</span></div><?php
	endif;?>
</div><?php

if($newpost or $author && $ajax):?>
<div class="postform <?php if (!$newpost) echo 'hide';?>">
	<h3>Title:</h3> Keep it short and sweet
	<input tabindex="1" type="text" name="edittitle" class="new edittitle" value="<?php the_title();?>" />
	<h3>Text:</h3> Maximum 1000 characters
	<textarea name="editcontent" rows="5" class="new richtext"><?php the_content();?></textarea>
	<h3>Source:</h3> ie: http://www.wikipedia.org/...
	<input tabindex="1" type="text" name="source" class="new source" value="<?php echo get_post_meta(get_the_ID(), 'source', true);?>" />
</div><?php
endif;

if(!$newpost):?>
<div class="postbox"><?php
	if($author && $ajax):?><div class="editpost right button">+ Edit Post</div><?php endif;?>
	<div class="title"><span class="postrss"></span><?php the_title();?></div><?php
	the_content();
	if($source = get_post_meta(get_the_ID(), 'source', true)):?>
		<br/>Via: <a href="<?php echo "$source";?>"><?php echo $source;?></a><?php
	endif;?>
</div><?php
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
