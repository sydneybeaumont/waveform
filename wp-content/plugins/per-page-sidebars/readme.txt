=== Per Page Sidebars ===
Contributors: BrianLayman
Tags: posts, pages, sidebars, widgets
Donate: http://thecodecave.com/donate
Requires at least: 3.1
Tested up to: 3.4
Stable tag: 2.0.0

The Per Page Sidebars (PPS) plugin allows blog administrators to create a unique sidebar for each Page. No template editing is required.

== Description ==
This plugin allows the creation and display of custom sidebars for any page (or post) on your site. On each page, you can choose which of your current theme's sidebars will be replaced. The replacement works for all descendants of a replaced page. 

In this fashion you can:
1. Easily create a CMS like site with unique sidebars for every page, even if your theme doesn't support it.
2. Create a sidebar with a Cart widget that displays on store pages created by the WP-E-Commerce page, but not anywhere else on the site.
3. Use parent pages to create themed areas of your site, without creating custom page templates.

This plugin is compatible with every theme and all widgets because it hijacks your theme's sidebars instead of replacing them. 

== Installation ==

The installation process.

1. Upload  to the `/wp-content/plugins/` directory. Or directly upload from your Plugin management page.
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. This is the metabox that appears on any page or post you are editing. It shows a list of all sidebars/widget areas built into the current theme. To replace one of them with a custom sidebar, you just need to mark the sidebar you are to replace.

2. Under Appearance->Widgets, for each page or post on you've activated a custom sidebar, you will see a widget area like this one - named after the post or page on which it will be used.

3. This plugin adds a "Custom Sidebar" metabox to the edit screens for pages and posts. Leaving the "Activate Custom Sidebar" box unchecked means that the plugin does not change the behavior of that page/post.

4. This screenshot shows a typical blog running the 2010 theme and displaying the default sidebar.

5. On the edit screen for any page or post, to create a custom sidebar, simply check "Activate Custom Sidebar" and choose the sidebar you will be replacing.

6. When you activate a custom sidebar on a page or post, the Appearance->Widgets screen gets a widget area created for that page or post.

7. Any widgets you put in the custom sidebar's widget area will be displayed with that page or post, completely replacing the chosen sidebar.

== Use ==

1. Edit a page or create and save one. It must at least have a title.
2. In the “Custom Sidebar” section, check “Activate a custom sidebar” and select the sidebar to replace.
3. On the Appearance->Widgets screen, place the widgets on the newly created sidebar that matches your post. 

That’s it. No code. No CSS changes. No custom templates.

This video describes the usage of the plugin:
 `[youtube http://www.youtube.com/watch?v=_U2XfrU7nZQ]`

== Frequently Asked Questions ==

= My left sidebar was replaced on the parent but it not on the child, why? = 

Some themes allow you to chose different sidebar layouts on each page.  Sometimes these themes rely on a Primary/Secondary Sidebar configuration rather than a Right/Left sidebar convention. This may lead to confusion when the parent displays two sidebars and the child only displays one.  This plugin replaces the sidebar by name not by position.  In a 2 column layout, the only sidebar is by definition the "Primary" sidebar. Replacing a "Secondary" sidebar in the parent page will not alter the appearance of 2 column child page if the "Secondary" sidebar is never displayed.

= Can I replace more than one sidebar per page? = 
Currently only one sidebar can be replaced per page.  If you wish to replace multiple sidebars per page, leave a commment and I may add this feature.

= Can I replace sidebars on posts or custom post types? = 
YES! Version 2 of the plugin allows sidebars on posts.  However, custom post types are not yet supported.  If this is a disired feature, leave comments requesting it and I can add the feature fairly easily.

= Can my authors & contributors replace the sidebar on a page? = 
A user can only replace a sidebar on a page if their role has the *edit_theme_options* capability. A user can edit a page that has been assigned custom sidebar and that custom sidebar will not be dropped. A user without the *edit_theme_options* capability simply cannot turn that functionality on or off.

== Changelog ==

= 1.0.0 =

* Initial release.

= 2.0.0 =

* Added the ability to activate custom sidebars on posts
* Fixed a bug that caused the wrong custom sidebar to be displayed on the page that was assigned as the Post page
* Added screenshots and video to the documentation of the plugin