<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
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
                <?php echo $datas['weather']['icon']; ?>
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
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['temperature']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['temperature']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['temperature']['icon']; ?>
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
            <?php if ($show_pressure || $show_humidity || $show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_temperature && !$temp_multipart):?>
            <!-- TEMPERATURE -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['temperature']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['temperature']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['temperature']['unit']; ?></div>
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
                    <?php echo $datas['pressure_sl']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['pressure_sl']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['pressure_sl']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_humidity || $show_uv || $show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_humidity):?>
            <!-- HUMIDITY -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Humidity', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['humidity']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['humidity']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['humidity']['unit']; ?></div>
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
                    <?php echo $datas['uv_index']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['uv_index']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['uv_index']['unit']; ?></div>
                    </div>
                </div>
            </div>
            <?php if ($show_wind || $show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_wind && $wind_multipart):?>
            <!-- WIND -->
            <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.__('Wind from', 'live-weather-station').' '.$datas['windangle']['from'].'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['windstrength']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['windstrength']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['windangle']['icon']; ?>
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
            <?php if ($show_rain || $show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_wind && !$wind_multipart):?>
            <!-- WIND -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.__('Wind from', 'live-weather-station').' '.$datas['windangle']['from'].'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['windangle']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['windstrength']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['windstrength']['unit']; ?></div>
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
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['rain']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['rain']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['rain']['icon']; ?>
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
            <?php if ($show_strike || $show_snow || $show_cloud_cover || $show_cloud_ceiling || $show_windchill || $show_humidex || $show_steadman || $show_summer_simmer || $show_heat || $show_frost || $show_dew):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($show_rain && !$rain_multipart):?>
            <!-- RAIN -->
            <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Rainfall', 'live-weather-station').'"' : ''); ?>>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['rain']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['rain']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['rain']['unit']; ?></div>
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
                    <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['strike']['value']; ?></div>
                    <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['strike']['unit']; ?></div>
                </div>
                <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                    <?php echo $datas['strike']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['strike_distance']['value']; ?></div>
                        <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['strike_distance']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                        <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['strike_distance']['ts']; ?></div>
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
                    <?php echo $datas['snow']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['snow']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['snow']['unit']; ?></div>
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
                    <?php echo $datas['cloudcover']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['cloudcover']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['cloudcover']['unit']; ?></div>
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
                    <?php echo $datas['cloudceiling']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['cloudceiling']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['cloudceiling']['unit']; ?></div>
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
                    <?php echo $datas['dew']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['dew']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['dew']['unit']; ?></div>
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
                    <?php echo $datas['frost']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['frost']['value']; ?></div>
                        <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['frost']['unit']; ?></div>
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
                    <?php echo $datas['heat']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['heat']['value']; ?></div>
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
                    <?php echo $datas['humidex']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['humidex']['value']; ?></div>
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
                    <?php echo $datas['summer_simmer']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['summer_simmer']['value']; ?></div>
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
                    <?php echo $datas['steadman']['icon']; ?>
                </div>
                <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['steadman']['value']; ?></div>
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
                    <?php echo $datas['windchill']['icon']; ?>
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