<div class="large" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content">
		<div class="gallery">
			<div class="slide"><div class="loadinggallery">Loading images</div></div>
		</div>
		<span class="title"><?php echo $post->post_title;?></span><br/><?php
		//prevent fee editor
		the_content();?>
		</div>
		<div class="cowobo_social_share"></div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"><?php
		if($author):?>
			<span class="save button" id="save-<?php echo $post->ID;?>">Save</span>
			<span class='cowobo_social_like button'>Share</span>
			<span class="delete button">Delete</span><?php
		else:
			$wp_query->is_single = true;
			$prev = get_adjacent_post(true,'',false); 
			$next = get_adjacent_post(true,'',true);?>
			<span class="<?php if(!empty($prev)) echo 'lastpost';?> button" id="last-<?php echo $prev->ID; ?>">Last</span>
			<span class="cowobo_social_like button">Share</span>
			<span class="<?php if(!empty($next)) echo 'nextpost';?> button" id="next-<?php echo $next->ID;?>">Next</span><?php
		endif;?>
	</div>
	<div class="shadowclick"></div>
</div>