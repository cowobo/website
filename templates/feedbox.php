<div class="large" id="feed">
	<div class="holder">
		<div class="content">
            <div class="gallery<?php if($author):?> editable<?php endif;?>">
			<div class="topshadow"><h1><?php the_title();?></h1>
				<?php the_content();?>
			</div><?php
			loadgallery_callback();?>
			</div>
			<h3>Choose the feed reader you prefer:</h3><br/><?php
			echo $social->speechbubble();?>
		</div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"></div>
</div>