<?php

namespace WeatherStation\UI\ListTable;

use WeatherStation\Data\Output;
use WeatherStation\System\Schedules\Handling as Schedule;
use WeatherStation\System\Analytics\Performance;

/**
 * Scheduled tasks list table for Weather Station plugin.
 *
 * @package Includes\Classes
 * @author WordPress
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.2.0
 */
class Tasks extends Base {

    use Schedule;

    private $schedules_description = array();
    private $schedules_performance = array();
    private $ts_none = 999999999999;

    public function __construct(){
        parent::__construct(array('singular' => 'scheduled task', 'plural' => 'scheduled tasks', 'ajax' => true));
    }

    protected function column_default($item, $column_name){
        return $item[$column_name];
    }

    private function get_icon($pool, $cron) {
        $result = '';
        switch ($pool) {
            case 'system' :
                $result = '<i style="color:#999" class="fa fa-lg fa-fw fa-cog"></i>&nbsp;';
                break;
            case 'pull' :
                $result = '<i style="color:#999" class="fa fa-lg fa-fw fa-cloud-download"></i>&nbsp;';
                break;
            case 'push' :
                $result = '<i style="color:#999" class="fa fa-lg fa-fw fa-share-alt"></i>&nbsp;';
                break;
            case 'history' :
                $result = '<i style="color:#999" class="fa fa-lg fa-fw fa-history"></i>&nbsp;';
                break;
            default :
                $result = '<i style="color:#999" class="fa fa-lg fa-fw fa-random"></i>&nbsp;';
                break;
        }
        if ($cron == self::$watchdog_name) {
            $result = '<i style="color:#999" class="fa fa-lg fa-fw fa-cogs"></i>&nbsp;';
        }
        return $result;
    }

    protected function column_task($item){

        $s = $this->get_icon($item['pool'], $item['hook']) . $item['task'];
        $s .= '<br/><span style="color:silver">' . ucfirst(sprintf(__('%s pool', 'live-weather-station'), self::get_pool_name($item['pool'])) ). '</span>';
        return $s;
    }

    protected function column_frequency($item){
        $result = '<i>- ' . __('disabled task', 'live-weather-station') . ' -</i>';
        if ($item['frequency']) {
            if (array_key_exists($item['frequency'], $this->schedules_description)) {
                $result = $this->schedules_description[$item['frequency']]['display'];
            }
        }
        return $result;
    }

    protected function column_avr($item){
        $result = __('unknown', 'live-weather-station');
        if ($item['avr'] >= 0) {
            $result = $item['avr'] . '&nbsp;' . __('ms', 'live-weather-station');
        }
        if (!$item['frequency'] || $item['count'] == 0) {
            $result = '-';
        }

        return $result;
    }

    protected function column_next($item){
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'next';
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc';
        $sort = '&orderby=' . $orderby . '&order=' . $order;
        $actions = array();
        if ($item['next'] != $this->ts_none) {
            $actions['force'] = sprintf('<a href="?page=lws-settings&tab=tasks&action=cron-force&hook=%s' . $sort . '">' . __('Force execution now', 'live-weather-station') . '</a>', $item['hook']);
        }
        if ($item['next'] != $this->ts_none) {
            if (wp_next_scheduled($item['hook']) < wp_get_schedules()[wp_get_schedule($item['hook'])]['interval'] + time()) {
                $actions['reschedule'] = sprintf('<a href="?page=lws-settings&tab=tasks&action=cron-reschedule&hook=%s' . $sort . '">' . __('Reschedule', 'live-weather-station') . '</a>', $item['hook']);
            }
        }
        $result = '-';
        if ($item['hook'] == self::$watchdog_name) {
            $actions = array();
            $actions['relaunch'] = '<a href="?page=lws-settings&tab=tasks&action=relaunch-watchdog' . $sort . '">' . __('Restart', 'live-weather-station') . '</a>';
        }
        if ($item['next'] != $this->ts_none) {
            $result = ucfirst(sprintf( __('in %s', 'live-weather-station'), human_time_diff(time(), $item['next'])));
            $result .= $this->row_actions($actions);
        }
        return $result;
    }

    public function get_columns(){
        $columns = array('task' => __('Task', 'live-weather-station'),
                        'frequency' => __('Frequency', 'live-weather-station'),
                        'avr' => __('Average duration', 'live-weather-station'),
                        'next' => __('Next execution', 'live-weather-station'));
        return $columns;
    }

    protected function get_hidden_columns() {
        return array();
    }

    protected function get_sortable_columns() {
        $sortable_columns = array();
        $sortable_columns['task'] = array('task',false);
        $sortable_columns['avr'] = array('avr',false);
        $sortable_columns['next'] = array('next',false);
        return $sortable_columns;
    }

    public function get_bulk_actions() {
        return array();
    }

    public function usort_reorder($a,$b){
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'next';
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc';
        if ($orderby === 'avr') {
            $result = $a[$orderby] - $b[$orderby];
        }
        else {
            $result = strcmp(strtolower($a[$orderby]), strtolower($b[$orderby]));
        }
        return ($order==='asc') ? $result : -$result;
    }

    public function prepare_items()
    {
        $this->schedules_description = wp_get_schedules();
        $perf = Performance::get_cron_values();
        if (array_key_exists('raw', $perf)) {
            $this->schedules_performance = $perf['raw'];
        }
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $data = array();
        foreach (array_merge(self::$cron_system, self::$cron_pull, self::$cron_push, self::$cron_history) as $cron) {
            $a = array();
            $a['hook'] = $cron;
            $a['task'] = self::get_cron_name($cron);
            $a['pool'] = self::get_cron_pool($cron);
            $a['frequency'] = wp_get_schedule($cron);
            $a['time'] = -1;
            $a['count'] = 0;
            if (array_key_exists($cron, $this->schedules_performance)) {
                $a['time'] = $this->schedules_performance[$cron]['time'];
                $a['count'] = $this->schedules_performance[$cron]['count'];
            }
            if ($a['count'] > 0) {
                $a['avr'] = round($a['time']/$a['count'], 0);
            }
            else {
                $a['avr'] = -1;
            }
            $a['next'] = wp_next_scheduled($cron);
            if (!$a['next']) {
                $a['next'] = $this->ts_none;
            }
            $data[] = $a;
        }
        usort($data, array($this, 'usort_reorder'));
        $this->items = $data;
    }
}