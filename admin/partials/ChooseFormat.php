<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */


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
                                    <option value="<?php echo $key ?>" <?php echo ($key==='ndjson'?'SELECTED':''); ?>><?php echo $format['name'] ?></option>
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
                                <option value="generic"><?php echo lws__('Generic', 'live-weather-station') ?></option>
                            </select>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php } ?>

<?php if (isset($ndejson)) { ?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;border: none !important;">
        <div style="margin-bottom: 10px;">
            <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                <tr>
                    <td align="left">
                        <span class="login">
                            <select id="lws-ndjson" name="lws-ndjson" style="width:100%;">
                                <?php foreach($ndjson as $file) { ?>
                                    <option value="<?php echo $file['uuid'] ?>"><?php echo $file['station'] ?> (<?php echo $file['from'] ?> ⇥ <?php echo $file['to'] ?>). <?php echo $file['std_size'] . ', ' . sprintf(lws__('exported %s ago.', 'live-weather-station'), human_time_diff($file['date'])) ?></option>
                                <?php } ?>
                            </select>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php } else {?>
    <div class="activity-block" style="padding-bottom: 0px;padding-top: 0px;border: none !important;">
        <div style="margin-bottom: 10px;">
            <table cellspacing="0" class="lws-settings" style="margin-top:8px;">
                <tr>
                    <td align="left">
                        <span class="login">
                            <select id="lws-ndjson" name="lws-ndjson" disabled style="width:100%;">
                                <option value="X"><?php echo lws__('No file', 'live-weather-station') ?>&hellip;</option>
                            </select>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php } ?>

<script language="javascript" type="text/javascript">
    jQuery(document).ready(function($) {

        $("#lws-format").change(function() {
            <?php foreach($formats as $key => $format) { ?>
                if ($(this).val() == "<?php echo $key ?>") {$("#lws-format-description").html("<?php echo $format['description'] ?>");}
            <?php } ?>
        });

        $("#lws-format").change();

    });
</script>
