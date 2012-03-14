<div class="medium" id="medium-<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content">
			<div class="image">
				<div class="topshadow">
					<h2><?php echo $typepost->name;?></h2>
					<div class="data"><?php if(is_sticky()) echo 'Just now'; else echo time_passed(strtotime(get_the_date('Y-m-d')));?></div>
				</div><?php
				$images = get_children(array('post_parent' => $post->ID, 'numberposts' => 1, 'post_mime_type' =>'image'));
				if (empty($images)) $images = cwob_default_image($typepost);  unset($smallimg);
				foreach($images as $image):
					$smallimg = wp_get_attachment_image_src($image->ID, $size = 'thumbnail');
				endforeach;
				if($smallimg):?>
					<img src="<?php echo $smallimg[0];?>" width="100%" alt=""/><?php
				endif;?>
			</div>
			<span class="title"><?php the_title();?></span><br/>
			<span class="tags"><b>Feeds: </b><?php 
				unset($catarray);
				foreach(get_the_category() as $cat): $catarray[] = $cat->name; endforeach;
				if(!empty($catarray)) echo implode(', ',$catarray);?>
			</span><br/><?php 
			if($coordinates = get_post_meta(get_the_ID(), 'coordinates', true)):
				echo '<input type="hidden" class="markerdata" id="markerdata-'.$post->ID.'" value="'.$coordinates.'" name="'.$smallimg[0].'" title="'.$typepost->name.'"/>';
				echo '<input type="hidden" class="markerimg" value="'.get_bloginfo('template_url').'/images/smallarrow.png"/>';
			endif;?>
		</div>
	</div>
	<div class="arrow"></div>
</div><?php

// Save post id for populating the share count later #redundant?
global $feed_posts;
$feed_posts[] = $post->ID;?>
