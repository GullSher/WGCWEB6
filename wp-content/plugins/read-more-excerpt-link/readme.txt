=== Read More Excerpt Link ===
Contributors: teckel
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=99J6Y4WCHCUN4&lc=US&item_name=Read%20More%20Excerpt%20Link&item_number=Read%20More%20Excerpt%20Link%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: read, more, excerpt, link, ellipsis, read more, readmore, length, words, archive
Requires at least: 2.9.0
Tested up to: 6.2
Stable tag: 1.6.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create "Read More" link after post excerpt instead of ellipsis [...] Also modify excerpt length.


== Description ==

Try it out on a free dummy site here => [https://demo.tastewp.com/read-more-excerpt-link](https://demo.tastewp.com/read-more-excerpt-link)

When WordPress makes an excerpt from your post content, it crops the content and adds an ellipsis [...] to the end.  This plugin changes the ellipsis to a **Read More** link to the full post content.

You can change the **Read More** link text to anything you wish from the **Read More Excerpt** submenu on the WordPress **Settings** menu.

Also from the **Read More Excerpt** submenu, you can specify the excerpt word length (WordPress defaults to 55 words).

Additionally, you can force the **Read More** link to show even when an excerpt is entered or when a read more tag is added to the content. This is turned on from the **Read More Excerpt** submenu with the **Show More Frequently** checkbox.

The **Read More** link uses the class **read-more-link**, so you can stylize the link any way you wish.


== Installation ==

= For an automatic installation through WordPress: =

1. Select **Add New** from the WordPress **Plugins** menu in the admin area.
2. Search for **Read More Excerpt Link**.
3. Click **Install Now**, then **Activate Plugin**.

= For manual installation via FTP: =

1. Upload the **read-more-excerpt-link** folder to the **/wp-content/plugins/** directory.
2. Activate the plugin from the **Plugins** screen in your WordPress admin area.

= To upload the plugin through WordPress, instead of FTP: =

1. From the **Add New** plugins page in your WordPress admin area, select the **Upload Plugin** button.
2. Select the **read-more-excerpt-link.zip** file, click **Install Now** and **Activate Plugin**.


= Optional Settings: =

* To change the **Read More** link text, visit the **Read More Excerpt** submenu on the WordPress **Settings** menu.
* You can also change the excerpt word length from the same **Read More Excerpt** submenu.
* To show an ellipsis "&hellip;" at the end of a truncated excerpt (but before the "Read More" link), check the **Include Ellipsis after Excerpt** checkbox.
* To show the **Read More** link even when an excerpt is entered or when a read more tag is added to the content, check the **Show More Frequently** checkbox.


== Frequently Asked Questions ==

= How do I change the "Read More" link text to something else? =

In the WordPress **Settings** menu, select the **Read More Excerpt** submenu, where you can modify the default **Read More** text link.

= How do I change the length of the excerpt? =

WordPress defaults to an excerpt length of 55 words. You can change this from the WordPress **Settings** menu, select the **Read More Excerpt** submenu where you can modify the excerpt word length.

= Why isn't "Read More" showing on all pages? =

Normally, WordPress doesn't show a read more ellipsis if an excerpt is entered as part of the post. Also, WordPress doesn't show the read more ellipsis if a read more tag is placed in the content before the set excerpt legnth (defaults to 55 words). To change this, from the WordPress **Settings** menu, select the **Read More Excerpt** submenu where you can active the **Show More Frequently** option.  This will force the **Read More** to show whenever there's post content.

= How can I stylize the "Read More" link? =

Here's a suggestion:

	a.read-more-link {
		font-size: 0.9em;
		text-transform: uppercase;
		display: inline-block;
		white-space: nowrap;
	}
	a.read-more-link:before {
		content: "(";
	}
	a.read-more-link:after {
		content: ")";
	}

= Why isn't the "Read More" showing at all? =

There's also some over-zealous themes that re-write complete sections of WordPress code for no good reason.  Elegant Themes is a good example of a theme company that makes highly bloated themes that re-write much of the WordPress normal operation.  Basically, some themes totally ignore the perfectly working WordPress auto-excerpt and create their own.  When themes do this, the **Read More Excerpt** plugin is ignored as the theme no longer calls the standard WordPress excerpt functions (which **Read More Excerpt** is hooked into).  Other than making modifications to your theme, there's nothing any plugin can do when themes don't use the WordPress hooks and hard-code new functionality instead.

= It displays "Read More" but without a link? =

This is rare, but can happen when a theme is not strictly calling the get_the_excerpt() function but doing something with the result (like stripping HTML tags). If you know how to create a child of your template and make slight modifications to that child you can easily correct this. Start by looking at the theme's archive.php file and following any get_template_part() calls from there. It's best to not make changes directly to an off the shelf theme, as updates will override any changes you make. Creating a child theme is a better method of making changes to an off the shelf theme.


== Screenshots ==

1. Read More Excerpt Link Settings.
2. Read More Excerpt Link turns this...
3. To this!


== Changelog ==

= v1.6.1 - 2/25/2023 =
* Nonce added to prevent CSRF vulnerability.

= v1.6.0 - 2/24/2023 =
* Added nonce for CSRF protection, verified working with WordPress v6.2

= v1.5 - 2/1/2021 =
* Updated contact email address, verified working with WordPress v5.6

= v1.4 - 5/13/2016 =
* Option to include an ellipsis "&hellip;" at the end of a truncated excerpt (but before the "Read More" link).

= v1.3 - 4/1/2016 =
* Won't show the read more excerpt link if the post has no content (otherwise it would link to a blank page).

= v1.2 - 3/14/2016 =
* Added option **Show More Frequently** to the **Read More Excerpt** submenu under **Settings**.
* Added descriptions to settings fields.

= v1.1 - 3/10/2016 =
* Added **Read More Excerpt** submenu to the **Settings** menu.
* From the **Read More Excerpt** submenu the **Read More** text link can be modified.
* From the **Read More Excerpt** submenu the excerpt word length can be modified.

= v1.0 - 3/9/2016 =
* Initial release.