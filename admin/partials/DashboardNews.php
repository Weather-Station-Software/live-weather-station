<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

try {
    $rss = fetch_feed($url);
} catch (\Exception $ex) {
    //$rss = null;
}
$maxitems = 0;
if (!is_wp_error($rss)) {
    setlocale(LC_ALL, lws_get_display_locale());
    $maxitems = $rss->get_item_quantity(4);
    if (isset($maxitems)) {
        if ($maxitems > 0) {
            $rss_items = $rss->get_items(0, $maxitems);
            $description = $rss_items[0]->get_description(true);
            $pos = strpos($description, '</p>');
            if ($pos > 0) {
                $description = str_replace('<p>', '', substr($description, 0, $pos));
            }
            $description = wp_trim_words(wp_strip_all_tags($description), 33);
            $id = $rss_items[0]->get_id();
        }
    }
    else {
        $maxitems = 0;
    }
}

?>
<div class="rss-widget">
    <ul>
    <?php if ($maxitems == 0) { ?>
        <li><?php _e( 'No available news', 'live-weather-station'); ?></li>
    <?php } else { ?>
        <?php foreach ($rss_items as $item) { ?>
            <li>
                <?php if ($item->get_id() == $id) { ?>
                <a class="rsswidget" href="<?php echo $item->get_permalink(); ?>"<?php echo ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank" ' : ''); ?>><?php echo $item->get_title(); ?></a>
                <span class="rss-date"><?php echo date_i18n(get_option('date_format'), strtotime($item->get_date())); ?></span>
                <div class="rssSummary">
                    <?php echo $description; ?>
                </div>
                <?php } ?>
            </li>
        <?php } ?>
    <?php } ?>
    </ul>
</div>
<div class="rss-widget">
    <ul>
        <?php if ($maxitems > 1) { ?>
            <?php foreach ($rss_items as $item) { ?>
                <?php if ($item->get_id() != $id) { ?>
                <li>
                    <a class="rsswidget" href="<?php echo $item->get_permalink(); ?>"<?php echo ((bool)get_option('live_weather_station_redirect_external_links') ? ' target="_blank" ' : ''); ?>><?php echo $item->get_title(); ?></a>
                    <span class="rss-date"><?php echo date_i18n(get_option('date_format'), strtotime($item->get_date())); ?></span>
                </li>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </ul>
</div>