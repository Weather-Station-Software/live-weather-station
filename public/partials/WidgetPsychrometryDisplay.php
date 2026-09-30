<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * @package Public\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */

?>
<div class="lws-widget-container lws-widget-container-<?php echo esc_attr($id) ?>">
    <div class="lws-widget-outer-psychrometry lws-widget-outer-psychrometry-<?php echo esc_attr($id) ?>">
        <div class="lws-widget-psychrometry lws-widget-psychrometry-<?php echo esc_attr($id) ?> noTypo">
            <?php if ($show_title || $subtitle != 0):?>
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
                <?php if ($show_wet_bulb || $show_temperature || $show_pressure || $show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_temperature):?>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperatures', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['temperature']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['potential_temperature']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['potential_temperature']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['equivalent_temperature']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['equivalent_temperature']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_wet_bulb || $show_pressure || $show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_wet_bulb):?>
                <!-- WET BULB -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Wet bulb temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['wet_bulb']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['wet_bulb']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['wet_bulb']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo esc_html__('Wet Bulb', 'live-weather-station'); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>">𝝙T=<?php echo wp_kses_post($measurements['delta_t']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['wet_bulb']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_pressure || $show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_pressure):?>
                <!-- PRESSURE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Atmospheric pressure', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['pressure']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['pressure']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['pressure']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_humidity):?>
                <!-- HUMIDITY -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Relative humidity', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['humidity']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['humidity']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['humidity']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew || $show_absolute_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_absolute_humidity):?>
                <!-- ABSOLUTE HUMIDITY -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Absolute humidity', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['partial_absolute_humidity']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['partial_absolute_humidity']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['partial_absolute_humidity']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['saturation_absolute_humidity']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['saturation_absolute_humidity']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo esc_html__('at saturation', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure || $show_dew):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_dew):?>
                <!-- DEW POINT -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Dew point', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['dew']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['dew']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['dew']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy || $show_air_density || $show_vapor_pressure):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_vapor_pressure):?>
                <!-- VAPOR PRESSURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Vapor pressures', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['partial_vapor_pressure']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['partial_vapor_pressure']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['partial_vapor_pressure']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['saturation_vapor_pressure']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['saturation_vapor_pressure']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo esc_html__('at saturation', 'live-weather-station'); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy || $show_air_density):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_air_density):?>
                <!-- AIR DENSITY -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Air density', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['air_density']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['air_density']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['air_density']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc || $show_enthalpy):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_enthalpy):?>
                <!-- SPECIFIC ENTHALPY -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Specific enthalpy', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['specific_enthalpy']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['specific_enthalpy']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['specific_enthalpy']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_emc):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_emc):?>
                <!-- EMC -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Equilibrium moisture content', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['wood_emc']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['wood_emc']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['wood_emc']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>