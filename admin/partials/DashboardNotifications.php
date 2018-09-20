<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */

use WeatherStation\System\Notifications\Notifier;
use WeatherStation\System\I18N\Handling as Intl;

$boxes = '';
$d = '<i style="font-size:80%" class="' . LWS_FAS . ' fa-chevron-circle-down fa-fw"></i>&nbsp;';
$c = '<i style="font-size:80%" class="' . LWS_FAS . ' fa-chevron-circle-up fa-fw"></i>&nbsp;';
$t = '<i style="font-size:80%" class="' . LWS_FAS . ' fa-trash fa-fw"></i>&nbsp;';
foreach (Notifier::get() as $notification) {
    $id = $notification['id'];
    $url = '';
    $delete = '<a onclick="jQuery.post( ajaxurl, {action: \'delete_notification\',id: ' . $id . '});jQuery(\'#notification-' . $id . '\').animate({opacity: 0}, 500, function() {jQuery(\'#notification-' . $id . '\').hide();});return false;" href="">' . $t . __('delete', 'live-weather-station') . '</a>';
    $expand = '<span id="ex-' . $id . '"><a onclick="jQuery(\'#dx-' . $id . '\').show();jQuery(\'#ex-' . $id . '\').hide();jQuery(\'#co-' . $id . '\').show();return false;" style="cursor:pointer">' . $d . __('expand', 'live-weather-station') . '</a></span>';
    $collapse = '<span id="co-' . $id . '" style="display:none;"><a onclick="jQuery(\'#dx-' . $id . '\').hide();jQuery(\'#ex-' . $id . '\').show();jQuery(\'#co-' . $id . '\').hide();return false;" style="cursor:pointer">' . $c . __('collapse', 'live-weather-station') . '</a></span>';
    $ago = '<span style="float:left;">' . sprintf( __('%s ago', 'live-weather-station'), human_time_diff(strtotime($notification['timestamp']))) . '</span>';
    $links = '<span style="text-align:right;float:right">' . $collapse . $expand . '&nbsp; &nbsp; &nbsp; &nbsp;' . $delete . '</span>';
    if ($notification['url'] !== '') {
        $target = '';
        if ((bool)get_option('live_weather_station_redirect_external_links')) {
            $target = ' target="_blank" ';
            $url = ' - <a href="' . $notification['url'] . '"' . $target . '>' . __('see details', 'live-weather-station') . '</a>' . Intl::get_language_markup(array('en'));
        }
    }
    $content = '<div style="display:inline-block; width:100%">' . $notification['name'] . $url . '</div>';
    $content .= '<div style="font-size:75%; display:inline-block;width:100%">' . $ago . $links . '</div>';
    $content .= '<div id="dx-' . $id . '"style="font-size:75%; display:none;width:100%">' . $notification['description'] . '</div>';
    $boxes .= '<div id="notification-' . $notification['id'] . '" class="lws-notification-box notice-' . $notification['level'] . ' bg-notice-' . $notification['level'] . '">' . $content . '</div>';
}

?>

<div class="activity-block" style="padding-bottom: 0px;">
    <?php echo $boxes; ?>
</div>


