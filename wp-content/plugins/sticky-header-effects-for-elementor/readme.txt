=== Sticky Header Effects for Elementor ===

Contributors: rwattner, dgovea
Donate Link: https://www.paypal.me/StickyHeaderEffects
Tags: Elementor, Elementor Page Builder, Elements, Elementor Add-ons, Add-ons, Page Builder, Widgets, Briefcasewp
Requires at least: 4.9.0
Tested up to: 6.3
Requires PHP: 7.0
Stable tag: 1.6.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Options and features that extend Elementor Pro's sticky header capabilities.

== Description ==

Sticky Header Effects for Elementor adds useful options that are missing from the "sticky" header feature introduced in Elementor Pro 2.0. Giving users the option to change the background color and height when the visitor starts scrolling down the page. This allows for a "transparent" menu effect that can become any color, semi-transparent or solid, once the visitor begins to scroll.

This plugin is cross-browser compatible and fully responsive. Meaning it will work on all browsers as well as tablets and mobile devices.

This plugin is meant to be an add-on to Elementor Pro page builder as it's not a standalone plugin.

### Features

* Options panel built-in to Elementor Pro's advanced section options. - Settings are right where they should be without cluttering up your workspace.
* Apply options on scrolling - The scrolling distance for the Sticky Header Effects to be applied is responsively adjustable for the best results in any situation.
* Transparent Header - Uses position to move header section down on top of the page. No need for problem causing negative margins.
* Header Background Color - Options for header after scrolling with full HEX, RGBA, and Color Name support.
* Bottom Border - Allows user to change the color and thickness of the bottom border upon scrolling.
* Shrink Header - An effect which changes section min-height to maximize space and achieve a slim style without losing functionality.
(Remember that the "shrink" effect is limited by the height and padding of the header content. See the F.A.Q.)
* Shrink Logo - Ability to adjust the logo height after scrolling
*Change Logo Color - Change the logo image color before or after the user scrolls. Useful for switching header design from monocromatic to full color
*Blur Background - Add a modern blur effect to a semi-transparent header background color after scrolling
*Hide Header on Scroll Down - Hides the header if scrolling down, and shows header if scrolling up. Has selectable distance to start the effect

### Pro Features Coming Soon

* Replace logo - Change logo image entirely
* Entrance animations - Slide-in and fade-in animations with animation duration
* Hide elements - Hide or show header elements after scrolling
* Box shadow - Add or remove box shadow effect upon scrolling with color, horizontal, vertical, blur, and spread controls
* Menu toggle animations - Entrance and exit animations for the mobile hamburger menu
* Split menu - Menus that will split to allow center logo

== Installation ==

= Minimum Requirements =
* WordPress 5.0 or greater
* PHP version 7.0 or greater
* MySQL version 5.6 or greater

= Installation Instructions =

- Make sure that you have installed Elementor Free and Elementor Pro Page Builder. This is not a stand-alone plugin and ONLY works with Elementor Pro.

https://www.youtube.com/watch?v=Ypn6fltn_7s

1. Install using the WordPress built-in Plugin installer, or Extract the zip file and drop the contents in the `wp-content/plugins/` directory of your WordPress installation.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Pages > Add New
4. Press the 'Edit with Elementor' button.
5. Add a header section or container.
6. Now you can find 'Sticky Header Effects' option under the section's or container's 'Advanced' tab.

== Frequently Asked Questions ==

= Why isn't the "shrink" effect working? =

The header cannot shrink smaller than the objects inside of it!

The "shrink" effect is restricted by the size of the content in the header section. Such as logos, images, menus, widgets, and also all of the padding and margins of those elements. This also inclused the padding and margins of the sections and columns themselves.

To get the best "shrink" effect use these settings:
* Set the top and bottom padding to 0px on your sticky header section, column, and elements inside of it.
* Set a custom logo and other image height(you can leave the width blank for "auto").
* Set the header section height to "min height" and adjust it to your desired height.

Basically what happens is that the content of the header is "too tall" to shrink down anymore.

= Is this a standalone Plugin? =

No. You cannot use Sticky Header Effects for Elementor by itself. It is a plugin for Elementor Pro.

= Does it work with any WordPress theme? =

Yes. It will work with any WordPress theme that is using Elementor Pro as a page builder.

= Will this plugin slow down my website speed? =

Sticky Header Options for Elementor is light-weight and you can also use only the options you want to use on your website for faster performance.

== Screenshots ==

1. Settings built-in to Elementor Pro.
2. Main settings panel.
3. Current effects settings.

