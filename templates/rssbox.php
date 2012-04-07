<div class="large" id="rss">
	<div class="holder">
		<div class="content">
            <div class="gallery<?php if($author):?> editable<?php endif;?>">
			<div class="topshadow"><h1>Add RSS</h1></div>
			</div>
            <?php if ( ! is_home ): ?>
                <h3>Get the feed for "<?php echo cowobo_get_pagetitle() ?>"</h3>
            <?php endif; ?>
			<h4>Choose the feed reader you prefer:</h4><br/>
            <ul>
                <?php $social->print_rss_links(false, '<li>', '</li>'); ?>
            </ul>
		</div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"></div>
</div>