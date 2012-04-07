<?php
require_once('../../../../wp-blog-header.php');

// Prevent the 404
header("HTTP/1.1 200 OK");

$userid = $_POST['user_id'];
if (empty($userid)) die;

if (isset($_POST['reset'])) $social->reset_feed($userid);

if (isset($_POST['add'])) {
	$feed_query = array ( $_POST['feed_type'] => $_POST['feed_id'] );
	$social->add_to_feed($userid,$feed_query);
} elseif ( isset($_POST['profile'] ) ) {
	$social->add_to_profile( $userid, $_POST['post_id'] );
}

?>