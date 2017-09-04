<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
 */
?>
<div class="lws-widget-container lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-ephemeris lws-widget-outer-ephemeris-<?php echo $id ?>">
        <div class="lws-widget-ephemeris lws-widget-ephemeris-<?php echo $id ?> noTypo">
        <?php if ($show_title || $subtitle != 0 || $mode != 0):?>
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
                    <?php if ( $mode == 1 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo $id ?>"><?php _e('Civil Daylight Times', 'live-weather-station') ?></div>
                    <?php endif;?>
                    <?php if ( $mode == 2 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo $id ?>"><?php _e('Nautical Daylight Times', 'live-weather-station') ?></div>
                    <?php endif;?>
                    <?php if ( $mode == 3 ):?>
                        <div class="lws-widget-subtitle lws-widget-subtitle-<?php echo $id ?>"><?php _e('Astronomical Daylight Times', 'live-weather-station') ?></div>
                    <?php endif;?>
                </div>
            </div>
            <?php if (((($format == 0) && ($show_sun || ($show_moon && $show_moonphase))) || (($format == 1 || $format == 2) && ($show_sun || ($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2))))):?>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
            <?php endif;?>
        <?php endif;?>
        <?php if ($format == 0):?>
            <?php if ($show_sun):?>
                <!-- SUNRISE / SUNSET -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunrise & sunset', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-day-sunny" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['sunrise']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['sunset']['value']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($show_moon && $show_moonphase) || (($format == 1 || $format == 2) && ($show_sun || ($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2)))):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moon && $show_moonphase):?>
                <!-- MOONRISE / MOONSET -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.__('Moon', 'live-weather-station').': '.$datas['moon_phase']['name'].'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-moon-<?php echo $datas['moon_phase']['value']; ?>" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['moonrise']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['moonset']['value']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($format == 1 || $format == 2) && ($show_sun || ($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2))):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
        <?php endif;?>

        <?php if ( $format == 1 || $format == 2):?>
            <?php if ($show_sun):?>
                <!-- SUNRISE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunrise', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-sunrise"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['sunrise']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- SUNSET -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunset', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-sunset"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['sunset']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($show_sundetails && $format == 2) || $show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_sundetails && $format == 2):?>
                <!-- SUN DETAILS -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sun distance and angular size', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-day-sunny" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['sun_distance']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['sun_distance']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['sun_diameter']['value']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $datas['sun_diameter']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_moonphase || $show_moon || ($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moonphase):?>
                <!-- MOONRISE / MOONSET -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moon illumination, phase & age', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['moon_illumination']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['moon_illumination']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-moon-<?php echo $datas['moon_phase']['value']; ?>" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['moon_phase']['name']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['moon_age']['value']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_moon || ($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moon):?>
                <!-- MOONSET -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moonset', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-moonset"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['moonset']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <!-- MOONRISE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moonrise', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x26-<?php echo $id ?> wi-moonrise"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['moonrise']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if (($show_moondetails && $show_moonphase && $format == 2)):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_moondetails && $show_moonphase && $format == 2):?>
                <!-- SUN DETAILS -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Moon distance and angular size', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x32-<?php echo $id ?> wi-moon-<?php echo $datas['moon_phase']['value']; ?>" style="padding-top: 6px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['moon_distance']['value']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['moon_distance']['unit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['moon_diameter']['value']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $datas['moon_diameter']['unit']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        <?php endif;?>
        </div>
    </div>
</div>