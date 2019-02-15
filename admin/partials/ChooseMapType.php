<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\UI\SVG\Handling as SVG;

if (get_option('live_weather_station_windy_apikey') != '') {
    $windy_s = ucfirst(addslashes(__('a full featured map from Windy.com with many weather layers and animations.', 'live-weather-station')));
    $windy_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'windy');
    $windy_t = '_self';
}
else {
    $windy_s = addslashes(__('To add a map of this type, you need to set a Windy API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $windy_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $windy_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_owm_apikey') != '') {
    $owm_s = ucfirst(addslashes(__('a full featured map from OpenWeatherMap with many weather and agricultural layers.', 'live-weather-station')));
    $owm_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'openweathermap');
    $owm_t = '_self';
}
else {
    $owm_s = addslashes(__('To add a map of this type, you need to set an OpenWeatherMap API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $owm_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $owm_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_mapbox_apikey') != '') {
    $mapbox_s = ucfirst(addslashes(sprintf(__('a beautiful static map from %s, powered by OpenStreetMap, with many overlays to choose from.', 'live-weather-station'), 'Mapbox')));
    $mapbox_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'mapbox');
    $mapbox_t = '_self';
}
else {
    $mapbox_s = addslashes(__('To add a map of this type, you need to set a Mapbox API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $mapbox_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $mapbox_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_maptiler_apikey') != '') {
    $maptiler_s = ucfirst(addslashes(sprintf(__('a beautiful static map from %s, powered by OpenStreetMap, with many overlays to choose from.', 'live-weather-station'), 'Maptiler')));
    $maptiler_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'maptiler');
    $maptiler_t = '_self';
}
else {
    $maptiler_s = addslashes(lws__('To add a map of this type, you need to set a Maptiler API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $maptiler_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $maptiler_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_thunderforest_apikey') != '') {
    $thunderforest_s = ucfirst(addslashes(sprintf(__('a beautiful static map from %s, powered by OpenStreetMap, with many overlays to choose from.', 'live-weather-station'), 'Thunderforest')));
    $thunderforest_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'thunderforest');
    $thunderforest_t = '_self';
}
else {
    $thunderforest_s = addslashes(__('To add a map of this type, you need to set a Thunderforest API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $thunderforest_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $thunderforest_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

$stamen_s = ucfirst(addslashes(sprintf(__('a beautiful static map from %s, powered by OpenStreetMap, with many overlays to choose from.', 'live-weather-station'), 'Stamen')));
$stamen_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'stamen');
$stamen_t = '_self';

if (get_option('live_weather_station_navionics_apikey') != '') {
    $navionics_s = ucfirst(addslashes(lws__('a full featured map from Navionics with nautical, sonar and ski layers.', 'live-weather-station')));
    $navionics_l = lws_get_admin_page_url('lws-maps', 'form', 'add-edit', 'navionics');
    $navionics_t = '_self';
}
else {
    $navionics_s = addslashes(lws__('To add a map of this type, you need to set a Navionics API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $navionics_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $navionics_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

?>

<div id="normal-sortables" class="meta-box-sortables ui-sortable" style="overflow: hidden;">
    <div id="add-map" class="postbox ">
        <h3 class="hndle" style="cursor:default;"><span><?php esc_html_e('Please, select the type of map you want to add', 'live-weather-station' );?>&hellip;</span></h3>
        <div style="width: 100%;text-align: center;padding: 0px;" class="inside">
            <div style="display:flex;flex-direction:row;flex-wrap:wrap;">
                <style>
                    .actionable:hover {border-radius:6px;cursor:pointer; -moz-transition: all .2s ease-in; -o-transition: all .2s ease-in; -webkit-transition: all .2s ease-in; transition: all .2s ease-in; background: #f5f5f5;border:1px solid #e0e0e0;}
                    .actionable {border-radius:6px;cursor:pointer; -moz-transition: all .5s ease-in; -o-transition: all .5s ease-in; -webkit-transition: all .5s ease-in; transition: all .5s ease-in; background: transparent;border:1px solid transparent;}
                </style>
                <div style="flex:auto;padding:14px;"><img id="owm" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_owm_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="mapbox" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_mapbox_color_logo());?>" /></div>
                <?php if (LWS_PREVIEW) { ?>
                    <div style="flex:auto;padding:14px;"><img id="maptiler" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_maptiler_color_logo());?>" /></div>
                    <div style="flex:auto;padding:14px;"><img id="navionics" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_navionics_color_logo());?>" /></div>
                <?php } ?>
                <div style="flex:auto;padding:14px;"><img id="stamen" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_stamen_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="thunderforest" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_thunderforest_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="windy" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_windy_color_logo());?>" /></div>
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
            $("#navionics").mouseover(function() {
                $("#tip-text").html("<?php echo $navionics_s; ?>");
            });
            $("#navionics").click(function() {
                window.open('<?php echo $navionics_l; ?>', '<?php echo $navionics_t; ?>');
            });
            $("#stamen").mouseover(function() {
                $("#tip-text").html("<?php echo $stamen_s; ?>");
            });
            $("#stamen").click(function() {
                window.open('<?php echo $stamen_l; ?>', '<?php echo $stamen_t; ?>');
            });
            $("#thunderforest").mouseover(function() {
                $("#tip-text").html("<?php echo $thunderforest_s; ?>");
            });
            $("#thunderforest").click(function() {
                window.open('<?php echo $thunderforest_l; ?>', '<?php echo $thunderforest_t; ?>');
            });
            $("#mapbox").mouseover(function() {
                $("#tip-text").html("<?php echo $mapbox_s; ?>");
            });
            $("#mapbox").click(function() {
                window.open('<?php echo $mapbox_l; ?>', '<?php echo $mapbox_t; ?>');
            });
            $("#maptiler").mouseover(function() {
                $("#tip-text").html("<?php echo $maptiler_s; ?>");
            });
            $("#maptiler").click(function() {
                window.open('<?php echo $maptiler_l; ?>', '<?php echo $maptiler_t; ?>');
            });
            $("#owm").mouseover(function() {
                $("#tip-text").html("<?php echo $owm_s; ?>");
            });
            $("#owm").click(function() {
                window.open('<?php echo $owm_l; ?>', '<?php echo $owm_t; ?>');
            });
        });
    </script>
</div>