=== Customer verification for WooCommerce   ===
Contributors: zorem,gaurav1092,eranzorem,gehorsam,yashpatel1007
Tags: woocommerce, email address verification, customer verification, registration verification, woocommerce signup spam
Requires at least: 5.3
Requires PHP: 7.2
Tested up to: 6.7.1
Stable tag: 2.2
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reduce WooCommerce registration spam by requiring customers to verify their email through a link sent to their registered address or OTP.

== Key Features==

* Require email verification for new user registrations
* Option to add the verification link in the WooCommerce new account email or to send the verification in a separate email
* Option to resend the verification link on login form in cases that the customer lost the first verification email.
* Customize the separate email verification header/subject/message
* Option to customize the verification message
* Option to allow the customer to enter his account after the first time he registered without email verification.
* Skip email verification for selected user roles
* Customize the frontend messages
* Redirect users to any page on your website after successful validation
* Option for manual email verification from admin
* Email verification status will display for each user on admin
* Option to bulk actions resend verification email and verify user email address
 
== Installation ==

1. Upload the folder `customer-email-verification-for-woocommerce` to the `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==
= 2.2 =
* Fix -Uncaught TypeError: Cannot read properties of undefined (reading 'top')
* Fix - PHP Notice:  Function _load_textdomain_just_in_time was called incorrectly
* Fix - Deprecated: explode(): Passing null to parameter
* Dev - tested with WordPress 6.7.1
* Dev - tested with WooCommerce 9.5.1

= 2.1 =
* Enhancement - Test with WPML 4.7 and update the documentation
* Dev - tested with WordPress 6.7
* Dev - tested with WooCommerce 9.4.2

= 2.0 =
* Enhancement - Add UTM link for all the external links to zorem.com
* Enhancement - Add the text domain in all the strings in customizer
* Dev - tested with WordPress 6.5.3
* Dev - tested with WooCommerce 8.9.1

= 1.8 =
* Fix - Broken HTML on Users page
* Fix- user approval function on backend isn't working.
* Dev - Tested with WooCommerce 8.5.1

= 1.7 =
* Enhancement - Improve design of Pro Banner
* Dev - Compatibility with PHP 8.2
* Dev - tested with WooCommerce 8.3.0
* Dev - tested with WordPress 6.4.1

= 1.6 =
* Dev - Tested with WordPress 6.0.2 and WooCommerce 6.9.4
* Dev - Added compatibility with new WooCommerce HPOS

= 1.5 =
* Dev - Tested with WordPress 6.0.2 and WooCommerce 6.9.4
* Dev - Tested with WooCommerce Multilingual 5.0.0 Beta
* Enhancement - Updated the settings page design

= 1.4.1 =
* Fix - Try Again link in Email verification popup

= 1.4 =
* Enhancement - Updated design of settings page
* Enhancement - Updated design of Go Pro page
* Dev - Improved the code security
* Dev - Tested with WP 5.8 and WC 5.5.2

= 1.3.9 =
* Enhancement - Tested WooCommerce customer email verification compatibility.
* Dev - Tested with WP 5.7.2 and WC 5.4.1

= 1.3.8 =
* Fix - PHP Fatal error:  Uncaught Error: Call to undefined function wc_cev_customizer() in /customer-email-verification-for-woocommerce/includes/customizer/class-cev-customizer.php:27

= 1.3.7 =
* Fix - Uncaught Error: Class 'cev_initialise_customizer_settings' not found in customer-email-verification-pro/includes/customizer/class-cev-seperate-email-customizer.php:20

= 1.3.6 =
* Enhancement - Updated settings page design.
* Enhancement - Set ajax on Email Verification and Actions panel on users listing page
* Dev - Tested with WP 5.7.1 and WC 5.2.1

= 1.3.5 =
* Enhancement - Improved the "Email Verification" link html in user verification email.
* Dev - Tested with WP 5.6 and WC 5.0.0

= 1.3.4 =
* Fix - For administrator user always shows the verification widget on my account page.
* Fix - Fixed issue with skip email verification for the selected user roles option.
* Dev - Tested with WP 5.6 and WC 4.9.2

= 1.3.3 =
* Enhancement - Setup My Account change email verification text.
* Enhancement - Customizer improvements.
* Dev - Tested with WP 5.6 and WC 4.9.2

= 1.3.2 =
* Enhancement - Updated settings page design.
* Enhancement - Add new customizer for the verification display on the new account email.
* Enhancement - Add new customizer for the verification widget style and verification widget message
* Enhancement - Remove customer view settings page tab
* Dev - Tested with WP 5.6 and WC 4.9.1

= 1.3.1 =
* Enhancement - Updated settings page design.
* Enhancement - Updated users list page design for Email Verification and actions panel
* Enhancement - Updated edit user page design for Email Verification panel
* Enhancement - Update Customer View settings page design and remove frontend message options
* Enhancement - Added functionality of live preview of  Verification widget
* Enhancement - Aaded verification sucess message in general settings.
* Dev - Tested with WP 5.6 and WC 4.8


= 1.3 =
* Enhancement - Added option for "Redirect to select page after verification".
* Dev - Tested with WC 4.5

= 1.2 =
* Fix - Fixed ERR_TO_MANY_REDIRECT issue in email verification page
* Dev - Tested with WordPress 5.5 

= 1.1 =
* Enhancement - merged the verification status fields in WordPress users admin and added actions icons.
* Enhancement - added option to  bulk actions resend verification email and verify user email address
* Fix - auto refresh the permalink

= 1.0.9 =
* Enhancement - Added a filter in Users list page for Verified and Non verified users
* Enhancement - Added a option for "Allow first login after registration without email verification"
* Fix - Fixed warnings Undefined offset: 0 in /includes/class-wc-customer-email-verification-admin.php on line 439

= 1.0.8 =
* Enhancement - change Navigation label
* Fix - Fixed settings page design issue
* Fix - Fixed user page layout issue
* Fix - Fixed warnings in Undefined index 0

= 1.0.7 =
* Dev - added functionality for skip email verification for already registered user

= 1.0.6 =
* Enhancement - Change design of enter your pin input box in email verification form
* Enhancement - Updated design of email verification form
* Dev - Change default value for email heading and email content

= 1.0.5 =
* Fix - Fixed Separate email subject issue
* Fix - Fixed WooCommerce email verification endpoint not found issue

= 1.0.4 =
* Enhancement - Added Enable/Disable option for customer email verification
* Enhancement - Added spinner and settings save message in settings page
* Fix - Fixed warnings in front end page

= 1.0.3 =
* Dev - Updated email verification process and added pin verification functionality
* Dev - After login block all my account page until user verify his email

= 1.0.2 =
* Dev - Tested with WordPress 5.4 and WooCommerce 4.0

= 1.0.1 =
* Fix - Fixed issue with WooCommerce email
* Fix - Fixed skip email verification for selected roles option save issue
* Fix - Fixed warnings from users list page

= 1.0 =
* Initial version.
