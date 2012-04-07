<?php
/**
 * Constructs the Cowobo feed
 *
 * Syntax: <?php $posts = new Cowobo_Feed ( $args, $wp_query ); ?>
 * To get the feed sorted and arranged, use $posts->get_feed();
 *
 * @param arr Arguments:  'posts' => [ array or single post ], 'cats'=> [ array, comma separated or single category ] , 'users' => [ array or single user id ] (fetches personal feed), 'profile' => [ array or single user id ] (fetches profile feed), 'sort' => [ ASC or DESC ]
 * @param arr $wp_query = [ Normal WordPress query ]
 */

class Cowobo_Feed {

	public function __construct($args, $wp_query = false) {
		//  Get all queried posts (array || single)
		if ( is_array($args['posts']) ) {
			foreach($args['posts'] as $postid) {
				$this->get_post_feed($postid);
			}
		}
		elseif (!empty($args['posts'])) $this->get_post_feed($args['posts']);

		//  Get all queried cats (array || comma separated || single)
		if ( is_array($args['cats']) ) {
			foreach($args['cats'] as $catid) {
				$this->get_cat_feed($catid);
			}
		}
		elseif (!empty($args['cats'])) $this->get_cat_feed($args['cats']);

		//  Get all queried personal feeds (array || single)
		if ( is_array($args['users']) ) {
			foreach($args['users'] as $uid) {
				$this->get_user_feed($uid);
			}
		}
		elseif ( !empty($args['users']) ) $this->get_user_feed($args['users']);

        //  Get all queried profile feeds (array || single)
		if ( is_array($args['profile']) ) {
			foreach($args['profile'] as $uid) {
				$this->get_profile_feed($uid);
			}
		}
		elseif ( !empty($args['profile']) ) $this->get_user_feed($args['profile']);

		// Merge optional wp_query
		if ( $wp_query ) {
			$newposts = query_posts( $wp_query );
			if (empty($this->feedposts)) $this->feedposts = $newposts;
			else $this->feedposts = array_merge($newposts, $this->feedposts);
		}

		// If we get no query or got no results, return the wiki [temporary]
		if ( empty($this->feedposts) ) $this->get_cat_feed(1);

		// Set the description and titles
		$this->feed_title = "Cowobo Favourite Feed";
		$this->feed_description = $this->get_titles();

		// Set sorting direction
		if (empty($args['sort'])) $args['sort'] = 'DESC';
		$this->direction = $args['sort'];
	}

	/**
     * Feed for users (personal feed)
     * @param int $userid
     * @return null|boolean
     */
	function get_user_feed( $userid ) {
		$feed_query = get_user_meta($userid,'cowobo_feed',true);
		if (!is_array($feed_query)) return null;
		// Stored as type => id  ('p' or 'c')
		foreach ($feed_query as $query) {
			if (key($query) == 'p') $this->get_post_feed(current($query));
			elseif (key($query) == 'c') $this->get_cat_feed(current($query));
		}
		return true;
	}

	/**
     * Feed for profiles
     * @param int $userid
     * @return null|boolean
     */
	function get_profile_feed( $userid ) {
		$feed_query = get_user_meta($userid,'cowobo_profilefeed',true);
		if (!is_array($feed_query)) return null;

		foreach ($feed_query as $query) {
			$this->get_post_feed( $query );
		}
		return true;
	}

    /**
     * Feed for posts
     *
     * @param int $postid
     * @return boolean
     */
	function get_post_feed( $postid ) {
		// Get the array of related posts
		$newposts =  Cowobo_Related_Posts::cwob_get_related_posts($postid);

		// If there are no related posts, only return this one post
		if (empty($newposts)) $newposts = array(get_post($postid));
		else $this->related_posts = $newposts;

		// Merge with posts already there
		if (empty($this->feedposts)) $this->feedposts = $newposts;
		else $this->feedposts = array_merge($newposts, $this->feedposts);

		// Set feed title and URL
		$this->feed_titles[] = get_the_title($postid);
		return true;
	}

    /**
     * Feed for categories
     *
     * @param obj $cats
     * @return boolean
     */
	function get_cat_feed($cats) {
		$args = array(
			'category'        => $cats,
			'numberposts' => 10,
			'orderby'         => 'post_date',
			'order'             => 'ASC',
			'post_type'      => 'post'
		);

		// Get posts and merge
		if (empty($this->feedposts))
			$this->feedposts = get_posts($args);
		else $this->feedposts = array_merge(get_posts($args), $this->feedposts);

		// Get the titles
		$cat = explode(',',$cats);
		foreach ( $cat as $c ) {
			$this->feed_titles[] = get_the_category_by_ID( $c );
		}
		return true;
	}

    /**
     * Convert PHP timestamp to RSS
     *
     * @param int $timestamp
     */
	function rss_date( $timestamp = null ) {
	  $timestamp = ($timestamp==null) ? time() : $timestamp;
	  echo date(DATE_RSS, $timestamp);
	}

	/**
     * Handle the limit on text length
     *
     * @param str $string
     * @param int $length
     * @param str (optional) $replacer
     * @return str
     */
	function rss_text_limit($string, $length, $replacer = '...') {
	  $string = strip_tags($string);
	  if(strlen($string) > $length)
	    return (preg_match('/^(.*)\W.*$/', substr($string, 0, $length+1), $matches) ? $matches[1] : substr($string, 0, $length)) . $replacer;
	  return $string;
	}

	/**
     * Add image where available
     * @param int $postid
     */
	function add_image($postid) {
		$img = cwob_get_first_image($postid);
		if($img) {
			$image = '<img align="left" src="'. $img .'" />';
			echo $image;
		}
	}

	/**
     * Return the feed sorted and cleaned of doubles
     *
     * @return arr
     */
	function get_feed() {
		$postlist = remove_doubles($this->feedposts);
		return array_object_sort( $postlist, 'post_date_gmt', $this->direction );
	}

	/**
     * Returns related (default = unsorted)
     * @param bool (optional) $sort
     * @return arr related posts
     */
	function get_related($sort = false) {
		if ( !$this->related_posts ) return false;
		if ($sort) {
			$postlist = remove_doubles($this->related_posts);
			return array_object_sort( $postlist, 'post_date_gmt', $this->direction );
		}
		else return $this->related_posts;
	}

	/**
     * Gives the position of the last post in the array
     *
     * @return int
     */
	function last_post() {
		return count($this->feedposts) - 1;
	}

	// Make pretty sentence of all the titles.
	function get_titles() {
		$feed_title = '';
		$count = 0;
		$title_count = count($this->feed_titles);
		if ( $title_count < 2 ) return $this->feed_titles[0];
		foreach ($this->feed_titles as $titleobject) {
			if ($count == $title_count - 1) $feed_title .= " and ";
			elseif (0 < $count) $feed_title .= ", ";
			$feed_title .= $titleobject;
			$count++;
		}
		return $feed_title;
	}
}
?>
