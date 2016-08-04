=== Live Weather Station ===
Contributors: PierreLannoy
Tags: wordpress, widget, weather, shortcode, openweathermap, netatmo, meteo, live, lcd, gauge, ephemeris, forecast, current weather, forecast widget, local weather, weather forecasts, weather widget, conditions, current conditions, weather by city, temperature, wind speed, wind, wind strength, pressure, humidity, CO2, rain, snow, cloudiness, cloud, moon, moon phase, sunrise, sunset, moonrise, moonset, noise, weather station, dew, frost, humidex, heat index, wind chill, weather plugin, wordpress widget, wind gauge, rain gauge, pws, met office, personal weather station, weather underground, wunderground, weather observations website, wow, observation, pollution, CO₂, CO, O3, O₃, ozone, carbon dioxide, carbon monoxide
Requires at least: 4.0
Tested up to: 4.6
Stable tag: 2.9.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://support.laquadrature.net/

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
Once you have connected the plugin to your Netatmo account (via the dashboard of your WordPress site), the data you have access to (your stations, plus all those for which you have permission) are collected every 5 minutes and stored in the database of your WordPress site.
For OpenWeatherMap the process is the same: after entering your free API key, the weather data are collected every 15 minutes and stored in the database of your WordPress site.
The various controls and viewers (like widgets, LCD panel, gauges, etc.) now will get their data from this database with the certainty of having fresh and cached data.

= Supported data, devices, modules & services =
Live Weather Station supports all measured values coming from OpenWeatherMap excluding UV index, brightness and radiations.
It supports all current devices and modules from Netatmo. This includes, in addition to the main station base:

* indoor modules
* outdoor modules
* rain gauges
* wind gauges

Live Weather Station allows you to send your Netatmo outdoor data, at a 10 minutes frequency, to the following services:

