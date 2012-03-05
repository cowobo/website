<?php
if (isset($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])){
	die ('Nerd alert: do not load this page directly. Thanks!'); }
if ( post_password_required() ) { ?>
	<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
<?php
	return; };?>
<div class="commentbox"><?php

$postcomments = get_comments(array('post_id' =>$post->ID));?>

<h3>Comments</h3><?php if(count($postcomments)>2):?><span class="showall">Show All &darr;</span><?php endif;?>
<div class="addbutton">+ Add</div>

<div class="replybox"><?php 
global $social;
if ($social->state != 4):
	echo $social->speechbubble();
else:?>
	<form action="wp-comments-post.php" method="post" class="commentform">
		<div class="thumbnail"></div>
		<div class="text">
		<a href="<?php echo admin_url('profile.php');?>"><?php echo $user_identity;?></a> just now:<br/>
		<textarea name="comment" class="commenttext" aria-required="true"></textarea>
		<input type='hidden' class='comment_post_ID' name='comment_post_ID' value='<?php echo $post->ID;?>'/>
		<input type='hidden' class='comment_parent' name='comment_parent' value='0' />
		<input name="submit" type="submit" class="submit" value="Submit Comment" /><span class="loading"></span>
		</div>
		<?php do_action('comment_form', $post->ID);?>
	</form><?php
endif;?>
</div><?php

// Calback for comments (display) - note: it's not proper to put this here, true. But it's purely VIEW code, so imho, it belongs here anyway.
if (!function_exists('cowobo_comments')) {
	function cowobo_comments($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
	<li id="li-comment-<?php comment_ID(); ?>">
		<article class="comments" id="comment-<?php comment_ID(); ?>" >
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<div class="reply">+ Reply</div>
					<div class="thumbnail"></div>
					<div class="text"><?php
						echo '<span class="authorlink">'.get_comment_author_link().'</span>, '.time_passed(strtotime($comment->comment_date)).':<br/>';
						comment_text();?>
					</div>
				</div>
			</footer>
		</article>
	</li><?php 
	} 
};?>

<ol class="listbox <?php if(count($postcomments)>2):?>restrict<?php endif;?>" id="comments<?php echo $post->ID;?>"><?php
	// get all comments for the current lightbox
	if(!empty($postcomments)):
		wp_list_comments(array('login_text'=> 'Login to reply', 'type'=> 'comment', 'callback'=> 'cowobo_comments'), $postcomments);
	endif;?>
</ol>

</div>