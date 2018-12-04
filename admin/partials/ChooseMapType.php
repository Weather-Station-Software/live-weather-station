<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\UI\SVG\Handling as SVG;

if (get_option('live_weather_station_windy_apikey') != '') {
    $windy_s = ucfirst(addslashes(lws__('a full featured map with many weather layers and animations.', 'live-weather-station')));
    $windy_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'windy');
    $windy_t = '_self';
}
else {
    $windy_s = addslashes(lws__('To add a map of this type, you need to set a Windy API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $windy_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $windy_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

$stamen_s = ucfirst(addslashes(lws__('a beautiful static map.', 'live-weather-station')));
$stamen_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'stamen');
$stamen_t = '_self';

?>

<div id="normal-sortables" class="meta-box-sortables ui-sortable" style="overflow: hidden;">
    <div id="add-map" class="postbox ">
        <h3 class="hndle" style="cursor:default;"><span><?php esc_html_e_lws__('Please, select the type of map you want to add', 'live-weather-station' );?>&hellip;</span></h3>
        <div style="width: 100%;text-align: center;padding: 0px;" class="inside">
            <div style="display:flex;flex-direction:row;flex-wrap:wrap;">
                <style>
                    .actionable:hover {border-radius:6px;cursor:pointer; -moz-transition: all .2s ease-in; -o-transition: all .2s ease-in; -webkit-transition: all .2s ease-in; transition: all .2s ease-in; background: #f5f5f5;border:1px solid #e0e0e0;}
                    .actionable {border-radius:6px;cursor:pointer; -moz-transition: all .5s ease-in; -o-transition: all .5s ease-in; -webkit-transition: all .5s ease-in; transition: all .5s ease-in; background: transparent;border:1px solid transparent;}
                </style>
                <div style="flex:auto;padding:14px;"><img id="windy" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_windy_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="stamen" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_windy_color_logo());?>" /></div>
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
            $("#windy").mouseover(function() {
                $("#tip-text").html("<?php echo $windy_s; ?>");
            });
            $("#windy").click(function() {
                window.open('<?php echo $windy_l; ?>', '<?php echo $windy_t; ?>');
            });
            $("#stamen").mouseover(function() {
                $("#tip-text").html("<?php echo $stamen_s; ?>");
            });
            $("#stamen").click(function() {
                window.open('<?php echo $stamen_l; ?>', '<?php echo $stamen_t; ?>');
            });
        });
    </script>
</div>