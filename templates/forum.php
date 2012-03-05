<b>Coders: </b><a href=""><?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?></a><br/><?php
the_content();
		
$withcomments = true;
comments_template();?>