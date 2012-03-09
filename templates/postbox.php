<?php 
//check type of post and authors
global $social; unset($profiles);
$postcat = cwob_get_category($post->ID);
$posttype = $postcat->slug;

//sort relatedposts by type
$relatedposts = new Cowobo_Feed(array('posts' => $post->ID));
if($relatedposts = $relatedposts->get_related()):
foreach($relatedposts as $relatedpost):
	$type = cwob_get_category($relatedpost->ID);
	$sorted[$type->term_id][] = $relatedpost;
endforeach;
endif;

//check if user has a profile which can edit the post
foreach($sorted[get_cat_ID('Profiles')] as $profile):
	$profiles[] = $profile->ID;
endforeach;
if(in_array($social->profile_id, $profiles)) $author = true; else $author = false;?>

<div class="large single" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content">
		<div class="gallery<?php if($author) echo ' editable';?>">
			<div class="topshadow">
				<h1><?php echo $typepost->name;?><div class="prev">< </div><div class="next"> ></div></h1>
			</div><?php
			loadgallery_callback();
			if($coordinates = get_post_meta(get_the_ID(), 'coordinates', true)):
				$xml = simplexml_load_string(file_get_contents('http://cbk0.google.com/cbk?output=xml&ll='.$coordinates));
				if($pano_id = $xml->data_properties['pano_id']):?>
					<div class="streetview" id="<?php echo $pano_id;?>">Streetview!</div><?php
				endif;
			endif;?>
		</div>
		<a class="title" href="<?php the_permalink();?>"><?php the_title();?></a><br/><?php
		if(file_exists(TEMPLATEPATH.'/templates/'.$post->post_name.'.php')):
			include(TEMPLATEPATH.'/templates/'.$post->post_name.'.php');
		elseif ( file_exists(TEMPLATEPATH.'/templates/' . $posttype . '.php') ):
			include(TEMPLATEPATH.'/templates/' . $posttype . '.php');	
		else:
			include(TEMPLATEPATH.'/templates/default.php');	
		endif;?>
		</div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="cowobo_social_share"></div>
	<div class="arrow"><?php
		if($author):?>
			<span class="save button" id="save-<?php echo $post->ID;?>">Save</span>
			<span class='cowobo_social_like button'>Share</span>
			<span class="delete button">Delete</span><?php
		else:
			$prev = get_adjacent_post(true,'',false); 
			$next = get_adjacent_post(true,'',true);?>
			<span class="<?php if(!empty($prev)) echo 'lastpost button';?>" id="last-<?php echo $prev->ID; ?>">Last</span>
			<span class="cowobo_social_like button">Share</span>
			<span class="<?php if(!empty($next)) echo 'nextpost button';?>" id="next-<?php echo $next->ID;?>">Next</span><?php
		endif;?>
	</div>
	<div class="shadowclick"></div>
</div>
