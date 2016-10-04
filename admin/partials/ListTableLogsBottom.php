<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.8.0
 */

?>
<div class="alignleft actions bulkactions">
    <label for="limit-selector-bottom" class="screen-reader-text"><?php esc_html_e('Number of lines to display', 'live-weather-station');?></label>
    <select name="limit" id="limit-selector-bottom">
        <?php foreach ($list->get_line_number_select() as $line) { ?>
            <option <?php echo $line['selected']; ?>value="<?php echo $line['value']; ?>"><?php echo $line['text']; ?></option>
        <?php } ?>
    </select>
    <input type="submit" class="button action" value="<?php esc_html_e('Apply', 'live-weather-station');?>"  />
</div>