=== Kama WP Smiles ===
Contributors: Tkama
Official website: http://wp-kama.ru/id_185/palagin-cmaylikov-na-lyuboy-vkus-v-postah-i-kommentariyah-dlya-wordpress.html
Tags: comments, smiles, posts, optimization
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: 1.6.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Replace original WordPress smiles to pretty dynamic one. Automaticaly add smiles block to comment form and to admin panel Visual/HTML editor.

== Description ==

Kama WP Smiles adds block with smiles to comment form in your theme and to HTML/Visual editor in admin panel, that allow easily add smiles to the comment or post content. Also, plugin replace original WordPress emoticons by new pretty dinamic one.

With Kama WP Smiles visitors of you blog will have easy instrument to add smiles in comments and you will have opportunity to add smiles while writing posts or answer comments.

On plugin settings page, you can choose which of accessible smiles will appear in the smiles block. And you can tune smiles special code like :) which will be replaced on smile image in content.

If you don't enjoy the plugin, you can just delete it. On uninstall, plugin will clean up after itself.

[Official plugin page](http://wp-kama.ru/id_185/palagin-cmaylikov-na-lyuboy-vkus-v-postah-i-kommentariyah-dlya-wordpress.html)


== Installation ==

1. Activate the plugin through the "Plugins" menu in WordPress;
2. Go to setting `settings/Kama WP Smiles` page and choose smiles that you want to appear in smile block.


== Frequently Asked Questions ==

= Plugin don't add smile block to comment form =

May be, you comment form textarea HTML tag have not default ID attribute. Specify comment form ID attribute tag on settings page. Default is `comment`

= I have HTML tag &lt;var&gt; where it needn't replace smile code to smile image =

Add exceptions tags on settings page in which no need to replace smile code to smile. Default is `code,pre`.

= Plugin can't add smile block to comment form correctly. How can i do it myself? =

To add smile block to comment form themself. Leave empty comment ID field in settings page and use this code in your theme:

    <?php echo kama_sm_get_smiles_code(); ?>
	
== Screenshots ==

1. Admin panel settings page.
2. Comment form with smile block in theme.
3. Comment form with smile block in admin panel.
4. Post html/visual editor with smile block in admin panel.

== Changelog ==

= 1.6.6.1 (6.09.2014) =
Adaptation to WP 4.0

= 1.6.0 (24.01.2014) =
1. Images in smile block now is not image and not downloading with page. It save HTML requests.
2. Now select used smiles in admin panel more comfortable.
3. New principle to add smile block to comment form.
4. CSS styles and JS scripts now adding direct to HTML document. It save HTML requests.
5. Added smile block in admin panel.
6. On uninstall, plugin will remove all it settings and smiles code strings from posts and comments content.
5. Improve plugin PHP code.

= 1.5.0 =
Add ability to specify exceptions tags in which plugin wiil not replace smile sode to smile image. 

