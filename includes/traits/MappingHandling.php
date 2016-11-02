<?php

namespace WeatherStation\UI\Mapping;

/**
 * Mapping handling for Weather Station plugin.
 *
 * @package Includes\Traits
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
trait Handling {

    /**
     * Get an embed map in a IFrame ready to echo.
     *
     * @param float $lat The latitude of the point.
     * @param float $lon The longitude of the point.
     * @param integer $height The height (in px) of the frame.
     * @param boolean $marker Optional. Indicates wheter the marker should be whown.
     * @return string The full html iframe ready to be printed.
     * @since 3.0.0
     */
    public static function get_embed($lat, $lon, $height, $marker=true) {
        $result = '<iframe style="width:100%%;height:' . $height . 'px;" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.openstreetmap.org/export/embed.html?bbox=%s&amp;layer=mapnik%s"></iframe>';
        /*
         * @fixme what to do in case of bbox has out of range coordinates?
         */
        $lat_shift = ($lat > 0 ? 0.01 : -0.01);
        $lon_shift = ($lon > 0 ? 0.01 : -0.01);
        $loc = array();
        $loc[] = $lon - $lon_shift;
        $loc[] = $lat - $lat_shift;
        $loc[] = $lon + $lon_shift;
        $loc[] = $lat + $lat_shift;
        $result = sprintf($result, implode('%2C', $loc), ($marker ? '&amp;marker=' . $lat . '%2C' . $lon : ''));
        return $result;
    }
}