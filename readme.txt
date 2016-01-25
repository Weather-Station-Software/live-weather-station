=== Live Weather Station ===
Contributors: PierreLannoy
Tags: wordpress, widget, weather, shortcode, openweathermap, netatmo, meteo, live, lcd, gauge, ephemeris, forecast, current weather, forecast widget, local weather, weather forecasts, weather widget, conditions, current conditions, weather by city, temperature, wind speed, wind, wind strength, pressure, humidity, co2, rain, snow, cloudiness, cloud, moon, moon phase, sunrise, sunset, moonrise, moonset, noise, weather station, dew, frost, humidex, heat index, wind chill
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 2.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display, in many different and elegant ways, the meteorological data collected by your Netatmo weather station or coming from OpenWeatherMap.

== Description ==

> Since 2.0 version of Live Weather Station plugin you can now display weather data even if you don't have a Netatmo station, simply by getting [a free OpenWeatherMap API key](http://openweathermap.org/appid). Of course, this plugin works always as well with all your Netatmo stations.


= Live Weather Station =
Live Weather Station is a plugin that allows you to display, on your WordPress site, meteorological data from the Netatmo weather stations you have access to. Initialy designed for Netatmo products, it provides now full support for free OpenWeatherMap services too...
To date, you can display data as follows:

* a "classical" configurable widget that displays outdoor weather data
* an "ephemeris" configurable widget that displays astronomical computed values
* a highly configurable LCD panel which displays selected weather data
* a highly configurable clean gauge control which displays the live weather data you have selected
* a hugely configurable steel meter control which displays the live weather data you have selected
* textual and numerical values you can insert in articles and pages via configurable shortcodes

To see it in action, go to the [demo page](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo-demo/).

= Simple and efficient =
The use of Live Weather Station requires no knowledge in programming and does not requires writing code.
Just set it, insert (in a page or an article) the provided shortcodes. And it works!

= How does it work? =
Once you have connected the plugin to your Netatmo account (via the dashboard of your WordPress site), the data you have access to (your stations, plus all those for which you have permission) are collected every 10 minutes and stored in the database of your WordPress site.
For OpenWeatherMap the process is the same: after entering your free API key, the weather data are collected every 15 minutes and stored in the database of your WordPress site.
The various controls and viewers (like widgets, LCD panel, gauges, etc.) now will get their data from this database with the certainty of having fresh and cached data.

= Supported data, devices & modules =
Live Weather Station supports all measured values coming from OpenWeatherMap excluding UV index, brightness and radiations.
It supports all current devices and modules from Netatmo. This includes, in addition to the main station base:

* indoor modules
* outdoor modules
* rain gauges
* wind gauges

= Supported languages =
Right now, Live Weather Station supports the following languages:

* English / default
* French

