<?php

namespace WeatherStation\Process;
use WeatherStation\DB\Query as DB;
use WeatherStation\System\Options\Handling as Options;
use WeatherStation\System\Storage\Manager as FS;
use WeatherStation\System\Logs\Logger;
use WeatherStation\Data\DateTime\Handling as DateTimeHandling;

/**
 * A process to export data line after line.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */
class ConfigurationExporter extends Process {

    use DateTimeHandling;

    protected $extension = 'wsconf.json';
    protected $fullfilename = null;
    protected $facility = 'Export Manager';


    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.8.0
     */
    protected function uuid() {
        return $this->generate_v4_uuid();
    }

    /**
     * Get the execution mode of the process.
     * Can be :
     *   - pause: can be restarted in the same cycle
     *   - schedule: must wait next cycle to be restarted
     *
     * @return string The execution mode of the process.
     * @since 3.8.0
     */
    protected function execution_mode() {
        return $this->state_schedule;
    }

    /**
     * Get the priority of the process.
     *
     * @return int The priority of the process.
     * @since 3.8.0
     */
    protected function priority(){
        return 20;
    }

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.8.0
     */
    protected function name($translated=true) {
        if ($translated) {
            return lws__('Configuration exporter', 'live-weather-station');
        }
        else {
            return 'Configuration exporter';
        }
    }

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.8.0
     */
    protected function description() {
        return lws__('Exporting all settings of Weather Station plugin as a JSON file.', 'live-weather-station');
    }

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.8.0
     */
    protected function is_needed() {
        return true;
    }

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.8.0
     */
    protected function is_terminated(){
        return ($this->params['todo'] <= $this->params['done']);
    }

    /**
     * Verify if process is in error.
     *
     * @return boolean True if the process is in error. False otherwise.
     * @since 3.8.0
     */
    protected function is_in_error(){
        return $this->params['error'];
    }

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.8.0
     */
    protected function message() {
        if ($this->is_in_error()) {
            $result = sprintf(__('Unable to create the file named %s in the directory "%s".', 'live-weather-station'), FS::get_file_name($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension), FS::get_root_name()) . "\r\n";
            $result .= "\r\n" . sprintf(__('Check the events log to see what\'s going on: %s', 'live-weather-station'), lws_get_admin_page_url('lws-events')) . "\r\n";
        }
        else {
            $fileurl = FS::get_full_file_url($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension);
            $result = sprintf(lws__('Configuration from "%s" has been successfully exported.', 'live-weather-station'), $this->params['init']['station_name']) . "\r\n";
            $result .= sprintf(__('The file is now ready to download. It will be kept on your server for %s days.', 'live-weather-station'), get_option('live_weather_station_file_retention', '7')) . "\r\n";
            $result .= "\r\n" . $fileurl . "\r\n";
        }
        return $result;
    }

    /**
     * Init the process.
     *
     * @since 3.8.0
     */
    protected function init_core(){
        $this->uuid = $this->meta_uuid();
        $datetime = new \DateTime('now', new \DateTimeZone($this->get_site_timezone()));
        $date = $datetime->format('Y-m-d');
        $this->params['init']['station_name'] = trim(get_bloginfo('name'));
        $this->params['todo'] = 1;
        $this->params['done'] = 0;
        $this->params['init']['start_date'] = $date;
        $this->params['init']['end_date'] = $date;
        $this->params['error'] = (false === FS::file_for_write($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension));
    }

    /**
     * Do the main process job.
     *
     * @since 3.8.0
     */
    protected function do_job() {
        $conf = array();
        $conf['settings'] = Options::get_all_options();
        $conf['stations'] = DB::get_stations_table();
        $conf['modules'] = DB::get_modules_table();
        $conf['maps'] = DB::get_maps_table();
        FS::write_file($this->fullfilename, wp_json_encode($conf));
    }

    /**
     * Run the process.
     *
     * @since 3.8.0
     */
    protected function run_core(){
        $this->fullfilename = FS::get_full_file_name($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension);
        if (!file_exists($this->fullfilename)) {
            if (!FS::create_file($this->params['init']['station_name'], $this->params['init']['start_date'], $this->params['init']['end_date'], $this->uuid, $this->extension)) {
                $this->params['error'] = true;
            }
            else {
                $this->do_job();
            }
        }
        $this->params['done'] = 1;
        if (!$this->is_in_error()) {
            Logger::notice('Export Manager', null, null, null, null, null, null, 'Configuration has been successfully exported.');
        }
        else {
            Logger::error('Export Manager', null, null, null, null, null, null, 'Unable to export configuration.');
        }
        $this->set_progress(100);
    }

}