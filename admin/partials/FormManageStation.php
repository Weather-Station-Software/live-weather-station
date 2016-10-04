<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

?>

<div class="wrap">
    <h2><?php esc_html_e( 'Manage associated services for', 'live-weather-station' );?> <?php echo htmlspecialchars($station['station_name']) ?></h2>
    <br />
    <?php if (!empty($error)) { ?>
        <p style="color:red;"><?php _e('Some services could not be activated. See details in the following sections...', 'live-weather-station' );?></p>
    <?php } else { ?>
        <p><?php echo sprintf(__('You can participate in the dissemination and sharing of data collected by your personal weather station by enabling %s to send, every 10 minutes, outdoor data<sup>*</sup> like temperature, pressure, humidity, dew point, wind and rain to the following services. To obtain help for each service, please click on logos.', 'live-weather-station' ), LWS_PLUGIN_NAME);?></p>
        <p><em><sup>*</sup> <?php _e('Note that no data from inside your home (noise, temperature, CO₂ ...) are transmitted to these services.', 'live-weather-station' );?></em></p>
    <?php } ?>
    <br />
    <form method="post" name="edit-netatmo" id="edit-netatmo" action="<?php echo esc_url(get_admin_page_url('lws-stations')); ?>">
        <input name="guid" type="hidden" value="<?php echo $station['guid']; ?>" />
        <input name="service" type="hidden" value="station" />
        <input name="tab" type="hidden" value="manage" />
        <input name="action" type="hidden" value="do" />
        <?php wp_nonce_field('manage-station'); ?>
        <hr style="margin-bottom:16px;"/>
        <table class="form-table">
            <img class="hlp-trigger" style="cursor:help;" src="<?php echo LWS_ADMIN_URL . 'images/wow.png'; ?>" />
            <div class="hlp-text" style="display:none;">
                <p><?php esc_html_e( 'To obtain site ID and authentication key from Met Office please, follow these steps:', 'live-weather-station' );?>
                <ol>
                    <li><a target="_blank" href="https://register.metoffice.gov.uk/WaveRegistrationClient/public/register.do?service=weatherobservations"><?php esc_html_e( 'Create an account', 'live-weather-station' );?></a> <?php esc_html_e( 'on the Weather Observations Website from Met Office', 'live-weather-station' );?>.</li>
                    <li><?php esc_html_e( 'After registration, log in and', 'live-weather-station' );?> <a target="_blank" href="http://wow.metoffice.gov.uk/sites/create"><?php esc_html_e( 'create a site', 'live-weather-station' );?></a>.</li>
                    <li><?php _e('Then, copy and paste <em>Site ID</em> and <em>Authentication Key</em> in the following fields, and activate push.', 'live-weather-station' );?></li>
                </ol>
                <?php esc_html_e( 'After a few hours you\'ll get something', 'live-weather-station' );?> <a target="_blank" href="http://wow.metoffice.gov.uk/weather/view?siteID=966476001"><?php esc_html_e( 'like this!', 'live-weather-station' );?></a>
                </p>
            </div>
            <?php if (array_key_exists('wow', $error)) { ?>
                <p style="color:red;"><?php esc_html_e( 'Unable to activate this service. Met Office servers have returned the following error:', 'live-weather-station' );?> <em><?php echo $error['wow']; ?></em></p>
            <?php } ?>
            <tr class="form-field">
                <th scope="row"><label for="wow_user"><?php esc_html_e('Site ID', 'live-weather-station');?></label></th>
                <td><input name="wow_user" type="text" id="wow_user" value="<?php echo htmlspecialchars($station['wow_user']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wow_password"><?php esc_html_e('Authentication key', 'live-weather-station');?></label></th>
                <td><input name="wow_password" type="text" id="wow_password" value="<?php echo htmlspecialchars($station['wow_password']) ?>" maxlength="66" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wow_sync"><?php esc_html_e( 'Push data', 'live-weather-station' );?></label></th>
                <td><input name="wow_sync" type="checkbox" id="wow_sync" <?php if ($station['wow_sync']) {?>checked="checked"<?php } ?>></td>
            </tr>
        </table>

        <hr style="margin-bottom:16px;"/>
        <table class="form-table">
            <img class="hlp-trigger" style="cursor:help;" src="<?php echo LWS_ADMIN_URL . 'images/pws.png'; ?>" />
            <div class="hlp-text" style="display:none;">
                <p><?php esc_html_e( 'To obtain Station ID from PWS please, follow these steps:', 'live-weather-station' );?>
                <ol>
                    <li><a target="_blank" href="http://www.pwsweather.com/register.php"><?php esc_html_e( 'Create an account', 'live-weather-station' );?></a> <?php esc_html_e( 'on the PWS website', 'live-weather-station' );?>.</li>
                    <li><?php esc_html_e( 'After registration, log in and', 'live-weather-station' );?> <a target="_blank" href="http://www.pwsweather.com/station.php"><?php esc_html_e( 'add a new station', 'live-weather-station' );?></a>.</li>
                    <li><?php _e( 'Then, copy and paste <em>Station ID</em> in the corresponding following field, set your password and activate push.', 'live-weather-station' );?></li>
                </ol>
                <?php esc_html_e( 'After a few hours you\'ll get something', 'live-weather-station' );?> <a target="_blank" href="http://www.pwsweather.com/obs/MOUVAUX.html"><?php esc_html_e( 'like this!', 'live-weather-station' );?></a>
                </p>
            </div>
            <?php if (array_key_exists('pws', $error)) { ?>
                <p style="color:red;"><?php esc_html_e( 'Unable to activate this service. PWS servers have returned the following error:', 'live-weather-station' );?> <em><?php echo $error['pws']; ?></em></p>
            <?php } ?>
            <tr class="form-field">
                <th scope="row"><label for="pws_user"><?php esc_html_e('Station ID', 'live-weather-station');?></label></th>
                <td><input name="pws_user" type="text" id="pws_user" value="<?php echo htmlspecialchars($station['pws_user']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="pws_password"><?php esc_html_e('Password', 'live-weather-station');?></label></th>
                <td><input name="pws_password" type="text" id="pws_password" value="<?php echo htmlspecialchars($station['pws_password']) ?>" maxlength="66" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="pws_sync"><?php esc_html_e( 'Push data', 'live-weather-station' );?></label></th>
                <td><input name="pws_sync" type="checkbox" id="pws_sync" <?php if ($station['pws_sync']) {?>checked="checked"<?php } ?>></td>
            </tr>
        </table>

        <hr style="margin-bottom:16px;"/>
        <table class="form-table">
            <img class="hlp-trigger" style="cursor:help;" src="<?php echo LWS_ADMIN_URL . 'images/wug.png'; ?>" />
            <div class="hlp-text" style="display:none;">
                <p><?php esc_html_e( 'To obtain Station ID from Weather Underground please, follow these steps:', 'live-weather-station' );?>
                <ol>
                    <li><a target="_blank" href="https://www.wunderground.com/personal-weather-station/signup"><?php esc_html_e( 'Create an account', 'live-weather-station' );?></a> <?php esc_html_e( 'on the Weather Underground website', 'live-weather-station' );?>.</li>
                    <li><?php esc_html_e( 'After registration, log in and', 'live-weather-station' );?> <a target="_blank" href="https://www.wunderground.com/personal-weather-station/signup?new=1"><?php esc_html_e( 'add a new station by following the 4 steps registration form', 'live-weather-station' );?></a>.</li>
                    <li><?php _e( 'Then, copy and paste <em>Station ID</em> in the corresponding following field, set your password and activate push.', 'live-weather-station' );?></li>
                </ol>
                <?php esc_html_e( 'After a few hours you\'ll get something', 'live-weather-station' );?> <a target="_blank" href="https://www.wunderground.com/personal-weather-station/dashboard?ID=INORDPAS92"><?php esc_html_e( 'like this!', 'live-weather-station' );?></a>
                </p>
            </div>
            <?php if (array_key_exists('wug', $error)) { ?>
                <p style="color:red;"><?php esc_html_e( 'Unable to activate this service. Weather Underground servers have returned the following error:', 'live-weather-station' );?> <em><?php echo $error['wug']; ?></em></p>
            <?php } ?>
            <tr class="form-field">
                <th scope="row"><label for="wug_user"><?php esc_html_e('Station ID', 'live-weather-station');?></label></th>
                <td><input name="wug_user" type="text" id="wug_user" value="<?php echo htmlspecialchars($station['wug_user']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wug_password"><?php esc_html_e('Password', 'live-weather-station');?></label></th>
                <td><input name="wug_password" type="text" id="wug_password" value="<?php echo htmlspecialchars($station['wug_password']) ?>" maxlength="66" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wug_sync"><?php esc_html_e( 'Push data', 'live-weather-station' );?></label></th>
                <td><input name="wug_sync" type="checkbox" id="wug_sync" <?php if ($station['wug_sync']) {?>checked="checked"<?php } ?>></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="manage-station" id="manage-station" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp; <input type="submit" name="donot-manage-station" id="donot-manage-station" class="button" value="<?php esc_html_e( 'Cancel', 'live-weather-station' );?>"  />
            <span id="span-sync" style="display: none;"><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>&nbsp;<strong><?php echo __('Contacting services, please wait', 'live-weather-station');?>&hellip;</strong></span></p>
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".hlp-trigger").click(function() {
                $(this).next(".hlp-text").slideToggle(600);
            });
        });
    </script>
</div>