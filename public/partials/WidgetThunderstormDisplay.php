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
    <div class="lws-widget-outer-thunderstorm lws-widget-outer-thunderstorm-<?php echo esc_attr($id) ?>">
        <div class="lws-widget-thunderstorm lws-widget-thunderstorm-<?php echo esc_attr($id) ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- CURRENT CONDITIONS -->
                <div class="lws-widget-header lws-widget-header-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current thunderstorm conditions', 'live-weather-station').'"' : ''); ?>>
                    <?php if (array_key_exists('strikecount',$measurements)):?>
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup built by output_iconic_value() (DataOutput.php), which whitelists the color and extra class by regex; icon and class names come from its internal tables. ?>
                        <?php echo $measurements['strikecount']['icon']; ?>
                        <?php echo wp_kses_post($measurements['strikecount']['value']); ?>
                    <?php endif;?>
                </div>
                <?php if (($show_title || $subtitle != 0) || $show_strikedistance || $show_strikebearing || $show_strikecount):?>
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
                <?php if ($show_strikedistance || $show_strikebearing || $show_strikecount):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_strikecount):?>
                <!-- COUNT -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Strike distance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['strikecount']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup built by output_iconic_value() (DataOutput.php), which whitelists the color and extra class by regex; icon and class names come from its internal tables. ?>
                        <?php echo $measurements['strikecount']['icon2']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['strikecount']['unit']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_strikedistance || $show_strikebearing):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_strikebearing):?>
                <!-- BEARING -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Last strike bearing', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup built by output_iconic_value() (DataOutput.php), which whitelists the color and extra class by regex; icon and class names come from its internal tables. ?>
                        <?php echo $measurements['strikebearing']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['strikebearing']['unit']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_strikedistance):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_strikedistance):?>
                <!-- DISTANCE -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Last strike distance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['strikedistance']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['strikedistance']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup built by output_iconic_value() (DataOutput.php), which whitelists the color and extra class by regex; icon and class names come from its internal tables. ?>
                        <?php echo $measurements['strikedistance']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['strikedistance']['ts1']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['strikedistance']['ts2']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>


