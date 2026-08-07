=== Illdy Companion ===
Contributors: colorlibplugins, silkalns
Tags: demo, one page, parallax, social, portfolio
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Illdy Companion is a companion plugin for Illdy WordPress theme by Colorlib.com.
== Description ==

Illdy Companion is a companion for Illdy One Page WordPress theme by Colorlib.com. This plugin won't do anything for other free or premium WordPress themes and you need to download and install <a href="https://colorlib.com/wp/themes/illdy/" target="_blank" rel="friend">Illdy</a>. If you are having problems with Illdy theme or its companion plugin the fastest way to receive help is via our theme <a href="http://colorlib.com/wp/forums" target="_blank" rel="friend">support forum</a>.

This plugin will add necessary WordPress widgets and allow to import demo content which will help you to with website setup.

While Illdy is a great one page WordPress theme it might not be for everyone therefore you might want to check other free <a href="https://colorlib.com/wp/themes/" target="_blank" rel="friend">WordPress themes</a> that are created by Colorlib.


= Plugin Options =

* Creates required WordPress widgets to be used in theme
* Creates demo(dummy) content for widgets to make them easier to use and understand how they work
* Provides an option to import demo(dummy) content.

= About Colorlib =

Colorlib is the best and by far the most popular source for free and premium WordPress themes. Our themes has been downloaded over 1,5 million times and are used by developers, webmasters and regular users all over the world. We believe in open source and that's why we have made our themes free to use for private and commercial use.

= Further Reading =

If you are new to WordPress but are dedicated to <a href="https://colorlib.com/wp/how-to-make-a-website/" target="_blank" rel="friend">make a website</a> on your own Colorlib is the right place to start. Usually the trickiest part is to choose the right hosting because all hosting providers are not equal. We have outlined the <a href="https://colorlib.com/wp/wordpress-hosting/" target="_blank" rel="friend">best WordPress hosting</a> providers and we hope you'll find them useful. We can also help with WordPress related <a href="https://colorlib.com/wp/fix-error-establishing-database-connection-wordpress/" target="_blank" rel="friend">errors</a> and problems.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the whole contents of the folder `illdy-companion` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress dashboard
3. Enjoy using it :)


== Screenshots ==

1. Screenshot of the Illdy companion plugin's demo content import option which you can find under Appearance - About Illdy in your WordPress dashboard.

== Frequently Asked Questions ==

= What themes this plugin supports? =

Currently it works only with Illdy theme.

= Am I obligated to use it? =

You can still use Illdy theme without this plugin but you won't be able to import demo content and use theme specific widgets that you see on front page of theme demo.

== Changelog ==

= 2.3.0 =
Modernisation pass for WordPress 7 / PHP 8.5. Verified by booting the plugin
together with the Illdy theme on WordPress 7.0.2 / PHP 8.5.6: the pair now
produces zero PHP notices, warnings or deprecations, where the previous
release produced seven.

Compatibility
* Fixed "Translation loading for the illdy-companion domain was triggered too early" on WordPress 6.7 and later. The dashboard widget was built while the plugin file was still parsing, and translated its title before init.
* Recent Posts widget no longer raises an undefined-key warning every time it is saved with the "Display title" toggle off; an unchecked checkbox is never submitted.
* Importer guards three get_option() results before indexing them. Assigning an index on false is deprecated in PHP 8.1.
* Front page importing checks wp_insert_post() for errors and reuses existing pages instead of creating a duplicate "Front Page" and "Blog" on every run.
* Widgets fall back cleanly when an attachment has been deleted instead of indexing a false return value.
* Requires PHP 7.4 or later.

Security
* The attachment lookup used by the widget media pickers now requires the upload_files capability and a nonce. It previously ran for any logged-in user with neither, which let a subscriber resolve any upload's URL, including media attached to private or draft posts.
* Demo import steps are validated against an allowlist. The requested step name was interpolated straight into a static method call.
* The dashboard widget escapes the remote blog feed's titles and links, which were printed into wp-admin unescaped, and renders dates in the site timezone.
* Escaped image URLs in the Person, Project and Testimonial widgets and 105 field attributes across all seven widget forms.
* Person widget social links now open in a new tab with rel="noopener noreferrer"; target and rel were previously placed on the icon element instead of the link for Twitter, LinkedIn and GitHub.
* Project widget links carry their title as visually hidden text. The link's only content was an empty overlay element and its only label a title attribute, which screen readers do not reliably announce, and its image is a CSS background so there was no alt text either — the link was announced with no name. Nothing about the design changes. It also fixes the widget reporting "No preview available" in the block widget editor, which treats markup with no text and no image as an empty preview.

Browser support
* Removed IE-only CSS: progid:DXImageTransform filters and -ms-transform from Font Awesome, and the @-o-/@-ms-keyframes duplicates from the icon picker.
* user-select now has a standard declaration rather than only prefixed ones.

Demo importer
* The importer is now a tab on Appearance -> About Illdy. It previously had no UI of its own — it handed a block of HTML to the theme's "Recommended Actions" list and ran through the welcome screen's generic AJAX dispatcher using a nonce the theme created. It now registers a tab through the theme and owns the nonce and the endpoint itself.
* If the active theme has no About Illdy screen, the importer registers its own page under Appearance instead, so it is never unreachable.
* The import asks for confirmation first. It replaces your Customizer settings and front page widgets, which was never stated before the old one-click button ran.
* What gets imported is unchanged. The same three steps write the same values, so a site importing today gets what it would have got from the previous release.

Performance
* Admin CSS and JavaScript no longer load on every wp-admin screen. Font Awesome and the icon picker load only on the widgets screen, and the importer script only on its own page: roughly 50 KB per admin page request.
* The Person, Project and Testimonial widgets stopped calling wp_enqueue_media() on every admin page, matching the guard the Service and Skill widgets already had.
* Every asset is versioned so plugin updates invalidate caches.

= 2.1.4 =
* Fixed critical bug in demo content import functionality 
* Fixed PHP syntax error in dynamic method calls
* Fixed undefined variable issue in the import_customizer method
* Fixed AJAX callback handler to properly process import requests
* Fixed multiple PHP warnings throughout the plugin
* Improved UI for recommended plugins section
* Fixed layout issues with plugin boxes and buttons
* Fixed plugin author links display
* Improved spacing and alignment in the admin dashboard
* Removed kb-support from recommended plugins list

= 2.1.3 =
* Compatibility with jQuery 3.0

= 2.1.2 =
* Strange output of illdy even on our demo( https://github.com/ColorlibHQ/illdy/issues/284 )

= 2.1.0 =
 * updated grunt package.json
 * Fixed #256 (videos in project section, they need an image backup)
 * Updated FancyBox to latest version
 * Added Colorlib Login Customizer as recommended plugin
 * Fixes #267 (add option to hide footer widget area || footer copyright message area)

= 2.0.3 =
 * Add TinyMCE instead of Textarea in Illdy Widgets ( https://github.com/puikinsh/illdy/issues/222 )
 * Parallax jumping on mobile ( https://github.com/puikinsh/illdy/issues/225 )

= 1.0.6 = 
* Portfolio URLs were not saving; bug fixed

= 1.0.5 =
* Improvements to support new Illdy theme version

= 1.0.4 =
* Code clean-up
* Wrapped all functions in a function_exists check just for sanity check
* All require_once functions are now using the plugin_dir_path function as a prefix. Absolute paths are preferred instead of relative ones.

= 1.0.2 =
* Updated description to reflect release of Illdy theme on WordPress.org

= 1.0.1 =
*  Small tweaks and bug fixes

= 1.0.0 =
* Initial release
