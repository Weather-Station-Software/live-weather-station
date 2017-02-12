<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
?>
<div class="lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-indoor-<?php echo $id ?>">
        <div class="lws-widget-indoor-<?php echo $id ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- HEALTH INDEX -->
                <div class="lws-widget-header-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Health index', 'live-weather-station').'"' : ''); ?>>
                    <i class="fa fa-leaf health-idx-<?php echo $id ?>"></i>
                </div>
                <div class="lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
            <!-- STATION NAME -->
            <div class="lws-widget-row-<?php echo $id ?>">
                <div class="lws-widget-column-<?php echo $id ?>">
                    <?php if ( $show_title ):?>
                        <div class="lws-widget-title-<?php echo $id ?>"><?php echo $title; ?></div>
                    <?php endif;?>
                    <?php if ( $subtitle == 1 ):?>
                        <div class="lws-widget-subtitle-<?php echo $id ?>"><?php echo $timestamp; ?></div>
                    <?php endif;?>
                    <?php if ( $show_status ):?>
                        <div class="lws-widget-subtitle-<?php echo $id ?>"><?php echo $status; ?></div>
                    <?php endif;?>
                </div>
            </div>
            <?php if ($show_co2):?>
                <div class="lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- CO2 -->
                <div class="lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Carbon monoxide', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-smoke"></i>
                    </div>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['co2']['value']; ?></div>
                            <div class="lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['co2']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_temperature && $temp_multipart):?>
                <div class="lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['temperature']['value']; ?></div>
                        <div class="lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['temperature']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-thermometer" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['temperature_max']['value']; ?></div>
                            <div class="lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['temperature_max']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['temperature_min']['value']; ?></div>
                            <div class="lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $datas['temperature_min']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_temperature && !$temp_multipart):?>
                <div class="lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-thermometer"></i>
                    </div>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['temperature']['value']; ?></div>
                            <div class="lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['temperature']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_humidity):?>
                <div class="lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- HUMIDITY -->
                <div class="lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Humidity', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-humidity"></i>
                    </div>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['humidity']['value']; ?></div>
                            <div class="lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['humidity']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
            <?php if ($show_noise):?>
                <div class="lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- NOISE -->
                <div class="lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Noise level', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <i class="fa wi-x26-<?php echo $id ?> fa-volume-down"></i>
                    </div>
                    <div class="lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['noise']['value']; ?></div>
                            <div class="lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['noise']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>