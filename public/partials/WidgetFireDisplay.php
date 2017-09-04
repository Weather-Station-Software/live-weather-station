<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-fire lws-widget-outer-fire-<?php echo $id ?>">
        <div class="lws-widget-fire lws-widget-fire-<?php echo $id ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- CURRENT CONDITIONS -->
                <div class="lws-widget-header lws-widget-header-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current fire weather risk', 'live-weather-station').'"' : ''); ?>>
                    <i class="wi wi-fire cbi-<?php echo $id ?>"></i>
                </div>
                <?php if (($show_title || $subtitle != 0) || $show_rain || $show_wind || $show_cbi || $show_humidity || $show_temperature):?>
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
                <?php if ($show_rain || $show_wind || $show_cbi || $show_humidity || $show_temperature):?>
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
                <?php if ($show_rain || $show_wind || $show_cbi || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_temperature && !$temp_multipart):?>
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
                <?php if ($show_rain || $show_wind || $show_cbi || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_humidity):?>
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
                <?php if ($show_rain || $show_wind || $show_cbi):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_cbi):?>
                <!-- CHANDLER BURNING INDEX -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Chandler burning index', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['cbi']['value']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-fire" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo esc_html__('Risk', 'live-weather-station'); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['cbi']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_rain || $show_wind):?>
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
                <?php if ($show_rain):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_wind && !$wind_multipart):?>
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
                <?php if ($show_rain):?>
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
        </div>
    </div>
</div>