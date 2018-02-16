<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Help\InlineHelp;

$target = ((bool)get_option('live_weather_station_redirect_internal_links') ? ' target="_blank"' : '');

$url = site_url('/get-weather/' . strtolower($station['station_id']) . '/stickertags/');
$s = '<a href="' . $url . '"' . $target . '>' . __('this URL', 'live-weather-station') . '</a>';
$message_txt = sprintf(__('This file can be accessed at %s, or at many alternates URLs.', 'live-weather-station'), $s);
$message_txt .= InlineHelp::get(16, ' ' . __('See %s for detailed information.', 'live-weather-station'),  __('documentation', 'live-weather-station'));

$url = site_url('/get-weather/' . strtolower($station['station_id']) . '/yowindow/');
$s1 = '<a href="' . $url . '"' . $target . '>' . __('this URL', 'live-weather-station') . '</a>';
$url = site_url('/get-weather/' . strtolower($station['station_id']) . '/YoWindow.xml');
$s2 = '<a href="' . $url . '"' . $target . '>' . __('this URL', 'live-weather-station') . '</a>';
$message_yow = sprintf(__('This file can be accessed at %s or at %s.', 'live-weather-station'), $s1, $s2);

$url = site_url('/get-weather/' . strtolower($station['station_id']) . '/clientraw/');
$s1 = '<a href="' . $url . '"' . $target . '>' . __('this URL', 'live-weather-station') . '</a>';
$url = site_url('/get-weather/' . strtolower($station['station_id']) . '/clientraw.txt');
$s2 = '<a href="' . $url . '"' . $target . '>' . __('this URL', 'live-weather-station') . '</a>';
$message_raw = sprintf(__('This file can be accessed at %s or at %s.', 'live-weather-station'), $s1, $s2);

$url = site_url('/get-weather/' . strtolower($station['station_id']) . '/realtime/');
$s1 = '<a href="' . $url . '"' . $target . '>' . __('this URL', 'live-weather-station') . '</a>';
$url = site_url('/get-weather/' . strtolower($station['station_id']) . '/realtime.txt');
$s2 = '<a href="' . $url . '"' . $target . '>' . __('this URL', 'live-weather-station') . '</a>';
$message_real = sprintf(__('This file can be accessed at %s or at %s.', 'live-weather-station'), $s1, $s2);

?>

<form name="publish" id="publish" action="<?php echo esc_url(lws_get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="guid" value="<?php echo $station['guid']; ?>" />
    <?php wp_nonce_field('edit-station', '_wpnonce', false ); ?>
    <div class="inside" style="padding: 11px;">
        <div class="activity-block" style="padding-bottom: 10px;padding-top: 0px;">
            <fieldset>
                <label>
                    <input name="txt_sync" id="txt_sync" type="checkbox" value="1"<?php echo ($station['txt_sync'] ? ' checked="checked"' : ''); ?>/>
                    <?php echo __('Publish outdoor data as stickertags format', 'live-weather-station'); ?>
                </label>
            </fieldset>
            <?php if ($station['txt_sync']) { ?>
                <p class="description"><?php echo $message_txt; ?></p>
            <?php } ?>

            <fieldset style="padding-top: 10px;">
                <label>
                    <input name="yow_sync" id="yow_sync" type="checkbox" value="1"<?php echo ($station['yow_sync'] ? ' checked="checked"' : ''); ?>/>
                    <?php echo __('Publish outdoor data as YoWindow XML format', 'live-weather-station'); ?>
                </label>
            </fieldset>
            <?php if ($station['yow_sync']) { ?>
                <p class="description"><?php echo $message_yow; ?></p>
            <?php } ?>
        </div>
    </div>
    <div id="major-publishing-actions">
        <div id="publishing-action" style="margin-top: -24px;margin-bottom: -20px;">
            <?php echo get_submit_button('', 'primary large', 'submit-publish'); ?>
        </div>
        <div class="clear"></div>
    </div>
</form>