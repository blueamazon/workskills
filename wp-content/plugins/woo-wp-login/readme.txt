=== WooCommerce WP-Login ===
Contributors: MachineITSvcs
Donate link: https://www.machineitservices.com/donate/
Tags: woocommerce, wp-login, woocommerce plugins, redirect, login
Requires at least: 2.5
Tested up to: 6.0.1
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables a login redirect to your WooCommerce "My account" page instead of the default wp-login.php

== Description ==

Redirects wp-login.php page to the WooCommerce "My account" page. You can set this is Dashboard > WooCommerce > Settings > Advanced tab. This plugin is also built in a manner so that if WooCommerce is not activated or installed, your site will continue to work whether this plugin is active or not.

Visit our website: [Machine IT Services](https://www.machineitservices.com/)

== Premium Version ==

The premium version of this plugin can be found on our website [Machine IT Services - WooCommerce WP-Login Premium](https://www.machineitservices.com/shop/services/development/web-development/plugins/woo-wp-login/). This version supports several additional redirect methods, and automatically hides the redirect information in the address bar, protecting against manipulation, and providing a more seamless user experience. These redirects can also include additional parameters for any developers out there, for both pre and post-authentication customization. It also supports the wp_login_url() and wp_logout_url() functions, passing the redirect and additional parameters to the login page and interrupting the default redirect to the "My account" page on logout if a redirect is specified, respectively. With the premium version you will also receive 24/7 support and assistance with implementation and advanced usage.

Premium Version Advanced Usage Example:
wp_redirect(wp_login_url('/your-target/here/?with=additional&post-authentication=parameters') . '&with=additional&pre-authentication=parameters');
This will pass the URL GET parameters within the wp_login_url() function to the final destination (post-authentication redirect) and pass the URL GET parameters near the end to the WooCommerce "My account" page (to be used pre-authentication), such as WooCommerce notices, session variables, tracking information, etc.

== This plugin is fully compatible with WordPress Multisite installations, as well as subdirectory installations! Best of all, IT DOES NOT REQUIRE ANY CODING KNOWLEDGE! ==

== Installation ==

1. Upload `woo-wp-login` folder to the `/wp-content/plugins/` directory
2. Activate the WooCommerce WP-Login plugin through the 'Plugins' menu in WordPress
3. And that's it!

== Frequently Asked Questions ==

= Why would I need this? =
If you've ever wanted to utilize a custom login page, and you currently have an eCommerce site using WooCommerce, this is the plugin for you.

= What does it do? =
It redirects the standard wp-login.php page to your default WooCommerce "My account" page, which is set in the Dashboard > WooCommerce > Settings > Advanced tab.

= Could this plugin break my site? What if I have WooCommerce disabled or the "My account" page option isn't set? =
This plugin is built to check first if WooCommerce is in fact installed and active using the is_plugin_active() function (Please note that this plugin includes "wp-admin/includes/plugin.php" in case it is not already).
Next, this plugin will check if the "My account" option in WooCommerce Settings is set.
Only after both of these dependencies are met will the plugin actually redirect your wp-login.php page.
If you decide to deactivate WooCommerce, or unset the "My account" page, no worries! Your site will continue to function normally, using the standard wp-login.php page.

= What happens to my wp-login.php redirects? =
We keep them, of course! They will be preserved when redirecting to your WooCommerce "My account" page and once logged in, will be used to redirect the site visitors to their desired destinations! This requires no configuration. These redirects will simply be to the initial page that prompted the login. For example, if you attempt to access the Admin Dashboard when logged out, this plugin will redirect the default wp-login.php page to your set WooCommerce "My account" while also getting the redirect query string from the login page in the process, so that upon successful login, you will be redirected to the Admin Dashboard.

== Changelog =
= 1.2.0 =
- Refactored code

= 2.1.1 =

- Fixed potential issue with logging out in Premium Version

= 1.1.9 =

- Added woo_wp_login_actions filter for you developers out there who require additional redirect control (blacklisting "action" GET parameter values), i.e adding the following code to your functions.php file or a mu-plugins file (if you're not using a child theme): add_filter('woo_wp_login_actions', function($actions) {$actions[] = "validate_2fa"; return $actions;});

= 2.0.7 =

- Added woo_wp_login_actions filter in Premium Version for you developers out there who require additional redirect control (blacklisting "action" GET parameter values), i.e adding the following code to your functions.php file or a mu-plugins file (if you're not using a child theme): add_filter('woo_wp_login_actions', function($actions) {$actions[] = "validate_2fa"; return $actions;});

= 1.1.8 =

- Further improvements to redirect check conditional for action GET parameter

= 1.1.7 =

- Improved redirect check conditional for action GET parameter

= 1.1.6 =

- Added WordPress Multisite Subsite Deactivation Check to Prevent Redirect if Subsite Deactivated/Disabled/Deleted

= 2.0.6 =

- Added WordPress Multisite Subsite Deactivation Check to Prevent Redirect if Subsite Deactivated/Disabled/Deleted in Premium Version

= 1.1.5 =

- Improved WooCommerce Installation Conditional Statement

= 2.0.5 =

- Improved Premium Version Argument Functionality for passing GET parameters to "My account" page (Pre-Authentication) and destination (Post-Authentication) separately for advanced usage. Ex. wp_redirect(wp_login_url('/your-target/here/?with=additional&post-authentication=parameters') . '&with=additional&pre-authentication=parameters')
- Introduced Compatibility with the new WordPress Recovery Mode

= 1.1.4 =

- Introduced Compatibility with the new WordPress Recovery Mode

= 2.0.4 =

- Fixed Premium Version Redirects from My Account when Already Logged In
- Improved Conditional Statements

= 2.0.3 =

- Fixed Premium Version Multi-Site WooCommerce Detection After Previous WooCommerce Check Update
- Moved Condition Call Further Up to Reduce Unnecessary Calls to Functions in Premium Version

= 1.1.3 =

- Fixed Multi-Site WooCommerce Detection After Previous WooCommerce Check Update
- Moved Conditional Call Further Up to Reduce Unnecessary Calls to Functions

= 2.0.2 =

- Updated Premium Version Reset Password Bypass for Initial Password (Re)Set for Account Created Manually in WordPress Dashboard by Admin

= 1.1.2 =

- Updated Reset Password Bypass for Initial Password (Re)Set for Account Created Manually in WordPress Dashboard by Admin

= 2.0.1 =

- Updated Premium Version WooCommerce Check
- Declared Support for WordPress 5.1 in Premium Version

= 1.1.1 =

- Updating WooCommerce Check Method
- Declared Support for WordPress 5.1

= 2.0.0 =

- Initial Premium Release.
- Uses JavaScript to hide redirect GET parameter. i.e. Clean "My account" URL.
- Preserves GET and POST parameters throughout redirect.

= 1.0.9 =

- Implemented a fix to prevent losing redirect from conflicting plugin.

= 1.0.8 =

- Added support for "interim-login" for quickly reauthenticating sessions without the redirect on the backend.

= 1.0.7 =

- Cleaned up some code. Received requests to again do away with reauth query. This has been re-implemented in the plugin.

= 1.0.6 =

- Decided to leave reauth query after all. It may be annoying, but dynamic code is always better. Manually removing specific query strings is not a viable solution.

= 1.0.5 =

- Removed reauth query from query string because it's annoying to see and unnecessary.

= 1.0.4 =

- Added exit if plugin file accessed directly.
- Listed support for the newest version of WordPress: 4.9.4.

= 1.0.3 =

- Simplified some code by using values directly instead of setting values to variables.
- Added Support for MultiSite WordPress installations. Still tests for WooCommerce activation and a set "My account" page per network site.
- Minor bug fixes due to MultiSite URL being different from root domain.
- Should also allow for the use of this plugin on WordPress installations within subdirectories. i.e. https://www.machineitservices.com/sub-site/wp-admin/

= 1.0.2 =

- Began preserving all query strings, except for redirect_to and loggedout. This was preventing wp-login.php specific features including password resets.
- Simplified syntax by using if statement alternatives.
- Cleaned up some unnecessary code by using common (parent) if statements, instead of specifying condition per statement.

= 1.0.1 =

- Using "redirect-to" query instead of the WordPress "redirect_to" in an effort to avoid any potential bugs that may arise from using the same string.
- Removes "redirect-to" query from all pages except the WooCommerce "My account" page, while also preserving any other query strings that may be present.
- Disallowed redirect on "My account" page that is unset or set to home page.
- Added keywords to hopefully improve plugin searchability.
- Added icons to plugin.

= 1.0.0 =

- Initial release.
- First commit to WordPress repo.
