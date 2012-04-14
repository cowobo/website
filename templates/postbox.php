<?php 
global $author; 
global $social;
unset($coordinates);

//setup parameters
$postcat = cwob_get_category($post->ID);		
$posttype = $postcat->slug;
$coordinates = get_post_meta($post->ID, 'coordinates', true);

if(!empty($coordinates) && checkdnsrr('google.com', 'ANY')):
	if($xmlstring = file_get_contents('http://cbk0.google.com/cbk?output=xml&ll='.$coordinates)):
		$xml = simplexml_load_string($xmlstring);
		$pano_id = $xml->data_properties['pano_id'];
	endif;
endif;
		
//check if user is author of post or added to the authors of post
if($post->post_author == get_current_user_id()) $author = true; else $author = false; 
$profiles = get_post_meta($post->ID, 'author', false);
if(!empty($profiles) && !empty($social->profile_id)):
	if(in_array($social->profile_id, $profiles)) $author = true;
endif;
?>

<div class="large <?php if ($ajax == 'true') echo 'single';?>" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content"><?php
		if(!empty($coordinates)):?>
			<input type="hidden" class="coordinates" value="<?php echo $coordinates;?>"/><?php 
		endif;
		//load the templates
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
		if($author):
			if ($post->post_status == 'draft' && $post->ID == $social->profile_id) : ?>
				<span class="save button">Complete Profile</span><?php
			else:?>
                <span class="save button">Save</span><?php
				if(!$newpost):?>
				<span class="link icon"></span><span class='cowobo_social_like button'>
					Share (<?php echo $social->get_total_shares;?>)
				</span><?php
				endif;
				if ($post->ID != $social->profile_id):?>
					<span class="delete button">Delete</span><?php                     
				endif;
            endif;
		else:
			$prev = get_adjacent_post(true,'',false);
			$next = get_adjacent_post(true,'',true);?>
			<span class="<?php if(!empty($prev)) echo 'lastpost button';?>" id="last-<?php echo $prev->ID; ?>">Last</span>
			<span class="link icon"></span><span class='cowobo_social_like button'>
				Share (<?php echo $social->get_total_shares;?>)
			</span>
			<span class="<?php if(!empty($next)) echo 'nextpost button';?>" id="next-<?php echo $next->ID;?>">Next</span><?php
		endif;?>
	</div>
	<div class="shadowclick"></div>
</div>
