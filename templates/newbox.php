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
			<input type="text" class="newtitle" value="Click here to add a title.." />
			<div class="newlocation relocate">
				<b>Click here to add a location on the map (optional) </b>
				<ul class="coordinates horlist"></ul>
			</div>
			<textarea name="newcontent" class="newcontent">Click here to add text to your post</textarea><?php
		elseif (file_exists(TEMPLATEPATH.'/templates/' . $posttype . '.php') ):
			include(TEMPLATEPATH.'/templates/' . $posttype . '.php');
		else:
			include(TEMPLATEPATH.'/templates/default.php');
		endif;
		$newpost = false;?>
		<div class="scrolltrack"><div class="slider"></div></div>
		</div>
	</div>
	<div class="arrow"></div>
</div>