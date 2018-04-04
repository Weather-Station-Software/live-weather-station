<?php

namespace WeatherStation\Engine\Module;

use WeatherStation\Data\Output;
use WeatherStation\Data\Arrays\Generator;

/**
 * Abstract class to maintains each module.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */
abstract class Maintainer {

    use Output, Generator {
        Output::get_service_name insteadof Generator;
        Output::get_comparable_dimensions insteadof Generator;
        Output::get_module_type insteadof Generator;
        Output::get_fake_module_name insteadof Generator;
        Output::get_measurement_type insteadof Generator;
        Output::get_dimension_name insteadof Generator;
        Output::get_operation_name insteadof Generator;
    }

    public static $module_mode = '';
    protected $module_type = '';
    protected $module_id = '';
    protected $module_name = '';
    protected $module_hint = '';
    protected $module_icon = 'fa fa-lg fa-fw fa-question';
    protected $module_icon_color = '#777777';
    protected $module_icon_index = '';
    protected $selected = false;
    protected $station_guid = 0;
    protected $station_id = '';
    protected $station_name = 0;
    protected $station_information;
    protected $data = null;
    protected $period = null;
    protected $layout = '';
    protected $fingerprint = '';
    public static $classes = array();

    protected $datasource_title = '';
    protected $parameter_title = '';
    protected $preview_title = '';

    protected $datasource_min_height = false;
    protected $parameter_min_height = false;
    protected $preview_min_height = false;

    protected $series_number = 1;

    /**
     * Initialize the class and set its properties.
     *
     * @param array $station_information An array containing the station inforrmations.
     * @since 3.4.0
     */
    public function __construct($station_information) {
        $this->module_id = self::$module_mode . '-' . $this->module_type;
        $this->station_information = $station_information;
        $this->station_guid = $this->station_information['guid'];
        $this->station_id = $this->station_information['station_id'];
        $this->station_name = $this->station_information['station_name'];
        $fingerprint = uniqid('', true);
        $this->fingerprint = $this->module_id.substr ($fingerprint, strlen($fingerprint)-6, 80);
        $this->fingerprint = str_replace('-', '_', $this->fingerprint);
    }

    /**
     * Prepare the data.
     *
     * @since 3.4.0
     */
    protected abstract function prepare();

    /**
     * Print the datasource section of the form.
     *
     * @return string The datasource section, ready to be printed.
     * @since 3.4.0
     */
    protected abstract function get_datasource();

    /**
     * Print the parameters section of the form.
     *
     * @return string The parameters section, ready to be printed.
     * @since 3.4.0
     */
    protected abstract function get_parameters();

    /**
     * Print the script section of the form.
     *
     * @return string The script section, ready to be printed.
     * @since 3.4.0
     */
    protected abstract function get_script();

    /**
     * Print the preview section of the form.
     *
     * @return string The preview section, ready to be printed.
     * @since 3.4.0
     */
    protected abstract function get_preview();

    /**
     * Enqueues needed styles and scripts.
     *
     * @since 3.4.0
     */
    protected abstract function enqueue_resources();

    /**
     * Register the module.
     *
     * @param string $type The type of the module.
     * @since 3.4.0
     */
    public static function register_module($type) {
        self::$classes[get_called_class()] = $type;
    }

    /**
     * Register the modules.
     *
     * @param string $type The type of modules.
     * @return array The modules of the type $type.
     * @since 3.4.0
     */
    public static function get_modules($type) {
        $result = array();
        foreach (self::$classes as $n => $t) {
            if ($type == $t ) {
                $result[] = $n;
            }
        }
        return $result;
    }

    /**
     * Get the module Id.
     *
     * @return string The module Id.
     * @since 3.4.0
     */
    public function get_id() {
        return strtolower($this->module_id);
    }

    /**
     * Get the module name.
     *
     * @return string The module name.
     * @since 3.4.0
     */
    public function get_name() {
        return $this->module_name;
    }

    /**
     * Get the module hint.
     *
     * @return string The module hint.
     * @since 3.4.0
     */
    public function get_hint() {
        return $this->module_hint;
    }

    /**
     * Get the module icon.
     *
     * @return string The module icon class.
     * @since 3.4.0
     */
    public function get_icon() {
        return strtolower($this->module_icon);
    }

    /**
     * Get the module icon color.
     *
     * @return string The module icon color.
     * @since 3.4.0
     */
    public function get_icon_color() {
        return $this->module_icon_color;
    }

    /**
     * Get the module icon index.
     *
     * @return string The module icon index.
     * @since 3.5.0
     */
    public function get_icon_index() {
        return strtolower($this->module_icon_index);
    }

    /**
     * Is the module selected?
     *
     * @return boolean True if the module is selected, false otherwise.
     * @since 3.4.0
     */
    public function is_selected() {
        return $this->selected;
    }

