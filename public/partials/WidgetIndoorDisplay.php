<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * @package Public\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.1.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo esc_attr($id) ?>">
    <div class="lws-widget-outer-indoor lws-widget-outer-indoor-<?php echo esc_attr($id) ?>">
        <div class="lws-widget-indoor lws-widget-indoor-<?php echo esc_attr($id) ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- HEALTH INDEX -->
                <div class="lws-widget-header lws-widget-header-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Health index', 'live-weather-station').'"' : ''); ?>>
                    <?php echo $measurements['health_idx']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup (span and i tags with class names) built by DataOutput::output_iconic_value(), which restricts the colour and the extra class with regular expressions and uses fixed icon class names ?>
                </div>
                <?php if (($show_title || $show_status || $subtitle != 0) || $show_co2 || $show_noise || $show_humidity || $show_temperature):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_title || $show_status || $subtitle != 0):?>
                <!-- STATION NAME -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>">
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <?php if ( $show_title ):?>
                            <div class="lws-widget-title lws-widget-title-<?php echo esc_attr($id) ?>"><?php echo esc_html($title); ?></div>
                        <?php endif;?>
                        <?php if ( $subtitle == 1 ):?>
                            <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($timestamp); ?></div>
                        <?php endif;?>
                        <?php if ( $show_status ):?>
                            <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($status); ?></div>
                        <?php endif;?>
                    </div>
                </div>
                <?php if ($show_co2 || $show_noise || $show_humidity || $show_temperature):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_co2):?>
                <!-- CO2 -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Carbon dioxide concentration', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['co2']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup (span and i tags with class names) built by DataOutput::output_iconic_value(), which restricts the colour and the extra class with regular expressions and uses fixed icon class names ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['co2']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['co2']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_noise || $show_humidity || $show_temperature):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_temperature && $temp_multipart):?>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['temperature']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup (span and i tags with class names) built by DataOutput::output_iconic_value(), which restricts the colour and the extra class with regular expressions and uses fixed icon class names ?>
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
                <?php if ($show_noise || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_temperature && !$temp_multipart):?>
                <!-- TEMPERATURE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Temperature', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['temperature']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup (span and i tags with class names) built by DataOutput::output_iconic_value(), which restricts the colour and the extra class with regular expressions and uses fixed icon class names ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['temperature']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_noise || $show_humidity):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_humidity):?>
                <!-- HUMIDITY -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Relative humidity', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['humidity']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup (span and i tags with class names) built by DataOutput::output_iconic_value(), which restricts the colour and the extra class with regular expressions and uses fixed icon class names ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['humidity']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['humidity']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_noise):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_noise):?>
                <!-- NOISE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Noise level', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['noise']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup (span and i tags with class names) built by DataOutput::output_iconic_value(), which restricts the colour and the extra class with regular expressions and uses fixed icon class names ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['noise']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['noise']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>