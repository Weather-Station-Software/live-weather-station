<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\SVG\Handling as SVG;

$dashboard = (isset($welcome) && $welcome);

if (get_option('live_weather_station_netatmo_connected')) {
    $netatmo_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a Netatmo station to which you have access to.', 'live-weather-station'));
    $netatmo_l = lws_get_admin_page_url('lws-stations', 'form', 'add', 'Netatmo', $dashboard);
    $netatmo_t = '_self';
}
else {
    $netatmo_s = addslashes(sprintf(__('To add a station of this type, you need to connect %s to your Netatmo account. To do it, click on this logo to be redirected to the services settings.', 'live-weather-station'), LWS_PLUGIN_NAME));
    $netatmo_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $netatmo_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_netatmohc_connected')) {
    $netatmo_hc_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a Netatmo "Healthy Home Coach" device to which you have access to.', 'live-weather-station'));
    $netatmo_hc_l = lws_get_admin_page_url('lws-stations', 'form', 'add', 'NetatmoHC', $dashboard);
    $netatmo_hc_t = '_self';
}
else {
    $netatmo_hc_s = addslashes(sprintf(__('To add a station of this type, you need to connect %s to your Netatmo account. To do it, click on this logo to be redirected to the services settings.', 'live-weather-station'), LWS_PLUGIN_NAME));
    $netatmo_hc_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $netatmo_hc_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_owm_apikey') != '') {
    $loc_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a "virtual" weather station whose you only know the city or its coordinates.', 'live-weather-station'));
    $loc_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'Location', $dashboard);
    $loc_t = '_self';
}
else {
    $loc_s = addslashes(__('To add a station of this type, you need to set an OpenWeatherMap API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $loc_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $loc_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_owm_apikey') != '') {
    $owm_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a personal weather station published on OpenWeatherMap.', 'live-weather-station'));
    $owm_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'OpenWeatherMap', $dashboard);
    $owm_t = '_self';
}
else {
    $owm_s = addslashes(__('To add a station of this type, you need to set an OpenWeatherMap API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $owm_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $owm_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

if (get_option('live_weather_station_wug_apikey') != '') {
    $wug_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a personal weather station published on Weather Underground.', 'live-weather-station'));
    $wug_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'WeatherUnderground', $dashboard);
    $wug_t = '_self';
}
else {
    $wug_s = addslashes(__('To add a station of this type, you need to set a Weather Underground API key. To set it, click on this logo to be redirected to the services settings.', 'live-weather-station'));
    $wug_l = lws_get_admin_page_url('lws-settings', null, 'services');
    $wug_t = ((bool)get_option('live_weather_station_redirect_internal_links') ? '_blank' : '_self');
}

$real_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a station exporting its data via a <em>realtime.txt</em> file (Cumulus, etc.).', 'live-weather-station'));
$real_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'realtime', $dashboard);
$real_t = '_self';

$raw_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a station exporting its data via a <em>clientraw.txt</em> file (Weather Display, WeeWX, etc.).', 'live-weather-station'));
$raw_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'clientraw', $dashboard);
$raw_t = '_self';

$txt_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a station exporting its data via a stickertags file (WeatherLink, WsWin32, MeteoBridge, etc.).', 'live-weather-station'));
$txt_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'stickertags', $dashboard);
$txt_t = '_self';

$wflw_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a public WeatherFlow station.', 'live-weather-station'));
$wflw_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'weatherflow', $dashboard);
$wflw_t = '_self';

$piou_s = __('Add', 'live-weather-station') . ' ' . addslashes(__('a Pioupiou sensor as a station.', 'live-weather-station'));
$piou_l = lws_get_admin_page_url('lws-stations', 'form', 'add-edit', 'pioupiou', $dashboard);
$piou_t = '_self';

?>

