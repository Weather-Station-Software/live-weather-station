<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Logs\Logger;

ob_start();
@phpinfo();
preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);
try {
    $phpinfo = join("\n", array_map(create_function('$i', 'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );' ), preg_split( '/\n/', $matches[1])));
    $phpinfo = str_replace('width: 934px', 'width: 90%', $phpinfo);
    $tables = $matches[2];
    $okinfo = true;
} catch (\Exception $ex) {
    $okinfo = false;
}

if ($tables == '' || !$okinfo) {
    $okinfo = false;
    Logger::warning('Core',null,null,null,null,null,null,'Your server configuration does not allow to query PHP informations.');
}

?>

<?php if ($okinfo) { ?>
    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div id="referrers" class="postbox ">
            <div class="handlediv" title="<?php echo __('Click to toggle', 'live-weather-station'); ?>"><br></div>
            <h3 class="hndle"><span><?php esc_html_e('PHP configuration', 'live-weather-station' );?></span></h3>
            <div class="inside">
                <div class='phpinfodisplay'>
                    <style type='text/css'><?php echo $phpinfo; ?></style>
                    <style type='text/css'><?php echo $phpinfo; ?></style>
                    <?php echo $tables; ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>