<?php
/**
 * Critical CSS module.
 *
 * @package Hummingbird\Core\Modules
 * @since 3.6.0
 */

namespace Hummingbird\Core\Modules;

use Hummingbird\Core\Filesystem;
use Hummingbird\Core\Module;
use Hummingbird\Core\Traits\Module as ModuleContract;
use Hummingbird\Core\Utils;
use Hummingbird\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Critical CSS
 */
class Critical_Css extends Module {
	use ModuleContract;

	/**
	 * Key to store critical status for a single post.
	 *
	 * @var string
	 */
	const CRITICAL_POST_META_KEY = '_wphb_singular_css_status';

	/**
	 * Key to store critical css process queue.
	 *
	 * @var string
	 */
	const QUEUE_OPTION_ID = 'wphb_cs_process_queue';

	/**
	 * Transient expiration timeout.
	 *
	 * @var string
	 */
	const TRANSIENT_EXPIRATION = 30;

	/**
	 * Transient name.
	 *
	 * @var string
	 */
	const TRANSIENT_NAME = 'wphb-cs-processing';

	/**
	 * Store all the items.
	 *
	 * @var array
	 */
	private $items = array();

	/**
	 * Initialize module.
	 *
	 * @since 3.6.0
	 */
	public function init() {
		add_filter( 'wp_hummingbird_is_active_module_critical_css', array( $this, 'module_status' ) );
		add_filter( 'wp_hummingbird_default_options', array( $this, 'wp_hummingbird_default_options' ) );
	}

	/**
	 * Execute module actions.
	 *
	 * @since 3.6.0
	 */
	public function run() {
		add_filter( 'wphb_dont_combine_handles', array( $this, 'wphb_dont_combine_handles' ), 10, 3 );
		add_filter( 'wphb_should_cache_exit', array( $this, 'should_cache_exit' ) );
		add_filter( 'wphb_buffer', array( $this, 'add_critical_css' ) );
		// Process the cs queue through WP Cron.
		add_action( 'wphb_cs_process_queue_cron', array( $this, 'generate_critical_for_queue' ) );
		add_action( 'wphb_cs_ping_queue_cron', array( $this, 'get_critical_for_queue' ) );
		add_action( 'admin_init', array( $this, 'schedule_get_critical_cron' ), 20000 );
		add_action( 'wp_footer', array( $this, 'schedule_cron' ), 20000 );
		add_action( 'wp_head', array( $this, 'insert_load_css_script' ) );
		add_action( 'edit_post', array( $this, 'post_edit' ), 11 );
		add_action( 'after_switch_theme', array( $this, 'regenerate_critical_css' ) );
	}

	/**
	 * Add page types to minify settings.
	 *
	 * @param array $defaults An array of default settings.
	 *
	 * @return array
	 */
	public function wp_hummingbird_default_options( $defaults ) {
		$defaults['minify']['critical_page_types'] = Page_Cache::get_page_types( true );

		return $defaults;
	}

