<div class="large" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content">
		<div class="gallery">
			<div class="slide"><div class="loadinggallery">Loading images</div></div>
		</div>
		<span class="title"><?php echo $post->post_title;?></span><br/><?php
		//prevent fee editor
		echo get_the_content();?>
		</div>
		<div class="cowobo_social_share"></div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"><?php
		if(!current_user_can('edit_posts')):?>
			<span class="navarrow left"></span>
			<span class='cowobo_social_like button'>Share</span>
			<span class="navarrow right"></span><?php
		else:?>
			<span class="save button" id="save-<?php echo $post->ID;?>">Save</span>
			<span class='cowobo_social_like button'>Share</span>
			<span class="delete button">Delete</span><?php
		endif;?>
	</div>
	<div class="shadowclick"></div>
</div>