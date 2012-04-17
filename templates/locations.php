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
		
	<h3>Coordinates:</h3> Enter an address below and then <span class="relocate">click here to geocode it</span> 
	<input type="text" class="searchform new latlng" value=""/>
	
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
		<div class="slide loading"><span class="loadicon">Loading post..</span></div><?php
		endif;?>
	</div>
	<div class="title"><span class="postrss"></span><?php if($ajax) the_title(); else echo $post->post_title;?></div><?php
	if($author):?>
	<div class="container" style="margin:0 10px 0 0;">
		<b>Coordinates:</b> <?php echo $coordinates;?>
		<div class="edit right button">+ Edit</div>
		<div class="selectbox relocatebox" id="new-<?php echo $postcat->term_id;?>">
			Enter address below and then <span class="relocate">click here to geocode it</span><br/>
			<input type="text" class="searchform new latlng" value="<?php echo $coordinates;?>"/>
		</div>
	</div><br/><?php
	endif;
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