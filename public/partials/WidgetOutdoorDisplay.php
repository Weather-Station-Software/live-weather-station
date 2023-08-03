<?php
/**
 * @package Public\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-outdoor lws-widget-outer-outdoor-<?php echo $id ?>">
        <div class="lws-widget-outdoor lws-widget-outdoor-<?php echo $id ?> noTypo">
        <?php if ( $show_current ):?>
            <!-- CURRENT CONDITIONS -->
            <div class="lws-widget-header lws-widget-wiheader-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current weather conditions', 'live-weather-station').'"' : ''); ?>>
                <?php echo $measurements['weather']['icon']; ?>
            </div>
            <?php if (($show_title || $subtitle != 0) || $show_temperature || $show_pressure || $show_humidity || $show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_title || $subtitle != 0):?>
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
            <?php if ($show_temperature || $show_pressure || $show_humidity || $show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_temperature && $temp_multipart):?>
            <!-- TEMPERATURE -->
            <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $measurements['temperature']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $measurements['temperature']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['temperature']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $measurements['temperature_max']['value']; ?></div>
                        <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $measurements['temperature_max']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $measurements['temperature_min']['value']; ?></div>
                        <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $measurements['temperature_min']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_pressure || $show_humidity || $show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_temperature && !$temp_multipart):?>
            <!-- TEMPERATURE -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['temperature']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['temperature']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['temperature']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_pressure || $show_humidity || $show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_pressure):?>
            <!-- PRESSURE -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Barometric pressure', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['pressure_sl']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['pressure_sl']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['pressure_sl']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_humidity || $show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_humidity):?>
            <!-- HUMIDITY -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Relative humidity', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['humidity']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['humidity']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['humidity']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_uv):?>
            <!-- UV -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('UV', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['uv_index']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['uv_index']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['uv_index']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_wind && $wind_multipart):?>
            <!-- WIND -->
            <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.__('Wind from', 'live-weather-station').' '.$measurements['windangle']['from'].'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $measurements['windstrength']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $measurements['windstrength']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['windangle']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $measurements['windstrength_max']['value']; ?></div>
                        <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $measurements['windstrength_max']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php esc_html_e('max', 'live-weather-station'); ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_wind && !$wind_multipart):?>
            <!-- WIND -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.__('Wind from', 'live-weather-station').' '.$measurements['windangle']['from'].'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['windangle']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['windstrength']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['windstrength']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_rain && $rain_multipart):?>
            <!-- RAIN -->
            <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Rainfall', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $measurements['rain']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $measurements['rain']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['rain']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $measurements['rain_day_aggregated']['value']; ?></div>
                        <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $measurements['rain_day_aggregated']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php esc_html_e('today', 'live-weather-station'); ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_rain && !$rain_multipart):?>
            <!-- RAIN -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Rainfall', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['rain']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['rain']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['rain']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>

        <?php if ($show_strike):?>
            <!-- THUNDERSTORM -->
            <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Thunderstorm', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $measurements['strike']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $measurements['strike']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['strike']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $measurements['strike_distance']['value']; ?></div>
                        <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $measurements['strike_distance']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $measurements['strike_distance']['ts']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ( $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>

        <?php if ($show_snow):?>
            <!-- SNOW -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Snowfall', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['snow']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['snow']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['snow']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_cloud_cover):?>
            <!-- CLOUDINESS -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Cloudiness', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['cloudcover']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['cloudcover']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['cloudcover']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_cloud_ceiling):?>
            <!-- CLOUD BASE -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Cloud base altitude', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['cloudceiling']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['cloudceiling']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['cloudceiling']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_dew):?>
            <!-- DEW POINT -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Dew point', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['dew']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['dew']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['dew']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_frost):?>
            <!-- FROST POINT -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Frost point', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['frost']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['frost']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $measurements['frost']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_heat):?>
            <!-- HEAT INDEX -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Heat index', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['heat']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['heat']['value']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_windchill || $show_humidex || $show_steadman || $show_summer_simmer):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_humidex):?>
            <!-- HUMIDEX -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Humidex', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['humidex']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['humidex']['value']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_windchill || $show_steadman || $show_summer_simmer):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>

        <?php if ($show_summer_simmer):?>
            <!-- SUMMER_SIMMER -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Summer Simmer index', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['summer_simmer']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['summer_simmer']['value']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_windchill || $show_steadman):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>

        <?php if ($show_steadman):?>
            <!-- STEADMAN -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Steadman index', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['steadman']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['steadman']['value']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_windchill):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>


        <?php if ($show_windchill):?>
            <!-- WIND CHILL -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Wind chill', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $measurements['windchill']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $measurements['windchill']['value']; ?></div>
                    </div>
                </div>
            </div>
        <?php endif;?>
        </div>
    </div>
</div>