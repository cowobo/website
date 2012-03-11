<div class="large" id="join">
	<div class="holder">
		<div class="content">
            <div class="gallery<?php if($author):?> editable<?php endif;?>">
			<div class="topshadow"><h1><?php the_title();?></h1>
				<?php the_content();?>
			</div><?php
			loadgallery_callback();?>
			</div>
			<h2>Click on your favourite account:</h2><?php
			echo $social->speechbubble();?>
		</div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"></div>
</div>