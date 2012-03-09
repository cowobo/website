<div class="container locations"><?php
global $social; 
$locationcats = get_categories(array('child_of'=>get_cat_ID('Locations'), 'hide_empty'=>false, 'parent'=>get_cat_ID('Locations')));
if($locationids = get_post_meta(get_the_ID(), 'location', false)):
	$locations = get_posts(array('post__in' => $locationids));
endif;?>

<h3>Locations </h3><?php if(count($locations)>2):?><span class="showall  button">Show All &darr;</span><?php endif;?>

<div class="edit button">+ Add</div>
<div class="selectbox"><?php
	if($author):?>
		<div class="column left">
			<h3>1. Choose Country</h3>
			<ul class="typelist"><?php
				foreach($locationcats as $cat):?>
					<li class="<?php echo $cat->term_id;?>"><?php echo $cat->name.'s';?> >></li><?php
				endforeach;?>
			</ul>
		</div>
		<div class="column right"><?php	
			foreach($locationcats as $cat):?>
			<div class="slide cat<?php echo $cat->term_id;?>">
				<h3>2. Choose City</h3>
				<ul class="verlist"><?php 
					foreach(get_posts(array('cat'=>$cat->term_id)) as $feedpost):?>
						<li class="<?php echo $feedpost->ID;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $feedpost->post_title;?></a></li><?php
					endforeach;?>
				</ul>
			</div><?php
			endforeach;?>
		</div>
		<br><span class="addlocation">Can't find your location? Add it!</span><?php
	else:
		global $social; echo $social->speechbubble();
	endif;?>
</div>

<div class="listbox <?php if(count($relatedposts)>2):?>restrict<?php endif;?>">
<ul class="coordinates horlist">
	<li id="<?php echo $coordinates;?>"><?php echo $coordinates;?><span> (x)</span></li>
</ul><?php
if (!empty($locations)):
	foreach ($locations as $related): unset($images);?>
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
				if(current_user_can('edit_posts')):?><span class="remove button"> (x)</span><?php endif;?><br/>
				Updated <?php echo time_passed(strtotime($related->post_date));?>
			</div>
		</div><?php
	endforeach;
elseif(!current_user_can('edit_posts')):
	echo '<br/>This coder is not yet added a location to this post';
endif;?>
</div>

</div>