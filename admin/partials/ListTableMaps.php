<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\UI\ListTable\Maps;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
$mapsListTable = new Maps();
$mapsListTable->prepare_items();

?>
<div class="wrap">
    <h2><?php echo esc_html__('Maps', 'live-weather-station');?> <a href="#" class="page-title-action add-trigger"><?php echo esc_html__('Add', 'live-weather-station'); ?></a></h2>
    <?php settings_errors(); ?>
    <div class="add-text" style="display:none;">
        <div id="wpcom-stats-meta-box-container" class="metabox-holder">
            <div class="postbox-container" style="width: 100%;margin-right: 10px;">
                <?php include(LIVE_WEATHER_STATION_ADMIN_DIR.'partials/ChooseMapType.php'); ?>
            </div>
        </div>
    </div>
    <form id="maps-filter" method="get">
        <input type="hidden" name="page" value="lws-maps" />
        <?php $mapsListTable->display(); ?>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".add-trigger").click(function() {
                $(".add-text").slideToggle(400);
            });
        });
    </script>
</div>