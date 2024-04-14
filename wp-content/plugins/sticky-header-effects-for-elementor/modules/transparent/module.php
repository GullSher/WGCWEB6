<?php
namespace SheHeader\Modules\Transparent;

use Elementor;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use SheHeader\Base\Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();

		$this->add_actions();
	}

	public function get_name() {
		return 'transparent';
	}

	public function register_controls( Controls_Stack $element ) {
		$element->start_controls_section(
			'section_sticky_header_effect',
			[
				'label' => __( 'Sticky Header Effects', 'she-header' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'transparent',
			[
				'label' => __( 'Enable', 'she-header' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'On', 'she-header' ),
				'label_off' => __( 'Off', 'bew-header' ),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'prefix_class'  => 'she-header-'
			]
		);

		$element->add_control(
			'sticky_header_notice',
			[
				'raw' => __( 'IMPORTANT: This plugin does NOT control the sticky position of the header. Please use the above Motion Effects tab sticky options to make the header sticky', 'she-header' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition' => [

					'transparent!' => '',
				],
			]
		);

		$element->add_control(
			'transparent_on',
			[
				'label' => __( 'Enable On', 'she-header' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'label_block' => 'true',
				'default' => [ 'desktop', 'tablet', 'mobile' ],
				'options' => [
					'desktop' => __( 'Desktop', 'she-header' ),
					'tablet' => __( 'Tablet', 'she-header' ),
					'mobile' => __( 'Mobile', 'she-header' ),
				],
				'condition' => [
					'transparent!' => ''
				],
				'render_type' => 'none',
				'description' => __( 'This will completely enable/disable settings below.<br>
				*MAY NOT AFFECT SOME SETTINGS WITH RESPONSIVE CONTROLS', 'she-header' ),
				'frontend_available' => true,
			]
		);


$element->add_responsive_control(
			'scroll_distance',
			[
				'label' => __( 'Scroll Distance (px)', 'she-header' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 60,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
				],
				'size_units' => [ 'px'],
				'description' => __( 'Choose the scroll distance to enable Sticky Header Effects', 'she-header' ),
				'frontend_available' => true,
				'condition' => [
					'transparent!' => '',
				],
			]
		);

		$element->add_control(
			'settings_notice',
			[
				'raw' => __( 'Remember: The settings below will not be applied until the page is scrolled the distance set above', 'she-header' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'transparent!' => '',
					],
			]
		);

		$element->add_control(
			'transparent_header_show',
			[
				'label' => __( 'Transparent Header', 'she-header' ),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => __( 'On', 'she-header' ),
				'label_off' => __( 'Off', 'bew-header' ),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'condition' => [
					'transparent!' => '',
				],
				'description' => __( 'Sets the header position to "absolute" so negative margins are not needed', 'she-header' ),
			]
		);

		$element->add_control(
			'transparent_note',
			[
				'raw' => __( 'IMPORTANT: This will make the header overlap the main page so extra spacing at the top of sections may be necesary. **May only work on frontend', 'she-header' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'transparent_header_show' => 'yes',
					'transparent!' => '',
					],
			]
		);

		$element->add_control(
			'transparent_important',
			[
				'label' => __( 'Above Header Sections', 'she-header' ),
				'raw' => __( '<br>"Scroll Distance" settings and Elementor "Motion Effects > Offset" settings should be set to the height of any section above the header.<br>Example: Above header section min-height = 60px. Set both scroll distance and motion effects offset to 60px', 'she-header' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'transparent_header_show' => 'yes',
					'transparent!' => '',
					],
			]
		);

		$element->add_control(
			'background_show',
			[
				'label' => __( 'Background Color', 'she-header' ),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => __( 'On', 'she-header' ),
				'label_off' => __( 'Off', 'bew-header' ),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'condition' => [
					'transparent!' => '',
				],
				'description' => __( 'Choose what color to change the background to after scrolling', 'she-header' ),
			]
		);

		$element->add_control(
			'background',
			[
				'label' => __( 'Color', 'she-header' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
				    'background_show' => 'yes',
					'transparent!' => '',
				],
				'render_type' => 'none',
				'frontend_available' => true,
			]
		);

		$element->add_control(
			'bottom_border',
			[
				'label' => __( 'Bottom Border', 'she-header' ),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => __( 'On', 'she-header' ),
				'label_off' => __( 'Off', 'she-header' ),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'condition' => [
					'transparent!' => '',
				],
				'description' => __( 'Choose bottom border size and color', 'she-header' ),
			]
		);


		$element->add_control(
			'custom_bottom_border_color',
			[
				'label' => __( 'Color', 'she-header' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
				    'bottom_border' => 'yes',
					'transparent!' => '',
				],
				'render_type' => 'none',
				'frontend_available' => true,
			]
		);

		$element->add_responsive_control(
			'custom_bottom_border_width',
			[
				'label' => __( 'Bottom Border Thickness (px)', 'she-header' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px'],
				'description' => __( 'Note: A border size(even 0px) must be set on the header for the transition to work both ways', 'she-header' ),
				'condition' => [
				    'bottom_border' => 'yes',
					'transparent!' => '',
				],
				'frontend_available' => true,
			]
		);

		$element->add_control(
			'shrink_header',
			[
				'label' => __( 'Shrink Header', 'she-header' ),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => __( 'On', 'she-header' ),
				'label_off' => __( 'Off', 'she-header' ),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'description' => __( 'Choose header height after scrolling', 'she-header' ),
				'condition' => [
					'transparent!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'custom_height_header',
			[
				'label' => __( 'Header Height (px)', 'she-header' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 70,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
				],
				'size_units' => [ 'px'],
				'description' => __( 'Remember: The header cannot shrink smaller than the elements inside of it', 'she-header' ),
				'condition' => [
				   'shrink_header' => 'yes',
					'transparent!' => '',
				],
				'frontend_available' => true,
			]
		);

$element->add_control(
			'shrink_header_logo',
			[
				'label' => __('Shrink Logo', 'she-header'),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => __('On', 'she-header'),
				'label_off' => __('Off', 'she-header'),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'description' => __('Resize logo after scrolling', 'she-header'),
				'condition' => [
					'transparent!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'custom_height_header_logo',
			[
				'label' => __( 'Logo Height (%)', 'she-header' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 100,
				],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ '%'],
				'condition' => [
				    'shrink_header_logo' => 'yes',
					'transparent!' => '',
				],
				'frontend_available' => true,
				// 'description' => __('<b>Depricated:</b> Responsive sizes only load on page refresh, not window resize. Please migrate to using the settings below', 'she-header'),
			]
		);

// ---------------------------------- LOGO COLOR TOGGLE

     $element->add_control(
		'change_logo_color',
		[
			'label' => __( 'Change Logo Color', 'she-header' ),
			'type' => Controls_Manager::SWITCHER,
			'separator' => 'before',
			'label_on' => __( 'On', 'she-header' ),
			'label_off' => __( 'Off', 'she-header' ),
			'return_value' => 'yes',
			'default' => '',
			'frontend_available' => true,
			'description' => __( 'Change the logo image color before or after the user reaches the scroll distance set above', 'she-header' ),
			'condition' => [
				'transparent!' => '',
			],
		]
	);

// ---------------------------------- LOGO COLOR NOTICE

$element->add_control(
'logo_color_notice',
[
	'raw' => __( 'Please select <b>only 1 option</b> for each tab', 'she-header' ),
	'type' => Controls_Manager::RAW_HTML,
	'content_classes' => 'elementor-descriptor',
	'condition' => [
		'change_logo_color' => 'yes',
		'transparent!' => '',
	],
 ]
);

// ---------------------------------- LOGO COLOR TABS

  $element->start_controls_tabs(
		 'logo_color_tabs'
	 );

// ---------------------------------- LOGO BEFORE TAB

  $element->start_controls_tab(
		 'before_tab',
		 [
	'label' => __( 'Before scrolling', 'she-header' ),
		 'condition' => [
				'change_logo_color' => 'yes',
				'transparent!' => '',
			],
		]
	);

// ---------------------------------- LOGO WHITE BEFORE

	$element->add_control(
		'logo_color_white_before',
		[
			'label' => __( 'White Logo', 'she-header' ),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => __( 'On', 'she-header' ),
			'label_off' => __( 'Off', 'she-header' ),
			'return_value' => 'yes',
			'default' => '',
			'frontend_available' => true,
			'description' => __( 'Change the logo to white', 'she-header' ),
			'prefix_class'  => 'she-header-change-logo-color-',
			'condition' => [
				'change_logo_color' => 'yes',
				'transparent!' => '',
			],
			'selectors' => [
				'{{WRAPPER}}.she-header-yes:not(.she-header) .elementor-widget-theme-site-logo:not(.elementor-widget-n-menu .elementor-widget-theme-site-logo) .elementor-widget-container, 
				{{WRAPPER}}.she-header-yes:not(.she-header) .elementor-widget-image:not(.elementor-widget-n-menu .elementor-widget-image) .elementor-widget-container, 
				{{WRAPPER}}.she-header-yes:not(.she-header) .logo .elementor-widget-container' => '-webkit-filter: brightness(0) invert(1); filter: brightness(0) invert(1);',
				'{{WRAPPER}}.she-header-yes:not(.she-header) .elementor-widget-n-menu .elementor-widget-image .elementor-widget-container, 
				{{WRAPPER}}.she-header-yes:not(.she-header) .not-logo .elementor-widget-container' => '-webkit-filter: none; filter: none;'
			],
		]
	);

// ---------------------------------- LOGO BLACK BEFORE

 $element->add_control(
		'logo_color_black_before',
		[
			'label' => __( 'Black Logo', 'she-header' ),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => __( 'On', 'she-header' ),
			'label_off' => __( 'Off', 'she-header' ),
			'return_value' => 'yes',
			'default' => '',
			'frontend_available' => true,
			'description' => __( 'Change the logo to black', 'she-header' ),
			'prefix_class'  => 'she-header-change-logo-color-',
			'condition' => [
				'change_logo_color' => 'yes',
				'transparent!' => '',
			],
			'selectors' => [
				'{{WRAPPER}}.she-header-yes:not(.she-header) .elementor-widget-theme-site-logo .elementor-widget-container,
				{{WRAPPER}}.she-header-yes:not(.she-header) .elementor-widget-image .elementor-widget-container,
				{{WRAPPER}}.she-header-yes:not(.she-header) .logo .elementor-widget-container' => '-webkit-filter: brightness(0) invert(0); filter: brightness(0) invert(0);',
				'{{WRAPPER}}.she-header-yes:not(.she-header) .elementor-widget-n-menu .elementor-widget-image .elementor-widget-container, 
				{{WRAPPER}}.she-header-yes:not(.she-header) .not-logo .elementor-widget-container' => '-webkit-filter: none; filter: none;'
			],
		]
	);

  $element->end_controls_tab();

// ---------------------------------- LOGO AFTER TAB

  $element->start_controls_tab(
		 'after_tab',
		 [
	'label' => __( 'After Scrolling', 'she-header' ),
		 'condition' => [
				'change_logo_color' => 'yes',
				'transparent!' => '',
			],
		]
	);

// ---------------------------------- LOGO WHITE AFTER

	$element->add_control(
		'logo_color_white_after',
		[
			'label' => __( 'White Logo', 'she-header' ),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => __( 'On', 'she-header' ),
			'label_off' => __( 'Off', 'she-header' ),
			'return_value' => 'yes',
			'default' => '',
			'frontend_available' => true,
			'description' => __( 'Change the logo to white', 'she-header' ),
			'prefix_class'  => 'she-header-change-logo-color-',
			'condition' => [
				'change_logo_color' => 'yes',
				'transparent!' => '',
			],
			'selectors' => [
				'{{WRAPPER}}.she-header .elementor-widget-theme-site-logo .elementor-widget-container,
				{{WRAPPER}}.she-header .elementor-widget-image .elementor-widget-container,
				{{WRAPPER}}.she-header .logo .elementor-widget-container' => '-webkit-filter: brightness(0) invert(1); filter: brightness(0) invert(1);',
				'{{WRAPPER}}.she-header .elementor-widget-n-menu .elementor-widget-image .elementor-widget-container, 
				{{WRAPPER}}.she-header .not-logo .elementor-widget-container' => '-webkit-filter: none; filter: none;'
			],
		]
	);

// ---------------------------------- LOGO BLACK AFTER

 $element->add_control(
		'logo_color_black_after',
		[
			'label' => __( 'Black Logo', 'she-header' ),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => __( 'On', 'she-header' ),
			'label_off' => __( 'Off', 'she-header' ),
			'return_value' => 'yes',
			'default' => '',
			'frontend_available' => true,
			'description' => __( 'Change the logo to black', 'she-header' ),
			'prefix_class'  => 'she-header-change-logo-color-',
			'condition' => [
				'change_logo_color' => 'yes',
				'transparent!' => '',
			],
			'selectors' => [
				'{{WRAPPER}}.she-header .elementor-widget-theme-site-logo .elementor-widget-container,
				{{WRAPPER}}.she-header .elementor-widget-image .elementor-widget-container,
				{{WRAPPER}}.she-header .logo .elementor-widget-container' => '-webkit-filter: brightness(0) invert(0); filter: brightness(0) invert(0);',
				'{{WRAPPER}}.she-header .elementor-widget-n-menu .elementor-widget-image .elementor-widget-container, 
				{{WRAPPER}}.she-header .not-logo .elementor-widget-container' => '-webkit-filter: none; filter: none;'
			],
		]
	);

// ---------------------------------- LOGO FULL COLOR AFTER

 $element->add_control(
		'logo_color_full_after',
		[
			'label' => __( 'Full Color Logo', 'she-header' ),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => __( 'On', 'she-header' ),
			'label_off' => __( 'Off', 'she-header' ),
			'return_value' => 'yes',
			'default' => '',
			'frontend_available' => true,
			'description' => __( 'Removes all filters to allow a full color logo image', 'she-header' ),
			'prefix_class'  => 'she-header-change-logo-color-',
			'condition' => [
				'change_logo_color' => 'yes',
				'transparent!' => '',
			],
			'selectors' => [
				'{{WRAPPER}}.she-header .elementor-widget-theme-site-logo .elementor-widget-container,
				{{WRAPPER}}.she-header .elementor-widget-image .elementor-widget-container,
				{{WRAPPER}}.she-header .logo .elementor-widget-container' => '-webkit-filter: brightness(1) invert(0); filter: brightness(1) invert(0);',
				'{{WRAPPER}}.she-header .elementor-widget-n-menu .elementor-widget-image .elementor-widget-container, 
				{{WRAPPER}}.she-header .not-logo .elementor-widget-container' => '-webkit-filter: none; filter: none;'
			],
		]
	);

	$element->end_controls_tab();
	$element->end_controls_tabs();
	
		$element->add_control(
			'blur_bg',
			[
				'label' => __( 'Blur Background', 'she-header' ),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => __( 'On', 'she-header' ),
				'label_off' => __( 'Off', 'she-header' ),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'condition' => [
					'transparent!' => '',
				],
				'description' => __( 'Add a modern blur effect to a semi-transparent header background color after scrolling', 'she-header' ),
			]
		);

		$element->add_control(
			'hide_header',
			[
				'label' => __( 'Hide header on scroll down', 'she-header' ),
				'type' => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'label_on' => __( 'On', 'she-header' ),
				'label_off' => __( 'Off', 'she-header' ),
				'return_value' => 'yes',
				'default' => '',
				'frontend_available' => true,
				'description' => __( 'Hides the header if scrolling down, and shows header if scrolling up', 'she-header' ),
				'prefix_class'  => 'she-header-hide-on-scroll-',
				'condition' => [
					'transparent!' => '',
				],
			]
		);

		$element->add_control(
			'hide_header_notice',
			[
				'raw' => __('WARNING: This might break section/container entrance animations', 'she-header'),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition' => [
					
					'hide_header' => 'yes',
					'transparent!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'scroll_distance_hide_header',
			[
				'label' => __( 'Scroll Distance (px)', 'she-header' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 500,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'size_units' => [ 'px'],
				'description' => __( 'Choose the scroll distance to start hiding the header', 'she-header' ),
				'frontend_available' => true,
				'condition' => [
					'hide_header' => 'yes',
					'transparent!' => '',
				],
			]
		);

		$element->end_controls_section();
	}

	private function add_actions()
	{
		if (!function_exists('is_plugin_active')) {

			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		// add She on sections
		if (is_plugin_active('elementor/elementor.php')) {
			add_action('elementor/element/section/section_effects/after_section_end', [$this, 'register_controls']);
		}

		add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
		if (Elementor\Plugin::instance()->editor->is_edit_mode()) {
		} else {
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		}

		// add She on containers
		if (is_plugin_active('elementor/elementor.php')) {
			add_action('elementor/element/container/section_effects/after_section_end', [$this, 'register_controls']);
		}
	}

	public function enqueue_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'she-header-style',
			SHE_HEADER_ASSETS_URL  . 'css/she-header-style' . '.css',
			[],
			SHE_HEADER_VERSION
		);

	}

	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'she-header',
			SHE_HEADER_URL . 'assets/js/she-header.js',
			[
				'jquery',
			],
			SHE_HEADER_VERSION,
			false
		);
	}


}
