<?php
/**
 * @package Public\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */

?>
<div class="lws-widget-container lws-widget-container-<?php echo $id ?>">
    <div class="lws-widget-outer-solar lws-widget-outer-solar-<?php echo $id ?>">
        <div class="lws-widget-solar lws-widget-solar-<?php echo $id ?> noTypo">
            <?php if ( $show_current ):?>
                <!-- CURRENT CONDITIONS -->
                <div class="lws-widget-header lws-widget-wiheader-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Current weather conditions', 'live-weather-station').'"' : ''); ?>>
                    <?php echo $datas['weather']['icon']; ?>
                </div>
                <?php if (($show_title || $subtitle != 0) || $show_illuminance || $show_irradiance || $show_sunshine || $show_uv):?>
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
                <?php if ($show_illuminance || $show_irradiance || $show_sunshine || $show_uv):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>

            <?php if ($show_irradiance):?>
                <!-- IRRADIANCE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Irradiance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                        <?php echo $datas['irradiance']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['irradiance']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['irradiance']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_uv || $show_sunshine || $show_illuminance):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_illuminance):?>
                <!-- ILLUMINANCE -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Illuminance', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                        <?php echo $datas['illuminance']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['illuminance']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['illuminance']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_uv || $show_sunshine):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_uv):?>
                <!-- UV -->
                <div class="lws-widget-row lws-widget-row-single-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('UV', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-icon-<?php echo $id ?>">
                        <?php echo $datas['uv_index']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                            <div class="lws-widget-med-value lws-widget-med-value-<?php echo $id ?>"><?php echo $datas['uv_index']['value']; ?></div>
                            <div class="lws-widget-med-unit lws-widget-med-unit-<?php echo $id ?>"><?php echo $datas['uv_index']['unit']; ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($show_sunshine):?>
                    <div class="lws-widget-bevel lws-widget-bevel-<?php echo $id ?>"></div>
                <?php endif;?>
            <?php endif;?>
            <?php if ($show_sunshine):?>
                <!-- SUNSHINE DURATION -->
                <div class="lws-widget-row lws-widget-row-<?php echo $id ?>"<?php echo ($show_tooltip ? ' title="'.esc_html__('Sunshine duration', 'live-weather-station').'"' : ''); ?>>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-big-value lws-widget-big-value-<?php echo $id ?>"></div>
                        <div class="lws-widget-big-unit lws-widget-big-unit-<?php echo $id ?>"></div>
                    </div>
                    <div style="padding-right: 6px;" class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <?php echo $datas['sunshine']['icon']; ?>
                    </div>
                    <div class="lws-widget-column lws-widget-column-<?php echo $id ?>">
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-up lws-widget-small-value-up-<?php echo $id ?>"><?php echo $datas['sunshine']['hvalue']; ?></div>
                            <div class="lws-widget-small-unit-up lws-widget-small-unit-up-<?php echo $id ?>"><?php echo $datas['sunshine']['hunit']; ?></div>
                        </div>
                        <div class="lws-widget-small-row lws-widget-small-row-<?php echo $id ?>">
                            <div class="lws-widget-small-value-down lws-widget-small-value-down-<?php echo $id ?>"><?php echo $datas['sunshine']['mvalue']; ?></div>
                            <div class="lws-widget-small-unit-down lws-widget-small-unit-down-<?php echo $id ?>"><?php echo $datas['sunshine']['munit']; ?></div>
                        </div>
                    </div>
                </div>



            <?php endif;?>
        </div>
    </div>
</div>