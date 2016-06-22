<?php

/**
 * Outputing / shortcoding functionalities for Live Weather Station plugin
 *
 * @since      1.0.0
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/includes
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 */

require_once(LWS_INCLUDES_DIR.'trait-unit-description.php');
require_once(LWS_INCLUDES_DIR.'trait-type-description.php');
require_once(LWS_INCLUDES_DIR.'trait-datetime-conversion.php');
require_once(LWS_INCLUDES_DIR.'trait-unit-conversion.php');
require_once(LWS_INCLUDES_DIR.'trait-datas-query.php');

trait Datas_Output {
    
    use Unit_Description, Type_Description, Datetime_Conversion, Unit_Conversion, Datas_Query;

    private $unit_espace = '&nbsp;';

    /**
     * Get value for LCD panel shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    1.0.0
     * @access   public
     */
    public function lcd_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','design' => '','size' => '','speed' => ''), $attributes );
        $fingerprint = uniqid('', true);
        $uniq = 'lcd'.substr ($fingerprint, count($fingerprint)-6, 80);
        $name = $this->get_station_name($_attributes['device_id']);
        $scalable='false';
        if ($_attributes['size'] == 'scalable') {
            $_attributes['size'] = 'small';
            $scalable='true';
        }
        if (is_array($name)) {
            return __(LWS_PLUGIN_NAME, 'live-weather-station').' - '.$name['condition']['message'];
        }
        $name = substr($name, 0, 20);
        wp_enqueue_script('lws-lcd.js',false, array(),$this->version);
        $result  = '<div id="'.$uniq.'"></div>'.PHP_EOL;
        $result .= '<script language="javascript" type="text/javascript">'.PHP_EOL;
        $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
        $result .= '    var c'.$uniq.' = new lws_lcd.LCDPanel({'.PHP_EOL;
        $result .= '                    id              : "id'.$uniq.'",'.PHP_EOL;
        $result .= '                    parentId        : "'.$uniq.'",'.PHP_EOL;
        $result .= '                    upperCenterText : "'.$name.'",'.PHP_EOL;
        $result .= '                    qDevice         : "'.$_attributes['device_id'].'",'.PHP_EOL;
        $result .= '                    qModule         : "'.$_attributes['module_id'].'",'.PHP_EOL;
        $result .= '                    qMeasure        : "'.$_attributes['measure_type'].'",'.PHP_EOL;
        $result .= '                    qPostUrl        : "'.LWS_AJAX_URL.'",'.PHP_EOL;
        $result .= '                    design          : "'.$_attributes['design'].'",'.PHP_EOL;
        $result .= '                    size            : "'.$_attributes['size'].'",'.PHP_EOL;
        $result .= '                    scalable        : '.(string)$scalable.','.PHP_EOL;
        $result .= '                    cycleSpeed      : "'.$_attributes['speed'].'"'.PHP_EOL;
        $result .= '    });'.PHP_EOL;
        $result .= '  });'.PHP_EOL;
        $result .= '</script>'.PHP_EOL;
        return $result;
    }

    /**
     * Stringify an array of labels.
     *
     * @return  string  $attributes The attributes of the value queryed by the shortcode.
     * @since    2.1.0
     */
    private function get_txt($source, $val) {
        $r = '';
        if (count($source) > 0 && $source[0] != 'none') {
            foreach ($source as $s) {
                if (strlen($r) > 0) {
                    if ($s == 'unit') {$b_sep = ' ('; $e_sep = ')';}
                    else { $b_sep = ' - '; $e_sep = '';}
                }
                else { $b_sep = ''; $e_sep = '';}
                $r = $r . $b_sep . $val[$s] . $e_sep;
            }
        }
        return $r;
    }

    /**
     * Get value for clean gauge element.
     *
     * @return  string  $attributes The value queryed.
     * @since    2.1.0
     */
    public function justgage_value($attributes, $full=false)
    {
        $_attributes = shortcode_atts(array('device_id' => '', 'module_id' => '', 'measure_type' => '', 'element' => '', 'format' => ''), $attributes);
        $_result = $this->get_line_datas($_attributes, false, true);
        $result = array();
        $val = array();
        if (count($_result) > 0) {
            foreach ($_result as $line) {
                if ($line['measure_type'] == $_attributes['measure_type']) {
                    $val = $line;
                }
            }
        }
        if (empty($val)) {
            $value = 0;
            $precision = 0;
            $min = 0;
            $max = 0;
            if ($full) {
                $result['type'] = $this->get_measurement_type($_attributes['measure_type']);
                $result['shorttype'] = $this->get_measurement_type($_attributes['measure_type'], true);
                $result['unit'] = $this->output_unit($_attributes['measure_type'])['unit'];
                $_attributes['measure_type'] = 'sos';
                $_result = $this->get_line_datas($_attributes, false, true);
                $master = $_result[0];
                if (count($_result) > 0) {
                    $result['station'] = $master['device_name'];
                    $result['module'] = $master['module_name'];
                } else {
                    $result['station'] = '';
                    $result['module'] = '';
                }
            }
        }
        else {
            $master = $_result[0];
            if (count($_result) > 1) {
                foreach ($_result as $line) {
                    if ($line['measure_type'] == $_attributes['measure_type']) {
                        $master = $line;
                    }
                }
            }
            $module_type = $master['module_type'];
            $measure_type = $master['measure_type'];
            $value = $this->output_value($master['measure_value'], $measure_type);
            $precision = $this->decimal_for_output($measure_type, $value);
            if ($precision < 2) {
                $prec = 0;
            }
            else {
                $prec = 1;
            }
            $min = round($this->output_value($this->get_measurement_min($measure_type, $module_type), $measure_type), $prec);
            $max = round($this->output_value($this->get_measurement_max($measure_type, $module_type), $measure_type), $prec);
            if ($measure_type == 'temperature' && (get_option('live_weather_station_settings')[7] == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'temperature_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'temperature_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 3 ;
                    if (strtolower($module_type)=='namodule1' || strtolower($module_type)=='nacurrent') {
                        $delta = 6 ;
                    }
                    if ($min <= $max) {
                        $min = round($this->output_value($min - $delta, $measure_type));
                        $max = round($this->output_value($max + $delta, $measure_type));
                    }
                }
            }
            if ($full) {
                $result['station'] = $master['device_name'];
                $result['module'] = $master['module_name'];
                $result['type'] = $this->get_measurement_type($measure_type, false, $module_type);
                $result['shorttype'] = $this->get_measurement_type($measure_type, true, $module_type);
                $result['unit'] = $this->output_unit($measure_type, $module_type)['unit'];
            }
        }
        $result['value'] = round($value, $precision);
        $result['decimals'] = $precision;
        $result['min'] = $min;
        $result['max'] = $max;
        if ($full && substr($result['module'], 0, 1) == '[') {
            $result['module'] = __('Outdoor', 'live-weather-station');
        }
        return $result;
    }


    /**
     * Get attributes for clean gauge shortcodes.
     *
     * @return  string  $attributes The attributes of the value queryed by the shortcode.
     * @since    2.1.0
     */
    public function justgage_attributes($attributes) {
        $result = array();
        $result['id'] = $attributes['id'];

        // POINTER
        $pointerOptions = array();
        $result['pointer'] = ($attributes['pointer'] != 'none');
        switch ($attributes['pointer']) {
            case 'external':
                $pointerOptions['toplength'] = 4;
                break;
            case 'internal':
                $pointerOptions['toplength'] = -4;
                break;
        }

        // SIZE
        $pb = array();
        switch ($attributes['size']) {
            case 'micro':
                $result['width'] = 75;
                $result['height'] = 75;
                $result['relativeGaugeSize'] = false;
                $result['hideMinMax'] = true;
                break;
            case 'small':
                $result['width'] = 100;
                $result['height'] = 100;
                $result['relativeGaugeSize'] = false;
                $pointerOptions['bottomwidth'] = 2 ;
                $pb['thin']['external'] = -10;
                $pb['thin']['internal'] = 6;
                $pb['standard']['external'] = -14;
                $pb['standard']['internal'] = 2;
                $pb['fat']['external'] = -20;
                $pb['fat']['internal'] = -4;
                $pb['pie']['external'] = -48;
                $pb['pie']['internal'] = -32;
                $pb['full']['external'] = -44;
                $pb['full']['internal'] = -27;
                break;
            case 'medium':
                $result['width'] = 225;
                $result['height'] = 225;
                $result['relativeGaugeSize'] = false;
                $pointerOptions['bottomwidth'] = 5 ;
                $pb['thin']['external'] = -17;
                $pb['thin']['internal'] = 7;
                $pb['standard']['external'] = -26;
                $pb['standard']['internal'] = -1;
                $pb['fat']['external'] = -39;
                $pb['fat']['internal'] = -15;
                $pb['pie']['external'] = -103;
                $pb['pie']['internal'] = -78;
                $pb['full']['external'] = -93;
                $pb['full']['internal'] = -68;
                break;
            case 'large':
                $result['width'] = 350;
                $result['height'] = 350;
                $pointerOptions['bottomwidth'] = 8 ;
                $pb['thin']['external'] = -24;
                $pb['thin']['internal'] = 0;
                $pb['standard']['external'] = -38;
                $pb['standard']['internal'] = -3;
                $pb['fat']['external'] = -59;
                $pb['fat']['internal'] = -25;
                $pb['pie']['external'] = -158;
                $pb['pie']['internal'] = -124;
                $pb['full']['external'] = -142;
                $pb['full']['internal'] = -108;
                break;
            case 'scalable':
                $result['relativeGaugeSize'] = true;
                $pointerOptions['bottomwidth'] = 5 ;
                $pb['thin']['external'] = -16;
                $pb['thin']['internal'] = 8;
                $pb['standard']['external'] = -23;
                $pb['standard']['internal'] = -1;
                $pb['fat']['external'] = -34;
                $pb['fat']['internal'] = -11;
                $pb['pie']['external'] = -87;
                $pb['pie']['internal'] = -64;
                $pb['full']['external'] = -65;
                $pb['full']['internal'] = -41;
                break;
        }

        // DESIGN
        $design = explode('-',$attributes['design']);
        $result['donut'] = (in_array('full', $design));
        if (in_array('thin', $design)) {
            $result['gaugeWidthScale'] = 0.15;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['thin'][$attributes['pointer']];
            }
            if ($attributes['size'] == 'large' && $attributes['pointer'] == 'internal') {
                $pointerOptions['toplength'] = -8;
                $pointerOptions['bottomlength'] = 13;
            }
        }
        if (in_array('standard', $design)) {
            $result['gaugeWidthScale'] = 0.40;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['standard'][$attributes['pointer']];
            }
            if ($attributes['size'] == 'small' && $attributes['pointer'] == 'internal') {
                $pointerOptions['toplength'] = -6;
                $pointerOptions['bottomlength'] = 4;
            }
            if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'internal') {
                $pointerOptions['toplength'] = -2;
            }
            if ($result['donut']) {
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'internal') {
                    $pointerOptions['bottomlength'] = -1;
                    $pointerOptions['toplength'] = 0;
                }
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'external') {
                    $pointerOptions['bottomlength'] = -21;
                }
            }
        }
        if (in_array('fat', $design)) {
            $result['gaugeWidthScale'] = 0.80;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['fat'][$attributes['pointer']];
            }
            if ($result['donut']) {
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'internal') {
                    $pointerOptions['bottomlength'] = -6;
                }
                if ($attributes['size'] == 'scalable' && $attributes['pointer'] == 'external') {
                    $pointerOptions['bottomlength'] = -30;
                }
            }
        }
        if (in_array('pie', $design)) {
            $result['gaugeWidthScale'] = ($result['donut'] ? 2.38 : 2.68);
            $result['hideMinMax'] = true;
            if ($result['pointer']) {
                $pointerOptions['bottomlength'] = $pb['pie'][$attributes['pointer']];
                if ($result['donut']) {
                    $pointerOptions['bottomlength'] = $pb['full'][$attributes['pointer']];
                }
            }
        }
        $result['shadowOpacity'] = (in_array('flat', $design) ? 0 : $result['gaugeWidthScale']/2.5);
        $result['counter'] = 0;

        // COLORS
        $color = explode('-',$attributes['color']);
        // text
        if (in_array('lgt', $color)) {
            $result['titleFontColor'] = '#999999';
            $result['valueFontColor'] = '#010101';
            $result['labelFontColor'] = '#B3B3B3';
        }
        if (in_array('drk', $color)) {
            $result['titleFontColor'] = '#999999';
            if (in_array('pie', $design)) {
                $result['valueFontColor'] = '#333333';
            }
            else {
                $result['valueFontColor'] = '#FEFEFE';
            }
            if ($result['donut']) {
                $result['labelFontColor'] = '#B3B3B3';
            }
            else {
                $result['labelFontColor'] = '#777777';
            }
        }
        if (in_array('transparent', $color)) {
            $result['levelColors'] = ['#EDEBEB'];
        }
        if (in_array('flag', $color)) {
            $result['levelColors'] = ['#0C58AC', '#BCAEFA', '#C30404'];
        }
        if (in_array('pinky', $color)) {
            $result['levelColors'] = ['#970000', '#A100BE', '#FF18EC'];
        }
        if (in_array('aquamarine', $color)) {
            $result['levelColors'] = ['#4E61C3', '#2D7EF7', '#00EDF0'];
        }
        if (in_array('bw', $color)) {
            $result['levelColors'] = ['#EDEBEB', '#000000'];
        }
        if (in_array('solidred', $color)) {
            $result['levelColors'] = ['#C30404'];
        }
        if (in_array('solidorange', $color)) {
            $result['levelColors'] = ['#FE9500'];
        }
        if (in_array('solidyellow', $color)) {
            $result['levelColors'] = ['#F1EE12'];
        }
        if (in_array('solidgreen', $color)) {
            $result['levelColors'] = ['#00C312'];
        }
        if (in_array('solidblue', $color)) {
            $result['levelColors'] = ['#0C58AC'];
        }
        if (in_array('solidpurple', $color)) {
            $result['levelColors'] = ['#6600B4'];
        }
        if (in_array('solidblack', $color)) {
            $result['levelColors'] = ['#000000'];
        }
        $pointerOptions['color'] = $result['valueFontColor'] ;
        if (in_array('lgt', $color)) {
            $pointerOptions['color'] = '#333333' ;
        }
        if (in_array('drk', $color) && $attributes['pointer'] == 'internal') {
            switch ($attributes['size']) {
                case 'small':
                    if (in_array('fat', $design)) {
                        $pointerOptions['color'] = '#333333' ;
                    }
                    break;
                case 'medium':
                case 'large':
                case 'scalable':
                    if (in_array('standard', $design) || in_array('fat', $design)) {
                        $pointerOptions['color'] = '#333333' ;
                    }
                    break;
            }
        }
        if (in_array('drk', $color) && $attributes['pointer'] == 'external' && in_array('pie', $design)) {
            $pointerOptions['color'] = '#FEFEFE' ;
        }
        $values = $this->justgage_value($attributes, true);

        // DATAS
        $result['value'] = $values['value'];
        if ($result['donut']) {
            $result['min'] = 0;
        }
        else {
            $result['min'] = $values['min'];
        }
        $result['max'] = $values['max'];
        $result['decimals'] = $values['decimals'];

        // TEXTS
        if ($attributes['unit'] == 'unit') {
            $result['symbol'] = $values['unit'];
        }
        $result['title'] = $this->get_txt(explode('-',$attributes['title']), $values);
        $result['label'] = $this->get_txt(explode('-',$attributes['subtitle']), $values);
        $result['counter'] = true;
        if ($result['pointer']) {
            $result['pointerOptions'] = $pointerOptions;
        }
        if (!$result['donut']) {
            unset($result['donut']);
        }
        return $result;
    }


    /**
     * Get value for clean gauge shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    2.1.0
     */
    public function justgage_shortcodes($attributes) {
        $fingerprint = uniqid('', true);
        $uniq = 'jgg'.substr ($fingerprint, count($fingerprint)-6, 80);
        $time = 1000 * (120 + rand(-20, 20));
        $_attributes = shortcode_atts( array('id' => $uniq,'device_id' => '','module_id' => '','measure_type' => '','design' => '','color' => '','pointer' => '','title' => '','subtitle' => '','unit' => '','size' => ''), $attributes );
        $sc_device = $_attributes['device_id'];
        $sc_module = $_attributes['module_id'];
        $sc_measurement = $_attributes['measure_type'];
        $values = json_encode($this->justgage_attributes($_attributes));
        switch ($attributes['size']) {
            case 'small':
                $h = '100px';
                $w = '100px';
                break;
            case 'medium':
                $h = '225px';
                $w = '225px';
                break;
            case 'large':
                $h = '350px';
                $w = '350px';
                break;
            case 'scalable':
                $h = '100%';
                $w = '100%';
                break;
        }
        $style = 'width:'.$w.';height:'.$h.';';
        wp_enqueue_script('justgage.js',false, array(),$this->version);
        $result  = '<div id="'.$uniq.'" style="'.$style.'"></div>'.PHP_EOL;
        $result .= '<script language="javascript" type="text/javascript">'.PHP_EOL;
        $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
        $result .= '    var g'.$uniq.' = new JustGage('.$values.');'.PHP_EOL;
        $result .= '  setInterval(function() {'.PHP_EOL;
        $result .= '    var http = new XMLHttpRequest();'.PHP_EOL;
        $result .= '    var params = "action=lws_query_justgage_datas";'.PHP_EOL;
        $result .= '    params = params+"&device_id='.$sc_device.'";'.PHP_EOL;
        $result .= '    params = params+"&module_id='.$sc_module.'";'.PHP_EOL;
        $result .= '    params = params+"&measure_type='.$sc_measurement.'";'.PHP_EOL;
        $result .= '    http.open("POST", "'.LWS_AJAX_URL.'", true);'.PHP_EOL;
        $result .= '    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");'.PHP_EOL;
        $result .= '    http.onreadystatechange = function () {'.PHP_EOL;
        $result .= '      if (http.readyState == 4 && http.status == 200) {'.PHP_EOL;
        $result .= '        var odatas = JSON.parse(http.responseText);'.PHP_EOL;
        $result .= '        if ( typeof odatas != "undefined") {'.PHP_EOL;
        $result .= '          g'.$uniq.'.refresh(odatas.value, odatas.max);'.PHP_EOL;
        $result .= '        }'.PHP_EOL;
        $result .= '      }'.PHP_EOL;
        $result .= '    }'.PHP_EOL;
        $result .= '    http.send(params);'.PHP_EOL;
        $result .= '  }, '.$time.');'.PHP_EOL;
        $result .= '});'.PHP_EOL;
        $result .= '</script>'.PHP_EOL;
        return $result;
    }

    /**
     * Get value for steel meter element.
     *
     * @return  string  $attributes The value queryed.
     * @since    2.2.0
     */
    public function steelmeter_value($attributes, $full=false)
    {
        $_attributes = shortcode_atts(array('device_id' => '', 'module_id' => '', 'measure_type' => '', 'element' => '', 'format' => ''), $attributes);
        $result = array();
        $value = 0;
        $min = 0;
        $max = 0;
        $precision = 0;
        $value_min = -9999;
        $value_max = -9999;
        $value_trend = 'steady';
        $value_aux = -9999;
        $alarm = false;
        $measure_type = $_attributes['measure_type'];
        $_result = $this->get_line_datas($_attributes, false, true);
        $val = array();
        if (count($_result) > 0) {
            foreach ($_result as $line) {
                if ($line['measure_type'] == $_attributes['measure_type']) {
                    $val = $line;
                }
            }
        }
        if (empty($val)) {
            $result['type'] = $this->get_measurement_type($_attributes['measure_type']);
            $result['unit'] = $this->output_unit($_attributes['measure_type'])['unit'];
            $_result = $this->get_line_datas($_attributes, false, true);
            if (!empty($_result)) {
                $master = $_result[0];
                $module_type = $master['module_type'];
                $measure_type = $master['measure_type'];
                $result['unit'] = $this->output_unit($measure_type, $module_type)['unit'];
                $alarm = true;
                $precision = $this->decimal_for_output($measure_type);
                if ($precision < 2) {
                    $prec = 0;
                }
                else {
                    $prec = 1;
                }
                $min = round($this->output_value($this->get_measurement_min($measure_type, $module_type), $measure_type), $prec);
                $max = round($this->output_value($this->get_measurement_max($measure_type, $module_type), $measure_type), $prec);
                $value = $min;
            }
        }
        else {
            $master = $_result[0];
            if (count($_result) > 1) {
                foreach ($_result as $line) {
                    if ($line['measure_type'] == $_attributes['measure_type']) {
                        $master = $line;
                    }
                    if ($line['measure_type'] == 'temperature_min') {
                        $value_min = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'temperature_max') {
                        $value_max = $this->output_value($line['measure_value'], $measure_type);
                    }
                    if ($line['measure_type'] == 'temperature_trend' || $line['measure_type'] == 'pressure_trend') {
                        $value_trend = strtolower($line['measure_value']);
                        if ($value_trend == 'stable') {
                            $value_trend = 'steady';
                        }
                    }
                    if ($line['measure_type'] == 'gustangle') {
                        $value_aux = $this->output_value($line['measure_value'], $measure_type);
                    }
                }
            }
            $module_type = $master['module_type'];
            $measure_type = $master['measure_type'];
            $result['unit'] = $this->output_unit($measure_type, $module_type)['unit'];
            $result['type'] = $this->get_measurement_type($measure_type, true, $module_type);
            $value = $this->output_value($master['measure_value'], $measure_type);
            $alarm = $this->is_alarm_on($master['measure_value'], $measure_type);
            $precision = $this->decimal_for_output($measure_type, $value);
            if ($precision < 2) {
                $prec = 0;
            }
            else {
                $prec = 1;
            }
            $min = round($this->output_value($this->get_measurement_min($measure_type, $module_type), $measure_type), $prec);
            $max = round($this->output_value($this->get_measurement_max($measure_type, $module_type), $measure_type), $prec);
            if ($measure_type == 'temperature' && (get_option('live_weather_station_settings')[7] == 1)) {
                $min_t = array();
                $max_t = array();
                foreach ($_result as $line) {
                    if ($line['measure_type'] == 'temperature_min') {
                        $min_t = $line;
                    }
                    if ($line['measure_type'] == 'temperature_max') {
                        $max_t = $line;
                    }
                }
                if (!empty($min_t) && !empty($max_t)) {
                    $min = $min_t['measure_value'];
                    $max = $max_t['measure_value'];
                    $delta = 3 ;
                    if (strtolower($module_type)=='namodule1' || strtolower($module_type)=='nacurrent') {
                        $delta = 6 ;
                    }
                    if ($min <= $max) {
                        $min = round($this->output_value($min - $delta, $measure_type));
                        $max = round($this->output_value($max + $delta, $measure_type));
                    }
                }
            }
        }
        if ($full) {
            $result['decimals'] = $precision;
            $result['min'] = $min;
            $result['max'] = $max;
            if ($result['max'] <= $result['min']) {
                $result['max'] = $result['min'] +1;
            }
        }
        $result['value'] = round($value, $precision);
        $result['value_min'] = $value_min;
        $result['value_max'] = $value_max;
        $result['value_trend'] = $value_trend;
        $result['value_aux'] = ($value_aux != -9999 ? $value_aux : $result['value'] );
        $result['alarm'] = $alarm;
        return $result;
    }


    /**
     * Get attributes for steel meter shortcodes.
     *
     * @return  string  $attributes The attributes of the value queryed by the shortcode.
     * @since    2.2.0
     */
    public function steelmeter_attributes($attributes) {
        require_once(LWS_INCLUDES_DIR.'class-colors-manipulation.php');
        $result = array();
        $values = $this->steelmeter_value($attributes, true);
        $result['minValue'] = $values['min'];
        $result['maxValue'] = $values['max'];
        $min = $values['min'];
        $max = $values['max'];
        if (strpos($attributes['design'], 'windcompass') !== false ) {
            $min = 0;
            $max = 360;
        }
        $digital = (strpos($attributes['design'], 'digital') > -1 );
        if (strpos($attributes['design'], '-1-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE1';
        }
        if (strpos($attributes['design'], '-2-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE2';
        }
        if (strpos($attributes['design'], '-3-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE3';
        }
        if (strpos($attributes['design'], '-4-4') !== false ) {
            $result['gaugeType'] = 'steelseries.GaugeType.TYPE4';
        }
        if (strpos($attributes['design'], '-left') !== false ) {
            $result['orientation'] = 'steelseries.Orientation.WEST';
        }
        if (strpos($attributes['design'], '-right') !== false ) {
            $result['orientation'] = 'steelseries.Orientation.EAST';
        }
        if (strpos($attributes['design'], 'windcompass-modern') !== false ) {
            $result['pointSymbolsVisible'] = false;
        }
        $result['frameDesign'] = 'steelseries.FrameDesign.'.$attributes['frame'];
        $result['backgroundColor'] = 'steelseries.BackgroundColor.'.$attributes['background'];
        if ($attributes['orientation'] != 'AUTO') {
            $result['tickLabelOrientation'] = 'steelseries.TickLabelOrientation.'.$attributes['orientation'];
        }
        $result['pointerType'] = 'steelseries.PointerType.'.$attributes['main_pointer_type'];
        $result['pointerTypeLatest'] = 'steelseries.PointerType.'.$attributes['main_pointer_type'];
        $result['pointerColor'] = 'steelseries.ColorDef.'.$attributes['main_pointer_color'];
        $result['pointerTypeAverage'] = 'steelseries.PointerType.'.$attributes['aux_pointer_type'];
        $result['pointerColorAverage'] = 'steelseries.ColorDef.'.$attributes['aux_pointer_color'];
        $knob = explode('-',$attributes['knob']);
        $result['knobType'] = 'steelseries.KnobType.'.$knob[0];
        $result['knobStyle'] = 'steelseries.KnobStyle.'.$knob[1];
        if ($attributes['lcd'] == 'NONE') {
            $result['lcdVisible'] = false;
        }
        else {
            $result['lcdColor'] = 'steelseries.LcdColor.'.$attributes['lcd'];
        }
        if ($attributes['alarm'] != 'NONE') {
            $result['userLedVisible'] = true;
            $result['userLedColor'] = 'steelseries.LedColor.'.$attributes['alarm'];
        }
        if ($attributes['trend'] != 'NONE') {
            $result['trendVisible'] = true;
            $result['trendColors'] = '[steelseries.LedColor.'.$attributes['trend'].', steelseries.LedColor.'.$attributes['trend'].', steelseries.LedColor.'.$attributes['trend'].']';
        }
        if ($attributes['minmax'] == 'cursor') {
            $result['minMeasuredValueVisible'] = true;
            $result['maxMeasuredValueVisible'] = true;
        }
        if ($attributes['index_style'] != 'NONE') {
            $style = explode('-', $attributes['index_style']);
            $tcolor = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
            $alpha = 0;
            $lighten = 1;
            if (isset($style[1])) {
                switch (strtolower($style[1])) {
                    case 'translucent':
                        $alpha = 0.1;
                        if ($tcolor->isVeryDark()) {
                            $lighten = 40;
                        }
                        else {
                            $lighten = 20;
                        }
                        break;
                    case 'liquid':
                        $alpha = 0.25;
                        if ($tcolor->isVeryDark()) {
                            $lighten = 28;
                        }
                        else {
                            $lighten = 15;
                        }
                        break;
                    case 'soft':
                        $alpha = 0.5;
                        if ($tcolor->isVeryDark()) {
                            $lighten = 15;
                        }
                        else {
                            $lighten = 10;
                        }
                        break;
                    case 'hard':
                        $alpha = 0.9;
                        $lighten = 1;
                        break;
                }
            }
            if (isset($style[0])) {
                switch (strtolower($style[0])) {
                    case 'fixed':
                        if ($digital) {
                            $color = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
                            $rgb1 = Colors_Manipulation::hexToRgb($color->lighten($lighten));
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $rgb = Colors_Manipulation::nameToRgb($attributes['index_color']);
                            $result['section'] = '[steelseries.Section('.$min.', '.$max.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$alpha.')")]';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'fadein':
                        if ($digital) {
                            $color = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
                            $rgb1 = Colors_Manipulation::hexToRgb($color->lighten($lighten*2));
                            $rgb2 = Colors_Manipulation::nameToRgb($attributes['index_color']);
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $rgb = Colors_Manipulation::nameToRgb($attributes['index_color']);
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $a = $alpha - $alpha * ($step - $i) * 0.9 / $step;
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$a.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'fadeout':
                        if ($digital) {
                            $color = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
                            $rgb2 = Colors_Manipulation::hexToRgb($color->lighten($lighten*2));
                            $rgb1 = Colors_Manipulation::nameToRgb($attributes['index_color']);
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $rgb = Colors_Manipulation::nameToRgb($attributes['index_color']);
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $a = $alpha - $alpha * $i * 0.9 / $step;
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$a.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'complementary':
                        if ($digital) {
                            $color = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
                            $rgb1 = Colors_Manipulation::nameToRgb($attributes['index_color']);
                            $rgb2 = Colors_Manipulation::hexToRgb($color->complementary());
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $color2 = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
                            $color = new Colors_Manipulation($color2->complementary());
                            $rgb2 = $color->complementary();
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $rgb = Colors_Manipulation::hexToRgb($color->mix($rgb2, round((200 / $step) * $i)-100));
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$alpha.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                    case 'invcomplementary':
                        if ($digital) {
                            $color = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
                            $rgb2 = Colors_Manipulation::nameToRgb($attributes['index_color']);
                            $rgb1 = Colors_Manipulation::hexToRgb($color->complementary());
                            $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb1['R'].','.$rgb1['G'].','.$rgb1['B'].', 1), new steelseries.rgbaColor('.$rgb2['R'].','.$rgb2['G'].','.$rgb2['B'].', 1)])';
                            $result['useValueGradient'] = true;
                        }
                        else {
                            $color = new Colors_Manipulation(Colors_Manipulation::nameToHex($attributes['index_color']));
                            $rgb2 = $color->complementary();
                            $s = '';
                            $step = 20 ;
                            $size = ($max - $min) / $step;
                            for ($i = 0; $i < $step; $i++) {
                                $rgb = Colors_Manipulation::hexToRgb($color->mix($rgb2, round((200 / $step) * $i)-100));
                                $mi =  ($min + ($i*$size));
                                $ma =  ($min + (($i+1)*$size));
                                $s = $s . 'steelseries.Section('.$mi.', '.$ma.', "rgba('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', '.$alpha.')"),';
                            }
                            $result['section'] = '['.substr($s, 0, strlen($s)-1).']';
                            $result['useSectionColors'] = true;
                        }
                        break;
                }
            }
        }
        else {
            if ($digital) {
                $rgb = Colors_Manipulation::nameToRgb('antiquewhite');
                $result['valueGradient'] = 'new steelseries.gradientWrapper('.$min.', '.$max.', [0,1], [new steelseries.rgbaColor('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', 1), new steelseries.rgbaColor('.$rgb['R'].','.$rgb['G'].','.$rgb['B'].', 1)])';
                $result['useValueGradient'] = true;
            }
        }
        $result['foregroundType'] = 'steelseries.ForegroundType.'.$attributes['glass'];
        switch ($attributes['size']) {
            case 'small':
                $result['size'] = 150;
                break;
            case 'medium':
                $result['size'] = 200;
                break;
            case 'large':
                $result['size'] = 250;
                break;
            case 'macro':
                $result['size'] = 300;
                break;
        }
        $result['thresholdVisible'] = false;
        $result['ledVisible'] = false;
        $result['niceScale'] = false;
        if ($values['decimals'] > 1) {
            $result['labelNumberFormat'] = 'steelseries.LabelNumberFormat.FRACTIONAL';
            $result['fractionalScaleDecimals'] = 1;
            if ($max-$min < 1) {
                $result['fractionalScaleDecimals'] = 2;
            }
        }
        if ($digital) {
            $result['valueColor'] = 'steelseries.ColorDef.WHITE';
        }
        $result['lcdDecimals'] = $values['decimals'];
        $result['titleString'] = '"'.$values['type'].'"';
        $result['unitString'] = '"• '.$values['unit'].' •"';
        $result['digitalFont'] = true;
        if (strpos($attributes['design'], 'digital') !== false ) {
            unset($result['titleString']);
            $result['unitString'] = '"'.$values['type'].' • '.$values['unit'].'"';
        }
        if (strpos($attributes['design'], 'meter-') !== false ) {
            unset($result['titleString']);
            $result['niceScale'] = true;
        }
        if (strpos($attributes['design'], 'windcompass') !== false ) {
            unset($result['titleString']);
            unset($result['unitString']);
            $result['lcdTitleStrings'] = '["'.__('Wind', 'live-weather-station').'", "'.__('Gust', 'live-weather-station').'"]';
        }
        if (strpos($attributes['design'], 'altimeter') !== false ) {
            unset($result['titleString']);
            $result['unitString'] = '"'.$values['type'].'"';
        }

        if (strpos($attributes['design'], 'windcompass-vintage') !== false ) {
            $result['roseVisible'] = true;
            $result['degreeScale'] = false;
        }
        if (strpos($attributes['design'], 'windcompass-standard') !== false ) {
            $result['roseVisible'] = true;
        }
        return $result;
    }


    /**
     * Get value for steel meter shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    2.2.0
     */
    public function steelmeter_shortcodes($attributes) {
        $result = '';
        $fingerprint = uniqid('', true);
        $uniq = 'ssm'.substr ($fingerprint, count($fingerprint)-6, 80);
        $time = 1000 * (120 + rand(-20, 20));
        $_attributes = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','design' => '',
            'frame' => '','background' => '','orientation' => '','main_pointer_type' => '','main_pointer_color' => '',
            'aux_pointer_type' => '','aux_pointer_color' => '','knob' => '','lcd' => '','alarm' => '','trend' => '',
            'minmax' => '','index_style' => '','index_color' => '','glass' => '','size' => ''), $attributes );
        $_attributes['frame'] = strtoupper($_attributes['frame']);
        $_attributes['background'] = strtoupper($_attributes['background']);
        $_attributes['orientation'] = strtoupper($_attributes['orientation']);
        $_attributes['main_pointer_type'] = strtoupper($_attributes['main_pointer_type']);
        $_attributes['main_pointer_color'] = strtoupper($_attributes['main_pointer_color']);
        $_attributes['aux_pointer_type'] = strtoupper($_attributes['aux_pointer_type']);
        $_attributes['aux_pointer_color'] = strtoupper($_attributes['aux_pointer_color']);
        $_attributes['knob'] = strtoupper($_attributes['knob']);
        $_attributes['lcd'] = strtoupper($_attributes['lcd']);
        $_attributes['alarm'] = strtoupper($_attributes['alarm']);
        $_attributes['trend'] = strtoupper($_attributes['trend']);
        $_attributes['index_style'] = strtoupper($_attributes['index_style']);
        $_attributes['index_color'] = strtoupper($_attributes['index_color']);
        $_attributes['glass'] = strtoupper($_attributes['glass']);
        $sc_device = $_attributes['device_id'];
        $sc_module = $_attributes['module_id'];
        $sc_measurement = $_attributes['measure_type'];

        $params = json_encode($this->steelmeter_attributes($_attributes));
        $value = $this->steelmeter_value($_attributes, true);

        $params = str_replace('\"', '!', $params);
        $params = str_replace('"', '', $params);
        $params = str_replace('!', '"', $params);

        switch ($attributes['size']) {
            case 'small':
                $h = '150px';
                $w = '150px';
                break;
            case 'medium':
                $h = '200px';
                $w = '200px';
                break;
            case 'large':
                $h = '250px';
                $w = '250px';
                break;
            case 'macro':
                $h = '300px';
                $w = '300px'; 
                break;
        }
        $control = 'Radial';
        $minmax = false;
        $alarm = false;
        $trend = false;
        $aux = false;
        if (strpos($attributes['design'], 'analog') !== false ) {
            $control = 'Radial';
            $minmax = true;
            $alarm = true;
            $trend = true;
            $aux = false;
        }
        if (strpos($attributes['design'], 'digital') !== false ) {
            $control = 'RadialBargraph';
            $minmax = false;
            $alarm = true;
            $trend = true;
            $aux = false;
        }
        if (strpos($attributes['design'], 'meter-') !== false ) {
            $control = 'RadialVertical';
            $minmax = true;
            $alarm = false;
            $trend = false;
            $aux = false;
        }
        if (strpos($attributes['design'], 'windcompass') !== false ) {
            $control = 'WindDirection';
            $minmax = false;
            $alarm = false;
            $trend = false;
            $aux = true;
        }
        if (strpos($attributes['design'], 'altimeter') !== false ) {
            $control = 'Altimeter';
            $minmax = false;
            $alarm = false;
            $trend = false;
            $aux = false;
        }

        if ($value['value'] <= $value['min']) {
            $value['value'] = $value['value'] + 0.00001;
        }

        $style = 'width:'.$w.';height:'.$h.';';
        wp_enqueue_script('steelseries.js',false, array(),$this->version);
        $result  = '<canvas id="'.$uniq.'" style="'.$style.'"></canvas>'.PHP_EOL;
        $result .= '<script language="javascript" type="text/javascript">'.PHP_EOL;
        $result .= '  jQuery(document).ready(function($) {'.PHP_EOL;
        $result .= '    var g'.$uniq.' = new steelseries.'.$control.'('.$uniq.', '.$params.');'.PHP_EOL;
        if ($aux) {
            $result .= '      g'.$uniq.'.setValueAnimatedLatest('.$value['value'].');'.PHP_EOL;
            $result .= '      g'.$uniq.'.setValueAnimatedAverage('.$value['value_aux'].');'.PHP_EOL;
        }
        else {
            $result .= '      g'.$uniq.'.setValueAnimated('.$value['value'].', function() {'.PHP_EOL;
        }
        if ($alarm) {
            $result .= '        g'.$uniq.'.blinkUserLed('.$value['alarm'].');'.PHP_EOL;
        }
        if ($minmax) {
            $result .= '        g'.$uniq.'.resetMinMeasuredValue();'.PHP_EOL;
            $result .= '        g'.$uniq.'.resetMaxMeasuredValue();'.PHP_EOL;
            if ($value['value_min'] > -9999) {
                $result .= '        g'.$uniq.'.setMinMeasuredValue('.$value['value_min'].');'.PHP_EOL;
            }
            if ($value['value_max'] > -9999) {
                $result .= '        g'.$uniq.'.setMaxMeasuredValue('.$value['value_max'].');'.PHP_EOL;
            }
        }
        if ($trend) {
            $result .= '        g'.$uniq.'.setTrend(steelseries.TrendState.'.strtoupper($value['value_trend']).');'.PHP_EOL;
        }
        $result .= '        setInterval(function() {'.PHP_EOL;
        $result .= '          var http = new XMLHttpRequest();'.PHP_EOL;
        $result .= '          var params = "action=lws_query_steelmeter_datas";'.PHP_EOL;
        $result .= '          params = params+"&device_id='.$sc_device.'";'.PHP_EOL;
        $result .= '          params = params+"&module_id='.$sc_module.'";'.PHP_EOL;
        $result .= '          params = params+"&measure_type='.$sc_measurement.'";'.PHP_EOL;
        $result .= '          http.open("POST", "'.LWS_AJAX_URL.'", true);'.PHP_EOL;
        $result .= '          http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");'.PHP_EOL;
        $result .= '          http.onreadystatechange = function () {'.PHP_EOL;
        $result .= '            if (http.readyState == 4 && http.status == 200) {'.PHP_EOL;
        $result .= '              var odatas = JSON.parse(http.responseText);'.PHP_EOL;
        $result .= '              if ( typeof odatas != "undefined") {'.PHP_EOL;
        if ($aux) {
            $result .= '                g'.$uniq.'.setValueAnimatedLatest(odatas.value);'.PHP_EOL;
            $result .= '                g'.$uniq.'.setValueAnimatedAverage(odatas.value_aux);'.PHP_EOL;
        }
        else {
            $result .= '                g'.$uniq.'.setValueAnimated(odatas.value);'.PHP_EOL;
        }
        if ($alarm) {
            $result .= '                g'.$uniq.'.blinkUserLed(odatas.alarm);'.PHP_EOL;
        }
        if ($trend) {
            $result .= '                if (odatas.value_trend == "up") {g'.$uniq.'.setTrend(steelseries.TrendState.UP);}'.PHP_EOL;
            $result .= '                if (odatas.value_trend == "down") {g'.$uniq.'.setTrend(steelseries.TrendState.DOWN);}'.PHP_EOL;
            $result .= '                if (odatas.value_trend == "steady") {g'.$uniq.'.setTrend(steelseries.TrendState.STEADY);}'.PHP_EOL;
        }
        $result .= '              }'.PHP_EOL;
        $result .= '            }'.PHP_EOL;
        $result .= '          }'.PHP_EOL;
        $result .= '          http.send(params);'.PHP_EOL;
        $result .= '        }, '.$time.');'.PHP_EOL;
        if (!$aux) {
            $result .= '      });'.PHP_EOL;
        }
        $result .= '    });'.PHP_EOL;
        $result .= '</script>'.PHP_EOL;
        return $result;
    }

    /**
     * Get value for textual shortcodes.
     *
     * @return  string  $attributes The value queryed by the shortcode.
     * @since    1.0.0
     * @access   public
     */
    public function textual_shortcodes($attributes) {
        $_attributes = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','element' => '','format' => ''), $attributes );
        $_result = $this->get_specific_datas($_attributes);
        $err = __('Malformed shortcode. Please verify it!', 'live-weather-station') ;
        if (empty($_result)) {
            return $err;
        }
        $tz = '';
        if (($_attributes['format'] == 'local-date') || ($_attributes['format'] == 'local-time')) {
            $_att = shortcode_atts( array('device_id' => '','module_id' => '','measure_type' => '','element' => '','format' => ''), $attributes );
            $_att['module_id'] = $_attributes['device_id'];
            $_att['measure_type'] = 'loc_timezone';
            $_att['element'] = 'measure_value';
            $tz = $this->get_specific_datas($_att)['result'][$_att['measure_type']];
        }
        $result = $_result['result'][$_attributes['measure_type']];
        $module_type = $_result['module_type'];
        switch ($_attributes['format']) {
            case 'raw':
                break;
            case 'type-formated':
                switch ($_attributes['element']) {
                    case 'module_type':
                        $result = $this->get_module_type($result);
                        break;
                    case 'measure_type':
                        $result = $this->get_measurement_type($result, $module_type);
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-unit':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->output_unit($result, $module_type)['unit'];
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-unit-full':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->output_unit($result, $module_type)['full'];
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'type-unit-long':
                switch ($_attributes['element']) {
                    case 'measure_type':
                        $result = $this->output_unit($result, $module_type)['long'];
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'local-date':
                try {
                    if ($_attributes['element'] == 'measure_timestamp') {
                        $result = $this->get_date_from_mysql_utc($result, $tz) ;
                    }
                    if ($_attributes['element'] == 'measure_value') {
                        $result = $this->get_date_from_utc($result, $tz) ;
                    }
                }
                catch(Exception $ex) {
                    $result = $err ;
                }
                break;
            case 'local-time':
                try {
                    if ($_attributes['element'] == 'measure_timestamp') {
                        $result = $this->get_time_from_mysql_utc($result, $tz) ;
                    }
                    if ($_attributes['element'] == 'measure_value') {
                        $result = $this->get_time_from_utc($result, $tz) ;
                    }
                }
                catch(Exception $ex) {
                    $result = $err ;
                }
                break;
            case 'local-diff':
                try {
                    if ($_attributes['element'] == 'measure_timestamp') {
                        $result = $this->get_time_diff_from_mysql_utc($result) ;
                    }
                    if ($_attributes['element'] == 'measure_value') {
                        $result = $this->get_time_diff_from_utc($result) ;
                    }
                }
                catch(Exception $ex) {
                    $result = $err ;
                }
                break;
            case 'plain-text':
                switch ($_attributes['measure_type']) {
                    case 'windangle':
                    case 'gustangle':
                    case 'windangle_max':
                    case 'windangle_day_max':
                    case 'windangle_hour_max':
                        $result = $this->get_angle_full_text($result);
                        break;
                    default:
                        $result = $this->output_value($result, $_attributes['measure_type'], false, true, $module_type);
                }
                break;
            case 'short-text':
                switch ($_attributes['measure_type']) {
                    case 'windangle':
                    case 'gustangle':
                    case 'windangle_max':
                    case 'windangle_day_max':
                    case 'windangle_hour_max':
                        $result = $this->get_angle_text($result);
                        break;
                    default:
                        $result = $err ;
                }
                break;
            case 'computed':
            case 'computed-unit':
                $test = '';
                switch ($_attributes['measure_type']) {
                    case 'dew_point':
                        if (!$this->is_valid_dew_point($_result['result']['temperature_ref'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'frost_point':
                        if (!$this->is_valid_frost_point($_result['result']['temperature_ref'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'heat_index':
                        if (!$this->is_valid_heat_index($_result['result']['temperature_ref'], $_result['result']['humidity_ref'], $_result['result']['dew_point'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'humidex':
                        if (!$this->is_valid_humidex($_result['result']['temperature_ref'], $_result['result']['humidity_ref'], $_result['result']['dew_point'])) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                    case 'wind_chill':
                        if (!$this->is_valid_wind_chill($_result['result']['temperature_ref'], $result)) {
                            $test = __('N/A', 'live-weather-station') ;
                        }
                        break;
                }
                if ($test == '') {
                    $unit = ($_attributes['format']=='computed-unit');
                    $result = $this->output_value( $result, $_attributes['measure_type'], $unit, false, $module_type);
                }
                else {
                    $result = $test;
                }
                if ($result == '') {
                    $result = $err ;
                }
                break;
            case 'computed-wgs84':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 1);
                break;
            case 'computed-wgs84-unit':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 2);
                break;
            case 'computed-dms':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 3);
                break;
            case 'computed-dms-short':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 6);
                break;
            case 'computed-dms-cardinal-start':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 4);
                break;
            case 'computed-dms-cardinal-end':
                $result = $this->output_coordinate( $result, $_attributes['measure_type'], 5);
                break;
            default:
                $result = esc_html($result);
        }
        return $result;
    }

    /**
     * Output a value with user's unit.
     *
     * @param   mixed       $value          The value to output.
     * @param   string      $type           The type of the value.
     * @param   boolean     $unit           Optional. Display unit.
     * @param   boolean     $textual        Optional. Display textual value.
     * @param   string      $module_type    Optional. The type of the module.
     * @param   string      $tz             Optional. The timezone.
     * @return  string      The value outputed with the right unit.
     * @since    1.0.0
     * @access   protected
     */
    protected function output_value($value, $type, $unit=false , $textual=false, $module_type='NAMain', $tz='') {
        $result = $value;
        switch (strtolower($type)) {
            case 'battery':
                $result = $this->get_battery_percentage($value, $module_type);
                $result .= ($unit ? $this->unit_espace.$this->get_battery_unit() : '');
                if ($textual) {
                    $result = $this->get_battery_level_text($value, $module_type);
                }
                break;
            case 'signal':
                $result = $this->get_signal_percentage($value, $module_type);
                $result .= ($unit ? $this->unit_espace.$this->get_signal_unit() : '');
                if ($textual) {
                    $result = $this->get_signal_level_text($value, $module_type);
                }
                break;
            case 'co2':
                $result = $this->get_co2($value);
                $result .= ($unit ? $this->unit_espace.$this->get_co2_unit() : '');
                break;
            case 'co':
                $ref = get_option('live_weather_station_settings')[10] ;
                $result = $this->get_co($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_co_unit($ref) : '');
                break;
            case 'o3':
                $result = $this->get_o3($value);
                $result .= ($unit ? $this->unit_espace.$this->get_o3_unit() : '');
                break;
            case 'humidity':
            case 'humidity_ref':
                $result = $this->get_humidity($value);
                $result .= ($unit ? $this->unit_espace.$this->get_humidity_unit() : '');
                break;
            case 'cloudiness':
                $result = $this->get_cloudiness($value);
                $result .= ($unit ? $this->unit_espace.$this->get_cloudiness_unit() : '');
                break;
            case 'noise':
                $result = $this->get_noise($value);
                $result .= ($unit ? $this->unit_espace.$this->get_noise_unit() : '');
                break;
            case 'rain':
                $ref = 2 * get_option('live_weather_station_settings')[9] ;
                $result = $this->get_rain($value, $ref);
                if (strtolower($module_type)=='namodule3') {
                    $ref = $ref + 1;
                }
                $result .= ($unit ? $this->unit_espace.$this->get_rain_unit($ref) : '');
                break;
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
                $ref = 2 * get_option('live_weather_station_settings')[9] ;
                $result = $this->get_rain($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_rain_unit($ref) : '');
                break;
            case 'snow':
                $ref = get_option('live_weather_station_settings')[9] ;
                $result = $this->get_snow($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_snow_unit($ref) : '');
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
                $result = $this->get_wind_angle($value);
                $result .= ($unit ? $this->unit_espace.$this->get_wind_angle_unit() : '');
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
            case 'wind_ref':
                $ref = get_option('live_weather_station_settings')[2] ;
                $result = $this->get_wind_speed($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_wind_speed_unit($ref) : '');
                break;
            case 'pressure':
                $ref = get_option('live_weather_station_settings')[1] ;
                $result = $this->get_pressure($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_pressure_unit($ref) : '');
                break;
            case 'temperature':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
                $ref = get_option('live_weather_station_settings')[0] ;
                $result = $this->get_temperature($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_temperature_unit($ref) : '');
                break;
            case 'heat_index':
            case 'humidex':
            case 'wind_chill':
                $ref = get_option('live_weather_station_settings')[0] ;
                $result = round($this->get_temperature($value, $ref));
                break;
            case 'loc_altitude':
            case 'cloud_ceiling':
                $ref = get_option('live_weather_station_settings')[4] ;
                $result = $this->get_altitude($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_altitude_unit($ref) : '');
                break;
            case 'temperature_trend':
            case 'pressure_trend':
                $result = $value;
                if ($textual) {
                    $result = $this->get_trend_text($value);
                }
                break;
            case 'sunrise':
            case 'sunset':
                $result = $value;
                if ($unit) {
                    $result = $this->get_rise_set_short_from_utc($value, $tz);
                }
                if ($textual) {
                    $result = $this->get_rise_set_long_from_utc($value, $tz);
                }
                break;
            case 'moonrise':
            case 'moonset':
                $result = $value;
                if ($unit) {
                    $result = $this->get_rise_set_short_from_utc($value, $tz, true);
                }
                if ($textual) {
                    $result = $this->get_rise_set_long_from_utc($value, $tz);
                }
                break;
            case 'moon_illumination':
                $result = $this->get_moon_illumination($value);
                $result .= ($unit ? $this->unit_espace.$this->get_moon_illumination_unit() : '');
                break;
            case 'moon_diameter':
            case 'sun_diameter':
                $result = $this->get_degree_diameter($value);
                $result .= ($unit ? $this->unit_espace.$this->get_degree_diameter_unit() : '');
                break;
            case 'moon_distance':
            case 'sun_distance':
                $ref = get_option('live_weather_station_settings')[5] ;
                $result = $this->get_distance_from_kilometers($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_distance_unit($ref) : '');
                break;
            case 'moon_phase':
                $result = $value;
                if ($unit || $textual) {
                    $result = $this->get_moon_phase_text($value);
                }
                break;
            case 'moon_age':
                $result = $value;
                if ($unit || $textual) {
                    $result = $this->get_age_from_days($value);
                }
                break;
            case 'o3_distance':
            case 'co_distance':
                $ref = get_option('live_weather_station_settings')[5] ;
                $result = $this->get_distance_from_meters($value, $ref);
                $result .= ($unit ? $this->unit_espace.$this->get_distance_unit($ref) : '');
                break;
            case 'loc_timezone':
            case 'timezone':
                $result = $value;
                if ($unit || $textual) {
                    $result = $this->output_timezone($value);
                }
                break;
        }
        return $result;
    }

    /**
     * Output a latitude or longitude with user's unit.
     *
     * @param   mixed       $value          The value to output.
     * @param   string      $type           The type of the value.
     * @param   integer     $mode           Optional. The mode in wich to output:
     *                                          1: Geodetic system WGS 84
     *                                          2: Geodetic system WGS 84 with unit
     *                                          3: DMS
     *                                          4: DMS starting with cardinal
     *                                          5: DMS ending with cardinal
     * @return  string      The value outputed with the right unit.
     * @since    1.1.0
     * @access   protected
     */
    protected function output_coordinate($value, $type, $mode=0) {
        switch ($mode) {
            case 1:
                $result = $value;
                break;
            case 2:
                $result = $value.'°';
                break;
            case 3:
            case 4:
            case 5:
            case 6:
                $abs = abs($value);
                $floor = floor($abs);
                $deg = (integer)$floor;
                $min = (integer)floor(($abs-$deg)*60);
                $min_alt = round(($abs-$deg)*60, 1);
                $sec = round(($abs-$deg-($min/60))*3600,1);
                $result = $deg.'° '.$min.'\' '.$sec.'"';
                $result_alt = $deg.'° '.$min_alt.'\' ';
                $fix = ($value >= 0 ? '' : '-') ;
                if ($type=='loc_longitude' && $mode != 3) {
                    if ($fix == '') {
                        $fix = $this->get_angle_text(90) ;
                    }
                    else {
                        $fix = $this->get_angle_text(270) ;
                    }
                }
                if ($type=='loc_latitude' && $mode != 3) {
                    if ($fix == '') {
                        $fix = $this->get_angle_text(0) ;
                    }
                    else {
                        $fix = $this->get_angle_text(180) ;
                    }
                }
                if ($mode == 3) {
                    $result = $fix.$result;
                }
                if ($mode == 4) {
                    $result = $fix.' '.$result;
                }
                if ($mode == 5) {
                    $result = $result.' '.$fix;
                }
                if ($mode == 6) {
                    $result = $result_alt.' '.$fix;
                }
                break;
            default:
                $result = $value;
        }
        return $result;
    }

    /**
     * Indicates if alarm is on.
     *
     * @param   mixed       $value          The value to test.
     * @param   string      $type           The type of the value.
     * @return  boolean     True if alarm is on, false otherwise.
     * @since    1.0.0
     * @access   protected
     */
    protected function is_alarm_on($value, $type) {
        $result = false;
        switch (strtolower($type)) {
            case 'co2':
                $result = ($value > 1000);
                break;
            case 'humidity':
                $result = ($value > 75);
                if (!$result) {
                    $result = ($value < 25);
                }
                break;
            case 'noise':
                $result = ($value > 55);
                break;
            case 'rain':
                $result = ($value > 2);
                break;
            case 'rain_hour_aggregated':
                $result = ($value > 5);
                break;
            case 'rain_day_aggregated':
                $result = ($value > 10);
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
                $result = ($value > 70);
                break;
            case 'temperature':
                $result = ($value > 40);
                if (!$result) {
                    $result = ($value < 0);
                }
            case 'heat_index':
                $result = ($value > 39);
                break;
            case 'humidex':
                $result = ($value > 44);
                break;
        }
        return $result;
    }

    /**
     * Outputs the right unit.
     *
     * @param   string  $type   The type of the value.
     * @param   string $module_type  Optional. Specify the type of the module.
     * @param   integer $force_ref  Optional. Forces the ref unit.
     * @return  array   The value of the right unit and its complement.
     * @since    1.0.0
     * @access   protected
     */
    protected function output_unit($type, $module_type='NAMain', $force_ref=0) {
        $result = array('unit'=>'', 'comp'=>'', 'full'=>'', 'long'=>'');
        switch ($type) {
            case 'loc_altitude':
            case 'cloud_ceiling':
                $ref = get_option('live_weather_station_settings')[4] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_altitude_unit($ref) ;
                $result['long'] = $this->get_altitude_unit_full($ref) ;
                break;
            case 'battery':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_battery_unit($ref) ;
                $result['long'] = $this->get_battery_unit_full($ref) ;
                break;
            case 'signal':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_signal_unit($ref) ;
                $result['long'] = $this->get_signal_unit_full($ref) ;
                break;
            case 'co2':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_co2_unit($ref) ;
                $result['long'] = $this->get_co2_unit_full($ref) ;
                break;
            case 'co':
                $ref = get_option('live_weather_station_settings')[10] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_co_unit($ref) ;
                $result['long'] = $this->get_co_unit_full($ref) ;
                break;
            case 'o3':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_o3_unit($ref) ;
                $result['long'] = $this->get_o3_unit_full($ref) ;
                break;
            case 'humidity':
            case 'humidity_ref':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_humidity_unit($ref) ;
                $result['long'] = $this->get_humidity_unit_full($ref) ;
                break;
            case 'cloudiness':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_cloudiness_unit($ref) ;
                $result['long'] = $this->get_cloudiness_unit_full($ref) ;
                break;
            case 'noise':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_noise_unit($ref) ;
                $result['long'] = $this->get_noise_unit_full($ref) ;
                break;
            case 'rain':
                $ref = 2 * get_option('live_weather_station_settings')[9] ;
                if (strtolower($module_type)=='namodule3') {
                    $ref = $ref + 1;
                }
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('now', 'live-weather-station') ;
                if (strtolower($module_type)=='nacurrent') {
                    $result['comp'] = __('/ 3 hr', 'live-weather-station') ;
                }
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                break;
            case 'rain_hour_aggregated':
                $ref = 2 * get_option('live_weather_station_settings')[9] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('/ 1 hr', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                break;
            case 'rain_day_aggregated':
                $ref = 2 * get_option('live_weather_station_settings')[9] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['comp'] = __('today', 'live-weather-station') ;
                $result['unit'] = $this->get_rain_unit($ref) ;
                $result['long'] = $this->get_rain_unit_full($ref) ;
                break;
            case 'snow':
                $ref = get_option('live_weather_station_settings')[9] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                if (strtolower($module_type)=='nacurrent') {
                    $result['comp'] = __('/ 3 hr', 'live-weather-station') ;
                }
                $result['unit'] = $this->get_snow_unit($ref) ;
                $result['long'] = $this->get_snow_unit_full($ref) ;
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'windangle_day_max':
            case 'windangle_hour_max':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                if ($type == 'windangle') {
                    $result['comp'] = __('now', 'live-weather-station') ;
                }
                if ($type == 'gustangle') {
                    $result['comp'] = __('gust', 'live-weather-station') ;
                }
                if ($type == 'windangle_day_max') {
                    $result['comp'] = __('today', 'live-weather-station') ;
                }
                if ($type == 'windangle_hour_max') {
                    $result['comp'] = __('/ 1 hr', 'live-weather-station') ;
                }
                $result['unit'] = $this->get_wind_angle_unit($ref);
                $result['long'] = $this->get_wind_angle_unit_full($ref);
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
                $ref = get_option('live_weather_station_settings')[2] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                if ($type == 'windstrength') {
                    $result['comp'] = __('now', 'live-weather-station') ;
                }
                if ($type == 'guststrength') {
                    $result['comp'] = __('gust', 'live-weather-station') ;
                }
                if ($type == 'windstrength_day_max') {
                    $result['comp'] = __('today', 'live-weather-station') ;
                }
                if ($type == 'windstrength_hour_max') {
                    $result['comp'] = __('/ 1 hr', 'live-weather-station') ;
                }
                $result['unit'] = $this->get_wind_speed_unit($ref);
                $result['long'] = $this->get_wind_speed_unit_full($ref);
                break;
            case 'pressure':
                $ref = get_option('live_weather_station_settings')[1] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_pressure_unit($ref);
                $result['long'] = $this->get_pressure_unit_full($ref);
                break;
            case 'temperature':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
                $ref = get_option('live_weather_station_settings')[0] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_temperature_unit($ref);
                $result['long'] = $this->get_temperature_unit_full($ref);
                break;
            case 'moon_illumination':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_moon_illumination_unit($ref);
                $result['long'] = $this->get_moon_illumination_unit_full($ref);
                break;
            case 'moon_diameter':
            case 'sun_diameter':
                $ref = 0;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_degree_diameter_unit($ref);
                $result['long'] = $this->get_degree_diameter_unit_full($ref);
                break;
            case 'moon_distance':
            case 'sun_distance':
                $ref = get_option('live_weather_station_settings')[5] ;
                if ($force_ref != 0) {
                    $ref = $force_ref;
                }
                $result['unit'] = $this->get_distance_unit($ref);
                $result['long'] = $this->get_distance_unit_full($ref);
                break;
        }
        if ($result['comp'] != __('now', 'live-weather-station')) {
            $result['full'] = $result['unit'].' '.$result['comp'];   
        }
        else {
            $result['full'] = $result['unit'].' ('.$result['comp'].')';
        }
        return $result;
    }

    /**
     * How decimals to display for this type of value.
     *
     * @param   string  $type   The type of the value.
     * @param   integer $value  Optional. The decimal value.
     * @return  integer   The number of decimals to show.
     * @since    2.1.0
     */
    protected function decimal_for_output($type, $value) {
        $result = 0;
        switch ($type) {
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
                $result = 1 + get_option('live_weather_station_settings')[9] ;
                break;
            case 'snow':
                $result = get_option('live_weather_station_settings')[9] ;
                break;
            case 'temperature':
            case 'temperature_min':
            case 'temperature_max':
            case 'temperature_ref':
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
                $result = 1 ;
                break;
            case 'pressure':
                if (get_option('live_weather_station_settings')[1] == 1) {
                    $result = 2 ;
                }
                break;
            case 'co':
                $ref = get_option('live_weather_station_settings')[10] ;
                switch ($ref) {
                    case 0 : $result = 6 ; break;
                    case 1 : $result = 4 ; break;
                    case 2 : $result = 5 ; break;
                }
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'windstrength_day_max':
            case 'windstrength_hour_max':
                $ref = get_option('live_weather_station_settings')[2] ;
                if ($ref == 1 || $ref == 2 || $ref == 4 || $ref == 5 || $ref == 6) {
                    if ($value < 5) {
                        $result = 1;
                    }
                }
                break;
        }
        return $result;
    }

    /**
     * Get a human readable time zone.
     *
     * @param   string  $timezone  Standardized timezone string
     * @return  array  A human readable time zone.
     * @since    2.0.0
     */
    private function output_timezone($timezone) {
        $result = str_replace('/', ' / ', $timezone);
        $result = str_replace('_', ' ', $result);
        $result = str_replace('DU', ' d\'U', $result);
        return $result;
    }

    /**
     * Outputs the abbreviation of a measure type.
     *
     * @param   string  $type   The type of the value.
     * @return  string   The value of the abreviation.
     * @since    1.0.0
     * @access   protected
     */
    protected function output_abbreviation($type) {
        $result = '';
        switch ($type) {
            case 'co2':
                $result = __('CO₂', 'live-weather-station') ;
                break;
            case 'co':
                $result = __('CO', 'live-weather-station') ;
                break;
            case 'o3':
                $result = __('O₃', 'live-weather-station') ;
                break;
            case 'humidity':
                $result = __('humidity', 'live-weather-station') ;
                break;
            case 'noise':
                $result = __('noise', 'live-weather-station') ;
                break;
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
                $result = __('rain', 'live-weather-station') ;
                break;
            case 'snow':
                $result = __('snow', 'live-weather-station') ;
                break;
            case 'windangle':
            case 'windangle_max':
            case 'windangle_hour_max':
            case 'windangle_day_max':
            case 'gustangle':
            case 'windstrength':
            case 'windstrength_max':
            case 'windstrength_hour_max':
            case 'windstrength_day_max':
            case 'guststrength':
                $result = __('wind', 'live-weather-station') ;
                break;
            case 'pressure':
                $result = __('atm pressure', 'live-weather-station') ;
                break;
            case 'dew_point':
                $result = __('dew point', 'live-weather-station') ;
                break;
            case 'frost_point':
                $result = __('frost point', 'live-weather-station') ;
                break;
            case 'heat_index':
                $result = __('heat-index', 'live-weather-station') ;
                break;
            case 'humidex':
                $result = __('humidex', 'live-weather-station') ;
                break;
            case 'wind_chill':
                $result = __('wind chill', 'live-weather-station') ;
                break;
            case 'cloud_ceiling':
                $result = __('cloud base', 'live-weather-station') ;
                break;
            case 'cloudiness':
                $result = __('cloudiness', 'live-weather-station') ;
                break;
            case 'temperature':
            case 'temperature_min':
            case 'temperature_max':
                $result = __('temperature', 'live-weather-station') ;
                break;
        }
        return $result;
    }

    /**
     * Get the full country name of an ISO-2 country code.
     *
     * @param   string   $value    The ISO-2 country code.
     * @return  string  The full country name.
     * @since    2.0.0
     */
    protected function get_country_name($value) {
        return Locale::getDisplayRegion('-'.$value, get_locale());
    }

    /**
     * Get an array containing country names associated with their ISO-2 codes.
     *
     * @return  array  An associative array with names and codes.
     * @since    2.0.0
     */
    protected function get_country_names() {

        function compareASCII($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b);
            return strcmp(strtoupper($at), strtoupper($bt));
        }

        $result = [];
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $continue = array('BU', 'DY', 'EU', 'HV', 'FX', 'NH', 'QO', 'RH', 'TP', 'YU', 'ZR', 'ZZ');
        $locale = get_locale();
        for ($i=0; $i<26; $i++) {
            for ($j=0; $j<26; $j++) {
                $s = $letters[$i].$letters[$j];
                if (in_array($s, $continue)) {
                    continue;
                }
                $t = Locale::getDisplayRegion('-'.$s, $locale);
                if ($s != $t) {
                    $result[$s] = ucfirst($t);
                }
            }
        }
        $save_locale = setlocale(LC_ALL,'');
        setlocale(LC_ALL, $locale);
        uasort($result, 'compareASCII');
        setlocale(LC_ALL, $save_locale);
        return $result;
    }

    /**
     * Get the battery level in human readable text.
     *
     * @param   integer   $value    The value of the battery.
     * @param   string  $type   The type of the module.
     * @return  string  The battery level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_battery_level_text($value, $type) {
        $level = $this->get_battery_level($value, $type);
        switch ($level) {
            case 4:
                $result = __('Very low', 'live-weather-station') ;
                break;
            case 3:
                $result = __('Low', 'live-weather-station') ;
                break;
            case 2:
                $result = __('Medium', 'live-weather-station') ;
                break;
            case 1:
                $result = __('High', 'live-weather-station') ;
                break;
            case 0:
                $result = __('Full', 'live-weather-station') ;
                break;
            case -1:
                $result = __('AC Power', 'live-weather-station') ;
                break;
            default:
                $result = __('Unknown Power State', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the wind angle in readable text (i.e. N-NW, S, ...).
     *
     * @deprecated 1.1.0 Angle "translation" is not specific to wind.
     * @see get_angle_text
     *
     * @param   integer   $value    The value of the angle.
     * @return  string  The wind angle in readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_windangle_text($value) {
        if ($value < 0) {
            $value = $value + 360;
        }
        $val = round(($value / 22.5) + 0.5);
        $dir = array();
        $dir[] = __('N', 'live-weather-station') ;
        $dir[] = __('N-NE', 'live-weather-station') ;
        $dir[] = __('NE', 'live-weather-station') ; 
        $dir[] = __('E-NE', 'live-weather-station') ;
        $dir[] = __('E', 'live-weather-station') ; 
        $dir[] = __('E-SE', 'live-weather-station') ;
        $dir[] = __('SE', 'live-weather-station') ; 
        $dir[] = __('S-SE', 'live-weather-station') ;
        $dir[] = __('S', 'live-weather-station') ; 
        $dir[] = __('S-SW', 'live-weather-station') ;
        $dir[] = __('SW', 'live-weather-station') ; 
        $dir[] = __('W-SW', 'live-weather-station') ;
        $dir[] = __('W', 'live-weather-station') ; 
        $dir[] = __('W-NW', 'live-weather-station') ;
        $dir[] = __('NW', 'live-weather-station') ; 
        $dir[] = __('N-NW', 'live-weather-station') ;
        return $dir[$val % 16];
    }

    /**
     * Get an angle in readable text (i.e. N-NW, S, ...).
     *
     * @param   integer   $value    The value of the angle.
     * @return  string  The wind angle in readable text.
     * @since    1.1.0
     * @access   protected
     */
    protected function get_angle_text($value) {
        $val = round((($value%360) / 22.5) + 0.4);
        $dir = array();
        $dir[] = __('N', 'live-weather-station') ;
        $dir[] = __('N-NE', 'live-weather-station') ;
        $dir[] = __('NE', 'live-weather-station') ;
        $dir[] = __('E-NE', 'live-weather-station') ;
        $dir[] = __('E', 'live-weather-station') ;
        $dir[] = __('E-SE', 'live-weather-station') ;
        $dir[] = __('SE', 'live-weather-station') ;
        $dir[] = __('S-SE', 'live-weather-station') ;
        $dir[] = __('S', 'live-weather-station') ;
        $dir[] = __('S-SW', 'live-weather-station') ;
        $dir[] = __('SW', 'live-weather-station') ;
        $dir[] = __('W-SW', 'live-weather-station') ;
        $dir[] = __('W', 'live-weather-station') ;
        $dir[] = __('W-NW', 'live-weather-station') ;
        $dir[] = __('NW', 'live-weather-station') ;
        $dir[] = __('N-NW', 'live-weather-station') ;
        $dir[] = __('N', 'live-weather-station') ;
        return $dir[$val];
    }

    /**
     * Get an angle in readable full text (i.e. North-Northwest, South, ...).
     *
     * @param   integer   $value    The value of the angle.
     * @return  string  The wind angle in readable text.
     * @since    1.2.2
     * @access   protected
     */
    protected function get_angle_full_text($value) {
        $val = round((($value%360) / 22.5) + 0.4);
        $dir = array();
        $dir[] = __('North', 'live-weather-station') ;
        $dir[] = __('North-Northeast', 'live-weather-station') ;
        $dir[] = __('Northeast', 'live-weather-station') ;
        $dir[] = __('East-Northeast', 'live-weather-station') ;
        $dir[] = __('East', 'live-weather-station') ;
        $dir[] = __('East-Southeast', 'live-weather-station') ;
        $dir[] = __('Southeast', 'live-weather-station') ;
        $dir[] = __('South-Southeast', 'live-weather-station') ;
        $dir[] = __('South', 'live-weather-station') ;
        $dir[] = __('South-Southwest', 'live-weather-station') ;
        $dir[] = __('Southwest', 'live-weather-station') ;
        $dir[] = __('West-Southwest', 'live-weather-station') ;
        $dir[] = __('West', 'live-weather-station') ;
        $dir[] = __('West-Northwest', 'live-weather-station') ;
        $dir[] = __('Northwest', 'live-weather-station') ;
        $dir[] = __('North-Northwest', 'live-weather-station') ;
        $dir[] = __('North', 'live-weather-station') ;
        return $dir[$val];
    }

    /**
     * Get the battery level in lcd readable type.
     *
     * @param   integer   $value    The value of the battery.
     * @param   string  $type   The type of the module.
     * @return  string  The battery level in lcd readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_battery_lcd_level_text($value, $type) {
        if ($value == -1) {
            return 'full';
        }
        $pct = $this->get_battery_percentage($value, $type);
        $result = 'full';
        if ($pct < 70) {
            $result = 'twothirds';
        }
        if ($pct < 40) {
            $result = 'onethird';
        }
        if ($pct < 10) {
            $result = 'empty';
        }
        return $result;
    }

    /**
     * Get the signal level in human readable text.
     *
     * @param   integer $value  The value of the battery.
     * @param   string  $type   The type of the module.
     * @return  string  The signal level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_signal_level_text($value, $type) {
        if ($type =='NAMain') {
            $result = $this->get_wifi_level_text($value);
        }
        else {
            $result = $this->get_rf_level_text($value) ;
        }
        return $result;
    }
    
    /**
     * Get the RF level in human readable text.
     *
     * @param   integer $value  The value of the RF.
     * @return  string  The RF level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_rf_level_text($value) {
        $level = $this->get_rf_level($value);
        switch ($level) {
            case 3:
                $result = __('Full', 'live-weather-station') ;
                break;
            case 2:
                $result = __('High', 'live-weather-station') ;
                break;
            case 1:
                $result = __('Medium', 'live-weather-station') ;
                break;
            case 0:
                $result = __('Very low', 'live-weather-station') ;
                break;
            default:
                $result = __('No RF Signal', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the signal level in lcd readable type.
     *
     * @param   integer   $value    The value of the signal.
     * @param   string  $type   The type of the module.
     * @return  float  The signal level in lcd readable value.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_signal_lcd_level_text($value, $type) {
        if ($value == -1) {
            return 0;
        }
        $pct = $this->get_signal_percentage($value, $type);
        $result = ((float)$pct)/100;
        return $result;
    }

    /**
     * Get the wifi level in human readable text.
     *
     * @param   integer $value  The value of the wifi.
     * @return  string   The wifi level in human readable text.
     * @since    1.0.0
     * @access   protected
     */
    protected function get_wifi_level_text($value) {
        $level = $this->get_wifi_level($value);
        switch ($level) {
            case 2:
                $result = __('High', 'live-weather-station') ;
                break;
            case 1:
                $result = __('Medium', 'live-weather-station') ;
                break;
            case 0:
                $result = __('Very low', 'live-weather-station') ;
                break;
            default:
                $result = __('No WiFi Signal', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the trend in human readable text.
     *
     * @param   integer $value  The value of the trend.
     * @return  string   The trend level in human readable text.
     * @since    1.1.0
     * @access   protected
     */
    protected function get_trend_text($value) {
        switch (strtolower($value)) {
            case 'up':
                $result = __('Rising', 'live-weather-station') ;
                break;
            case 'down':
                $result = __('Falling', 'live-weather-station') ;
                break;
            default:
                $result = __('Stable', 'live-weather-station');
        }
        return $result;
    }

    /**
     * Get the trend in standard readable text.
     *
     * @param   integer $value  The value of the trend.
     * @return  string   The trend level in standard readable text.
     * @since    3.0.0
     * @access   protected
     */
    protected function get_standard_trend_text($value) {
        switch (strtolower($value)) {
            case 'up':
                $result = 'Rising';
                break;
            case 'down':
                $result = 'Falling';
                break;
            default:
                $result = 'Steady';
        }
        return $result;
    }

    /**
     * Get the moon phase in human readable text.
     *
     * @param   integer $value  The decimal value of the moon phase.
     * @return  string   The moon phase in human readable text.
     * @since    2.0.0
     */
    protected function get_moon_phase_text($value) {
        $names = array( __('New Moon', 'live-weather-station'),
                        __('Waxing Crescent', 'live-weather-station'),
                        __('First Quarter', 'live-weather-station'),
                        __('Waxing Gibbous', 'live-weather-station'),
                        __('Full Moon', 'live-weather-station'),
                        __('Waning Gibbous', 'live-weather-station'),
                        __('Third Quarter', 'live-weather-station'),
                        __('Waning Crescent', 'live-weather-station'),
                        __('New Moon', 'live-weather-station'));
        return $names[(int)floor(($value + 0.0625) * 8)];
    }

    /**
     * Get the icon moon phase id.
     *
     * @param   integer $value  The decimal value of the moon phase.
     * @return  string   The moon phase in human readable text.
     * @since    2.0.0
     */
    protected function get_moon_phase_icon($value) {
        $id = array('new',
                    'waxing-crescent-1',
                    'waxing-crescent-2',
                    'waxing-crescent-3',
                    'waxing-crescent-4',
                    'waxing-crescent-5',
                    'waxing-crescent-6',
                    'first-quarter',
                    'waxing-gibbous-1',
                    'waxing-gibbous-2',
                    'waxing-gibbous-3',
                    'waxing-gibbous-4',
                    'waxing-gibbous-5',
                    'waxing-gibbous-6',
                    'full',
                    'waning-gibbous-1',
                    'waning-gibbous-2',
                    'waning-gibbous-3',
                    'waning-gibbous-4',
                    'waning-gibbous-5',
                    'waning-gibbous-6',
                    'third-quarter',
                    'waning-crescent-1',
                    'waning-crescent-2',
                    'waning-crescent-3',
                    'waning-crescent-4',
                    'waning-crescent-5',
                    'waning-crescent-6',
                    'new');
        return $id[(int)floor(($value + 0.01786) * 28)];
    }

    /**
     * Format the selected datas for widget usage.
     *
     * @param   array   $datas  An array containing the selected datas.
     * @return  array   An array containing the formated datas, ready to be read by widgets.
     * @since    1.0.0
     * @access   protected
     */
    protected function format_widget_datas($datas) {
        $result = array();
        $err = 0 ;
        $ts = 0;
        $msg = __('Successful operation', 'live-weather-station');
        if (count($datas)==0) {
            $err = 3 ;
            $msg = __('Database contains inconsistent datas', 'live-weather-station');
        }
        else {
            $result['name'] = $datas[0]['device_name'];
            $key = '';
            $sub = array();
            foreach ($datas as $data) {
                if ($data['module_id'] != $key) {
                    if (!empty($sub)) {
                        $result['modules'][$key] = $sub;
                    }
                    $key = $data['module_id'];
                    $sub = array();
                    $sub['name'] = $data['module_name'];
                    $sub['type'] = $data['module_type'];
                    $sub['datas'] = array();
                }
                $ssub = array();
                $ssub['value'] = $this->output_value($data['measure_value'], $data['measure_type'], false, false, $data['module_type']);
                $ssub['unit'] = $this->output_unit($data['measure_type'], $data['module_type']);
                $sub_ts = strtotime ($data['measure_timestamp']);
                if ($sub_ts>$ts) {$ts=$sub_ts;}
                $sub['datas'][$data['measure_type']] = $ssub;
            }
            if (!empty($sub)) {
                $result['modules'][$key] = $sub;
            }
        }
        $result['condition'] = array('value' => $err, 'message' =>$msg);
        $result['timestamp'] = $ts;
        return $result;
    }

    /**
     * Says if value must be shown.
     *
     * @param   $measure_type               string  The type of value.
     * @param   $aggregated                  boolean  Display is in aggregated mode.
     * @param   $outdoor                    boolean  Display is in outdoor mode.
     * @param   $computed                   boolean  Display is in computed mode.
     * @param   $pollution                   boolean  Display is in pollution mode.
     * @return  boolean     True if the value must be shown, false otherwise.
     * @since    2.0.0
     */
    private function is_value_ok ($measure_type, $aggregated, $outdoor, $computed, $pollution) {
        $result = false;
        switch ($measure_type) {
            case 'co2':
            case 'noise':
                $result = $aggregated && !$outdoor;
                break;
            case 'cloudiness':
            case 'pressure':
            case 'humidity':
            case 'temperature':
            case 'snow':
            case 'rain':
            case 'rain_hour_aggregated':
            case 'rain_day_aggregated':
            case 'windstrength':
            case 'windangle':
            case 'guststrength':
            case 'windstrength_hour_max':
            case 'windstrength_day_max':
            //case 'gustangle':
                $result = $aggregated || $outdoor;
                break;
            case 'dew_point':
            case 'frost_point':
            case 'wind_chill':
            case 'heat_index':
            case 'cloud_ceiling':
                $result = $computed && $outdoor;
                break;
            case 'o3':
            case 'co':
                $result = $aggregated || $pollution;
                break;
        }
        return $result;
    }

    /**
     * Format the selected datas for lcd usage.
     *
     * @param   array   $datas  An array containing the selected datas.
     * @param   string   $measure_type  The measure type(s) to include.
     * @param   boolean   $computed  Includes computed measures too.
     * @return  array   An array containing the formated datas, ready to be displayed by lcd controls.
     * @since    1.0.0
     * @access   protected
     */
    protected function format_lcd_datas($datas, $measure_type, $computed=false) {
        $save_locale = setlocale(LC_ALL,'');
        setlocale(LC_ALL, get_locale());
        $result = array();
        $response = array ();
        $battery = array();
        $signal = array();
        $min = array();
        $max = array();
        $values = array();
        $value_types = array ('humidity' => 'NAModule1', 'rain' => 'NAModule3', 'windangle' => 'NAModule2', 'windstrength' => 'NAModule2', 'pressure' => 'NAMain', 'temperature' => 'NAModule1');
        $temperature_trend = array();
        $pressure_trend = array();
        $err = 0;
        $aggregated = ($measure_type == 'aggregated');
        $outdoor = ($measure_type == 'outdoor');
        $pollution = ($measure_type == 'pollution');
        $dew_point = 0;
        $has_dew_point = false;
        $temp_ref = 0;
        $has_temp_ref = false;
        $hum_ref = 0;
        $has_hum_ref = false;
        $wind_ref = 0;
        $has_wind_ref = false;
        $temperature_test = 0;
        $msg = __('Successful operation', 'live-weather-station');
        if (count($datas)==0) {
            $err = 3 ;
            $msg = __('Database contains inconsistent datas', 'live-weather-station');
        }
        else {
            foreach ($datas as $data) {
                if ($data['measure_type'] == 'battery') {
                    $battery[$data['module_id']] = $this->get_battery_lcd_level_text($data['measure_value'], $data['module_type']);
                }
                if ($data['measure_type'] == 'signal') {
                    $signal[$data['module_id']] = $this->get_signal_lcd_level_text($data['measure_value'], $data['module_type']);
                }
                if ($data['measure_type'] == 'temperature_max') {
                    $max[$data['module_id']] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'temperature_min') {
                    $min[$data['module_id']] = $this->output_value($data['measure_value'], $data['measure_type']);
                }
                if ($data['measure_type'] == 'temperature_trend') {
                    $temperature_trend[$data['module_id']] = ($data['measure_value'] == 'stable' ? 'steady' : $data['measure_value']);
                }
                if ($data['measure_type'] == 'pressure_trend') {
                    $pressure_trend[$data['module_id']] = ($data['measure_value'] == 'stable' ? 'steady' : $data['measure_value']);
                }
                if ($data['measure_type'] == 'dew_point') {
                    $dew_point = $data['measure_value'];
                    $has_dew_point = true;
                }
                if ($data['measure_type'] == 'temperature_ref') {
                    $temp_ref = $data['measure_value'];
                    $has_temp_ref = true;
                }
                if ($data['measure_type'] == 'wind_ref') {
                    $wind_ref = $data['measure_value'];
                    $has_wind_ref = true;
                }
                if ($data['measure_type'] == 'humidity_ref') {
                    $hum_ref = $data['measure_value'];
                    $has_hum_ref = true;
                }
                if (array_key_exists($data['measure_type'], $value_types) && $value_types[$data['measure_type']] == $data['module_type']) {
                    $values[$data['measure_type']] = $data['measure_value'] ;
                }
            }
            if ($has_temp_ref ) {
                $temperature_test = $temp_ref;
            }
            elseif (array_key_exists('temperature', $values)) {
                $temperature_test = $values['temperature'];
            }
            foreach ($datas as $data) {
                $unit = $this->output_unit($data['measure_type'], $data['module_type']);
                $measure = array ();
                $measure['min'] = 0;
                $measure['max'] = 0;
                $measure['value'] = $this->output_value($data['measure_value'], $data['measure_type']);
                $measure['unit'] = $unit['unit'];
                $measure['decimals'] = $this->decimal_for_output($data['measure_type'], $measure['value']);
                if ($measure['decimals']>5) {
                    $measure['decimals'] = 5;
                }
                $measure['sub_unit'] = $unit['comp'];
                $measure['show_sub_unit'] = ($unit['comp']!='');
                $measure['show_min_max'] = false;
                if ($outdoor || ($data['module_name'][0] == '[' && $aggregated && $outdoor)) {
                    $measure['title'] = iconv('UTF-8','ASCII//TRANSLIT',__('O/DR', 'live-weather-station') . ':' .$this->output_abbreviation($data['measure_type']));
                }
                elseif ($pollution || ($data['measure_type'] == 'o3') || ($data['measure_type'] == 'co')) {
                    $measure['title'] = iconv('UTF-8','ASCII//TRANSLIT',$this->get_measurement_type($data['measure_type']));
                }
                else {
                    if ($data['module_name'][0] == '[') {
                        $measure['title'] = iconv('UTF-8','ASCII//TRANSLIT',__('O/DR', 'live-weather-station') . ':' .$this->output_abbreviation($data['measure_type']));
                    }
                    else {
                        $measure['title'] = iconv('UTF-8', 'ASCII//TRANSLIT', $data['module_name']);
                    }
                }
                if (array_key_exists($data['module_id'], $battery)) {
                    $measure['battery'] = $battery[$data['module_id']];
                }
                else {
                    $measure['battery'] = $this->get_battery_lcd_level_text(-1, 'none');
                }
                $measure['trend'] = '';
                $measure['show_trend'] = false;
                $measure['show_alarm'] = $this->is_alarm_on($data['measure_value'], $data['measure_type']);
                if (array_key_exists($data['module_id'], $signal)) {
                    $measure['signal'] = $signal[$data['module_id']];
                }
                else {
                    $measure['signal'] = $this->get_signal_lcd_level_text(-1, 'none');
                }
                if (($data['measure_type'] == $measure_type) || (($data['measure_type'] != $measure_type) && $this->is_value_ok($data['measure_type'], $aggregated, $outdoor, $computed, $pollution))) {
                    switch (strtolower($data['measure_type'])) {
                        case 'co2':
                        case 'o3':
                        case 'co':
                        case 'noise':
                            $response[] = $measure;
                            break;
                        case 'dew_point':
                            if ($has_temp_ref && $this->is_valid_dew_point($temp_ref)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'frost_point':
                            if ($has_temp_ref && $this->is_valid_frost_point($temp_ref)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'wind_chill':
                            if ($has_temp_ref && $has_wind_ref && $this->is_valid_wind_chill($temp_ref, $wind_ref)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'heat_index':
                            if ($has_temp_ref && $has_hum_ref && $has_dew_point && $this->is_valid_heat_index($temp_ref, $hum_ref, $dew_point)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'humidex':
                            if ($has_temp_ref && $has_hum_ref && $has_dew_point && $this->is_valid_humidex($temp_ref, $hum_ref, $dew_point)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'cloud_ceiling':
                            if ($has_temp_ref && $has_dew_point) {
                                $response[] = $measure;
                            }
                            break;
                        case 'cloudiness':
                            if (!$outdoor) {
                                $measure['sub_unit'] = __('clouds', 'live-weather-station');
                                $measure['show_sub_unit'] = true;
                            }
                            $response[] = $measure;
                            break;
                        case 'humidity':
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NAModule1') {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'pressure':
                            if (array_key_exists($data['module_id'], $pressure_trend)) {
                                $measure['trend'] = $pressure_trend[$data['module_id']];
                                $measure['show_trend'] = true;
                            }
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NAMain') {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'temperature':
                            if (array_key_exists($data['module_id'], $temperature_trend)) {
                                $measure['trend'] = $temperature_trend[$data['module_id']];
                                $measure['show_trend'] = true;
                            }
                            if (array_key_exists($data['module_id'], $min) && array_key_exists($data['module_id'], $max)) {
                                $measure['min'] = $min[$data['module_id']];
                                $measure['max'] = $max[$data['module_id']];
                                $measure['show_min_max'] = true;
                            }
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NAModule1') {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'rain':
                            if (($data['measure_type'] == $measure_type) || ($data['measure_type'] != $measure_type && $aggregated)) {
                                if ($this->is_valid_rain($temperature_test)) {
                                    $response[] = $measure;
                                }
                            }
                            if ($outdoor && $data['module_type'] == 'NAModule3') {
                                if ($this->is_valid_rain($temperature_test)) {
                                    $response[] = $measure;
                                }
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                if ($this->is_valid_rain($temperature_test)) {
                                    $response[] = $measure;
                                }
                            }
                            break;
                        case 'rain_hour_aggregated':
                        case 'rain_day_aggregated':
                            if ($this->is_valid_rain($temperature_test)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'snow':
                            if ($this->is_valid_snow($temperature_test)) {
                                $response[] = $measure;
                            }
                            break;
                        case 'windangle':
                        case 'gustangle':
                        case 'windangle_max':
                        case 'windangle_day_max':
                        case 'windangle_hour_max':
                        case 'windstrength':
                        case 'guststrength':
                        case 'windstrength_max':
                        case 'windstrength_day_max':
                        case 'windstrength_hour_max':
                            if (($data['measure_type'] == $measure_type) || $aggregated) {
                                $response[] = $measure;
                            }
                            if (($outdoor && $data['module_type'] == 'NAModule2') || ($data['measure_type'] != $measure_type && $aggregated)) {
                                $response[] = $measure;
                            }
                            if ($outdoor && $data['module_type'] == 'NACurrent' && !array_key_exists($data['measure_type'], $values)) {
                                $response[] = $measure;
                            }
                            break;
                    }
                }
            }
        }
        if (count($response)==0) {
            $err = 4 ;
            $msg = __('All data have been filtered: nothing to show', 'live-weather-station');
        }
        $result['condition'] = array('value' => $err, 'message' =>$msg);
        $result['datas'] = $response;
        setlocale(LC_ALL, $save_locale);
        return $result;
    }

    /**
     * Format the selected datas for stickertags usage.
     *
     * @param   array   $datas  An array containing the selected datas.
     * @return  string   The formated datas, ready to be outputed as stickertags.txt file.
     * @since    3.0.0
     *
     */
    protected function format_stickertags_data($datas) {
        $tz = get_option('timezone_string');
        $ts = time();
        $tr = 0;
        $hr = 0;
        $dr = 0;
        $values = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        if (count($datas) == 0) {
            $values[0] = $this->get_time_from_utc($ts, $tz, 'H:i');
            $values[1] = $this->get_date_from_utc($ts, $tz, 'd/m/Y');
        }
        else {
            foreach ($datas as $data) {
                switch ($data['measure_type']) {
                    case 'loc_timezone':
                        $tz = $data['measure_value'];
                        $ts = strtotime($data['measure_timestamp']);
                        break;
                    case 'temperature_ref':
                        $tr = $data['measure_value'];
                        break;
                    case 'humidity_ref':
                        $hr = $data['measure_value'];
                        break;
                    case 'dew_point':
                        $dr = $data['measure_value'];
                        break;
                }
            }
            if ((time() - $ts > 240) && (time() - $ts < 900)) {
                $ts = time() - 240;
            }
            $values[0] = $this->get_time_from_utc($ts, $tz, 'H:i');
            $values[1] = $this->get_date_from_utc($ts, $tz, 'd/m/Y');
            foreach ($datas as $data) {
                switch ($data['measure_type']) {
                    case 'temperature':
                        if (strtolower($data['module_type']) == 'namodule1') {
                            $values[2] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'heat_index':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_heat_index($tr, $hr, $dr)) {
                                $values[3] = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'wind_chill':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_wind_chill($tr, $data['measure_value'])) {
                                $values[4] = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'humidity':
                        if (strtolower($data['module_type']) == 'namodule1') {
                            $values[5] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'dew_point':
                        if (strtolower($data['module_type']) == 'nacomputed') {
                            if ($this->is_valid_dew_point($tr)) {
                                $values[6] = sprintf('%.1F', round($data['measure_value'], 1));
                            }
                        }
                        break;
                    case 'pressure':
                        if (strtolower($data['module_type']) == 'namain') {
                            $values[7] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'pressure_trend':
                        if (strtolower($data['module_type']) == 'namain') {
                            $values[8] = $this->get_standard_trend_text($data['measure_value']);
                        }
                        break;
                    case 'windstrength':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $values[9] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'windangle':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $values[10] = str_replace('-', '', $this->get_angle_text($data['measure_value']));
                        }
                        break;
                    case 'rain_day_aggregated':
                        if (strtolower($data['module_type']) == 'namodule3') {
                            $values[11] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                    case 'sunrise':
                        if (strtolower($data['module_type']) == 'naephemer') {
                            $values[12] = $this->get_time_from_utc($data['measure_value'], $tz, 'H:i');
                        }
                        break;
                    case 'sunset':
                        if (strtolower($data['module_type']) == 'naephemer') {
                            $values[13] = $this->get_time_from_utc($data['measure_value'], $tz, 'H:i');
                        }
                        break;
                    case 'guststrength':
                        if (strtolower($data['module_type']) == 'namodule2') {
                            $values[15] = sprintf('%.1F', round($data['measure_value'], 1));
                        }
                        break;
                }

            }
        }
        $values[16] = 'C|km/h|hPa|mm';
        return implode(',', $values);
    }

    /**
     * Indicates if rain is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees.
     * @return  boolean   True if rain is valid, false otherwise.
     * @since    2.0.0
     */
    protected function is_valid_rain($temp_ref) {
        $result = false;
        if ($temp_ref >= 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if snow is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees.
     * @return  boolean   True if snow is valid, false otherwise.
     * @since    2.0.0
     */
    protected function is_valid_snow($temp_ref) {
        $result = false;
        if ($temp_ref < 3) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if dew point is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if dew point is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_dew_point($temp_ref) {
        $result = false;
        if ($temp_ref >= 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if frost point is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if frost point is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_frost_point($temp_ref) {
        $result = false;
        if ($temp_ref < 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if wind chill is valid (i.e. must be displayed).
     *
     * @param   float   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @param   float   $wind_chill     The wind chill value
     * @return  boolean   True if wind chill is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_wind_chill($temp_ref, $wind_chill=-200.0) {
        $result = false;
        if ($temp_ref < 10 && $temp_ref > $wind_chill) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if heat index is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @param   integer   $hum_ref      Reference humidity in % (reference = as it was at compute time).
     * @param   integer   $dew_ref      Reference dew point in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if heat index is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_heat_index($temp_ref, $hum_ref, $dew_ref) {
        $result = false;
        if ( ($temp_ref >= 27) && ($hum_ref>=40) && ($dew_ref>=12)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Indicates if humidex is valid (i.e. must be displayed).
     *
     * @param   integer   $temp_ref      Reference temperature in celcius degrees (reference = as it was at compute time).
     * @param   integer   $hum_ref      Reference humidity in % (reference = as it was at compute time).
     * @param   integer   $dew_ref      Reference dew point in celcius degrees (reference = as it was at compute time).
     * @return  boolean   True if humidex is valid, false otherwise.
     * @since    1.1.0
     */
    protected function is_valid_humidex($temp_ref, $hum_ref, $dew_ref) {
        $result = false;
        if ( ($temp_ref >= 15) && ($hum_ref>=20) && ($dew_ref>=10)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Get the measurement minimal rendered value.
     *
     * @param   string      $type           The type of the value.
     * @param   string      $module_type    The type of the module.
     * @return  integer  The the measurement minimal to render in controls.
     * @since    2.1.0
     */
    protected function get_measurement_min ($type, $module_type) {
        switch (strtolower($type)) {
            case 'pressure':
                $result = 900;
                break;
            case 'temperature':
            case 'temperature_ref':
            case 'temperature_max':
            case 'max_temp':
            case 'temperature_min':
            case 'min_temp':
                if (strtolower($module_type)=='namodule1' || strtolower($module_type)=='nacurrent') {
                    $result = -20;
                }
                else {
                    $result = 10;
                }
                break;
            case 'dew_point':
                $result = 0;
                break;
            case 'frost_point':
                $result = -20;
                break;
            case 'heat_index':
                $result = 20;
                break;
            case 'humidex':
                $result = 20;
                break;
            case 'wind_chill':
                $result = -40;
                break;
            case 'o3':
                $result = 100;
                break;
            case 'co':
                $result = 0.1;
                break;
            default:
                $result = 0;
        }
        return $result;
    }

    /**
     * Get the measurement maximal rendered value.
     *
     * @param   string      $type           The type of the value.
     * @param   string      $module_type    The type of the module.
     * @return  integer  The the measurement maximal to render in controls.
     * @since    2.1.0
     */
    protected function get_measurement_max ($type, $module_type) {
        switch (strtolower($type)) {
            case 'co2':
                $result = 2000;
                break;
            case 'o3':
                $result = 500;
                break;
            case 'co':
                $result = 0.2;
                break;
            case 'noise':
                $result = 90;
                break;
            case 'pressure':
                $result = 1080;
                break;
            case 'temperature':
            case 'temperature_ref':
            case 'temperature_max':
            case 'max_temp':
            case 'temperature_min':
            case 'min_temp':
                if (strtolower($module_type)=='namodule1' || strtolower($module_type)=='nacurrent') {
                    $result = 40;
                }
                else {
                    $result = 25;
                }
                break;
            case 'dew_point':
                $result = 20;
                break;
            case 'frost_point':
                $result = 0;
                break;
            case 'heat_index':
                $result = 80;
                break;
            case 'humidex':
                $result = 50;
                break;
            case 'wind_chill':
                $result = 10;
                break;
            case 'cloud_ceiling':
                $result = 3000;
                break;
            case 'rain':
            case 'sum_rain_1':
            case 'rain_hour_aggregated':
                $result = 10;
                break;
            case 'rain_day_aggregated':
            case 'sum_rain_24':
                $result = 25;
                break;
            case 'snow':
                $result = 50;
                break;
            case 'windangle':
            case 'gustangle':
            case 'windangle_max':
            case 'max_wind_angle':
                $result = 360;
                break;
            case 'windstrength':
            case 'guststrength':
            case 'windstrength_max':
            case 'max_wind_str':
            case 'wind_ref':
                $result = 100;
                break;
            default:
                $result = 100;
        }
        return $result;
    }
}
