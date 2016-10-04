<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

ob_start();
phpinfo();
preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);
$phpinfo = join("\n", array_map(create_function('$i', 'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );' ), preg_split( '/\n/', $matches[1])));

?>
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
    <div id="referrers" class="postbox ">
        <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'PHP configuration of your server', 'live-weather-station' );?></span></h3>
        <div class="inside">
            <div class='phpinfodisplay'>
                <style type='text/css'><?php echo $phpinfo; ?></style>
                <?php echo $matches[2]; ?>
            </div>
        </div>
    </div>
</div>