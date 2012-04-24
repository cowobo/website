<div class="gallery<?php if($author):?> editable<?php endif;?>">
	<div class="topshadow">
	<h1><?php echo $postcat->name;?><div class="prev">< </div><div class="next"> ></div></h1>
	</div><?php
	if($newpost):?>
		<div class="slide loading">Click here to add images</div><?php
	elseif($ajax):
		loadgallery_callback();
		if(!empty($pano_id)):?><div class="streetview" id="<?php echo $pano_id;?>">Streetview!</div><?php endif;
	else:?>
		<div class="slide loading"><span class="loadicon">Loading post..</span></div><?php
	endif;?>
</div><?php

$postcat = cwob_get_category($post->ID);		
$settings = get_option('template_options');
$sections = $settings[$postcat->term_id];

if($newpost or $author && $ajax):?>
<div class="postform <?php if (!$newpost) echo 'hide';?>"><?php
	foreach($sections as $section): $tabindex++;
		echo '<h3>'.$section['label'].'</h3> '.$section['hint'];
		if($section['type'] == 'title'):
			echo '<input tabindex="'.$tabindex.'" type="text" name="edittitle" class="new edittitle" value="'.get_the_title().'"/>';
		elseif($section['type'] == 'content'):
			echo '<textarea name="editcontent" rows="5" class="new richtext">'.apply_filters('the_content', get_the_content()).'</textarea>';
		elseif($section['type'] == 'coordinates'):
			echo '<span class="relocate"> click here to geocode it</span>'; 
			echo '<input type="text" class="searchform new latlng" value="'.$coordinates.'"/>';
		elseif($section['type'] == 'custom'):
			$slug = sanitize_title($section['label']);
			$value = get_post_meta(get_the_ID(), $slug, true);
			echo '<input tabindex="'.$tabindex.'" type="text" name="'.$slug.'" class="new" value="'.$value.'"/>';
		endif;
	endforeach;?>
</div><?php
endif;

if(!$newpost):?>
<div class="postbox"><?php
	if($author && $ajax):?><div class="editpost right button">+ Edit Post</div><?php endif;
	foreach($sections as $section):
		if($section['type'] == 'title'):
			echo '<div class="title"><span class="postrss"></span>'.get_the_title().'</div>';
		elseif($section['type'] == 'content'):
			the_content();
			echo '<br/>';
		elseif($section['type'] == 'custom'):
			$slug = sanitize_title($section['label']);
			if($value = get_post_meta(get_the_ID(), $slug, true)):
				echo '<b>'.$section['label'].'</b>: '.$value.'<br/>';
			endif;
		endif;
	endforeach;?>
</div><?php
endif;

if($ajax):
	$withcomments = true; comments_template();
	include(TEMPLATEPATH.'/templates/edittags.php');
	foreach($sections as $section):
		if($section['type'] == 'authors'):
			include(TEMPLATEPATH.'/templates/editauthors.php');
		endif;	
	endforeach;
	include(TEMPLATEPATH.'/templates/editposts.php');
endif;
?>
