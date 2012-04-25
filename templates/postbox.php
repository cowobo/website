<?php 
global $author; 
global $social;
unset($coordinates);

//setup parameters
$postcat = cwob_get_category($post->ID);
$coordinates = get_post_meta($post->ID, 'coordinates', true);

$streetview = 'http://cbk0.google.com/cbk?output=xml&ll='.$coordinates;
if(!empty($coordinates)):
	$xmlstring = file_get_contents($streetview);
	$xml = simplexml_load_string($xmlstring);
	$pano_id = $xml->data_properties['pano_id'];
endif;
		
//check if user is author of post or added to the authors of post
if($post->post_author == get_current_user_id() or current_user_can('edit_others_posts')) $author = true; else $author = false; 
$profiles = get_post_meta($post->ID, 'authors', false);
if(!empty($profiles) && !empty($social->profile_id)):
	if(in_array($social->profile_id, $profiles)) $author = true;
endif;
?>

<div class="large<?php if ($ajax == 'true') echo ' single';?>" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content"><?php
		if(!empty($coordinates)):?>
			<input type="hidden" class="coordinates" value="<?php echo $coordinates;?>"/><?php 
		endif;
		include(TEMPLATEPATH.'/templates/templates.php');?>
		</div>
		<div class="scrolltrack"><div class="toparrow"></div><div class="slider"></div><div class="bottomarrow"></div></div>
		<div class="cowobo_social_share"><?php
			if ($social->state < 2) echo $social->speechbubble();?>
		</div>
	</div>
	<div class="arrow"><?php
		$sharecount = $social->get_total_shares($post->ID, true);
		if($author):
			if ($post->post_status == 'draft' && $post->ID == $social->profile_id) : ?>
				<span class="save button">Complete Profile</span><?php
			else:?>
                <span class="save button">Save</span><?php
				if(!$newpost):?>
				<span class="link icon"></span><span class='cowobo_social_like button'>
					Share (<span><?php echo $sharecount;?></span>)
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
				Share (<span><?php echo $sharecount;?></span>)
			</span>
			<span class="<?php if(!empty($next)) echo 'nextpost button';?>" id="next-<?php echo $next->ID;?>">Next</span><?php
		endif;?>
	</div>
	<div class="shadowclick"></div>
</div>
