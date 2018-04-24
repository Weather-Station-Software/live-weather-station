<?php

namespace WeatherStation\System\Help;

use WeatherStation\System\Logs\Logger;
use WeatherStation\UI\SVG\Handling as SVG;
use WeatherStation\DB\Query;
use WeatherStation\System\Environment\Manager;
use WeatherStation\System\I18N\Handling as Intl;

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
    public static $station_instance = null;
    private static $links = array (
        'en' => array (
            'handbook/settings', //0  source: settings - general tab
            'handbook/settings/history', //1  source: settings - history tab
            'handbook/settings/services', //2  source: settings - service tab
            'handbook/settings/display', //3  source: settings - display tab
            'handbook/settings/thresholds', //4  source: settings - thresholds tab
            'handbook/settings/system', //5  source: settings - system tab
            'handbook/data-historization/', //6  source: settings - history tab
            'support/frequently-asked-questions', //7  faq section
            'handbook/dashboard', //8  dashboard
            'handbook/stations-management', //9  stations
            'handbook/events', //10 events
            'handbook/requirements', //11 requirements
            'support/languages-translation', //12 translation help
            'blog', //13 Blog
            'handbook/getting-started', //14 Starting guide
            'handbook/settings/maintenance-operations', //15 source: settings - maintenance tab
            'handbook/technical-specifications#url', //16 stickertags documentation
            'handbook', //17 main documentation
            'support', //18 main support page
            'handbook/controls', //19 shortcodes
            ),
        'fr' => array (
            'documentation/reglages', //0  source: settings - general tab
            'documentation/reglages/historiques', //1  source: settings - history tab
            'documentation/reglages/services', //2  source: settings - service tab
            'documentation/reglages/affichage', //3  source: settings - display tab
            'documentation/reglages/seuils', //4  source: settings - thresholds tab
            'documentation/reglages/systeme', //5  source: settings - system tab
            'documentation/historisation-donnees/', //6  source: settings - history tab
            'assistance/questions-frequentes', //7  faq section
            'documentation/tableau-de-bord', //8  dashboard
            'documentation/gestion-des-stations', //9  stations
            'documentation/evenements', //10 events
            'documentation/prerequis-techniques', //11 requirements
            'assistance/langues-traductions', //12 translation help
            'journal', //13 Blog
            'documentation/demarrage-rapide', //14 Starting guide
            'documentation/reglages/operations-de-maintenance', //15 source: settings - maintenance tab
            'documentation/specifications-techniques#url', //16 stickertags documentation
            'documentation', //17 main documentation
            'assistance', //18 main support page
            'documentation/controles', //19 Controles
            ),
    );

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
     * Get the what's new string.
     *
     * @return string The complete icon string, ready to print.
     *
     * @since 3.3.0
     */
    public static function whats_new() {
        $lang = Intl::get_language_id();
        $target = '';
        if ((bool)get_option('live_weather_station_redirect_external_links')) {
            $target = ' target="_blank" ';
        }
        $url = LWS_WATSNEW_EN;
        if ($lang == 'fr') {
            $url = LWS_WATSNEW_FR;
        }
        if (Manager::patch_version() != 0 && LWS_SHOW_CHANGELOG) {
            $url = LWS_CHANGELOG_EN;
            if ($lang == 'fr') {
                $url = LWS_CHANGELOG_FR;
            }
        }
        if (Manager::is_plugin_in_production_mode()) {
            return '<a href="' . $url . '"' . $target . '>' . __('See what\'s new', 'live-weather-station') . '&hellip;</a>';
        }
        else {
            return '';
        }
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
        $lang = Intl::get_language_id();
        $target = '';
        if ((bool)get_option('live_weather_station_redirect_external_links')) {
            $target = ' target="_blank" ';
        }
        if ($number >= 0) {
            $path = self::$links[$lang][$number];
        }
        if ($path != '') {
            $result = sprintf($message, '<a href="https://weather.station.software/' . $lang . '/' . $path . '"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -1) {
            $result = sprintf($message, '<a href="https://weather.station.software/' . '"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -2) {
            $result = sprintf($message, '<a href="https://wordpress.org/support/plugin/live-weather-station"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -3) {
            $result = sprintf($message, '<a href="http://openweathermap.org/price"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -4) {
            $result = 'https://weather.station.software/' . $lang . '/feed/';
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
            $result = '<a href="http://reseaumeteofrance.fr/"' . $target . '>Patrice Corre</a>';
        }
        if ($number == -9) {
            $result = '<a href="http://creativecommons.org/licenses/by-sa/4.0/"' . $target . '>' . __('Creative Commons CC:BY-SA 4.0 license', 'live-weather-station') . '</a>';
        }
        if ($number == -10) {
            $result = sprintf($message, '<a href="https://weather.station.software/en/support/languages-translation/"' . $target . '>' . $anchor . '</a>');
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
        if ($number == -25) {
            $result = sprintf($message, '<a href="https://wordpress.org/support/topic/howto-translate-this-plugin-in-your-own-language/"' . $target . '>' . $anchor . '</a>');
        }
        if ($number == -26) {
            $result = '<a href="http://developers.pioupiou.fr/data-licensing/"' . $target . '>Open Data</a>';
        }
        return $result;
    }

    /**
     * Get icon for this article number.
     *
     * @param integer $number The article number.
     * @return string The complete icon string, ready to print.
     *
     * @since 3.3.0
     */
    public static function article($number) {
        $lang = Intl::get_language_id();
        $target = '';
        if ((bool)get_option('live_weather_station_redirect_external_links')) {
            $target = ' target="_blank" ';
        }
        $url = '';
        switch ($number) {
            case 0 :
                $url = 'https://weather.station.software/en/how-to-get-up-to-date-weather-data/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/comment-sassurer-de-la-fraicheur-des-donnees-meteo/';
                }
                break;
            case 1 :
                $url = 'https://weather.station.software/en/how-to-update/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/comment-mettre-jour/';
                }
                break;
            case 2 :
                $url = 'https://weather.station.software/en/find-nearest-weather-station/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/trouvez-la-station-meteorologique-la-plus-proche/';
                }
                break;
            case 3 :
                $url = 'https://weather.station.software/en/what-are-dew-and-frost-points/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/que-sont-les-points-de-rosee-et-de-givre/';
                }
                break;
            case 4 :
                $url = 'https://weather.station.software/en/heat-index-humidex/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/heat-index-et-humidex/';
                }
                break;
            case 5 :
                $url = 'https://weather.station.software/en/wind-chill/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/refroidissement-eolien/';
                }
                break;
            // todo 6 : cloud ceiling
            // todo 7 : health index + description + aggravating factor
            case 8 :
                $url = 'https://weather.station.software/en/dawn-dusk-story-angles/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/aube-crepuscule/';
                }
                break;
            // todo 9 : CBI + others
            case 10 :
                $url = 'https://weather.station.software/en/scheduled-task-interface/';
                if ($lang == 'fr') {
                    $url = 'https://weather.station.software/fr/utiliser-linterface-des-taches-planifiees/';
                }
                break;
            // todo 11 : analytics
            // todo 12 : humidity relative or absolute
            // todo 13 : temperatures + wet bulb
            // todo 14 : vapor pressure, density, enthalpy
            // todo 15 : emc
            // todo 16 : athmospheric pressure

            default:
                return '';
        }
        return '&nbsp;<a href="'. $url . '"' . $target . '><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
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
        add_action($loader, array('WeatherStation\System\Help\InlineHelp', 'set_contextual_' . $type));
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
            '<p>' . self::get(18, '%s', __('Support', 'live-weather-station')) . '</p>'.
            '<p>' . self::get(-1, '%s', __('Official website', 'live-weather-station')) . '</p>';
    }

    /**
     * Contextual help for "dashboard" panel.
     *
     * @see set_contextual_help()
     * @since    3.0.0
     */
    public static function set_contextual_dashboard() {
        $action = null;
        if (!($action = filter_input(INPUT_GET, 'action'))) {
            $action = filter_input(INPUT_POST, 'action');
        }
        if (!isset($action)) {
            $s = sprintf(__('Welcome to your %1$s Dashboard! This is the screen you will see when you click on %1$s icon in the WordPress left-hand navigation menu. You can get help for any %1$s screen by clicking the Help tab above the screen title.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $screen = get_current_screen();
            $tabs = array();
            $tabs[] = array(
                'title' => __('Overview', 'live-weather-station'),
                'id' => 'lws-contextual-dashboard',
                'content' => '<p>' . $s . '</p>');

            $s1 = sprintf(__('You can use the following controls to arrange your %s Dashboard screen to suit your workflow:', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = '<strong>' . __('Screen Options', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Use the Screen Options tab to choose which %s Dashboard boxes to show.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s3 = '<strong>' . __('Drag and Drop', 'live-weather-station') . '</strong> &mdash; ' . __('To rearrange the boxes, drag and drop by clicking on the title bar of the selected box and releasing when you see a gray dotted-line rectangle appear in the location you want to place the box.', 'live-weather-station');
            $s4 = '<strong>' . __('Box Controls', 'live-weather-station') . '</strong> &mdash; ' . __('Click the title bar of the box to expand or collapse it.', 'live-weather-station');
            $tabs[] = array(
                'title' => __('Layout', 'live-weather-station'),
                'id' => 'lws-contextual-dashboard-layout',
                'content' => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p>');

            $s1 = sprintf(__('The boxes on your %s Dashboard screen are:', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = '<strong>' . __('Welcome', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Shows links for some of the most common tasks when getting started or using %s.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s3 = '<strong>' . __('At a Glance', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Displays a summary of %s operations. Note that a similar box is displayed in your main WordPress Dashboard.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s4 = '<strong>' . __('Quota usage', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Displays quota usage and peak rates for main services.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s5 = '<strong>' . __('Cache performance', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('If cache is activated, displays efficiency (hit rate) and time saved.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s6 = '<strong>' . __('Events', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Displays counts of occurred events.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s7 = '<strong>' . __('Versions', 'live-weather-station') . '</strong> &mdash; ' . __('Displays important versions numbers.', 'live-weather-station');
            $s8 = '<strong>' . sprintf(__('%s News', 'live-weather-station'), LWS_PLUGIN_NAME) . '</strong> &mdash; ' . sprintf(__('Shows news from %s blog.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s9 = '<strong>' . __('Subscribe', 'live-weather-station') . '</strong> &mdash; ' . __('Displays a form to subscribe for latest news by mail.', 'live-weather-station');
            $s10 = '<strong>' . __('Translation', 'live-weather-station') . '</strong> &mdash; ' . __('If displayed, shows translations status.', 'live-weather-station');
            $s11= '<strong>' . __('About', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Displays information about %s and contributors.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s12= '<strong>' . __('Licenses', 'live-weather-station') . '</strong> &mdash; ' . __('Displays important information about the licenses under which are published some weather data.', 'live-weather-station');
            $tabs[] = array(
                'title' => __('Content', 'live-weather-station'),
                'id' => 'lws-contextual-dashboard-content',
                'content' => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p><p>' . $s6 . '</p><p>' . $s7 . '</p><p>' . $s8 . '</p><p>' . $s9 . '</p><p>' . $s10 . '</p><p>' . $s11 . '</p><p>' . $s12 . '</p>');

            foreach ($tabs as $tab) {
                $screen->add_help_tab($tab);
            }
            $screen->set_help_sidebar(
                '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
                '<p>' . self::get(8, '%s', __('Dashboard description', 'live-weather-station')) . '</p>' .
                self::get_standard_help_sidebar());
        }
    }

    /**
     * Contextual help for "settings" panel.
     *
     * @see set_contextual_help()
     * @since 3.0.0
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
        $s6 = '<strong>' . __('History', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('Here, you can set and review the settings used by %s to store and manage historical data.', 'live-weather-station'), LWS_PLUGIN_NAME) . ' ' . sprintf(__('This tab is visible only if %s runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s7 = '<strong>' . __('System', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('You can set here all the parameters related to the operation of the %s subsystems.', 'live-weather-station'), LWS_PLUGIN_NAME) . ' ' . sprintf(__('This tab is visible only if %s runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s8 = '<strong>' . __('Maintenance', 'live-weather-station') . '</strong> &mdash; ' . __('Here, you can make some maintenance operations that are not directly accessible elsewhere.', 'live-weather-station') . ' ' . sprintf(__('This tab is visible only if %s runs in extended mode.', 'live-weather-station'), LWS_PLUGIN_NAME);
        $s9 = '<strong>' . __('Sheduled tasks', 'live-weather-station') . '</strong> &mdash; ' . __('Here, you can view all scheduled tasks, force their execution or reschedule them.', 'live-weather-station') . ' ' . __('This tab is visible only if you\'re a time sorcerer.', 'live-weather-station');
        $tabs[] = array(
            'title'    => __('Content', 'live-weather-station'),
            'id'       => 'lws-contextual-settings-content',
            'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p><p>' . $s6 . '</p><p>' . $s7 . '</p><p>' . $s8 . '</p><p>' . $s9 . '</p>');

        $s1 = __('To obtain an API key from OpenWeatherMap please, follow these steps:', 'live-weather-station' );
        $s2 = self::get(-23, __('%s on the OpenWeatherMap website.', 'live-weather-station'), __('Create an account', 'live-weather-station'));
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
            '<p>' . self::get(0, '%s', __('Settings management', 'live-weather-station')) . '</p>'.
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
        if (!($service = strtolower(filter_input(INPUT_GET, 'service')))) {
            $service = strtolower(filter_input(INPUT_POST, 'service'));
        }
        if (!($id = filter_input(INPUT_GET, 'id'))) {
            $id = filter_input(INPUT_POST, 'id');
        }
        if (!($tab = filter_input(INPUT_GET, 'tab'))) {
            $tab = filter_input(INPUT_POST, 'tab');
        }
        if (is_numeric($id)) {
            $station = self::get_station($id);
            $type = $station['station_type'];
        }
        $tabs = array();
        if (isset($action) && $action == 'shortcode') {
            if (isset($tab) && $tab == 'current') {
                $s1 = __('This section shows you the the available shortcodes types for the current records.', 'live-weather-station');
            }
            if (isset($tab) && $tab == 'daily') {
                $s1 = __('This section shows you the the available shortcodes types for the daily values.', 'live-weather-station');
            }
            if (isset($tab) && $tab == 'yearly') {
                $s1 = __('This section shows you the the available shortcodes types for the historical data.', 'live-weather-station');
            }
            $s2 = __('To configure a shortcode, just click on its icon then set its parameters and copy/paste it in a page or a post.', 'live-weather-station');
            $tabs[] = array(
                'title' => __('Overview', 'live-weather-station'),
                'id' => 'lws-contextual-station-' . $tab,
                'content' => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');
            if (isset(self::$station_instance)) {
                $s1 = sprintf(__('In this version of %s and depending of your settings, you can use the following shortcodes:', 'live-weather-station'), LWS_PLUGIN_NAME);
                $s2 = self::$station_instance->get_help_modules($tab);
                $tabs[] = array(
                    'title' => __('Shortcodes', 'live-weather-station'),
                    'id' => 'lws-contextual-station-' . $tab . '-shortcodes',
                    'content' => '<p>' . $s1 . '</p>' . $s2 );
            }


        }
        if (isset($action) && $action == 'manage') {
            $s1 = __('This "station view" shows you the details of a station.', 'live-weather-station');
            $s2 = __('The left-hand column display statical information on the station as well as sharing and publishing format options.', 'live-weather-station');
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
        if (isset($action) && $action == 'form') {
            if (isset($service) && $service == 'location') {
                $s1 = __('In this screen, you can', 'live-weather-station');
                $s1 .= ' ' . lcfirst(__('Add or edit a "virtual" weather station', 'live-weather-station')) . '.';
                $s2 = sprintf(__('A "virtual" weather station is not a real, hardware station. This is in fact an assembly of meteorological measurements collected and updated by OpenWeatherMap service for specific coordinates; these measurements are presented by %s as those from a real station.', 'live-weather-station'), LWS_PLUGIN_NAME);
                $tabs[] = array(
                    'title'    => __('Overview', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-overview',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');

                $s1 = __('To add a "virtual" weather station, first complete the fields', 'live-weather-station');
                $s1 .= ' <strong>' . lcfirst(__('Station name', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('City', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Country', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Time zone', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . __('and', 'live-weather-station') . ' <strong>' . lcfirst(__('Altitude', 'live-weather-station')) . '</strong>.';
                $s2 = __('If you know the precise coordinates of the location, then complete the fields', 'live-weather-station');
                $s2 .= ' <strong>' . lcfirst(__('Latitude', 'live-weather-station')) . '</strong>';
                $s2 .= ' ' . __('and', 'live-weather-station') . ' <strong>' . lcfirst(__('Longitude', 'live-weather-station')) . '</strong>. ';
                $s2 .= sprintf(__('If you don\'t know these coordinates, left blank the corresponding fields, %s will try to find them based on the city and country information.', 'live-weather-station'), LWS_PLUGIN_NAME);
                $s3 = '<em>' . __('Note that the information you enter here is required for computations and presentations of meteorological and astronomical data. It is therefore crucial that they are as accurate as possible.', 'live-weather-station') . '</em>';
                $tabs[] = array(
                    'title'    => __('Settings', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-settings',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p>');
            }
            if (isset($service) && $service == 'weatherunderground') {
                $s1 = __('In this screen, you can', 'live-weather-station');
                $s1 .= ' ' . lcfirst(sprintf(__('Add or edit a weather station published on %s', 'live-weather-station'), 'Weather Underground')) . '.';
                $s2 = sprintf(__('This station may be a station that belongs to you or a station you know. The main thing is that it must be publicly available on the %s website.', 'live-weather-station'), 'Weather Underground');
                $tabs[] = array(
                    'title'    => __('Overview', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-overview',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');

                $s1 = __('To add a station of this type, complete the fields', 'live-weather-station');
                $s1 .= ' <strong>' . lcfirst(__('Station name', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Station model', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . __('and', 'live-weather-station') . ' <strong>' . lcfirst(__('Station ID', 'live-weather-station')) . '</strong>.';
                $s1 .= ' ' . __('You could find the value of the field', 'live-weather-station') . ' <strong>' . lcfirst(__('Station ID', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . sprintf(__('on the %s website (in the dashboard) or right in the URL of the station\'s page.', 'live-weather-station'), 'Weather Underground');
                $s2 = '<em>' . __('Note that the information you enter here is required for computations and presentations of meteorological and astronomical data. It is therefore crucial that they are as accurate as possible.', 'live-weather-station') . '</em>';
                $tabs[] = array(
                    'title'    => __('Settings', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-settings',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');
            }
            if (isset($service) && $service == 'realtime') {
                $s1 = __('In this screen, you can', 'live-weather-station');
                $s1 .= ' ' . lcfirst(__('Add a station via <em>realtime</em> file', 'live-weather-station')) . '.';
                $s2 = sprintf(__('If you operate your weather station using a software such as %1$s or %2$s, you can ask it to export its data via a  <em>%3$s</em> file. This file must be locally accessible, via a file server or a web server to be read by %4$s.', 'live-weather-station'), 'Cumulus', 'WeeWX', 'realtime.txt', LWS_PLUGIN_NAME);
                $tabs[] = array(
                    'title'    => __('Overview', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-overview',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');

                $s1 = __('To add a station of this type, first complete the fields', 'live-weather-station');
                $s1 .= ' <strong>' . lcfirst(__('Station name', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Station model', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('City', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Country', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Time zone', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Altitude', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Latitude', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . __('and', 'live-weather-station') . ' <strong>' . lcfirst(__('Longitude', 'live-weather-station')) . '</strong>.';
                $s1 .= ' ' . __('Then, complete', 'live-weather-station') . ' <strong>' . lcfirst(__('Source type', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . __('and', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong> ';
                $s1 .= ' ' . __('as follow:', 'live-weather-station') . '<br/>';
                $s1 .= '<p><strong>' . __('Local file', 'live-weather-station') . '</strong> &mdash; ' . __('for the field', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . sprintf(__('you can specify the full path of the file like %1$s or %2$s or %3$s.', 'live-weather-station'), '<code>/path/to/realtime.txt</code>', '<code>C:\path\to\realtime.txt</code>', '<code>\\\\smbserver\share\path\to\realtime.txt</code>') . '</p>';
                $s1 .= '<p><strong>' . __('Web server', 'live-weather-station') . '</strong> &mdash; ' . __('for the field', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . sprintf(__('you can specify the resource like %1$s.', 'live-weather-station'), '<code>www.example.com/path/realtime.txt</code>') . '</p>';
                $s1 .= '<p><strong>' . __('File server', 'live-weather-station') . '</strong> &mdash; ' . __('for the field', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . sprintf(__('you can specify the resource like %1$s (anonymous file server) or %2$s (authenticated file server).', 'live-weather-station'), '<code>example.com/path/realtime.txt</code>', '<code>user:password@example.com/path/realtime.txt</code>') . '</p>';
                $s2 = '<em>' . __('Note that the information you enter here is required for computations and presentations of meteorological and astronomical data. It is therefore crucial that they are as accurate as possible.', 'live-weather-station') . '</em>';
                $tabs[] = array(
                    'title'    => __('Settings', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-settings',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');
            }
            if (isset($service) && $service == 'clientraw') {
                $s1 = __('In this screen, you can', 'live-weather-station');
                $s1 .= ' ' . lcfirst(__('Add a station via <em>clientraw</em> file', 'live-weather-station')) . '.';
                $s2 = sprintf(__('If you operate your weather station using a software such as %1$s or %2$s, you can ask it to export its data via a  <em>%3$s</em> file. This file must be locally accessible, via a file server or a web server to be read by %4$s.', 'live-weather-station'), 'Weather Display', 'WeeWX', 'clientraw.txt', LWS_PLUGIN_NAME);
                $tabs[] = array(
                    'title'    => __('Overview', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-overview',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');

                $s1 = __('To add a station of this type, first complete the fields', 'live-weather-station');
                $s1 .= ' <strong>' . lcfirst(__('Station name', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Station model', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('City', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Country', 'live-weather-station')) . '</strong>';
                $s1 .= ', <strong>' . lcfirst(__('Time zone', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . __('and', 'live-weather-station') . ' <strong>' . lcfirst(__('Altitude', 'live-weather-station')) . '</strong>.';
                $s1 .= ' ' . __('Then, complete', 'live-weather-station') . ' <strong>' . lcfirst(__('Source type', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . __('and', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong> ';
                $s1 .= ' ' . __('as follow:', 'live-weather-station') . '<br/>';
                $s1 .= '<p><strong>' . __('Local file', 'live-weather-station') . '</strong> &mdash; ' . __('for the field', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . sprintf(__('you can specify the full path of the file like %1$s or %2$s or %3$s.', 'live-weather-station'), '<code>/path/to/clientraw.txt</code>', '<code>C:\path\to\clientraw.txt</code>', '<code>\\\\smbserver\share\path\to\clientraw.txt</code>') . '</p>';
                $s1 .= '<p><strong>' . __('Web server', 'live-weather-station') . '</strong> &mdash; ' . __('for the field', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . sprintf(__('you can specify the resource like %1$s.', 'live-weather-station'), '<code>www.example.com/path/clientraw.txt</code>') . '</p>';
                $s1 .= '<p><strong>' . __('File server', 'live-weather-station') . '</strong> &mdash; ' . __('for the field', 'live-weather-station') . ' <strong>' . lcfirst(__('Source name', 'live-weather-station')) . '</strong>';
                $s1 .= ' ' . sprintf(__('you can specify the resource like %1$s (anonymous file server) or %2$s (authenticated file server).', 'live-weather-station'), '<code>example.com/path/clientraw.txt</code>', '<code>user:password@example.com/path/clientraw.txt</code>') . '</p>';
                $s2 = '<em>' . __('Note that the information you enter here is required for computations and presentations of meteorological and astronomical data. It is therefore crucial that they are as accurate as possible.', 'live-weather-station') . '</em>';
                $tabs[] = array(
                    'title'    => __('Settings', 'live-weather-station'),
                    'id'       => 'lws-contextual-' . $service . '-settings',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');
            }
        }
        if (!isset($action)) {
            $s1 = sprintf(__('This screen displays all stations collected by %s.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = sprintf(__('To add a new weather station (to have its data collected by %s), just click on the "add" button after the title of this screen, then choose the type of the station.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s3 = __('If you mouse over the line of an existing station, some of the station management features will appear. To display the full station view, just click on its name.', 'live-weather-station');
            $tabs[] = array(
                    'title'    => __('Overview', 'live-weather-station'),
                    'id'       => 'lws-contextual-stations',
                    'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p>');
            $s1 = sprintf(__('In this version of %s and depending of the API key you have set, you can add the following types of stations:', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_netatmo_color_logo()) . '" /><strong>' . __('Netatmo', 'live-weather-station') . '</strong> &mdash; ' . __('a Netatmo station to which you have access to.', 'live-weather-station') . '</p>';
            $s3 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_netatmo_hc_color_logo()) . '" /><strong>' . __('Netatmo "Healthy Home Coach"', 'live-weather-station') . '</strong> &mdash; ' . __('a Netatmo "Healthy Home Coach" device to which you have access to.', 'live-weather-station') . '</p>';
            $s4 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_weatherflow_color_logo()) . '" /><strong>' . __('WeatherFlow', 'live-weather-station') . '</strong> &mdash; ' . __('a public WeatherFlow station.', 'live-weather-station') . '</p>';
            $s5 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_piou_color_logo()) . '" /><strong>' . __('Pioupiou', 'live-weather-station') . '</strong> &mdash; ' . __('a Pioupiou sensor as a station.', 'live-weather-station') . '</p>';
            $s6 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_loc_color_logo()) . '" /><strong>' . __('Virtual', 'live-weather-station') . '</strong> &mdash; ' . __('a "virtual" weather station whose you only know the city or its coordinates.', 'live-weather-station') . '</p>';
            if (LWS_OWM_READY) {
                $s7 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_owm_color_logo()) . '" /><strong>' . __('OpenWeatherMap', 'live-weather-station') . '</strong> &mdash; ' . __('a personal weather station published on OpenWeatherMap.', 'live-weather-station') . '</p>';
            }
            else {
                $s7 = '';
            }
            $s8 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_wug_color_logo()) . '" /><strong>' . __('Weather Undergroung', 'live-weather-station') . '</strong> &mdash; ' . __('a personal weather station published on Weather Underground.', 'live-weather-station') . '</p>';
            $s9 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_real_color_logo()) . '" /><strong>' . __('Realtime File', 'live-weather-station') . '</strong> &mdash; ' . __('a station exporting its data via a <em>realtime.txt</em> file (Cumulus, etc.).', 'live-weather-station') . '</p>';
            $s10 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_raw_color_logo()) . '" /><strong>' . __('Clientraw File', 'live-weather-station') . '</strong> &mdash; ' . __('a station exporting its data via a <em>clientraw.txt</em> file (Weather Display, WeeWX, etc.).', 'live-weather-station') . '</p>';
            $s11 = '<p><img style="width:26px;float:left;margin-top: -4px;padding-right: 6px;" src="' . set_url_scheme(SVG::get_base64_txt_color_logo()) . '" /><strong>' . __('Stickertags File', 'live-weather-station') . '</strong> &mdash; ' . __('a station exporting its data via a stickertags file (WeatherLink, WsWin32, MeteoBridge, etc.).', 'live-weather-station') . '</p>';
            $tabs[] = array(
                'title'    => __('Stations types', 'live-weather-station'),
                'id'       => 'lws-contextual-stations-types',
                'content'  => $s1 . $s2 . $s3 . $s4 . $s5 . $s6 . $s7 . $s8 . $s9 . $s10 . $s11);

            $s1 = __('Depending of the type of the station, you can access to these features:', 'live-weather-station');
            $s2 = '<strong>' . __('Edit', 'live-weather-station') . '</strong> &mdash; ' . __('To modify or update the properties of the station (city, country, coordinates, etc.).', 'live-weather-station');
            $s3 = '<strong>' . __('Remove', 'live-weather-station') . '</strong> &mdash; ' . sprintf(__('To remove the station from the %s collect process.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s4 = '<strong>' . __('See events log', 'live-weather-station') . '</strong> &mdash; ' . __('To see events associated with the station.', 'live-weather-station');
            $s5 = '<strong>' . __('Verify on a map', 'live-weather-station') . '</strong> &mdash; ' . __('To verify, visually, the coordinates of the station.', 'live-weather-station');
            $s6 = '<strong>' . __('Data', 'live-weather-station') . '</strong> &mdash; ' . __('To get the direct URL where the station shares its data.', 'live-weather-station');
            $tabs[] = array(
                'title'    => __('Features', 'live-weather-station'),
                'id'       => 'lws-contextual-stations-features',
                'content'  => '<p>' . $s1 . '</p><p>' . $s2 . '</p><p>' . $s3 . '</p><p>' . $s4 . '</p><p>' . $s5 . '</p><p>' . $s6 . '</p>');
        }
        $screen = get_current_screen();
        foreach($tabs as $tab) {
            $screen->add_help_tab($tab);
        }
        if (isset($action) && $action == 'shortcode') {
            $screen->set_help_sidebar(
                '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
                '<p>' . self::get(19, '%s', __('Shortcodes', 'live-weather-station')) . '</p>'.
                self::get_standard_help_sidebar());
        }
        else {
            $screen->set_help_sidebar(
                '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
                '<p>' . self::get(9, '%s', __('Stations management', 'live-weather-station')) . '</p>'.
                self::get_standard_help_sidebar());
        }
    }

    /**
     * Contextual help for "events" panel.
     *
     * @see set_contextual_help()
     * @since    3.0.0
     */
    public static function set_contextual_events() {
        if (!($view = filter_input(INPUT_GET, 'view'))) {
            $view = filter_input(INPUT_POST, 'view');
        }
        if (!isset($view) || $view == 'list-table-logs') {
            $s1 = sprintf(__('This screen displays all events generated by %s during its operation. These events can help you to detect or understand issues or troubles when collecting weather data.', 'live-weather-station'), LWS_PLUGIN_NAME);
            $s2 = __('To view the details of an event, just click on its name.', 'live-weather-station');
            $screen = get_current_screen();
            $tabs = array();
            $tabs[] = array(
                'title' => __('Overview', 'live-weather-station'),
                'id' => 'lws-contextual-events',
                'content' => '<p>' . $s1 . '</p><p>' . $s2 . '</p>');

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
                'title' => __('Events types', 'live-weather-station'),
                'id' => 'lws-contextual-events-types',
                'content' => $s);
            foreach ($tabs as $tab) {
                $screen->add_help_tab($tab);
            }
            $screen->set_help_sidebar(
                '<p><strong>' . __('For more information:', 'live-weather-station') . '</strong></p>' .
                '<p>' . self::get(10, '%s', __('Events log description', 'live-weather-station')) . '</p>' .
                self::get_standard_help_sidebar());
        }
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