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
wp_enqueue_script('mainscript', get_bloginfo('template_url').'/js/script.js', array('jquery'));
wp_enqueue_script('horscroll', get_bloginfo('template_url').'/js/horscroll.js', array('mainscript'));
wp_enqueue_script('jqueryui', get_bloginfo('template_url').'/js/jquery-ui-1.8.16.custom.min.js', array('horscroll'));
wp_enqueue_script('hashchange', get_bloginfo('template_url').'/js/hashchange.min.js',array('jqueryui'),'',true);
wp_enqueue_script('autosize', get_bloginfo('template_url').'/js/autoresize.min.js',array('hashchange'),'',true);


// SETUP VARIABLES
global $wp_query;
global $newposts;
global $currentcat;
global $currentid;
global $currenttype;
global $social;
global $author;
global $query_string;

// if post was requested to be deleted do this first
wp_delete_post($_GET["deleteid"]);

// get current category 
if (is_home()):
	$catid = 0;
elseif ($catid = get_query_var('cat')):
	$currentcat = get_category($catid);
else:
	$cat = get_the_category($post->ID);
	$currentcat = $cat[0];
	$catid = $currentcat->term_id;
endif;

// get current type
$currenttype = cwob_get_type($currentcat->term_id);
// Query for the current feed

//$feed_query = ($catid = get_query_var('cat')) ? "'c',$catid" : "'p',".$post->ID;
$userid = wp_get_current_user()->ID;
$feed_query .= ",".$userid;

wp_head();

if(isset($_GET['user'])) $pagetitle = "Favourite Feed";
elseif(is_home()) $pagetitle= "<b>CODERS</b> WITHOUT <b>BORDERS</b>";
elseif(is_search()) $pagetitle = "<b>Search Results</b>";
elseif(is_404()) $pagetitle = "<b>Post not found</b>..is it one of these?";
else $pagetitle = '<b>'.$currentcat->name.'</b>';

$nextlink = next_posts($max_page, false);
if(empty($nextlink)) $backlink = '#';

// LOAD POSTS AND MENU LINKS
if(is_single()):
	$newposts = get_posts(array('cat'=>$catid));
	foreach(get_categories(array('child_of'=>$catid, 'hide_empty'=>false, 'parent'=>$catid)) as $cat):
		$links .= '<li><a href="'.get_category_link($cat->term_id).'">'.$cat->name.'</a></li>';
	endforeach;
	$currentid = $post->ID; //store main post id before it is overwritten by the loop
elseif (isset($_GET['userfeed'])):
	$args = array( 'userfeed' => $_GET['userfeed'] );
	$newposts = new Cowobo_Feed($args);
	$newposts = $newposts->get_feed();
	$links = '<a href="">This feed has no additional categories</a>';
else:
	$sort = '&orderby='.$_GET['sort'];
	$newposts = query_posts($query_string.$sort); //store posts in variable so we can use the same loop
	foreach(get_categories(array('child_of'=>$catid, 'hide_empty'=>false, 'parent'=>$catid, 'exclude'=>get_cat_ID('Uncategorized'),)) as $cat):
		$catposts = get_posts(array('cat'=>$cat->term_id, 'number'=>-1));
		$links .= '<li><a href="'.get_category_link($cat->term_id).'">'.$cat->name.'</a></li>';
	endforeach;
endif;
// Sort the query if needed
if ( isset ( $_GET['sort'] ) && ! empty ( $_GET['sort'] ) ) :
	$newposts = $social->sort_posts( $newposts, $_GET['sort'] );
endif;?>

</head>

<body>

<div id="menubar">
	<div id="profilelink"><?php
	if ($social->state == 1 ) : ?>
		<a href="<?php echo get_category_link(get_cat_ID('Cowobo Tour'));?>">Take A Tour!</a></span>
		<span class="messenger join">Login</span><?php
	elseif ($social->state != 4) : ?>
		<span class="messenger create_new_profile profile-<?php echo $social->profile_id; ?>">Create Profile</span>
		<a id="logout" href="<?php echo wp_logout_url( home_url() ); ?>" title="Logout">Logout</a><?php
	else : ?>
		<span class="messenger edit_profile profile-<?php echo $social->profile_id;?>">Update Profile</span>
		<a id="logout" href="<?php echo wp_logout_url( home_url() ); ?>" title="Logout">Logout</a><?php
	endif;?>
	</div>
	
	<div id="menu"><ul>
		<li>
			<span id="homebutton" onclick="document.location.href='<?php bloginfo('url');?>'">Home</span>
		</li>
		<li>Filter
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
				<li>Keywords <input type="text" id="keywords" value=""><button type="submit" name="submit" class="searchbutton"></button></li>
				<li>Address <input type="text" id="address" value=""><button type="submit" name="submit" class="searchbutton"></button></li>
			</ul>
		</li>
		<li>Zoom
			<ul>
				<li class="zoomin">In</li>
				<li class="zoomout">Out</li>		
			</ul>
		</li>
	</ul></div>
</div>

<div id="map"></div>


<div class="marker" id="editmarker">
	<div class="mcontent">
		<span class="savelocation">Save</span>
		<span class="cancellocation">Cancel</span>
	</div>
	<img src="<?php echo get_bloginfo('template_url').'/images/smallarrow.png';?>" alt=""/>
</div>

<div id="page">
	<div id="pagetitle" class="<?php echo $currentcat->term_id;?>"><?php echo $pagetitle;?><span class="largerss"></span>
	<span id='pagination'><?php cowobo_pagination();?></span>
	</div>

	<div id='speaking_angel'>
		<div id="angel"></div>
		<div class="speechbubble right">
			<span class="close" onclick='jQuery(".speechbubble").fadeOut();'>close</span>
			<span id='speech'></span>
		</div>
	</div>