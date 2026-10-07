<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

use WeatherStation\System\Environment\Manager;
use WeatherStation\System\Output\Guard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$colors = Manager::icon_color_scheme();
$name = '';
$type = __('graph', 'live-weather-station');
foreach ($modules as $module) {
    if ($module->is_selected()) {
        $name = live_weather_station_lcfirst($module->get_name());
        $name = str_replace('lCD', 'LCD', $name);
        if ($module->module_type() == 'current') {
            $type = __('control', 'live-weather-station');
        }
    }
}
?>

<div style="margin-left:8px; margin-right: 8px;">
    <div class="postbox">
        <?php if ($this->arg_service == 'station') { ?>
            <h3 style="border-bottom: 1px solid #EEE;cursor:default;"><span><?php esc_html_e('Please, select the type of control you want to set', 'live-weather-station' );?>&hellip;</span></h3>
        <?php } else { ?>
            <h3 style="border-bottom: 1px solid #EEE;cursor:default;"><span><?php echo wp_kses_post(sprintf(/* translators: 1: type of module, 2: name of the selected type */ __('The type of %1$s currently selected is %2$s', 'live-weather-station'), esc_html($type), '<em>' . esc_html($name) . '</em>'));?>&hellip;</span></h3>
        <?php } ?>
        <div style="width: 100%;text-align: center;padding: 0px;margin-bottom: 0px;" class="inside">
            <div style="padding: 0px 16px 16px 0px;display:flex;flex-direction:row;flex-wrap:wrap;justify-content: center;align-items: center;align-content: center;">
                <style>
                    .container-actionable {flex:auto;margin: 16px;width: 40px;height: 40px;}
                    .actionable {width:40px;height:40px;padding:10px 10px 13px 10px;font-size:30px;border-radius:6px;cursor:pointer; -moz-transition: all .5s ease-in; -o-transition: all .5s ease-in; -webkit-transition: all .5s ease-in; transition: all .5s ease-in; background: transparent;border:1px solid transparent;}
                    .actionable-selected {border-radius:6px !important;background: <?php echo esc_attr($colors['background']); ?> !important;border:1px solid <?php echo esc_attr($colors['border']); ?> !important;}
                    .actionable-selected:hover {border-radius:6px;cursor:pointer; -moz-transition: all .2s ease-in; -o-transition: all .2s ease-in; -webkit-transition: all .2s ease-in; transition: all .2s ease-in; opacity: 0.6 !important;}
                    .actionable:hover {border-radius:6px;cursor:pointer; -moz-transition: all .2s ease-in; -o-transition: all .2s ease-in; -webkit-transition: all .2s ease-in; transition: all .2s ease-in; background: #f5f5f5;border:1px solid #e0e0e0;}
                    <?php if (!LIVE_WEATHER_STATION_FA5) { ?>
                        #yearly-astream, #daily-astream, #current-snapshot {margin-top: 12px !important;}
                    <?php } else {?>
                        #current-snapshot {margin-top: 16px !important;}
                    <?php }?>
                </style>
                <?php foreach ($modules as $module) { ?>
                    <div id="<?php echo esc_attr($module->get_id()); ?>" class="container-actionable"><span class="actionable<?php echo $module->is_selected()?' actionable-selected':''; ?>"><span style="color:<?php echo esc_attr($module->is_selected()?$colors['text']:$module->get_icon_color()); ?>;" class="<?php echo esc_attr($module->get_icon()); ?>"><?php echo $module->get_icon_index() != '' ? '<span style="font-size:12px;">' . esc_html($module->get_icon_index()) . '</span>':''; ?></span></span></div>
                <?php } ?>
            </div>
        </div>
        <div id="major-publishing-actions">
            <div id="tip-text">&nbsp;</div>
            <div class="clear"></div>
        </div>
    </div>
    <script language="javascript" type="text/javascript">
        jQuery(document).ready(function($) {
            $(".actionable").mouseout(function() {
                $("#tip-text").html("&nbsp;");
            });
            <?php foreach ($modules as $module) { ?>
                $("#" + <?php echo Guard::js($module->get_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes the value with wp_json_encode() and the JSON_HEX_TAG/AMP/APOS/QUOT flags, so it is safe in an inline script ?>).mouseover(function() {
                    $("#tip-text").html(<?php echo Guard::js($module->get_hint()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes the value with wp_json_encode() and the JSON_HEX_TAG/AMP/APOS/QUOT flags, so it is safe in an inline script ?>);
                });
                <?php if ($module->is_selected()) { ?>
                    $("#" + <?php echo Guard::js($module->get_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes the value with wp_json_encode() and the JSON_HEX_TAG/AMP/APOS/QUOT flags, so it is safe in an inline script ?>).click(function() {
                        document.location.href=<?php echo Guard::js($module->get_parent_url()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes the value with wp_json_encode() and the JSON_HEX_TAG/AMP/APOS/QUOT flags, so it is safe in an inline script ?>;
                    });
                <?php } else { ?>
                    $("#" + <?php echo Guard::js($module->get_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes the value with wp_json_encode() and the JSON_HEX_TAG/AMP/APOS/QUOT flags, so it is safe in an inline script ?>).click(function() {
                        document.location.href=<?php echo Guard::js($module->get_module_url()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() encodes the value with wp_json_encode() and the JSON_HEX_TAG/AMP/APOS/QUOT flags, so it is safe in an inline script ?>;
                    });
                <?php } ?>
            <?php } ?>

        });
    </script>
</div>