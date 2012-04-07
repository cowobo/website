<?php
//load form for post
if($newpost):?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
		<h1><?php echo $postcat->name;?></h1>
		</div>
		<div class="slide loading">Click here to add images</div>
	</div>
	<h3>Title:</h3> Keep it short and sweet
	<input tabindex="1" type="text" name="edittitle" class="new edittitle" value="" />
	<h3>Text:</h3> Maximum 1000 characters
	<textarea tabindex="3" name="editcontent" rows="5" class="new editcontent"></textarea>
	<h3>Source:</h3> ie: http://www.wikipedia.org/...
	<input tabindex="1" type="text" name="source" class="new source" value="" /><?php
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
		<div class="slide loading"><span class="loadicon">Loading post..</span></div><?php
		endif;?>
	</div>
	<div class="title"><span class="postrss"><?php if($ajax) the_title(); else echo $post->post_title;?></span></div><?php
	the_content();
	if($source = get_post_meta(get_the_ID(), 'source', false)):?>
		<br/><a href="<?php echo "$source";?>"><?php echo editable_post_meta(get_the_ID(), 'source', 'input')?></a><?php
	endif;
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
