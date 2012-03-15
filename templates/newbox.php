<div class="large" id="new">
	<div class="holder">
	<div class="content"><?php
	$posttype = $currenttype->slug;
	$newpost = true;
	if(is_home()):?>
		<div class="gallery<?php if($author):?> editable<?php endif;?>">
			<div class="topshadow">
			<h1>Choose type of post:</h1><?php
			wp_dropdown_categories();?>
			</div>
		</div>
	<span class="title">Short title of your post:</span>
	<input tabindex="1" type="text" class="new edittitle" value="" />
	<h3>Location (optional):</h3> Enter address or <span class="relocate">click here</span> to use our map
	<input tabindex="2" type="text" class="new editaddress" value="" />
	<ul class="coordinates horlist"></ul>
	<h3>Text:</h3>
	<textarea tabindex="3" name="newcontent" rows="5" class="new editcontent"></textarea><?php
	elseif (file_exists(TEMPLATEPATH.'/templates/' . $posttype . '.php') ):
		include(TEMPLATEPATH.'/templates/' . $posttype . '.php');
	else:
		include(TEMPLATEPATH.'/templates/default.php');
	endif;
	$newpost = false;?>
	<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	</div>
	<div class="arrow">
		<span class="save button" id="save-<?php echo $post->ID;?>">Save</span>
		<span class="delete button">Delete</span>      
	</div>
</div>