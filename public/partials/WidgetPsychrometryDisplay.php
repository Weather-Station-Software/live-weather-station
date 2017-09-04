<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-psychrometry lws-widget-outer-psychrometry-<?php echo $id ?>">
        <div class="lws-widget-psychrometry lws-widget-psychrometry-<?php echo $id ?> noTypo">
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
                <?php if ($show_wet_bulb || $show_temperature || $show_pressure || $show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_temperature):?>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperatures', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['temperature']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['temperature']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-thermometer" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['potential_temperature']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['potential_temperature']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['equivalent_temperature']['value']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $datas['equivalent_temperature']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_wet_bulb || $show_pressure || $show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_wet_bulb):?>
                <!-- WET BULB -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Wet bulb temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['wet_bulb']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['wet_bulb']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-thermometer" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo esc_html__('Wet Bulb', 'live-weather-station'); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>">&nbsp;</div>
                        </div>
                    </div>
                </div>
                <?php if ($show_pressure || $show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_pressure):?>
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
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_humidity):?>
                <!-- HUMIDITY -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Relative humidity', 'live-weather-station').'"' : ''); ?>>
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
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_absolute_humidity):?>
                <!-- ABSOLUTE HUMIDITY -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Absolute humidity', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['partial_absolute_humidity']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['partial_absolute_humidity']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-raindrop" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['saturation_absolute_humidity']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['saturation_absolute_humidity']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo esc_html__('at saturation', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_dew):?>
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
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_vapor_pressure):?>
                <!-- VAPOR PRESSURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Vapor pressures', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['partial_vapor_pressure']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['partial_vapor_pressure']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-barometer"></i><i class="fa fa-ellipsis-v" style="font-size: 32px;"></i>&nbsp;
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['saturation_vapor_pressure']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['saturation_vapor_pressure']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo esc_html__('at saturation', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy || $show_air_density):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_air_density):?>
                <!-- AIR DENSITY -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Air density', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="fa wi-x26-<?php echo $id ?> fa-adjust" style="font-size:22px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['air_density']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['air_density']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_enthalpy):?>
                <!-- SPECIFIC ENTHALPY -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Specific enthalpy', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-refresh-alt"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['specific_enthalpy']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['specific_enthalpy']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_emc):?>
                <!-- EMC -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Equilibrium moisture content', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="fa wi-x26-<?php echo $id ?> fa-tree" style="font-size:20px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['wood_emc']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['wood_emc']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>