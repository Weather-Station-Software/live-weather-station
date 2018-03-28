<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.4.0
 */

use WeatherStation\System\Environment\Manager;

$colors = Manager::icon_color_scheme();
$name = '';
$type = __('graph', 'live-weather-station');
foreach ($modules as $module) {
    if ($module->is_selected()) {
        $name = lcfirst($module->get_name());
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
            <h3 style="border-bottom: 1px solid #EEE;cursor:default;"><span><?php echo sprintf(__('The type of %s currently selected is %s', 'live-weather-station'), $type, '<em>' . $name . '</em>');?>&hellip;</span></h3>
        <?php } ?>
        <div style="width: 100%;text-align: center;padding: 0px;" class="inside">
            <div style="display:flex;flex-direction:row;flex-wrap:wrap;">
                <style>
                    .actionable-selected {border-radius:6px !important;background: <?php echo $colors['background']; ?> !important;border:1px solid <?php echo $colors['border']; ?> !important;}
                    .actionable-selected:hover {border-radius:6px;cursor:pointer; -moz-transition: all .2s ease-in; -o-transition: all .2s ease-in; -webkit-transition: all .2s ease-in; transition: all .2s ease-in; opacity: 0.6 !important;}
                    .actionable:hover {border-radius:6px;cursor:pointer; -moz-transition: all .2s ease-in; -o-transition: all .2s ease-in; -webkit-transition: all .2s ease-in; transition: all .2s ease-in; background: #f5f5f5;border:1px solid #e0e0e0;}
                    .actionable {width:40px;height:40px;font-size:30px;padding:16px 10px 4px 10px;border-radius:6px;cursor:pointer; -moz-transition: all .5s ease-in; -o-transition: all .5s ease-in; -webkit-transition: all .5s ease-in; transition: all .5s ease-in; background: transparent;border:1px solid transparent;}
                </style>
                <?php foreach ($modules as $module) { ?>
                    <?php if ($module->is_selected()) { ?>
                        <div style="flex:auto;"><span style="font-size:30px;color:<?php echo $colors['text']; ?>;" id="<?php echo $module->get_id(); ?>" class="actionable actionable-selected <?php echo $module->get_icon(); ?>"><?php echo $module->get_icon_index() != '' ? '<span style="font-size:12px;">' . $module->get_icon_index() . '</span>':''; ?></span></div>
                    <?php } else { ?>
                        <div style="flex:auto;"><span style="font-size:30px;color:<?php echo $module->get_icon_color(); ?>;" id="<?php echo $module->get_id(); ?>" class="actionable <?php echo $module->get_icon(); ?>"><?php echo $module->get_icon_index() != '' ? '<span style="font-size:12px;">' . $module->get_icon_index() . '</span>':''; ?></span></div>
                    <?php } ?>
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
                $("#<?php echo $module->get_id(); ?>").mouseover(function() {
                    $("#tip-text").html("<?php echo $module->get_hint(); ?>");
                });
                <?php if ($module->is_selected()) { ?>
                    $("#<?php echo $module->get_id(); ?>").click(function() {
                        document.location.href='<?php echo $module->get_parent_url(); ?>';
                    });
                <?php } else { ?>
                    $("#<?php echo $module->get_id(); ?>").click(function() {
                        document.location.href='<?php echo $module->get_module_url(); ?>';
                    });
                <?php } ?>
            <?php } ?>

        });
    </script>
</div>