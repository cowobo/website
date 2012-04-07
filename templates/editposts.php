<?php
$exclude = get_cat_ID('Uncategorized');
if($postcat->slug == 'locations') $exclude .= ','.get_cat_ID('Locations');
$types = get_categories(array('parent'=>0, 'hide_empty'=>false, 'exclude'=>$exclude));

//load and sort related posts by type
$relatedposts = new Cowobo_Feed(array('posts' => $post->ID));
if($relatedposts = $relatedposts->get_related()):
	foreach($relatedposts as $relatedpost):
		$typecat = cwob_get_category($relatedpost->ID);
		$typeposts[$typecat->term_id][] = $relatedpost;
	endforeach;
endif;

//Sort type by number of related posts and put locations on top
foreach($types as $type):
	if($type->slug == 'locations') $count = 100; else $count = count($typeposts[$type->term_id]); 
	$typearray[] = array('cat'=>$type, 'posts'=>$count);
endforeach;
usort($typearray, 'sort_types');

foreach($typearray as $type):
	$typeid = $type['cat']->term_id;
	$catposts = $typeposts[$typeid];
	$count = count($catposts);
	$subcats = get_categories(array('child_of'=>$typeid, 'hide_empty'=>false, 'parent'=>$typeid));?>
	<div class="container <?php echo $type['cat']->slug;?>">
	<h3 class="<?php if($count<1) echo 'empty';?>"><span class="link smallicon"></span><?php echo $type['cat']->name.' ('.$count.')'?></h3><?php
	if($count>2):?><span class="showall button">Show All &darr;</span><?php endif;?>
	<div class="edit button">+ Add</div>
	<div class="selectbox" id="new-<?php echo $typeid;?>"><?php
		if($author):
			if($type['cat']->slug == 'locations'):
				include(TEMPLATEPATH.'/templates/editlocations.php');
			else:?>
			<div class="column left">
				<h3>1. Choose Category</h3>
				<ul class="typelist">
					<li class="sugg selected">Suggested >></li><?php
					foreach($subcats as $cat):?>
						<li class="<?php echo $cat->term_id;?>"><?php echo $cat->name;?> >></li><?php
					endforeach;?>
					<li class="addcat"><b>Add Category >></b></li>
				</ul>
			</div>
			<div class="column right">
				<div class="slide cataddcat" style="display:block">
					<h3>2. Add A Category</h3><br/>
					Name of category:<br/>
					<input type="text" name="newtag" class="newtag"/>
					<span class="addtag button">Add</span><br/>
				</div>
				<div class="slide catsugg" style="display:block">
					<h3>2. Choose Posts</h3>
					<ul class="verlist"><?php
					$suggestedposts  = new Cowobo_Related_Posts();
					if ($suggestedposts = $suggestedposts->find_similar_posts(false, $typeid, false)) :
						foreach($suggestedposts as $suggested):?>
							<li class="<?php echo $suggested->ID;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $suggested->post_title;?></a></li><?php
						endforeach;
					endif;?>
					</ul>
				</div>
				<?php	
				foreach($subcats as $cat):?>
				<div class="slide cat<?php echo $cat->term_id;?>">
					<h3>2. Choose Posts</h3>
					<ul class="verlist"><?php 
						foreach(get_posts(array('cat'=>$cat->term_id)) as $feedpost):?>
							<li class="<?php echo $feedpost->ID;?>" id="<?php echo $cat->name;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $feedpost->post_title;?></a></li><?php
						endforeach;?>
					</ul>
				</div><?php
				endforeach;?>
			</div><?php
			endif;
		elseif($social->state > 1):
			echo 'Become a contributor to this post by sending a request to the author';
		else:
			echo $social->speechbubble();
		endif;?>
	</div>
	
	<div class="listbox <?php if(count($sectionposts)>2):?>restrict<?php endif;?>"><?php
	if(count($catposts)>0):
	foreach ($catposts as $related): unset($images);?>
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
	endif;?>
	</div>
</div><?php
endforeach;
?>