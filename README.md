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

2015 &copy; [Sami Keijonen](https://foxland.fi).

## Changelog

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