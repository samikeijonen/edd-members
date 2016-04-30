# Edd Members

Edd Members is add-on plugin for [Easy Digital Downloads](https://easydigitaldownloads.com/). It gives you power to set content private and
build membership site in no time.

## Commercial Plugin

EDD Members is a commercial Plugin available in my site [Foxland](https://foxland.fi/downloads/edd-members/). The plugin is hosted here
on a public Github repository in order to better faciliate community contributions from developers and users alike.
If you have a suggestion, a bug report, or a patch for an issue, you can submit it here.

If you are using the plugin on a live site it would be cool that you purchase a valid license from [Plugin page](https://foxland.fi/downloads/edd-members/).
After that you get support and automatic updates.

## Support and documentation

* You can get [support from here](https://foxland.fi/support/forum/plugins/edd-members/).
* See all [documentation here](https://foxland.fi/documents/for/edd-members/).

## How does EDD Members work?

* Site owner can make any public post type content private.
* Or site owner can make singular post type content private.
* Plugin is filtering `the_content` when content is suppose to be private.
* Plugin loads `content-private.php` template for private content and that can be overwritten in a theme `edd-members` folder.
* Site admin have capability `edd_members_show_all_content` by default and can see everything. All the others can't see the content that's marked as private.
* In EDD download site owner can choose does he/she enable membership for particular download and pick membership length.
* You can also set different membership lengths for variable prices. This works kind of like a subscription levels. 
* After user have purchased one item that has membership enabled at any time length, user can see the content. The expire date is calculated and saved in `user_meta`.
* Site owner can set email notification before or after expire date.

## Copyright and License

Plugin resources are licensed under the [GNU GPL](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html), version 2 or later.

&copy; 2015-2016 [Sami Keijonen](https://foxland.fi).

## Changelog

### Version 1.2.1 - April 30, 2016

* Bug fix: Use `EDD_Recurring_Subscriber` class instead of `EDD_Recurring_Customer` class which is deprecated.
* Tweak: Link to EDD Members setting page have been fixed in post metabox.

### Version 1.2.0 - January 26, 2016

* Add multisite compability: User can now have different expire dates on sub sites.
* New section for settings.

### Version 1.1.4 - November 11, 2015

* Fix headers already sent warning. There was extra marks in the beginning of the user-meta.php file.
* Update edd action hooks because they might conflict with other add-ons.

### Version 1.1.3 - November 5, 2015

* Update edd action hooks because they might conflict with other add-ons.

### Version 1.1.2 - October 7, 2015

* Add `unknown_text` attribute to `[edd_members_expire_date]` shortcode. By default shortcode returns `Unknown` text if there is no expire date. You can overwrite default text like this: `[edd_members_expire_date unknown_text="Unfortunately your expire date is unknown."]` 
* Add `[edd_members_name]` shortcode. By default it shows user's display name. It accepts `field` attribute and can be like `field="first_name"`.
* Fix undefined notice when bbPress isn't activated.

### Version 1.1.1 - September 25, 2015

* Fix undefined notices and warnings in profile page by checking do we have expire date or not.

### Version 1.1.0 - September 16, 2015

* edd_members_only shortcode now outputs the same as private page if you leave message attribute empty.
* Secure every text string using esc_html and similar functions.

### Version 1.0.3 - June 3, 2015

* Enable sorting by Expire date in Users (users.php) admin page.

### Version 1.0.2 - May 25, 2015

* Fix: Get user ID from payment meta so that expire date calculation works for all payment gateways.

### Version 1.0.1

* Bug fix: during the EDD update plugin crashed.
* Added French translations.

### Version 1.0.0

* Everything's new!