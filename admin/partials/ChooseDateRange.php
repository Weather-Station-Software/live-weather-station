<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */


?>

<?php if (isset($constraint_range) && $constraint_range) { ?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
        <div style="margin-bottom: 10px;">
            <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                <tr>
                    <th class="lws-login" width="23%" align="left" scope="row"><?php esc_html_e('From', 'live-weather-station' );?>&hellip;</th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input class="regular-text" id="lws-date-start" name="lws-date-start" type="date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" value="<?php echo htmlspecialchars($station['oldest_data']) ?>" min="<?php echo htmlspecialchars($station['oldest_data']) ?>" max="<?php echo htmlspecialchars($station['newest_data']) ?>" /></span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-login" width="23%" align="left" scope="row"><?php esc_html_e('To', 'live-weather-station' );?>&hellip;</th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input class="regular-text" id="lws-date-end" name="lws-date-end" type="date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" value="<?php echo htmlspecialchars($station['newest_data']) ?>" min="<?php echo htmlspecialchars($station['oldest_data']) ?>" max="<?php echo htmlspecialchars($station['newest_data']) ?>" /></span>
                    </td>
                </tr>
            </table>
            <?php if ($show_override) { ?>
                    <span class="login" style="padding: 8px 8px 0px 8px;display: inline-block;"><input style="margin-top: 1px; margin-right: 10px;" id="lws-option-override" name="lws-option-override" type="checkbox" /><label for="lws-option-override"><?php esc_html_e('Overwrite data for dates already compiled.', 'live-weather-station') ?></label></span>
            <?php } ?>
        </div>
    </div>
<?php } else { ?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;">
        <div style="margin-bottom: 10px;">
            <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                <tr>
                    <th class="lws-login" width="38%" align="left" scope="row"><?php esc_html_e('From', 'live-weather-station' );?>&hellip;</th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input class="regular-text" id="lws-date-start" name="lws-date-start" type="date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" value="<?php echo htmlspecialchars($station['newest_data']) ?>" /></span>
                    </td>
                </tr>
                <tr>
                    <th class="lws-login" width="38%" align="left" scope="row"><?php esc_html_e('To', 'live-weather-station' );?>&hellip;</th>
                    <td width="2%"/>
                    <td align="left">
                        <span class="login"><input class="regular-text" id="lws-date-end" name="lws-date-end" type="date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" value="<?php echo htmlspecialchars($station['newest_data']) ?>" /></span>
                    </td>
                </tr>
            </table>
            <?php if ($show_override) { ?>
                <span class="login" style="padding: 8px 8px 0px 8px;display: inline-block;"><input style="margin-top: 1px; margin-right: 10px;" id="lws-option-override" name="lws-option-override" type="checkbox" /><label for="lws-option-override"><?php esc_html_e('Overwrite data for dates already compiled.', 'live-weather-station') ?></label></span>
            <?php } ?>
        </div>
    </div>
<?php } ?>