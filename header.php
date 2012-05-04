<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/1">
<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
<meta name="generator" content="Dev-PHP 2.4.0" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<meta name="rooturl" content="<?php bloginfo('url'); ?>/"/>
<link rel="shortcut icon" href="<?php bloginfo('template_url');?>/images/favicon.ico" />
<link rel="icon" type="image/gif" href="<?php bloginfo('template_url');?>/images/animated_favicon1.gif" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url') ?>/style.css" media="screen"/>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" /><?php

wp_enqueue_script("jquery");
wp_enqueue_script('hashchange', get_bloginfo('template_url').'/js/hashchange.min.js',array('jquery'),'',true);
wp_enqueue_script('mainscript', get_bloginfo('template_url').'/js/script.js', array('hashchange'));
wp_enqueue_script('horscroll', get_bloginfo('template_url').'/js/horscroll.js', array('mainscript'));
wp_enqueue_script('autosize', get_bloginfo('template_url').'/js/autoresize.min.js',array('horscroll'),'',true);
wp_enqueue_script('richtext', get_bloginfo('template_url').'/js/jquery.rte.js',array('autosize'),'',true);

// SETUP VARIABLES
global $wp_query;
global $newposts;
global $currentcat;
global $currenttype;
global $social;
global $author;
global $query_string;
global $paged;

//load the thickbox for users that need to edit_bookmark_link(
if($social->state > 1):
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	wp_enqueue_script('media-upload');
endif;

// if post was requested to be deleted do this first
wp_delete_post($_GET["deleteid"]);

// get current category
$current_category = cowobo_get_current_category();
extract ( $current_category );

// get current type
$currenttype = cwob_get_type($currentcat->term_id);
// Query for the current feed

//$feed_query = ($catid = get_query_var('cat')) ? "'c',$catid" : "'p',".$post->ID;
$userid = wp_get_current_user()->ID;
$feed_query .= ",".$userid;

//get profileid;
if($social->state != 1) $profileid = $social->profile_id; 
else $profileid = 'newprofile';

wp_head();

$pagetitle = cowobo_get_pagetitle($currentcat);
if($wp_query->max_num_pages != $paged):
	$nextlink = '<a class="nextposts hide" href="'.next_posts($max_page, false).'"></a>';
endif;

// LOAD POSTS AND MENU LINKS
if ( $userfeed = is_userfeed() ) :
	$args = array( 'users' => $userfeed->ID );
	$newposts = new Cowobo_Feed($args);
	$newposts = $newposts->get_feed();
	$links = '<a href="">None</a>';
elseif ( is_cowobo_profile( $currentcat ) ) :
    $profile_user_id = $social->get_user_from_profile_id ( $wp_query->queried_object_id  );
    $profile_feed = get_user_meta($profile_user_id, 'cowobo_profilefeed', true );
    if ( ! is_array ( $profile_feed ) ) $profile_feed = array();
    $profile_feed[] = $profile_user_id;
    $newposts = query_posts(array ( 'post__in' => $profile_feed, 'sort' => 'DESC', 'orderby'=>'modified' ) );
else:
    if ( is_404() ) :
        $query_string= '';
        $_GET['sort'] = 'random';
    endif;
	if($_GET['sort'])$sort = '&orderby='.$_GET['sort'];
	else $sort = '&orderby=modified';
	$newposts = query_posts($query_string.$sort); //store posts in variable so we can use the same loop
	foreach(get_categories(array('child_of'=>$catid, 'hide_empty'=>false, 'parent'=>$catid, 'exclude'=>get_cat_ID('Uncategorized'),)) as $cat):
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

<div class="topmenu">
	<ul class="topleft menu">
		<li class="catrss">
			<ul>
		  		<li><a href='<?php echo PERSONALFEEDURL . '/' . wp_get_current_user()->user_login; ?>'>Add to Personal feed</a></li>
			</ul>
		</li>
		<li class="pagetitle"><?php echo $pagetitle;?>
			<ul><?php echo $links;?></ul>
		
		</li>		
		<li class="sort"><b>Sort</b>
			<ul>
				<li><a href="?sort=popularity">Most Popular</a></li>
				<li><a href="?sort=comments">Most Commented</a></li>
				<li><a href="?sort=editor">Editors Choice</a></li>
				<li><a href='?sort=random'>Random</a></li>
			</ul>
		</li>

		<li class="search"><b>Search</b>
			<ul>
				<li>
					<form method="get" action="<?php bloginfo('url'); ?>/">
					Keywords <input type="text" name="s" class="searchform" id="searchform"/>
					<button type="submit" name="submit" class="searchbutton keywords"></button>
					</form>
				</li>
				<li>
					Location <input type="text" class="searchform" value=""/>
					<span class="searchbutton address"></span>
				</li>
			</ul>
		</li>
		<li class="zoomlevels"><b>Zoom</b>
			<ul>
				<li class="zoom level-3 zoomselect">1</li>
				<li class="zoom level-5">2</li>
				<li class="zoom level-7">3</li>
				<li class="zoom level-9">4</li>
				<li class="zoom level-11">5</li>
				<li class="zoom level-13">6</li>
				<li class="zoom level-15">7</li>
				<li class="zoom level-17">8</li>
			</ul>
		</li>
		<li><span class="maploading loadicon">Loading...</span></li>
	</ul>
	<ul class="topright menu">
		<li class="home">
			<span class="homebutton">Home</span>
			<div class="droparrow"></div>
			<ul>
				<?php if ($social->state != 1):?>
				<li><a id="logout" href="<?php echo wp_logout_url(home_url());?>" title="Logout">Logout</a></li><?php
				endif;?>
				<li>Disclaimer</li>
				<li>Account Settings</li>
			</ul>
		</li>
		<li class="profile <?php echo $profileid;?>">Your Profile</li>
	</ul>
</div>

<div class="map">
	<div class="maplayer" style="display:block">
		<div class="mainmap">
			<img class="buffer" src="http://maps.googleapis.com/maps/api/staticmap?maptype=satellite&sensor=false&size=640x320&format=jpg&zoom=2&center=15.49860809171295,10.932544165625036" alt=""/>
		</div>
		<div class="mapshadow"></div>
	</div>
	<div class="pan moveleft"><div></div></div>
	<div class="pan moveright"><div></div></div>
	<div class="pan moveup"><div></div></div>
	<div class="pan movedown"><div></div></div>
</div>

<div class="marker editmarker">
	<img src="<?php bloginfo('template_url');?>/images/angel.png" alt=""/>
	<div class="mtitle"><span class="savelocation">Save</span></div>
</div>

<div class="scrollarrow">
<div class="scrollicon"></div>older posts
</div>

<img src="<?php bloginfo('template_url');?>/images/largearrow.png" class="hide"/><?php 
echo $nextlink;?>