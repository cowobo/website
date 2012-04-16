<?php
/*
 *      class-cowobo-social-options.php
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
 * WP-admin options for the Cowobo Social library
 *
 * @package cowobo-social
 */
class Cowobo_Social_Options {

	public function add_menu_pages() {
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		//add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );

		add_menu_page("Cowobo Social", "Cowobo Social", 'manage_options', "cowobo-social", array ( &$this, 'main_page' ), get_bloginfo('template_directory') . '/images/animated_favicon1.gif' );

		add_submenu_page('cowobo-social', 'Connect', 'Connect', 'manage_options', 'social-connect-id', 'sc_render_social_connect_settings' );
		add_submenu_page('cowobo-social', 'Connect: Nags', 'Connect: Nags', 'manage_options', 'connect-nags', array ( &$this, "connect_nags" ) );


		global $fb_comments;
		if ( $fb_comments ) {
			add_submenu_page("cowobo-social", "Merge", "Merge", 'manage_options', "wp-fb-comments", array( $fb_comments, "admin_page" ) );
			add_submenu_page("cowobo-social", "Merge: Log","Merge: Log", 'manage_options', "fb-merge", "fb_merge_home_page");
			unset($fb_comments);
		}

		add_submenu_page('cowobo-social','Share', 'Share', 'manage_options', 'cs-social-share', array( &$this, 'social_share') );

	}

	public function main_page() {
		echo '<div class="wrap">

		<h2>Coders Without Borders Social Network Integration</h2>
		<div id="poststuff" style="padding-top:10px; position:relative;">
			<div class="postbox">
			<div class="inside">
			<p>Use this menu to adjust the way Coders Without Borders interacts with social networks.</p>
			<p>Summary of available options:</p>
			<table>
				<tr><td style="width:90px;font-weight:bold">Connect</td><td>Handles the social login using Facebook, Google, Yahoo, Twitter or WordPress.com.</td></tr>
				<tr><td style="font-weight:bold">Merge</td><td>Customize the merging behaviour of posts and comments (now only Facebook).</td></tr>
				<tr><td style="font-weight:bold">Share</td><td>Set the options for sharing on Google+, FB, Twitter, StumbleUpon, etc.</td></tr>
			</table>
		</div>
		</div></div>';

	}

	public function connect_nags() {
		if (!current_user_can('manage_options'))
				wp_die( __('You do not have sufficient permissions to access this page.') );


		if ( isset ( $_POST['state1'] ) && ! empty ( $_POST['state1'] ) ) {
			$nags = array(
				1 => $_POST['state1'],
				2 => $_POST['state2'],
				3 => $_POST['state3']
			);
			update_option ( 'cwob_nags', $nags );
		}

		$nags = get_option ( 'cwob_nags' );
		// Default options
		if ( empty ( $nags ) ) {
			$nags = array (
				1 => "Simply click on the account you use most:<br/>COWOBOCONNECT",
				2 => "Your current display name is <a href='PROFILEURL'>DISPLAYNAME</a>. You can change this display name and get access to all cowobo.org functionalities, by <a href='PROFILEURL'>creating a profile</a>",
				3 => "Hi DISPLAYNAME! You didn't make your Coders Without Borders profile yet. Please do so by clicking <a href='PROFILEURL'>here</a>"
			);
			update_option ( 'cwob_nags', $nags );
		}

		echo '<div class="wrap">
			<div id="poststuff">
				<div class="postbox">
					<div class="inside">
						<h3>Instructions</h3>
						<p>For state 1: COWOBOCONNECT shows social login buttons</p>
						<p>For state 2 & 3: DISPLAYNAME shows displayname and PROFILEURL the user\'s profileurl</p>
					</div>
				</div>
			</div>

			<form name="nags" method="post">
				<h3>State 1: Not logged in</h3>
				<textarea name="state1" style="width:75%;height:100px;">' . stripslashes ( $nags[1] ) . '</textarea>
				<h3>State 2: First time login</h3>
				<textarea name="state2" style="width:75%;height:100px;">' . stripslashes ( $nags[2] ) . '</textarea>
				<h3>State 3: User logged in, no profile</h3>
				<textarea name="state3" style="width:75%;height:100px;">' . stripslashes ( $nags[3] ) . '</textarea>
				<input type="submit" value="Save">
			</form>
			</div>';

	}

