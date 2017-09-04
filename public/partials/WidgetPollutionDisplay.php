<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-pollution lws-widget-outer-pollution-<?php echo $id ?>">
        <div class="lws-widget-pollution lws-widget-pollution-<?php echo $id ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- CURRENT CONDITIONS -->
                <div class="lws-widget-header lws-widget-header-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current weather conditions', 'live-weather-station').'"' : ''); ?>>
                    <i class="wi wi-owm<?php echo $datas['day']['value']; ?>-<?php echo $datas['weather']['value']; ?>"></i>
                </div>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
            <!-- STATION NAME -->
            <div class="lws-widget-row lws-widget-row-<?php echo $id ?>">
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <?php if ( $show_title ):?>
                        <div class="lws-widget-title lws-widget-title-<?php echo $id ?>"><?php echo $title; ?></div>
                    <?php endif;?>
                    <?php if ( $subtitle == 1 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo $id ?>"><?php echo $timestamp; ?></div>
                    <?php endif;?>
                    <?php if ( $subtitle == 2 && $location != '' ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo $id ?>"><?php echo $location; ?></div>
                    <?php endif;?>
                </div>
            </div>
            <?php if ($show_temperature && $temp_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['temperature']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['temperature']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-thermometer" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['temperature_max']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['temperature_max']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['temperature_min']['value']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $datas['temperature_min']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_temperature && !$temp_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-thermometer"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['temperature']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['temperature']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_pressure):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- PRESSURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Atmospheric pressure', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-barometer"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['pressure']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['pressure']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_humidity):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- HUMIDITY -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Humidity', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-humidity"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['humidity']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['humidity']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_wind && $wind_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- WIND -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.__('Wind from', 'live-weather-station').' '.$datas['windangle']['from'].'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['windstrength']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['windstrength']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x42-<?php echo $id ?> wi-wind <?php echo $windsemantic ?>-<?php echo $datas['windangle']['value']; ?>-deg"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['windstrength_max']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['windstrength_max']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php esc_html_e('max', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_wind && !$wind_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- WIND -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.__('Wind from', 'live-weather-station').' '.$datas['windangle']['from'].'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x42-<?php echo $id ?> wi-wind <?php echo $windsemantic ?>-<?php echo $datas['windangle']['value']; ?>-deg"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['windstrength']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['windstrength']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_rain && $rain_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- RAIN -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Rainfall', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['rain']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['rain']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-umbrella"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['rain_day_aggregated']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['rain_day_aggregated']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php esc_html_e('today', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_rain && !$rain_multipart):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- RAIN -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Rainfall', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-umbrella"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['rain']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['rain']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_snow):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- RAIN -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Snowfall', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-snowflake-cold"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['snow']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['snow']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_cloud_cover):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- CLOUDINESS -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Cloudiness', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-cloud"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['cloudcover']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['cloudcover']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_cloud_ceiling):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- CLOUD BASE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Cloud base altitude', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-cloud-up"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['cloudceiling']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['cloudceiling']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- DEW POINT -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Dew point', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-raindrops"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['dew']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['dew']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_frost):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- FROST POINT -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Frost point', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-stars"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['frost']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['frost']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_heat):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- HEAT INDEX -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Heat index', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-thermometer-internal"></i><i class="wi wi-x26 wi-degrees"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['heat']['value']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_humidex):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- HUMIDEX -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Humidex', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-thermometer-internal"></i><i class="wi wi-x26 wi-degrees"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['humidex']['value']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_windchill):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- WIND CHILL -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Wind chill', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-strong-wind"></i><i class="wi wi-x26 wi-degrees"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['windchill']['value']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>