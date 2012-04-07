<div class="container tags"><?php
global $currenttype;

if(!$newpost) $relatedtags = get_the_category(); //to prevent users saveing post under parent cat
$subcats = get_categories(array('parent'=>$postcat->term_id, 'hide_empty'=>false, 'child_of'=>$postcat->term_id,));?>

<h3><span class="tag icon"></span>Tags (<?php echo count($relatedtags);?>)</h3><?php if(count($relatedtags)>2):?><span class="showall button">Show All &darr;</span><?php endif;?>
<div class="edit button">+ Add</div>

<div class="selectbox" id="new-<?php echo $postcat->term_id;?>"><?php
	if($author):?>
		<div class="column left">
			<h3>Choose existing tags:</h3>
			<ul class="verlist"><?php 
				foreach($subcats as $cat):?>
					<li class="<?php echo $cat->term_id;?>" id="<?php echo $cat->name;?>"><?php echo $cat->name;?></li><?php
				endforeach;?>
			</ul>
		</div>
		<div class="column right">
			<h3>Or create new tags:</h3><br/>
			Name of Tag:<br/>
			<input type="text" name="newtag" class="newtag"/>
			<span class="addtag button">Add</span><br/>
		</div><?php
	elseif($social->state > 1):
		echo 'Become a contributor to this post by sending a request to the author';
	else:
		echo $social->speechbubble();
	endif;?>
</div>

<div class="listbox <?php if(count($relatedtags)>2):?>restrict<?php endif;?>" ><?php
if (!empty($relatedtags)):
	foreach($relatedtags as $cat): unset($images);?>
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
endif;?>
</div>

</div>