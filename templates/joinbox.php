<div class="large" id="join">
	<div class="holder">
		<div class="content">
            <h2>Benefits Of Joining</h2>
			<p>1. Create a profile </p>
			<p>2. Get access to exclusive job placements around the world</p>
			<p>3. Get help from other Coders Without Borders in our forums</p>
			<p>4. Contribute to our wikis, news, projects, and placements</p>
			<br/><br/>
			<h2>Click on your favourite account:</h2>
			<?php echo $social->cowobo_connect(); ?>
			<br/><br/>
   			<h2>Or create a new cowobo account:</h2>
			<form method="post" class="padding" action="<?php bloginfo('url');?>/wp-login.php?action=register">
				Name:
				<input id="first_name" type="text" value="<?php echo $_POST['firstname']; ?>" name="firstname" tabindex="1"/> 
				Username:
				<input type="text" name="user_login" value="" id="user_login" />
				Email:
				<input type="text" name="user_email" value="" id="user_email" tabindex="2" />
				<?php do_action('register_form'); ?>
				<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?register=true" />
				<input type="hidden" name="user-cookie" value="1" />
				<input type="submit" name="user-submit" value="Subscribe" tabindex="3" />
			</form>
			
		</div>
		<div class="scrolltrack"><div class="slider"></div></div>
	</div>
	<div class="arrow"></div>
</div>