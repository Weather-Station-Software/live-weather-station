<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * @package Public\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

?>
<div class="lws-widget-container lws-widget-container-<?php echo esc_attr($id) ?>">
    <div class="lws-widget-outer-solar lws-widget-outer-solar-<?php echo esc_attr($id) ?>">
        <div class="lws-widget-solar lws-widget-solar-<?php echo esc_attr($id) ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- CURRENT CONDITIONS -->
                <div class="lws-widget-header lws-widget-wiheader-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current weather conditions', 'live-weather-station').'"' : ''); ?>>
                    <?php echo $measurements['weather']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output_iconic_value() (DataOutput trait) returns only span and i markup built from fixed icon class maps, a colour checked by a regex, an extra class stripped to [A-Za-z0-9_ -] and value parts reduced to [A-Za-z0-9_-] ?>
                </div>
                <?php if (($show_title || $subtitle != 0) || $show_illuminance || $show_irradiance || $show_sunshine || $show_uv):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
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
                <?php if ($show_illuminance || $show_irradiance || $show_sunshine || $show_uv):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>

            <?php if ($show_irradiance):?>
                <!-- IRRADIANCE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Irradiance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['irradiance']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output_iconic_value() (DataOutput trait) returns only span and i markup built from fixed icon class maps, a colour checked by a regex, an extra class stripped to [A-Za-z0-9_ -] and value parts reduced to [A-Za-z0-9_-] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['irradiance']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['irradiance']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_uv || $show_sunshine || $show_illuminance):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_illuminance):?>
                <!-- ILLUMINANCE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Illuminance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['illuminance']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output_iconic_value() (DataOutput trait) returns only span and i markup built from fixed icon class maps, a colour checked by a regex, an extra class stripped to [A-Za-z0-9_ -] and value parts reduced to [A-Za-z0-9_-] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['illuminance']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['illuminance']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_uv || $show_sunshine):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_uv):?>
                <!-- UV -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('UV', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['uv_index']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output_iconic_value() (DataOutput trait) returns only span and i markup built from fixed icon class maps, a colour checked by a regex, an extra class stripped to [A-Za-z0-9_ -] and value parts reduced to [A-Za-z0-9_-] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['uv_index']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['uv_index']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_sunshine):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_sunshine):?>
                <!-- SUNSHINE DURATION -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunshine duration', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"></div>
                    </div>
                    <div style="padding-right: 6px;" class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['sunshine']['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output_iconic_value() (DataOutput trait) returns only span and i markup built from fixed icon class maps, a colour checked by a regex, an extra class stripped to [A-Za-z0-9_ -] and value parts reduced to [A-Za-z0-9_-] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunshine']['hvalue']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunshine']['hunit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunshine']['mvalue']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunshine']['munit']); ?></div>
                        </div>
                    </div>
                </div>



            <?php endif;?>
        </div>
    </div>
</div>