	/**
	 * Get module status.
	 *
	 * @return bool
	 */
	public function module_status() {
		if ( ! Utils::is_member() ) {
			return false;
		}

		$options           = Settings::get_settings( 'minify' );
		$critical_css      = $options['critical_css'];
		$critical_css_mode = $options['critical_css_mode'];

		if ( ! $options['enabled'] || ! $critical_css || ( 'critical_css' !== $critical_css_mode ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Function to ignore minify, if critical css is enabled.
	 *
	 * @param boolean $result Result.
	 * @param string $handles Asset Handles.
	 * @param string $type Asset type.
	 *
	 * @return bool
	 */
	public function wphb_dont_combine_handles( $result, $handles, $type ) {
		$options           = Utils::get_module( 'minify' )->get_options();
		$critical_css_type = $options['critical_css_type'];

		if ( 'styles' === $type && 'remove' === $critical_css_type ) {
			return true;
		}

		return $result;
	}

	/**
	 * Add into Item.
	 *
	 * @param string $url URL.
	 * @param string $type Type.
	 * @param string $singular Is singular.
	 */
	private function add_item( $url, $type, $singular = '' ) {
		// Nothing to be added.
		if ( empty( $url ) || empty( $type ) || ! empty( $_GET ) ) {
			return;
		}

		$url = add_query_arg( 'hb_doing_critical', 1, $url );

		$queue = array(
			'url'            => $url,
			'type'           => $type,
			'singular'       => $singular, // For the individual pages.
			'status'         => 'pending', // pending - we have not sent the data for processing, processing - we have sent the data for processing, complete - response has been received.
			'result'         => false,  // False for no css generated , True for css generated .
			'error_message'  => '',
			'error_code'     => '',
			'last_updated'   => '',
			'display_notice' => 0, // 0 - Notice not displayed yet, 1 - Notice has been displayed.
			'id'             => '',
			'hash'           => $this->hash( $type ),
		);

		$this->items[ $queue['hash'] ] = (object) $queue;
	}

	/**
	 * Sets the items for critical CSS generation.
	 */
	private function set_items() {
		// Add frontpage page url to queue.
		if ( ! $this->skip_page_type( 'frontpage' ) ) {
			$this->add_item( home_url( '/' ), 'frontpage' );
		}

		$page_for_posts = get_option( 'page_for_posts' );

		if ( 'page' === get_option( 'show_on_front' ) && ! empty( $page_for_posts ) ) {
			if ( ! $this->skip_page_type( 'home' ) ) {
				$this->add_item( get_permalink( get_option( 'page_for_posts' ) ), 'home' );
			}
		}

		$post_types      = $this->get_public_post_types();
		$site_url_length = strlen( get_site_url() );

		foreach ( $post_types as $post_type ) {
			$get_post_type_url = get_permalink( $post_type->ID );
			if ( ! empty( $get_post_type_url ) && substr( $get_post_type_url, 0, $site_url_length ) === get_site_url() ) {
				if ( ! $this->skip_page_type( $post_type->post_type ) ) {
					$this->add_item( $get_post_type_url, $post_type->post_type );
				}
			}
		}

		$taxonomies = $this->get_public_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			$get_term_url = get_term_link( (int) $taxonomy->ID );
			if ( ! empty( $get_term_url ) && substr( $get_term_url, 0, $site_url_length ) === get_site_url() ) {
				if ( ! $this->skip_page_type( $taxonomy->taxonomy ) ) {
					$this->add_item( $get_term_url, $taxonomy->taxonomy );
				}
			}
		}

		$this->items = (array) apply_filters( 'wphb_css_items', $this->items );
	}

	/**
	 * Push items into queue.
	 *
	 * @param bool $overwrite Overwrite.
	 *
	 * @return boolean
	 */
	private function persist_queue_to_db( $overwrite = false ) {
		if ( empty( $this->items ) ) {
			return false;
		}

		$current_queue = $this->get_persistent_queue();

		if ( empty( $current_queue ) ) {
			update_option( self::QUEUE_OPTION_ID, $this->items );
			return true;
		}

		$updated = false;

		foreach ( $this->items as $hash => $item ) {
			if ( empty( $current_queue[ $hash ] ) || $overwrite ) {
				$updated                = true;
				$current_queue[ $hash ] = $item;
			}
		}

		if ( $updated ) {
			update_option( self::QUEUE_OPTION_ID, $current_queue );
		}

		return $updated;
	}

	/**
	 * Toggle critical CSS function.
	 *
	 * @param bool $value Critical CSS to set.
	 */
	public function toggle_critical_css( $value ) {
		$minify_options               = Utils::get_module( 'minify' );
		$options                      = $minify_options->get_options();
		$options['critical_css']      = $value;
		$options['critical_css_mode'] = 'critical_css';

		$minify_options->update_options( $options );

		// Clear queue and generate critical css.
		if ( ! empty( $value ) ) {
			$this->regenerate_critical_css();
		}
	}

	/**
	 * General purpose function. Returns an array hashed.
	 *
	 * @param array|string $list Array of strings or single string.
	 *
	 * @return string
	 */
	private function hash( $list ) {
		return wp_hash( maybe_serialize( $list ) );
	}

	/**
	 * Should Cache Exit.
	 *
	 * @param bool $should_exit Should Cache Exit. Default: false.
	 */
	public function should_cache_exit( $should_exit ) {
		$queue = $this->get_persistent_queue( array( 'pending', 'processing' ) ); // Fetch only Pending queue.

		if ( empty( $queue ) ) {
			return $should_exit;
		}

		$type = $this->get_url_type();

		if ( empty( $type ) ) {
			return $should_exit;
		}

		$all_pages = array();

		foreach ( $queue as $item ) {
			$all_pages[] = $item->type;
		}

		$singular_css_id                 = $this->get_singular_page_id();
		$single_post_critical_css_status = $this->get_single_post_critical_css_status( $singular_css_id );

		if ( in_array( $type, $all_pages, true ) || 'processing' === $single_post_critical_css_status ) {
			return true;
		}

		return $should_exit;
	}

	/**
	 * Determines if we can add critical css.
	 *
	 * @return boolean
	 */
	public function should_add_critical_css() {
		if ( isset( $_GET['hb_doing_critical'] ) && 1 === absint( $_GET['hb_doing_critical'] ) ) {
			return false;
		}

		if ( is_user_logged_in() ) {
			return false;
		}

		if ( ( defined( 'WPHBDONOTOPTIMIZE' ) && WPHBDONOTOPTIMIZE ) || Utils::is_amp() || Utils::wphb_is_page_builder() || is_preview() || is_customize_preview() ) {
			return false;
		}

		return true;
	}

	/**
	 * Function to apply critical css in buffer.
	 *
	 * @param string $html HTML page buffer.
	 *
	 * @return string
	 */
	public function add_critical_css( $html ) {
		if ( ! $this->should_add_critical_css() ) {
			return $html;
		}
		// Only known url types.
		$type = $this->get_url_type();
		if ( empty( $type ) || $this->ignore_types( $type ) || $this->skip_page_type( $type ) ) {
			return $html;
		}

		// Check if singular type exists.
		$singular_type = $this->maybe_get_singular_page_type();

		if ( false === $singular_type ) {
			return $html;
		}

		$type = $singular_type ?: $type;

		// Setup file variables.
		$used_css_path   = $this->used_css_path( $type );
		$used_css_exists = file_exists( $used_css_path );

		// If Used CSS File exists.
		if ( $used_css_exists ) {
			$options = Utils::get_module( 'minify' )->get_options();

			// Delay stylesheets.
			if ( empty( $options['critical_css_type'] ) || 'remove' === $options['critical_css_type'] ) {
				if ( 'user_interaction_with_remove' === $options['critical_css_remove_type'] ) {
					$html = $this->load_stylesheet_on_user_interaction( $html );
				} else {
					$html = $this->remove_used_css_from_html( $html );
				}
			} elseif ( 'asynchronously' === $options['critical_css_type'] ) {
				$html = $this->make_css_async( $html );
			}

			// Print used css inline after first title tag.
			$pos = strpos( $html, '</title>' );

			if ( false !== $pos ) {
				// IF critical css is generated.
				$generated_critical = file_get_contents( $used_css_path );
				$used_css_output    = $this->get_used_css_markup( $type, $generated_critical );
				$html               = substr_replace( $html, '</title>' . $used_css_output, $pos, 8 );
			}
		}

		// If generated critical css is not available, generate.
		if ( ! $used_css_exists ) {
			$req_url  = is_single() ? get_permalink() : home_url( $_SERVER['REQUEST_URI'] );
			$singular = '';

			// For singular pages.
			if ( 'frontpage' === $type || strpos( $type, 'page-' ) !== false ) {
				$singular = get_the_ID();
			}

			$this->add_item( $req_url, $type, $singular );

			// If new Item is pushed.
			if ( $this->persist_queue_to_db() ) {
				// Fire the cron.
				$this->schedule_cron();
			}
		}

		return $html;
	}

	/**
	 * Get all stylesheets in the HTML.
	 *
	 * @param string $html HTML code.
	 *
	 * @return array
	 */
	public function get_stylesheets( $html ) {
		/**
		 * Filters the pattern used to get all stylesheets in the HTML.
		 *
		 * @since 3.6.0
		 */
		$stylesheet_pattern = apply_filters(
			'wphb_css_stylesheet_pattern',
			'/(?=<link[^>]*\s(id\s*=\s*[\'"](.*)["\']))(?=<link[^>]*\s(rel\s*=\s*[\'"]stylesheet["\']))<link[^>]*\shref\s*=\s*[\'"]([^\'"]+)[\'"](.*)>/iU'
		);

		preg_match_all( $stylesheet_pattern, $html, $stylesheets, PREG_SET_ORDER );

		return $stylesheets;
	}

	/**
	 * Load stylesheet on user interaction.
	 *
	 * @param string $html HTML code.
	 *
	 * @return string
	 */
	public function load_stylesheet_on_user_interaction( $html ) {
		$stylesheets = $this->get_stylesheets( $html );

		if ( ! empty( $stylesheets ) ) {
			foreach ( $stylesheets as $stylesheet ) {
				$style_href = trim( $stylesheet[4] );
				$new_link   = preg_replace( '#href=([\'"]).+?\1#', 'data-wphbdelayedstyle="' . $style_href . '"', $stylesheet[0] );
				$html       = str_replace( $stylesheet[0], $new_link, $html );
			}

			$script = '<script type="text/javascript" id="wphb-delayed-styles-js">
			(function () {
				const events = ["keydown", "mousemove", "wheel", "touchmove", "touchstart", "touchend"];
				function wphb_load_delayed_stylesheets() {
					document.querySelectorAll("link[data-wphbdelayedstyle]").forEach(function (element) {
						element.setAttribute("href", element.getAttribute("data-wphbdelayedstyle"));
					}),
						 events.forEach(function (event) {
						  window.removeEventListener(event, wphb_load_delayed_stylesheets, { passive: true });
						});
				}
			   events.forEach(function (event) {
				window.addEventListener(event, wphb_load_delayed_stylesheets, { passive: true });
			  });
			})();
		</script>';

			$html = str_replace( '</body>', $script . '</body>', $html );
		}

		return $html;
	}

	/**
	 * Remove all CSS which was used on the current page.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function remove_used_css_from_html( $html ) {
		$stylesheets = $this->get_stylesheets( $html );

		if ( ! empty( $stylesheets ) ) {
			foreach ( $stylesheets as $stylesheet ) {
				$html = str_replace( $stylesheet[0], '', $html );
			}
		}

		return $html;
	}

	/**
	 * Convert CSS stylesheet to load asynchronously.
	 *
	 * @param string $html HTML code.
	 *
	 * @return string
	 */
	public function make_css_async( $html ) {
		$stylesheets = $this->get_stylesheets( $html );

		if ( ! empty( $stylesheets ) ) {
			$noscripts = '<noscript>';

			foreach ( $stylesheets as $stylesheet ) {
				if ( preg_match( '/media\s*=\s*[\'"]print[\'"]/i', $stylesheet[0] ) ) {
					continue;
				}

				$preload    = str_replace( 'stylesheet', 'preload', $stylesheet[3] );
				$onload     = preg_replace( '~' . preg_quote( $stylesheet[5], '~' ) . '~iU', ' as="style" onload="" ' . $stylesheet[5] . '>', $stylesheet[5] );
				$tag        = str_replace( $stylesheet[5] . '>', $onload, $stylesheet[0] );
				$tag        = str_replace( $stylesheet[3], $preload, $tag );
				$tag        = str_replace( 'onload=""', 'onload="this.onload=null;this.rel=\'stylesheet\'"', $tag );
				$tag        = preg_replace( '/(id\s*=\s*[\"\'](?:[^\"\']*)*[\"\'])/i', '', $tag );
				$html       = str_replace( $stylesheet[0], $tag, $html );
				$noscripts .= $stylesheet[0];
			}

			$noscripts .= '</noscript>';
			$html      = str_replace( '</body>', $noscripts . '</body>', $html );
		}

		return $html;
	}

	/**
	 * Return Markup for used_css into the page.
	 *
	 * @param string $type Current page type.
	 * @param string $used_css_contents Used CSS content.
	 *
	 * @return string
	 */
	public function get_used_css_markup( $type, $used_css_contents ) {
		$manual_critical = Minify::get_css( 'manual-critical' );

		return sprintf( /* translators: %1$s - page type, %2$s - Used css content, %3$s - manual critical css */
			'<style id="wphb-used-css-%1$s">%2$s%3$s</style>',
			$type,
			$used_css_contents,
			$manual_critical
		);
	}

	/**
	 * Get path to store used css.
	 *
	 * @return string
	 */
	public function get_critical_css_path() {

		if ( is_multisite() ) {
			$blog = get_blog_details();

			if ( '/' === $blog->path ) {
				$site = trailingslashit( $blog->domain );
			} else {
				$site = $blog->path;
			}
		} else {
			// Purge specific folder.
			$http_host = '';
			if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
				$http_host = htmlentities( wp_unslash( $_SERVER['HTTP_HOST'] ) ); // Input var ok.
			} elseif ( function_exists( 'get_option' ) ) {
				$http_host = preg_replace( '/https?:\/\//', '', get_option( 'siteurl' ) );
			}

			$site = $http_host . '/';
		}

		return WP_CONTENT_DIR . '/wphb-cache/critical-css/' . $site;
	}

	/**
	 * Set url to store used css
	 *
	 * @return string
	 */
	public function get_critical_css_url() {
		$site = '';
		if ( is_multisite() ) {
			$blog = get_blog_details();

			if ( '/' === $blog->path ) {
				$site = trailingslashit( $blog->domain );
			} else {
				$site = $blog->path;
			}
		}

		return trailingslashit( content_url() ) . '/wphb-cache/critical-css/' . $site;
	}

	/**
	 * Get path to store used css.
	 *
	 * @param string $type Script or style.
	 *
	 * @return string
	 */
	public function used_css_path( $type ) {
		return $this->get_critical_css_path() . '/' . $type . '-used.css';
	}

	/**
	 * Check if critical css for singular post is exist.
	 *
	 * @return string|boolean
	 */
	private function maybe_get_singular_page_type() {
		// Check if singular type exists.
		$singular_css_id                 = $this->get_singular_page_id();
		$single_post_critical_css_status = $this->get_single_post_critical_css_status( $singular_css_id );

		if ( $single_post_critical_css_status ) {
			// If there was an error in generating singular file or File is being generated.
			if ( 'error' === $single_post_critical_css_status || 'processing' === $single_post_critical_css_status ) {
				return false;
			}

			$singular_type            = $this->get_singular_page_type();
			$singular_used_css_path   = $this->used_css_path( $singular_type );
			$singular_used_css_exists = file_exists( $singular_used_css_path );

			// Update the type.
			if ( $singular_used_css_exists ) {
				return $singular_type;
			}
		}

		return '';
	}

	/**
	 * Get the url type.
	 *
	 * @return string
	 */
	public function get_url_type() {
		global $wp_query;

		$type = '';

		if ( $wp_query->is_page || is_front_page() ) {
			$type = is_front_page() ? 'frontpage' : 'page-' . $wp_query->post->ID;
		} elseif ( $wp_query->is_home ) {
			$type = 'home';
		} elseif ( $wp_query->is_single ) {
			$type = get_post_type() !== false ? get_post_type() : 'single';
		} elseif ( $wp_query->is_category ) {
			$type = 'category';
		} elseif ( $wp_query->is_tag ) {
			$type = 'post_tag';
		} elseif ( $wp_query->is_tax ) {
			$term = get_queried_object();
			$type = $term->taxonomy;
		} elseif ( $wp_query->is_archive ) {
			$type = $wp_query->is_day ? 'day' : ( $wp_query->is_month ? 'month' : ( $wp_query->is_year ? 'year' : ( $wp_query->is_author ? 'author' : 'archive' ) ) );
		} elseif ( $wp_query->is_search ) {
			$type = 'search';
		} elseif ( $wp_query->is_404 ) {
			$type = '404';
		}

		return $type;
	}

	/**
	 * Returns the correct type so that we can correctly check whether to exclude it or not.
	 *
	 * @param string $type type.
	 *
	 * @return string
	 */
	public function get_mapped_type_name_for_skipping( $type ) {
		if ( 'post' === $type ) {
			return 'single';
		} elseif ( 'post_tag' === $type ) {
			return 'tag';
		} elseif ( strpos( $type, 'page-' ) !== false ) {
			return 'page';
		} elseif ( in_array( $type, array( 'day', 'month', 'year', 'author' ), true ) ) {
			return 'archive';
		}

		return $type;
	}

	/**
	 * Process the queue.
	 *
	 * @return boolean|void
	 */
	public function generate_critical_for_queue() {
		// Process the queue.
		if ( get_transient( self::TRANSIENT_NAME ) ) {
			// Still processing. Try again.
			if ( ! $this->is_cron_disabled() ) {
				$this->maybe_schedule_generate_critical_cron();
			}

			return;
		}

		$queue = $this->get_persistent_queue( array( 'pending' ) ); // Get only pending queue to proceed.
		if ( empty( $queue ) ) {
			return;
		}

		set_transient( self::TRANSIENT_NAME, true, self::TRANSIENT_EXPIRATION );
		$status = $this->send_generate_critical_api_request();
		delete_transient( self::TRANSIENT_NAME );

		return $status;
	}

	/**
	 * Ping the critical api.
	 */
	public function get_critical_for_queue() {
		// Process the queue.
		if ( get_transient( self::TRANSIENT_NAME ) ) {
			// Still processing. Try again.
			if ( ! $this->is_cron_disabled() ) {
				$this->maybe_schedule_get_critical_cron();
			}

			return;
		}

		$queue = $this->get_persistent_queue( array( 'processing' ) ); // Get only processed queue.

		if ( empty( $queue ) ) {
			return;
		}

		set_transient( self::TRANSIENT_NAME, true, self::TRANSIENT_EXPIRATION );
		$this->fetch_generated_css_from_api();

		$updated_queue = $this->get_persistent_queue( array( 'processing' ) ); // Get only processed queue.

		if ( ! $this->is_cron_disabled() ) {
			if ( ! empty( $updated_queue ) ) {
				// Still needs processing.
				$this->maybe_schedule_get_critical_cron();
			}
		}

		if ( empty( $updated_queue ) ) {
			// Finish processing.
			delete_transient( self::TRANSIENT_NAME );
		}
	}

	/**
	 * Schedule queue process through WP Cron.
	 */
	public function maybe_schedule_generate_critical_cron() {
		if ( ! wp_next_scheduled( 'wphb_cs_process_queue_cron' ) ) {
			wp_schedule_single_event( time(), 'wphb_cs_process_queue_cron' );
		}
	}

	/**
	 * Schedule queue process through WP Cron.
	 */
	public function maybe_schedule_get_critical_cron() {
		if ( ! wp_next_scheduled( 'wphb_cs_ping_queue_cron' ) ) {
			wp_schedule_single_event( time(), 'wphb_cs_ping_queue_cron' );
		}
	}

	/**
	 * Update queue item with respective statuses.
	 *
	 * @param string $hash          Item hash.
	 * @param bool   $status        Process status.
	 * @param int    $id Queue      ID.
	 * @param string $result        Result.
	 * @param string $error_message Error message.
	 * @param string $error_code    Error code.
	 */
	private function update_item_in_persistent_queue( $hash, $status = false, $id = '', $result = '', $error_message = '', $error_code = '' ) {
		$queue = $this->get_persistent_queue();
		if ( ! empty( $queue[ $hash ] ) ) {
			if ( false !== $status ) {
				$queue[ $hash ]->status = $status;
			}

			if ( $id ) {
				$queue[ $hash ]->id = $id;
			}

			$get_date_time = date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) );

			if ( '' !== $result ) {
				$queue[ $hash ]->result        = $result;
				$queue[ $hash ]->error_message = $error_message;
				$queue[ $hash ]->error_code    = $error_code;
				$queue[ $hash ]->last_updated  = $get_date_time;
			}

			$log_data = array(
				'status'        => $status,
				'result'        => $result,
				'error_message' => $error_message,
				'error_code'    => $error_code,
				'last_updated'  => $get_date_time,
			);

			update_option( 'wphb_critical_css_log', $log_data );
			update_option( self::QUEUE_OPTION_ID, $queue );

			return true;
		}

