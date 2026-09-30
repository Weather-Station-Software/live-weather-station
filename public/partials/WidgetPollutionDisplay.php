<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * @package Public\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo esc_attr($id) ?>">
    <div class="lws-widget-outer-pollution lws-widget-outer-pollution-<?php echo esc_attr($id) ?>">
        <div class="lws-widget-pollution lws-widget-pollution-<?php echo esc_attr($id) ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- CURRENT CONDITIONS -->
                <div class="lws-widget-header lws-widget-header-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current weather conditions', 'live-weather-station').'"' : ''); ?>>
                    <i class="wi wi-owm<?php echo esc_attr($measurements['day']['value']); ?>-<?php echo esc_attr($measurements['weather']['value']); ?>"></i>
                </div>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
            <?php endif;?>
            <!-- STATION NAME -->
            <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>">
                <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                    <?php if ( $show_title ):?>
                        <div class="lws-widget-title lws-widget-title-<?php echo esc_attr($id) ?>"><?php echo esc_html($title); ?></div>
                    <?php endif;?>
                    <?php if ( $subtitle == 1 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($timestamp); ?></div>
                    <?php endif;?>
                    <?php if ( $subtitle == 2 && $location != '' ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($location); ?></div>
                    <?php endif;?>
                </div>
            </div>
            <?php if ($show_temperature && $temp_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x32-<?php echo esc_attr($id) ?> wi-thermometer" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature_max']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature_max']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature_min']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature_min']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_temperature && !$temp_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-thermometer"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_pressure):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- PRESSURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Atmospheric pressure', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-barometer"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['pressure']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['pressure']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_humidity):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- HUMIDITY -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Relative humidity', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-humidity"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['humidity']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['humidity']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_wind && $wind_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- WIND -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_attr(__('Wind from', 'live-weather-station').' '.$measurements['windangle']['from']).'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['windstrength']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['windstrength']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x42-<?php echo esc_attr($id) ?> wi-wind <?php echo esc_attr($windsemantic) ?>-<?php echo esc_attr($measurements['windangle']['value']); ?>-deg"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['windstrength_max']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['windstrength_max']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php esc_html_e('max', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_wind && !$wind_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- WIND -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_attr(__('Wind from', 'live-weather-station').' '.$measurements['windangle']['from']).'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x42-<?php echo esc_attr($id) ?> wi-wind <?php echo esc_attr($windsemantic) ?>-<?php echo esc_attr($measurements['windangle']['value']); ?>-deg"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['windstrength']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['windstrength']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_rain && $rain_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- RAIN -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Rainfall', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['rain']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['rain']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x32-<?php echo esc_attr($id) ?> wi-umbrella"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['rain_day_aggregated']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['rain_day_aggregated']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php esc_html_e('today', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_rain && !$rain_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- RAIN -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Rainfall', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-umbrella"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['rain']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['rain']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_snow):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- RAIN -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Snowfall', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-snowflake-cold"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['snow']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['snow']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_cloud_cover):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- CLOUDINESS -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Cloudiness', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-cloud"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['cloudcover']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['cloudcover']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_cloud_ceiling):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- CLOUD BASE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Cloud base altitude', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-cloud-up"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['cloudceiling']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['cloudceiling']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- DEW POINT -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Dew point', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-raindrops"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['dew']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['dew']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_frost):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- FROST POINT -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Frost point', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-stars"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['frost']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['frost']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_heat):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- HEAT INDEX -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Heat index', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-thermometer-internal"></i><i class="wi wi-x26 wi-degrees"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['heat']['value']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_humidex):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- HUMIDEX -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Humidex', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-thermometer-internal"></i><i class="wi wi-x26 wi-degrees"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['humidex']['value']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_windchill):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- WIND CHILL -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Wind chill', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <i class="wi wi-x26-<?php echo esc_attr($id) ?> wi-strong-wind"></i><i class="wi wi-x26 wi-degrees"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['windchill']['value']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>