== Changelog ==
= 1.6.9 =
Fixed: Javascript bug with sections
Fixed: Header width bug
Tweaked: Optimized CSS

= 1.6.8 =
Fixed: Shrink logo bug
Fixed: Shrink header bug
Fixed: Transparent header bug
Fixed: All CSS color and size transitions
Added: Compatibility with new elementor "mega" menu
Added: Ability to override logo color change on elements using class "not-logo"
Tweaked: Logo color change optimized so "full color" option no longer overwrites elementor css settings

= 1.6.7 =
- Fixed: Transparent header bug

= 1.6.6 =
- Fixed: Shrink header bug
- Fixed: Bottom border color and size transitions
- Added: Compatibility with new elementor "mega" menu
- Added: Ability to override logo color change on elements using class "not-logo"
- Tweaked: Logo color change optimized so "full color" option no longer overwrites elementor css settings

= 1.6.5 =
- Tweaked: Logo transition CSS for image widget
- Tweaked: Logo color CSS for image widget
- Maintenance: Wordpress 6.2 compatibility update

= 1.6.4 =
- Fixed: Js bug
- Fixed: Logo color not changing on scroll
- Fixed: CSS for smooth transitions
- Konwn Bugs: Shrink logo/header

= 1.6.3 =
- Reverted to 1.5.5 only with container compatibility

= 1.6.2 =
- Fixed: Section height bug

= 1.6.1 =
- Fixed: Js bug
- Fixed: Container shrink bug

= 1.6 =
- Fixed: Shrinking Logo bug
- Fixed: Container support using Elementor free
- Fixed: Logo color change set to none doesn't remove CSS filter settings
- Added: Disable/enable completely transparent header
- Tweak: Changed logo color settings to be more clear 
- Maintenence: Optimized CSS for v2.0
- Maintenence: Optimized JavaScript for v2.0
- Maintenence: Optimized Elementor controls for v2.0

= 1.5.5 =
- Fixed: Shrinking Logo bug
- Fixed: Header section top position
- Maintenence: Optimized CSS code

= 1.5.4 =
- Fixed: Shrinking Logo bug

= 1.5.3 =
- Fixed: Gap above mobile header bug

= 1.5.2 =
- Fixed: Gap above header bug

= 1.5.1 =
- Fixed: Wordpress admin bar bug

= 1.5 =
- Fixed: Gap above header bug

= 1.4.9 =
- Added: Compatibility with Elementor Containers
- Maintenance: Wordpress 6.0 compatibility update

= 1.4.8 =
- Maintenance: Wordpress 5.9 compatibility update

= 1.4.7 =
- Fixed: Misc Bugs

= 1.4.6 =
- Fixed: Hide header bug

= 1.4.5 =
- Fixed: WP admin bar bug

= 1.4.4 =
- Maintenance: Wordpress 5.8 compatibility update
- Fixed: Header width bug
- Update: Better responsive Worspress admin bar handling

= 1.4.3 =
- Fixed: Full color logo after scroll bug

= 1.4.2 =
- Fixed: Bugs

= 1.4.1 =
- Fixed: Gap above header bug

= 1.4.0 =
- Added: Wordpress 5.5 compatibility
- Added: Elementor 3 compatibility
- Added: Hide on scroll down feature
- Added: Blur background feature
- Added: Before and after scrolling logo color options
- Fixed: Opera browser hash links not activating scrolling effects
- Fixed: Editor handle bug
- Fixed: All labels and descriptions to help with user operation
- Fixed: Various code optimizations
- Fixed: Transparent on mobile bug
- Fixed: Stretched section broke transparent header

= 1.3.2 =
- Fixed: Transition CSS

= 1.3.1 =
- Fixed: Bugs

= 1.3.0 =
- Added: Shrink Logo feature

= 1.2.3 =
- Fixed: Hook on the new Elementor Pro 2.4.7 version

= 1.2.2 =
- Removed: Stretched section condition

= 1.2.1 =
- Added: Compatibility with the new Elementor 2.1.1 version
- Fixed: JavaScript error

= 1.2.0 =
- Removed: Sticky Header feature(Elementor fixed theirs)
- Added: Transparent header feature
- Added: Compatibility with the new Elementor 2.1 version
- Added: Change javascript to external file
- Fixed: Bugs

= 1.1.2 =
- Fixed: Bugs

= 1.1.1 =
- Fixed: Bug where section didn't move to top of page

= 1.1.0 =
- Added: Sticky header feature
- Added: Bottom border feature
- Fixed: Bugs with tablet and mobile responsive modes
- Fixed: Padding issues when using the "shrink" effect

= 1.0.0 =

- Initial stable release

