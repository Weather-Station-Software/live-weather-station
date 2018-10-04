<?php

namespace WeatherStation\Process;
use WeatherStation\System\Notifications\Notifier;
use WeatherStation\System\Cache\Cache;
use WeatherStation\System\Quota\Quota;
use WeatherStation\System\Schedules\Watchdog;
use WeatherStation\System\Logs\Logger;
use WeatherStation\DB\Storage;
use WeatherStation\System\Schedules\Handling as Schedules;
use WeatherStation\System\Data\Data;

/**
 * The base class of process.
 *
 * @package Includes\Process
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */
abstract class Process {

    use Schedules, Storage;

    protected $class = 'WeatherStation\Process';
    protected $state = 'init';
    protected $params = array();
    protected $timestamp = '0000-00-00 00:00:00';
    protected $exectime = 0;
    protected $pass = 0;
    protected $progress = 0;

    private $chrono = 0.0;
    protected $facility = 'Background Process';

    protected $state_init = 'init';
    protected $state_pause = 'pause';
    protected $state_schedule = 'schedule';
    protected $state_running = 'running';
    protected $state_unneeded = 'unneeded';
    protected $state_end = 'end';



    /**
     * Initialize the class and set its properties.
     *
     * @param string $class The class of the process.
     * @param array $params The parameters of the process.
     * @param string $state The state of the process.
     * @param string $timestamp The timestamp of the last state change of the process.
     * @param int $exectime The execution time of the process so far.
     * @since 3.6.0
     */
    protected function init($class='WeatherStation\Process', $params=array(), $state='init', $timestamp='0000-00-00 00:00:00', $exectime=0) {
        $this->class = $class;
        $this->state = $state;
        $this->params = $params;
        $this->timestamp = $timestamp;
        $this->exectime = $exectime;
    }

    /**
     * Change the state of the process.
     *
     * @param string $new_state The new state to set.
     * @since 3.6.0
     */
    protected function change_state($new_state='init') {
        $this->state = $new_state;
        $this->timestamp = date('Y-m-d H:i:s');
    }

    /**
     * Change the state of the process.
     *
     * @param integer $value The new progress value.
     * @since 3.6.0
     */
    protected function set_progress($value) {
        $this->progress = (int)round($value);
    }

    /**
     * Get the UUID of the process.
     *
     * @return string The UUID of the process.
     * @since 3.6.0
     */
    protected abstract function uuid();

    /**
     * Get the execution mode of the process.
     * Can be :
     *   - pause: can be restarted in the same cycle
     *   - schedule: must wait next cycle to be restarted
     *
     * @return string The execution mode of the process.
     * @since 3.6.0
     */
    protected abstract function execution_mode();

    /**
     * Get the name of the process.
     *
     * @param boolean $translated Optional. Indicates if the name must be translated.
     * @return string The name of the process.
     * @since 3.6.0
     */
    protected abstract function name($translated=true);

    /**
     * Get the description of the process.
     *
     * @return string The description of the process.
     * @since 3.6.0
     */
    protected abstract function description();

    /**
     * Get the message for end of process.
     *
     * @return string The message to send.
     * @since 3.6.0
     */
    protected abstract function message();

    /**
     * Get the url of the process doc.
     *
     * @return string The url of the process doc.
     * @since 3.6.0
     */
    protected function url() {
        return 'http://weather.station.software/background-process/' . sanitize_title($this->name(false));
    }

    /**
     * Get the full url of the process doc as html A tag.
     *
     * @return string The full url of the process doc as html A tag.
     * @since 3.6.0
     */
    protected function full_url() {
        if ((bool)get_option('live_weather_station_redirect_external_links')) {
            $target = ' target="_blank" ';
        }
        return '<a href="' . $this->url() . '"' . $target . '>' . __('see details', 'live-weather-station') . '</a>';
    }

    /**
     * Set the notification for init.
     *
     * @since 3.6.0
     */
    protected function init_notification() {
        $s = sprintf(__('A background process named <em>%s</em> has been launched.', 'live-weather-station'), $this->name());
        $s .= ' ' . __('This process may take from minutes to days.', 'live-weather-station');
        $s .= ' ' . __('It will not interfere with the operation of your server and you will be notified by email of the end of treatment.', 'live-weather-station');
        Notifier::warning($this->name(), $this->url(), $s);
    }

    /**
     * Set the notification for end.
     *
     * @since 3.6.0
     */
    protected function end_notification() {
        $s = sprintf(__('The background process named <em>%s</em> has completed successfully.', 'live-weather-station'), $this->name());
        Notifier::info($this->name(), $this->url(), $s);
    }

    /**
     * Get the priority of the process.
     *
     * @return int The priority of the process.
     * @since 3.6.0
     */
    protected abstract function priority();

    /**
     * Verify if process is needed.
     *
     * @return boolean True if the process is needed. False otherwise.
     * @since 3.6.0
     */
    protected abstract function is_needed();

    /**
     * Verify if process is terminated.
     *
     * @return boolean True if the process is terminated. False otherwise.
     * @since 3.6.0
     */
    protected abstract function is_terminated();

