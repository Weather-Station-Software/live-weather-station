<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */

use WeatherStation\System\Help\InlineHelp;

?>
<p>
    <label for="<?php echo $this->get_field_id( 'station' ); ?>"><?php esc_html_e( 'Station to display' , 'live-weather-station'); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'station' ); ?>" name="<?php echo $this->get_field_name( 'station' ); ?>">
        <?php foreach ($stations as $stat) { ?>
            <option value="<?php echo $stat['device_id'] ?>"<?php if ($stat['device_id']==$station):?> selected="selected"<?php endif;?>><?php echo $stat['device_name'] ?></option>;
        <?php } ?>
    </select>
</p>
<p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_current'); ?>" name="<?php echo $this->get_field_name('show_current'); ?>"<?php checked( $show_current ); ?> />
    <label for="<?php echo $this->get_field_id('show_current'); ?>"><?php esc_html_e( 'Display current thunderstorm conditions (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_strikecount'); ?>" name="<?php echo $this->get_field_name('show_strikecount'); ?>"<?php checked( $show_strikecount ); ?> />
    <label for="<?php echo $this->get_field_id('show_strikecount'); ?>"><?php esc_html_e( 'Display strike counts (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_strikebearing'); ?>" name="<?php echo $this->get_field_name('show_strikebearing'); ?>"<?php checked( $show_strikebearing ); ?> />
    <label for="<?php echo $this->get_field_id('show_strikebearing'); ?>"><?php esc_html_e( 'Display last strike bearing (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_strikedistance'); ?>" name="<?php echo $this->get_field_name('show_strikedistance'); ?>"<?php checked( $show_strikedistance ); ?> />
    <label for="<?php echo $this->get_field_id('show_strikedistance'); ?>"><?php esc_html_e( 'Display last strike distance (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_obsolete'); ?>" name="<?php echo $this->get_field_name('hide_obsolete'); ?>"<?php checked( $hide_obsolete ); ?> />
    <label for="<?php echo $this->get_field_id('hide_obsolete'); ?>"><?php esc_html_e( 'Hide obsolete measurements' , 'live-weather-station'); ?></label>
</p>
<hr>
<p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Displayed name' , 'live-weather-station'); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'subtitle' ); ?>"><?php esc_html_e( 'Subtitle to display' , 'live-weather-station'); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'subtitle' ); ?>" name="<?php echo $this->get_field_name( 'subtitle' ); ?>">
        <option value="0"<?php if ($subtitle ==0) {echo'selected="selected"';}?>><?php esc_html_e( 'Nothing' , 'live-weather-station'); ?></option>;
        <option value="1"<?php if ($subtitle ==1) {echo'selected="selected"';}?>><?php esc_html_e( 'Date and time of records' , 'live-weather-station'); ?></option>;
        <option value="2"<?php if ($subtitle ==2) {echo'selected="selected"';}?>><?php esc_html_e( 'Station coordinates (if known)' , 'live-weather-station'); ?></option>;
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php esc_html_e( 'Max width (in px)' , 'live-weather-station'); ?></label><br/>
    <input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo esc_attr( $width ); ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'txt_color' ); ?>"><?php esc_html_e( 'Text color' , 'live-weather-station'); ?></label><br/>
    <input class="widefat wp-color-picker" id="<?php echo $this->get_field_id( 'txt_color' ); ?>" name="<?php echo $this->get_field_name( 'txt_color' ); ?>" type="text" value="<?php echo esc_attr( $txt_color ); ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'bg_color' ); ?>"><?php esc_html_e( 'Background color' , 'live-weather-station'); ?></label><br/>
    <input class="widefat wp-color-picker" id="<?php echo $this->get_field_id( 'bg_color' ); ?>" name="<?php echo $this->get_field_name( 'bg_color' ); ?>" type="text" value="<?php echo esc_attr( $bg_color ); ?>" />
</p>
<label for="<?php echo $this->get_field_id( 'bg_opacity' ); ?>"><?php esc_html_e( 'Transparence of background' , 'live-weather-station'); ?></label>
<select class="widefat" id="<?php echo $this->get_field_id( 'bg_opacity' ); ?>" name="<?php echo $this->get_field_name( 'bg_opacity' ); ?>">
    <?php for ($i=0;$i<11;$i++) { ?>
        <option value="<?php echo $i ?>"<?php if ($bg_opacity==$i):?> selected="selected"<?php endif;?>><?php echo ($i*10).'%' ?></option>;
    <?php } ?>
</select>
<p>
    <label for="<?php echo $this->get_field_id('day_url'); ?>"><?php esc_html_e( 'Image URL for day' , 'live-weather-station'); ?></label><br/>
    <input type="text" class="widefat" id="<?php echo $this->get_field_id('day_url'); ?>" name="<?php echo $this->get_field_name('day_url'); ?>" value="<?php echo $day_url; ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id('night_url'); ?>"><?php esc_html_e( 'Image URL for night' , 'live-weather-station'); ?></label><br/>
    <input type="text" class="widefat" id="<?php echo $this->get_field_id('night_url'); ?>" name="<?php echo $this->get_field_name('night_url'); ?>" value="<?php echo $night_url; ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id('dawn_url'); ?>"><?php esc_html_e( 'Image URL for dawn' , 'live-weather-station'); ?></label><br/>
    <input type="text" class="widefat" id="<?php echo $this->get_field_id('dawn_url'); ?>" name="<?php echo $this->get_field_name('dawn_url'); ?>" value="<?php echo $dawn_url; ?>" />
</p>
<p>
    <label for="<?php echo $this->get_field_id('dusk_url'); ?>"><?php esc_html_e( 'Image URL for dusk' , 'live-weather-station'); ?></label><br/>
    <input type="text" class="widefat" id="<?php echo $this->get_field_id('dusk_url'); ?>" name="<?php echo $this->get_field_name('dusk_url'); ?>" value="<?php echo $dusk_url; ?>" />
</p>
<p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('fixed_background'); ?>" name="<?php echo $this->get_field_name('fixed_background'); ?>"<?php checked( $fixed_background ); ?> />
    <label for="<?php echo $this->get_field_id('fixed_background'); ?>"><?php esc_html_e( 'Fixed background' , 'live-weather-station'); ?></label>
</p>
<p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('follow_light'); ?>" name="<?php echo $this->get_field_name('follow_light'); ?>"<?php checked( $follow_light ); ?> />
    <label for="<?php echo $this->get_field_id('follow_light'); ?>"><?php esc_html_e( 'Luminosity follows current light' , 'live-weather-station'); ?></label>
</p>
<p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_borders'); ?>" name="<?php echo $this->get_field_name('show_borders'); ?>"<?php checked( $show_borders ); ?> />
    <label for="<?php echo $this->get_field_id('show_borders'); ?>"><?php esc_html_e( 'Show borders' , 'live-weather-station'); ?></label>
</p>
<p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_tooltip'); ?>" name="<?php echo $this->get_field_name('show_tooltip'); ?>"<?php checked( $show_tooltip ); ?> />
    <label for="<?php echo $this->get_field_id('show_tooltip'); ?>"><?php esc_html_e( 'Show tooltips' , 'live-weather-station'); ?></label>
</p>
<p>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('flat_design'); ?>" name="<?php echo $this->get_field_name('flat_design'); ?>"<?php checked( $flat_design ); ?> />
    <label for="<?php echo $this->get_field_id('flat_design'); ?>"><?php esc_html_e( 'Flat design' , 'live-weather-station'); ?></label>
</p>
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
<hr>