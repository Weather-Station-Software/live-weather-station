<?php
/**
 * @package Admin\Partials
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template included from a function scope: its variables are local.
?>
<div class="alignleft actions bulkactions">
    <label for="limit-selector-bottom" class="screen-reader-text"><?php esc_html_e('Number of lines to display', 'live-weather-station');?></label>
    <select name="limit" id="limit-selector-bottom">
        <?php foreach ($list->get_line_number_select() as $line) { ?>
            <option <?php echo $line['selected']; ?>value="<?php echo esc_attr($line['value']); ?>"><?php echo esc_html($line['text']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $line['selected'] is either 'selected="selected" ' or an empty string (ListTableMaps::get_line_number_select()). ?></option>
        <?php } ?>
    </select>
    <input type="submit" class="button action" value="<?php esc_html_e('Apply', 'live-weather-station');?>"  />
</div>