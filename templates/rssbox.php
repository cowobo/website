<div class="large" id="rss">
	<div class="holder">
		<div class="content">
            <div class="gallery<?php if($author):?> editable<?php endif;?>">
			<div class="topshadow"><h1>Add RSS</h1></div>
			</div>
			<h3>Choose the feed reader you prefer:</h3><br/>
            <ul>
                <?php $social->print_rss_links(false, '<li>', '</li>'); ?>
            </ul>
		</div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"></div>
</div>