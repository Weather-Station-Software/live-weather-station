<?php
/**
 * @package Public\Partials
 * @author Jason Rouet <https://www.jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo esc_attr($id) ?>">
    <div class="lws-widget-outer-ephemeris lws-widget-outer-ephemeris-<?php echo esc_attr($id) ?>">
        <div class="lws-widget-ephemeris lws-widget-ephemeris-<?php echo esc_attr($id) ?> noTypo">
        <?php if ($show_title || $subtitle != 0 || $mode != 0):?>
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
                    <?php if ( $mode == 1 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo esc_attr($id) ?>"><?php _e('Civil Daylight Times', 'live-weather-station') ?></div>
                    <?php endif;?>
                    <?php if ( $mode == 2 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo esc_attr($id) ?>"><?php _e('Nautical Daylight Times', 'live-weather-station') ?></div>
                    <?php endif;?>
                    <?php if ( $mode == 3 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo esc_attr($id) ?>"><?php _e('Astronomical Daylight Times', 'live-weather-station') ?></div>
                    <?php endif;?>
                </div>
            </div>
            <?php if (((($format == 0) && ($show_sun || ($show_moon && $show_moonphase))) || (($format == 1 || $format == 2) && ($show_sun || ($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2))))):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($format == 0):?>
            <?php if ($show_sun):?>
                <!-- SUNRISE / SUNSET -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunrise & sunset', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['sun']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunrise']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunset']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($show_moon && $show_moonphase) || (($format == 1 || $format == 2) && ($show_sun || ($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2)))):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moon && $show_moonphase):?>
                <!-- MOONRISE / MOONSET -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_attr(__('Moon', 'live-weather-station').': '.$measurements['moon_phase']['name']).'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['moon_phase']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moonrise']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moonset']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($format == 1 || $format == 2) && ($show_sun || ($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2))):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
        <?php endif;?>

        <?php if ( $format == 1 || $format == 2):?>
            <?php if ($show_sun):?>
                <!-- SUNRISE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunrise', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['sunrise']['icon'] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunrise']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- SUNSET -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunset', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['sunset']['icon'] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sunset']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_sundetails && $format == 2):?>
                <!-- SUN DETAILS -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sun distance and angular size', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['sun_distance']['icon'] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sun_distance']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sun_distance']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sun_diameter']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['sun_diameter']['unit']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moonphase):?>
                <!-- MOON DETAILS -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moon illumination, phase & age', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_illumination']['value']); ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_illumination']['unit']); ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['moon_phase']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_phase']['name']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_age']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_moon || ($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moon):?>
                <!-- MOONSET -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moonset', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['moonset']['icon'] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moonset']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <!-- MOONRISE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moonrise', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['moonrise']['icon'] ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moonrise']['value']); ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo esc_attr($id) ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo esc_attr($id) ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moondetails && $show_moonphase && $format == 2):?>
                <!-- MOON DETAILS -->
                <div class="lws-widget-row lws-widget-row-<?php echo esc_attr($id) ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moon distance and angular size', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo esc_attr($id) ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo esc_attr($id) ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo esc_attr($id) ?>">
                        <?php echo $measurements['moon_phase']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo esc_attr($id) ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_distance']['value']); ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_distance']['unit']); ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo esc_attr($id) ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_diameter']['value']); ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo esc_attr($id) ?>"><?php echo wp_kses_post($measurements['moon_diameter']['unit']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        <?php endif;?>
        </div>
    </div>
</div>