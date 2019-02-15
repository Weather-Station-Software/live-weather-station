<?php

namespace WeatherStation\UI\SVG;

/**
 * This class add svg management.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Handling {

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
     * Returns a base64 svg resource for use in the main admin menu.
     *
     * @param string $color1 Optional. External color of the icon.
     * @param string $color2 Optional. Internal color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_menu_icon($color1='#000', $color2='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 1500 1500">';
        $source .= '<path style="fill:' . $color1 . '" d="M719.726 13.792c-61.976 10.239-110.896 35.253-153.82 78.193-36.114 36.114-56.575 70.805-71.651 122.543l-7.39 25.595-1.707 204.995-1.424 205.292-25.031 21.604c-67.11 58.86-116.859 138.463-139.888 224.060-59.143 220.053 58.86 454.635 271.244 538.229 256.466 101.221 541.375-45.209 608.751-312.773 41.517-163.775-14.213-339.779-142.455-450.094l-23.887-20.758-1.424-203.867-1.707-203.867-7.39-27.583c-12.507-45.209-33.842-83.877-65.404-117.706-49.765-53.162-115.717-82.733-189.935-85.018-17.893-0.549-38.934 0.015-46.885 1.157zM796.778 201.724c17.626 8.533 34.972 26.159 44.068 44.068l6.825 14.213 0.861 247.652 0.564 247.652 18.771 7.969c81.889 34.689 143.3 113.446 158.938 203.007 15.356 87.29-12.507 174.015-76.487 237.977-76.77 76.487-184.815 100.657-286.882 63.697-71.369-25.878-135.346-89.842-161.209-161.209-31.84-88.15-18.19-182.546 37.241-257.31 26.442-35.536 68.802-69.1 108.892-86.146l18.771-7.969v-242.25c0-196.181 0.847-244.521 3.977-255.041 7.969-27.302 32.418-52.036 59.706-60.848 17.345-5.684 48.905-3.413 65.968 4.54z"></path>';
        $source .= '<path style="fill:' . $color2 . '" d="M736.493 923.641c-13.651 3.413-37.821 17.626-45.492 26.723-15.921 18.771-22.182 36.114-22.182 60.284-0.283 20.178 0.847 25.017 9.095 41.796 15.639 32.123 43.504 48.905 80.181 49.188 17.063 0 24.453-1.424 37.241-7.39 33.265-15.639 52.315-45.492 52.315-82.17-0.283-36.397-17.345-64.542-48.341-79.898-19.318-9.393-45.478-13.087-62.821-8.533z"></path>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the plugin.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_lws_icon() {
        return self::get_base64_menu_icon($color1='#FFFFFF', $color2='#ffd200');
    }

    /**
     * Returns a base64 svg resource for the icon.
     *
     * @param integer $type The type of the station.
     * @param string $color The color of the logo.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_station_icon($type, $color='#000') {
        switch ($type) {
            case 0:
                $result = self::get_base64_netatmo_icon($color);
                break;
            case 1:
                $result = self::get_base64_loc_icon($color);
                break;
            case 2:
                $result = self::get_base64_owm_icon($color);
                break;
            case 3:
                $result = self::get_base64_wug_icon($color);
                break;
            case 4:
                $result = self::get_base64_raw_icon($color);
                break;
            case 5:
                $result = self::get_base64_real_icon($color);
                break;
            case 6:
                $result = self::get_base64_netatmo_icon($color);
                break;
            case 7:
                $result = self::get_base64_txt_icon($color, $color, $color, $color);
                break;
            case 8:
                $result = self::get_base64_weatherflow_icon($color);
                break;
            case 9:
                $result = self::get_base64_piou_icon($color);
                break;
            case 10:
                $result = self::get_base64_bloomsky_icon($color);
                break;
            case 11:
                $result = self::get_base64_ambient_icon($color);
                break;
            case 12:
                $result = self::get_base64_weatherlink_icon($color);
                break;
            default:
                $result = self::get_base64_lws_icon();
        }
        return $result;
    }

    /**
     * Returns a base64 svg resource for the icon.
     *
     * @param integer $type The type of the station.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_station_color_logo($type) {
        switch ($type) {
            case 0:
                $result = self::get_base64_netatmo_color_logo();
                break;
            case 1:
                $result = self::get_base64_loc_color_logo();
                break;
            case 2:
                $result = self::get_base64_owm_color_logo();
                break;
            case 3:
                $result = self::get_base64_wug_color_logo();
                break;
            case 4:
                $result = self::get_base64_raw_color_logo();
                break;
            case 5:
                $result = self::get_base64_real_color_logo();
                break;
            case 6:
                $result = self::get_base64_netatmo_hc_color_logo();
                break;
            case 7:
                $result = self::get_base64_txt_color_logo();
                break;
            case 8:
                $result = self::get_base64_weatherflow_color_logo();
                break;
            case 9:
                $result = self::get_base64_piou_color_logo();
                break;
            case 10:
                $result = self::get_base64_bloomsky_color_logo();
                break;
            case 11:
                $result = self::get_base64_ambient_color_logo();
                break;
            case 12:
                $result = self::get_base64_weatherlink_color_logo();
                break;
            default:
                $result = self::get_base64_lws_icon();
        }
        return $result;
    }





    /**
     * Returns a base64 svg resource for the OpenWeatherMap icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_owm_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="-1000 -1000 6000 6000">';
        $source .= '<g transform="translate(0,0) scale(0.9,0.9)">';
        $source .= '<path style="fill:' . $color . '" d="M0 2240 l0 -2240 2220 0 2220 0 0 2240 0 2240 -2220 0 -2220 0 0 -2240z m2278 1823 c35 -52 384 -664 381 -668 -2 -2 -41 7 -85 20 -209 61 -468 61 -676 1 -61 -18 -78 -20 -74 -9 7 17 339 594 372 646 29 44 55 48 82 10z m1112 -871 c-46 -185 -86 -343 -90 -350 -4 -9 -21 11 -46 54 -96 164 -265 323 -451 425 -55 29 -60 35 -42 41 32 12 608 178 649 187 25 6 39 4 50 -6 13 -11 4 -56 -70 -351z m-2065 242 c193 -48 354 -90 359 -95 4 -4 -11 -17 -35 -30 -86 -43 -165 -104 -260 -198 -99 -100 -149 -166 -215 -288 -21 -40 -42 -73 -46 -73 -4 0 -9 8 -12 18 -19 63 -200 691 -204 710 -6 29 16 55 42 48 12 -3 179 -45 371 -92z m1115 -180 c217 -41 434 -171 579 -347 314 -379 314 -925 0 -1304 -356 -432 -1002 -491 -1436 -132 -488 403 -488 1165 0 1568 162 135 351 212 572 234 60 7 200 -3 285 -19z m-1356 -654 c-53 -184 -58 -448 -11 -630 14 -52 24 -96 22 -98 -5 -6 -640 367 -650 382 -7 11 -7 21 0 32 8 12 314 195 661 393 1 1 -9 -35 -22 -79z m2621 -113 c176 -101 324 -192 330 -201 7 -11 7 -21 0 -32 -9 -14 -656 -395 -662 -390 -2 1 9 47 23 102 48 187 44 448 -10 635 -14 47 -19 78 -12 76 6 -3 155 -88 331 -190z m-331 -863 c19 -65 67 -231 106 -368 77 -265 79 -284 26 -278 -53 7 -739 181 -748 190 -7 7 8 20 39 37 123 70 185 117 279 210 101 101 152 167 214 282 19 35 38 59 42 55 5 -5 23 -62 42 -128z m-2130 -32 c27 -42 92 -120 145 -172 97 -98 166 -149 278 -210 34 -19 61 -35 60 -37 -8 -7 -705 -203 -725 -203 -12 0 -25 7 -29 16 -4 12 114 520 170 724 l9 35 22 -39 c11 -21 43 -73 70 -114z m1416 -475 c-1 -20 -386 -681 -404 -692 -11 -7 -21 -7 -32 0 -18 12 -403 672 -404 693 0 16 0 16 115 -14 187 -48 449 -44 632 9 80 24 93 24 93 4z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome OpenWeatherMap logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_owm_grey_logo() {
        return self::get_base64_owm_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored OpenWeatherMap logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_owm_color_logo() {
        return self::get_base64_owm_icon('#F9853B');
    }


    /**
     * Returns a base64 svg resource for the Pioupiou icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.5.0
     */
    public static function get_base64_piou_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="-173 -90 200 200">';
        $source .= '<g transform="translate(0,0) scale(1,-1)">';
        $source .= '<path style="fill:' . $color . '" d="M 0,0 C -3.337,25.016 -22.673,49.194 -37.659,50.347 -42.021,52.523 -44.696,52.767 -49.094,53.151 -38.052,45.863 -36.223,29.964 -36.985,17.58 -37.757,5.108 -42.023,-4.414 -47.848,-13.646 -35.19,-6.392 -25.468,5.84 -19.385,19.028 -18.619,20.692 -14.749,21.343 -14.709,18.919 -14.239,-10.317 -28.129,-37.095 -53.693,-52.412 -59.883,-56.12 -66.08,-58.094 -72.079,-58.597 -77.798,-59.505 -89.662,-59.743 -90.124,-65.325 -90.248,-66.819 -95.008,-67.78 -94.86,-65.971 -94.452,-61.026 -88.721,-59.02 -82.77,-57.912 -83.632,-57.727 -84.487,-57.504 -85.332,-57.261 -92.381,-55.965 -105.162,-54.23 -105.688,-63.376 -105.767,-64.766 -110.474,-65.325 -110.431,-64.644 -109.952,-56.335 -100.994,-54.36 -92.414,-54.426 -101.373,-49.779 -109.006,-41.616 -114.275,-31.136 -114.313,-31.064 -114.294,-31 -114.324,-30.928 -114.367,-30.851 -114.443,-30.803 -114.477,-30.721 -117.333,-23.752 -122.053,-13.561 -121.109,-5.168 -128.185,-3.075 -135.636,-3.247 -142.83,-1.758 -143.889,-14.122 -144.729,-30.636 -136.666,-36.99 -137.225,-39.139 -137.948,-40.48 -136.036,-42.893 -134.847,-44.839 -133.606,-45.492 -131.972,-46.399 -132.943,-63.574 -95.224,-78.452 -81.312,-79.189 -75.416,-79.505 -71.012,-76.28 -65.563,-76.049 -59.424,-75.78 -56.203,-78.058 -49.82,-77.696 -48.305,-77.611 -35.955,-71.774 -35.353,-71.484 -28.674,-68.243 -25.896,-66.186 -26.902,-56.495 -4.203,-61.026 1.876,-14.034 0,0 M -106.732,-5.243 -106.732,-5.244 C -105.505,-4.732 -106.71,-4.548 -106.732,-5.243 M -105.153,-5.191 C -105.101,-5.279 -105.194,-5.4 -105.338,-5.527 -105.153,-5.576 -105.015,-5.642 -105.002,-5.756 -104.957,-6.338 -104.953,-7.004 -105.137,-7.565 -105.561,-8.802 -107.347,-9.154 -108.441,-9.379 -108.954,-9.482 -109.982,-9.706 -110.356,-9.209 -111.545,-7.646 -112.103,-5.928 -110.101,-4.907 -108.624,-4.148 -106.161,-3.479 -105.153,-5.191 M -142.533,1.557 C -142.461,2.34 -142.394,3.104 -142.333,3.833 -140.585,24.257 -136.296,32.427 -118.247,38.655 -113.313,44.106 -108.524,46.784 -102.381,50.134 -93.91,54.659 -74.687,59.569 -64.662,50.134 -63.913,52.189 -59.543,53.041 -56.375,53.616 -56.375,53.542 -56.403,53.498 -56.403,53.42 -55.606,26.723 -54.47,0.672 -70.809,-19.616 -71.333,-19.551 -71.857,-19.484 -72.364,-19.386 -72.355,-19.252 -72.362,-19.111 -72.456,-18.975 -85.155,-0.958 -82.786,30.904 -72.902,49.194 -72.161,50.567 -76.349,49.58 -76.694,49.39 -94.731,39.447 -97.967,18.677 -95.392,-0.208 -97.511,2.229 -99.891,4.32 -103.011,5.824 -108.903,8.666 -116.218,5.701 -119.344,0.459 -126.976,1.885 -134.786,1.591 -142.533,1.557"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Pioupiou logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.5.0
     */
    public static function get_base64_piou_grey_logo() {
        return self::get_base64_piou_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Pioupiou logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.5.0
     */
    public static function get_base64_piou_color_logo() {
        return self::get_base64_piou_icon('#285291');
    }

    /**
     * Returns a base64 svg resource for the Ambient icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.5.0
     */
    public static function get_base64_ambient_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 400 400">';
        $source .= '<g transform="translate(55,355) scale(0.075,-0.075)">';
        $source .= '<path style="fill:' . $color . '" d="M1815 3813 c-634 -52 -1207 -434 -1483 -988 -60 -121 -60 -116 0 -166 350 -293 869 -470 1451 -496 l147 -6 0 -39 0 -38 -118 0 c-175 0 -443 37 -653 90 -324 82 -607 219 -814 394 -33 28 -68 57 -77 64 -15 12 -20 4 -48 -83 -17 -54 -28 -106 -25 -116 3 -10 47 -57 98 -105 176 -166 412 -299 697 -394 246 -82 587 -140 824 -140 50 0 97 -4 104 -9 8 -5 12 -22 10 -42 l-3 -34 -110 3 c-412 11 -808 104 -1130 265 -55 27 -119 63 -142 78 -23 16 -44 29 -48 29 -3 0 -11 5 -18 10 -7 6 -42 33 -79 60 -37 28 -103 87 -147 131 l-80 82 -5 -34 c-3 -19 -10 -72 -16 -118 -10 -83 -10 -86 16 -122 46 -67 216 -223 313 -288 94 -62 314 -175 401 -206 321 -113 560 -160 928 -181 l122 -7 0 -34 c0 -22 -6 -36 -17 -40 -9 -3 -91 -1 -182 5 -483 35 -865 146 -1206 352 -106 64 -257 192 -325 275 l-55 67 -3 -79 c-8 -169 41 -408 125 -617 168 -421 492 -765 903 -960 403 -190 836 -226 1278 -105 61 17 112 35 112 40 0 5 9 9 20 9 36 0 223 91 347 169 211 133 424 352 566 581 270 438 335 1007 172 1507 -33 98 -117 290 -133 303 -4 3 -18 27 -32 55 -34 68 -132 198 -225 301 -363 399 -925 622 -1460 577z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Ambient logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.5.0
     */
    public static function get_base64_ambient_grey_logo() {
        return self::get_base64_ambient_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Ambient logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.5.0
     */
    public static function get_base64_ambient_color_logo() {
        return self::get_base64_ambient_icon('#333B79');
    }


    /**
     * Returns a base64 svg resource for the BloomSky icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.6.0
     */
    public static function get_base64_bloomsky_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 750 750">';
        $source .= '<g transform="translate(130,670) scale(0.065,-0.065)">';
        $source .= '<path style="fill:' . $color . '" d="M3585 8509 c-1085 -63 -2074 -609 -2704 -1494 -356 -500 -579 -1095 -642 -1715 -17 -162 -17 -500 0 -665 83 -835 415 -1558 993 -2160 246 -257 1113 -1010 2217 -1924 l474 -393 51 49 c228 218 540 515 796 758 615 584 1463 1405 1590 1540 530 563 859 1265 957 2040 21 173 24 593 4 765 -95 836 -426 1542 -1001 2134 -546 562 -1253 920 -2045 1035 -117 17 -493 44 -545 39 -8 0 -73 -4 -145 -9z m361 -1808 c279 -58 512 -263 605 -535 32 -92 56 -278 45 -339 l-7 -36 -92 48 c-128 68 -258 118 -390 149 -103 24 -129 27 -322 27 -166 0 -227 -4 -293 -18 -149 -33 -346 -109 -465 -179 -26 -15 -51 -28 -54 -28 -12 0 6 267 22 325 9 33 34 96 55 140 166 341 529 522 896 446z m2084 -2240 c0 -128 -22 -295 -59 -446 -187 -762 -758 -1381 -1497 -1624 -394 -130 -762 -152 -1169 -70 -430 86 -803 281 -1128 589 -419 398 -664 934 -703 1543 l-7 107 2282 0 2281 0 0 -99z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome BloomSky logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.6.0
     */
    public static function get_base64_bloomsky_grey_logo() {
        return self::get_base64_bloomsky_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored BloomSky logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.6.0
     */
    public static function get_base64_bloomsky_color_logo() {
        return self::get_base64_bloomsky_icon('#5DBFDE');
    }


    /**
     * Returns a base64 svg resource for the Weather Underground icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_wug_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 48 48">';
        //$source .= '<g transform="translate(0,9000) scale(0.95,-0.95)">';
        $source .= '<path fill="#D50000" d="M24,6C14.1,6,6,14.1,6,24s8.1,18,18,18s18-8.1,18-18S33.9,6,24,6z M24,10c3.1,0,6,1.1,8.4,2.8L12.8,32.4 C11.1,30,10,27.1,10,24C10,16.3,16.3,10,24,10z M24,38c-3.1,0-6-1.1-8.4-2.8l19.6-19.6C36.9,18,38,20.9,38,24C38,31.7,31.7,38,24,38 z"/>';
        //$source .= '</g>';
        $source .= '</svg>';
        /*$source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="-500 -500 9500 9500">';
        $source .= '<g transform="translate(0,9000) scale(0.95,-0.95)">';
        $source .= '<path style="fill:' . $color . '" d="M6906 8112 c-22 -53 -56 -137 -77 -187 -120 -285 -383 -926 -394 -960 -21 -64 -27 -103 -27 -174 1 -351 332 -602 681 -516 190 46 339 193 391 385 17 65 20 173 5 241 -11 56 -51 164 -98 271 -8 18 -40 94 -70 168 -127 307 -189 458 -211 510 -13 30 -52 124 -87 208 -34 83 -65 152 -68 152 -3 0 -24 -44 -45 -98z"/>';
        $source .= '<path style="fill:' . $color . '" d="M693 6369 c-200 -33 -369 -189 -428 -394 -21 -73 -26 -192 -10 -260 14 -59 75 -218 139 -358 14 -32 26 -61 26 -66 0 -4 6 -21 14 -37 8 -16 40 -92 71 -169 57 -140 69 -170 98 -235 8 -19 35 -84 60 -145 99 -242 169 -411 183 -439 8 -16 14 -32 14 -36 0 -4 10 -29 22 -56 12 -27 23 -51 23 -54 1 -3 7 -18 14 -35 21 -48 45 -105 151 -365 56 -135 105 -254 110 -265 5 -11 45 -107 89 -213 91 -220 112 -259 172 -322 119 -124 254 -176 434 -167 154 7 269 59 376 170 55 58 87 119 189 367 40 96 95 229 122 295 27 66 74 180 105 254 30 74 63 152 73 175 10 23 48 115 85 206 37 91 68 166 70 168 1 1 64 -147 140 -330 76 -183 166 -400 200 -483 34 -82 88 -213 120 -290 90 -220 107 -257 136 -301 101 -150 276 -237 468 -233 185 4 350 89 444 231 19 29 143 316 276 638 133 322 246 594 251 605 5 11 21 49 35 85 15 36 35 84 46 107 10 24 19 45 19 48 0 2 11 28 24 57 13 29 88 207 167 396 79 188 146 342 149 342 3 0 4 -222 3 -493 -1 -536 2 -587 51 -777 120 -475 457 -882 906 -1094 95 -45 224 -92 290 -107 19 -4 44 -9 55 -12 139 -31 401 -43 545 -25 248 32 487 122 706 266 108 71 121 81 237 192 106 100 209 230 282 355 45 76 68 126 136 290 26 65 72 261 64 275 -3 5 -2 11 3 14 15 9 17 118 17 951 0 797 -1 843 -19 895 -44 129 -123 233 -229 301 -168 109 -395 115 -572 14 -72 -41 -175 -150 -213 -226 -62 -126 -62 -116 -62 -975 0 -756 -1 -784 -21 -855 -42 -153 -153 -287 -290 -352 -203 -96 -427 -65 -598 82 -62 53 -132 156 -157 231 -11 30 -22 56 -25 58 -4 2 -7 250 -8 553 -2 591 -6 646 -55 791 -151 449 -544 748 -1001 762 -207 6 -372 -30 -547 -121 -171 -88 -307 -207 -411 -360 -46 -68 -117 -201 -117 -219 0 -5 -25 -65 -50 -119 -5 -11 -71 -168 -146 -350 -75 -181 -150 -363 -166 -404 l-31 -74 -17 44 c-25 63 -358 870 -370 894 -5 11 -29 70 -54 130 -144 357 -171 401 -302 486 -95 61 -191 89 -316 90 -225 1 -417 -131 -507 -349 -16 -37 -83 -200 -150 -362 -67 -162 -128 -308 -135 -325 -8 -16 -66 -158 -131 -315 -64 -157 -118 -286 -120 -288 -1 -1 -26 55 -55 125 -28 71 -59 144 -67 163 -8 19 -19 44 -23 55 -4 11 -13 34 -21 50 -7 17 -13 32 -14 35 -2 7 -19 47 -70 170 -24 58 -83 200 -130 315 -190 460 -199 477 -260 546 -125 140 -324 209 -512 178z"/>';
        $source .= '</g>';
        $source .= '</svg>';*/
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Weather Underground logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_wug_grey_logo() {
        return self::get_base64_wug_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Weather Underground logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_wug_color_logo() {
        return self::get_base64_wug_icon('#077DC2');
    }

    /**
     * Returns a base64 svg resource for the Netatmo icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_netatmo_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 9000 9000">';
        $source .= '<g transform="translate(0,9000) scale(1,-1)">';
        $source .= '<path style="fill:' . $color . '" d="M4381 7078 c-65 -25 -134 -90 -164 -156 l-22 -47 0 -1175 0 -1175 28 -57 c111 -227 443 -227 554 0 l28 57 0 1175 0 1175 -25 50 c-72 147 -244 213 -399 153z"/>';
        $source .= '<path style="fill:' . $color . '" d="M4820 3395 l0 -205 -105 0 -105 0 0 -90 0 -90 105 0 104 0 3 -547 3 -548 27 -47 c44 -74 92 -92 253 -96 75 -2 142 1 158 7 27 10 27 11 27 102 l0 91 -58 -7 c-74 -8 -134 7 -161 42 -21 25 -21 37 -21 514 l0 489 120 0 120 0 0 90 0 90 -120 0 -120 0 -2 203 -3 202 -112 3 -113 3 0 -206z"/>';
        $source .= '<path style="fill:' . $color . '" d="M3030 3360 l0 -200 -95 0 -95 0 0 -50 0 -50 95 0 95 0 0 -562 c0 -357 4 -577 10 -600 14 -48 55 -97 97 -114 33 -14 178 -19 221 -8 19 6 22 13 22 56 0 42 -3 49 -17 44 -10 -3 -43 -8 -73 -11 -64 -7 -117 14 -137 55 -10 19 -13 157 -13 583 l0 557 110 0 110 0 0 50 0 50 -110 0 -110 0 0 200 0 200 -55 0 -55 0 0 -200z"/>';
        $source .= '<path style="fill:' . $color . '" d="M3870 3220 c-142 -22 -238 -86 -289 -193 -30 -61 -51 -156 -51 -229 l0 -38 115 0 115 0 0 38 c0 59 28 139 62 178 42 48 89 66 174 67 57 1 80 -3 120 -24 72 -37 94 -81 94 -189 0 -49 -6 -96 -13 -110 -19 -37 -75 -63 -262 -119 -236 -72 -269 -86 -330 -145 -81 -80 -100 -138 -100 -306 1 -116 4 -143 23 -191 47 -119 143 -190 284 -209 137 -19 266 27 357 126 l50 55 11 -42 c26 -96 65 -119 197 -119 136 0 133 -2 133 91 l0 79 -43 -6 c-79 -11 -75 -36 -79 526 -3 426 -6 502 -20 543 -35 103 -105 168 -218 202 -65 19 -251 28 -330 15z m338 -930 c-3 -142 -5 -159 -28 -203 -74 -145 -278 -206 -379 -114 -43 40 -61 94 -61 187 0 158 48 210 257 279 72 24 148 53 169 66 l39 24 3 -42 c2 -23 2 -112 0 -197z"/>';
        $source .= '<path style="fill:' . $color . '" d="M5910 3217 c-78 -22 -141 -60 -191 -115 l-49 -54 0 71 0 71 -110 0 -110 0 0 -705 0 -705 115 0 115 0 0 513 c0 556 1 573 54 642 15 19 48 47 74 62 40 24 58 28 122 28 91 0 126 -20 162 -93 l23 -47 3 -552 3 -553 114 0 114 0 3 538 c3 532 3 537 25 578 42 78 115 124 208 132 111 8 173 -43 194 -162 7 -37 11 -252 11 -573 l0 -514 113 3 112 3 0 575 c0 565 0 576 -22 645 -39 124 -120 192 -258 216 -158 27 -282 -22 -386 -153 l-37 -48 -13 37 c-16 50 -103 135 -154 151 -60 19 -183 24 -235 9z"/>';
        $source .= '<path style="fill:' . $color . '" d="M7634 3215 c-193 -42 -305 -176 -360 -429 -21 -94 -30 -377 -15 -497 32 -278 140 -454 314 -515 123 -43 291 -37 405 16 80 37 156 114 199 201 119 242 121 735 4 984 -92 198 -308 292 -547 240z m233 -201 c97 -56 143 -183 158 -434 9 -136 -8 -339 -34 -435 -39 -138 -123 -215 -238 -215 -193 0 -274 191 -260 618 7 224 28 321 89 407 65 93 183 117 285 59z"/>';
        $source .= '<path style="fill:' . $color . '" d="M1159 3168 c-78 -28 -144 -79 -188 -144 l-30 -46 -3 88 -3 89 -52 3 -53 3 0 -691 0 -691 53 3 52 3 5 510 c5 496 6 512 27 569 32 83 90 156 154 191 49 27 63 30 144 30 80 0 95 -3 137 -28 26 -15 55 -39 66 -55 47 -67 47 -67 52 -657 l5 -560 53 -3 53 -3 -3 583 -3 583 -26 55 c-37 79 -72 115 -144 151 -89 44 -201 50 -296 17z"/>';
        $source .= '<path style="fill:' . $color . '" d="M2200 3175 c-155 -44 -256 -164 -311 -370 -17 -65 -23 -118 -26 -276 -9 -365 41 -567 169 -685 77 -70 136 -89 273 -89 102 0 115 2 175 31 79 37 145 101 187 180 35 67 63 170 63 232 l0 42 -50 0 c-56 0 -53 4 -68 -91 -29 -193 -164 -309 -344 -297 -188 14 -287 193 -288 521 l0 97 387 0 386 0 -7 126 c-21 392 -149 580 -405 590 -57 3 -104 -1 -141 -11z m206 -100 c134 -39 204 -170 220 -407 l7 -98 -328 0 -328 0 6 88 c22 316 194 485 423 417z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Netatmo logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_netatmo_grey_logo() {
        return self::get_base64_netatmo_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Netatmo logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_netatmo_color_logo() {
        return self::get_base64_netatmo_icon('#57919A');
    }

    /**
     * Returns a base64 svg resource for the colored Netatmo HC logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.1.0
     */
    public static function get_base64_netatmo_hc_color_logo() {
        return self::get_base64_netatmo_icon('#d497a8');
    }

    /**
     * Returns a base64 svg resource for the WeatherLink icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.8.0
     */
    public static function get_base64_weatherlink_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="100%" height="100%" viewBox="0 0 240 240" fill="' . $color . '">';
        $source .= '<g transform="translate(-384,40) scale(0.8,0.8)">';
        $source .= '<polyline points="738.9,43.9 713.5,158.5 718.9,158.5 744.2,43.9 738.9,43.9"/>';
        $source .= '<polygon points="572.3,158.5 587.7,158.5 594.1,129.7 577.8,133.7 572.3,158.5"/>';
        $source .= '<polygon points="607.6,158.5 615.1,124.7 601.5,128 594.7,158.5"/>';
        $source .= '<polygon points="637.4,119.3 625.9,122.1 617.8,158.5 628.7,158.5"/>';
        $source .= '<polygon points="553.7,124.5 564.3,114.9 571,85 564.9,73.8"/>';
        $source .= '<polygon points="597.7,43.9 593.5,63 608.1,66.8 613.1,43.9"/>';
        $source .= '<polyline points="587.1,61.1 590.9,43.9 571.3,43.9 571.6,43.9 568.6,55.9 568.8,56.5 586.5,61.1"/>';
        $source .= '<polyline points="546.7,158.5 565.5,158.5 570.6,135.5 550.2,140.3 546.2,158.5"/>';
        $source .= '<polygon points="633,43.9 620.1,43.9 614.7,68.5 626.9,71.7"/>';
        $source .= '<polyline points="540.3,158.5 544.1,141.8 531.2,144.9 546.4,131.1 560.8,66.3 553.2,52.5 563.3,55.1 565.7,43.9 538.9,43.9 538.3,43.9 512.9,158.5 540.3,158.5"/>';
        $source .= '<polygon points="649.7,158.5 659.6,114 650.3,116.2 641,158.5"/>';
        $source .= '<polygon points="693.4,158.5 706.3,102.7 699,104.5 687,158.5"/>';
        $source .= '<polygon points="701.9,91.3 708.5,93 718.7,43.9 712.4,43.9"/>';
        $source .= '<polygon points="682,108.6 674,110.5 663.4,158.5 670.9,158.5"/>';
        $source .= '<polygon points="675.1,43.9 666.4,43.9 658.4,79.9 666.7,82.1"/>';
        $source .= '<polygon points="643.2,43.9 636.5,74.2 646.8,76.9 654.1,43.9"/>';
        $source .= '<polygon points="688.8,43.9 679.6,85.5 686.7,87.3 696.3,43.9"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome WeatherLink logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.8.0
     */
    public static function get_base64_weatherlink_grey_logo() {
        return self::get_base64_weatherlink_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored WeatherLink logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.8.0
     */
    public static function get_base64_weatherlink_color_logo() {
        return self::get_base64_weatherlink_icon('#1E384A');
    }

    /**
     * Returns a base64 svg resource for the Navionics icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.8.0
     */
    public static function get_base64_navionics_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="100%" height="100%" viewBox="0 0 900 900">';
        $source .= '<g transform="translate(130,-470) scale(2.1,2.1)">';
        $source .= '<path style="fill:' . $color . '" d="M297.5,408,137.18,557.13l-2.39-56.63L64.6,565.91a153,153,0,0,0,54.7,23.88c81.41,17.54,162-34.9,178.94-116.1A152.61,152.61,0,0,0,297.5,408Z"/>';
        $source .= '<path style="fill:' . $color . '" d="M12.17,382.34l85.14-81.25C59.44,315.58,28.47,343.78,12.17,382.34Z"/>';
        $source .= '<path style="fill:' . $color . '" d="M154.68,291.13,0,433.5c.43-7.62,1.47-18.82,3.7-26.63l118.57-113.2A147.36,147.36,0,0,1,154.68,291.13Z"/>';
        $source .= '<path style="fill:' . $color . '" d="M202.63,300.27,5.71,484A143.12,143.12,0,0,1,.8,457.43L176.44,293.05q2.85.48,5.7,1.09A141.09,141.09,0,0,1,202.63,300.27Z"/>';
        $source .= '<path style="fill:' . $color . '" d="M247.09,325.68a149.48,149.48,0,0,0-25.36-17.41l-5.59,44.67Z"/>';
        $source .= '<path style="fill:' . $color . '" d="M274.37,356.13,166.69,459.82l.8-60.62-139,130.33c-6.91-8.7-10.78-16-14.89-25L210.56,321,205,374.47l49.23-42.21A141.79,141.79,0,0,1,274.37,356.13Z"/>';
        $source .= '<path style="fill:' . $color . '" d="M294.31,397.61,144.36,534.8l-2.39-55-85.1,80.49A155.79,155.79,0,0,1,33.8,536.89L158.72,419.94l-.8,60.62L280,364.72A142.07,142.07,0,0,1,294.31,397.61Z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Navionics logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.8.0
     */
    public static function get_base64_navionics_grey_logo() {
        return self::get_base64_navionics_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Navionics logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.8.0
     */
    public static function get_base64_navionics_color_logo() {
        return self::get_base64_navionics_icon('#00529A');
    }

    /**
     * Returns a base64 svg resource for the Weatherflow icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.3.0
     */
    public static function get_base64_weatherflow_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 900 900">';
        $source .= '<g transform="translate(-1580,1450) scale(2.6,-2.6)">';
        $source .= '<path style="fill:' . $color . '" d="m 819.754,424 c -17.16,0.602 -27.164,-8.562 -29.469,-17.453 -2.308,-8.895 4.774,-5.598 4.774,-5.598 24.531,9.879 27.332,-13.34 27.332,-13.34 C 828.316,335.75 703.352,278.453 703.352,278.453 v -0.656 c 36.222,2.633 98.949,25.68 128.75,49.887 27.39,22.25 35.234,46.257 28.156,68.324 -5.938,18.496 -21.735,27.324 -40.504,27.992"/>';
        $source .= '<path style="fill:' . $color . '" d="m 841.629,456.566 c -5.707,17.782 -20.899,26.278 -38.941,26.914 -16.504,0.583 -26.122,-8.234 -28.34,-16.781 -2.219,-8.551 4.593,-5.386 4.593,-5.386 23.586,9.503 26.274,-12.825 26.274,-12.825 5.703,-49.863 -114.453,-104.953 -114.453,-104.953 v -0.633 c 34.824,2.532 95.144,24.696 123.797,47.969 26.336,21.387 33.875,44.481 27.07,65.695"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Weatherflow logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.3.0
     */
    public static function get_base64_weatherflow_grey_logo() {
        return self::get_base64_weatherflow_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Weatherflow logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.3.0
     */
    public static function get_base64_weatherflow_color_logo() {
        return self::get_base64_weatherflow_icon('#1476D6');
    }

    /**
     * Returns a base64 svg resource for the owm icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_loc_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="-500 -500 9500 9500">';
        $source .= '<g transform="translate(1000,8200) scale(0.95,-0.95)">';
        $source .= '<path style="fill:' . $color . '" d="M2745 7123 c-44 -23 -65 -44 -86 -85 -19 -36 -19 -107 -19 -2708 l0 -2670 195 0 195 0 0 1065 c0 586 3 1065 8 1065 4 0 16 -6 27 -14 54 -37 321 -128 440 -151 131 -24 145 -26 275 -32 250 -11 511 59 760 204 134 78 312 237 442 393 192 230 427 447 623 574 33 22 72 48 87 58 15 10 30 18 32 18 3 0 29 15 58 34 162 103 530 264 1017 445 73 28 136 52 138 55 6 5 -27 56 -36 56 -3 0 -24 16 -46 35 -22 19 -82 65 -134 103 -273 194 -553 341 -836 436 -171 58 -229 74 -334 95 -57 12 -106 23 -110 25 -15 9 -306 35 -511 46 -444 22 -734 61 -924 121 -32 11 -63 19 -68 19 -39 0 -357 145 -448 204 -85 56 -196 144 -261 207 -88 86 -199 228 -199 256 0 56 -49 124 -107 149 -43 19 -141 17 -178 -3z"/>';
        $source .= '<path style="fill:' . $color . '" d="M2205 2783 c-264 -58 -442 -116 -585 -190 -227 -117 -391 -267 -475 -432 -119 -238 -92 -504 76 -727 146 -194 386 -335 774 -457 98 -30 344 -74 490 -86 155 -14 558 -14 700 -1 398 38 753 147 1015 314 80 51 265 231 290 281 8 17 22 42 32 56 10 15 32 70 49 124 30 93 31 103 26 219 -4 95 -10 135 -30 188 -106 286 -382 504 -807 637 -89 28 -305 81 -329 81 -8 0 -11 -62 -9 -207 l3 -206 60 -12 c124 -26 321 -97 421 -152 125 -69 238 -174 272 -253 24 -54 20 -158 -9 -216 -64 -133 -240 -255 -494 -342 -138 -47 -253 -72 -450 -97 -433 -55 -911 -15 -1254 106 -238 83 -409 205 -472 336 -26 54 -29 165 -5 215 39 82 151 186 270 252 102 55 293 124 418 149 l63 13 3 207 c3 218 4 211 -43 200z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Netatmo logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_loc_grey_logo() {
        return self::get_base64_loc_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored OpenWeatherMap logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_loc_color_logo() {
        return self::get_base64_loc_icon('#FFA501');
    }

    /**
     * Returns a base64 svg resource for the clientraw.txt icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_raw_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 400 400">';
        $source .= '<g transform="translate(32,24) scale(0.85,0.85)">';
        $source .= '<path style="fill:' . $color . '" d="M 183.3125,43.09375 L 183.3125,83.8125 L 152.71875,66.125 L 137.1875,92.9375 L 183.3125,119.65625 L 183.3125,179.75 L 131.5,149.8125 L 131.40625,96.28125 L 100.40625,96.34375 L 100.46875,131.90625 L 65.09375,111.46875 L 49.59375,138.3125 L 84.875,158.6875 L 54.25,176.3125 L 69.6875,203.1875 L 115.90625,176.59375 L 167.90625,206.625 L 116.09375,236.53125 L 69.6875,209.84375 L 54.25,236.71875 L 85.0625,254.46875 L 49.6875,274.875 L 65.1875,301.71875 L 100.46875,281.34375 L 100.40625,316.6875 L 131.40625,316.75 L 131.5,263.4375 L 183.5,233.4375 L 183.5,293.25 L 137.1875,320.09375 L 152.71875,346.90625 L 183.5,329.09375 L 183.5,369.9375 L 214.5,369.9375 L 214.5,329.21875 L 245.09375,346.90625 L 260.625,320.09375 L 214.5,293.375 L 214.5,233.28125 L 266.3125,263.21875 L 266.40625,316.75 L 297.40625,316.6875 L 297.34375,281.125 L 332.71875,301.5625 L 348.21875,274.71875 L 312.9375,254.34375 L 343.5625,236.71875 L 328.125,209.84375 L 281.9375,236.4375 L 229.90625,206.40625 L 281.75,176.46875 L 328.125,203.1875 L 343.5625,176.3125 L 312.75,158.5625 L 348.125,138.15625 L 332.625,111.3125 L 297.34375,131.6875 L 297.40625,96.34375 L 266.40625,96.28125 L 266.3125,149.59375 L 214.3125,179.59375 L 214.3125,119.78125 L 260.625,92.9375 L 245.09375,66.125 L 214.3125,83.9375 L 214.3125,43.09375 L 183.3125,43.09375 z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome clientraw.txt logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_raw_grey_logo() {
        return self::get_base64_raw_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored clientraw.txt logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_raw_color_logo() {
        return self::get_base64_raw_icon('#2A39CE');
    }

    /**
     * Returns a base64 svg resource for the realtime.txt icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_real_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 96 96">';
        $source .= '<g transform="translate(-11,-11) scale(1.24,1.24)">';
        $source .= '<path style="fill:' . $color . '" d="M30.5,34.7c-1-1-1-2.6,0-3.5c1-1,2.5-1,3.5,0l16.6,13.3l0.9,0.7c1.9,1.9,1.9,5.1,0,7c-1.9,1.9-5.1,1.9-7,0 l-0.7-0.9L30.5,34.7 M48,68.5c10.9,0,19.8-8.9,19.8-19.8c0-5.5-2.2-10.4-5.8-14l3.5-3.5c4.5,4.5,7.3,10.7,7.3,17.5 c0,13.7-11.1,24.8-24.8,24.8S23.2,62.4,23.2,48.7h5C28.2,59.7,37.1,68.5,48,68.5 M48,21.5c2.7,0,5,2.2,5,5s-2.2,5-5,5s-5-2.2-5-5 S45.3,21.5,48,21.5z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome realtime.txt logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_real_grey_logo() {
        return self::get_base64_real_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored realtime.txt logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_real_color_logo() {
        return self::get_base64_real_icon('#A6E22E');
    }

    /**
     * Returns a base64 svg resource for the stickertags icon.
     *
     * @param string $color1 Optional. Color of the icon.
     * @param string $color2 Optional. Color of the icon.
     * @param string $color3 Optional. Color of the icon.
     * @param string $color4 Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_txt_icon($color1='#000', $color4='#000', $color3='#000', $color2='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 1000 1000">';
        $source .= '<g transform="translate(160,160) scale(0.7,0.7)">';
        $source .= '<path style="fill:' . $color1 . '" d="M500,480.1c-20.4,0-40-3.3-55.2-9.2L48.1,316.8c-9.8-3.8-17.8-8.7-23.9-14.5c-9.1-8.7-14.2-20-14.2-31.6c0-9.4,3.7-32.8,38.1-46.1L444.8,70.5c15.2-5.9,34.8-9.2,55.2-9.2c20.3,0,39.9,3.3,55.2,9.2l396.7,154.1c9.8,3.8,17.8,8.7,23.9,14.5c9.1,8.7,14.2,20,14.2,31.6c0,9.4-3.7,32.8-38.1,46.1L555.2,470.9C539.9,476.8,520.3,480.1,500,480.1z M101.6,270.7l365.8,142.1c8,3.1,20.2,4.9,32.6,4.9c12.4,0,24.6-1.9,32.6-4.9l365.8-142.1L532.6,128.6c-8-3.1-20.2-4.9-32.6-4.9c-12.4,0-24.6,1.8-32.6,4.9L101.6,270.7z"/>';
        $source .= '<path style="fill:' . $color2 . '" d="M492.8,938.7c-19.7,0-39.5-3.1-55.2-9.2L40.8,775.4l22.6-58.1l396.7,154.1c17.1,6.6,48.2,6.6,65.2,0l396.7-154.1l22.6,58.1L547.9,929.5C532.2,935.6,512.5,938.7,492.8,938.7z"/>';
        $source .= '<path style="fill:' . $color3 . '" d="M492.8,783.5c-19.7,0-39.5-3.1-55.2-9.2L40.8,620.3l22.6-58.1l396.7,154.1c17.1,6.6,48.2,6.6,65.2,0l396.7-154.1l22.6,58.1L547.9,774.4C532.2,780.5,512.5,783.5,492.8,783.5z"/>';
        $source .= '<path style="fill:' . $color4 . '" d="M492.8,624.8c-19.7,0-39.5-3.1-55.2-9.2L40.8,461.5l22.6-58.1l396.7,154.1c17.1,6.6,48.2,6.6,65.2,0l396.7-154.1l22.6,58.1L547.9,615.6C532.2,621.7,512.5,624.8,492.8,624.8z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome stickertags logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_txt_grey_logo() {
        return self::get_base64_txt_icon('#666666', '#666666', '#666666', '#666666');
    }

    /**
     * Returns a base64 svg resource for the colored stickertags logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.0.0
     */
    public static function get_base64_txt_color_logo() {
        return self::get_base64_txt_icon('#D4161E', '#776666', '#A69999', '#D0CCCC');
    }

    /**
     * Returns a base64 svg resource for the Windy icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_windy_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 900 900">';
        $source .= '<g transform="translate(114,790) scale(0.06,-0.06)">';
        $source .= '<path style="fill:' . $color . '" d="M5355 11234 c-1270 -70 -2432 -532 -3394 -1350 -162 -138 -467 -443 -605 -605 -699 -822 -1141 -1795 -1296 -2854 -41 -284 -54 -469 -54 -805 0 -336 13 -521 54 -805 155 -1059 597 -2032 1296 -2854 138 -162 443 -467 605 -605 822 -699 1795 -1141 2854 -1296 284 -41 469 -54 805 -54 336 0 521 13 805 54 1059 155 2032 597 2854 1296 162 138 467 443 605 605 699 822 1141 1795 1296 2854 41 284 54 469 54 805 0 336 -13 521 -54 805 -155 1059 -597 2032 -1296 2854 -138 162 -443 467 -605 605 -351 298 -711 538 -1109 740 -655 332 -1305 520 -2045 591 -155 15 -627 27 -770 19z m-294 -2670 c78 -23 183 -80 239 -131 66 -60 135 -164 167 -253 37 -105 40 -230 14 -495 -88 -892 -109 -1878 -56 -2575 68 -886 176 -1434 356 -1808 42 -88 52 -101 67 -93 83 45 502 443 682 648 305 347 756 984 1072 1513 382 639 820 1517 994 1990 29 80 53 140 54 134 1 -38 -97 -377 -184 -639 -220 -658 -458 -1230 -791 -1900 -197 -397 -365 -700 -569 -1022 -218 -347 -399 -587 -663 -883 l-130 -145 39 -3 c132 -11 360 102 570 282 87 74 294 305 423 471 536 690 1172 2001 1731 3570 57 160 103 286 104 280 0 -5 -27 -123 -59 -262 -376 -1593 -881 -3014 -1349 -3793 -180 -300 -372 -561 -521 -711 -252 -251 -475 -389 -736 -454 -103 -26 -117 -27 -289 -22 -211 5 -304 25 -474 100 l-103 45 -74 -34 c-93 -43 -224 -86 -335 -110 -125 -27 -368 -25 -510 5 -196 41 -390 134 -590 281 -103 76 -327 299 -432 430 -319 401 -627 1007 -805 1585 -155 503 -366 1588 -558 2870 -79 526 -81 548 -61 648 69 351 415 566 755 471 211 -60 379 -235 425 -444 8 -36 24 -225 36 -420 64 -1071 184 -2263 276 -2750 95 -500 253 -966 444 -1307 169 -303 290 -459 455 -588 135 -105 230 -143 378 -152 53 -3 97 -3 97 1 0 4 -20 40 -44 80 -58 95 -165 309 -215 430 -178 429 -377 1273 -470 2001 -87 678 -124 1308 -125 2140 l-1 550 28 78 c77 219 258 372 481 408 68 10 191 2 257 -17z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Windy logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_windy_grey_logo() {
        return self::get_base64_windy_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Windy logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_windy_color_logo() {
        return self::get_base64_windy_icon('#A32029');
    }

    /**
     * Returns a base64 svg resource for the Thunderforest icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_thunderforest_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 144 144">';
        $source .= '<g transform="translate(18,126) scale(0.075,-0.075)">';
        $source .= '<path style="fill:' . $color . '" d="M575 1425 c-152 -32 -256 -88 -364 -196 -200 -200 -261 -470 -170 -754 45 -138 174 -295 308 -375 226 -134 516 -133 744 2 344 205 451 670 233 1011 -48 74 -158 180 -235 226 -152 90 -346 122 -516 86z m315 -120 c220 -72 343 -195 415 -415 78 -236 -23 -526 -234 -671 -160 -109 -358 -139 -534 -80 -47 16 -108 41 -134 56 -64 37 -181 158 -215 220 -104 195 -104 414 0 610 38 71 164 194 236 229 112 56 182 74 291 75 81 1 115 -4 175 -24z"/>';
        $source .= '<path style="fill:' . $color . '" d="M300 1075 l0 -45 130 0 130 0 2 -337 3 -338 55 0 55 0 3 338 2 337 130 0 130 0 0 45 0 45 -320 0 -320 0 0 -45z"/>';
        $source .= '<path style="fill:' . $color . '" d="M760 580 l0 -380 50 0 50 0 2 178 3 177 153 3 153 3 -3 42 -3 42 -152 3 -153 3 0 109 0 110 170 0 170 0 0 45 0 45 -220 0 -220 0 0 -380z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Thunderforest logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_thunderforest_grey_logo() {
        return self::get_base64_thunderforest_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Thunderforest logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_thunderforest_color_logo() {
        return self::get_base64_thunderforest_icon();
    }

    /**
     * Returns a base64 svg resource for the Stamen icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_stamen_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 20 20">';
        $source .= '<g transform="translate(4.2,3) scale(0.37,0.37)">';
        $source .= '<path style="fill:' . $color . '" d="m 8.2,26.1 c 0.2,1.6 0.7,2.8 1.3,3.5 1.2,1.4 3.2,2.1 6.1,2.1 1.7,0 3.1,-0.2 4.2,-0.6 2.1,-0.7 3.1,-2.1 3.1,-4 0,-1.1 -0.5,-2 -1.5,-2.7 -1,-0.6 -2.6,-1.2 -4.8,-1.6 L 12.9,22.1 C 9.2,21.3 6.7,20.4 5.3,19.4 3,17.8 1.8,15.3 1.8,11.9 1.8,8.8 2.9,6.2 5.2,4.2 c 2.3,-2.1 5.6,-3.1 10,-3.1 3.7,0 6.8,1 9.4,2.9 2.6,1.9 4,4.7 4.1,8.4 l -6.9,0 C 21.7,10.3 20.7,8.8 19,8 17.9,7.4 16.4,7.1 14.7,7.1 12.8,7.1 11.3,7.5 10.2,8.2 9.1,9 8.5,10 8.5,11.3 c 0,1.2 0.6,2.2 1.7,2.8 0.7,0.4 2.2,0.9 4.6,1.4 l 6.1,1.4 c 2.7,0.6 4.6,1.5 6,2.5 2.1,1.6 3.1,4 3.1,7.1 0,3.2 -1.2,5.8 -3.7,7.9 -2.4,2.1 -5.9,3.1 -10.3,3.1 -4.5,0 -8.1,-1 -10.7,-3.1 C 2.7,32.3 1.4,29.5 1.4,25.9 l 6.8,0 z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Stamen logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_stamen_grey_logo() {
        return self::get_base64_stamen_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Stamen logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_stamen_color_logo() {
        return self::get_base64_stamen_icon();
    }

    /**
     * Returns a base64 svg resource for the Mapbox icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_mapbox_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 256 256">';
        $source .= '<g transform="translate(32,32) scale(0.75,0.75)">';
        $source .= '<path style="fill:' . $color . '" d="M128,0 C57.3048369,0 0,57.3048369 0,128 C0,198.695163 57.3048369,256 128,256 C198.695163,256 256,198.695163 256,128 C256,57.3048369 198.695163,0 128,0 Z M188.760405,159.388076 C144.989876,203.158605 66.807649,189.192351 66.807649,189.192351 C66.807649,189.192351 52.6974128,111.154106 96.6119235,67.2395951 C120.944882,42.9066367 161.259843,43.9145107 186.744657,69.2553431 C212.229471,94.5961755 213.093363,135.055118 188.760405,159.388076 L188.760405,159.388076 Z M142.686164,75.0146232 L130.15973,100.787402 L104.386952,113.313836 L130.15973,125.84027 L142.686164,151.613048 L155.212598,125.84027 L180.985377,113.313836 L155.212598,100.787402 L142.686164,75.0146232 Z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Mapbox logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_mapbox_grey_logo() {
        return self::get_base64_mapbox_icon('#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Mapbox logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_mapbox_color_logo() {
        return self::get_base64_mapbox_icon('#4264FB');
    }

    /**
     * Returns a base64 svg resource for the Maptiler icon.
     *
     * @param string $color1 Optional. Color of the icon.
     * @param string $color2 Optional. Color of the icon.
     * @param string $color3 Optional. Color of the icon.
     * @param string $color4 Optional. Color of the icon.
     * @param string $color5 Optional. Color of the icon.
     * @param string $color6 Optional. Color of the icon.
     * @param string $color7 Optional. Color of the icon.
     * @param string $color8 Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_maptiler_icon($color1='#000', $color2='#000', $color3='#000', $color4='#000', $color5='#000', $color6='#000', $color7='#000', $color8='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 2000 2000">';
        $source .= '<g transform="translate(32,32) scale(0.95,0.95)">';
        $source .= '<path style="fill:' . $color1 . '" d="M714.993,1505.704c77.303,77.287,283.078,287.052,282.98,287.265 c-0.751,1.369,204.42-204.633,286.141-286.303l-285.049-285.032L714.993,1505.704z"/>';
        $source .= '<path style="fill:' . $color2 . '" d="M999.064,1221.634l285.049,285.032c0.423-0.474,0.944-1.011,1.401-1.435l284.771-284.788L1285.27,935.429 L999.064,1221.634z"/>';
        $source .= '<path style="fill:' . $color3 . '" d="M1570.285,1220.443l0.732-0.732c157.523-157.509,157.656-412.784,0.474-570.488L1285.27,935.429 L1570.285,1220.443z"/>';
        $source .= '<path style="fill:' . $color4 . '" d="M712.843,935.429L428.82,1219.434c0.082,0.098,0.13,0.195,0.228,0.277l285.472,285.521 c0.163,0.146,0.326,0.295,0.472,0.473l284.072-284.07L712.843,935.429z"/>';
        $source .= '<path style="fill:' . $color5 . '" d="M999.064,649.19l286.205,286.238l286.222-286.206c-0.228-0.164-0.309-0.327-0.474-0.489l-285.503-285.496 c-0.066-0.082-0.163-0.164-0.245-0.245L999.064,649.19z"/>';
        $source .= '<path style="fill:' . $color6 . '" d="M427.614,650.201c-156.122,157.703-155.73,412.035,1.206,569.233l284.022-284.005L427.614,650.201z"/>';
        $source .= '<path style="fill:' . $color7 . '" d="M999.064,649.19L713.819,363.954l-284.771,284.78c-0.489,0.489-0.929,1.01-1.434,1.467l285.229,285.228 L999.064,649.19z"/>';
        $source .= '<path style="fill:' . $color8 . '" d="M1285.27,362.993c-157.719-157.426-413.144-157.345-570.749,0.245l-0.701,0.716L999.064,649.19 L1285.27,362.993z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the monochrome Maptiler logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_maptiler_grey_logo() {
        return self::get_base64_maptiler_icon('#666666', '#666666', '#666666', '#666666', '#666666', '#666666', '#666666', '#666666');
    }

    /**
     * Returns a base64 svg resource for the colored Maptiler logo.
     *
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_maptiler_color_logo() {
        return self::get_base64_maptiler_icon('#3A1888', '#03A1C4', '#05D0DF', '#761FE8', '#FFAA01', '#F1175D', '#FB3A1B', '#FBC935');
    }

    /**
     * Returns a base64 svg resource for the Mapbox icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_marker_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 1000 1000">';
        $source .= '<g transform="translate(0,0) scale(1,1)">';
        $source .= '<path style="fill:' . $color . '" d="M500,10c-192.1,0-347.9,155.8-347.9,347.9c0,78.5,26,151,69.9,209.2l278,422.5l0.2,0.4l0.3-0.4l283.4-430.7c40.3-56.7,64-126.1,64-201C847.9,165.8,692.1,10,500,10z"/>';
        $source .= '<path style="fill:' . $color . '" d="M495.5,128.3c-124.2,0-225.1,101-225.1,225.2s100.9,225.2,225.1,225.2c124.2,0,225.2-101,225.2-225.2S619.7,128.3,495.5,128.3z M495.5,559.1C382,559.1,290,467,290,353.5c0-113.6,92-205.6,205.5-205.6c113.6,0,205.6,92,205.6,205.6C701.1,467.1,609.1,559.1,495.5,559.1z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }

    /**
     * Returns a base64 svg resource for the Mapbox icon.
     *
     * @param string $color Optional. Color of the icon.
     * @return string The svg resource as a base64.
     * @since 3.7.0
     */
    public static function get_base64_pin_icon($color='#000') {
        $source =  '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill="none" width="100%" height="100%"  viewBox="0 0 1200 1200">';
        $source .= '<g transform="translate(180,1280) scale(0.1,-0.1)">';
        $source .= '<path style="fill:' . $color . '" d="M3855 12789 c-555 -44 -1043 -176 -1530 -414 -1457 -712 -2370 -2223 -2322 -3840 19 -605 152 -1155 406 -1680 109 -225 183 -353 331 -575 65 -96 856 -1369 1760 -2827 903 -1459 1646 -2653 1650 -2653 4 0 747 1194 1650 2652 904 1459 1695 2732 1760 2828 148 222 222 350 331 575 421 869 520 1869 279 2821 -244 958 -822 1795 -1640 2371 -696 491 -1551 759 -2404 752 -94 -1 -216 -5 -271 -10z m635 -1764 c440 -80 813 -271 1120 -575 769 -761 825 -1980 130 -2812 -335 -402 -817 -663 -1344 -728 -114 -14 -378 -14 -492 0 -853 105 -1550 715 -1764 1544 -141 545 -52 1136 243 1613 330 531 862 876 1497 968 130 19 481 13 610 -10z"/>';
        $source .= '</g>';
        $source .= '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($source);
    }
}