* [Met Office](http://wow.metoffice.gov.uk/) weather observations website
* [PWS Weather](http://www.pwsweather.com/)
* [Weather Underground](https://www.wunderground.com/)

= Supported languages =
Right now, Live Weather Station supports the following languages:

* Dutch (thanks to [@hanstis](https://profiles.wordpress.org/hanstis))
* English (default)
* English / Australia (thanks to [translation team](https://translate.wordpress.org/locale/en-au/default/wp-plugins/live-weather-station))
* English / Canada (thanks to [translation team](https://translate.wordpress.org/locale/en-ca/default/wp-plugins/live-weather-station))
* English / New Zealand (thanks to [translation team](https://translate.wordpress.org/locale/en-nz/default/wp-plugins/live-weather-station))
* English / South Africa (thanks to [translation team](https://translate.wordpress.org/locale/en-za/default/wp-plugins/live-weather-station))
* English / UK (thanks to [translation team](https://translate.wordpress.org/locale/en-gb/default/wp-plugins/live-weather-station))
* French / Belgium (thanks to [translation team](https://translate.wordpress.org/locale/fr-be/default/wp-plugins/live-weather-station))
* French / Canada (thanks to [translation team](https://translate.wordpress.org/locale/fr-ca/default/wp-plugins/live-weather-station))
* French / France (thanks to [translation team](https://translate.wordpress.org/locale/fr/default/wp-plugins/live-weather-station))

= Instructions =
You can find a more in-depth description and instructions to configure [on this page](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/).

= Support =
This plugin is free and provided without warranty of any kind. Use it at your own risk, I'm not responsible for any improper use of this plugin, nor for any damage it might cause to your site. Always backup all your data before installing a new plugin.

Anyway, I'll be glad to help you if you encounter issues when using this plugin. Just use the support section of this plugin page.

= Donation =
If you like this plugin or find it useful and want to thank me for the work done, please consider making a donation to [La Quadrature Du Net](https://www.laquadrature.net/en) which is an advocacy group defending the rights and freedoms of citizens on the Internet. By supporting them, you help the daily actions they perform to defend our fundamental freedoms!

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

You need **WordPress 4.X** and at least **PHP 5.4** (with internationalisation extension).

= Can this plugin work on multisite? =

Yes. You can install it via the network admin plugins page but the plugin **must not be "Network Activated"**, instead you must activate it on a site by site basis.

= Where can I get support? =

Support is provided via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

= Where can I find documentation? =

You can find instructions in english [here](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/). Vous trouverez des instructions en français [ici](https://pierre.lannoy.fr/wordpress-live-weather-station-pour-netatmo/).

= Where can I report a bug? =

You can report bugs and suggest ideas via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

== Changelog ==

= 2.9.3 =

Release date: August 4th, 2016

* Improvement: support for Wordpress 4.6.
* Improvement: `readme.txt`.

= 2.9.2 =

Release date: June 22nd, 2016

* Improvement: `readme.txt`.

= 2.9.1 =

Release date: June 22nd, 2016

* New language: Dutch (thanks to [@hanstis](https://profiles.wordpress.org/hanstis)).
* Improvement: `readme.txt`.
* Bug fix: wrong displaying of noise, O₃, CO and CO₂ in LCD Panel.

= 2.9.0 =

Release date: June 19th, 2016

* Added: Live Weather Station can now collects max wind strength and angle for today (from Netatmo stations) and display it in textual shortcodes, LCD Panel, clean gauges and steel meter.
* New language: English / South Africa.
* Improvement: the number of decimals for wind strength are now adapted to the true strength (on a suggestion from [seasparrow](https://wordpress.org/support/profile/seasparrow)).
* Improvement: better display for wind and units in LCD Panel.
* Improvement: added compatibility to *WN (local weather networks) thanks to Patrice Corre from FRWN.
* Improvement: some events have new severity for better visibility.
* Improvement: event log is now more precise whith Netatmo connection error codes and messages.
* Improvement: 4th phase of code refactoring for provisioning 3.X version.
* Improvement: `readme.txt`.
* Bug fix: in some cases, unit name in plain text, for wind and rain, is not correctly rendered.
* Bug fix: some generated files are not correctly refreshed when using Varnish as cache.

= 2.8.1 =

Release date: June 8th, 2016

* Improvement: systems, services and stations are now alphabeticaly sorted in the events log filter.
* Improvement: 3rd phase of code refactoring for provisioning 3.X version.
* Improvement: `readme.txt`.
* Bug fix: url rewriting doesn't cover all DB_* files cases.

= 2.8.0 =

Release date: June 6th, 2016

* Added: new events log for all operations and cron jobs.
* New language: English / Canada.
* Improvement: new way of error handling for some processes.
* Improvement: more visible warnings when internationalization support is not installed.
* Improvement: better handling of connection/disconnection for Netatmo and OpenWeatherMap account.
* Improvement: 2nd phase of code refactoring for provisioning 3.X version.
* Improvement: `readme.txt`.
* Bug fix: connectivity test for PWS Weather services may fails in some cases.
* Bug fix: remaining or elapsed approximate time maybe wrong under one minute.
* Bug fix: titles of columns are not translatable in stations views.

= 2.7.4 =

Release date: May 24th, 2016

* New language: English / UK.
* Improvement: `readme.txt`.
* Improvement: modification of API handling for better performances in cron jobs.
* Improvement: code refactoring for provisioning 3.X version.
* Bug fix: PHP warning when saving empty associated services for a Netatmo station.
* Bug fix: some typos in french translations (be, ca and fr).

= 2.7.3 =

Release date: May 16th, 2016

* Improvement: `readme.txt`.

= 2.7.2 =

Release date: May 16th, 2016

* New language: French / Belgium.
* New language: French / Canada.
* Improvement: French translation now complies with french typography rules and main wordpress-fr recommendations.
* Improvement: better javascript files refreshing after updating the plugin.
* Improvement: `readme.txt`.

= 2.7.1 =

Release date: May 10th, 2016

* Improvement: `readme.txt`.
* Bug fix: some javascript files are incorrectly minified.

= 2.7.0 =

Release date: May 9th, 2016

* Added: Live Weather Station can now collects polution data for ozone (O₃) and carbon monoxide (CO) and display it in textual shortcodes, LCD Panel, clean gauges and steel meter. It is an experimental feature, [see notes](https://wordpress.org/support/topic/new-pollution-data-types-units-and-limitations).
* Improvement: new "bullet-proof" mode for cron jobs.
* Improvement: steel meters have now adaptative decimal numbers for labels.
* Improvement: LCD panel now displays the correct number of decimal digits depending on the measurement type.
* Improvement: clean gauge and steel meters now supports values smaller than 1.
* Improvement: better min/max values handling in clean gauges.
* Improvement: sample urls for related services redirects now to Netatmo stations.
* Improvement: `readme.txt`.
* Bug fix: some MySQL warnings while activating the plugin.
* Bug fix: in some circumstances, cron jobs are disappearing (and data updates are not done) - many thanks to [Atle](https://wordpress.org/support/profile/atlehogberg) to have pointed out this annoying issue.

= 2.6.0 =

Release date: April 24th, 2016

* Added: Live Weather Station can now send outdoor Netatmo data to [Weather Underground](https://www.wunderground.com/) service.
* New option for rain and snow displaying: metric or imperial system.
* Improvement: textual shortcodes can now display units (symbol/abbrev. or full unit name) for measurement types.
* Improvement: unit for snow in metric system is now automatic (millimeters or centimeters).
* Improvement: `readme.txt`.
* Bug fix: LCD panel doesn't shows the right unit for snowfall in some cases.
* Bug fix: impossible to disable data sending to Met Office or PWS Weather when station is correctly synchronized.
* Bug fix: some typos in french translation.

= 2.5.0 =

Release date: April 13th, 2016

* Added: Live Weather Station can now send outdoor Netatmo data to [Met Office](http://wow.metoffice.gov.uk/) and [PWS Weather](http://www.pwsweather.com/) services.
* New option for wind direction icons: it's now possible to choose the semantics of the icons (towards or from).
* Improvement: steel meters can now shows accumulated rainfall for last hour (Netatmo).
* Improvement: support for Wordpress 4.5.
* Improvement: `readme.txt`.
* Bug fix: in some circumstances, Live Weather Station are not able to obtain correct geolocation of Netatmo stations.
* Bug fix: database and PHP warnings when updating plugin in some cases.
* Bug fix: PHP warning when in Netatmo & OWM mode and there is no specified OWM station.

= 2.4.1 =

Release date: March 14th, 2016

* Improvement: an error window now shows the reason when it's not possible to generate a shortcode (mostly for OpenWeatherMap errors).
* Improvement: `readme.txt`.
* Bug fix: unable to generate shortcodes when OpenWeatherMap servers sends empty responses.
* Bug fix: unable to connect a Netatmo account with a password containing quotes, double-quotes or backslashes.
* Bug fix: station names containing html special characters, quotes, double-quotes or backslashes are not correctly displayed in widgets and admin panel.


= 2.4.0 =

Release date: March 8th, 2016

* New language: English / New Zealand.
* Improvement: Netatmo servers are now queried every five minutes (it was every ten minutes in previous versions).
* Improvement: `readme.txt`.
* Bug fix: a string of the "about" box is not translatable.
* Bug fix: name collision for Color Class with other unknown plugin.

= 2.3.0 =

Release date: March 2nd, 2016

* New language: English / Australia.
* Improvement: all live controls and widgets now makes distinction between (and shows correct units and labels for) rain rates and rainfalls.
* Improvement: steel meters can now shows accumulated rainfall for today (Netatmo) and last 3 hours (OpenWeatherMap).
* Improvement: Live Weather Station now supports empty responses sent sometimes by OpenWeatherMap servers.
* Improvement: Live Weather Station now supports erroneous data sent sometimes by Netatmo servers.
* Improvement: a warning is now displayed in the site dashboard when php internationalisation extension is not installed (it is a requirement to run Live Weather Station, see [FAQ](https://wordpress.org/plugins/live-weather-station/faq/)).
* Improvement: `readme.txt`.
* Bug fix: rainfalls and snowfalls are not correctly shown in LCD panel when their values are null.

= 2.2.2 =

Release date: February 17th, 2016

* Improvement: `readme.txt`.
* Bug fix: live controls are no correctly displayed when `wp-admin` directory is not at the root of the site.

= 2.2.1 =

Release date: February 11th, 2016

* Improvement: `readme.txt`.
* Bug fix: error in some steel meters while displaying obsolete data.
* Bug fix: LCD font is not correctly displayed (in steel meters) when current value is out of range.
* Bug fix: some typos in carbon dioxide strings.

= 2.2.0 =

Release date: January 25th, 2016

* Added: new steel meters, with tons of customization parameters, to display "live" values.
* Improvement: using of subscript character for carbon dioxide, now displayed as CO₂ (previously, it was CO2).
* Improvement: in clean gauges it's now possible to choose to display the shortened measurement type as title or label.
* Improvement: `readme.txt`.

= 2.1.0 =

Release date: January 13th, 2016

* Added: new clean gauges to display "live" values.
* New option for outdoor weather widget: you can now choose to hide obsolete data.
* Improvement: pressure displayed in inHg have now reasonable accuracy with 2 decimals.
* Improvement: the data collecting method for Netatmo & OpenWeatherMap is now "bullet-proof".
* Improvement: an improved connection mode that should avoid unwanted disconnections (on Netatmo servers temporary errors).
* Improvement: refresh cycle for live controls goes from 5 minutes to 2 minutes - datas are fresher than ever ;).
* Improvement: the "about box" is now much more readable.
* Improvement: `readme.txt`.
* Bug fix: some typos in french translation.

= 2.0.1 =

Release date: December 22nd, 2015

* Improvement: `readme.txt`.
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
* Improvement: in textual shortcodes, timestamps can now be displayed in remaining or elapsed approximate time.
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

= 2.9.X =
New wind values collected from Netatmo stations and one new language. Many optimizations, improvements and bug fixes.

= 2.8.X =
New events log and new languages. Many optimizations, improvements and bug fixes.

= 2.7.X =
New pollution data collection from OpenWeatherMap. New cron jobs mode. Many optimizations, improvements and bug fixes.

= 2.6.X =
Added the possibility to send outdoor data from Netatmo stations to Weather Underground. Many optimizations and improvements.

= 2.5.X =
Added the possibility to send outdoor data from Netatmo stations to some online weather services. Many optimizations and improvements.

= 2.4.X =
New frequency for updating Netatmo values. Many optimizations and improvements.

= 2.3.X =
Many optimizations and improvements.

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