<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
<meta name="generator" content="Dev-PHP 2.4.0" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<link rel="shortcut icon" href="<?php bloginfo('template_url');?>/images/favicon.ico" />
<link rel="icon" type="image/gif" href="<?php bloginfo('template_url');?>/images/animated_favicon1.gif" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url') ?>/style.css" media="screen"/>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" /><?php

wp_enqueue_script("jquery");
wp_enqueue_script('comment-reply');
wp_enqueue_script('mainscript', get_bloginfo('template_url').'/js/script.js', array('jquery'));
wp_enqueue_script('horscroll', get_bloginfo('template_url').'/js/horscroll.js', array('mainscript'));
wp_enqueue_script('jqueryui', get_bloginfo('template_url').'/js/jquery-ui-1.8.16.custom.min.js', array('horscroll'));
wp_enqueue_script('autosize_js', get_bloginfo('template_url').'/js/autoresize.jquery.min.js',array('jqueryui'),'',true);

// SETUP VARIABLES
global $wp_query;
global $newposts;
global $currentcat;
global $postid;
global $social;

// Query for the current feed
$feed_query = ($catid = get_query_var('cat')) ? "'c',$catid" : "'p',".$post->ID;
$userid = wp_get_current_user()->ID;
$feed_query .= ",".$userid;

if (is_home()):
	$catid = 0;
elseif ($catid = get_query_var('cat')):
	$currentcat = get_category($catid);
else:
	$cat = get_the_category($post->ID);
	$currentcat = $cat[0];
endif;

wp_head();

if(isset($_GET['user'])) $pagetitle = "Favourite Feed";
elseif(is_home()) $pagetitle= "<b>CODERS</b> WITHOUT <b>BORDERS</b>";
elseif(is_search()) $pagetitle = "Search Results";
elseif(is_404()) $pagetitle = "Post not found..is it one of these?";
else $pagetitle = $currentcat->name;

$nextlink = next_posts($max_page, false);
if(empty($nextlink)) $backlink = '#';

// if post was requested to be deleted do this first
wp_delete_post($_GET["deleteid"]);

// LOAD POSTS AND MENU LINKS
// if is page display only child pages
if(is_author()):
	$curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
	$args = array( 'posts' => $curauth->cowobo_profile );
	$wp_query = array('post_type'=>'page', 'author' => $curauth->ID, 'order' => 'ASC', 'exclude' => $curauth->cowobo_profile );
	$newposts = new Cowobo_Feed ( $args, $wp_query );
	$newposts = $newposts->get_feed();
elseif(is_single()):
	$newposts = get_posts(array('cat'=>$currentcat->term_id));
	foreach(get_categories(array('child_of'=>$currentcat->term_id, 'hide_empty'=>false, 'parent'=>$currentcat->term_id)) as $cat):
		$links .= '<li><a href="'.get_category_link($cat->term_id).'">'.$cat->name.' ('.$cat->category_count.')</a></li>';
	endforeach;
	$postid = $post->ID; //store main post id before it is overwritten by the loop
elseif (isset($_GET['user'])):
	$args = array( 'users' => $_GET['user'] );
	$newposts = new Cowobo_Feed($args);
	$newposts = $newposts->get_feed();
	$links = '<a href="">This feed has no additional categories</a>';
else:
	global $query_string;
	$sort = '&orderby='.$_GET['sort'];
	$newposts = query_posts($query_string.$sort); //store posts in variable so we can use the same loop
	foreach(get_categories(array('exclude'=>get_cat_ID('Uncategorized'), 'child_of'=>0, 'hide_empty'=>false, 'parent'=>0)) as $cat):
		$catposts = get_posts(array('cat'=>$cat->term_id, 'number'=>-1));
		$links .= '<li><a href="'.get_category_link($cat->term_id).'">'.$cat->name.' ('.count($catposts).')</a></li>';
	endforeach;
endif;

// Sort the query if needed
if ( isset ( $_GET['sort'] ) && ! empty ( $_GET['sort'] ) ) :
	$newposts = $social->sort_posts( $newposts, $_GET['sort'] );
endif;?>

</head>

<body>

<div id="map"></div>
<div class="zoom"><div class="zoomin"></div><div class="zoomout"></div></div>
<div class="showlabels">Labels on/off</div>
<div class="marker" id="editmarker">
	<div class="mcontent">
		<span class="savelocation">Save</span>
		<span class="cancellocation">Cancel</span>
	</div>
	<img src="<?php echo get_bloginfo('template_url').'/images/smallarrow.png';?>" alt=""/>
</div>

<div id="page">
	<h1 id="<?php echo $currentcat->term_id;?>"><b><?php echo $pagetitle;?></b><?php
	if (is_user_logged_in()) :
		if (isset($_GET['user'])) :
			$rsspage = get_page_by_title('Favourite Feed');?>
			<a href="<?php echo get_permalink($rsspage->ID);?>?user=<?php echo $userid; ?>" class="rss">RSS</a>
			<a onclick="reset_feed(<?php echo $userid; ?>)" class="rss">Reset Feed</a><?php
		else:?>
			<a onclick="add_to_feed(<?php echo $feed_query; ?>)" class="rss">Subscribe to Feed</a><?php
		endif;
	else:?>
		<span id="taketour">Take the Tour!</span><?php
	endif; ?></h1>

	<div id='speaking_angel'>
		<div id="angel"></div>
		<div class="speechbubble right">
			<span class="close" onclick='jQuery(".speechbubble").fadeOut();'>close</span>
			<span id='speech'>
				<?php echo $social->speechbubble(); ?>
			</span>
		</div>
	</div>

	<div id="profilelink">
        <?php
            /**
             * @todo make the links for new profile and edit profile
             * @todo restyle speechbubble
             */
            $state = $social->state;
            if ( $state == 1 ) : ?>
                <a href="#" class="messenger new_profile">Login / Join us!</a><?php
            elseif ( $state != 4 ): ?>
                <a href="#" class="messenger create_new_profile profile<?php echo $social->get_profile_url();?>">Create Your Profile</a><?php
            else : ?>
                <a href="#" class="messenger edit_profile profile<?php echo $social->get_profile_url();?>">Create Your Profile</a><?php
            endif;
        ?>
	</div>

	<div id="menubar">
	<ul id="menu">
		<li>
			<span id="backbutton">Back</span>
		</li>
		<li>Browse
			<ul>
				<?php echo $links;?>
			</ul>
		</li>
		<li>Sort
			<ul>
				<li><a href="?sort=popularity">Most Popular</a></li>
				<li><a href="?sort=comments">Most Commented</a></li>
				<li><a href="?sort=editor">Editors Choice</a></li>
				<li><a href='?sort=random'>Random</a></li>
			</ul>
		</li>
		<li>Search
			<ul>
				<li><input type="text" id="keywords" value=""><button type="submit" name="submit" id="searchbutton"></button></li>
			</ul>
		</li>
		<li>
			<span id="homebutton" onclick="document.location.href='<?php bloginfo('url');?>'">Home</span>
		</li>
	</ul>
	</div>