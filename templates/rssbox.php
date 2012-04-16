<div class="large" id="rss">
	<div class="holder">
		<div class="content">
            <div class="gallery<?php if($author):?> editable<?php endif;?>">
			<div class="topshadow"><h1>Stay Connected  via RSS</h1></div>
			</div>
			<?php if (!is_home): ?>
                <h3>Get the feed for "<?php echo cowobo_get_pagetitle() ?>"</h3>
            <?php else: ?>
                <h3>Get the feed for "Coders Without Borders"</h3>			
			<?php endif; ?>
            <ul>
                <?php $social->print_rss_links(false, '<li>', '</li>'); ?>
            </ul>
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