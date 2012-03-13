<b>Coders: </b><a href=""><?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?></a><br/><?php
the_content();

if($ajax):
	// Include Comments
	$withcomments = true; comments_template();
	// Include Related Feeds
	if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')): include(TEMPLATEPATH.'/templates/editfeeds.php'); endif;
	// Include Authors
	if(file_exists(TEMPLATEPATH.'/templates/editauthors.php')): include(TEMPLATEPATH.'/templates/editauthors.php'); endif;
	// Include Related Posts
	if(file_exists(TEMPLATEPATH.'/templates/editposts.php')): include(TEMPLATEPATH.'/templates/editposts.php'); endif;
else:?>
	<span class="loading">Loading related posts</span><?php
endif;?>