<?php
/*
 *      class-related-posts.php
 *
 *      Copyright 2011 Coders Without Borders
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
 */

global $related;
$related = new Cowobo_Related_Posts;

/**
 * This class makes the related posts and similar post suggestions
 *
 * @package related-posts
 */
class Cowobo_Related_Posts {

    /**
     * Inverse weight of popularity in the similar posts algorithm
     *
     * @var int
     */
	private $popularity_weight = 10;

    /**
     * Runs installation and cleans db on deletion of posts.
     */
	public function __construct() {
		// Run at theme activation
		global $pagenow;
		if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) )
			$this->cwob_activate();
		if ( get_option('cowobo_similar_activated') != 1)
			if ( $this->activate_similar_posts() )
				update_option('cowobo_similar_activated',1);

		// The various action hooks
		add_action("delete_post", array($this,'cwob_delete_relationships'));
	}

    /**
     * Run when the plugin is first installed and after an upgrade
     */
	private function cwob_activate() {
		global $wpdb;
		// Check if post_relationships table exists, if not, create it
		$query = "SHOW TABLES LIKE '".$wpdb->prefix."post_relationships'";
		if( !count( $wpdb->get_results( $query ) ) ) {
			$query = "CREATE TABLE ".$wpdb->prefix."post_relationships (
						post1_id bigint(20) unsigned NOT NULL,
						post2_id bigint(20) unsigned NOT NULL,
						PRIMARY KEY  (post1_id,post2_id)
					)";
			$create = $wpdb->query( $query );
		}
	}

	/**
     * Delete all relationships for a post
     *
     * @param int post_id
     */
	public function cwob_delete_relationships($post_id) {
		global $wpdb;
			$query = "DELETE FROM ".$wpdb->prefix."post_relationships WHERE post1_id = $post_id OR post2_id = $post_id";
		$delete = $wpdb->query($query);
	}

	/**
     * Get the related posts for a post
     *
     * @param int post_id
     * @return array wpdb post objects of related posts
     */
	public function cwob_get_related_posts($post_id) {
		global $wpdb;
		$post_status = array("'publish'");
		if( current_user_can("read_private_posts")) $post_status[] = "'private'";
			# get current post
			# Add related posts
			$query = "SELECT * ".
				"FROM ".$wpdb->prefix."post_relationships	wpr ".
				",".$wpdb->prefix."posts					wp ".
				"WHERE wpr.post1_id = $post_id ".
				"AND wp.id = wpr.post2_id ".
				"AND wp.post_status IN (".implode( ",", $post_status ).") ".
			$query .= "UNION ALL ".
				"SELECT * ".
				"FROM ".$wpdb->prefix."post_relationships	wpr ".
				",".$wpdb->prefix."posts					wp ".
				"WHERE wpr.post2_id = $post_id ".
				"AND wp.id = wpr.post1_id ".
				"AND wp.post_status IN (".implode( ",", $post_status ).") ";
			# Return all posts in one object
			$results = $wpdb->get_results( $query );
			if($results) return $results;
			return null;
	}

	/**
     *  Installs similar posts functionality on the db
     */
	public function activate_similar_posts() {
		require(dirname(__FILE__).'/../../../../' .'wp-config.php');

		global $table_prefix;

		$connexion = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD) or die("Can't connect.<br />".mysql_error());
		$dbconnexion = mysql_select_db(DB_NAME, $connexion);

		if ( !$dbconnexion ) {
			echo mysql_error();
			die();
		}
		$sql_run = 'ALTER TABLE `'.$table_prefix.'posts` ENGINE = MYISAM' ;
		$sql_result = mysql_query($sql_run);
		if ($sql_result) {
			$sql_run = 'ALTER TABLE `'.$table_prefix.'posts` ADD FULLTEXT `post_related` ( `post_name` , `post_content` )';
			$sql_result = mysql_query($sql_run);
		}

		if ($sql_result)
			return true;
		else {
			echo "Something in the installation of the related posts plugin went wrong, please contact your nearest coding angel...";
			echo mysql_error();
			die;
			return false;
		}
	}

    /**
     * Find similar posts by content
     *
     * @param int (optional) limit the number of posts. Standard 10 for all posts, 30 for categorized.
     * @param bool (optional) if true, method returns posts in a multidimensional array of categories
     * @param int (optional) postid if not in the loop.
     * @return array sorted similar posts by popularity and score
     */
	public function find_similar_posts ( $limit = false, $cat = false, $postid = false ) {
		global $wpdb;

        if ( $postid ) $post = get_post ( $postid );
        else global $post;

		$terms = $this->current_post_keywords( $post );

		$time_difference = get_settings('gmt_offset');
		$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));

        if ( ! $limit )
            $limit = ( $cat ) ? 30 : 10;

		// Match
		$sql = "SELECT ID, post_title, post_content, "
			. "MATCH (post_name, post_content) "
			. "AGAINST ('$terms') AS score "
			. "FROM {$wpdb->posts} WHERE "
			. "MATCH (post_name, post_content) "
			. "AGAINST ('$terms') "
			. "AND post_date <= '$now' "
			. "AND (post_status IN ( 'publish', 'static' ) && ID != '{$post->ID}') "
			. "AND post_password ='' "
            . "AND post_type = 'post' "
			. "ORDER BY score DESC LIMIT $limit";
		$results = $wpdb->get_results($sql);
		$results = $this->sort_similar_posts ( $results );

        if ( ! $cat ) return $results;

        return $this->categorize_posts ( $results );
	}

    /**
     * Takes an array of posts and returns an array of categories holding the posts
     * @param arr $results
     * @return arr Postobjects in an array of categories.
     */
    protected function categorize_posts ( $results ) {
        $cat_results = array();
        foreach ( $results as $result ) {
            $category = cwob_get_category ( $result->ID )->term_id;
            if ( array_key_exists ( $category, $cat_results ) )
                $cat_results[ $category ][] = $result;
            else
                $cat_results[ $category ] = array ( $result );
        }

		return $cat_results;
    }

    /**
     * Sort posts based on populairty and similarity scores
     *
     * @param array posts with scores
     * @return array sorted posts
     */
	private function sort_similar_posts ( $posts ) {
		// Get the highest matching score
		$highest_score = 0;
		foreach ( $posts as $post ) {
			$highest_score = ( $highest_score < $post->score ) ? $post->score : $highest_score;
		}
		// Get the highest popularity
		$highest_popularity = 0;
		$popularities = array();
		foreach ( $posts as $post ) {
			$popularity = $popularities[] = get_post_meta ( $post->ID, 'cowobo_popularity', true);
			$highest_popularity = ( $highest_popularity < $popularity ) ? $popularity : $highest_popularity;
		}

		// Grade them posts
		$i = 0;
		$popularity_score = array();
		foreach ( $posts as $postpos => $post ) {
			$score = $post->score / $highest_score; // Match on a scale from 0-1
			// $popularity = get_post_meta ( $post->ID, 'cowobo_popularity', true);
			$popularity = ( $highest_popularity > 0 ) ? $popularities[$i] / $highest_popularity : 1; // Match on a scale from 0-1
			$popularity_score[$postpos] = $score + ( $popularity / $this->popularity_weight );
			$i++;
		}
		arsort ( $popularity_score );
		$results = array();
		foreach ( $popularity_score as $postpos => $score ) {
			$results[] = $posts[$postpos];
		}
		return $results;
	}

    /**
     * Extract the keywords from the postobject
     *
     * @param obj (optional) post, if not in the loop
     * @param int (optional) number of terms to get
     * @return array current post keywords
     */
	private function current_post_keywords( $post = false, $num_to_ret = 20 ) {
		if ( ! $post ) global $post;

		$string =	$post->post_title.' '.
				str_replace('-', ' ', $post->post_name).' '.
				$post->post_content;

		// Remove punctuation
		$wordlist = preg_split('/\s*[\s+\.|\?|,|(|)|\-+|\'|\"|=|;|&#0215;|\$|\/|:|{|}]\s*/i', $string);

		// Build array of words and number of times they occur
		$all = array_count_values($wordlist);

		// Remove words without information
		$stopwords = array( 'coders', 'without', 'borders', '', 'a', 'an', 'the', 'and', 'of', 'i', 'to', 'is', 'in', 'with', 'for', 'as', 'that', 'on', 'at', 'this', 'my', 'was', 'our', 'it', 'you', 'we', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '10', 'about', 'after', 'all', 'almost', 'along', 'also', 'amp', 'another', 'any', 'are', 'area', 'around', 'available', 'back', 'be', 'because', 'been', 'being', 'best', 'better', 'big', 'bit', 'both', 'but', 'by', 'c', 'came', 'can', 'capable', 'control', 'could', 'course', 'd', 'dan', 'day', 'decided', 'did', 'didn', 'different', 'div', 'do', 'doesn', 'don', 'down', 'drive', 'e', 'each', 'easily', 'easy', 'edition', 'end', 'enough', 'even', 'every', 'example', 'few', 'find', 'first', 'found', 'from', 'get', 'go', 'going', 'good', 'got', 'gt', 'had', 'hard', 'has', 'have', 'he', 'her', 'here', 'how', 'if', 'into', 'isn', 'just', 'know', 'last', 'left', 'li', 'like', 'little', 'll', 'long', 'look', 'lot', 'lt', 'm', 'made', 'make', 'many', 'mb', 'me', 'menu', 'might', 'mm', 'more', 'most', 'much', 'name', 'nbsp', 'need', 'new', 'no', 'not', 'now', 'number', 'off', 'old', 'one', 'only', 'or', 'original', 'other', 'out', 'over', 'part', 'place', 'point', 'pretty', 'probably', 'problem', 'put', 'quite', 'quot', 'r', 're', 'really', 'results', 'right', 's', 'same', 'saw', 'see', 'set', 'several', 'she', 'sherree', 'should', 'since', 'size', 'small', 'so', 'some', 'something', 'special', 'still', 'stuff', 'such', 'sure', 'system', 't', 'take', 'than', 'their', 'them', 'then', 'there', 'these', 'they', 'thing', 'things', 'think', 'those', 'though', 'through', 'time', 'today', 'together', 'too', 'took', 'two', 'up', 'us', 'use', 'used', 'using', 've', 'very', 'want', 'way', 'well', 'went', 'were', 'what', 'when', 'where', 'which', 'while', 'white', 'who', 'will', 'would', 'your');
		foreach ($stopwords as $stopword) {
			 unset($all[$stopword]);
		}

		// Sort it, count it, slice it
		arsort($all, SORT_NUMERIC);
		$num_words = count($all);
		$num_to_ret = $num_words > $num_to_ret ? $num_to_ret : $num_words;
		$outwords = array_slice($all, 0, $num_to_ret);

		return implode(' ', array_keys($outwords));
	}

    /**
     * Create relations for a post.
     *
     * @global obj $wpdb
     * @param int $postid of the post to be related
     * @param arr $relatedpostids of posts to be related
     * @return arr wp queries
     */
    public function create_relations($postid, $relatedpostids) {
        global $wpdb;
        $results = array();
        foreach($relatedpostids as $relatedpostid) {
            $relatedpostid = (int) $relatedpostid;
			$type = cwob_get_category($relatedpostid);
			if($type->slug == "location"):
				$coordinates = get_post_meta($relatedpostid, 'coordinates', true);
				add_post_meta($postid, 'coordinates', $coordinates);
			endif;
            $results[] = $this->create_relation( $postid, $relatedpostid );
        }
        return $results;
    }

    /**
     * Creates one relation between two posts
     *
     * @param int $post1
     * @param int $post2
     * @return str Result of WP query.
     */
    public function create_relation ($post1, $post2) {
		global $wpdb;
        $type = cwob_get_category($post2);
        if($type->slug == "locations"):
            $coordinates = get_post_meta($post2, 'coordinates', true);
            add_post_meta($post1, 'coordinates', $coordinates);
        endif;
        $query = "INSERT INTO ".$wpdb->prefix."post_relationships VALUES($post1, $post2)";
        $result = $wpdb->query($query);
        return $result;
    }
}