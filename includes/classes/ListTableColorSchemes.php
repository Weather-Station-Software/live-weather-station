<?php

namespace WeatherStation\UI\ListTable;

use WeatherStation\System\Options\Handling as Options;

/**
 * Color schemes list table for Weather Station plugin.
 *
 * @package Includes\Classes
 * @author WordPress
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
class ColorSchemes extends Base {

    public function __construct(){
        parent::__construct(array('singular' => 'palette', 'plural' => 'palettes', 'ajax' => true));
    }

    protected function column_default($item, $column_name){
        return $item[$column_name];
    }

    protected function column_colors($item){
        $s = '';
        foreach ($item['colors'] as $color) {
            $s .= '<i class="' . LWS_FAS . ' fa-lg fa-fw fa-circle" style="color:#' . $color . '"></i>';
        }
        return $s;
    }

    protected function column_name($item){
        $actions['edit'] = sprintf('<a href="?page=lws-settings&action=form&tab=edit&service=palette&id=%s">'.__('Modify', 'live-weather-station').'</a>', $item['id']);
        $actions['reset'] = sprintf('<a href="?page=lws-settings&action=reset-cschemes&tab=styles&id=%s">'.__('Reset', 'live-weather-station').'</a>', $item['id']);
        return '<i style="color:#999" class="' . LWS_FAS . ' fa-lg fa-fw fa-palette"></i>&nbsp;' . $item['name'] . $this->row_actions($actions);
    }

    public function get_columns(){
        $columns = array('name' => __('Name', 'live-weather-station'), 'colors' => __('Colors', 'live-weather-station'));
        return $columns;
    }

    protected function get_hidden_columns() {
        return array();
    }

    protected function get_sortable_columns() {
        return array();
    }

    public function get_bulk_actions() {
        return array();
    }

    public function prepare_items(){
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data = array();
        foreach (Options::get_cschemes() as $key=>$palette) {
            $a = array();
            $a['id'] = $key;
            $a['name'] = $palette['name'];
            $a['colors'] = $palette['colors'];
            $data[] = $a;
        }
        $this->items = $data;
    }
}