    /**
     * Set the module selected.
     *
     * @since 3.4.0
     */
    public function select() {
        $this->selected = true;
    }

    /**
     * Set the module selected.
     *
     * @since 3.4.0
     */
    public function unselect() {
        $this->selected = false;
    }

    /**
     * Get the module type.
     *
     * @return string The module type.
     * @since 3.4.0
     */
    public function module_type() {
        $result = 'view';
        foreach (self::$classes as $n => $t) {
            if (get_class($this) == $n ) {
                $result = $t;
                break;
            }
        }
        return strtolower($result);
    }

    /**
     * Get the parent page url.
     *
     * @return string The parent page url.
     * @since 3.4.0
     */
    public function get_parent_url() {
        return lws_re_get_admin_page_url(array('action'=>'shortcode', 'tab'=>$this->module_type(), 'service'=>'station'));
    }

    /**
     * Get the module page url.
     *
     * @return string The module page url.
     * @since 3.4.0
     */
    public function get_module_url() {
        return lws_re_get_admin_page_url(array('action'=>'shortcode', 'tab'=>$this->module_type(), 'service'=>$this->module_id));
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param string $options Optional. The options of the control.
     * @param boolean $label Optional. Display the th of the table.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @return string The control ready to print.
     * @since 3.4.0
     */
    private function get_option_select($id, $title, $options='', $label=true, $hidden=false, $displayed=true) {
        $visibility = '';
        if ($id == '') {
            $visibility = ' class="lws-placeholder" style="visibility:hidden;"';
            $id = 'o' . md5(random_bytes(20));
            $title = '';
        }
        $style = array();
        if ($hidden) {
            $style[] = 'visibility:hidden';
        }
        if (!$displayed) {
            $style[] = 'display:none';
        }
        if (count($style) > 0) {
            $visibility .= ' style="' . implode(';', $style) . '"';
        }
        $result = '';
        $result .= '<tr' . $visibility .'>';
        if ($label) {
            $result .= '<th class="lws-option" width="35%" align="left" scope="row">' . $title . '</th>';
            $result .= '<td width="2%"/>';
        }
        $result .= '<td align="left">';
        $result .= '<span class="select-option">';
        $result .= '<select class="option-select" id="' . $id .'">';
        if ($options != '') {
            $result .= $options;
        }
        $result .= '</select>';
        $result .= '</span>';
        $result .= '</td>';
        $result .= '</tr>';
        return $result;
    }

    /**
     * Get color picker control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param string $value Optional. The value of the control.
     * @param boolean $label Optional. Display the th of the table.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @return string The control ready to print.
     * @since 3.5.0
     */
    protected function get_color_picker($id, $title, $value='', $label=true, $hidden=false, $displayed=true) {
        $visibility = '';
        if ($id == '') {
            $visibility = ' class="lws-placeholder" style="visibility:hidden;"';
            $id = 'o' . md5(random_bytes(20));
            $title = '';
        }
        $style = array();
        if ($hidden) {
            $style[] = 'visibility:hidden';
        }
        if (!$displayed) {
            $style[] = 'display:none';
        }
        if (count($style) > 0) {
            $visibility .= ' style="' . implode(';', $style) . '"';
        }
        $result = '';
        $result .= '<tr' . $visibility .'>';
        if ($label) {
            $result .= '<th class="lws-option" width="35%" align="left" scope="row">' . $title . '</th>';
            $result .= '<td width="2%"/>';
        }
        $result .= '<td align="left">';
        $result .= '<span class="color-picker">';
        $result .= '<input class="widefat wp-color-picker" id="' . $id .'" type="text" value="' . $value .'" />';
        $result .= '</span>';
        $result .= '</td>';
        $result .= '</tr>';
        return $result;
    }

    /**
     * Get an option group.
     *
     * @param string $id The control id.
     * @param array $args The contents of the group.
     * @return string The group ready to print.
     * @since 3.4.0
     */
    protected function get_group($id, $args) {
        if ($id == '') {
            $id = 'g' . md5(random_bytes(20));
        }
        $tab_id = $id . '-tab-';
        $item_id = $id . '-item-';
        $result = '<tr style="display:table-row;height:12px;"><th width="35%"></th><td width="2%"/><td></td></tr>';
        $result .= '<tr>';
        $result .= '<th class="lws-option" width="35%" align="center" scope="row">';
        $show = true;
        $i = 1;
        foreach ($args as $arg) {
            if ($show) {
                $class = 'class="' . $tab_id . $i . ' lws-group-selected"';
                $show = false;
            }
            else {
                $class = 'class="' . $tab_id . $i . ' lws-group-unselected"';
            }
            $result .= '<span ' . $class . '>' . $arg['name'] . '</span>';
            $i++;
        }
        $result .= '</th>';
        $result .= '<td width="0"><span class="lws-group-separator"></span></td>';
        $result .= '<td align="left">';
        $show = true;
        $i = 1;
        foreach ($args as $arg) {
            if ($show) {
                $class = 'class="' . $item_id . $i . ' lws-group-option-selected"';
                $show = false;
            }
            else {
                $class = 'class="' . $item_id . $i . ' lws-group-option-unselected"';
            }
            $result .= '<span ' . $class . '><table cellspacing="0"><tbody>';
            $result .= $arg['content'];
            $result .= '</tbody></table></span>';
            $i++;
        }
        $result .= '</td>';
        $result .= '</tr>';
        $result .= '<script language="javascript" type="text/javascript">';
        $result .= 'jQuery(document).ready(function($) {';
        for ($i=1 ; $i<=count($args) ; $i++) {
            $result .= '$(".' . $tab_id . $i . '").click(function() {';
            $result .= '$(".lws-group-selected").removeClass("lws-group-selected").addClass("lws-group-unselected");';
            $result .= '$(this).addClass("lws-group-selected").removeClass("lws-group-unselected");';
            $result .= '$(".lws-group-option-selected").removeClass("lws-group-option-selected").addClass("lws-group-option-unselected");';
            $result .= '$(".' . $item_id . $i . '").addClass("lws-group-option-selected").removeClass("lws-group-option-unselected");';
            $result .= '});';
        }
        $result .= '});' . PHP_EOL;
        $result .= '</script>' . PHP_EOL;
        return $result;
    }

    /**
     * Get a placeholder of an option select control height.
     *
     * @return string The control ready to print.
     * @since 3.4.0
     */
    protected function get_placeholder_option_select() {
        return $this->get_option_select('', '');
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @return string The control ready to print.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @since 3.4.0
     */
    protected function get_neutral_option_select($id, $title, $hidden=false, $displayed=true) {
        return $this->get_option_select($id, $title, '', true, $hidden, $displayed);
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param array $items The array of items.
     * @param mixed $field Optional. The field to print.
     * @return string The control ready to print.
     * @since 3.4.0
     */
    protected function get_assoc_option_select($id, $title, $items, $field=null) {
        $result = '';
        foreach ($items as $key=>$item) {
            if (is_null($field)) {
                $result .= '<option value="' . $key . '">' . $item . '</option>;';
            }
            elseif (array_key_exists($field, $item)) {
                $result .= '<option value="' . $key . '">' . $item[$field] . '</option>;';
            }
        }
        return $this->get_option_select($id, $title, $result);
    }

    /**
     * Get an option select control.
     *
     * @param string $id The control id.
     * @param string $title The control title.
     * @param array $items The array of items.
     * @param boolean $label Optional. Display the th of the table.
     * @param mixed $selected Optional. Set the selected item.
     * @param boolean $hidden Optional. Hide the select option.
     * @param boolean $displayed Optional. Display the select option.
     * @return string The control ready to print.
     * @since 3.4.0
     */
    protected function get_key_value_option_select($id, $title, $items, $label=true, $selected=null, $hidden=false, $displayed=true) {
        $result = '';
        foreach ($items as $item) {
            $sel = '';
            if (!is_null($selected)){
                if ($selected == $item[0]) {
                    $sel = ' SELECTED';
                }
            }
            $result .= '<option value="' . $item[0] . '"' . $sel . '>' . $item[1] . '</option>;';
        }
        return $this->get_option_select($id, $title, $result, $label, $hidden, $displayed);
    }

    /**
     * Get a box.
     *
     * @param string $id The box id.
     * @param string $title The box title.
     * @param string $content The box content.
     * @param string $footer Optional. The box footer.
     * @param string $special_footer Optional. A special footer for the box.
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    protected function get_box($id, $title, $content, $footer='', $special_footer='') {
        $result = '';
        $result .= '<div class="meta-box-sortables" style="width:100%;">';
        $result .= '<div class="postbox" id="' . $id . '" style="min-width:300px;">';
        $result .= '<button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">' . __('Click to toggle', 'live-weather-station') . '</span><span class="toggle-indicator" aria-hidden="true"></span></button>';
        $result .= '<h3 class="hndle" style="cursor:default"><span>' . $title . '</span><span class="' . $id . '-spinner" style ="float: initial;margin-top:-4px;margin-bottom:-1px;"></span></h3>';
        $result .= '<div class="inside" style="text-align:center;">';
        $result .= $content;
        $result .= '</div>';
        if ($special_footer != '') {
            $result .= $special_footer;
        }
        if ($footer != '') {
            $result .= '<div id="major-publishing-actions">';
            $result .= '<div id="publishing-action">';
            $result .= $footer;
            $result .= '</div>';
            $result .= '<div class="clear"></div>';
            $result .= '</div>';
        }
        $result .= '</div>';
        $result .= '</div>';
        return $result;
    }
    /**
     * Get a script section.
     *
     * @param string $content The script itself.
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    protected function get_script_box($content) {
        $result = '';
        $result .= '<script language="javascript" type="text/javascript">';
        $result .= 'jQuery(document).ready(function($) {';
        // copy button attach action
        $result .= 'new Clipboard(".' . $this->module_id . '-cpy-' . $this->station_guid . '");';
        // wrapping control
        $result .= '$(window).resize(function() {';
        $result .= '    var wrapped = true;';
        $result .= '    var left = $(".item-boxes-container").position().left;';
        $result .= '    $(".item-boxes-container").each(function() {if ($(this).position().left != left) {wrapped = false;}});';
        if ($this->preview_min_height){
            $result .= '    if (wrapped) {$("#lws-preview-id").css("min-height", 0)}';
            $result .= '    if (!wrapped) {$("#lws-preview-id").css("min-height", $("#lws-parameter-id").height());}';
        }
        $result .= '    $.each($(".lws-placeholder"), function() {$(this).toggle(!wrapped);});';
        $result .= '}).resize();';
        // data
        $result .= 'var js_array_' . str_replace('-', '_',$this->module_id) . '_' . $this->station_guid . ' = ' . json_encode($this->data) . ';';
        // period
        if (self::$module_mode == 'yearly') {
            $result .= 'var js_array_' . str_replace('-', '_',$this->module_id) . '_period_' . $this->station_guid . ' = ' . json_encode($this->period) . ';';
        }
        // content
        $result .= $content;
        $result .= '});';
        $result .= '</script>';
        return $result;
    }

    /**
     * Get a box for shortcode text.
     *
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    protected function get_shortcode_box() {
        $id = $this->module_id . '-datas-shortcode-' . $this->station_guid;
        $title = __('4. Copy the following shortcode', 'live-weather-station');
        $content = '<textarea readonly rows="3" style="width:100%;font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace;" id="' . $id . '"></textarea>';
        $footer = '<button data-clipboard-target="#' . $id . '" class="button button-primary ' . $this->module_id . '-cpy-' . $this->station_guid . '">' . __('Copy', 'live-weather-station'). '</button>';
        return $this->get_box('lws-shortcode-id', $title, $content, $footer);
    }

    /**
     * Get the error box for data unavailable.
     *
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    private function get_error_box() {
        $title = __('No data', 'live-weather-station');
        $content = __('There is currently no data collected for this station and, for this reason, it is not possible to generate shortcodes. This is normally a temporary condition so, please, retry later or force a resynchronization.', 'live-weather-station' );
        return $this->get_box('lws-error-id', $title, $content);
    }

    /**
     * Get the error box for data unavailable.
     *
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    private function get_no_collect_box() {
        $title = __('No data compilation', 'live-weather-station');
        $url = lws_get_admin_page_url('lws-settings', null, 'history');
        $s = sprintf('<a href="%s">%s</a>', $url, __('right option', 'live-weather-station'));
        $content = sprintf(__('%s is not set to compile daily data and, for this reason, it is not possible to generate shortcodes for these data. To compile daily data, please set the %s.', 'live-weather-station' ), LWS_PLUGIN_NAME, $s);
        return $this->get_box('lws-error-id', $title, $content);
    }

    /**
     * Get the error box for data unavailable.
     *
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    private function get_no_build_box() {
        $title = __('No data compilation', 'live-weather-station');
        $url = lws_get_admin_page_url('lws-settings', null, 'history');
        $s = sprintf('<a href="%s">%s</a>', $url, __('right option', 'live-weather-station'));
        $content = sprintf(__('%s is not set to compile historical data and, for this reason, it is not possible to generate shortcodes for these data. To compile historical data, please set the %s.', 'live-weather-station' ), LWS_PLUGIN_NAME, $s);
        return $this->get_box('lws-error-id', $title, $content);
    }

    /**
     * Get the error box for data unavailable.
     *
     * @return string The box, ready to be printed.
     * @since 3.4.0
     */
    private function get_no_history_box() {
        $title = __('No data yet', 'live-weather-station');
        $content = sprintf(__('%s collects and compiles weather data for this station but, for now, there is not enough historical data to display graphs. Please, come back in 24-36 hours', 'live-weather-station' ), LWS_PLUGIN_NAME);
        return $this->get_box('lws-error-id', $title, $content);
    }

    /**
     * Print the error box for data unavailable.
     *
     * @param integer $id Optional. Type of the error.
     * @since 3.4.0
     */
    private function print_error($id=0) {
        $result = '';
        $result .= '<div class="main-boxes-container">';
        $result .= '<div class="row-boxes-container">';
        $result .= '<div class="item-boxes-container" id="lws-error">';
        switch ($id) {
            case 1:
                $result .= $this->get_no_collect_box();
                break;
            case 2:
                $result .= $this->get_no_build_box();
                break;
            case 3:
                $result .= $this->get_no_history_box();
                break;
            default:
                $result .= $this->get_error_box();
        }
        $result .= '</div>';
        $result .= '</div>';
        $result .= '</div>';
        echo $result;
    }

    /**
     * Print the form boxes with layout.
     *
     * @since 3.4.0
     */
    protected function print_boxes() {
        $result = '';
        $result .= '<div class="main-boxes-container">';
        $result .=wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false, false);
        switch ($this->layout) {
            case '12-3-4':
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-datasource">';
                $result .= $this->get_datasource();
                $result .= '</div>';
                $result .= '<div class="item-boxes-container" id="lws-parameters">';
                $result .= $this->get_parameters();
                $result .= '</div>';
                $result .= '</div>';
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-preview">';
                $result .= $this->get_script();
                $result .= $this->get_preview();
                $result .= '</div>';
                $result .= '</div>';
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-shortcode">';
                $result .= $this->get_shortcode_box();
                $result .= '</div>';
                $result .= '</div>';
                break;
            case '1-23-4':
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-datasource">';
                $result .= $this->get_datasource();
                $result .= '</div>';
                $result .= '</div>';
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-parameters">';
                $result .= $this->get_parameters();
                $result .= '</div>';
                $result .= '<div class="item-boxes-container" id="lws-preview">';
                $result .= $this->get_script();
                $result .= $this->get_preview();
                $result .= '</div>';
                $result .= '</div>';
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-shortcode">';
                $result .= $this->get_shortcode_box();
                $result .= '</div>';
                $result .= '</div>';
                break;
            default:
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-datasource">';
                $result .= $this->get_datasource();
                $result .= '</div>';
                $result .= '</div>';
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-parameters">';
                $result .= $this->get_parameters();
                $result .= '</div>';
                $result .= '</div>';
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-preview">';
                $result .= $this->get_script();
                $result .= $this->get_preview();
                $result .= '</div>';
                $result .= '</div>';
                $result .= '<div class="row-boxes-container">';
                $result .= '<div class="item-boxes-container" id="lws-shortcode">';
                $result .= $this->get_shortcode_box();
                $result .= '</div>';
                $result .= '</div>';
        }
        $result .= '</div>';
        echo $result;
    }

    /**
     * Print the form allowing to parameter the control/graph.
     *
     * @since 3.4.0
     */
    public function print_form() {
        $this->datasource_title = __('1. Select data sources', 'live-weather-station');
        $this->parameter_title = __('2. Set the general design parameters', 'live-weather-station');
        $this->preview_title = __('3. Verify the generated output', 'live-weather-station');
        $this->prepare();
        if (is_null($this->data)) {
            $this->print_error(0);
        }
        elseif ($this->module_type() == 'daily' && !(bool)get_option('live_weather_station_collect_history')) {
            $this->print_error(1);
        }
        elseif ($this->module_type() == 'yearly' && !(bool)get_option('live_weather_station_build_history')) {
            $this->print_error(2);
        }
        elseif ($this->module_type() == 'yearly' && $this->station_information['oldest_data'] == '0000-00-00') {
            $this->print_error(3);
        }
        else {
            $this->enqueue_resources();
            $this->print_boxes();
        }
    }


    /**
     * Get the standard script section of the form.
     *
     * @return string The script section, ready to be printed.
     * @since 3.4.0
     */
    protected function get_standard_script() {
        $name = self::$module_mode . '-' . $this->module_type;
        $js_name = self::$module_mode . '_' . $this->module_type;
        $content = '';

        if (self::$module_mode == 'yearly') {
            $content .= '$("#' . $name . '-datas-period-type-' . $this->station_guid . '").change(function() {';
            $content .= 'var js_array_' . $js_name . '_p_' . $this->station_guid . ' = null;';
            $content .= '$(js_array_' . $js_name . '_period_' . $this->station_guid . ').each(function (i) {';
            $content .= 'if (js_array_' . $js_name . '_period_' . $this->station_guid . '[i][0] == $("#' . $name . '-datas-period-type-' . $this->station_guid . '").val()) {js_array_' . $js_name . '_p_' . $this->station_guid . '=js_array_' . $js_name . '_period_' . $this->station_guid . '[i][1]}  ;});';
            $content .= '$("#' . $name . '-datas-period-value-' . $this->station_guid . '").html("");';
            $content .= '$(js_array_' . $js_name . '_p_' . $this->station_guid . ').each(function (i) {';
            $content .= '$("#' . $name . '-datas-period-value-' . $this->station_guid . '").append("<option value="+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][0]+">"+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][1]+"</option>");});';
            $content .= '$("#' . $name . '-datas-period-value-' . $this->station_guid . '" ).change();});';
            $content .= '$("#' . $name . '-datas-period-value-' . $this->station_guid . '").change(function() {';
            $content .= '$("#' . $name . '-datas-template-' . $this->station_guid . '" ).change();});';
        }
        if ($this->module_type == 'lines' || $this->module_type == 'bars' || $this->module_type == 'sareas' || $this->module_type == 'windrose' || $this->module_type == 'astream' || $this->module_type == 'distributionrc' || $this->module_type == 'valuerc') {
            $content .= '$("#' . $name . '-datas-dimension-' .$this->station_guid . '").change(function() {';
            for ($i=1; $i<=$this->series_number; $i++) {
                $content .= '$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . ' option[value=\'0\']").attr("selected", true);';
            }
            $content .= '$("#' . $name . '-datas-module-1-' . $this->station_guid . '").change();});';
        }
        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . '").change(function() {';
            $content .= 'var js_array_' . $js_name . '_measurement_' . $this->station_guid . ' = js_array_' . $js_name . '_' . $this->station_guid . '[$(this).val()][2];';
            $content .= '$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").html("");';
            $content .= '$(js_array_' . $js_name . '_measurement_' . $this->station_guid . ').each(function (i) {';
            if ($this->module_type == 'lines' || $this->module_type == 'bars' || $this->module_type == 'sareas') {
                $content .= '$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").append("<option value="+i+" "+((js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][3] != $("#' . $name . '-datas-dimension-' . $this->station_guid . '").val() && js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][1] != "none") ? "disabled" : "")+">"+js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
            }
            elseif (($this->module_type == 'windrose' && $i == 1) || ($this->module_type == 'astream' && $i == 1) || ($this->module_type == 'valuerc' && $i == 1) || $this->module_type == 'distributionrc') {
                $content .= '$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").append("<option value="+i+" "+((js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][3] != "angle" && js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][1] != "none") ? "disabled" : "")+">"+js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
            }
            elseif (($this->module_type == 'windrose' && $i != 1) || ($this->module_type == 'astream' && $i != 1) || ($this->module_type == 'valuerc' && $i != 1)) {
                $content .= '$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").append("<option value="+i+" "+(((js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][3] == "angle" || js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][1] == "rain_day_aggregated" || js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][1] == "strike_count") && js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][1] != "none") ? "disabled" : "")+">"+js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
            }
            else {
                $content .= '$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").append("<option value="+i+">"+js_array_' . $js_name . '_measurement_' . $this->station_guid . '[i][0]+"</option>");});';
            }
            $content .= '$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '" ).change();});';
            $content .= '$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").change(function() {';
            if ($this->module_type == 'lines' || $this->module_type == 'bars') {
                $content .= 'if ($("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").val() == 0) {';
                $content .= '$("#' . $name . '-datas-line-mode-' . $i . '-' . $this->station_guid . ' option[value=\'line\']").attr("selected", true);';
                $content .= '$("#' . $name . '-datas-dot-style-' . $i . '-' . $this->station_guid . ' option[value=\'none\']").attr("selected", true);';
                $content .= '$("#' . $name . '-datas-line-style-' . $i . '-' . $this->station_guid . ' option[value=\'solid\']").attr("selected", true);';
                $content .= '$("#' . $name . '-datas-line-size-' . $i . '-' . $this->station_guid . ' option[value=\'regular\']").attr("selected", true);};';
            }
            if ($this->module_type == 'sareas') {
                $content .= 'if ($("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").val() == 0) {';
                $content .= '$("#' . $name . '-datas-line-mode-' . $i . '-' . $this->station_guid . ' option[value=\'line\']").attr("selected", true);';
                $content .= '$("#' . $name . '-datas-dot-style-' . $i . '-' . $this->station_guid . ' option[value=\'res-10\']").attr("selected", true);';
                $content .= '$("#' . $name . '-datas-line-style-' . $i . '-' . $this->station_guid . ' option[value=\'solid\']").attr("selected", true);';
                $content .= '$("#' . $name . '-datas-line-size-' . $i . '-' . $this->station_guid . ' option[value=\'regular\']").attr("selected", true);};';
            }
            if (self::$module_mode == 'yearly') {
                $content .= 'var js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . ' = js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . '").val()][2][$(this).val()][4];';
                $content .= '$("#' . $name . '-datas-set-' . $i . '-' . $this->station_guid . '").html("");';
                $content .= '$(js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . ').each(function (i) {';
                $content .= '$("#' . $name . '-datas-set-' . $i . '-' . $this->station_guid . '").append("<option value="+js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . '[i][0]+">"+js_array_' . $js_name . '_set_' . $i . '_' . $this->station_guid . '[i][1]+"</option>");});';
                $content .= '$("#' . $name . '-datas-set-' . $i . '-' . $this->station_guid . ' option[value=\'avg\']").attr("selected", true);';
                $content .= '$("#' . $name . '-datas-set-' . $i . '-' . $this->station_guid . '" ).change();});';
                $content .= '$("#' . $name . '-datas-set-' . $i . '-' . $this->station_guid . '").change(function() {';
            }
            if ($this->module_type != 'calendarhm') {
                $content .= '$("#' . $name . '-datas-line-mode-' . $i . '-' . $this->station_guid . '" ).change();});';
                $content .= '$("#' . $name . '-datas-line-mode-' . $i . '-' . $this->station_guid . '").change(function() {';
                $content .= 'if ($(this).val() == "transparent" || $(this).val() == "area") {';
                $content .= '$("#' . $name . '-datas-line-style-' . $i . '-' . $this->station_guid . '").prop("disabled", true);';
                $content .= '$("#' . $name . '-datas-line-size-' . $i . '-' . $this->station_guid . '").prop("disabled", true);}';
                $content .= 'else {';
                $content .= '$("#' . $name . '-datas-line-style-' . $i . '-' . $this->station_guid . '").prop("disabled", false);';
                $content .= '$("#' . $name . '-datas-line-size-' . $i . '-' . $this->station_guid . '").prop("disabled", false);}';
                $content .= '$("#' . $name . '-datas-dot-style-' . $i . '-' . $this->station_guid . '" ).change();});';
                $content .= '$("#' . $name . '-datas-dot-style-' . $i . '-' . $this->station_guid . '").change(function() {';
                $content .= '$("#' . $name . '-datas-line-style-' . $i . '-' . $this->station_guid . '" ).change();});';
                $content .= '$("#' . $name . '-datas-line-style-' . $i . '-' . $this->station_guid . '").change(function() {';
                $content .= '$("#' . $name . '-datas-line-size-' . $i . '-' . $this->station_guid . '" ).change();});';
                $content .= '$("#' . $name . '-datas-line-size-' . $i . '-' . $this->station_guid . '").change(function() {';
            }
            $content .= '$("#' . $name . '-datas-template-' . $this->station_guid . '" ).change();});';
        }

        $content .= '$("#' . $name . '-datas-template-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-color-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-color-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-interpolation-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-interpolation-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-timescale-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-timescale-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-valuescale-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-valuescale-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-guideline-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-guideline-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-height-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-height-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-label-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-label-' . $this->station_guid . '").change(function() {';
        $content .= '$("#' . $name . '-datas-data-' . $this->station_guid . '" ).change();});';
        $content .= '$("#' . $name . '-datas-data-' . $this->station_guid . '").change(function() {';

        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= 'if (typeof js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . '").val()] !== "undefined" && typeof js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . '").val()][2][$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").val()] !== "undefined") {';
            $content .= 'var sc_device_' . $i . ' = "' . $this->station_id . '";';
            $content .= 'var sc_module_' . $i . ' = js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . '").val()][1];';
            $content .= 'var sc_measurement_' . $i . ' = js_array_' . $js_name . '_' . $this->station_guid . '[$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . '").val()][2][$("#' . $name . '-datas-measurement-' . $i . '-' . $this->station_guid . '").val()][1];';
            if (self::$module_mode == 'yearly') {
                $content .= 'var sc_set_' . $i . ' = $("#' . $name . '-datas-set-' . $i . '-' . $this->station_guid . '").val();';
                $content .= 'sc_measurement_' . $i . ' = sc_set_' . $i . '+":"+sc_measurement_' . $i . ';';
            }
            if ($this->module_type == 'distributionrc') {
                $content .= 'var sc_line_mode_' . $i . ' = $("#' . $name . '-datas-line-mode-1-' . $this->station_guid . '").val();';
            }
            else {
                $content .= 'var sc_line_mode_' . $i . ' = $("#' . $name . '-datas-line-mode-' . $i . '-' . $this->station_guid . '").val();';
            }
            $content .= 'var sc_dot_style_' . $i . ' = $("#' . $name . '-datas-dot-style-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_line_style_' . $i . ' = $("#' . $name . '-datas-line-style-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_line_size_' . $i . ' = $("#' . $name . '-datas-line-size-' . $i . '-' . $this->station_guid . '").val();';
            $content .= 'var sc_' . $i . ' = "";';
            $content .= ' if (sc_measurement_' . $i . ' != "none" && sc_measurement_' . $i . ' != "none:none") {';
            $content .= '   sc_' . $i . ' = " device_id_' . $i . '=\'"+sc_device_' . $i . '+"\' module_id_' . $i . '=\'"+sc_module_' . $i . '+"\' measurement_' . $i . '=\'"+sc_measurement_' . $i . '+"\' line_mode_' . $i . '=\'"+sc_line_mode_' . $i . '+"\' dot_style_' . $i . '=\'"+sc_dot_style_' . $i . '+"\' line_style_' . $i . '=\'"+sc_line_style_' . $i . '+"\' line_size_' . $i . '=\'"+sc_line_size_' . $i . '+"\'";';
            $content .= ' }';
            $content .= ' }';
        }
        if (self::$module_mode == 'yearly') {
            $content .= 'var sc_period_type = $("#' . $name . '-datas-period-type-' . $this->station_guid . '").val();';
            $content .= 'var sc_period_value = $("#' . $name . '-datas-period-value-' . $this->station_guid . '").val();';
        }
        else {
            $content .= 'var sc_period_type = "none";';
            $content .= 'var sc_period_value = "none";';
        }
        $content .= 'var sc_template = $("#' . $name . '-datas-template-' . $this->station_guid . '").val();';
        $content .= 'var sc_color = $("#' . $name . '-datas-color-' . $this->station_guid . '").val();';
        $content .= 'var sc_interpolation = $("#' . $name . '-datas-interpolation-' . $this->station_guid . '").val();';
        $content .= 'var sc_timescale = $("#' . $name . '-datas-timescale-' . $this->station_guid . '").val();';
        $content .= 'var sc_valuescale = $("#' . $name . '-datas-valuescale-' . $this->station_guid . '").val();';
        $content .= 'var sc_guideline = $("#' . $name . '-datas-guideline-' . $this->station_guid . '").val();';
        $content .= 'var sc_height = $("#' . $name . '-datas-height-' . $this->station_guid . '").val();';
        $content .= 'var sc_label = $("#' . $name . '-datas-label-' . $this->station_guid . '").val();';
        $content .= 'var sc_data = $("#' . $name . '-datas-data-' . $this->station_guid . '").val();';
        $content .= 'var shortcode = "[live-weather-station-graph mode=\'' . self::$module_mode . '\' type=\'' . $this->module_type . '\' template=\'"+sc_template+"\' data=\'"+sc_data+"\' color=\'"+sc_color+"\' label=\'"+sc_label+"\' interpolation=\'"+sc_interpolation+"\' timescale=\'"+sc_timescale+"\' valuescale=\'"+sc_valuescale+"\' guideline=\'"+sc_guideline+"\' height=\'"+sc_height+"\' periodtype=\'"+sc_period_type+"\' periodvalue=\'"+sc_period_value+"\'"';
        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '+sc_' . $i;
        }
        $content .= '+"]";';
        $content .= '$(".lws-preview-id-spinner").addClass("spinner");';
        $content .= '$(".lws-preview-id-spinner").addClass("is-active");';
        $content .= '$.post( "' . LWS_AJAX_URL . '", {action: "lws_query_graph_code", data:sc_data, cache:"no_cache", mode:"' . self::$module_mode . '", type:"' . $this->module_type . '", template:sc_template, label:sc_label, color:sc_color, interpolation:sc_interpolation, timescale:sc_timescale, valuescale:sc_valuescale, guideline:sc_guideline, height:sc_height, periodtype:sc_period_type, periodvalue:sc_period_value, ';
        $t = array();
        for ($i=1; $i<=$this->series_number; $i++) {
            $u = array();
            foreach ($this->graph_allowed_serie as $param) {
                $u[] = $param . '_' . $i . ':sc_' . str_replace('_id', '', $param) . '_' . $i;
            }
            $t[] = implode(', ', $u);
        }
        $content .= implode(', ', $t);
        $content .= '}).done(function(data) {$("#lws-graph-preview").html(data);$(".lws-preview-id-spinner").removeClass("spinner");$(".lws-preview-id-spinner").removeClass("is-active");});';
        $content .= '$("#' . $name . '-datas-shortcode-' . $this->station_guid . '").html(shortcode);});';

        // INIT
        if (self::$module_mode == 'yearly') {
            $content .= 'var js_array_' . $js_name . '_p_' . $this->station_guid . ' = null;';
            $content .= '$(js_array_' . $js_name . '_period_' . $this->station_guid . ').each(function (i) {';
            $content .= 'if (js_array_' . $js_name . '_period_' . $this->station_guid . '[i][0] == $("#' . $name . '-datas-period-type-' . $this->station_guid . '").val()) {js_array_' . $js_name . '_p_' . $this->station_guid . '=js_array_' . $js_name . '_period_' . $this->station_guid . '[i][1]}  ;});';
            $content .= '$("#' . $name . '-datas-period-value-' . $this->station_guid . '").html("");';
            $content .= '$(js_array_' . $js_name . '_p_' . $this->station_guid . ').each(function (i) {';
            $content .= '$("#' . $name . '-datas-period-value-' . $this->station_guid . '").append("<option value="+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][0]+">"+js_array_' . $js_name . '_p_' . $this->station_guid . '[i][1]+"</option>");});';
        }

        for ($i=1; $i<=$this->series_number; $i++) {
            $content .= '$("#' . $name . '-datas-module-' . $i . '-' . $this->station_guid . '").change();';
        }

        return $content;
    }
}