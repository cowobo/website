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
if (isset($_GET['userfeed'])):
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
endif;

if ($social->state == 1 ) : 
	$profile = '<li class="messenger join">Create Profile</li>';
	$loginout = '<li class="messenger join">Login</li>';
else: 
	$profile = '<li class="messenger create_new_profile profile-'.$social->profile_id.'">Update Profile</li>';
	$loginout = '<li><a id="logout" href="'.wp_logout_url(home_url()).'" title="Logout">Logout</a></li>';
endif;
?>
		
</head>

<body>

<div id="menubar">	
	<div id="menu"><ul class="menu">
		<li>
			<span id="homebutton" onclick="document.location.href='<?php bloginfo('url');?>'">Home</span>
		</li>
		<li><b>Profile</b>
			<ul>
				<?php echo $profile;?>
				<?php echo $loginout;?>
			</ul>
		</li>
		<li><b>Filter</b>
			<ul>
				<?php echo $links;?>
			</ul>
		</li>
		<li><b>Sort</b>
			<ul>
				<li><a href="?sort=popularity">Most Popular</a></li>
				<li><a href="?sort=comments">Most Commented</a></li>
				<li><a href="?sort=editor">Editors Choice</a></li>
				<li><a href='?sort=random'>Random</a></li>
			</ul>
		</li>
		<li><b>Search</b>
			<ul class="searchmenu">
				<li>
					<form method="get" action="<?php bloginfo('url'); ?>/">
					Keywords <input type="text" name="s" class="searchform" id="searchform"/>
					<button type="submit" name="submit" class="searchbutton keywords"></button>
					</form>
				</li>
				<li>
					Address <input type="text" class="searchform" value=""/>
					<span class="searchbutton address"></span>
				</li>
			</ul>
		</li>
		<li><b>Map</b>
			<ul class="mapmenu">
				<li class="mapin">Zoom In</li>
				<li class="mapout">Out</li>
				<li class="mapleft">Left</li>
				<li class="mapright">Right</li>
				<li class="mapup">Up</li>
				<li class="mapdown">Down</li>
				<li class="labels">Labels On</li>
			</ul>
		</li>
		
		<li><span class="maploading">Loading map...</span></li>	
		<?php //echo $loginout;?>
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
	<div id="pagetitle" class="<?php echo $currentcat->term_id;?>">
		<?php echo $pagetitle;?>
		<span class="largerss"></span>
		<span id='pagination'><?php cowobo_pagination();?></span>
	</div>

	<div id='speaking_angel'>
		<div id="angel"></div>
		<div class="speechbubble right">
			<span class="close" onclick='jQuery(".speechbubble").fadeOut();'>close</span>
			<span id='speech'></span>
		</div>
	</div>