<div class="large <?php if ($ajax == 'true') echo 'single';?>" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content"><?php
		$postcat = cwob_get_category($post->ID);
		$posttype = $postcat->slug;
		global $author; global $social; unset($profileids); 
		
		//load related posts and check if user has a profile to edit post
		$relatedposts = new Cowobo_Feed(array('posts' => $post->ID));
		if($relatedposts = $relatedposts->get_related()):
			foreach($relatedposts as $relatedpost):
				$type = cwob_get_category($relatedpost->ID);
				$sorted[$type->term_id][] = $relatedpost;
			endforeach;
		endif;
		if($profiles = $sorted[get_cat_ID('Profiles')]):
			foreach($profiles as $profile):
				$profileids[] = $profile->ID;
			endforeach;
		else: 
			$profileids = array();
		endif;
		if(in_array($social->profile_id, $profileids) or $post->post_author == get_current_user_id()):
	 		$author = true; else: $author = false;
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