== Upgrade Notice ==
= 1.6.9 =
Fixed: Javascript bug with sections
Fixed: Header width bug
Tweaked: Optimized CSS

= 1.6.8 =
Fixed: Shrink logo bug
Fixed: Shrink header bug
Fixed: Transparent header bug
Fixed: All CSS color and size transitions
Added: Compatibility with new elementor "mega" menu
Added: Ability to override logo color change on elements using class "not-logo"
Tweaked: Logo color change optimized so "full color" option no longer overwrites elementor css settings

= 1.6.7 =
- Fixed: Transparent header bug

= 1.6.6 =
- Fixed: Shrink header bug
- Fixed: Bottom border color and size transitions
- Added: Compatibility with new elementor "mega" menu
- Added: Ability to override logo color change on elements using class "not-logo"
- Tweaked: Logo color change optimized so "full color" option no longer overwrites elementor css settings

= 1.6.5 =
- Tweaked: Logo transition CSS for image widget
- Tweaked: Logo color CSS for image widget
- Maintenance: Wordpress 6.2 compatibility update

= 1.6.4 =
- Fixed: Js bug
- Fixed: Logo color not changing on scroll
- Fixed: CSS for smooth transitions
- Konwn Bugs: Shrink logo/header

= 1.6.3 =
This is identical to v1.5.5 only with container compatibility.

= 1.6.2 =
The 1.6 update includes some substantial changes.
**Some settings may need to be re-applied to continue to work properly.**

= 1.6 =
**Heads up, Please backup before updating!**

The latest update includes some substantial changes. We highly recommend you backup your site before upgrading, and make sure you first update in a staging environment.
**Some settings may need to be re-applied to continue to work properly.**

= 1.5.5 =
Fixed: Shrinking Logo bug
Fixed: Header section top position
Maintenence: Optimized CSS code

= 1.5.4 =
Fixed: Shrinking Logo bug

= 1.5.3 =
Fixed: Gap above mobile header bug

= 1.5.2 =
Fixed: Gap above header bug

= 1.5.1 =
Fixed: Wordpress admin bar bug

= 1.5 =
Fixed: Gap above header bug

= 1.4.9 =
Added: Compatibility with Elementor Containers
Maintenance: Wordpress 6.0 compatibility update

= 1.4.8 =
Maintenance: Wordpress 5.9 compatibility update

= 1.4.7 =
Fixed: Misc Bugs

= 1.4.6 =
Fixed: Hide header bug

= 1.4.5 =
Fixed: WP admin bar bug

= 1.4.4 =
Maintenance: Wordpress 5.8 compatibility update
Fixed: Header width bug
Update: Better responsive Worspress admin bar handling

= 1.4.3 =
Fixed: Full color logo after scroll bug

= 1.4.2 =
Fixed: Bugs

= 1.4.1 =
Fixed: Gap above header bug

= 1.4.0 =
- Added: Wordpress 5.5 compatibility
- Added: Elementor 3 compatibility
- Added: Hide on scroll down feature
- Added: Blur background feature
- Added: Before and after scrolling logo color options
- Fixed: Opera browser hash links not activating scrolling effects
- Fixed: Editor handle bug
- Fixed: All labels and descriptions to help with user operation
- Fixed: Various code optimizations
- Fixed: Transparent on mobile bug
- Fixed: Stretched section broke transparent header

= 1.3.2 =
- Fixed: Transition CSS

= 1.3.1 =
- Fixed: Bugs

= 1.3.0 =
- Added: Shrink Logo featured

= 1.2.3 =
- Fixed: Hook on the new Elementor Pro 2.4.7 version

= 1.2.2 =
- Removed: Stretched section condition

= 1.2.1 =
- Added: Compatibility with the new Elementor 2.1.1 version
- Fixed: JavaScript error

= 1.2.0 =
Removed: Sticky Header feature(Elementor fixed theirs)
Added: Transparent header feature
Added: Compatibility with the new Elementor 2.1 version
Added: Change javascript to external file
Fixed: Bugs

= 1.1.2 =
Bug fixes

= 1.1.1 =
Bug fixes

= 1.1.0 =
This version adds "Sticky Header" and "Bottom Border" options.
The sticky feature will bring the section down and overlay it on top of the page. This eliminates the need for negative margins which causes page scrolling problems.
The bottom border feature adds bottom border width and color options when user scrolls.

== Donate ==
Enjoy using *Sticky Header Effects*? Please consider making a **[donation](https://www.paypal.me/StickyHeaderEffects)**
Every donation helps the continued development, maintenance, and support for this plugin.
**Thank you very much your support!**
