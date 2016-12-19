=== Weather Station ===
Contributors: PierreLannoy
Tags: wordpress, widget, weather, shortcode, openweathermap, netatmo, meteo, live, lcd, gauge, ephemeris, forecast, current weather, forecast widget, local weather, weather forecasts, weather widget, conditions, current conditions, weather by city, temperature, wind speed, wind, wind strength, pressure, humidity, CO2, rain, snow, cloudiness, cloud, moon, moon phase, sun, sunrise, sunset, moonrise, moonset, noise, weather station, dew, frost, humidex, heat index, wind chill, weather plugin, wordpress widget, wind gauge, rain gauge, pws, met office, personal weather station, weather underground, wunderground, weather observations website, wow, observation, pollution, CO₂, CO, O3, O₃, ozone, carbon dioxide, carbon monoxide, clientraw, clientraw.txt, realtime, realtime.txt
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 3.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://support.laquadrature.net/

Display on your WordPress site, in many elegant ways, the meteorological data collected by public or personal weather stations.

== Description ==
Weather Station is a plugin that allows you to display, on your WordPress site, meteorological data from weather stations you have access to. It provides full support for many models of weather stations and for free or paid OpenWeatherMap and Weather Underground services&hellip;
Whether you own a weather station or not, you can enjoy the power of Weather Station!

To see it in action, go to the [demo page](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo-demo/).

= Simple and efficient =
The use of Weather Station requires no knowledge in programming and does not requires writing code.
Just set it, insert (in a page or an article) the provided shortcodes. And it works!

= How does it work? =
Once you have connected the plugin to your weather stations (via the dashboard of your WordPress site), the data you have access to are collected every 5 or 10 minutes and stored in the database of your WordPress site.
The various controls and viewers now will get their data from this database with the certainty of having fresh and cached data.
To date, you can display data with the following controls:

* a "classical" configurable widget that displays outdoor weather data
* an "ephemeris" configurable widget that displays astronomical computed values
* a highly configurable LCD panel which displays selected weather data
* a highly configurable clean gauge control which displays the live weather data you have selected
* textual and numerical values you can insert in articles and pages via configurable shortcodes
* a hugely configurable steel meter control which displays the live weather data you have selected

= Supported devices & services =
Weather Station supports:

* the Netatmo station (all modules)
* all stations supported by softwares like Cumulus, Weather Display, WeeWX, etc. (so, yes, stations from Davis, La Crosse, Oregon Scientific, RainWise, etc. are supported)
* all stations published on Weather Underground (regardless which model it is)
* all geolocation from OpenWeatherMap

If you want, Weather Station can send outdoor data, at a 10 minutes frequency, to the following services:

