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

new Cowobo_Related_Posts;

class Cowobo_Related_Posts {

	// Inverse weight of popularity in the similar posts algorithm
	private $popularity_weight = 10;

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

	//cwob_activate - Run when the plugin is first installed and after an upgrade
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
			
	// cwob_delete_relationships - Delete all relationships for a post
	public function cwob_delete_relationships($post_id) {
		global $wpdb;
			$query = "DELETE FROM ".$wpdb->prefix."post_relationships WHERE post1_id = $post_id OR post2_id = $post_id";
		$delete = $wpdb->query($query);
	}

	//cwob_get_related_posts - Get the related posts for a post
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
	
	// Similar posts functions
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
	
	public function find_similar_posts ( $limit = 10 ) {
		global $post, $wpdb;
		
		$terms = $this->current_post_keywords();

		$time_difference = get_settings('gmt_offset');
		$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));

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
			. "ORDER BY score DESC LIMIT $limit";
		$results = $wpdb->get_results($sql);
		$results = $this->sort_similar_posts ( $results );
		return $results;
	}
	
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

	private function current_post_keywords( $num_to_ret = 20 ) {
		global $post;
				
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
}
?>