    /**
     * Get the process row.
     *
     * @return array The process row.
     * @since 3.6.0
     */
    private function _get() {
        global $wpdb;
        $table = $wpdb->prefix . self::live_weather_station_background_process_table();
        $sql = "SELECT * FROM " . $table . " WHERE `uuid`='" . $this->uuid() . "';";
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Verify if process is already registered.
     *
     * @return boolean True if the process is already registered. False otherwise.
     * @since 3.6.0
     */
    private function is_already_registered() {
        return count($this->_get()) === 1;
    }

    /**
     * Register the process.
     *
     * @param array $args The args to pass to the instance.
     * @since 3.6.0
     */
    public function register($args=array()) {
        $class = new \ReflectionClass(get_class($this));
        $this->init($class->getShortName(), $args);
        if (!$this->is_already_registered()) {
            $is_needed = $this->is_needed();
            $this->change_state($is_needed?$this->state_init:$this->state_unneeded);
            if ($is_needed) {
                $this->init_core();
                $this->init_notification();
            }
            $this->save();
        }
    }

    /**
     * Send a message and a notification when process is finished.
     *
     * @since 3.6.0
     */
    public function send_end_of_process() {
        $detail = $this->message();
        $to = get_bloginfo('admin_email');
        $subject = __('End of the background process:', 'live-weather-station') . ' ' . $this->name();
        $message = __('Hello!', 'live-weather-station') . "\r\n" . "\r\n";
        $message .= sprintf(__('%s informs you that the background process named "%s" has completed successfully.', 'live-weather-station'), LWS_PLUGIN_NAME, $this->name()) . "\r\n" . "\r\n";
        if ($detail !== '') {
            $message .= $detail . "\r\n" . "\r\n";
        }
        $message .= __('Have a nice day.', 'live-weather-station') . "\r\n" . "\r\n";
        $message .= '- - - - - - - - - - - - - - - - - - - - - - -' . "\r\n";
        $message .= __('Name:', 'live-weather-station') . ' ' . $this->name() . "\r\n" ;
        $message .= __('Description:', 'live-weather-station') . ' ' . $this->description() . "\r\n" ;
        $message .= __('More information:', 'live-weather-station') . ' ' . $this->url() . "\r\n" ;
        try {
            wp_mail($to, $subject, $message);
            Logger::debug($this->facility, null, null, null, null, null, 0, 'Mail sent from background process {' . $this->uuid() . '}.');
        }
        catch (\Exception $ex) {
            Logger::error($this->facility, null, null, null, null, null, 999, 'Unable to send mail from background process {' . $this->uuid() . '}. Message: ' . $ex->getMessage());
        }
        $this->end_notification();
    }

    /**
     * Init the process core.
     *
     * @since 3.6.0
     */
    protected abstract function init_core();

    /**
     * Run the process core.
     *
     * @since 3.6.0
     */
    protected abstract function run_core();

    /**
     * Load the process details.
     *
     * @throws \Exception
     * @since 3.6.0
     */
    protected function load() {
        $row = $this->_get();
        if (count($row) !== 1) {
            throw new \Exception('Database contains inconsistent data');
        }
        else {
            $this->class = $row[0]['class'];
            $this->state = $row[0]['state'];
            $this->timestamp = $row[0]['timestamp'];
            $this->params = unserialize($row[0]['params']);
            $this->exectime = $row[0]['exec_time'];
            $this->pass = $row[0]['pass'];
            $this->progress = $row[0]['progress'];
        }
    }

    /**
     * Save the process details.
     *
     * @since 3.6.0
     */
    protected function save() {
        $row = array();
        $row['uuid'] = $this->uuid();
        $row['priority'] = $this->priority();
        $row['class'] = $this->class;
        $row['name'] = $this->name();
        $row['description'] = $this->description();
        $row['state'] = $this->state;
        $row['timestamp'] = $this->timestamp;
        $row['params'] = serialize($this->params);
        $row['exec_time'] = $this->exectime;
        $row['pass'] = $this->pass;
        $row['progress'] = $this->progress;
        self::insert_update_table(self::live_weather_station_background_process_table(), $row);
    }

    /**
     * Run the process wrapper.
     *
     * @param boolean $count_as_pass Optional. It's a full pass!
     * @since 3.6.0
     */
    public function run($count_as_pass=true) {
        try {
            $this->chrono = microtime(true);
            $this->load();
            $this->change_state($this->state_running);
            $this->save();
            $this->run_core();
            if ($this->is_terminated()) {
                $this->change_state($this->state_end);
                $this->send_end_of_process();
            }
            else {
                $this->change_state($this->execution_mode());
            }
            $this->chrono = microtime(true) - $this->chrono;
            $this->exectime += (int)round($this->chrono, 0);
            if ($count_as_pass) {
                $this->pass += 1;
            }
            $this->save();
        }
        catch (\Exception $ex) {
            $this->change_state($this->execution_mode());
            $this->save();
            Logger::error($this->facility, null, null, null, null, null, 999, 'Unable to run background process {' . $this->uuid() . '}. Message: ' . $ex->getMessage());
        }
    }

}