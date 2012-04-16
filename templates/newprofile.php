<div class="large" id="newprofile">
	<div class="holder">
	<div class="content">
		<div class="gallery">
			<div class="topshadow"><h1>Create a Profile</h1></div><?php
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
		<div class="grey">
		<h3>Work Experience:</h3> Projects, Jobs, etc
		<input tabindex="2" type="text" name="workexperience" class="new workexperience" value="" disabled="disabled"/>
		<h3>Looking for:</h3> Coders, Fundings, etc
		<input tabindex="3" type="text" name="searchingfor" class="new searchingfor" value="" disabled="disabled"/>
		<h3>More about you:</h3> Maximum 1000 characters
		<textarea tabindex="4" name="editcontent" rows="5" class="new editcontent" disabled="disabled"></textarea>
		</div>
	</div>
	<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow">
		<span class="save button" id="save-<?php echo $post->ID;?>">Save</span>
		<span class="delete button">Delete</span>      
	</div>
</div>