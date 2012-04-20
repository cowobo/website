<div class="column left">
	<h3>Choose a location:</h3>
	<ul class="typelist">
		<li class="addcat">Add Location >></li><?php
		foreach($subcats as $cat):?>
			<li class="<?php echo $cat->term_id;?>" id="<?php echo $cat->name;?>"><?php echo $cat->name;?> >></li><?php
		endforeach;?>
	</ul>
</div>
<div class="column right"><?php	
	foreach($subcats as $cat):?>
		<div class="slide cat<?php echo $cat->term_id;?>">
			<h3>2. Choose Cities</h3>
			<ul class="verlist"><?php 
				foreach(get_posts(array('cat'=>$cat->term_id)) as $feedpost):?>
					<li class="<?php echo $feedpost->ID;?>" id="<?php echo $cat->name;?>"><a href="<?php echo get_permalink($feedpost->term_id);?>" onclick="return false"><?php echo $feedpost->post_title;?></a></li><?php
				endforeach;?>
			</ul>
		</div><?php
	endforeach;?>
	<div class="slide cataddcat" style="display:block">
		<h3>Or add a new one:</h3><br/>
		Name of Town/City:<br/>
		<input type="text" name="newcity" class="newcity"/><br/>
		Name of Country:<br/>
		<input type="text" name="newcountry" class="newcountry"/><br/>
		<span class="addlocation button">Add</span><br/>
	</div>
</div>

