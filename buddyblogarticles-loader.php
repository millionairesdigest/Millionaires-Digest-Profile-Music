<?php
/**
 * BuddyBlog Component Loader
 *
 * @package buddyblog
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyBlog Component
 */
class BuddyBlogArticles_Core_Component extends BP_Component {

	/**
	 * Initialize component
	 */
	public function __construct() {

		parent::start(
			'buddyblogarticles',
			__( 'BuddyBlog Articles', 'buddyblogphotos' ),
			untrailingslashit( plugin_dir_path( __FILE__ ) )
		);

		$this->includes();
		// set it as active.
		buddypress()->active_components[ $this->id ] = 1;
	}

	/**
	 * Include files
	 *
	 * @param array $includes included files.
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'core/buddyblogphotos-templates.php',
			'core/buddyblogphotos-actions.php',
			'core/buddyblogphotos-screens.php',
			'core/buddyblogphotos-functions.php',
			'core/buddyblogphotos-notifications.php',
			'core/buddyblogphotos-hooks.php',
			'core/buddyblogphotos-filters.php',
			'core/buddyblogphotos-permissions.php',
		);

		parent::includes( $includes );
	}

	/**
	 * Setup globals
	 */
	public function setup_globals( $globals = array() ) {

		// Define a slug, if necessary.
		if ( ! defined( 'BP_BUDDYBLOGARTICLES_SLUG' ) ) {
			define( 'BP_BUDDYBLOGARTICLES_SLUG', $this->id );
		}

		$globals = array(
			'slug'                  => BP_BUDDYBLOGARTICLES_SLUG,
			'root_slug'             => BP_BUDDYBLOGARTICLES_SLUG,
			'has_directory'         => false,
			'notification_callback' => 'buddyblogphotos_format_notifications',
			'search_string'         => __( 'Search Posts...', 'buddyblogphotos' ),
			'global_tables'         => array(),
		);

		parent::setup_globals( $globals );
	}

	/**
	 * Setup BuddyBar navigation
	 * Sets up user tabs
	 *
	 * @param array $main_nav main nav items.
	 * @param array $sub_nav sub nav items.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Define local variables.
		$sub_nav = array();
		// instance of the blog screen.
		$screen  = BuddyBlogPhotos_Screens::get_instance();

		$total_posts = 0;

		if ( bp_is_my_profile() ) {
			$total_posts = buddyblogphotos_get_total_posted( bp_displayed_user_id() );

		} else {
			$total_posts = buddyblogphotos_get_total_published_posts( bp_displayed_user_id() );
		}

		$total_posts = apply_filters( 'buddyblogphotos_visible_posts_count', $total_posts, bp_displayed_user_id() );

		// Add 'Blog' to the main navigation.
		$main_nav = array(
			'name'                => sprintf( __( 'BuddyBlog Photos <span>%d</span>', 'buddyblogphotos' ), $total_posts ),
			'slug'                => $this->slug,
			'position'            => 70,
			'screen_function'     => array( $screen, 'my_photos' ),
			'default_subnav_slug' => BUDDYBLOGARTICLES_ARCHIVE_SLUG,
			'item_css_id'         => $this->id,
		);

		// Whether to link to logged in user or displayed user.
		if ( ! bp_is_my_profile() ) {
			$blog_link = trailingslashit( bp_displayed_user_domain() . $this->slug );
		} else {
			$blog_link = trailingslashit( bp_loggedin_user_domain() . $this->slug );
		}
		// Add the Group Invites nav item.
		$sub_nav['my-photos'] = array(
			'name'            => __( 'Posts', 'buddyblogphotos' ),
			'slug'            => BUDDYBLOGARTICLES_ARCHIVE_SLUG,
			'parent_url'      => $blog_link,
			'parent_slug'     => $this->slug,
			'screen_function' => array( $screen, 'my_photos' ),
			'position'        => 30,
		);

		$sub_nav['new-post'] = array(
			'name'            => __( 'New Post', 'buddyblogphotos' ),
			'slug'            => 'edit',
			'parent_url'      => $blog_link,
			'parent_slug'     => $this->slug,
			'screen_function' => array( $screen, 'new_post' ),
			'user_has_access' => bp_is_my_profile(),
			'position'        => 30,
		);

		$main_nav = apply_filters( 'buddyblogphotos_setup_main_nav', $main_nav );
		$sub_nav  = apply_filters( 'buddyblogphotos_setup_sub_nav', $sub_nav );

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Setup an admin bar menu
	 *
	 * @param array $nav array for admin nav.
	 */
	public function setup_admin_bar( $nav = array() ) {

		$bp = buddypress();
		// Prevent debug notices.
		$wp_admin_nav = array();

		// Menus for logged in user.
		if ( is_user_logged_in() ) {
			// Setup the logged in user variables.
			$user_domain = bp_loggedin_user_domain();
			$blog_link   = trailingslashit( $user_domain . $this->slug );

			$title = __( 'Posts', 'buddyblogphotos' );
			// My Posts.
			$wp_admin_nav['posts'] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => trailingslashit( $blog_link ),
			);

			$wp_admin_nav['my-photos'] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . '-my-photos',
				'title'    => __( 'My Posts', 'buddyblogphotos' ),
				'href'     => trailingslashit( $blog_link ),
				'position' => 10,
			);

			// Add new Posts.
			$wp_admin_nav['new-post'] = array(
				'parent'   => 'my-account-' . $this->id,
				'id'       => 'my-account-' . $this->id . '-new-post',
				'title'    => __( 'New Post', 'buddyblogphotos' ),
				'href'     => trailingslashit( $blog_link . 'edit' ),
				'position' => 20,
			);

		}

		$wp_admin_nav = apply_filters( 'buddyblogphotos_adminbar_nav', $wp_admin_nav );
		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Sets up the title for pages and <title>
	 *
	 * @global BuddyPress $bp The one true BuddyPress instance
	 */
	public function setup_title() {

		$bp = buddypress();

		if ( bp_is_buddyblogphotos_component() ) {

			if ( bp_is_my_profile() && ! bp_is_single_item() ) {

				$bp->bp_options_title = __( 'Posts', 'buddyblogphotos' );

			} elseif ( ! bp_is_my_profile() && ! bp_is_single_item() ) {

				$bp->bp_options_avatar = bp_core_fetch_avatar( array(
					'item_id' => bp_displayed_user_id(),
					'type'    => 'thumb',
					'alt'     => sprintf( __( 'Profile picture of %s', 'buddyblogphotos' ), bp_get_displayed_user_fullname() ),
				) );

				$bp->bp_options_title = bp_get_displayed_user_fullname();

				// We are viewing a single group, so set up the
				// group navigation menu using the $this->current_group global.
			}
		}

		parent::setup_title();
	}

}

/**
 * Setup BuddyBlog component.
 */
function bp_setup_buddyblogphotos() {
	buddypress()->buddyblogphotos = new BuddyBlogPhotos_Core_Component();
}

add_action( 'bp_loaded', 'bp_setup_buddyblogphotos' );
