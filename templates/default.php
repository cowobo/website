<?php 
the_content();

// Include Related Coders (Authors)
if(file_exists(TEMPLATEPATH.'/templates/relatedfeeds.php')) include(TEMPLATEPATH.'/templates/relatedcoders.php');
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/relatedfeeds.php')) include(TEMPLATEPATH.'/templates/relatedfeeds.php');
// Include Related Posts
if(file_exists(TEMPLATEPATH.'/templates/relatedposts.php')) include(TEMPLATEPATH.'/templates/relatedposts.php');
// Include Related Places
if(file_exists(TEMPLATEPATH.'/templates/relatedfeeds.php')) include(TEMPLATEPATH.'/templates/relatedplaces.php');	
// Include Related Comments
$withcomments = true; comments_template();
?>
