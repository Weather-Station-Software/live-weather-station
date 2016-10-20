<?php

namespace WeatherStation\System\Help;

use WeatherStation\System\Logs\Logger;
use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\DB\Query;

/**
 * This class add inline help links to the plugin.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class InlineHelp {

    use Query;

    private $Live_Weather_Station;
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $Live_Weather_Station The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since 3.0.0
     */
    public function __construct($Live_Weather_Station, $version) {
        $this->Live_Weather_Station = $Live_Weather_Station;
        $this->version = $version;
    }

    /**
     * Get string for this help number.
     *
     * @param integer $number The help number.
     * @param string $message The string of the help containing %s.
     * @param string $anchor The anchor tag.
     * @return string The complete help string.
     *
     * @since    3.0.0
     */
    public static function get($number, $message='%s', $anchor='') {
        $result = '';
        $path = '';
        //todo: get correct language id
        $lang = 'en';
        $target = '';
        if ((bool)get_option('live_weather_station_redirect_external_links')) {
            $target = ' target="_blank" ';
        }
        switch ($number) {
            case 0: $path = '---'; break; //source: settings - general tab
            case 1: $path = '---'; break; //source: requirements + phpinfo page
            case 2: $path = '---'; break; //source: settings - service tab
            case 3: $path = '---'; break; //source: settings - display tab
            case 4: $path = '---'; break; //source: settings - thresholds tab
            case 5: $path = '---'; break; //source: settings - system tab
            case 6: $path = '---'; break; //settings section
            case 7: $path = '---'; break; //faq section
            case 8: $path = '---'; break; //dashboard
            case 9: $path = '---'; break; //stations
            case 10: $path = '---'; break; //events
            case 11: $path = '---'; break; //requirements
            case 12: $path = '---'; break; //translation help
            case 13: $path = '---'; break; //Blog
            case 14: $path = '---'; break; //Starting guide
            case 15: $path = '---'; break; //source: settings - maintenance tab
            case 16: $path = '---'; break; //stickertags documentation
            case 17: $path = '---'; break; //main documentation
        }
        if ($path != '') {
            $result = sprintf($message, '<a href="https://weather.station.software/' . $lang . '/' . $path . '"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -1) {
            $result = sprintf($message, '<a href="https://weather.station.software/' . $lang . '/' . '"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -2) {
            $result = sprintf($message, '<a href="https://wordpress.org/support/plugin/live-weather-station"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -3) {
            $result = sprintf($message, '<a href="http://openweathermap.org/price"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -4) {
            $result = 'https://pierre.lannoy.fr/feed/';
        }
        if ($number == -5) {
            $result = sprintf($message, '<a href="https://wordpress.org/support/plugin/live-weather-station/reviews/"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -6) {
            $result = '<a href="https://twitter.com/cyril_lakech"' . $target . '>Cyril Lakech</a>';
        }
        if ($number == -7) {
            $result = '<a href="http://www.punz.info/"' . $target . '>Martin Punz</a>';
        }
        if ($number == -8) {
            $result = '<a href="http://www.meteo-daoulas.fr/"' . $target . '>Patrice Corre</a>';
        }
        if ($number == -9) {
            $result = '<a href="http://creativecommons.org/licenses/by-sa/4.0/"' . $target . '>Creative Commons CC:BY-SA 4.0 license</a>';
        }
        if ($number == -10) {
            $result = sprintf($message, '<a href="https://translate.wordpress.org/projects/wp-plugins/live-weather-station"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -11) {
            $result = sprintf($message, '<a href="https://www.wunderground.com/weather/api/d/pricing.html?apiref=d97bd03904cd49c5"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -12) {
            $result = sprintf($message, '<a href="https://register.metoffice.gov.uk/WaveRegistrationClient/public/register.do?service=weatherobservations"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -13) {
            $result = sprintf($message, '<a href="http://wow.metoffice.gov.uk/sites/create"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -14) {
            $result = sprintf($message, '<a href="http://wow.metoffice.gov.uk/weather/view?siteID=966476001"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -15) {
            $result = sprintf($message, '<a href="http://www.pwsweather.com/register.php"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -16) {
            $result = sprintf($message, '<a href="http://www.pwsweather.com/station.php"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -17) {
            $result = sprintf($message, '<a href="http://www.pwsweather.com/obs/MOUVAUX.html"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -18) {
            $result = sprintf($message, '<a href="https://www.wunderground.com/personal-weather-station/signup"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -19) {
            $result = sprintf($message, '<a href="https://www.wunderground.com/personal-weather-station/signup?new=1"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -20) {
            $result = sprintf($message, '<a href="https://www.wunderground.com/personal-weather-station/dashboard?ID=INORDPAS92&apiref=d97bd03904cd49c5"' . $target . '>' . $anchor . '</a>');
        }

        if ($number == -21) {
            $result = sprintf($message, '<a href="https://www.wunderground.com/member/registration?apiref=d97bd03904cd49c5"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -22) {
            $result = sprintf($message, '<a href="https://www.wunderground.com/weather/api/d/pricing.html?apiref=d97bd03904cd49c5"' . $target . '>' . $anchor . '</a>');
        }

        if ($number == -23) {
            $result = sprintf($message, '<a href="https://home.openweathermap.org/users/sign_up"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -24) {
            $result = sprintf($message, '<a href="https://home.openweathermap.org/api_keys"' . $target . '>' . $anchor . '</a>');
        }

        if (LWS_INLINE_HELP) {
            return $result;
        }
        else {
            if ($number < 0) {
                return $result;
            }
            else {
                return '';
            }
        }
    }

    /**
     * Set contextual help tab.
     *
     * @param string $loader Loader name.
     * @param string $type Help type.
     *
     * @since 3.0.0
     */
    public static function set_contextual_help($loader, $type) {
        if (LWS_INLINE_HELP) {
            add_action($loader, array('WeatherStation\System\Help\InlineHelp', 'set_contextual_' . $type));
        }
    }

    /**
     * Contextual help for "settings" panel.
     *
     * @return string
     * @since    3.0.0
     */
    public static function get_standard_help_sidebar() {
        return'<br/><p><strong>' . __('See also:', 'live-weather-station') . '</strong></p>' .
            '<p>' . self::get(17, '%s', __('Documentation', 'live-weather-station')) . '</p>'.
            '<p>' . self::get(7, '%s', __('FAQ', 'live-weather-station')) . '</p>'.
            '<p>' . self::get(-2, '%s', __('Support', 'live-weather-station')) . '</p>'.
            '<p>' . self::get(-1, '%s', __('Official website', 'live-weather-station')) . '</p>';
    }

    /**
     * Contextual help for "dashboard" panel.
     *
     * @see set_contextual_help()
     * @since    3.0.0
     */
    public static function set_contextual_dashboard() {
        $s = sprintf(__('Welcome to your %1$s Dashboard! This is the screen you will see when you click on %1$s icon in the WordPress left-hand navigation menu. You can get help for any %1$s screen by clicking the Help tab above the screen title.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $screen = get_current_screen();
        $tabs = array();
        $tabs[] = array(
                'title'    => __('Overview', 'live-weather-station'),
                'id'       => 'lws-contextual-dashboard',
                'content'  => '<p>' . $s . '</p>');

        $s1 = sprintf(__('You can use the following controls to arrange your %s Dashboard screen to suit your workflow:', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s2 = '<strong>' . __('Screen Options', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Use the Screen Options tab to choose which %s Dashboard boxes to show.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s3 = '<strong>' . __('Drag and Drop', 'live-weather-station') . '</strong> &mdash; ' . __('To rearrange the boxes, drag and drop by clicking on the title bar of the selected box and releasing when you see a gray dotted-line rectangle appear in the location you want to place the box.', 'live-weather-station');
        $s4 = '<strong>' . __('Box Controls', 'live-weather-station') . '</strong> &mdash; ' . __('Click the title bar of the box to expand or collapse it.', 'live-weather-station');
        $tabs[] = array(
                'title'    => __('Layout', 'live-weather-station'),
                'id'       => 'lws-contextual-dashboard-layout',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p>');

        $s1 = sprintf(__('The boxes on your %s Dashboard screen are:', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s2 = '<strong>' . __('At a Glance', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Displays a summary of %s operations. Note that a similar box is displayed in your main WordPress Dashboard.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s3 = '<strong>' . __('Versions', 'live-weather-station') . '</strong> &mdash; ' . __('Displays important versions numbers.', 'live-weather-station');
        $s4 = '<strong>' . __('Translation', 'live-weather-station') . '</strong> &mdash; ' . __('If displayed, shows translations status.', 'live-weather-station');
        $s5 = '<strong>' . __('About', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Displays information about %s and contributors.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s6 = '<strong>' . __('Licenses', 'live-weather-station') . '</strong> &mdash; ' . __('Displays important information about the licenses under which are published some weather data.', 'live-weather-station');
        $s7 = '<strong>' . __('Welcome', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Shows links for some of the most common tasks when getting started or using %s.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $tabs[] = array(
            'title'    => __('Content', 'live-weather-station'),
            'id'       => 'lws-contextual-dashboard-content',
            'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p><p>' . $s6 . '</p><p>' . $s7 . '</p>');

        foreach($tabs as $tab) {
            $screen->add_help_tab($tab);
        }
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
            '<p>' . self::get(8, '%s', __('Dashboard description', 'live-weather-station')) . '</p>'.
            self::get_standard_help_sidebar());
    }

    /**
     * Contextual help for "settings" panel.
     *
     * @see set_contextual_help()
     * @since    3.0.0
     */
    public static function set_contextual_settings() {
        $s = sprintf(__('This screen allows you to adjust all settings required to adapt the operation of %s to what you expect.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $screen = get_current_screen();
        $tabs = array();
        $tabs[] = array(
            'title'    => __('Overview', 'live-weather-station'),
            'id'       => 'lws-contextual-settings',
            'content'  => '<p>' . $s . '</p>');

        $s1 = __('The tabs on the settings screen are:', 'live-weather-station');
        $s2 = '<strong>' . __('General', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Allows you to switch the mode in which %s runs.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s3 = '<strong>' . __('Services', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('In order to work properly, %s has to be connected to some services. You can manage here these connections.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s4 = '<strong>' . __('Display', 'live-weather-station') . '</strong> &mdash; ' . __('You can set here all the units and display options for controls and widgets.', 'live-weather-station') . ' ' . sprintf(__('This tab is visible only if %s runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s5 = '<strong>' . __('Thresholds', 'live-weather-station') . '</strong> &mdash; ' . __('You can set here all the thresholds which define limits and alarms in some controls (LCD panel, gauges, meters, etc.).', 'live-weather-station') . ' ' . sprintf(__('This tab is visible only if %s runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s6 = '<strong>' . __('System', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('You can set here all the parameters related to the operation of the %s subsystems.', 'live-weather-station'), LWS_PLUGIN_NAME) . ' ' . sprintf(__('This tab is visible only if %s runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s7 = '<strong>' . __('Maintenance', 'live-weather-station') . '</strong> &mdash; ' . __('Here, you can make some maintenance operations that are not directly accessible elsewhere.', 'live-weather-station') . ' ' . sprintf(__('This tab is visible only if %s runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $tabs[] = array(
            'title'    => __('Content', 'live-weather-station'),
            'id'       => 'lws-contextual-settings-content',
            'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p><p>' . $s6 . '</p><p>' . $s7 . '</p>');

        $s1 = __('To obtain an API key from OpenWeatherMap please, follow these steps:', 'live-weather-station' );
        $s2 = self::get(-23, __('%s on the OpenWeatherMap website.', 'live-weather-station'), __('Create an account', 'live-weather-station'));
        //todo: verify following translation
        $s3 = self::get(-24, __('After registration, log in to %s.', 'live-weather-station'), __('create an get your API key', 'live-weather-station'));
        $s4 = __('Then, copy and paste your API key in the corresponding fields of the "OpenWeatherMap" box, set your plan and click on the "connect" button.', 'live-weather-station');
        $s5 = sprintf(__('Note: the <em>Free Plan</em> will allow you to add up to 10 OpenWeatherMap stations in %s.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $tabs[] = array(
            'title'    => 'OpenWeatherMap',
            'id'       => 'lws-contextual-station-settings-owm',
            'content'  => '<p>' . $s1 . '</p><ol><li>' . $s2 . '</li><li>' . $s3 . '</li><li>' . $s4 . '</li></ol><p>' . $s5 .'</p>');

        $s1 = __('To obtain an API key from Weather Underground please, follow these steps:', 'live-weather-station' );
        $s2 = self::get(-21, __('%s on the Weather Underground website.', 'live-weather-station'), __('Create an account', 'live-weather-station'));
        $s3 = self::get(-22, __('After registration, log in and %s after selecting your plan.', 'live-weather-station'), __('get your API key', 'live-weather-station'));
        $s4 = __('Then, copy and paste your API key in the corresponding fields of the "Weather Underground" box, set your plan and click on the "connect" button.', 'live-weather-station');
        $s5 = sprintf(__('Note: the <em>Stratus Developper Plan</em> - the free one - will allow you to add up to 3 Weather Underground stations in %s.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $tabs[] = array(
            'title'    => 'Weather Underground',
            'id'       => 'lws-contextual-station-settings-wug',
            'content'  => '<p>' . $s1 . '</p><ol><li>' . $s2 . '</li><li>' . $s3 . '</li><li>' . $s4 . '</li></ol><p>' . $s5 .'</p>');

        foreach($tabs as $tab) {
            $screen->add_help_tab($tab);
        }
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
            '<p>' . self::get(6, '%s', __('Settings management', 'live-weather-station')) . '</p>'.
            self::get_standard_help_sidebar());
    }

    /**
     * Contextual help for "stations" panel.
     *
     * @see set_contextual_help()
     * @since    3.0.0
     */
    public static function set_contextual_stations() {
        $action = null;
        $id = null;
        $type = -1;
        if (!($action = filter_input(INPUT_GET, 'action'))) {
            $action = filter_input(INPUT_POST, 'action');
        }
        if (!($id = filter_input(INPUT_GET, 'id'))) {
            $id = filter_input(INPUT_POST, 'id');
        }
        if ($id) {
            $station = self::get_station($id);
            $type = $station['guid'];
        }
        $tabs = array();
        if (isset($action) && $action='manage') {
            $s1 = __('This "station view" shows you the details of a station.', 'live-weather-station');
            $s2 = __('The left-hand column display statical information on the station as well as shortcodes or publishing format options.', 'live-weather-station');
            $s3 = __('The right-hand column displays all modules (main base, outdoor, indoor and virtual modules) attached to the station.', 'live-weather-station');
            $tabs[] = array(
                'title'    => __('Overview', 'live-weather-station'),
                'id'       => 'lws-contextual-station',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p>');
            $s1 = sprintf(__('You can use the following controls to arrange this station view to suit your workflow:', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = '<strong>' . __('Screen Options', 'live-weather-station') . '</strong> &mdash; ' . __('Use the Screen Options tab to choose which boxes and modules to show.', 'live-weather-station');
            $s3 = '<strong>' . __('Drag and Drop', 'live-weather-station') . '</strong> &mdash; ' . __('To rearrange the boxes, drag and drop by clicking on the title bar of the selected box and releasing when you see a gray dotted-line rectangle appear in the location you want to place the box.', 'live-weather-station');
            $s4 = '<strong>' . __('Box Controls', 'live-weather-station') . '</strong> &mdash; ' . __('Click the title bar of the box to expand or collapse it.', 'live-weather-station');
            $tabs[] = array(
                'title'    => __('Layout', 'live-weather-station'),
                'id'       => 'lws-contextual-station-layout',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p>');
            if ($type == 0 || $type > 3) {
                $s1 = sprintf(__('You can participate in the dissemination and sharing of data collected by your personal weather station by enabling %s to send, every 10 minutes, outdoor data like temperature, pressure, humidity, dew point, wind and rain to online services. To obtain help for a specific service, please read the corresponding help tab.', 'live-weather-station' ), LWS_PLUGIN_NAME);
                $s2 = __('Note that no data from inside your home (noise, temperature, CO₂ ...) are transmitted to these services.', 'live-weather-station' );
                $tabs[] = array(
                    'title'    => __('Sharing data', 'live-weather-station'),
                    'id'       => 'lws-contextual-station-sharing',
                    'content'  => '<p>' . $s1 . '</p><p><em>' . $s2 . '</em></p>');


                $s1 = __('To obtain site ID and authentication key from Met Office please, follow these steps:', 'live-weather-station' );
                $s2 = self::get(-12, __('%s on the Weather Observations Website from Met Office.', 'live-weather-station'), __('Create an account', 'live-weather-station'));
                $s3 = self::get(-13, __('After registration, log in and %s.', 'live-weather-station'), __('create a site', 'live-weather-station'));
                $s4 = __('Then, copy and paste <em>Site ID</em> and <em>Authentication Key</em> in the corresponding fields of the "WOW Met Office" box, and click on the "connect" button.', 'live-weather-station');
                $s5 = self::get(-14, __('After a few hours you\'ll get something %s', 'live-weather-station'), __('like this!', 'live-weather-station'));
                $tabs[] = array(
                    'title'    => 'Met Office',
                    'id'       => 'lws-contextual-station-sharing-wow',
                    'content'  => '<p>' . $s1 . '</p><ol><li>' . $s2 . '</li><li>' . $s3 . '</li><li>' . $s4 . '</li></ol><p>' . $s5 .'</p>');

                $s1 = __('To obtain Station ID from PWS please, follow these steps:', 'live-weather-station' );
                $s2 = self::get(-15, __('%s on the PWS website.', 'live-weather-station'), __('Create an account', 'live-weather-station'));
                $s3 = self::get(-16, __('After registration, log in and %s.', 'live-weather-station'), __('add a new station', 'live-weather-station'));
                $s4 = __('Then, copy and paste <em>Station ID</em> in the corresponding fields of the "PWS Weather" box, set your password and click on the "connect" button.', 'live-weather-station');
                $s5 = self::get(-17, __('After a few hours you\'ll get something %s', 'live-weather-station'), __('like this!', 'live-weather-station'));
                $tabs[] = array(
                    'title'    => 'PWS Weather',
                    'id'       => 'lws-contextual-station-sharing-pws',
                    'content'  => '<p>' . $s1 . '</p><ol><li>' . $s2 . '</li><li>' . $s3 . '</li><li>' . $s4 . '</li></ol><p>' . $s5 .'</p>');

                $s1 = __('To obtain Station ID from Weather Underground please, follow these steps:', 'live-weather-station' );
                $s2 = self::get(-18, __('%s on the Weather Underground website.', 'live-weather-station'), __('Create an account', 'live-weather-station'));
                $s3 = self::get(-19, __('After registration, log in and %s.', 'live-weather-station'), __('add a new station by following the 4 steps registration form', 'live-weather-station'));
                $s4 = __('Then, copy and paste <em>Station ID</em> in the corresponding fields of the "Weather Underground" box, set your password and click on the "connect" button.', 'live-weather-station');
                $s5 = self::get(-20, __('After a few hours you\'ll get something %s', 'live-weather-station'), __('like this!', 'live-weather-station'));
                $tabs[] = array(
                    'title'    => 'Weather Underground',
                    'id'       => 'lws-contextual-station-sharing-wug',
                    'content'  => '<p>' . $s1 . '</p><ol><li>' . $s2 . '</li><li>' . $s3 . '</li><li>' . $s4 . '</li></ol><p>' . $s5 .'</p>');

            }
        }
        else {
            $s1 = sprintf(__('This screen displays all stations collected by %s.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = sprintf(__('To add a new weather station (to have its data collected by %s), just click on the "add" button after the title of this screen, then choose the type of the station.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s3 = __('If you mouse over the line of an existing station, some of the station management features will appear. To display the full station view, just click on its name.', 'live-weather-station');
            $tabs[] = array(
                    'title'    => __('Overview', 'live-weather-station'),
                    'id'       => 'lws-contextual-stations',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p>');
            $s1 = sprintf(__('In this version of %s and depending of the API key you have set, you can add the following types of stations:', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = '<img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_netatmo_color_logo()) . '" /><strong>' . __('Netatmo', 'live-weather-station') . '</strong> &mdash; ' . __('a Netatmo station to which you have access to.', 'live-weather-station');
            $s3 = '<img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_loc_color_logo()) . '" /><strong>' . __('Virtual', 'live-weather-station') . '</strong> &mdash; ' . __('a "virtual" weather station whose you only know the city or its coordinates.', 'live-weather-station');
            if (LWS_OWM_READY) {
                $s4 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_owm_color_logo()) . '" /><strong>' . __('OpenWeatherMap', 'live-weather-station') . '</strong> &mdash; ' . __('a personal weather station published on OpenWeatherMap.', 'live-weather-station') . '</p>';
            }
            else {
                $s4 = '';
            }
            $s5 = '<img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_wug_color_logo()) . '" /><strong>' . __('WeatherUndergroung', 'live-weather-station') . '</strong> &mdash; ' . __('a personal weather station published on Weather Underground.', 'live-weather-station');
            if (LWS_REAL_READY) {
                $s6 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_real_color_logo()) . '" /><strong>' . __('Realtime File', 'live-weather-station') . '</strong> &mdash; ' . __('a station exporting its data via a <em>realtime.txt</em> file (Cumulus, etc.).', 'live-weather-station') . '</p>';
            }
            else {
                $s6 = '';
            }
            if (LWS_RAW_READY) {
                $s7 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_raw_color_logo()) . '" /><strong>' . __('Clientraw File', 'live-weather-station') . '</strong> &mdash; ' . __('a station exporting its data via a <em>clientraw.txt</em> file (Weather Display, WeeWX, etc.).', 'live-weather-station') . '</p>';
            }
            else {
                $s7 = '';
            }
            if (LWS_TXT_READY) {
                $s8 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_txt_color_logo()) . '" /><strong>' . __('Stickertags File', 'live-weather-station') . '</strong> &mdash; ' . __('a station exporting its data via a stickertags file (WeatherLink, WsWin32, MeteoBridge, etc.).', 'live-weather-station') . '</p>';
            }
            else {
                $s8 = '';
            }
            $tabs[] = array(
                'title'    => __('Stations types', 'live-weather-station'),
                'id'       => 'lws-contextual-stations-types',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p>' . $s4 . '<p>' . $s5 . '</p>' . $s6 . $s7 . $s8);

            $s1 = __('Depending of the type of the station, you can access to these features:', 'live-weather-station');
            $s2 = '<strong>' . __('Edit', 'live-weather-station') . '</strong> &mdash; ' . __('To modify or update the properties of the station (city, country, coordinates, etc.).', 'live-weather-station');
            $s3 = '<strong>' . __('Remove', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('To remove the station from the %s collect process.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s4 = '<strong>' . __('See events log', 'live-weather-station') . '</strong> &mdash; ' . __('To see events associated with the station.', 'live-weather-station');
            $s5 = '<strong>' . __('Verify on a map', 'live-weather-station') . '</strong> &mdash; ' . __('To verify, visually, the coordinates of the station.', 'live-weather-station');
            $s6 = '<strong>' . __('Sharing on&hellip;', 'live-weather-station') . '</strong> &mdash; ' . __('To get the direct url where the station shares its data.', 'live-weather-station');
            $s7 = '<strong>' . __('Shortcodes', 'live-weather-station') . '</strong> &mdash; ' . __('To obtain shortcodes ready to insert in a page or a post.', 'live-weather-station');
            $tabs[] = array(
                'title'    => __('Features', 'live-weather-station'),
                'id'       => 'lws-contextual-stations-features',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p><p>' . $s6 . '</p><p>' . $s7 . '</p>');
        }
        $screen = get_current_screen();
        foreach($tabs as $tab) {
            $screen->add_help_tab($tab);
        }
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
            '<p>' . self::get(9, '%s', __('Stations management', 'live-weather-station')) . '</p>'.
            self::get_standard_help_sidebar());
    }

    /**
     * Contextual help for "events" panel.
     *
     * @see set_contextual_help()
     * @since    3.0.0
     */
    public static function set_contextual_events() {
        $s1 = sprintf(__('This screen displays all events generated by %s during its operation. These events can help you to detect or understand issues or troubles when collecting weather data.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s2 = __('To view the details of an event, just click on its name.', 'live-weather-station');
        $screen = get_current_screen();
        $tabs = array();
        $tabs[] = array(
            'title'    => __('Overview', 'live-weather-station'),
            'id'       => 'lws-contextual-events',
            'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');

        $s = '<p>' . __('Events have the following types:', 'live-weather-station') . '</p>';
        $event_types = array(
            'emergency' => sprintf(__('A major error. %s doesn\'t run anymore or can\'t start.', 'live-weather-station'), LWS_PLUGIN_NAME),
            'alert' => sprintf(__('An error that undoubtedly affects the %s system operations.', 'live-weather-station'), LWS_PLUGIN_NAME),
            'critical' => sprintf(__('An error that undoubtedly affects the %s current operations.', 'live-weather-station'), LWS_PLUGIN_NAME),
            'error' => sprintf(__('An error that may affects the %s operations.', 'live-weather-station'), LWS_PLUGIN_NAME),
            'warning' => sprintf(__('A warning related to a temporary condition. Does not usually affect the %s operations.', 'live-weather-station'), LWS_PLUGIN_NAME),
            'notice' => __('An important information. Now you know!', 'live-weather-station'),
            'info' => __('A standard information, just for you to know... and forget!', 'live-weather-station'),
            'debug' => __('An information for coders and testers, so not for humans.', 'live-weather-station'),
            'unknown' => __('The event is not typed, this can\'t be a good news.', 'live-weather-station')
        );
        foreach ($event_types as $key => $event_type) {
            $s .= '<p><i style="color:' . Logger::get_color($key) . '" class="fa fa-fw fa-lg ' . Logger::get_icon($key) . '"></i>&nbsp;';
            $s .= '<strong>' . Logger::get_name($key) . '</strong> &mdash; ' . $event_type . '</p>';
        }
        $tabs[] = array(
            'title'    => __('Events types', 'live-weather-station'),
            'id'       => 'lws-contextual-events-types',
            'content'  => $s);
        foreach($tabs as $tab) {
            $screen->add_help_tab($tab);
        }
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
            '<p>' . self::get(10, '%s', __('Events log description', 'live-weather-station')) . '</p>'.
            self::get_standard_help_sidebar());
    }

    /**
     * Contextual help for "requirements" panel.
     *
     * @see set_contextual_help()
     * @since    3.0.0
     */
    public static function set_contextual_requirements() {
        $s = sprintf(__('Your installation of WordPress does not meet the minimum requirements needed for %s to run. The items to be corrected are shown on this screen.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $screen = get_current_screen();
        $tabs = array();
        $tabs[] = array(
            'title'    => __('Overview', 'live-weather-station'),
            'id'       => 'lws-contextual-requirements',
            'content'  => '<p>' . $s . '</p>');
        foreach($tabs as $tab) {
            $screen->add_help_tab($tab);
        }
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
            '<p>' . self::get(11, '%s', __('Plugin requirements', 'live-weather-station')) . '</p>'.
            self::get_standard_help_sidebar());
    }

}