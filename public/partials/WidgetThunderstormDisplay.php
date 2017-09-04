<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

?>
<div class="lws-widget-container lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-thunderstorm lws-widget-outer-thunderstorm-<?php echo $id ?>">
        <div class="lws-widget-thunderstorm lws-widget-thunderstorm-<?php echo $id ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- CURRENT CONDITIONS -->
                <div class="lws-widget-header lws-widget-header-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current thunderstorm conditions', 'live-weather-station').'"' : ''); ?>>
                    <i class="wi wi-lightning"></i>
                    <?php if (array_key_exists('strikecount',$datas)):?>
                        <div class="lws-widget-title lws-widget-title-<?php echo $id ?>" style="display: inline-block;margin-left: -50px;font-size: 36px;vertical-align: bottom;"><?php echo $datas['strikecount']['value']; ?></div>
                    <?php endif;?>
                </div>
                <?php if (($show_title || $subtitle != 0) || $show_strikedistance || $show_strikebearing || $show_strikecount):?>
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
                <?php if ($show_strikedistance || $show_strikebearing || $show_strikecount):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_strikecount):?>
                <!-- COUNT -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Strike distance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['strikecount']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="fa wi-x42-<?php echo $id ?> fa-flash"  style="font-size:32px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['strikecount']['unit']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_strikedistance || $show_strikebearing):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_strikebearing):?>
                <!-- BEARING -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Last strike bearing', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="wi wi-x42-<?php echo $id ?> wi-wind towards-<?php echo $datas['strikebearing']['value']; ?>-deg"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['strikebearing']['unit']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_strikedistance):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_strikedistance):?>
                <!-- DISTANCE -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Last strike distance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"><?php echo $datas['strikedistance']['value']; ?></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"><?php echo $datas['strikedistance']['unit']; ?></div>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <i class="fa wi-x42-<?php echo $id ?> fa-long-arrow-left"  style="font-size:32px;"></i>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['strikedistance']['ts1']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $datas['strikedistance']['ts2']; ?></div>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>