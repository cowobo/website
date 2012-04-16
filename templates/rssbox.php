<div class="large" id="rss">
	<div class="holder">
		<div class="content">
            <div class="gallery<?php if($author):?> editable<?php endif;?>">
			<div class="topshadow"><h1><?php the_title();?></h1></div><?php
			foreach(get_children(array('post_parent' => $post->ID, 'numberposts' => -1, 'post_mime_type' =>'image', 'order'=>'ASC')) as $image):?>
			<div class="slide"><?php
				$imgsrc = wp_get_attachment_image_src($image->ID, $size = 'large');?>
				<img src="<?php echo $imgsrc[0];?>" width="100%" alt=""/>
 			</div><?php
			endforeach;?>
			</div>
			<?php if (!is_home): ?>
                <h3>Get the feed for "<?php echo cowobo_get_pagetitle() ?>"</h3>
            <?php else: ?>
                <h3>Get the feed for "Coders Without Borders"</h3>			
			<?php endif; ?>
			<div class="rssicons"><?php $social->print_rss_links(false); ?></div>
		</div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"><?php
	    // Add to CoWoBo favourite feed
        if (is_user_logged_in() && !is_userfeed()):
            $feed_type = 'c';
            $category = cowobo_get_current_category();
            $feed_id = $category['catid'];
            $user_id = wp_get_current_user()->ID;
            echo "<a href='javascript:void(0)' onclick='add_to_feed(\"$feed_type\",$feed_id,$user_id)'>Add to Favourites</a>";
		endif;?>
	</div>
</div>