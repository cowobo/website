<?php
/*
 *      class-cowobo-social.php
 *
 *      Copyright 2012 Coders Without Borders
 *
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 *
 *
 */

/**
 * This class regulates all front end user interaction
 *
 * @package cowobo-social
 */

include_once('class-cowobo-social-options.php');

class Cowobo_Social {

	public $show_bubble = true;

    /**
     * Gives the user state, 1-4.
     *
     * State 1: not logged in.
     * State 2: first login
     * State 3: logged in, no profile
     * State 4: Registration complete: logged in with profile.
     *
     * @var int
     */
    public $state;

    /**
     * Gives the profile id of the logged in user
     *
     * @var int
     */
    public $profile_id;
    /**
     * Constructor for cowobo social
     *
     * @param boolean $skip_construct true skips constructor
     */
	public function __construct( $skip_construct = false ) {
		if ( $skip_construct ) return;
		if ( is_admin ) {
			$options = new Cowobo_Social_Options();
			add_action('admin_menu',array( &$options, 'add_menu_pages' ));
			unset($options);
		}

		// Profile nag
		$userid = wp_get_current_user()->ID;

        $this->set_cowobo_state();

		$this->profile_id = get_user_meta($userid, 'cowobo_profile', true);
		if ( $this->state == '4' ) $this->show_bubble = false;
		else { // Move away from state 4 (logged in with profile)
			add_action('profile_update',array( &$this, 'complete_register'));
		}
		// New user profile page
		add_action('user_register',array( &$this, 'new_user_profile'));

		// RSS
		add_filter('pre_get_posts',array( &$this, 'feed_filter' ));

		// Give the profile a fancy link ( profile/username )
		/*global $wp_rewrite;
		$wp_rewrite->author_base = 'profile'; */

		// Share
		add_action('init', array ( &$this, 'share_scripts') );
		//add_action ( 'wp_footer',array ( &$this, 'total_sharing' ) );

		// Schedule share cron job
		add_action('daily_events', array ( &$this, 'update_total_count' ) );
	}

	/* === User Profiles === */
	/**
     * Creates the new user profile
     *
     * @param int $userid
     * @return int profile id
     */
	public function new_user_profile ( $userid ) {
		$user = get_userdata($userid);
		if ( ! empty($user->cowobo_profile) ) return false;
		if ( ! wp_update_user ( array ( 'ID' => $userid, 'role' => 'author' ) ) ) return false;
		$post = array(
			  'post_author' => $userid,
			  'post_category' => array(get_term_by('name', 'profile', 'category')->term_id),
			  'post_content' => "Another Coders Without Borders profile.",
			  'post_name' => $user->user_login,
			  'post_status' => 'draft',
			  'post_title' => $user->display_name,
			  'post_type' => 'post'
		);

		if ( !$profileid = wp_insert_post($post) ) return false;
		if ( !update_user_meta( $userid, 'cowobo_profile', $profileid) ) return false;
		return $profileid;
	}

	/* === RSS === */
    /**
     * General filter calling all the RSS mods
     *
     * @param type $query
     * @return type
     */
	public function feed_filter($query) {
		if ($query->is_feed) {
			add_filter('the_content', array( &$this, 'feedContentFilter' ) );
		}
		return $query;
	}

    /**
     * Add query to user feed
     *
     * @param int $userid
     * @param int $feed_query
     * @return boolean true on success
     */
	public function add_to_feed($userid,$feed_query) {
		$currentfeed = get_user_meta($userid,'cowobo_feed',true);
		if (empty($currentfeed)) $currentfeed = array();
		$currentfeed[] = $feed_query;
		update_user_meta($userid,'cowobo_feed',$currentfeed);
		return true;
	}

    /**
     * Full reset on user's personal feed
     *
     * @param int $userid
     * @return boolean true on success
     */
	public function reset_feed($userid) {
		update_user_meta($userid,'cowobo_feed', '');
		return true;
	}

