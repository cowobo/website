<div class="container authors"><?php

if(!empty($profiles)):
	$authorprofiles = get_posts(array('post__in' => $profiles));
endif;

$codercats = get_categories(array('child_of'=>get_cat_ID('Profiles'), 'hide_empty'=>false, 'parent'=>get_cat_ID('Profiles')));?>

<h3><span class="author icon"></span>Authors (<?php echo count($authorprofiles);?>)</h3><?php if(count($authorprofiles)>2):?><span class="showall  button">Show All &darr;</span><?php endif;?>
<div class="edit button">+ Add</div>

<div class="selectbox"><?php 
	if($author):?>
		<div class="column left">
			<h3>1. Choose Type</h3>
			<ul class="typelist"><?php
				foreach($codercats as $cat):?>
					<li class="<?php echo $cat->term_id;?>"><?php echo $cat->name;?> >></li><?php
				endforeach;?>
			</ul>
		</div>
		<div class="column right"><?php	
			foreach($codercats as $cat):?>
			<div class="slide cat<?php echo $cat->term_id;?>">
				<h3>2. Choose Profile</h3>
				<ul class="verlist"><?php 
					foreach(get_posts(array('cat'=>$cat->term_id)) as $feedpost):?>
						<li class="<?php echo $feedpost->ID;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $feedpost->post_title;?></a></li><?php
					endforeach;?>
				</ul>
			</div><?php
			endforeach;?>
		</div><?php
	elseif($social->state > 1):
			echo 'Become a contributor to this post by sending a request to the author';
	else:
		echo $social->speechbubble();
	endif;?>
</div>

<div class="listbox <?php if(count($authorprofiles)>2):?>restrict<?php endif;?>"><?php
if (!empty($authorprofiles)):
	foreach ($authorprofiles as $authorprofile): unset($images);?>
		<div class="<?php echo $authorprofile->ID;?> listitem">
			<div class="thumbnail"><?php
				$images = get_children(array('post_parent' => $authorprofile->ID, 'numberposts' => 1, 'post_mime_type' =>'image'));
				if (empty($images)) $images = cwob_default_image($typepost);  unset($smallimg);
				foreach($images as $image):
					$smallimg = wp_get_attachment_image_src($image->ID, $size = 'thumbnail');
				endforeach;
				if($smallimg):?>
					<a href="<?php echo get_permalink($authorprofile->ID);?>"><img src="<?php echo $smallimg[0];?>" height="100%" alt=""/></a><?php
				endif;?>
			</div>
			<div class="text">
				<a href="<?php echo get_permalink($authorprofile->ID);?>"><?php echo $authorprofile->post_title;?></a><?php 
				if(current_user_can('edit_posts')):?><span class="remove button"> (x)</span><?php endif;?><br/>
				Updated <?php echo time_passed(strtotime($authorprofile->post_date));?>
			</div>
		</div><?php
	endforeach;
endif;?>
</div>

</div>