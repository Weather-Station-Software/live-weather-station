<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.7
 */

use WeatherStation\Utilities\Markdown;

$changelog = LWS_PLUGIN_DIR . 'changelog.txt';

if (file_exists($changelog)) {
    try {
        $s = file_get_contents($changelog);
        $Markdown = new Markdown();
        $changelog_text = $Markdown->text($s);
    }
    catch (Exception $e) {
        $changelog_text = __('Sorry, unable to find or read changelog file.', 'live-weather-station');
    }
}
else {
    $changelog_text = __('Sorry, unable to find or read changelog file.', 'live-weather-station');
}



?>
<div class="wrap">
    <h2><?php echo ucfirst(__('changelog', 'live-weather-station')); ?></h2>
    <div class="markdown">
        <style type="text/css">
            .markdown ul {
                list-style-type: disc; !important;
                padding-left: 40px !important;
            }
        </style>
        <?php echo $changelog_text ?>
    </div>
</div>