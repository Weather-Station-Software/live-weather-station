<?php

namespace WeatherStation\Engine\Page\Standalone;

use WeatherStation\System\Logs\Logger;
use WeatherStation\System\Logs\LoggableException;

require_once(dirname(__FILE__) . '/PageStandaloneFramework.php');

/**
 * Generic class to generate standalone page in the wordpress context.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Generator extends Framework {

    /**
     * Get available args.
     *
     * @since 3.0.0
     */
    protected function available_args() {
        $result = array();
        $result['type'] = ['stickertags.txt', 'YoWindow.xml'];
        $result['fields'] = ['station'];
        $result['variables']['station'] = '@/([A-Z0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2}:[A-F0-9]{2})/@i';
        return $result;
    }

    /**
     * Use the right generator to render the file.
     *
     * @since 3.0.0
     */
    protected function generate() {
        try {
            $classname = ucfirst($this->type);
            $file = LWS_INCLUDES_DIR . 'classes/PageStandalone' . $classname . 'Generator.php';
            if (!file_exists($file)) {
                $this->error();
            }
            $classname = '\WeatherStation\Engine\Page\Standalone\\' . $classname;
            $generator = new $classname;
            $generator->send($this->params, $this->subformat);
        }
        catch(LoggableException $ex) {
            Logger::exception($ex);
            $code = $ex->getCode();
            $message = $ex->getMessage();
            if ($code != 0) {
                $this->error($code, $message);
            }
            else {
                $this->error();
            }
        }
        catch(\Exception $ex) {
            $code = $ex->getCode();
            $message = $ex->getMessage();
            if ($code != 0) {
                $this->error($code, $message);
            }
            else {
                $this->error();
            }
        }
    }
}