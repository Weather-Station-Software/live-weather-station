<?php

namespace WeatherStation\Utilities;

use WeatherStation\Utilities\ColorsManipulation;


/**
 * Add features to pages to get options settings.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class CSS {

    /**
     * Build shadow string.
     *
     * @param string $position Position of the shadow.
     * @param string $length Position of the shadow.
     * @param string $diffusion Position of the shadow.
     * @param string $obscurity Position of the shadow.
     * @param string $color Position of the shadow.
     *
     * @return string The shadow string.
     * @since 3.7.5
     */
    public static function shadow($position, $length, $diffusion, $obscurity, $color) {
        if (strpos($position, '-') === false) {
            return '';
        }
        switch ($length) {
            case 'medium':
                $size = 3;
                break;
            case 'distant':
                $size = 6;
                break;
            case 'faraway':
                $size = 12;
                break;
            default:
                $size = 1;
        }
        switch ($diffusion) {
            case 'medium':
                $dif = 1.5;
                break;
            case 'precise':
                $dif = 3;
                break;
            default:
                $dif = 1;
        }
        switch ($obscurity) {
            case 'medium':
                $obs = 0.3;
                break;
            case 'strong':
                $obs = 0.7;
                break;
            default:
                $obs = 0.1;
        }
        $dif = (int)round($size / $dif, 0);
        $x = 0;
        $y = 0;
        $pos = explode('-', $position);
        if (count($pos) === 2) {
            switch ($pos[0]) {
                case 'top':
                    $y -= $size;
                    break;
                case 'bottom':
                    $y += $size;
                    break;
            }
            switch ($pos[1]) {
                case 'left':
                    $x -= $size;
                    break;
                case 'right':
                    $x += $size;
                    break;
            }
        }
        else {
            return '';
        }
        try {
            $c = ColorsManipulation::hexToRgb($color);
            $R = $c['R'];
            $G = $c['G'];
            $B = $c['B'];
        }
        catch (\Exception $ex) {
            $R = 0;
            $G = 0;
            $B = 0;
        }
        return $x . 'px ' . $y . 'px ' . $dif . 'px rgba(' . $R .',' . $G .',' . $B .',' . $obs . ')';
    }

}
