<?php

namespace WeatherStation\Utilities;

use WeatherStation\Data\Arrays\Generator as Arrays;
use WeatherStation\Data\Output;

/**
 * Add features to pages to get options settings.
 *
 * @package Includes\Classes
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */
class Settings {

    use Arrays, Output{
        Output::get_service_name insteadof Arrays;
        Output::get_comparable_dimensions insteadof Arrays;
        Output::get_module_type insteadof Arrays;
        Output::get_fake_module_name insteadof Arrays;
        Output::get_measurement_type insteadof Arrays;
        Output::get_dimension_name insteadof Arrays;
        Output::get_operation_name insteadof Arrays;
    }

}