	/**
     * Add thumbs to feed
     *
     * @param object $content
     * @return object content with image
     */
	public function feedContentFilter($content) {
		$img = cwob_get_first_image($post->ID);
		if($img) {
			$image = '<img align="left" src="'. $img .'" alt="" />';
			echo $image;
		}
		return $content;
	}

    /**
     * Does the state magic
     *
     * @return int $state
     */
    protected function set_cowobo_state () {
        if ( is_user_logged_in() ) {
            $registered_state = get_user_meta($userid, 'cowobo_state', true);
            if ( empty ( $registered_state ) ) $this->state = 2;
            else $this->state = $registered_state;
        } else {
            $this->state = 1;
        }
        return $tis->state;
    }

	/* === Add your profile === */
	/**
     * The profile nags
     *
     * @return str $profile_nag
     */
	public function speechbubble() {
		$nags = get_option('cwob_nags');
		if ( $this->state == 2 || $this->state == 3 ) { // State 2 & 3 (logged in, no profile)
			$userid = wp_get_current_user()->ID;
			$current_display_name = get_userdata($userid)->display_name;
			$current_profile_url = $this->get_profile_url ( $userid );
			$profile_nag = stripslashes ( strtr ( $nags[$this->state], array ( 'DISPLAYNAME' => $current_display_name, 'PROFILEURL' => $current_profile_url ) ) );
			if ( $this->state == 2 ) update_user_meta($userid,'cowobo_state','3');
		}
		else {	// State 1: Not logged in
			$profile_nag = stripslashes ( strtr ( $nags[1], array ('COWOBOCONNECT' => $this->cowobo_connect() ) ) );
		}
		return $profile_nag;
	}

    public function get_profile_url ( $userid ) {
        $ret = get_bloginfo('siteurl') . "/category/coders/" . get_userdata($userid)->user_display_name;
        return $ret;
    }

	/**
     * Completes registration and stops showing the nags
     *
     * @param int $userid
     */
	public function complete_register($userid) {
		if (get_user_meta($userid, 'cowobo_state', true) == '3') update_user_meta($userid,'cowobo_state','4');
	}

