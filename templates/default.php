<?php 
the_content();
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')) include(TEMPLATEPATH.'/templates/editfeeds.php');
// Include Related Content
if(file_exists(TEMPLATEPATH.'/templates/editposts.php')) include(TEMPLATEPATH.'/templates/editposts.php');	
// Include Related Comments
$withcomments = true; comments_template();
?>
