<div class="large" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content">
		<div class="gallery editable">
			<div class="slide"><div class="loadinggallery">Loading images</div></div>
		</div>
		<span class="title"><?php the_title();?></span><br/>
		<b>Coders: </b><a href=""><?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?></a><?php
		if(current_user_can('edit_posts')):
			$coordinates = get_post_meta($post->ID, 'coordinates', true);?>
			<br/><b>Coordinates: </b>
				<ul class="coordinates horlist">
					<li id="<?php echo $coordinates;?>"><?php echo $coordinates;?><span> (x)</span></li>
				</ul><span class="editlocation"> Edit</span><?php
		endif;?><br/><?php
		the_content();?>
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