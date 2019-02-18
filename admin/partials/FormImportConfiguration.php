<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */



?>

<div class="wrap">
<?php if ($configuration['uuid'] == 'error') { ?>
    <h1><?php echo lws__('Unable to import this file', 'live-weather-station');?></h1>
    <p><?php echo sprintf(lws__('There\'s something wrong with this file, %s can\'t read it.', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
    <p><a href="<?php echo esc_url(lws_get_admin_page_url('lws-files'), null, 'url'); ?>" class="button" ><?php esc_html_e('Cancel', 'live-weather-station');?></a></p>
<?php } else { ?>
    <h1><?php echo lws__('Import configuration', 'live-weather-station');?></h1>
    <form name="import-configuration" id="import-configuration" action="<?php echo esc_url(lws_get_admin_page_url('lws-files', 'do', 'import', 'configuration', false, null, $configuration['uuid']), null, 'url'); ?>" method="POST" style="margin:0px;padding:0px;">
        <input type="hidden" name="xid" value="<?php echo $configuration['uuid']; ?>" />
        <?php wp_nonce_field('import-configuration'); ?>
        <p><?php echo lws__('Please, select the elements you want to import:', 'live-weather-station');?></p>
        <table class="form-table">
            <tbody>
            <?php if (array_key_exists('settings', $configuration)) { ?>
                <tr>
                    <th scope="row"><?php esc_html_e_lws__('Settings', 'live-weather-station');?></th>
                    <td disabled>
                        <fieldset><label><input name="configuration-settings" id="configuration-settings" type="checkbox"><?php echo sprintf(lws_n('%s element', '%s elements', $configuration['settings'], 'live-weather-station'), $configuration['settings']);?></label>
                        </fieldset>
                        <p class="description"><?php echo sprintf(lws__('Check this to import these settings in %s. Note: it will replace current settings.', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
                    </td>
                </tr>
            <?php }  ?>

            <?php if (array_key_exists('stations', $configuration)) { ?>
                <tr>
                    <th scope="row"><?php esc_html_e_lws__('Stations', 'live-weather-station');?></th>
                    <td disabled>
                        <fieldset><label><input name="configuration-stations" id="configuration-stations" type="checkbox"><?php echo sprintf(lws_n('%s element', '%s elements', $configuration['stations'], 'live-weather-station'), $configuration['stations']);?></label>
                        </fieldset>
                        <p class="description"><?php echo sprintf(lws__('Check this to import these stations in %s. Note: it will replace all current stations and modules.', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
                    </td>
                </tr>
            <?php }  ?>

            <?php if (array_key_exists('maps', $configuration)) { ?>
                <tr>
                    <th scope="row"><?php esc_html_e_lws__('Maps', 'live-weather-station');?></th>
                    <td disabled>
                        <fieldset><label><input name="configuration-maps" id="configuration-maps" type="checkbox"><?php echo sprintf(lws_n('%s element', '%s elements', $configuration['maps'], 'live-weather-station'), $configuration['maps']);?></label>
                        </fieldset>
                        <p class="description"><?php echo sprintf(lws__('Check this to import these maps in %s. Note: it will replace all current maps.', 'live-weather-station'), LWS_PLUGIN_NAME);?></p>
                    </td>
                </tr>
            <?php }  ?>
            </tbody>
        </table>
        <div style="width: 100%;clear: both;">
            <p class="submit"><input disabled type="submit" name="do-import-configuration" id="do-import-configuration" class="button button-primary" value="<?php esc_html_e_lws__('Import Elements', 'live-weather-station');?>" /> &nbsp;&nbsp;&nbsp;
                <a href="<?php echo esc_url(lws_get_admin_page_url('lws-files'), null, 'url'); ?>" class="button" ><?php esc_html_e('Cancel', 'live-weather-station');?></a>
        </div>
    </form>
    <script language="javascript" type="text/javascript">
        jQuery(document).ready(function($) {
            $("#configuration-settings").change(function() {
                $("#do-import-configuration").prop('disabled', !($("#configuration-settings").is(':checked') || $("#configuration-stations").is(':checked') || $("#configuration-maps").is(':checked')));
            });
            $("#configuration-stations").change(function() {
                $("#do-import-configuration").prop('disabled', !($("#configuration-settings").is(':checked') || $("#configuration-stations").is(':checked') || $("#configuration-maps").is(':checked')));
            });
            $("#configuration-maps").change(function() {
                $("#do-import-configuration").prop('disabled', !($("#configuration-settings").is(':checked') || $("#configuration-stations").is(':checked') || $("#configuration-maps").is(':checked')));
            });
        });
    </script>
<?php }  ?>
</div>


