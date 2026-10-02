=== Weather Station ===
Contributors: jaz_on
Tags: weather, openweathermap, netatmo, weatherflow, weatherstation
Requires at least: 4.9
Tested up to: 7.1
Requires PHP: 7.1
Stable tag: 3.9.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://buymeacoffee.com/jasonrouet

Display on your WordPress site, in many elegant ways, the meteorological data collected by public or personal weather stations.

== Description ==
Weather Station is a plugin that allows you to display, on your WordPress site, meteorological data from weather stations you have access to. It provides full support for [many models of weather stations and for free or paid services](https://weather.station.software/handbook/technical-specifications/)&hellip;
Whether you own a weather station or not, you can enjoy the power of Weather Station!

You can find many demos and documentation on the [official website](https://weather.station.software/).

= Simple and efficient =
The use of Weather Station requires no knowledge of programming and does not require writing code.
Just set it and insert (in a page or an article) the provided shortcodes. And it works!

= How does it work? =
Once you have connected the plugin to your weather stations (via the dashboard of your WordPress site), the data you have access to is collected regularly and stored in the database of your WordPress site.
The various controls and viewers now will get their data from this database with the certainty of having fresh and cached data.

To see all available widgets, controls and viewers, please take a look at the [live demo](https://weather.station.software/weather-station-in-action/).


= Supported devices & services =
Weather Station supports:

* the BloomSky stations (Sky1, Sky2 & Storm)
* the Netatmo station (all modules)
* the Netatmo *Healthy Home Coach*
* the Pioupiou wind stations (V1 & V2)
* the WeatherFlow station (Sky & Air modules)
* all stations published on Ambient Weather Network
* all stations published on WeatherLink Network
* all stations supported by software like Cumulus, Weather Display, WeeWX, etc. (so, yes, stations from Davis, La Crosse, Oregon Scientific, RainWise, etc. are supported)
* all stations supported by software like WeatherLink, WsWin32, MeteoBridge (with stickertags export)
* all geolocation from OpenWeatherMap

If you want, Weather Station can send outdoor data to the following services:

* [Met Office](https://wow.metoffice.gov.uk/) weather observations website
* [PWS Weather](https://www.pwsweather.com/)

= Instructions =
You can find a more in-depth description and instructions to configure [in the handbook](https://weather.station.software/handbook/).

= Support =
This plugin is free and provided without warranty of any kind. Use it at your own risk, I'm not responsible for any improper use of this plugin, nor for any damage it might cause to your site. Always backup all your data before installing a new plugin.
- Community support [via the support forums on wordpress.org](https://wordpress.org/support/plugin/live-weather-station/)

= Contribution =
- Active development of this plugin [is handled on GitHub](https://github.com/Weather-Station-Software/live-weather-station). Pull requests are highly appreciated!
- If you want to help us translate "Weather Station" into your language, [you can do so on WordPress Translate.](https://translate.wordpress.org/projects/wp-plugins/live-weather-station/)

= Credits =
- Actual maintainer (since v3.8.12): [Jason Rouet](https://profiles.wordpress.org/jaz_on/)
- Original author: [Pierre Lannoy](https://profiles.wordpress.org/pierrelannoy/) (see props.txt for more details)

= Support the Development =
Weather Station is completely free and open source, but your support helps maintain and improve it!

Your sponsorship enables:
- Security updates and WordPress compatibility
- Purchase or upgrade of weather station hardware for testing (the maintainer does not own devices for every brand/model)
- Testing across a wide range of station models and services
- New features and continuous improvements
- Better documentation and user support
- Optional external contributors for specialized tasks

- Buy Me a Coffee: https://buymeacoffee.com/jasonrouet

You can also help by reporting bugs, translating, or sharing the plugin with others.


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

You need **WordPress 4.9** and at least **PHP 7.1**. See full [requirements](https://weather.station.software/handbook/requirements/).

PHP 8.2 and later are supported and tested with every release. Older versions of PHP may still work but are not supported: please ask your host to upgrade.

= Can this plugin work on multisite? =

Yes. You can install it via the network admin plugins page, then either network activate it or activate it on a site by site basis. When network activated, the plugin is set up on every site, including the ones created afterwards.

= Where can I get support? =

Support is provided via [the plugin's official support forum here on wordpress.org](https://wordpress.org/support/plugin/live-weather-station/).

= Where can I find documentation? =

You can find instructions [here](https://weather.station.software/handbook/).

= Where can I report a bug? =
 
You can report bugs and suggest ideas [via the Github repository](https://github.com/Weather-Station-Software/live-weather-station/issues).

= Where do I report security bugs found in this plugin? =
Please report security bugs found in the source code of the Weather Station plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/wordpress/plugin/live-weather-station).
The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.
Alternatively, you can also [contact me directly by email](mailto:weather@station.network).

= Where are the credentials of the weather services stored? =
The passwords and access tokens that the plugin needs to talk to the services (for example the Netatmo tokens, or the identifiers used to share your data with Weather Underground, PWS Weather or WOW) are stored in the database of your site, in clear, like the secrets stored by WordPress itself and by most plugins. The configuration export only contains them if you tick the option to include the credentials.
Protect your database and its backups like you protect your site. When a service gives you a key dedicated to your station or to an application, use it instead of the password of your main account: if it leaks, you can replace it without changing your account password.

= Is the API key of my maps visible on my site? =
Yes. The maps are drawn by the browser of your visitors, which downloads the tiles directly from the map service (Mapbox, Maptiler, Thunderforest, OpenWeatherMap, Windy, Stadia) with your key in the address, so anyone can read it in the code of the page. It is the same for every map plugin. Use a key restricted to your domain name, as these services let you do in their dashboard: the key then only works on your site.

= How do I connect Netatmo to my site? =
Netatmo no longer accepts a login with a password from a plugin, so each site uses its own Netatmo application. Create one on [dev.netatmo.com/apps](https://dev.netatmo.com/apps) (free, with your Netatmo account), then in Weather Station, Settings, Services, Netatmo:
1. Copy the "Redirect URI" shown by the plugin and paste it in the settings of your Netatmo application.
2. Paste the Client id and the Client secret of your application in the plugin.
3. Click "Connect with Netatmo", sign in on the Netatmo page and accept: you come back to your site, connected.

If you prefer, you can paste a refresh token of your application instead (second button). The connections made with previous versions keep working: you only need the steps above for a new connection or a reconnection.

= What is the admin analytics shortcode? =
The Analytics screens of the plugin (API quotas, events, cache, database, scheduled tasks) are drawn by the shortcode `[live-weather-station-admin-analytics item="quota" metric="service_short"]` (and its variants). It shows internal statistics of your site, so it only displays something to an administrator: for any other visitor it returns nothing. You can put it in a page to see these charts on the front of your site, but do not publish it on a page that is served to visitors through a full-page cache (cache plugin, host cache, CDN page cache): if the page is cached while an administrator is logged in, the charts would be kept in the cache and shown to everyone. Exclude that page from the cache, or keep these statistics in the Analytics screens of the dashboard.

= Are the exports of the plugin protected? =
The exports (configuration, data) are stored in the folder `wp-content/uploads/live-weather-station/`. Their names contain a random identifier, the plugin never shows their address, and an administrator downloads them through a protected link. In addition the plugin protects the folder for Apache (`.htaccess`) and IIS (`web.config`). nginx ignores these files: if your site runs on nginx, add this to its configuration and reload it:

`location ^~ /wp-content/uploads/live-weather-station/ { deny all; }`

The Site Health screen of WordPress (Tools, Site Health) tells you if the folder can be read from the web.

= Can a station be on my local network? =
Yes. The stations which are read from a file or a feed (Clientraw, Realtime, Stickertags) can be at an address of your local network (192.168.x.x, 10.x.x.x, a name of your LAN), because many personal stations live there. For this reason the plugin does not block private addresses when it reads a station: only an administrator can add a station, so only enter addresses you trust.

= My site is behind a CDN or a reverse proxy: do the request limits still work? =
The plugin limits the number of requests per minute that a visitor can make to its live controls, charts and feeds (see "Public requests limit" in the system settings, 0 removes a limit). A visitor is identified by the address the server sees. Behind a CDN or a reverse proxy (Cloudflare, a load balancer...) this address is the one of the proxy, so all your visitors would share the same counter. In that case give the plugin the real address of the visitor, with a few lines in a small plugin or in the functions.php of your child theme. Example for Cloudflare:

`add_filter( 'live_weather_station_public_rate_limit_ip', function ( $ip ) { return isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $ip; } );`

Only use a header that your proxy sets itself and that visitors cannot forge: the plugin never trusts forwarded headers by default. The other filters are `live_weather_station_public_rate_limit` (the limit, 0 disables it) and `live_weather_station_public_rate_window` (the length of the window in seconds, 60 by default).

= Are there some paid services or limitations? =
NO. Weather Station is a free software. That means you (the users) have the freedom to run, copy, distribute, study, change and improve the software.
Although it is not free of charge for its maintainer, I'd rather have your help to improve the plugin's code than receive money to pay for my coffee or beers. 🫶

== Changelog ==

See [full changelog](https://weather.station.software/handbook/changelog/).

== Upgrade Notice ==

= 3.9.0 =
PHP 8.2 and later are supported and tested. Versions of PHP older than 8.2 are not supported.

Please, see [full changelog](https://weather.station.software/handbook/changelog/) and [requirements](https://weather.station.software/handbook/requirements/).

== Screenshots ==

To see Weather Station in action, please take a look at the [live demo](https://weather.station.software/weather-station-in-action/).
