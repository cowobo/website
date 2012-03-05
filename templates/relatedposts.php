<div class="container"><?php

$relatedposts = new Cowobo_Feed(array('posts' => $post->ID));
$relatedposts = $relatedposts->get_related();?>

<h3>Posts </h3><?php if(count($relatedposts)>2):?><span class="showall">Show All &darr;</span><?php endif;?>
<div class="editbutton">+ Add</div>

<div class="selectbox"><?php
	if($social->state != 4):?>
		<div class="column left">
			<h3>1. Choose Type</h3>
			<ul class="typelist">
				<li id="sugg" class="selected">Suggested Posts >></li><?php
				foreach(get_categories(array('exclude'=>get_cat_ID('Uncategorized'), 'hide_empty'=>false, 'parent'=>0)) as $cat):?>
					<li class="<?php echo $cat->term_id;?>"><?php echo $cat->name.'s';?> >></li><?php
				endforeach;?>
			</ul>
		</div>
		<div class="column right">
			<div class="slide catsugg" style="display:block">
				<h3>2. Choose Posts</h3>
				<ul class="verlist"><?php
				$suggestedposts  = new Cowobo_Related_Posts();
				if ($suggestedposts = $suggestedposts->find_similar_posts()) :
					foreach($suggestedposts as $suggested):?>
						<li class="<?php echo $suggested->ID;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $suggested->post_title;?></a></li><?php
					endforeach;
				endif;?>
				</ul>
			</div><?php	
			foreach(get_categories(array('exclude'=>get_cat_ID('Uncategorized'), 'hide_empty'=>false, 'parent'=>0)) as $cat):?>
			<div class="slide cat<?php echo $cat->term_id;?>">
				<h3>2. Choose Posts</h3>
				<ul class="verlist"><?php 
					foreach(get_posts(array('cat'=>$cat->term_id)) as $feedpost):?>
						<li class="<?php echo $feedpost->ID;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $feedpost->post_title;?></a></li><?php
					endforeach;?>
				</ul>
			</div><?php
			endforeach;?>
		</div><?php
	else:
		global $social; echo $social->speechbubble();
	endif;?>
</div>

<div class="listbox <?php if(count($relatedposts)>2):?>restrict<?php endif;?>"><?php
if (!empty($relatedposts)):
	foreach ($relatedposts as $related): unset($images);?>
		<div class="<?php echo $related->ID;?> listitem">
			<div class="thumbnail"><?php
				$images = get_children(array('post_parent' => $related->ID, 'numberposts' => 1, 'post_mime_type' =>'image'));
				if (empty($images)) $images = cwob_default_image($typepost);  unset($smallimg);
				foreach($images as $image):
					$smallimg = wp_get_attachment_image_src($image->ID, $size = 'thumbnail');
				endforeach;
				if($smallimg):?>
					<a href="<?php echo get_permalink($related->ID);?>"><img src="<?php echo $smallimg[0];?>" height="100%" alt=""/></a><?php
				endif;?>
			</div>
			<div class="text">
				<a href="<?php echo get_permalink($related->ID);?>"><?php echo $related->post_title;?></a><?php 
				if(current_user_can('edit_posts')):?><span class="removebutton"> (x)</span><?php endif;?><br/>
				Updated <?php echo time_passed(strtotime($related->post_date));?>
			</div>
		</div><?php
	endforeach;
elseif(!current_user_can('edit_posts')):
	echo '<br/>This post is not yet related to other content';
endif;?>
</div>

</div>