	/* === Social login === */
	/**
     * Social login buttons (extension of Social Connect plugin)
     * @param array $args
     * @return str The social connect html
     */
	public function cowobo_connect( $args = NULL ) {

		if( $args == NULL )
			$display_label = true;
		elseif ( is_array( $args ) )
			extract( $args );

		if( !isset( $images_url ) )
			$images_url = SOCIAL_CONNECT_PLUGIN_URL . '/media/img/';

		$twitter_enabled = get_option( 'social_connect_twitter_enabled' ) && get_option( 'social_connect_twitter_consumer_key' ) && get_option( 'social_connect_twitter_consumer_secret' );
		$facebook_enabled = get_option( 'social_connect_facebook_enabled', 1 ) && get_option( 'social_connect_facebook_api_key' ) && get_option( 'social_connect_facebook_secret_key' );
		$google_enabled = get_option( 'social_connect_google_enabled', 1 );
		$yahoo_enabled = get_option( 'social_connect_yahoo_enabled', 1 );
		$wordpress_enabled = get_option( 'social_connect_wordpress_enabled', 1 );
		ob_start();
		?>
		<div class="cowobo_connect <?php if( strpos( $_SERVER['REQUEST_URI'], 'wp-signup.php' ) ) echo 'mu_signup'; ?>">
			<div class="social_connect_form" title="Social Connect">
				<?php if( $facebook_enabled ) : ?>
					<a href="javascript:void(0);" title="Facebook" class="social_connect_login_facebook"><img alt="Facebook" src="<?php echo $images_url . 'facebook_32.png' ?>" /></a>
				<?php endif; ?>
				<?php if( $twitter_enabled ) : ?>
					<a href="javascript:void(0);" title="Twitter" class="social_connect_login_twitter"><img alt="Twitter" src="<?php echo $images_url . 'twitter_32.png' ?>" /></a>
				<?php endif; ?>
				<?php if( $google_enabled ) : ?>
					<a href="javascript:void(0);" title="Google" class="social_connect_login_google"><img alt="Google" src="<?php echo $images_url . 'google_32.png' ?>" /></a>
				<?php endif; ?>
				<?php if( $yahoo_enabled ) : ?>
					<a href="javascript:void(0);" title="Yahoo" class="social_connect_login_yahoo"><img alt="Yahoo" src="<?php echo $images_url . 'yahoo_32.png' ?>" /></a>
				<?php endif; ?>
				<?php if( $wordpress_enabled ) : ?>
					<a href="javascript:void(0);" title="WordPress.com" class="social_connect_login_wordpress"><img alt="Wordpress.com" src="<?php echo $images_url . 'wordpress_32.png' ?>" /></a>
				<?php endif; ?>
			</div>

			<?php
		$social_connect_provider = isset( $_COOKIE['social_connect_current_provider']) ? $_COOKIE['social_connect_current_provider'] : '';

	?>
		<div id="social_connect_facebook_auth">
			<input type="hidden" name="client_id" value="<?php echo get_option( 'social_connect_facebook_api_key' ); ?>" />
			<input type="hidden" name="redirect_uri" value="<?php echo urlencode( SOCIAL_CONNECT_PLUGIN_URL . '/facebook/callback.php' ); ?>" />
		</div>
		<div id="social_connect_twitter_auth"><input type="hidden" name="redirect_uri" value="<?php echo( SOCIAL_CONNECT_PLUGIN_URL . '/twitter/connect.php' ); ?>" /></div>
		<div id="social_connect_google_auth"><input type="hidden" name="redirect_uri" value="<?php echo( SOCIAL_CONNECT_PLUGIN_URL . '/google/connect.php' ); ?>" /></div>
		<div id="social_connect_yahoo_auth"><input type="hidden" name="redirect_uri" value="<?php echo( SOCIAL_CONNECT_PLUGIN_URL . '/yahoo/connect.php' ); ?>" /></div>
		<div id="social_connect_wordpress_auth"><input type="hidden" name="redirect_uri" value="<?php echo( SOCIAL_CONNECT_PLUGIN_URL . '/wordpress/connect.php' ); ?>" /></div>

		<div class="social_connect_wordpress_form" title="WordPress">
			<p><?php _e( 'Enter your WordPress.com blog URL', 'social_connect' ); ?></p><br/>
			<p>
				<span>http://</span><input class="wordpress_blog_url" size="15" value=""/><span>.wordpress.com</span> <br/><br/>
				<a href="javascript:void(0);" class="social_connect_wordpress_proceed"><?php _e( 'Proceed', 'social_connect' ); ?></a>
			</p>
		</div>
	</div> <!-- End of social_connect_ui div -->
	<?php return ob_get_clean();
	}