* [Met Office](http://wow.metoffice.gov.uk/) weather observations website
* [PWS Weather](http://www.pwsweather.com/)
* [Weather Underground](https://www.wunderground.com/)

= Instructions =
You can find a more in-depth description and instructions to configure [on this page](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/).

= Support =
This plugin is free and provided without warranty of any kind. Use it at your own risk, I'm not responsible for any improper use of this plugin, nor for any damage it might cause to your site. Always backup all your data before installing a new plugin.

Anyway, I'll be glad to help you if you encounter issues when using this plugin. Just use the support section of this plugin page.

= Donation =
If you like this plugin or find it useful and want to thank me for the work done, please consider making a donation to [La Quadrature Du Net](https://www.laquadrature.net/en) which is an advocacy group defending the rights and freedoms of citizens on the Internet. By supporting them, you help the daily actions they perform to defend our fundamental freedoms!

> **DISCLAIMER:** *This plugin is developed and maintained by me, [Pierre Lannoy](https://pierre.lannoy.fr "Pierre Lannoy"). This plugin **IS NOT** an official software from [Netatmo](http://www.netatmo.com "Netatmo Homepage"), [OpenWeatherMap](http://openweathermap.org/) or [Weather Underground](https://www.wunderground.com/) and is not endorsed or supported by these companies. Moreover, I am not a partner, employee, affiliate, or licensee of Netatmo, OpenWeatherMap or Weather Underground. I'm just a happy customer/user of their products and a fan of meteorology.*


== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'.
2. Search for 'Weather Station'.
3. Click on the 'Install Now' button.
4. Activate Weather Station.

= From WordPress.org =

1. Download Weather Station.
2. Upload the `live-weather-station` directory to your `/wp-content/plugins/` directory, using your favorite method (ftp, sftp, scp, etc...).
3. Activate Weather Station from your Plugins page.

= Once Activated =

1. Visit 'Weather Station' in the left-hand menu of your WP Admin to adjust settings.
2. Enjoy!

== Frequently Asked Questions ==

= What are the requirements for this plugin to work? =

You need **WordPress 4.X** and at least **PHP 5.4** with internationalisation, curl and json extensions.

= Can this plugin work on multisite? =

Yes. You can install it via the network admin plugins page but the plugin **must not be "Network Activated"**, instead you must activate it on a site by site basis.

= Where can I get support? =

Support is provided via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

= Where can I find documentation? =

You can find instructions in english [here](https://pierre.lannoy.fr/wordpress-live-weather-station-for-netatmo/). Vous trouverez des instructions en français [ici](https://pierre.lannoy.fr/wordpress-live-weather-station-pour-netatmo/).

= Where can I report a bug? =

You can report bugs and suggest ideas via the [support section](https://wordpress.org/support/plugin/live-weather-station) of this plugin page.

== Changelog ==

= 3.0.6 / December 19th, 2016 =

* Improvement: optimized "Purge & Resynchronize" feature.
* Improvement: better database migration from previous versions.
* Improvement: adding/modifying links in inline help.
* Improvement: more precise error messages when unable to add a Weather Underground station.
* Improvement: optimized layout of shortcodes windows.
* Improvement: internal translations updates are now done once per day.
* Improvement: `readme.txt` cleaning.
* Bug fix: activating stickertags publishing need page refreshing to be shown.
* Bug fix: there are some typos in shortcodes windows titles.

= 3.0.5 / November 21st, 2016 =

* Improvement: better latitude & longitude interpretation for clientraw.txt files.
* Bug fix: bad file format error is detected for some clientraw.txt files generated by WeeWX (thanks to Marco Sartini to find this bug).

= 3.0.4 / November 18th, 2016 =

* Bug fix: in some rare cases, the gauge boundaries for wind measurements are not correctly saved.

= 3.0.3 / November 18th, 2016 =

* Improvement: min/max values and alarms are now displayed with consistent accuracy.
* Improvement: better detection and process for migration from previous 2.x versions.
* Bug fix: the gauge boundaries for some wind measurements are wrong.

= 3.0.2 / November 17th, 2016 =

* Improvement: extreme conditions for pressure are more accurate when expressed in inHg and mmHg.
* Improvement: caching mechanism on slow queries.
* Improvement: error handling when generating shortcodes for missing data.
* Bug fix: the gauge boundaries for pressure are wrong with units in inHg and mmHg.
* Bug fix: the gauge boundaries for cloudiness are wrong.

= 3.0.1 / November 15th, 2016 =

* Bug fix: some typos in dir names.

= 3.0.0 / November 14th, 2016 =

This new release is a major update of the plugin which is now named **Weather Station**.
After upgrading, please, review all your settings.

* New: totally redesigned plugin admin pages to provide a simpler and easier access to all features and settings.
* New: a full featured dashboard (like the WordPress one) provides a simplified way to access to all features and operating analytics of the plugin.
* New: a new "station view" offers more in-depth visibility and understanding of the collected weather stations.
* New: in addition to its nominal mode, Weather Station can now run in a simplified mode (automatic selection of settings, display options, units, etc.).
* New: it's now possible to collect data from stations published on Weather Underground.
* New: it's now possible to collect data from stations publishing their data via realtime.txt file (Cumulus, etc.).
* New: it's now possible to collect data from stations publishing their data via clientraw.txt file (Weather Display, WeeWX, etc.).
* New: the min & max boundaries for controls (like meters or gauges) can be set for each measurement types.
* New: the min & max alarms thresholds for controls (like LCD panel or meters) can be set for each measurement types.
* New: a widget in the WordPress dashboard now display an "operating summary" of the plugin.
* New: it's now possible to manually purge and resynchronize data.
* New: humidity min & max collecting for station supporting it. Output rendering in all shortcodes.
* New: pressure min & max collecting for station supporting it. Output rendering in all shortcodes.
* New: yesterday, month, season and year aggregated rain collecting for station supporting it. Output rendering in all shortcodes.
* New: for Netatmo stations first setup, last setup and last upgrade dates are collected and may be rendered like other measurements.
* New: for all personal stations last seen and last refresh dates are collected and may be rendered like other measurements.
* New: new alternate icons set to display moon ephemeris values.
* New: ability to publish outdoor data of personal stations as stickertags format (for local, national or transnational weather networks like FRWN, etc.).
* New: a caching mechanism is implemented to accelerate all backend rendering.
* Improvement: stability and speed are dramatically optimized.
* Improvement: it's now possible to use partial translations.
* Improvement: Weather Station has a new stylized (and stylish) logo!
* Improvement: Weather Station has now its own menu in the main admin sidebar.
* Improvement: all UI have been redesigned for easy use (icons, information messages, confirmation prompts, etc.).
* Improvement: each admin page have now a contextual help.
* Improvement: the settings page now adheres to the "standard" WordPress admin look & feel.
* Improvement: external and auxiliary links can now be opened in same or new window (settings in 'system' tab)'.
* Improvement: all mapping features are now provided by OpenStreetMap.
* Improvement: events log and logging policy are now configurable (settings in 'system' tab)'.
* Improvement: short codes previews are now based on true values (before it was on estimated values).
* Improvement: namespaces refactored for avoiding name collision with other plugins.
* Improvement: Weather Station now use the latest version of Netatmo SDK.
* Improvement: pressure displayed in hPa have now reasonable accuracy with 1 decimal.
* Improvement: better cURL error reporting (now in event log) when SSL issues occurs.
* Improvement: it's now possible to be warned when time difference between Netatmo server and your server is too large (settings 'Server Time shift' in 'system' tab)'.
* Improvement: the plugin now verify all prerequisites (mandatory extensions) at startup and shows warning if there's something missing.
* Improvement: connection status, dashboard and stations provides direct links to filtered events log.
* Improvement: it's now possible to manage individually each Netatmo station (i.e. add or remove station to the mechanism of collect).
* Improvement: huge source code refactoring and commenting for better readability.
* Improvement: all forms now implements security best practices (nonces, validations, etc.).
* Improvement: better support for anti directory listing.
* Improvement: event log now shows events numbers and supports direct access to detailed description.
* Improvement: battery and signal levels for Netatmo stations are more accurate.
* Improvement: optimized country / time zones list for Deutchland, United-Kingdom, Russia, Serbia, Vietnam and Yemen.
* Improvement: Weather Station now use the latest version of Font Awesome (4.7).
* Bug fix: wind strength rounding in km/h are not accurate.
* Bug fix: current weather from OpenWeatherMap is wrongly registered in the events log.
* Bug fix: filtering events log by station doesn't work.
* Bug fix: unable to validate site ID and AWS 6-digit PIN for sending data to MET Office.
* Bug fix: some warning events are not logged.

== Upgrade Notice ==

= 3.0.X =
New major release with tones of new features, optimization and options. Live Weather Station become Weather Station!

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