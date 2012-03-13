<div class="large <?php if ($ajax == 'true') echo 'single';?>" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content"><?php
		$postcat = cwob_get_category($post->ID);
		$posttype = $postcat->slug;
		global $author; global $social; unset($profileids);
		
		//check if user has been added to authors of post
		$profiles = get_post_meta($post->ID, 'author', false);
		
		if(in_array($social->profile_id, $profiles) or $post->post_author == get_current_user_id()) $author = true; else $author = false;

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
