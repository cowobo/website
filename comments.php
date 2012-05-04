<?php
global $social;

if (isset($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])){
	die ('Nerd alert: do not load this page directly. Thanks!'); }
if ( post_password_required() ) { ?>
	<p class="nocomments">This post is password protected. Enter the password to view comments.</p><?php
return; };?>
	
<div class="commentbox"><?php
//get all comments sow
$postcomments = get_comments(array('post_id' =>$post->ID));
$count = count($postcomments);?>

<h3><span class="postrss"></span><?php if($count<1) echo 'Share your thoughts:'; else echo 'Messages ('.$count.')';?></h3><?php if($count>2):?><span class="showall button">Show All &darr;</span><?php endif;?>
<div class="add right button">+ Add</div>

<div class="replybox" style="<?php if($count>0) echo 'display:none';?>"><?php 
if ($social->state > 1):?>
	<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" class="commentform">
		<div class="thumbnail"></div>
		<div class="text">
		<a href="<?php echo admin_url('profile.php');?>"><?php echo $user_identity;?></a> just now:
		<textarea name="comment" class="commenttext" aria-required="true"></textarea>
		<input type='hidden' class='comment_post_ID' name='comment_post_ID' value='<?php echo $post->ID;?>'/>
		<input type='hidden' class='comment_parent' name='comment_parent' value='0' />
		<input name='submit' type="submit" class="submit" value="Submit Comment" /><span class="sendingcomment"></span>
		<input type='checkbox' name='privatemsg' value='1' /> Private message to author<br/>
		</div>
		<?php do_action('comment_form', $post->ID);?>
	</form><?php
else:
	echo $social->speechbubble();
endif;?>
</div><?php

// Calback for comments (display) - note: it's not proper to put this here, true. But it's purely VIEW code, so imho, it belongs here anyway.
if (!function_exists('cowobo_comments')) {
	function cowobo_comments($comment, $args, $depth) {
		global $author; $GLOBALS['comment'] = $comment; 
		$private = get_comment_meta($comment->comment_ID, 'privatemsg', true);
		if(!$author && $private == 1): echo ''; 
		else: //only show private comments if user is author;?>
		<li id="li-comment-<?php comment_ID(); ?>">
		<article class="comments" id="comment-<?php comment_ID(); ?>" >
			<footer class="comment-meta">
				<div class="comment-author vcard">
					<div class="reply right button">+ Reply</div>
					<div class="thumbnail"></div>
					<div class="text"><?php
						echo '<b><span class="authorlink">'.get_comment_author_link().'</span></b>, '.time_passed(strtotime($comment->comment_date));
						if($author) echo '<span class="deletemsg button"> (x)</span>';
						if($private == 1) echo '<span class="grey"> Private</span>';?><br/><?php
						comment_text();?>
					</div>
				</div>
			</footer>
		</article>
		</li><?php 
		endif;
	} 
};?>

<ol class="listbox <?php if($count>2):?>restrict<?php endif;?>" id="comments<?php echo $post->ID;?>"><?php
	// get all comments for the current lightbox
	if(!empty($postcomments)):
		wp_list_comments(array('login_text'=> 'Login to reply', 'type'=> 'comment', 'callback'=> 'cowobo_comments'), $postcomments);
	endif;?>
</ol>

</div>