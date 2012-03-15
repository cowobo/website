<?php
if(!is_home()):
$posttype = $currenttype->slug;?>

<div class="large" id="new">
	<div class="holder">
	<div class="content"><?php
		$newpost = true;
		if (file_exists(TEMPLATEPATH.'/templates/' . $posttype . '.php') ):
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
</div><?php 

else:?>
<div class="large" id="selecttype">
	<div class="holder">
	<div class="content">
		<div class="gallery">
			<div class="topshadow">
				<h1>Add New Post</h1>
			</div>
		</div>
	<span class="title">Choose the type of post</span><?php
		wp_dropdown_categories(array(
		'depth'=> 1, 
		'class' =>'new choosetype', 
		'hide_empty'=> 0, 
		'hierarchical' => 1, 
		'exclude'=>get_cat_ID('Uncategorized'),
		'show_option_none' =>'',
		));?>
	<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	</div>
	<div class="arrow">
		<span class="save button" id="save-<?php echo $post->ID;?>">Save</span>
		<span class="delete button">Delete</span>      
	</div>
</div><?php

endif;?>
