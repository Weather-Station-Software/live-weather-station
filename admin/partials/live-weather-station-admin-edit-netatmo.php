<?php
/**
 * @package    Live_Weather_Station
 * @subpackage Live_Weather_Station/admin/partials
 * @author     Pierre Lannoy <https://pierre.lannoy.fr/>
 * @since      2.5.0
 */
?>

<div class="wrap">
    <h2><?php esc_html_e( 'Manage services for', 'live-weather-station' );?> <?php echo htmlspecialchars($station['station_name']) ?></h2>
    <br />
    <?php if (!empty($error)) { ?>
        <p style="color:red;"><?php esc_html_e( 'Some services could not be activated. See details in the following sections...', 'live-weather-station' );?></p>
    <?php } else { ?>
        <p><?php _e( 'You can participate in the dissemination and sharing of data collected by your Netatmo weather station by enabling Live Weather Station to send, every 10 minutes, outdoor data<sup>*</sup> like temperature, pressure, humidity, dew point, wind and rain to the following services. To obtain help for each service, please click on logos.', 'live-weather-station' );?></p>
        <p><em><sup>*</sup> <?php _e( 'Note that no data from inside your home (noise, temperature, CO₂ ...) are transmitted to these services.', 'live-weather-station' );?></em></p>
    <?php } ?>
    <br />
    <form method="post" name="edit-netatmo" id="edit-netatmo" action="<?php echo esc_url( $this->get_page_url('manage_netatmo') ); ?>">
        <input name="station_id" type="hidden" value="<?php echo $station['station_id']; ?>" />
        <hr style="margin-bottom:16px;"/>
        <table class="form-table">
            <img class="hlp-trigger" style="cursor:help;" src="<?php echo LWS_ADMIN_URL . 'images/wow.png'; ?>" />
            <div class="hlp-text">
                <p><?php esc_html_e( 'To obtain Site ID and AWS 6-digit PIN from Met Office please, follow these steps:', 'live-weather-station' );?>
                    <ol>
                        <li><a target="_blank" href="https://register.metoffice.gov.uk/WaveRegistrationClient/register.do?service=weatherobservations"><?php esc_html_e( 'Create an account', 'live-weather-station' );?></a> <?php esc_html_e( 'on the Weather Observations Website from Met Office', 'live-weather-station' );?>.</li>
                        <li><?php esc_html_e( 'After registration, log in and', 'live-weather-station' );?> <a target="_blank" href="http://wow.metoffice.gov.uk/sitehandlerservlet?requestedAction=CREATE"><?php esc_html_e( 'create a site', 'live-weather-station' );?></a>.</li>
                        <li><?php _e( 'Then, copy and paste <em>Site ID</em> and <em>AWS 6-digit PIN</em> in the following fields, and activate push.', 'live-weather-station' );?></li>
                    </ol>
                <?php esc_html_e( 'After a few hours you\'ll get something', 'live-weather-station' );?> <a target="_blank" href="http://wow.metoffice.gov.uk/weather/view?siteID=966476001"><?php esc_html_e( 'like this!', 'live-weather-station' );?></a>
                </p>
            </div>
            <?php if (array_key_exists('wow', $error)) { ?>
                <p style="color:red;"><?php esc_html_e( 'Unable to activate this service. Met Office servers have returned the following error:', 'live-weather-station' );?> <em><?php echo $error['wow']; ?></em></p>
            <?php } ?>
            <tr class="form-field">
                <th scope="row"><label for="wow_user">Met Office Site ID</label></th>
                <td><input name="wow_user" type="text" id="wow_user" value="<?php echo htmlspecialchars($station['wow_user']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wow_password">Met Office AWS 6-digit PIN</label></th>
                <td><input name="wow_password" type="text" id="wow_password" value="<?php echo htmlspecialchars($station['wow_password']) ?>" maxlength="66" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wow_sync"><?php esc_html_e( 'Push data to Met Office', 'live-weather-station' );?></label></th>
                <td><input name="wow_sync" type="checkbox" id="wow_sync" <?php if ($station['wow_sync']) {?>checked="checked"<?php } ?>></td>
            </tr>
        </table>

        <hr style="margin-bottom:16px;"/>
        <table class="form-table">
            <img class="hlp-trigger" style="cursor:help;" src="<?php echo LWS_ADMIN_URL . 'images/pws.png'; ?>" />
            <div class="hlp-text">
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
                <th scope="row"><label for="pws_user">PWS Station ID</label></th>
                <td><input name="pws_user" type="text" id="pws_user" value="<?php echo htmlspecialchars($station['pws_user']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="pws_password">PWS password</label></th>
                <td><input name="pws_password" type="text" id="pws_password" value="<?php echo htmlspecialchars($station['pws_password']) ?>" maxlength="66" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="pws_sync"><?php esc_html_e( 'Push data to PWS', 'live-weather-station' );?></label></th>
                <td><input name="pws_sync" type="checkbox" id="pws_sync" <?php if ($station['pws_sync']) {?>checked="checked"<?php } ?>></td>
            </tr>
        </table>

        <hr style="margin-bottom:16px;"/>
        <table class="form-table">
            <img class="hlp-trigger" style="cursor:help;" src="<?php echo LWS_ADMIN_URL . 'images/wug.png'; ?>" />
            <div class="hlp-text">
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
                <th scope="row"><label for="wug_user">W-U Station ID</label></th>
                <td><input name="wug_user" type="text" id="wug_user" value="<?php echo htmlspecialchars($station['wug_user']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wug_password">W-U password</label></th>
                <td><input name="wug_password" type="text" id="wug_password" value="<?php echo htmlspecialchars($station['wug_password']) ?>" maxlength="66" style="width:25em;" /></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="wug_sync"><?php esc_html_e( 'Push data to Weather Underground', 'live-weather-station' );?></label></th>
                <td><input name="wug_sync" type="checkbox" id="wug_sync" <?php if ($station['wug_sync']) {?>checked="checked"<?php } ?>></td>
            </tr>
        </table>

        <input type="hidden" name="action" value="do-edit-netatmo">
        <p class="submit"><input type="submit" name="edit-netatmo" id="edit-netatmo" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'live-weather-station' );?>"  /></p>
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".hlp-text").hide();
            $(".hlp-trigger").click(function() {
                $(this).next(".hlp-text").slideToggle(600);
            });
        });
    </script>
</div>