<?php
if ( isset( $_POST['postid'] ) ) {
	require_once('../../../../wp-blog-header.php');
	require_once ('class-cowobo-social.php');

	// Prevent the 404
	header("HTTP/1.1 200 OK");
	
	$post_link = get_permalink( $_POST['postid'] );
	$post_title = get_the_title ( $_POST['postid'] ); ?>

	<div class='like_wrapper' style='display:block;text-align:center;'>
	<?php $social = new Cowobo_Social(true);
	$social->show_share($_POST['postid']); ?>
	</div>

<?php } ?>
