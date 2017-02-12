<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 1.0.0
 */
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
    <label for="<?php echo $this->get_field_id('show_current'); ?>"><?php esc_html_e( 'Display current weather conditions (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_temperature'); ?>" name="<?php echo $this->get_field_name('show_temperature'); ?>"<?php checked( $show_temperature ); ?> />
    <label for="<?php echo $this->get_field_id('show_temperature'); ?>"><?php esc_html_e( 'Display temperatures (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_pressure'); ?>" name="<?php echo $this->get_field_name('show_pressure'); ?>"<?php checked( $show_pressure ); ?> />
    <label for="<?php echo $this->get_field_id('show_pressure'); ?>"><?php esc_html_e( 'Display atmospheric pressure (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_humidity'); ?>" name="<?php echo $this->get_field_name('show_humidity'); ?>"<?php checked( $show_humidity ); ?> />
    <label for="<?php echo $this->get_field_id('show_humidity'); ?>"><?php esc_html_e( 'Display humidity (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_rain'); ?>" name="<?php echo $this->get_field_name('show_rain'); ?>"<?php checked( $show_rain ); ?> />
    <label for="<?php echo $this->get_field_id('show_rain'); ?>"><?php esc_html_e( 'Display rainfall (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_snow'); ?>" name="<?php echo $this->get_field_name('show_snow'); ?>"<?php checked( $show_snow ); ?> />
    <label for="<?php echo $this->get_field_id('show_snow'); ?>"><?php esc_html_e( 'Display snowfall (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_wind'); ?>" name="<?php echo $this->get_field_name('show_wind'); ?>"<?php checked( $show_wind ); ?> />
    <label for="<?php echo $this->get_field_id('show_wind'); ?>"><?php esc_html_e( 'Display wind (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_cloud_cover'); ?>" name="<?php echo $this->get_field_name('show_cloud_cover'); ?>"<?php checked( $show_cloud_cover ); ?> />
    <label for="<?php echo $this->get_field_id('show_cloud_cover'); ?>"><?php esc_html_e( 'Display cloud cover (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_cloud_ceiling'); ?>" name="<?php echo $this->get_field_name('show_cloud_ceiling'); ?>"<?php checked( $show_cloud_ceiling ); ?> />
    <label for="<?php echo $this->get_field_id('show_cloud_ceiling'); ?>"><?php esc_html_e( 'Display cloud base altitude (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_dew'); ?>" name="<?php echo $this->get_field_name('show_dew'); ?>"<?php checked( $show_dew ); ?> />
    <label for="<?php echo $this->get_field_id('show_dew'); ?>"><?php esc_html_e( 'Display dew point (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_frost'); ?>" name="<?php echo $this->get_field_name('show_frost'); ?>"<?php checked( $show_frost ); ?> />
    <label for="<?php echo $this->get_field_id('show_frost'); ?>"><?php esc_html_e( 'Display frost point (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_heat'); ?>" name="<?php echo $this->get_field_name('show_heat'); ?>"<?php checked( $show_heat ); ?> />
    <label for="<?php echo $this->get_field_id('show_heat'); ?>"><?php esc_html_e( 'Display heat index (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_humidex'); ?>" name="<?php echo $this->get_field_name('show_humidex'); ?>"<?php checked( $show_humidex ); ?> />
    <label for="<?php echo $this->get_field_id('show_humidex'); ?>"><?php esc_html_e( 'Display humidex (if available)' , 'live-weather-station'); ?></label>
    <br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_windchill'); ?>" name="<?php echo $this->get_field_name('show_windchill'); ?>"<?php checked( $show_windchill ); ?> />
    <label for="<?php echo $this->get_field_id('show_windchill'); ?>"><?php esc_html_e( 'Display wind chill (if available)' , 'live-weather-station'); ?></label>
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