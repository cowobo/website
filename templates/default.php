<?php 
the_content();
// Include Related Feeds
if(file_exists(TEMPLATEPATH.'/templates/editfeeds.php')) include(TEMPLATEPATH.'/templates/editfeeds.php');
// Include Related Coders (Authors)
if(file_exists(TEMPLATEPATH.'/templates/editauthors.php')) include(TEMPLATEPATH.'/templates/editauthors.php');
// Include Related Posts
if(file_exists(TEMPLATEPATH.'/templates/editlocations.php')) include(TEMPLATEPATH.'/templates/editlocations.php');	
// Include Related Comments
$withcomments = true; comments_template();
?>
