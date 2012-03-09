<div class="container feeds"><?php
global $social; 
$relatedfeeds = get_the_category();?>

<h3>Feeds (<?php echo count($relatedfeeds);?>)</h3><?php if(count($relatedfeeds)>2):?><span class="showall button">Show All &darr;</span><?php endif;?>
<div class="edit button">+ Add</div>

<div class="selectbox"><?php
	if($author):?>
	<div class="column left">
		<h3>1. Choose Type</h3>
		<ul class="typelist"><?php
			foreach(get_categories(array('parent'=>0, 'hide_empty'=>false, 'exclude'=>get_cat_ID('Uncategorized'))) as $cat):?>
				<li class="<?php echo $cat->term_id.' '; if ($typepost->term_id == $cat->term_id) echo 'selected';?>"><?php echo $cat->name;?> >></li><?php
			endforeach;?>
		</ul>
	</div>
	<div class="column right"><?php
		foreach(get_categories(array('parent'=>0, 'hide_empty'=>false, 'exclude'=>get_cat_ID('Uncategorized'))) as $cat):?>
			<div class="slide cat<?php echo $cat->term_id;?>" <?php if ($typepost->term_id == $cat->term_id) echo 'style="display:block"';?>>
				<h3>2. Choose Feeds</h3>
				<ul class="verlist"><?php 
					foreach(get_categories(array('child_of'=>$cat->term_id, 'hide_empty'=>false)) as $cat):?>
						<li class="<?php echo $cat->term_id;?>"><a href="<?php echo get_category_link($cat->term_id);?>" onclick="return false"><?php echo $cat->name;?></a></li><?php
					endforeach;?>
				</ul>
			</div><?php
		endforeach;?>
	</div><?php
	else:
		global $social; echo $social->speechbubble();
	endif;?>
</div>

<div class="listbox <?php if(count($relatedfeeds)>2):?>restrict<?php endif;?>" ><?php
if (!empty($relatedfeeds)):
	foreach($relatedfeeds as $cat): unset($images);?>
		<div class="<?php echo $cat->term_id;?> listitem">
			<div class="thumbnail"><?php
				$posts = get_posts(array('cat'=>$cat->term_id, 'number'=>1)); 
				$images = get_children(array('post_parent' => $posts[0]->ID, 'numberposts' => 1, 'post_mime_type' =>'image'));
				if (empty($images)) $images = cwob_default_image($typepost);  unset($smallimg);
				foreach($images as $image):
					$smallimg = wp_get_attachment_image_src($image->ID, $size = 'thumbnail');
				endforeach;
				if($smallimg):?>
					<a href="<?php echo get_category_link($cat->term_id);?>"><img src="<?php echo $smallimg[0];?>" height="100%" alt=""/></a><?php
				endif;?>
			</div>
			<div class="text">
				<a href="<?php echo get_category_link($cat->term_id);?>"><?php echo $cat->name;?></a><?php 
				if(current_user_can('edit_posts')):?><span class="remove button"> (x)</span><?php endif;?><br/>
				Updated <?php echo time_passed(strtotime($posts[0]->post_date));?>
			</div>
		</div><?php
	endforeach;
elseif(!current_user_can('edit_posts')):
	echo '<br/>This post has not been asssigned to any feeds. Please notify the author.';
endif;?>
</div>

</div>