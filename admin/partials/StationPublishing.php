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
$message = sprintf(__('This stickertags file can be accessed at %s, or at alternates URLs.', 'live-weather-station'), $s);
$message .= InlineHelp::get(16, ' ' . __('See %s for detailed information.', 'live-weather-station'),  __('documentation', 'live-weather-station'));

?>

<form action="<?php echo esc_url(get_admin_page_url('lws-stations', 'manage', 'view', 'station', false, $station['guid'])); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="action" value="manage" />
    <input type="hidden" name="service" value="station" />
    <input type="hidden" name="tab" value="edit" />
    <input type="hidden" name="guid" value="<?php echo $station['guid']; ?>" />
    <?php wp_nonce_field('edit-station', '_wpnonce', false ); ?>
    <div class="inside" style="padding: 11px;">
        <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
            <fieldset>
                <label>
                    <input name="txt_sync" id="txt_sync" type="checkbox" value="1"<?php echo ($station['txt_sync'] ? ' checked="checked"' : ''); ?>/>
                    <?php echo __('Publish outdoor data as stickertags format', 'live-weather-station'); ?>
                </label>
            </fieldset>
            <?php if ($station['txt_sync']) { ?>
                <p class="description"><?php echo $message; ?></p>
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