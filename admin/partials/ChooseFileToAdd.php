<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.8.0
 */

use WeatherStation\System\Storage\Manager;

?>

<div id="normal-sortables" class="meta-box-sortables ui-sortable" style="overflow: hidden;">
    <div id="add-file" class="postbox ">
        <h3 class="hndle" style="cursor:default;"><span><?php esc_html_e('Please, select the file you want to add, then click on "upload" button', 'live-weather-station' );?>&hellip;</span></h3>
        <div style="width: 100%;text-align: center;" class="inside">
            <div style="display:flex;flex-direction:row;flex-wrap:wrap;">
                <form method="post" name="add-file" id="add-file" action="<?php echo esc_url(lws_get_admin_page_url('lws-files', 'do', 'add', 'file')); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('add-file'); ?>
                    <p class="submit" style="float:left !important;"><input required name="file-to-upload" type="file" id="file-to-upload" accept="<?php echo Manager::get_allowed_extension(); ?>" multiple="false"/></p>
                    <p class="submit" style="float:left !important;"><input type="submit" name="do-add-file" id="do-add-file" class="button button-primary" value="<?php esc_html_e('Upload', 'live-weather-station' );?>"  /></p>
                </form>
            </div>
        </div>
    </div>
</div>