<?php
/**
 * Plugin Name: AffiliateWP - Flag Affiliates
 * Plugin URI: https://affiliatewp.com/
 * Description: Flag your affiliates.
 * Author: AffiliateWP, LLC
 * Author URI: https://affiliatewp.com
 * Version: 1.0
 * Text Domain: affiliatewp-flag-affiliates
 * Domain Path: languages
 *
 * AffiliateWP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * AffiliateWP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AffiliateWP. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AffiliateWP_Flag_Affiliates' ) ) {

	final class AffiliateWP_Flag_Affiliates {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of AffiliateWP_Flag_Affiliates exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @access	private
		 * @var		object
		 * @static
		 * @since 	1.0
		 */
		private static $instance;

		/**
		 * The version number
		 *
		 * @access	private
		 * @since	1.0
		 */
		private $version = '1.0';

		/**
		 * Main AffiliateWP_Flag_Affiliates Instance
		 *
		 * Insures that only one instance of AffiliateWP_Flag_Affiliates exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access	public
		 * @since	1.0
		 * @static	var array $instance
		 * @return	The one true AffiliateWP_Flag_Affiliates
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Flag_Affiliates ) ) {

				self::$instance = new AffiliateWP_Flag_Affiliates;
				self::$instance->load_textdomain();
				self::$instance->hooks();

			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
 		 * @access	protected
		 * @since	1.0
		 * @return	void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-flag-affiliates' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class
		 * @access	protected
		 * @since	1.0
		 * @return	void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-flag-affiliates' ), '1.0' );
		}

		/**
		 * Constructor Function
		 * @access	private
		 * @since	1.0
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Reset the instance of the class
		 *
		 * @access	public
		 * @since	1.0
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Loads the plugin language files
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'AffiliateWP_Flag_Affiliates_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale   = apply_filters( 'plugin_locale',  get_locale(), 'affiliatewp-flag-affiliates' );
			$mofile   = sprintf( '%1$s-%2$s.mo', 'affiliatewp-flag-affiliates', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/affiliatewp-flag-affiliates/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/affiliatewp-flag-affiliates/ folder
				load_textdomain( 'affiliatewp-flag-affiliates', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/affiliatewp-flag-affiliates/languages/ folder
				load_textdomain( 'affiliatewp-flag-affiliates', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'affiliatewp-flag-affiliates', false, $lang_dir );
			}
		}

		/**
		 * Setup the default hooks and actions
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		private function hooks() {

			add_filter( 'affwp_affiliate_table_affiliate_id', array( $this, 'icon' ) );
			add_filter( 'affwp_affiliate_row_actions', array( $this, 'row_action' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );

			add_action( 'affwp_flag', array( $this, 'process_flag_affiliate' ), 10, 1 );
			add_action( 'affwp_unflag', array( $this, 'process_unflag_affiliate' ), 10, 1 );
			add_action( 'admin_notices', array( $this, 'notices' ) );
			add_action( 'affwp_edit_affiliate_end', array( $this, 'edit_affiliate' ), 10, 1 );
			add_action( 'affwp_update_affiliate', array( $this, 'update_affiliate' ), -1 );

			// bulk actions
		//  Uncomment once this issue has been merged - https://github.com/AffiliateWP/AffiliateWP/issues/2016
		//	add_filter( 'affwp_affiliates_bulk_actions', array( $this, 'bulk_actions' ), 10, 2 );
		//	add_action( 'affwp_affiliates_do_bulk_action_flag', array( $this, 'process_bulk_action_flag' ) );
		//	add_action( 'affwp_affiliates_do_bulk_action_unflag', array( $this, 'process_bulk_action_unflag' ) );
		}

		/**
		 * Add flag icon
		 *
		 * @access	public
		 * @since 	1.0
		 * @param 	int $affiliate_id ID of the affiliate_id
		 * @return	string Affiliate ID followed by the icon
		 */
		public function icon( $affiliate_id ) {

			$flagged = affwp_get_affiliate_meta( $affiliate_id, 'flagged', true );

			/**
			 * Filters the icon used to flag affiliates
			 *
			 * @param string $icon The icon to use from https://developer.wordpress.org/resource/dashicons/
			 */
			$dashicon = apply_filters( 'affiliatewp_flag_affiliates_icon', 'dashicons-flag' );

			if ( $flagged ) {
				$icon = ' <span class="dashicons ' . esc_attr( $dashicon ) . '"></span>';
			}

			return $affiliate_id . $icon;

		}

		/**
		 * Allow the actions to be filtered
		 *
		 * @access	public
		 * @since 	1.0
		 * @return	array $actions
		 */
		public function actions() {

			$actions = array(
				'enable'   => __( 'Flag', 'affiliatewp-flag-affiliates' ),
				'disable'  => __( 'Unflag', 'affiliatewp-flag-affiliates' ),
				'enabled'  => strtolower( __( 'Flagged', 'affiliatewp-flag-affiliates' ) ),
				'disabled' => strtolower( __( 'Unflagged', 'affiliatewp-flag-affiliates' ) )
			);

			return apply_filters( 'affiliatewp_flag_affiliates_actions', $actions );

		}

		/**
		 * Admin notices
		 *
		 * @access	public
		 * @since 	1.0
		 * @return	void
		 */
		public function notices() {

			if ( empty ( $_REQUEST['affwp_notice' ] ) ) {
				return;
			}

			$notice  = '';
			$class   = '';
			$actions = $this->actions();

			switch ( $_REQUEST['affwp_notice'] ) {

				case 'affiliate_flagged' :
					$notice = sprintf( __( 'Affiliate successfully %s', 'affiliatewp-flag-affiliates' ), $actions['enabled'] );
					$class  = 'updated';
					break;

				case 'affiliate_unflagged' :
					$notice = sprintf( __( 'Affiliate successfully %s', 'affiliatewp-flag-affiliates' ), $actions['disabled'] );
					$class  = 'updated';
					break;

				case 'affiliate_flag_failed' :
					$notice = sprintf( __( 'Affiliate was not %s', 'affiliatewp-flag-affiliates' ), $actions['enabled'] );
					$class  = 'error';
					break;

				case 'affiliate_unflag_failed' :
					$notice = sprintf( __( 'Affiliate was not %s', 'affiliatewp-flag-affiliates' ), $actions['disabled'] );
					$class  = 'error';
					break;

			}

			echo '<div class="' . $class . '"><p>' . $notice . '</p></div>';

		}

		/**
		 * Add "flag" row action
		 *
		 * @access	public
		 * @since 	1.0
		 * @param 	array $row_actions Current row actions
		 * @param 	object $affiliate Affiliate object
		 * @return	array $row_actions
		 */
		public function row_action( $row_actions, $affiliate ) {

			$actions = $this->actions();
			$flagged = affwp_get_affiliate_meta( $affiliate->ID, 'flagged', true );

			if ( $flagged ) {
				$row_actions['unflag'] = '<a href="' . wp_nonce_url( admin_url( "admin.php?page=affiliate-wp-affiliates&amp;affiliate_id=$affiliate->ID&amp;affwp_action=unflag&amp;affwp_notice=affiliate_unflagged" ), "affiliate-nonce" ) . '">' . $actions['disable'] . '</a>';
			} else {
				$row_actions['flag'] = '<a href="' . wp_nonce_url( admin_url( "admin.php?page=affiliate-wp-affiliates&amp;affiliate_id=$affiliate->ID&amp;affwp_action=flag&amp;affwp_notice=affiliate_flagged" ), "affiliate-nonce" ) . '">' . $actions['enable'] . '</a>';
			}

			return $row_actions;

		}

		/**
		 * Process the action to flag an affiliate
		 *
		 * @access	public
		 * @since 	1.0
		 * @param 	array $data Request data
		 * @return	void
		 */
		public function process_flag_affiliate( $data ) {

			if ( empty( $data['affiliate_id'] ) ) {
				return false;
			}

			if ( ! $affiliate = affwp_get_affiliate( $data['affiliate_id'] ) ) {
				return false;
			}

			if ( ! is_admin() ) {
				return false;
			}

			if ( ! current_user_can( 'manage_affiliates' ) ) {
				wp_die( __( 'You do not have permission to manage affiliates', 'affiliate-wp' ), __( 'Error', 'affiliate-wp' ), array( 'response' => 403 ) );
			}

			if ( $this->flag_affiliate( $affiliate->ID ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-affiliates&action=flag&affwp_notice=affiliate_flagged&affiliate_id=' . $affiliate->ID ) );
				exit;
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-affiliates&affwp_notice=affiliate_flag_failed' ) );
				exit;
			}

		}

		/**
		 * Process the action to unflag an affiliate
		 *
		 * @access	public
		 * @since 	1.0
		 * @param 	array $data Request data
		 * @return	void
		 */
		public function process_unflag_affiliate( $data ) {

			if ( empty( $data['affiliate_id'] ) ) {
				return false;
			}

			if ( ! $affiliate = affwp_get_affiliate( $data['affiliate_id'] ) ) {
				return false;
			}

			if ( ! is_admin() ) {
				return false;
			}

			if ( ! current_user_can( 'manage_affiliates' ) ) {
				wp_die( __( 'You do not have permission to manage affiliates', 'affiliate-wp' ), __( 'Error', 'affiliate-wp' ), array( 'response' => 403 ) );
			}


			if ( $this->unflag_affiliate( $affiliate->ID ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-affiliates&action=flag&affwp_notice=affiliate_unflagged&affiliate_id=' . $affiliate->ID ) );
				exit;
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-affiliates&affwp_notice=affiliate_unflag_failed' ) );
				exit;
			}

		}

		/**
		 * Register a new bulk action
		 *
		 * @access	public
		 * @param	array $actions Bulk actions
		 * @since	1.0
		 * @return	array $actions
		 */
		public function bulk_actions( $actions ) {

			$text = $this->actions();

			$actions['flag']   = $text['enable'];
			$actions['unflag'] = $text['disable'];

			return $actions;
		}

		/**
		 * Process a bulk flag
		 *
		 * @access	public
		 * @param	int $affiliate_id ID of the affiliate
		 * @since	1.0
		 * @return	void
		 */
		public function process_bulk_action_flag( $affiliate_id ) {

			if ( ! $affiliate = affwp_get_affiliate( $affiliate_id ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_affiliates' ) ) {
				return;
			}

			$this->flag_affiliate( $affiliate->ID );

		}

		/**
		 * Process a bulk unflag
		 *
		 * @access	public
		 * @param	int $affiliate_id ID of the affiliate
		 * @since	1.0
		 * @return	void
		 */
		public function process_bulk_action_unflag( $affiliate_id ) {

			if ( ! $affiliate = affwp_get_affiliate( $affiliate_id ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_affiliates' ) ) {
				return;
			}

			$this->unflag_affiliate( $affiliate->ID );

		}

		/**
		 * Flag an affiliate
		 *
		 * @access 	public
		 * @param 	int $affiliate_id ID of the affiliate
		 * @since 	1.0
		 * @return 	void
		 */
		public function flag_affiliate( $affiliate_id = 0 ) {

			if ( ! $affiliate = affwp_get_affiliate( $affiliate_id ) ) {
				return false;
			}

			return affwp_update_affiliate_meta( $affiliate->ID, 'flagged', true );

		}

		/**
		 * Unflag an affiliate
		 *
		 * @access	public
		 * @param	int $affiliate_id ID of the affiliate
		 * @since	1.0
		 * @return	void
		 */
		public function unflag_affiliate( $affiliate_id = 0 ) {

			if ( ! $affiliate = affwp_get_affiliate( $affiliate_id ) ) {
				return false;
			}

			return affwp_delete_affiliate_meta( $affiliate->ID, 'flagged' );

		}

		/**
		 * Flag the affiliate from the edit affiliate screen
		 *
		 * @access	public
		 * @param	array $affiliate Affiliate object
		 * @since	1.0
		 * @return	void
		 */
		public function edit_affiliate( $affiliate ) {

			$actions = $this->actions();
			$checked = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'flagged', true );

			?>
			<tr class="form-row">

				<th scope="row">
					<label><?php printf( __( '%s Affiliate', 'affiliatewp-flag-affiliates' ), $actions['enable'] ); ?></label>
				</th>

				<td>
					<label for="flag-affiliate">
						<input type="checkbox" name="flag_affiliate" id="flag-affiliate" value="1" <?php checked( $checked, '1' ); ?>/>
						<?php printf( __( '%s this affiliate', 'affiliatewp-flag-affiliates' ), $actions['enable'] ); ?>
					</label>
				</td>

			</tr>

		<?php

		}

		/**
		 * Flag affiliate from the edit affiliate screen
		 *
		 * @access	public
		 * @since	1.0
		 * @param	array $data Request data
		 * @return	void
		 *
		 */
		public function update_affiliate( $data ) {

			if ( empty( $data['affiliate_id'] ) ) {
				return false;
			}

			if ( ! $affiliate = affwp_get_affiliate( $data['affiliate_id'] ) ) {
				return;
			}

			if ( ! is_admin() ) {
				return false;
			}

			if ( ! current_user_can( 'manage_affiliates' ) ) {
				wp_die( __( 'You do not have permission to manage direct links', 'affiliatewp-direct-link-tracking' ), __( 'Error', 'affiliatewp-direct-link-tracking' ), array( 'response' => 403 ) );
			}

			$this->flag_affiliate( $affiliate->ID );

		}

		/**
		 * Modify plugin metalinks
		 *
		 * @access      public
		 * @since       1.0
		 * @param       array $links The current links array
		 * @param       string $file A specific plugin table entry
		 * @return      array $links The modified links array
		 */
		public function plugin_meta( $links, $file ) {

		    if ( $file == plugin_basename( __FILE__ ) ) {

				$url = admin_url( 'admin.php?page=affiliate-wp-add-ons' );

		        $plugins_link = array(
		            '<a title="' . __( 'Get more add-ons for AffiliateWP', 'affiliatewp-flag-affiliates' ) . '" href="' . esc_url( $url ) . '">' . __( 'More add-ons', 'affiliatewp-flag-affiliates' ) . '</a>'
		        );

		        $links = array_merge( $links, $plugins_link );
		    }

		    return $links;
		}
	}

	/**
	 * The main function responsible for returning the one true AffiliateWP_Flag_Affiliates
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $affiliatewp_flag_affiliates = affiliatewp_flag_affiliates(); ?>
	 *
	 * @since 1.0
	 * @return object The one true AffiliateWP_Flag_Affiliates Instance
	 */
	function affiliatewp_flag_affiliates() {

	    if ( ! class_exists( 'Affiliate_WP' ) ) {
	        if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
	            require_once 'includes/class-activation.php';
	        }

	        $activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
	        $activation = $activation->run();
	    } else {
	        return AffiliateWP_Flag_Affiliates::instance();
	    }

	}
	add_action( 'plugins_loaded', 'affiliatewp_flag_affiliates', 100 );

}