Multilingualism is provided by the use of .po files (the standard way), so it is fully translatable - by you - in your language. If you have translated this plugin in a new language, get in touch with me, I will include it in the next release or, if you want you can translate the plugin directly with [GlotPress](https://translate.wordpress.org/projects/wp-plugins/live-weather-station) (use the 'stable' column).

An effort from some members to begin the **german** translation have been done. If you speak german, you can contribute to this translation [here](https://translate.wordpress.org/locale/de/default/wp-plugins/live-weather-station) (choose 'stable' row) to finish it and include german language in the last release of the plugin.

= Instructions =
You can find a more in-depth description and instructions to configure [on this page](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/).

= Support =
This plugin is free and provided without warranty of any kind. Use it at your own risk, I'm not responsible for any improper use of this plugin, nor for any damage it might cause to your site. Always backup all your data before installing a new plugin.

Anyway, I'll be glad to help you if you encounter issues when using this plugin. Just use the support section of this plugin page.

> **DISCLAIMER:** *This plugin is developed and maintained by me, [Pierre Lannoy](https://pierre.lannoy.fr "Pierre Lannoy"). This plugin **IS NOT** an official software from [Netatmo](http://www.netatmo.com "Netatmo Homepage") or [OpenWeatherMap](http://openweathermap.org/) and is not endorsed or supported by these companies. Moreover, I am not a partner, employee, affiliate, or licensee of Netatmo or OpenWeatherMap. I'm just a happy customer/user of their products and a fan of meteorology.*


== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'.
2. Search for 'Live Weather Station'.
3. Click on the 'Install Now' button.
4. Activate Live Weather Station.

= From WordPress.org =

1. Download Live Weather Station.
2. Upload the `live-weather-station` directory to your `/wp-content/plugins/` directory, using your favorite method (ftp, sftp, scp, etc...).
3. Activate Live Weather Station from your Plugins page.

= Once Activated =

1. Visit 'Settings > Live Weather Station' and set your Netatmo credentials and/or your OpenWeatherMap API key (you can get it for free [here](http://openweathermap.org/appid)).
2. Enjoy!

== Frequently Asked Questions ==

= What are the requirements for this plugin to work? =

You need **WordPress 4.X** and at least **PHP 5.4**.

= Can this plugin work on multisite? =

Yes. You can install it via the network admin plugins page but the plugin **must not be "Network Activated"**, instead you must activate it on a site by site basis.

= Where can I get support? =

Support is provided via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

= Where can I find documentation? =

You can find instructions in english [here](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/). Vous trouverez des instructions en français [ici](https://pierre.lannoy.fr/wordpress-live-weather-station-pour-netatmo/).

= Where can I report a bug? =

You can report bugs and suggest ideas via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

== Changelog ==

= 2.2.0 =

Release date: January 25th, 2015

* Added: new steel meters, with tons of customization parameters, to display "live" values
* Improvement: using of subscript character for carbon dioxide, now displayed as CO₂ (previously, it was CO2)
* Improvement: in clean gauges it's now possible to choose to display the shortened measurement type as title or label
* Improvement: `readme.txt`

= 2.1.0 =

Release date: January 13th, 2015

* Added: new clean gauges to display "live" values
* New option for outdoor weather widget: you can now choose to hide obsolete data
* Improvement: pressure displayed in inHg have now reasonable accuracy with 2 decimals
* Improvement: the data collecting method for Netatmo & OpenWeatherMap is now "bullet-proof"
* Improvement: an improved connection mode that should avoid unwanted disconnections (on Netatmo servers temporary errors)
* Improvement: refresh cycle for live controls goes from 5 minutes to 2 minutes - datas are fresher than ever ;)
* Improvement: the "about box" is now much more readable
* Improvement: `readme.txt`
* Bug fix: some typos in french translation.

= 2.0.1 =

Release date: December 22nd, 2015

* Improvement: `readme.txt`
* Bug fix: PHP notice while collecting non-existent rain or snow values from OpenWeatherMap.

= 2.0.0 =

Release date: December 17th, 2015

*Note to existing 1.X users: due to the new connection mode (see improvements below), you must set again your Netatmo credentials in plugin dashboard after the plugin update.*

* Added: ability to create "virtual" weather stations based on OpenWeatherMap records ; you can now use Live Weather Station even if you don't have a Netatmo weather station.
* Added: when a Netatmo station is correctly located (in netatmo online app) its collected data are enriched with data from OpenWeatherMap (cloudiness, snow falls, etc.) if collect mode is set to "Netatmo and OpenWeatherMap".
* Added: astronomical computed values (aka ephemeris) for sun (sunrise, sunset, distance and angular size) and for moon (mooonrise, moonset, age, phase, illumination, distance and angular size), available via shortcodes (textual values) and a new widget.
* New option for outdoor weather widget and live controls: you can now set a data obsolesence time beyond which data will no longer be displayed. This is especially useful for Netatmo modules management (when turned off or disconnected from the main base) or while connectivity issues.
* New option for outdoor weather widget: it's now possible to specify max width of the rendered widget.
* New option for outdoor weather widget: subtitle can now be "nothing", "date & time of records" or "station coordinates (if known)".
* New options for outdoor weather widget: you can now display current weather (as a big icon), cloudiness and snowfall if connected to OpenWeatherMap.
* New option for distance displaying: metric or imperial system.
* New option for textual shortcodes: wind direction/angle can now be displayed abbreviated or in plain text.
* New option for live controls: displaying only measured data or measured and computed data.
* Improvement: new connection mode to Netatmo account to comply with the new manufacturer's recommendations that take effect in a few months (no more login or password storing).
* Improvement: computed values are now displayed in the site dashboard as an additional module for each station.
* Improvement: reference wind strength for wind chill computing can now be displayed via shortcodes.
* Improvement: stations time zone can now be displayed via shortcodes.
* Improvement: for some unknown reason, some netatmo wind gauges doesn't have historical values nor gusts values. The widget now take care of this...
* Improvement: in textual shortcodes, timestamps can now be displayed in remaining or elapsed approximative time.
* Improvement: times and dates in widgets, textual shortcodes and site dashboard are now local to station (in previous version it was local to server).
* Improvement: more meaningful error message if no data to show in LCD Panel.
* Improvement: `readme.txt`
* Bug fix: unable to set colors for widgets when displayed for the same station on the same page.
* Bug fix: wrong preview of LCD panel in the site dashboard (admin panel) when there was more than one Netatmo station.
* Bug fix: malformed subtitles in LCD panel for some wind and rain gauges.


= 1.2.2 =

Release date: November 25th, 2015

* Added an "about box" in dashboard
* Improvement: widget now shows max wind strength of the day.
* Improvement: widget tooltip now shows the wind direction in plain text.
* Improvement: better handling of wind direction when strength is null.
* Improvement: secured handling of wind strength historic and max wind strength.
* Improvement: for some unknown reason, some netatmo rain gauges doesn't aggregate hour/daily values. The widget now take care of this...
* Improvement: `readme.txt`
* Bug fix: a `<a>` tag was not closed in some circumstances in the admin panel.
* Bug fix: wrong displaying in the admin panel when there was more than one station to show.
* Bug fix: color picker was not working with some themes delaying JQuery loading.

= 1.2.1 =

Release date: November 24th, 2015

* Bug fix: wrong settings when not updating widget after Live Weather Station update.
* Bug fix: no icon in widget when it was displayed in content (not in sidebar).
* Improved `readme.txt`

= 1.2.0 =

Release date: November 23rd, 2015

* Added wind chill computing ([what is it?](https://en.wikipedia.org/wiki/Wind_chill)): available in widget, textual shortcodes and live controls (as outdoor values).
* Added cloud base (above ground level) computing ([what is it?](https://en.wikipedia.org/wiki/Cloud_base)): available in widget, textual shortcodes and live controls (as outdoor values).
* New option for altitude displaying: metric or imperial system.
* Bug fix: PHP notice when not updating widget settings after Live Weather Station update.
* Bug fix: wrong battery level and signal quality stored for virtual modules.
* Improved `readme.txt`

= 1.1.1 =

Release date: November 19th, 2015

* Bug fix: wrong border style when widget was displayed in content (not in sidebar).
* Improved `readme.txt`

= 1.1.0 =

Release date: November 19th, 2015

* Added dew point and frost point computing ([what is it?](https://en.wikipedia.org/wiki/Dew_point)): available in widget, textual shortcodes and live controls (as outdoor values).
* Added heat index (USA, humidity based) computing ([what is it?](https://en.wikipedia.org/wiki/Heat_index)): available in widget, textual shortcodes and live controls (as outdoor values).
* Added humidex (Canada, dew point based) computing ([what is it?](https://en.wikipedia.org/wiki/Humidex)): available in widget, textual shortcodes and live controls (as outdoor values).
* Added temperature trend: data collecting and output rendering in all shortcodes.
* Added pressure trend: data collecting and output rendering in all shortcodes.
* Added altitude of the main station base: data collecting and output rendering in textual shortcodes.
* Added coordinates (latitude and longitude) of the main station base: data collecting and output rendering in textual shortcodes.
* Added device/module firmware revision: data collecting and output rendering in textual shortcodes.
* New options for widget: background opacity and show/hide borders.
* New option for live controls: show/hide computed values.
* Improved `readme.txt`

= 1.0.0 =

Release date: November 14th, 2015

* First public version.

== Upgrade Notice ==

= 2.2.X =
New steel meters with tons of customization options to display measurements and some improvements.

= 2.1.X =
New clean gauges to display measurements and many improvements.

= 2.0.X =
New major release with tones of new features and options. It is no longer necessary to have a Netatmo station to use the plugin: it also becomes suitable for use with OpenWeatherMap!

= 1.2.X =
Live Weather Station for Netatmo now computes wind chill and cloud base altitude.

= 1.1.X =
Live Weather Station for Netatmo now: computes dew & frost points, heat index and humidex; supports temperature & pressure trends; supports altitute and coordinates for the main station base; supports firmware version for all module types; improves look & feel of its widget.

= 1.0.X =
Initial version.

== Screenshots ==

1. A widget displaying weather data. Note that you can customize which data are displayed, colors, design, etc.
2. A widget displaying extended ephemeris.
3. A compact widget displaying ephemeris in flat design.
4. Using collected data shortcodes, to fill TablePress tables with textual values.
5. A LCD panel to display current temperatures. Note that you can customize which data are displayed, colors, design, etc.
6. 3 clean gauges to display current temperatures. Note that you can customize which data are displayed, colors, design, etc.
7. 2 clean gauges on a dark background.
8. Some types of steel meters.
9. A view of the plugin dashboard.
10. Configuration of a shortcode to show a LCD panel.
11. Configuration of a shortcode to show a clean gauge.
12. Configuration of a shortcode to show a steel meter.