	public function social_share() {
		$option_name = 'cowobo_share';
		if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		$active_buttons = array(
				'facebook_like'=>'Facebook like',
				'twitter'=>'Twitter',
				'stumbleupon'=>'Stumbleupon',
				'google_plus'=>'Google Plus',
				'linkedin'=>'LinkedIn'
			);

		$out = '';

		if( isset($_POST['Submit']) ) {

			$option['auto'] = (isset($_POST['cowobo_share_auto_display']) && $_POST['cowobo_share_auto_display']=='on') ? true : false;

			foreach (array_keys($active_buttons) as $item) {
				$option['active_buttons'][$item] = (isset($_POST['cowobo_share_active_'.$item]) and $_POST['cowobo_share_active_'.$item]=='on') ? true : false;
			}
			$option['border'] = esc_html($_POST['cowobo_share_border']);
			$option['bkcolor'] = (isset($_POST['cowobo_share_background_color']) and $_POST['cowobo_share_background_color']=='on') ? true : false;

			$option['bkcolor_value'] = esc_html($_POST['cowobo_share_bkcolor_value']);

			$option['twitter_id'] = esc_html($_POST['cowobo_share_twitter_id']);
			$option['twitter_count'] = (isset($_POST['cowobo_share_twitter_count']) and $_POST['cowobo_share_twitter_count']=='on') ? true : false;
			$option['google_count'] = (isset($_POST['cowobo_share_google_count']) and $_POST['cowobo_share_google_count']=='on') ? true : false;
			$option['linkedin_count'] = (isset($_POST['cowobo_share_linkedin_count']) and $_POST['cowobo_share_linkedin_count']=='on') ? true : false;
			update_option($option_name, $option);
			// Put a settings updated message on the screen
			$out .= '<div class="updated"><p><strong>'.__('Settings saved.', 'menu-test' ).'</strong></p></div>';
		}

		//GET ARRAY OF STORED VALUES
		$option = new Cowobo_Social( true );
		$option = $option->share_options();

		$bkcolor = ($option['bkcolor']) ? 'checked="checked"' : '';
		$auto =    ($option['auto']) ? 'checked="checked"' : '';
		$google_count = ($option['google_count']) ? 'checked="checked"' : '';
		$twitter_count = ($option['twitter_count']) ? 'checked="checked"' : '';
		$linkedin_count = ($option['linkedin_count']) ? 'checked="checked"' : '';

		$out .= '
		<div class="wrap">

		<h2>'.__( 'Cowobo share buttons', 'menu-test' ).'</h2>
		<div id="poststuff" style="padding-top:10px; position:relative;">
			<div style="float:left; padding-right:1%;">
		<form name="cowobo_share" method="post" action="">
		<div class="postbox">
		<h3>'.__("General options", 'menu-test' ).'</h3>
		<div class="inside">
		<table>

		<tr><td valign="top" style="width:130px;">'.__("Active share buttons", 'menu-test' ).':</td>
		<td style="padding-bottom:30px;">';

		foreach ($active_buttons as $name => $text) {
			$checked = ($option['active_buttons'][$name]) ? 'checked="checked"' : '';
			$out .= '<div style="width:150px; float:left;">
					<input type="checkbox" name="cowobo_share_active_'.$name.'" '.$checked.' /> '
					. __($text, 'menu-test' ).' &nbsp;&nbsp;</div>';

		}


		$out .= '

		<tr><td style="padding-bottom:20px;" valign="top">'.__("Show Background Color", 'menu-test' ).':</td>
		<td style="padding-bottom:20px;">
			<input type="checkbox" name="cowobo_share_background_color" '.$bkcolor.' />
		</td></tr>

		<tr><td style="padding-bottom:20px;" valign="top">'.__("Background Color", 'menu-test' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="cowobo_share_bkcolor_value" value="'.$option['bkcolor_value'].'" size="10">
		</td></tr>

		<tr><td style="padding-bottom:20px;" valign="top">'.__("Twitter ID", 'menu-test' ).':</td>
		<td style="padding-bottom:20px;">
		<input type="text" name="cowobo_share_twitter_id" value="'.$option['twitter_id'].'" size="30">
			 <span class="description">'.__("Specify your twitter id without @", 'menu-test' ).'</span>
		</td></tr>
		</table>
		</div>
		</div>

		<div class="postbox">
		<h3>'.__("Adjust Count Display", 'menu-test' ).'</h3>
		<div class="inside">
			<table>
			<tr><td style="padding-bottom:20px; padding-right:10px;" valign="top">'.__("Google +1 counter", 'menu-test' ).':</td>
				<td style="padding-bottom:20px;">
					<input type="checkbox" name="cowobo_share_google_count" '.$google_count.' />
				</td>
				<td style="padding-bottom:20px; padding-right:10px;" valign="top">'.__("LinkedIn counter", 'menu-test' ).':</td>
				<td style="padding-bottom:20px;">
					<input type="checkbox" name="cowobo_share_linkedin_count" '.$linkedin_count.' />
				</td>
			</tr>
			<tr><td style="padding-bottom:20px; padding-right:10px;" valign="top">'.__("Twitter counter", 'menu-test' ).':</td>
				<td style="padding-bottom:20px;">
					<input type="checkbox" name="cowobo_share_twitter_count" '.$twitter_count.' />
				</td></tr>
			</table>
		</div>
		</div>

		<tr><td valign="top" colspan="2">
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" name="cowobo_share" value="'.esc_attr('Save Changes').'" />
		</p>
		</td></tr>
		</form>
		</div>
		</div>
		';
		echo $out;
	}
}