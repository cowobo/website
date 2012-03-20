<div class="column left">
	<h3>1. Choose Country</h3>
	<ul class="typelist">
		<li class="addcat">Add Location >></li><?php
		foreach($subcats as $cat):?>
			<li class="<?php echo $cat->term_id;?>"><?php echo $cat->name;?> >></li><?php
		endforeach;?>
	</ul>
</div>
<div class="column right"><?php	
	foreach($subcats as $cat):?>
		<div class="slide cat<?php echo $cat->term_id;?>">
			<h3>2. Choose Cities</h3>
			<ul class="verlist"><?php 
				foreach(get_posts(array('cat'=>$cat->term_id)) as $feedpost):?>
					<li class="<?php echo $feedpost->ID;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $feedpost->post_title;?></a></li><?php
				endforeach;?>
			</ul>
		</div><?php
	endforeach;?>
	<div class="slide cataddcat" style="display:block">
		<h3>Add Location:</h3><br/>
		Name of Country:<br/>
		<input type="text" name="newtag" class="newtag"/><br/>
		Name of City:<br/>
		<input type="text" name="newpost" class="newpost"/><br/>
		<span class="addlocation button">Add</span><br/>
	</div>
</div>

