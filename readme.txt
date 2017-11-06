=== Bg Book Publisher ===
Contributors: VBog
Tags: book, publisher, table of contents, nextpage, page, header, level
Requires PHP: 5.3
Requires at least: 3.0.1
Tested up to: 4.8.3
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html


The plugin helps you to publish big book with a detailed structure of chapters and sections and forms table of contents of the book.

== Description ==

When you save a post, the plugin splits it into pages and adds a table of contents in the form of a spoiler to the top of each page.

On the post editing page you can set the maximum level of headers included in the table of contents and the level of headings where you break the pages (see screenshot).

You can set the header levels from 1 to 6, and also disable the action of the plug-in for this post by unchecking the item 'this post is book'.

You can also set the author of the published book, which will be showed directly in the post title.
To insert name of book author into the text you can also use shortcode [book_author]  or PHP-function bg_bpub_book_author($post_id) in page template.

To customize the post appearance, you can use the following classes:
1. bg_bpub_toc - class of contaner (div) with table of contents.
2. bg_bpub_toc_h1 ... bg_bpub_toc_h6 - classes of chapter headers in table of contents.
3. bg_bpub_book_author - class of contaner (span) with name of book author.

== Installation ==

1. Upload 'bg-book-publisher' directory to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Plugin's post settings.
2. Plugin's options.

== Changelog ==

= 1.0 =

* Plugin in start edition

== Upgrade Notice ==

* Start edition

== Notes for Translators ==

You can translate this plugin using POT-file in languages folder with program PoEdit (http://www.poedit.net/). 
More in detail about translation WordPress plugins, see "Translating WordPress" (http://codex.wordpress.org/Translating_WordPress).

Send me your PO-files. I will insert them in plugin in next version.

== License ==

GNU General Public License v2

