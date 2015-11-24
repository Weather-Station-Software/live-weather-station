=== Live Weather Station for Netatmo ===
Contributors: PierreLannoy
Tags: wordpress, widget, weather, shortcode, netatmo, meteo, live, lcd, gauge
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to display, in many different and elegant ways, the meteorological data collected by your Netatmo weather station.

== Description ==

**DISCLAIMER:** *This plugin is developed and maintained by me, [Pierre Lannoy](https://pierre.lannoy.fr "Pierre Lannoy"). This plugin IS NOT an official software from [Netatmo](http://www.netatmo.com "Netatmo Homepage") and is not endorsed or supported by this company.
Moreover, I am not a partner, employee, affiliate, or licensee of Netatmo. I'm just a happy customer/user of their products and a fan of meteorology.*

= Live Weather Station for Netatmo =
Live Weather Station for Netatmo is a plugin that allows you to display, on your WordPress site, meteorological data from the Netatmo weather stations you have access to.
To date, you can display data as follows:

* a "classical" widget that displays outdoor weather data
* a highly configurable LCD panel control which displays the data you have selected
* textual values you can insert in your articles and pages via configurable shortcodes

To see it in action, go to the [demo page](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo-demo/).

Note that more controls and viewers will be launched in the coming months.

= Simple and efficient =
The use of Live Weather Station for Netatmo requires no knowledge in programming and does not require writing code.
Just set it, insert (in a page or an article) the provided shortcodes. And it works!

= How does it work? =
Once you have connected the plugin to your Netatmo account (via the dashboard of your WordPress site), the data you have access to (your stations, plus all those for which you have permission) are collected every 10 minutes and stored in the database of your WordPress site.
The various controls and viewers (like widget or LCD Panel) now will get their data from this database with the certainty of having fresh and cached data.

= Supported devices & modules =
Live Weather Station for Netatmo supports all current devices and modules from Netatmo. This includes, in addition to the main station base:

* indoor modules
* outdoor modules
* rain gauges
* wind gauges

= Supported languages =
Right now, Live Weather Station support the following languages:

* English / default
* French

Multilingualism is provided by the use of .po files (the standard way), so it is fully translatable in your language. If you have translated this plugin in a new language, get in touch with me, I will include it in the next release or, if you want you can translate the plugin directly with [GlotPress](https://translate.wordpress.org/projects/wp-plugins/live-weather-station) (use the 'stable' column).

= Instructions =
You can find a more in-depth description and instructions to configure [on this page](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/).

= Support =
This plugin is free and provided without warranty of any kind. Use it at your own risk, I'm not responsible for any improper use of this plugin, nor for any damage it might cause to your site. Always backup all your data before installing a new plugin.

Anyway, I'll be glad to help you if you encounter issues when using this plugin. Just use the support section of this plugin page.

== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'.
2. Search for 'Live Weather Station for Netatmo'.
3. Click on the 'Install Now' button.
4. Activate Live Weather Station for Netatmo.

= From WordPress.org =

1. Download Live Weather Station for Netatmo.
2. Upload the 'live-weather-station' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...).
3. Activate Live Weather Station for Netatmo from your Plugins page.

= Once Activated =

1. Visit 'Settings > Live Weather Station' and set your Netatmo credentials.
2. Enjoy!

== Frequently Asked Questions == 

= Where can I get support? =

Support is provided via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

= Where can I find documentation? =

You can find instructions in english [here](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/). Vous trouverez des instructions en français [ici](https://pierre.lannoy.fr/wordpress-live-weather-station-pour-netatmo/).

= Where can I report a bug? =

You can report bugs and suggest ideas via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

= What are the requirements for this to work? =

You need WordPress 4.X and a PHP version greater than or equal to 5.4.

== Changelog ==

= 1.2.2 =

Release Date: November 25th, 2015

* Added an "about box" in dashboard
* Improvement: widget now shows max wind strength of the day
* Improvement: widget tooltip now shows the wind direction in plain text
* Improvement: better handling of wind direction when strength is null
* Improvement: secured handling of wind strength historic and max wind strength
* Improvement: for some unknown reason, some netatmo rain gauge doesn't aggregate hour/daily values. The widget now take care of this...
* Improvement: readme.txt
* Bug fix: a <a> tag was not closed in some circumstances in the admin panel
* Bug fix: wrong displaying in the admin panel when there was more than one station to show
* Bug fix: color picker was not working with some themes delaying JQuery loading

= 1.2.1 =

Release Date: November 24th, 2015

* Bug fix: wrong settings when not updating widget after Live Weather Station update
* Bug fix: no icon in widget when it was displayed in content (not in sidebar)
* Improved readme.txt

= 1.2.0 =

Release Date: November 23rd, 2015

* Added wind chill computing ([what is it?](https://en.wikipedia.org/wiki/Wind_chill)): available in widget, textual shortcodes and live controls (as outdoor values)
* Added cloud base (above ground level) computing ([what is it?](https://en.wikipedia.org/wiki/Cloud_base)): available in widget, textual shortcodes and live controls (as outdoor values)
* New option for altitude displaying: metric or imperial system
* Bug fix: PHP notice when not updating widget settings after Live Weather Station update
* Bug fix: wrong battery level and signal quality stored for virtual modules
* Improved readme.txt

= 1.1.1 =

Release Date: November 19th, 2015

* Bug fix: wrong border style when widget was displayed in content (not in sidebar)
* Improved readme.txt

= 1.1.0 =

Release Date: November 19th, 2015

* Added dew point and frost point computing ([what is it?](https://en.wikipedia.org/wiki/Dew_point)): available in widget, textual shortcodes and live controls (as outdoor values)
* Added heat index (USA, humidity based) computing ([what is it?](https://en.wikipedia.org/wiki/Heat_index)): available in widget, textual shortcodes and live controls (as outdoor values)
* Added humidex (Canada, dew point based) computing ([what is it?](https://en.wikipedia.org/wiki/Humidex)): available in widget, textual shortcodes and live controls (as outdoor values)
* Added temperature trend: data collecting and output rendering in all shortcodes
* Added pressure trend: data collecting and output rendering in all shortcodes
* Added altitude of the main station base: data collecting and output rendering in textual shortcodes
* Added coordinates (latitude and longitude) of the main station base: data collecting and output rendering in textual shortcodes
* Added device/module firmware revision: data collecting and output rendering in textual shortcodes
* New options for widget: background opacity and show/hide borders
* New option for live controls: show/hide computed values
* Improved readme.txt

= 1.0.0 =

Release Date: November 14th, 2015

* First public version

== Upgrade Notice ==

= 1.2.X =
Live Weather Station for Netatmo now computes wind chill and cloud base altitude.

= 1.1.X =
Live Weather Station for Netatmo now : computes dew & frost points, heat index and humidex; supports temperature & pressure trends; supports altitute and coordinates for the main station base; supports firmware version for all module types; improves look & feel of its widget.

= 1.0.X =
Initial version.