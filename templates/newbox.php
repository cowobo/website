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
			<input type="text" class="new edittitle" value="" />
			<b>Location (optional):</b> Click box to zoom to a spot on the map
			<div class="new relocate">
				<ul class="coordinates horlist"></ul>
			</div>
			<b>Add or paste in text of your post:</b>
			<textarea name="newcontent" class="newcontent"></textarea><?php
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