<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

use WeatherStation\System\Output\Guard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
?>

<?php if (isset($formats)) { ?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;border: none !important;">
        <div style="margin-bottom: 10px;">
            <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                <tr>
                    <td align="left">
                        <span class="login">
                            <select id="lws-format" name="lws-format" style="width:100%;">
                                <?php foreach($formats as $key => $format) { ?>
                                    <option value="<?php echo esc_attr($key) ?>" <?php echo ($key==='ndjson'?'SELECTED':''); ?>><?php echo esc_html($format['name']) ?></option>
                                <?php } ?>
                            </select>
                        </span>
                    </td>
                </tr>
            </table>
            <span class="login" id="lws-format-description" style="padding: 8px 8px 0px 8px;display: inline-block;"></span>
        </div>
    </div>
<?php } else { ?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;border: none !important;">
        <div style="margin-bottom: 10px;">
            <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                <tr>
                    <td align="left">
                        <span class="login">
                            <select disabled id="lws-format" name="lws-format" style="width:100%;">
                                <option value="generic"><?php esc_html_e('Generic', 'live-weather-station') ?></option>
                            </select>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php } ?>

<?php if (isset($show_files) && $show_files) { ?>
    <?php if (isset($ndjson) && count($ndjson) > 0) { ?>
        <div id="lws-ndjson-div" class="activity-block" style="padding-bottom: 0px;padding-top: 0px;border: none !important;">
            <div style="margin-bottom: 10px;">
                <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                    <tr>
                        <td align="left">
                            <span class="login">
                                <select id="lws-ndjson" name="lws-ndjson" style="width:100%;">
                                    <?php foreach($ndjson as $file) { ?>
                                        <option value="<?php echo esc_attr($file['uuid']) ?>"><?php echo esc_html($file['station']) ?> (<?php echo esc_html($file['from']) ?> ⇥ <?php echo esc_html($file['to']) ?>). <?php echo esc_html($file['std_size'] . ', ' . sprintf(/* translators: %s: time elapsed since the file was exported, like "3 hours" */ __('exported %s ago.', 'live-weather-station'), human_time_diff($file['date']))) ?></option>
                                    <?php } ?>
                                </select>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php } else {?>
        <div id="lws-ndjson-div" class="activity-block" style="padding-bottom: 0px;padding-top: 0px;border: none !important;">
            <div style="margin-bottom: 10px;">
                <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                    <tr>
                        <td align="left">
                            <span class="login">
                                <select id="lws-ndjson" name="lws-ndjson" disabled style="width:100%;">
                                    <option value="X"><?php esc_html_e('No file', 'live-weather-station') ?>&hellip;</option>
                                </select>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php } ?>
<?php } ?>

<script language="javascript" type="text/javascript">
    jQuery(document).ready(function($) {

        $("#lws-format").change(function() {
            <?php foreach((isset($formats) ? $formats : array()) as $key => $format) { ?>
                if ($(this).val() == <?php echo Guard::js($key) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() returns wp_json_encode() with the JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS and JSON_HEX_QUOT flags, a safe JavaScript literal; the description is a translated string defined by the plugin ?>) {$("#lws-format-description").html(<?php echo Guard::js($format['description']) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Guard::js() returns wp_json_encode() with the JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS and JSON_HEX_QUOT flags, a safe JavaScript literal; the description is a translated string defined by the plugin ?>);}
            <?php } ?>
            if ($(this).val() == "ndjson") {
                $("#lws-ndjson-div").show();
            }
            else {
                $("#lws-ndjson-div").hide();
            }


            if ($(this).val() == "ndjson") {
                $("#do-import-data").prop('disabled', $("#lws-ndjson").val() == "X");
            }
            if ($(this).val() == "netatmo") {
                $("#do-import-data").prop('disabled', false);
            }
        });

        $("#lws-ndjson").change(function() {
            $("#lws-format").change();
        });

        $("#lws-format").change();
        $("#lws-ndjson").change();

    });
</script>