    /**
     * Echos the share buttons
     *
     * @param int $post_id
     */
	public function show_share($post_id) {
		$option = $this->share_options();
		$post_link = get_permalink( $post_id );
		$post_title = get_the_title ( $post_id );

		$output = '';
		if ($option['active_buttons']['facebook_like']==true) {
			$output .= '
				<div style="display:inline-block; width:' .$option['facebook_like_width']. 'px;padding-right:10px; margin:4px;height:30px;">
				<iframe src="http://www.facebook.com/plugins/like.php?href=' . urlencode($post_link) . '&amp;layout=button_count&amp;show_faces=false&amp;width='.$option['facebook_like_width'].'&amp;action=like&amp;font=verdana&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width='.$option['facebook_like_width'].'px; height:21px;" allowTransparency="true"></iframe></div>';
		}

		if ($option['active_buttons']['google_plus']==true) {
			$data_count = ($option['google_count']) ? '' : 'count="false"';
			$output .= '
				<div style="display:inline-block; width:' .$option['google_width']. 'px;padding-right:10px; margin:4px;height:30px;">
				<g:plusone size="medium" href="' . $post_link . '"'.$data_count.'></g:plusone>
				</div>';
		}

		if ($option['active_buttons']['twitter']==true) {
			$data_count = ($option['twitter_count']) ? 'horizontal' : 'none';
			if ($option['twitter_id'] != ''){
				$output .= '
					<div style="display:inline-block; width:' .$option['twitter_width']. 'px;padding-right:10px; margin:4px;height:30px;">
					<a href="http://twitter.com/share" class="twitter-share-button" data-url="'. $post_link .'"  data-text="'. $post_title . '" data-count="'.$data_count.'" data-via="'. $option['twitter_id'] . '">Tweet</a>
					</div>';
			} else {
				$output .= '
					<div style="display:inline-block; width:' .$option['twitter_width']. 'px;padding-right:10px; margin:4px;height:30px;">
					<a href="http://twitter.com/share" class="twitter-share-button" data-url="'. $post_link .'"  data-text="'. $post_title . '" data-count="'.$data_count.'">Tweet</a>
					</div>';
			}
		}
		if ($option['active_buttons']['linkedin']==true) {
			$counter = ($option['linkedin_count']) ? 'right' : '';
			$output .= '<div style="float:left; width:' .$option['linkedin_width']. 'px;padding-right:10px; margin:4px 4px 4px 4px;height:30px;"><script type="in/share" data-url="' . $post_link . '" data-counter="' .$counter. '"></script></div>';
		}

		if ($option['active_buttons']['stumbleupon']==true) {
			$output .= '
				<div style="float:left; width:' .$option['stumbleupon_width']. 'px;padding-right:10px; margin:4px 4px 4px 4px;height:30px;"><!--<script src="http://www.stumbleupon.com/hostedbadge.php?s=1&amp;r='.$post_link.'"></script>-->
				<iframe src="http://www.stumbleupon.com/badge/embed/1/?url='.urlencode($post_link).'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:74px; height: 18px;" allowtransparency="true"></iframe></div>';

		}

		$output .= '';

		echo $output;
	}

    /**
     * Gets the options for social share from db
     * @return array options
     */
	public function share_options () {
		$option = get_option('cowobo_share');

		if ($option===false) {
			$option = $this->share_default();
			add_option('cowobo_share', $option);
		}
		return $option;
	}

    /**
     * Enqueues share scripts
     */
	public function share_scripts() {
		wp_enqueue_script('cowobo_share_gplus', 'https://apis.google.com/js/plusone.js','','',true);
		wp_enqueue_script('cowobo_share_twitter', 'http://platform.twitter.com/widgets.js','','',true);
	}

    /**
     * Sets the defaults for sharing
     * @return array defaults
     */
	private function share_default() {
		$option = array(
			'auto' => true,
			'active_buttons' => array('facebook_like'=>true, 'twitter'=>true, 'stumbleupon'=>true, 'google_plus'=>true, 'linkedin'=>true),
			'border' => 'none',
			'bkcolor' => false,
			'bkcolor_value' => '#ffffff',
			'facebook_like_width' => '85',
			'twitter_width' => '95',
			'linkedin_width' => '105',
			'stumbleupon_width' => '85',
			'google_width' => '80',
			'google_count' => true,
			'twitter_count' => true,
			'linkedin_count' => true
		);
		return $option;
	}

    /**
     * Updates the total-like count from ShareThis into the database
     * @global object $wpdb
     * @param array $postids
     */
	public function update_total_count( $postids = false ) {
		$pubkey = "4a45176a-73d4-4e42-8be9-c015a589c031";
		$accesskey = "b7766bcf68b38dc47009f4e8eec78957";
		global $wpdb;
		if ( ! $postids ) $postids = get_published_ids();
		foreach ( $postids as $postid ) {
			$url = get_permalink ( $postid );
			$share_count = $this->get_total_count_sharethis ( $url );
			update_post_meta ( $postid, 'cowobo_share_count', $share_count );
			update_post_meta ( $postid, 'cowobo_popularity', $this->get_popularity_count( $postid, true ) );
		}
		// return $share_count;
	}

