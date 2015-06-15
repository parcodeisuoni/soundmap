=== Soundmap ===
Contributors: xavierbalderas, parcodeisuoni, codiceovvio
Tags: audio, sound, google-maps, openstreet-maps, geolocation, soundscape, sound
Donate link: http://www.audio-lab.org
Requires at least: 3.8
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Plugin for creating and publishing Sound Maps


== Description ==

Soundmap is a free plugin for implement Sound Maps over a Wordpress site with both OpenStreet and Google Maps Api. The plugin creates a new content type called Marker, wich represents the Sound Marker over the map.

What Soundmap can be used for:

*   With Soundmap you can publish a sound you recorded and place a marker over a web map.
*   The published markers can be accessed through both Google and OpenStreet maps, within the site in which they're published
*   Soundmap handles as well any other post content that can be written with WordPress, e.g. thumbnails or featured images, post content, post custom fields, post excerpt, etc..
*   You can locate the post and add it to a marker to be displayed on the map.
*   Clicking on it will open an infoWindow with the post content inside.
*   The plugin is specifically designed to handle mp3 audio files, but it can correctly display any field saved inside the WordPress post object.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the _Plugins_ menu in WordPress
3. The plugin settings can be accessed via the _Settings_ menu in the administration area (either your site administration for single-site installs, or your network administration for network installs).


== Frequently Asked Questions ==

= 1 - Usage =

Once you have activated the plugin on your WordPress installation, creating a soundmap is very easy. On the configuration panel for plugin, located under the Settings panel of Wordpress, ypu will find all the options related with the plugin, just selected the original position, zoom and type for the map and the sound player you want to use.

= 2 - Configuration =

Under the Setting option of you Wordpress Administration Area you will find a new option called _Sound Map_. This option allows you to configure al the parameters of the plugin.

To configure the map presentation, just move, zoom or change the style of the map, and the plugin will reflect the new configuration. This configuration will be used for the administration area and for the template.

You can adjust the map's parameters also manually with the fileds available. There is also one option box area for selecting the mp3 player plugin you want to use for the Sound Map.

Just remember to click on SAVE after you have done all the changes.

= 3 - Showing the soundmap =

To show the map, you only have use the theme tag the_map as follows:

`the_map(css_id = 'map_canvas', all_markers = FALSE, $options = array());`

in the template page you want to show the map.
More information about this and more theme tags in the inline documentation.
To render the map, just include this HTML line inside the template file you want it to show: `<?php the_map("map_canvas",true); ?>`

= ________________________________________ =

For more info, look in the [plugin's WIKI pages](https://github.com/audiolab/SoundMap-Wordpress-Plugin/wiki "Soundmap Wiki and Infos") on GitHub.


== Changelog ==

= 0.6 =
* updated plugin header
* added readme.txt

= 0.5 =
* updated old procedural plugin version to the new oop structure


== Upgrade Notice ==

= 0.5 =
* Updated from the old version to get it working with the latest WordPress installation (at the moment 4.2.2)
The old plugin version can be used with WordPress up to version 3.4.2, but it is highly recommended to keep your site updated to avoid possible security issues.