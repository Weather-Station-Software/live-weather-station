<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 2.0.0
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
    <label for="<?php echo $this->get_field_id( 'format' ); ?>"><?php esc_html_e( 'Format' , 'live-weather-station'); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'format' ); ?>" name="<?php echo $this->get_field_name( 'format' ); ?>">
        <option value="0"<?php if ($format ==0) {echo'selected="selected"';}?>><?php esc_html_e( 'Compact' , 'live-weather-station'); ?></option>;
        <option value="1"<?php if ($format ==1) {echo'selected="selected"';}?>><?php esc_html_e( 'Standard' , 'live-weather-station'); ?></option>;
        <option value="2"<?php if ($format ==2) {echo'selected="selected"';}?>><?php esc_html_e( 'Extended' , 'live-weather-station'); ?></option>;
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id( 'mode' ); ?>"><?php esc_html_e( 'Ephemeris mode' , 'live-weather-station'); echo InlineHelp::article(8)?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'mode' ); ?>" name="<?php echo $this->get_field_name( 'mode' ); ?>">
        <option value="0"<?php if ($mode ==0) {echo'selected="selected"';}?>><?php esc_html_e( 'Standard' , 'live-weather-station'); ?></option>;
        <option value="1"<?php if ($mode ==1) {echo'selected="selected"';}?>><?php esc_html_e( 'Civil' , 'live-weather-station'); ?></option>;
        <option value="2"<?php if ($mode ==2) {echo'selected="selected"';}?>><?php esc_html_e( 'Nautical' , 'live-weather-station'); ?></option>;
        <option value="3"<?php if ($mode ==3) {echo'selected="selected"';}?>><?php esc_html_e( 'Astronomical' , 'live-weather-station'); ?></option>;
    </select>
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