<div id="normal-sortables" class="meta-box-sortables ui-sortable" style="overflow: hidden;">
    <div <?php if (isset($welcome) && $welcome) { ?>id="add-station" <?php } ?>class="postbox ">
        <?php if (!isset($welcome) || !$welcome) { ?>
            <h3 class="hndle" style="cursor:default;"><span><?php esc_html_e('Please, select the type of station you want to add', 'live-weather-station' );?>&hellip;</span></h3>
        <?php } ?>
        <div style="width: 100%;text-align: center;padding: 0px;" class="inside">
            <div style="display:flex;flex-direction:row;flex-wrap:wrap;">
                <style>
                    .actionable:hover {border-radius:6px;cursor:pointer; -moz-transition: all .2s ease-in; -o-transition: all .2s ease-in; -webkit-transition: all .2s ease-in; transition: all .2s ease-in; background: #f5f5f5;border:1px solid #e0e0e0;}
                    .actionable {border-radius:6px;cursor:pointer; -moz-transition: all .5s ease-in; -o-transition: all .5s ease-in; -webkit-transition: all .5s ease-in; transition: all .5s ease-in; background: transparent;border:1px solid transparent;}
                </style>
                <div style="flex:auto;padding:14px;"><img id="netatmo" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_netatmo_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="netatmohc" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_netatmo_hc_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="weatherflow" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_weatherflow_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="pioupiou" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_piou_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="loc" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_loc_color_logo());?>" /></div>
                <?php if (LWS_OWM_READY) { ?>
                    <div style="flex:auto;padding:14px;"><img id="owm" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_owm_color_logo());?>" /></div>
                <?php } ?>
                <div style="flex:auto;padding:14px;"><img id="wug" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_wug_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="real" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_real_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="raw" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_raw_color_logo());?>" /></div>
                <div style="flex:auto;padding:14px;"><img id="txt" class="actionable" style="width:80px;" src="<?php echo set_url_scheme(SVG::get_base64_txt_color_logo());?>" /></div>
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
            $("#netatmo").mouseover(function() {
                $("#tip-text").html("<?php echo $netatmo_s; ?>");
            });
            $("#netatmo").click(function() {
                window.open('<?php echo $netatmo_l; ?>', '<?php echo $netatmo_t; ?>');
            });
            $("#netatmohc").mouseover(function() {
                $("#tip-text").html("<?php echo $netatmo_hc_s; ?>");
            });
            $("#netatmohc").click(function() {
                window.open('<?php echo $netatmo_hc_l; ?>', '<?php echo $netatmo_hc_t; ?>');
            });
            $("#weatherflow").mouseover(function() {
                $("#tip-text").html("<?php echo $wflw_s; ?>");
            });
            $("#weatherflow").click(function() {
                window.open('<?php echo $wflw_l; ?>', '<?php echo $wflw_t; ?>');
            });
            $("#pioupiou").mouseover(function() {
                $("#tip-text").html("<?php echo $piou_s; ?>");
            });
            $("#pioupiou").click(function() {
                window.open('<?php echo $piou_l; ?>', '<?php echo $piou_t; ?>');
            });
            $("#loc").mouseover(function() {
                $("#tip-text").html("<?php echo $loc_s; ?>");
            });
            $("#loc").click(function() {
                window.open('<?php echo $loc_l; ?>', '<?php echo $loc_t; ?>');
            });
            $("#owm").mouseover(function() {
                $("#tip-text").html("<?php echo $owm_s; ?>");
            });
            $("#owm").click(function() {
                window.open('<?php echo $owm_l; ?>', '<?php echo $owm_t; ?>');
            });
            $("#wug").mouseover(function() {
                $("#tip-text").html("<?php echo $wug_s; ?>");
            });
            $("#wug").click(function() {
                window.open('<?php echo $wug_l; ?>', '<?php echo $wug_t; ?>');
            });
            $("#real").mouseover(function() {
                $("#tip-text").html("<?php echo $real_s; ?>");
            });
            $("#real").click(function() {
                window.open('<?php echo $real_l; ?>', '<?php echo $real_t; ?>');
            });
            $("#raw").mouseover(function() {
                $("#tip-text").html("<?php echo $raw_s; ?>");
            });
            $("#raw").click(function() {
                window.open('<?php echo $raw_l; ?>', '<?php echo $raw_t; ?>');
            });
            $("#txt").mouseover(function() {
                $("#tip-text").html("<?php echo $txt_s; ?>");
            });
            $("#txt").click(function() {
                window.open('<?php echo $txt_l; ?>', '<?php echo $txt_t; ?>');
            });
        });
    </script>
</div>