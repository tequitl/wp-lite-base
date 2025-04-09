=== Version Control Your Content ===
Contributors: harisamjed
Donate link: https://buymeacoffee.com/harisamjed/
Tags: version control, git, github
Requires at least: 5.6
Tested up to: 6.7.2
Requires PHP: 7.2
Stable tag: 1.0.0
License:         GNU General Public License v3.0
License URI:     http://www.gnu.org/licenses/gpl-3.0.html

Provides an alternative to the native WP Revisions feature using Git services. Also works for Additional CSS and wp-admin Settings pages.

== Description ==

The Version Control Your Content plugin provides an alternative to the native WP [Revisions](https://wordpress.org/documentation/article/revisions/) feature using Git services.

= Requirements =
- A GitHub account. If you don't have one, you can create one [here](https://github.com/join).
- A GitHub private repository. If you don't have one, you can create one [here](https://github.com/new).
- (Optional but highly recommended) A GitHub fine-grained personal access token. If you don't have one, you can create one [here](https://github.com/settings/tokens/new).

This plugin provides an option for version control in the following places for now:

1. Block Editor (Gutenberg)
2. Classic Editor
3. Additional CSS in the Customizer
4. Default Settings pages in WP Admin

On all supported pages, you will see a "Version Control" box, and you can activate or deactivate it for that page. It will also show you the real-time GitHub API usage in the top admin bar.

This solution will put minimal load on your server because there will be no database entries, no complex SQL operations, and only simple JavaScript-based API calls to external Git services.

= How it works behind the scenes? =

1. When you submit any HTML form, such as saving a post, it captures the form data via JavaScript before sending it to the server backend.
2. It sanitizes the data and converts it to JSON format (if needed).
3. It sends that JSON or HTML data to your Git repository via a commit.
4. In this way, all your changes will be new commits in your Git repo in related JSON or HTML files.
5. In the commits box, a list of all commits will be shown. It will also provide an option to view the changes or revert to that version.

= Planned Upcoming Features =
Currently, I have developed it for three views as described above, and the following features will be added in the future:

- Integration with page builders like Elementor, Divi, Beaver Builder, WPBakery, etc.
- Integration with WooCommerce
- Integration with BuddyPress
- Integration with bbPress
- Integration with WPML, etc.

== Installation ==

1. Upload the plugin ZIP file and search for it on the "Add New Plugin" page.
2. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).
3. Go to the "Version Control" page in WP Admin.
4. Add a new connection and activate it.
5. "Version Control" boxes will appear in the post editor, Additional CSS, and Settings pages.
6. A new version will be created every time you press the save/submit button.

== Frequently Asked Questions ==

= What is version control? =
Version control is a system that allows you to track changes to your content over time. It enables you to revert to previous versions of your content and compare changes between different versions.

= Is it free to use? =
Yes, it is free to use. GitHub has been offering free private repositories since Jan 2019, but with some limitations. I think the current offering will work for most small to medium websites. You can upgrade to an Enterprise plan on GitHub if you max out the free plan.

= What is its use in settings pages? =
As per my experience, website owners change site titles, taglines, and other details every now and then for SEO reasons or otherwise. Currently, it is a manual process to keep track of all these changes, perhaps in an external spreadsheet or document. With this plugin, all your history will be maintained automatically with date and time.

= How is this plugin different from activity monitor plugins? =
This plugin is different from many available activity logging or monitoring plugins in that it won't add anything to your database, so it should not create any performance issues. Instead, it will just send logs to your secure private Git repository.

= Is this plugin a replacement for backup plugins/systems? =
No, it is not intended to replace any backup plugin or backup system. It just provides a history of all the changes you made to your content.

= Are there any limitations of GitHub API? =
GitHub API has a limit of 5000 requests per hour. The maximum repository size limit is 1GB. I think that should be enough for most websites.

= What about other Git services like Bitbucket or GitLab? =
This plugin is currently using the GitHub API. However, it is designed to be extended to other Git services like Bitbucket or GitLab in the future.

= Why is a personal access token better than the VCYC OAuth App? =
A personal access token is better because you have full control over what you want to share from your account. You can give access to only a single repository to test this version control plugin.

= My repository is not showing up in the list? =
This plugin works for private repositories only since I see no reason to share your content with the public.

== Screenshots ==
1. Version Control in Block Editor
2. Version Control in Classic Editor
3. Version Control in Customizer's Additional CSS
4. Version Control in Customizer's Settings pages
5. Add a new connection page
6. Add new Connection form
7. Connections display page
8. GitHub API usage quota
9. Add GitHub fine-grained personal access token
10. Commit box as popup

== Changelog ==
= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.0.0 =
* This is the initial release of the plugin.

