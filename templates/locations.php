<?php
if($newpost):?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
		<h1><?php echo $postcat->name;?></h1>
		</div>
		<div class="slide loading">Click here to add images</div>
	</div>
	<h3>Name of Town or City:</h3> Check it does not exist on our site
	<input type="text" name="edittitle" class="new edittitle" value="" />
	
	<h3>Coordinates:</h3> Enter address below and then <span class="relocate">click here</span>
	<input type="text" class="searchform new" value=""/>
	<div class="latlng" id="<?php echo $coordinates;?>"><?php echo $coordinates;?></div>
	
	<h3>Description:</h3> Maximum 1000 characters
	<textarea name="editcontent" rows="5" class="new editcontent"></textarea><?php
else:?>
	<div class="gallery<?php if($author):?> editable<?php endif;?>">
		<div class="topshadow">
			<h1><?php echo $postcat->name;?><div class="prev">< </div><div class="next"> ></div></h1>
		</div><?php
		if($ajax):
			loadgallery_callback();
			if(!empty($pano_id)):?><div class="streetview" id="<?php echo $pano_id;?>">Streetview!</div><?php endif;
		else:?>
		<div class="slide loading">Loading post..</div><?php
		endif;?>
	</div>
	<div class="title"><?php if($ajax) the_title(); else echo $post->post_title;?><span class="rss icon"></span></div>
	<div class="container" style="margin:0;">
		<b>Coordinates:</b> <span class="latlng" id="<?php echo $coordinates;?>"><?php echo $coordinates;?></span>
		<div class="edit button">+ Edit</div>
		<div class="selectbox" id="new-<?php echo $postcat->term_id;?>">
			Search for address or <span class="relocate">click here</span> to zoom to a location
			<input type="text" class="searchform" value=""/>
			<span class="searchbutton address"></span>
			<br/>
		</div>
	</div><br/><?php 
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