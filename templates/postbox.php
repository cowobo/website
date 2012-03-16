<?php 
global $author; 
global $social;

//setup parameters
$postcat = cwob_get_category($post->ID);		
$posttype = $postcat->slug;

if($coordinates = get_post_meta(get_the_ID(), 'coordinates', true)):
	$xml = simplexml_load_string(file_get_contents('http://cbk0.google.com/cbk?output=xml&ll='.$coordinates));
	$pano_id = $xml->data_properties['pano_id'];
endif;
		
//check if user has been added to authors of post
$profiles = get_post_meta($post->ID, 'author', false);
if(empty($profiles)) $profiles = array();
$auth = $post->post_author;
//make editable if the post author is admin, the user, or has assigned the user as author
if($auth = 1 or $auth == get_current_user_id() or in_array($social->profile_id, $profiles)):
	$author = true; 
else:
	$author = false;
endif;?>

<div class="large <?php if ($ajax == 'true') echo 'single';?>" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content"><?php
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
		if($author):?>
			<span class="save button" id="save-<?php echo $post->ID;?>">
                <?php if ( $post->post_status == 'draft' && $post->ID == $social->profile_id ) : ?>
                    Complete Profile</span>
                <?php else: ?>
                    Save</span>
                    <span class='cowobo_social_like button'>Like</span>
                    <?php if ( $post->ID != $social->profile_id ) : ?>
                        <span class="delete button">Delete</span>
                    <?php endif; ?>
                <?php endif; ?>
        <?php else:
			$prev = get_adjacent_post(true,'',false);
			$next = get_adjacent_post(true,'',true);?>
			<span class="<?php if(!empty($prev)) echo 'lastpost button';?>" id="last-<?php echo $prev->ID; ?>">Last</span>
			<span class="cowobo_social_like button">Like</span>
			<span class="<?php if(!empty($next)) echo 'nextpost button';?>" id="next-<?php echo $next->ID;?>">Next</span><?php
		endif;?>
	</div>
	<div class="shadowclick"></div>
</div>
