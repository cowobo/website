<div class="large" id="new">
	<div class="holder">
	<div class="content">
		<div class="gallery">
			<div class="topshadow"><h1>Add Posts</h1></div><?php
			if($social->state < 2):?>
				<div class="slide">
					<div class="login">
						<span class="title">Who are you Angel?</span><br/><?php
						echo $social->speechbubble();?>
					</div>
				</div><?php
			endif;?>
			<div class="slide loading hide"><span class="loadicon">Loading form..</span></div>
		</div>
		<h3>Choose the type of post</h3><?php
		if($social->state < 2):?>
			<select disabled="disabled" class="new choosetype"></select><?php
		else:
			wp_dropdown_categories(array(
				'depth'=> 1, 
				'class' =>'new choosetype', 
				'hide_empty'=> 0, 
				'hierarchical' => 1, 
				'exclude'=>get_cat_ID('Uncategorized').','.get_cat_ID('Profiles'),
				'show_option_none' =>'Cick here to select',
			));
		endif;?>
		<div class="grey">
		<h3>Title of post</h3>
		<input tabindex="1" type="text" name="edittitle" class="new edittitle" value="" disabled="disabled"/>
		<h3>Address</h3>
		<input tabindex="2" type="text" name="editaddress" class="new editaddress" value="" disabled="disabled"/>
		<ul class="coordinates horlist"></ul>
		<h3>Content of post</h3>
		<textarea tabindex="3" name="editcontent" rows="5" class="new editcontent" disabled="disabled"></textarea>
		</div>
	</div>
	<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow">
		<span class="save button" id="save-<?php echo $post->ID;?>">Save</span>
		<span class="delete button">Delete</span>      
	</div>
</div>