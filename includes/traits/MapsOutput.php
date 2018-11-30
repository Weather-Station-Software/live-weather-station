<?php

namespace WeatherStation\Maps;

use WeatherStation\Data\DateTime\Conversion as Datetime_Conversion;
use WeatherStation\Data\Type\Description as Type_Description;
use WeatherStation\Data\Unit\Description as Unit_Description;
use WeatherStation\Data\Unit\Conversion as Unit_Conversion;
use WeatherStation\SDK\Generic\Plugin\Season\Calculator;
use WeatherStation\SDK\OpenWeatherMap\Plugin\BaseCollector as OWM_Base_Collector;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Utilities\ColorsManipulation;
use WeatherStation\DB\Query;
use WeatherStation\System\Analytics\Performance;
use WeatherStation\Utilities\Markdown;
use WeatherStation\Data\History\Builder as History;
use WeatherStation\System\Environment\Manager as EnvManager;
use WeatherStation\Utilities\ColorBrewer;
use WeatherStation\System\Device\Manager as DeviceManager;
use WeatherStation\System\Options\Handling as Options;

/**
 * Outputing / shortcoding maps functionalities for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */
trait Output {

    //use Unit_Description, Type_Description, Datetime_Conversion, Unit_Conversion, Query;

    /**
     * Get the changelog.
     *
     * @return string $attributes The id of the map.
     * @since 3.3.0
     */
    public function maps_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('style' => 'markdown', 'title' => 'h3', 'list' => 'bullet'), $attributes );
        $style = $_attributes['style'];
        $title = $_attributes['title'];
        $list = $_attributes['list'];

        $changelog = LWS_PLUGIN_DIR . 'changelog.txt';
        if (file_exists($changelog)) {
            try {
                $s = file_get_contents($changelog);
                $Markdown = new Markdown();
                $result = $Markdown->text($s);
            }
            catch (\Exception $e) {
                $result = __('Sorry, unable to find or read changelog file.', 'live-weather-station');
            }
        }
        else {
            $result = __('Sorry, unable to find or read changelog file.', 'live-weather-station');
        }


        if ($list == 'icon') {
            lws_font_awesome();
            $result = str_replace('<ul>', '', $result);
            $result = str_replace('</ul>', '', $result);
            $result = str_replace('<li>', '', $result);
            $result = str_replace('</li>', '<br/>', $result);
            $result = str_replace('New: ', '<i class="'. LWS_FAS . ' fa-fw fa-plus-square" aria-hidden="true">&nbsp;</i>&nbsp;', $result);
            $result = str_replace('Removed: ', '<i class="'. LWS_FAS . ' fa-fw fa-minus-square" aria-hidden="true">&nbsp;</i>&nbsp;', $result);
            $result = str_replace('New language: ', '<i class="'. LWS_FAS . ' fa-fw fa-language" aria-hidden="true">&nbsp;</i>&nbsp;new translation: ', $result);
            $result = str_replace('Improvement: ', '<i class="'. LWS_FAS . ' fa-fw fa-check-square" aria-hidden="true">&nbsp;</i>&nbsp;', $result);
            $result = str_replace('Bug fix: ', '<i class="'. LWS_FAS . ' fa-fw fa-bug" aria-hidden="true">&nbsp;</i>&nbsp;fixed: ', $result);
        }

        if ($style == 'divi_accordion') {
            $result = str_replace('<h1>',  '</p>' . PHP_EOL .'</div>' . PHP_EOL . '</div>' . PHP_EOL . '<div class="et_pb_module et_pb_toggle et_pb_toggle_close">' . PHP_EOL . '<h1 class="et_pb_toggle_title">', $result);
            $result = str_replace('</h1>',  '</h1>' . PHP_EOL . '<div class="et_pb_toggle_content clearfix"><p>', $result);
            $result = substr($result, 78, 100000000);
            $result = '<div class="et_pb_module et_pb_toggle et_pb_toggle_open">' . PHP_EOL . $result;
            $result = '<div class="et_pb_module et_pb_accordion">' . PHP_EOL . $result;
            $result .= '</p></div></div></div>';
        }

        $result = str_replace('<h1', '<'.$title, $result);
        $result = str_replace('</h1', '</'.$title, $result);
        return $result;
    }


}
