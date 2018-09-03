<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.6.0
 */

wp_enqueue_style( 'wp-color-picker' );

?>

<div class="wrap">
    <h2><?php esc_html_e('Modify a custom palette', 'live-weather-station');?></h2>
    <form method="post" name="edit-palette" id="edit-palette" action="<?php echo esc_url(lws_get_admin_page_url('lws-settings', 'do', 'styles', 'palette')); ?>">
        <input name="service" type="hidden" value="palette" />
        <input name="tab" type="hidden" value="edit" />
        <input name="action" type="hidden" value="do" />
        <input name="id" type="hidden" value="<?php echo $subject['id'] ?>" />
        <?php wp_nonce_field('edit-palette'); ?>
        <table class="form-table">
            <tr class="form-field">
                <th scope="row"><label for="palette_name"><?php esc_html_e( 'Name', 'live-weather-station' );?></label></th>
                <td align="left"><input required name="palette_name" aria-required="true" type="text" id="palette_name" value="<?php echo htmlspecialchars($subject['detail']['name']) ?>" maxlength="60" style="width:25em;" /></td>
            </tr>
            <?php for ($i=0 ; $i<8 ; $i++) {?>
                <tr class="form-field">
                    <th scope="row"><label for="color_<?php echo $i ?>"><?php echo sprintf(__('Color %s','live-weather-station'), $i+1);?></label></th>
                    <td align="left"><span class="color-picker"><input class="widefat wp-color-picker" id="color_<?php echo $i ?>" name="color_<?php echo $i ?>" type="text" value="#<?php echo htmlspecialchars($subject['detail']['colors'][$i]) ?>" /></span></td>
                </tr>
            <?php }?>
        </table>
        <p class="submit"><input type="submit" name="edit-palette" id="edit-palette" class="button button-primary" value="<?php esc_html_e('Save Changes', 'live-weather-station' );?>"  /> &nbsp;&nbsp;&nbsp; <input type="submit" name="donot-edit-palette" id="donot-edit-palette" class="button" value="<?php esc_html_e('Cancel', 'live-weather-station' );?>"  />
    </form>
    <script>
        ( function( $ ){
            function initColorPicker( widget ) {
                widget.find( '.wp-color-picker' ).wpColorPicker( {
                    change: _.throttle( function() {
                        $(this).trigger( 'change' );
                    }, 3000 )
                });
            }

            function onFormUpdate( event, widget ) {
                initColorPicker( widget );
            }

            $( document ).on( 'widget-added widget-updated', onFormUpdate );

            $( document ).ready( function() {
                if ( $( "#widgets-right" ).length ) {
                    $('#widgets-right .widget:has(.wp-color-picker)').each(function () {
                        initColorPicker($(this));
                    });
                }
                else {
                    $('.wp-color-picker').wpColorPicker();
                }
            } );
        }( jQuery ) );
    </script>
</div>