		return false;
	}

	/**
	 * Get the list of groups that are yet pending to be processed.
	 *
	 * @param array $status An array of status.
	 */
	public function get_persistent_queue( $status = array() ) {
		$wphb_cs_process_queue = $this->get_queue_option();
		if ( empty( $status ) || empty( $wphb_cs_process_queue ) ) {
			return $wphb_cs_process_queue;
		}

		$process_array = array();

		foreach ( $wphb_cs_process_queue as $hash => $item ) {
			if ( isset( $item->status ) && in_array( $item->status, $status, true ) ) {
				$process_array[ $hash ] = $item;
			}
		}

		return $process_array;
	}

	/**
	 * Get the critical queue by hash.
	 *
	 * @param bool $hash Hash.
	 */
	public function get_queue_item_by_hash( $hash ) {
		$wphb_cs_process_queue = $this->get_queue_option();
		if ( false === $hash || empty( $wphb_cs_process_queue ) || empty( $wphb_cs_process_queue[ $hash ] ) ) {
			return false;
		}

		return $wphb_cs_process_queue[ $hash ];
	}

	/**
	 * Fetch the first id.
	 */
	public function fetch_id_from_processing_queue() {
		$wphb_cs_process_queue = $this->get_queue_option();
		if ( empty( $wphb_cs_process_queue ) ) {
			return false;
		}

		$id = false;

		foreach ( $wphb_cs_process_queue as $hash => $item ) {
			if ( isset( $item->status ) && 'processing' === $item->status ) {
				$id = $item->id;
				break;
			}
		}

		return $id;
	}

	/**
	 * Fetch all the queue by id.
	 *
	 * @param int $id ID.
	 */
	public function get_queue_items_by_job_id( $id ) {
		$process_array         = array();
		$wphb_cs_process_queue = $this->get_queue_option();

		if ( empty( $wphb_cs_process_queue ) && false === $id ) {
			return $process_array;
		}

		foreach ( $wphb_cs_process_queue as $hash => $item ) {
			if ( isset( $item->id ) && $item->id === $id ) {
				$process_array[ $hash ] = $item;
			}
		}

		return $process_array;
	}

	/**
	 * Deletes the persistent queue completely
	 */
	public function delete_pending_persistent_queue() {
		delete_option( self::QUEUE_OPTION_ID );
		wp_cache_delete( self::QUEUE_OPTION_ID, 'options' );
	}

	/**
	 * Clear pending queue.
	 */
	public function clear_pending_process_queue() {
		$this->delete_pending_persistent_queue();
		delete_transient( self::TRANSIENT_NAME );

		// Clear cron events.
		if ( wp_next_scheduled( 'wphb_cs_process_queue_cron' ) ) {
			wp_clear_scheduled_hook( 'wphb_cs_process_queue_cron' );
		}
		// Clear cron events.
		if ( wp_next_scheduled( 'wphb_cs_ping_queue_cron' ) ) {
			wp_clear_scheduled_hook( 'wphb_cs_ping_queue_cron' );
		}
	}

	/**
	 * CLear critical css data and files.
	 */
	public function regenerate_critical_css() {
		if ( ! $this->is_active() ) {
			return;
		}

		// Clear cache before generating the critical css.
		Utils::get_module( 'page_cache' )->clear_cache();

		$this->clear_pending_process_queue();
		$this->delete_all_post_meta_for_critical();
		$this->delete_css_cache_files();

		$this->set_items();
		$this->persist_queue_to_db();
		$this->generate_critical_for_queue();
	}

	/**
	 * Trigger the action to process the ping queue.
	 */
	public function schedule_get_critical_cron() {
		$queue_processing = $this->get_persistent_queue( array( 'processing' ) ); // Get all the pending Queue.

		if ( ! empty( $queue_processing ) ) {
			if ( $this->is_cron_disabled() ) {
				$this->get_critical_for_queue();
			} else {
				$this->maybe_schedule_get_critical_cron();
			}
		}
	}

	/**
	 * Schedule the first cron if we need to process the queue for generation or schedule the second cron if we need to fetch the generated critical css.
	 */
	public function schedule_cron() {
		$this->schedule_generate_critical_cron();
		$this->schedule_get_critical_cron();
	}

	/**
	 * Process the critical css.
	 *
	 * @return boolean|void
	 */
	public function send_generate_critical_api_request() {
		$queue = $this->get_persistent_queue( array( 'pending' ) ); // Get only pending queue to proceed.

		if ( empty( $queue ) ) {
			return;
		}

		$urls = null;

		foreach ( $queue as $hash => $item ) {
			$urls[ $hash ] = $item->url;
		}

		$api     = Utils::get_api();
		$options = Utils::get_module( 'minify' )->get_options();

		if ( empty( $options['critical_css_type'] ) || 'remove' === $options['critical_css_type'] ) {
			$api_call_type = 'PURGE';
		} else {
			$api_call_type = 'CRITICAL';
		}

		$response      = $api->performance->generate_critical_css( $urls, $api_call_type );
		$is_type_error = true;

		if ( ! is_wp_error( $response ) && ! empty( $response ) ) {
			$response = json_decode( wp_remote_retrieve_body( (array) $response ) );
			// Fetch the id.
			$success = ! empty( $response ) && 1 === absint( $response->success );

			if ( $success ) {
				// Fetch the id.
				$response_id = ! empty( $response->id ) ? $response->id : false;

				// Update Queue.
				$is_type_error = false;

				// If id is received from the response, update in the queue.
				if ( $response_id ) {
					foreach ( $urls as $hash => $url ) {
						$this->update_item_in_persistent_queue( $hash, 'processing', $response_id );
					}
				}
			}
		}

		// Log the error.
		if ( $is_type_error ) {
			$api_error = is_wp_error( $response ) ? $response->get_error_message() : esc_html__( 'HB critical unknown error', 'wphb' );
			Utils::get_module( 'minify' )->log( 'There is an error in calling critical api ' . $api_error );

			// Update the pending queues.
			foreach ( $urls as $hash => $url ) {
				$this->update_item_in_persistent_queue( $hash, 'complete', '', 'ERROR', $api_error );
				$hash_object = $this->get_queue_item_by_hash( $hash );
				$singular    = ! empty( $hash_object->singular ) ? $hash_object->singular : '';

				if ( ! empty( $singular ) ) {
					$this->delete_post_meta_for_single_post( $singular );
					update_post_meta( $singular, self::CRITICAL_POST_META_KEY, 'error' );
				}
			}
		}

		return ! $is_type_error;
	}

	/**
	 * Ping the critical api to fetch the css.
	 *
	 * @return boolean|void
	 */
	public function fetch_generated_css_from_api() {
		$fs = Filesystem::instance();
		$id = $this->fetch_id_from_processing_queue();

		if ( ! $id ) {
			return;
		}

		$api      = Utils::get_api();
		$response = $api->performance->get_generated_critical_css( $id );

		if ( ! is_wp_error( $response ) ) {
			$response      = json_decode( wp_remote_retrieve_body( (array) $response ) );
			$status        = isset( $response->status ) ? $response->status : '';
			$urls          = isset( $response->urls ) ? $response->urls : '';
			$error_message = ! empty( $response->errorMessage ) ? $response->errorMessage : '';
			$error_code    = ! empty( $response->errorCode ) ? $response->errorCode : '';

			if ( 'COMPLETE' === $status ) {
				$critical_css = isset( $response->criticalCss ) ? $response->criticalCss : array();
				foreach ( $critical_css as $hash => $css_value ) {
					$hash_object = $this->get_queue_item_by_hash( $hash );
					if ( ! empty( $css_value ) && ! empty( $hash_object ) ) {

						$type     = ! empty( $hash_object->type ) ? $hash_object->type : '';
						$singular = ! empty( $hash_object->singular ) ? $hash_object->singular : '';

						$used_css_path = $this->used_css_path( $type );
						// Create the css file.
						$status_file = $fs->write( $used_css_path, apply_filters( 'wphb_used_css', $css_value ) );

						// If singular page.
						if ( ! empty( $singular ) ) {
							update_post_meta( $singular, self::CRITICAL_POST_META_KEY, 'complete' );
						}
					}

					$this->update_item_in_persistent_queue( $hash, 'complete', '', $status, $error_message, $error_code );
				}
			} elseif ( 'ERROR' === $status && ! empty( $urls ) ) {
				foreach ( $urls as $hash => $url_value ) {
					$hash_object = $this->get_queue_item_by_hash( $hash );

					if ( ! empty( $hash_object ) ) {
						$singular = ! empty( $hash_object->singular ) ? $hash_object->singular : '';
						// If singular page.
						if ( ! empty( $singular ) ) {
							$this->delete_post_meta_for_single_post( $singular );
							update_post_meta( $singular, self::CRITICAL_POST_META_KEY, 'error' );
						}
					}

					$this->update_item_in_persistent_queue( $hash, 'complete', '', $status, $error_message, $error_code );
				}
			} elseif ( 'ERROR' === $status ) {
				$this->update_item_for_general_api_error( $id, $error_message, $error_code );
			}
		} else {
			// If any API error occurs.
			$api_error = $response->get_error_message();
			$this->update_item_for_general_api_error( $id, $api_error, $api_error );
		}
	}

	/**
	 * Function to update the item if there is a general API error, e.g. job not found.
	 *
	 * @param int    $id            Job ID.
	 * @param string $error_message Error message.
	 * @param string $error_code    Error code.
	 */
	public function update_item_for_general_api_error( $id, $error_message, $error_code ) {
		Utils::get_module( 'minify' )->log( 'There is an error in fetching data from the critical api ' . $error_message );
		$queue_by_ids = $this->get_queue_items_by_job_id( $id );

		foreach ( $queue_by_ids as $hash => $id_value ) {
			$singular = ! empty( $id_value->singular ) ? $id_value->singular : '';
			// If singular page.
			if ( ! empty( $singular ) ) {
				$this->delete_post_meta_for_single_post( $singular );
				update_post_meta( $singular, self::CRITICAL_POST_META_KEY, 'error' );
			}

			$this->update_item_in_persistent_queue( $hash, 'complete', '', 'ERROR', $error_message, $error_code );
		}
	}

	/**
	 * Function to delete all the critical css files.
	 */
	public function delete_css_cache_files() {
		global $wphb_fs;
		if ( ! $wphb_fs ) {
			$wphb_fs = Filesystem::instance();
		}

		$directory = 'critical-css';

		$is_network_admin = false;
		if ( is_multisite() && isset( $_SERVER['HTTP_REFERER'] ) ) {
			$is_network_admin = preg_match( '#^' . network_admin_url() . '#i', $_SERVER['HTTP_REFERER'] );
		}

		// For multisite we need to set this to null.
		if ( is_multisite() && ! $is_network_admin ) {
			$current_blog = get_site( get_current_blog_id() );
			$directory    .= $current_blog->path;
		}

		return $wphb_fs->purge( $directory );
	}

	/**
	 * Function to get the singular css.
	 *
	 * @param int $post_id Post ID.
	 */
	public function get_single_post_critical_css_status( $post_id ) {
		return get_post_meta( $post_id, self::CRITICAL_POST_META_KEY, true );
	}

	/**
	 * Function to create critical css file for post.
	 *
	 * @param int $id Post ID.
	 */
	public function create_post_css_file( $id ) {
		$type    = $this->make_post_type_key( $id );
		$req_url = get_permalink( $id );
		update_post_meta( $id, self::CRITICAL_POST_META_KEY, '' );

		$this->add_item( $req_url, $type, $id );
		// If new Item is pushed.
		if ( $this->persist_queue_to_db( true ) ) {
			// Delete Existing CSS File.
			$this->unlink_generated_critical_css_file( $type, $id );
			update_post_meta( $id, self::CRITICAL_POST_META_KEY, 'processing' );
			// Fire the cron.
			$this->generate_critical_for_queue();
		}

		return true;
	}

	/**
	 * Function to create critical css file for post.
	 *
	 * @param int $id Post ID.
	 */
	public function recreate_post_css_file( $id ) {
		return $this->create_post_css_file( $id );
	}

	/**
	 * Delete a generated critical css file.
	 *
	 * @param string $type Post type.
	 * @param int $post_id Post ID.
	 */
	public function unlink_generated_critical_css_file( $type, $post_id ) {
		$used_css_path           = $this->used_css_path( $type );
		$is_hb_critical_css_path = false !== strpos( $used_css_path, 'wphb-cache' ) && false !== strpos( $used_css_path, 'critical-css' );

		if ( file_exists( $used_css_path ) && $is_hb_critical_css_path ) {
			unlink( $used_css_path );

			if ( $post_id ) {
				do_action( 'wphb_clear_page_cache', $post_id ); // Clear page cache for the supplied post.
			}

			return true;
		}

		return false;
	}

	/**
	 * Function to revert critical css file for post.
	 *
	 * @param int $id Post ID.
	 */
	public function revert_post_css_file( $id ) {
		// Delete Existing CSS FIle.
		$type = $this->make_post_type_key( $id );
		$this->unlink_generated_critical_css_file( $type, $id );

		$this->delete_post_meta_for_single_post( $id );

		return true;
	}

	/**
	 * Delete post meta key for single post.
	 *
	 * @param int $id Post ID.
	 */
	public function delete_post_meta_for_single_post( $id ) {
		delete_post_meta( $id, self::CRITICAL_POST_META_KEY );

		return true;
	}

	/**
	 * Deletes all the post meta critical key.
	 */
	public function delete_all_post_meta_for_critical() {
		global $wpdb;
		$post_table = $wpdb->prefix . 'postmeta';

		$wpdb->delete( $post_table, array( 'meta_key' => self::CRITICAL_POST_META_KEY ) );
	}

	/**
	 * Skip page type selected in settings.
	 *
	 * @since   3.6.0
	 * @access  public
	 * @param bool $type Post Type.
	 *
	 * @return bool
	 */
	public function skip_page_type( $type ) {
		$minify_options                     = Settings::get_settings( 'minify' );
		$critical_page_types                = $minify_options['critical_page_types'];
		$critical_skipped_custom_post_types = $minify_options['critical_skipped_custom_post_types'];
		$all_pages_type                     = Page_Cache::get_page_types( true );

		if ( $type ) {
			$type = $this->get_mapped_type_name_for_skipping( $type );
			if ( ! empty( $critical_skipped_custom_post_types ) && in_array( $type, $critical_skipped_custom_post_types, true ) ) {
				return true;
			}

			if ( in_array( $type, $all_pages_type, true ) && ! in_array( $type, $critical_page_types, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Fetch the queue by type.
	 *
	 * @param int $type Type.
	 */
	public function get_queue_item_by_type( $type ) {
		if ( empty( $type ) ) {
			return false;
		}

		$get_hash = $this->hash( $type );

		return $this->get_queue_item_by_hash( $get_hash );
	}

	/**
	 * Fires on edit_post action.
	 *
	 * @param int $post_id Post ID.
	 */
	public function post_edit( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Only trigger for public page.
		$post = get_post( $post_id );

		if ( ! isset( $post->post_type ) || ! is_post_type_viewable( $post->post_type ) ) {
			return;
		}

		$type             = $this->make_post_type_key( $post_id );
		$processing_queue = $this->get_queue_item_by_type( $type );

		// If no object found.
		if ( empty( $processing_queue ) ) {
			return;
		}

		$used_css_path = $this->used_css_path( $type );

		$this->recreate_post_css_file( $post_id );
	}

	/**
	 * Gets all public post types.
	 */
	private function get_public_post_types() {
		global $wpdb;

		$post_types = get_post_types(
			array(
				'public'             => true,
				'publicly_queryable' => true,
			)
		);

		/**
		 * Exclude the post types.
		 *
		 * @return array
		 */
		$excluded_post_types = array(
			'blocks',
			'cms_block',
			'elementor_library',
			'fl-builder-template',
			'fusion_template',
			'jet-woo-builder',
			'karma-slider',
			'oceanwp_library',
			'slider',
			'tbuilder_layout',
			'tbuilder_layout_part',
			'tt-gallery',
			'web-story',
			'xlwcty_thankyou',
		);

		// Apply Filter.
		$excluded_post_types = (array) apply_filters( 'wphb_css_excluded_post_types', $excluded_post_types );

		$post_types = array_diff( $post_types, $excluded_post_types );
		$post_types = esc_sql( $post_types );
		$post_types = "'" . implode( "','", $post_types ) . "'";

		$result = $wpdb->get_results(
			"SELECT MAX(ID) as ID, post_type
			FROM (
				SELECT ID, post_type
				FROM $wpdb->posts
				WHERE post_type IN ( $post_types )
				AND post_status = 'publish'
				ORDER BY post_date DESC
			) AS posts
			GROUP BY post_type"
		);

		if ( ! is_wp_error( $result ) ) {
			return $result;
		}

		return array();
	}

	/**
	 * Gets all public taxonomies.
	 */
	private function get_public_taxonomies() {
		global $wpdb;

		$taxonomies = get_taxonomies(
			array(
				'public'             => true,
				'publicly_queryable' => true,
			)
		);

		$excluded_taxonomies = array(
			'attachment_category',
			'coupon_campaign',
			'element_category',
			'karma-slider-category',
			'mediamatic_wpfolder',
			'post_format',
			'product_shipping_class',
			'truethemes-gallery-category',
		);
		$excluded_taxonomies = (array) apply_filters(
			'wphb_css_excluded_taxonomies',
			$excluded_taxonomies
		);

		$taxonomies = array_diff( $taxonomies, $excluded_taxonomies );
		$taxonomies = esc_sql( $taxonomies );
		$taxonomies = "'" . implode( "','", $taxonomies ) . "'";

		$result = $wpdb->get_results(
			"SELECT MAX( term_id ) AS ID, taxonomy
			FROM (
				SELECT term_id, taxonomy
				FROM $wpdb->term_taxonomy
				WHERE taxonomy IN ( $taxonomies )
				AND count > 0
			) AS taxonomies
			GROUP BY taxonomy"
		);

		if ( ! is_wp_error( $result ) ) {
			return $result;
		}

		return array();
	}

	/**
	 * Get the page id.
	 *
	 * @return string
	 */
	public function get_singular_page_type() {
		global $wp_query;
		$page_type = false;
		if ( $wp_query->is_page ) {
			$page_type = 'page-' . $wp_query->post->ID;
		} elseif ( $wp_query->is_single ) {
			$page_type = $this->make_post_type_key( $wp_query->post->ID );
		}

		return $page_type;
	}

	/**
	 * Get the page id.
	 *
	 * @return string
	 */
	private function get_singular_page_id() {
		global $wp_query;
		$page_id = false;

		if ( $wp_query->is_page || $wp_query->is_single ) {
			$page_id = $wp_query->post->ID;
		}

		return $page_id;
	}

	/**
	 * Function to ignore types.
	 *
	 * @param string $type Page type.
	 */
	public function ignore_types( $type ) {
		$ignore_types = array( '404' );

		if ( in_array( $type, $ignore_types, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * This notice is displayed when the critical CSS generation is complete.
	 *
	 * @since 3.6.0
	 */
	public function critical_css_generation_complete_notice() {
		$wphb_cs_process_queue          = $this->get_queue_option();
		$wphb_cs_created_css_log_update = array();
		$update_css_log                 = false;
		$message                        = '';
		$group_id                       = null;

		if ( $wphb_cs_process_queue && is_array( $wphb_cs_process_queue ) ) {
			foreach ( $wphb_cs_process_queue as $hash => $queue_array ) {
				$keep_queue_key = true;

				if ( 'complete' === $queue_array->status && 0 === $queue_array->display_notice ) {
					if ( 'COMPLETE' !== $queue_array->result ) {

						if ( $group_id !== $queue_array->id ) {
							$group_id = $queue_array->id;
							$error    = ! empty( $queue_array->error_message ) ? __( '<em> Error: ', 'wphb' ) . $queue_array->error_message . ' </em>' : $queue_array->result;
							$message .= sprintf(
							// translators: %1$s = error message.
								__( '<br />%1$s', 'wphb' ),
								'<strong>' . $error . '</strong>'
							);
						}
					} else {
						$keep_queue_key = false;
					}

					$update_css_log = true;
				}

				if ( $keep_queue_key ) {
					$wphb_cs_created_css_log_update[ $hash ] = $queue_array;
				}
			}

			if ( $update_css_log ) {
				update_option( self::QUEUE_OPTION_ID, $wphb_cs_created_css_log_update );
				// If all the statuses are completed.
				if ( ! empty( $message ) ) {
					$message = sprintf(
					// translators: %1$s = message.
						__( 'Critical CSS generation failed. Please review the below error to troubleshoot.%1$s', 'wphb' ),
						$message
					);
				}
			}
		}

		return $message;
	}

	/**
	 * Adds load css script.
	 */
	public function insert_load_css_script() {
		$options  = Utils::get_module( 'minify' )->get_options();
		$is_async = 'asynchronously' === $options['critical_css_type'] || ( 'remove' === $options['critical_css_type'] && 'async_with_remove' === $options['critical_css_remove_type'] );

		if ( ! $is_async ) {
			return;
		}

		// Don't load on search page.
		if ( is_search() ) {
			return;
		}

		// Don't load on 404 page.
		if ( is_404() ) {
			return;
		}

		echo '<script>
		/*! loadCSS rel=preload polyfill. [c]2017 Filament Group, Inc. MIT License */
		(function(w){"use strict";if(!w.loadCSS){w.loadCSS=function(){}}
		var rp=loadCSS.relpreload={};rp.support=(function(){var ret;try{ret=w.document.createElement("link").relList.supports("preload")}catch(e){ret=!1}
		return function(){return ret}})();rp.bindMediaToggle=function(link){var finalMedia=link.media||"all";function enableStylesheet(){link.media=finalMedia}
		if(link.addEventListener){link.addEventListener("load",enableStylesheet)}else if(link.attachEvent){link.attachEvent("onload",enableStylesheet)}
		setTimeout(function(){link.rel="stylesheet";link.media="only x"});setTimeout(enableStylesheet,3000)};rp.poly=function(){if(rp.support()){return}
		var links=w.document.getElementsByTagName("link");for(var i=0;i<links.length;i++){var link=links[i];if(link.rel==="preload"&&link.getAttribute("as")==="style"&&!link.getAttribute("data-loadcss")){link.setAttribute("data-loadcss",!0);rp.bindMediaToggle(link)}}};if(!rp.support()){rp.poly();var run=w.setInterval(rp.poly,500);if(w.addEventListener){w.addEventListener("load",function(){rp.poly();w.clearInterval(run)})}else if(w.attachEvent){w.attachEvent("onload",function(){rp.poly();w.clearInterval(run)})}}
		if(typeof exports!=="undefined"){exports.loadCSS=loadCSS}
		else{w.loadCSS=loadCSS}}(typeof global!=="undefined"?global:this))
		</script>';
	}

	/**
	 * This notice is displayed when the critical CSS generation is complete.
	 *
	 * @since 3.6.0
	 */
	public function critical_css_status_for_queue() {
		if ( ! $this->is_active() ) {
			return false;
		}

		$critical_css_log = $this->get_log_option();

		if ( empty( $critical_css_log ) ) {
			return false;
		}

		return $critical_css_log;
	}

	/**
	 * Get html data for status tag.
	 *
	 * @return bool|string
	 * @since 3.6.0
	 */
	public function get_html_for_status_tag() {
		if ( ! Utils::is_member() || ! $this->is_active() ) {
			return '<span id="critical_progress_tag"></span>';
		}

		$critical_css_log = $this->get_log_option();

		$status = isset( $critical_css_log['status'] ) ? $critical_css_log['status'] : '';
		if ( $this->is_active() && ( 'processing' === $status || 'pending' === $status ) ) {
			$tag_display_value = esc_html__( 'Optimizing ', 'wphb' );
			$sui_tag           = 'sui-tag sui-tag-blue sui-tooltip sui-tooltip-constrained';
			$sui_icon          = 'sui-icon-loader sui-loading';
			$tooltip_text      = esc_html__( 'Generating Critical CSS, this could take about a minute.', 'wphb' );
		} elseif ( $this->is_active() && 'complete' === $status ) {
			$is_result_complete = 'COMPLETE' === $critical_css_log['result'];
			$tag_display_value  = $is_result_complete ? esc_html__( 'Optimized', 'wphb' ) : esc_html__( 'Error', 'wphb' );
			$sui_tag            = $is_result_complete ? 'sui-tag sui-tag-green sui-tooltip sui-tooltip-constrained' : 'sui-tag sui-tag-yellow';
			$sui_icon           = $is_result_complete ? 'sui-icon-info' : 'sui-icon-info sui-icon-error';
			$tooltip_text       = $is_result_complete ? esc_html__( 'Last Generated: ', 'wphb' ) . $critical_css_log['last_updated'] : '';
		} else {
			$tag_display_value = esc_html__( 'Unoptimized', 'wphb' );
			$sui_tag           = 'sui-tag sui-tag-grey sui-tooltip sui-tooltip-constrained';
			$sui_icon          = 'sui-icon-info';
			$tooltip_text      = esc_html__( 'Select settings and saves changes to generate Critical CSS', 'wphb' );
		}

		return sprintf(
		// translators: %1$s = tooltip text, %2$s = sui tag, %3$s = info icon, %4$s = text for tag.
			__( '<span id="critical_progress_tag" data-tooltip="%1$s" class="%2$s"><span class="%3$s" aria-hidden="true"></span>%4$s</span>', 'wphb' ),
			$tooltip_text,
			$sui_tag,
			$sui_icon,
			$tag_display_value
		);
	}

	/**
	 * Get processing queue data.
	 */
	private function get_queue_option() {
		return get_option( self::QUEUE_OPTION_ID, array() );
	}

	/**
	 * Get status tag data.
	 */
	private function get_log_option() {
		return get_option( 'wphb_critical_css_log', array() );
	}

	/**
	 * Get key for single post type.
	 *
	 * @param int $id Post ID.
	 */
	private function make_post_type_key( $id ) {
		return get_post_type( $id ) . '-' . $id;
	}

	/**
	 * Checks if cron is disabled or not. Returns true if disabled false otherwise.
	 */
	private function is_cron_disabled() {
		return defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
	}

	/**
	 * Schedule the cron to fetch the generated critical css.
	 *
	 * @return void
	 */
	private function schedule_generate_critical_cron() {
		$queue_pending = $this->get_persistent_queue( array( 'pending' ) ); // Get all the pending Queue.
		if ( ! empty( $queue_pending ) ) {
			if ( $this->is_cron_disabled() ) {
				$this->generate_critical_for_queue();
			} else {
				$this->maybe_schedule_generate_critical_cron();
			}
		}
	}

	/**
	 * Returns the formatted error message for MP.
	 *
	 * @param array $item_detail Item detail.
	 */
	public function get_error_code_from_log( $item_detail = array() ) {
		$critical_css_log = ! empty( $item_detail ) ? $item_detail : $this->get_log_option();
		$error_code       = '';

		if ( isset( $critical_css_log['result'] ) && 'ERROR' === $critical_css_log['result'] ) {
			$error_code = isset( $critical_css_log['error_code'] ) ? $critical_css_log['error_code'] : 'unknown';
		}

		return $error_code;
	}
}
