<?php
get_header();
global $social;
$postids = array();?>

<!--[if IE 6]>
<?php include( TEMPLATEPATH . '/templates/unsupported.php');?>
<![endif]-->

<div class="page"><?php echo $nextlink;?>
<div class="scroller" style="<?php if(is_single()) echo 'display:none';?>"><?php
	include(TEMPLATEPATH.'/templates/newthumb.php');
	foreach($newposts as $post): $counter++;
		setup_postdata($post);
		$wp_query->in_the_loop = true;
		$typepost = cwob_get_category($post->ID);
		include(TEMPLATEPATH.'/templates/postthumb.php');
		$postids[] = $post->ID; //to determine whether or not to load the profile post
	endforeach;?>
</div>
</div>

<?php 
//load rrs and new post/profile boxes
$rsspost = get_posts(array('name' => 'stay-connected', 'showposts' => 1));
foreach($rsspost as $post): setup_postdata($post); $wp_query->in_the_loop = true;
	include(TEMPLATEPATH.'/templates/rssbox.php');
endforeach;

//load new post/profile boxes
include( TEMPLATEPATH . '/templates/newbox.php');
if ($social->state > 1 && !in_array( $social->profile_id, $postids ) ) :
	$post = get_post ( $social->profile_id );
	setup_postdata($post);
	$wp_query->in_the_loop = true;
	include(TEMPLATEPATH.'/templates/postbox.php');
else:
	include(TEMPLATEPATH.'/templates/newprofile.php');
endif;

//load marker posts
if(is_home()) $markercat = get_cat_id('Location'); else $markercat = $currentcat->term_id;
foreach (query_posts(array('cat'=>$markercat, 'numberposts'=>40)) as $post): setup_postdata($post);
	$relatedposts = new Cowobo_Feed(array('posts' => $post->ID));
	$postcount = count($relatedposts->get_related());
	$coordinates = get_post_meta($post->ID, 'coordinates', true);
	include(TEMPLATEPATH.'/templates/postbox.php');?>
	<div class="marker <?php echo $post->ID;?> hide">
		<input type="hidden" class="markerdata" value="<?php echo $coordinates;?>"/>
		<img src="<?php bloginfo('template_url');?>/images/angel.png" alt=""/>
		<div class="mtitle"><?php echo $postcount;?></div>
	</div><?php
endforeach;

//load other posts in current category
foreach($newposts as $post): setup_postdata($post); $wp_query->in_the_loop = true;
	$postcat = cwob_get_category($post->ID);		
	$posttype = $postcat->slug;
	if(is_single() or is_sticky()) $ajax = true; else $ajax = false; 
	if($posttype != 'location') include(TEMPLATEPATH.'/templates/postbox.php');
endforeach;

get_footer();