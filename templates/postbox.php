<div class="large single" id="<?php echo $post->ID;?>">
	<div class="holder">
		<div class="content">
		<div class="gallery editable">
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
		$posttype = cwob_get_category($post->ID)->slug;
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
		if(!current_user_can('edit_posts')):?>
			<span class="navarrow left"></span>
			<a href='#' class='cowobo_social_like'>Share</a>
			<span class="navarrow right"></span><?php
		else:?>
			<span class="savebutton" id="save-<?php echo $post->ID;?>">Save</span> | 
			<span class="deletebutton">Delete</span><?php
		endif;?>
	</div>
	<div class="shadowclick"></div>
</div>