    /**
     * Gets the total share / like count from ShareThis
     * @param str $url
     * @return var $total_count
     */
	public function get_total_count_sharethis ( $url ) {
		$total_share_url = "http://rest.sharethis.com/reach/getUrlInfo.php?"
							."url=".urlencode($url)
							."&provider=total"
							."&pub_key=$pubkey"
							."&access_key=$accesskey";
		$total_count = json_decode ( file_get_contents( $total_share_url ) )->total;
		$total_count = $total_count->inbound + $total_count->outbound;
		return $total_count;
	}

	/* Get the current count from the database.
	 *
	 * name: get_total_count
	 * @param $postids (optional) - array of post ids
	 * @return array ( postid => count )
	 *
	 */
	public function get_total_shares ( $postids = false, $is_single = false ) {
		if ( $postids && ! is_array ( $postids ) ) $postids = array ( $postids );
		elseif ( ! $postids ) $postids = get_published_ids();
		$total_count = array();
		foreach ( $postids as $postid ) {
			$total_count [ $postid ] = get_post_meta ( $postid, 'cowobo_share_count', true );
		}
		return ($is_single) ? $total_count[$postids[0]] : $total_count;
	}

    /**
     * Compute the full popularity count, based on shares and comments
     *
     * @param array $postids
     * @param boolean $is_single
     * @param boolean (optional) $only_comments
     * @return var or array with the total count
     */
	public function get_popularity_count ( $postids = false, $is_single = false, $only_comments = false  ) {
		if ( $postids && ! is_array ( $postids ) ) $postids = array ( $postids );
		elseif ( ! $postids ) $postids = get_published_ids();
		$total_shares = ( $only_comments ) ? array() : $this->get_total_shares ( $postids );
		$total_count = array();
		foreach ( $postids as $postid ) {
			$total_count [ $postid ] = wp_count_comments( $postid )->total_comments + $total_shares [ $postid ];
		}
		return ($is_single) ? $total_count[$postids[0]] : $total_count;
	}

    /**
     * Sort an array of post-objects by popularity
     *
     * @param array $posts
     * @param boolean (optional) $only_comments
     * @return array sorted posts
     */
	private function sort_by_popularity ( $posts, $only_comments = false ) {
		$postids = array();
		foreach ( $posts as $key => $post ) {
			$postids[$key] = $post->ID;
		}
		$popularity = ( $only_comments ) ? $this->get_popularity_count ( $postids, false, true ) : $this->get_popularity_count ( $postids );
		arsort ( $popularity );
		$postids = array_flip ( $postids );
		$newposts = array();
		foreach ( $popularity as $postid => $count ) {
			$newposts[] = $posts[$postids[$postid]];
		}
		return $newposts;
	}

    /**
     * Randomize array of post objects
     *
     * @param array $posts
     * @return array $posts
     */
	private function sort_random ( $posts ) {
		shuffle ( $posts );
		return $posts;
	}

    /**
     * Sort array of post objects by criterium
     *
     * @param array $posts
     * @param str $sort_by popularity, comments or random
     * @return type
     */
	public function sort_posts ( $posts, $sort_by ) {
		switch ( $sort_by ) {
			case 'popularity' :
				$posts = $this->sort_by_popularity ( $posts );
				break;
			case 'comments' :
				$posts = $this->sort_by_popularity ( $posts, true );
				break;
			case 'random' :
				$posts = $this->sort_random( $posts );
				break;
		}
		return $posts;
	}

	/**
     * List all feeds a user is subscribed to
     *
     * @param int $userid
     * @param str $type (optional) default is categories, alternative is posts
     * @return array posts or categories
     */
	public function list_user_feeds ( $userid, $type = 'categories' ) {
		$feed_query = get_user_meta($userid,'cowobo_feed',true);
		if (!is_array($feed_query)) return null;

		// Stored as type => id  ('p' or 'c')
		$categories = array();
		$posts = array();
		foreach ($feed_query as $query) {
			if (key($query) == 'p') $posts[] = current($query);
			elseif (key($query) == 'c') $cats[] = current($query);
		}
		if ($type == 'posts' ) return $posts;
		else return $cats;
	}

}