<?php

namespace WeatherStation\System\Help;

use WeatherStation\System\Logs\Logger;
use WeatherStation\UI\SVG\Handling as SVG;

/**
 * This class add inline help links to the plugin.
 *
 * @package Includes\System
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class InlineHelp {

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
        if (!($action = filter_input(INPUT_GET, 'action'))) {
            $action = filter_input(INPUT_POST, 'action');
        }
        $tabs = array();
        if (isset($action) && $action='manage') {
            $s1 = __('This "station view" shows you the details of a station.', 'live-weather-station');
            $s2 = __('The lef-hand column display statical information on the station as well as shortcodes or publishing format options.', 'live-weather-station');
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
            $s4 = '<img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_owm_color_logo()) . '" /><strong>' . __('OpenWeatherMap', 'live-weather-station') . '</strong> &mdash; ' . __('a personal weather station published on OpenWeatherMap.', 'live-weather-station');
            $s5 = '<img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_wug_color_logo()) . '" /><strong>' . __('WeatherUndergroung', 'live-weather-station') . '</strong> &mdash; ' . __('a personal weather station published on Weather Underground.', 'live-weather-station');
            $tabs[] = array(
                'title'    => __('Stations types', 'live-weather-station'),
                'id'       => 'lws-contextual-stations-types',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p>');

            $s1 = __('Depending of the type of the station, you can access to these features:', 'live-weather-station');
            $s2 = '<strong>' . __('Edit', 'live-weather-station') . '</strong> &mdash; ' . __('To modify or update the properties of the station (city, country, coordinates, etc.).', 'live-weather-station');
            $s3 = '<strong>' . __('Manage services', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('To let %s send outdoor data of the station to some online services.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s4 = '<strong>' . __('Remove', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('To remove the station from the %s collect process.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s5 = '<strong>' . __('See events log', 'live-weather-station') . '</strong> &mdash; ' . __('To see events associated with the station.', 'live-weather-station');
            $s6 = '<strong>' . __('Verify on a map', 'live-weather-station') . '</strong> &mdash; ' . __('To verify, visually, the coordinates of the station.', 'live-weather-station');
            $s7 = '<strong>' . __('Shortcodes', 'live-weather-station') . '</strong> &mdash; ' . __('To obtain shortcodes ready to insert in a page or a post.', 'live-weather-station');
            $tabs[] = array(
                'title'    => __('Features', 'live-weather-station'),
                'id'       => 'lws-contextual-stations-features',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p><p>' . $s6 . '</p><p>' . $s7 . '</p>');
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