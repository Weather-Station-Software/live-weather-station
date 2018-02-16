<?php
/**
 * @package Admin\Partials
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.3.0
 */

$email = get_option('admin_email');

?>



<form name="subscribe-form" id="subscribe-form" action="<?php echo esc_url(lws_get_admin_page_url()); ?>" method="POST" style="margin:0px;padding:0px;">
    <input type="hidden" name="action" value="subscribe" />
    <?php wp_nonce_field('subscribe', '_wpnonce', false ); ?>
    <p>
        <i style="color:#999;" class="fa fa-lg fa-fw fa-envelope-o"></i>&nbsp;&nbsp;
        <?php echo sprintf(esc_attr__('Receive the latest news and updates from %s.', 'live-weather-station'), LWS_PLUGIN_NAME);?>&nbsp;&nbsp;
    </p>
    <p>
        <input required id="email" name="email" type="email" value="<?php echo $email;?>" style="width:70%">&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="submit" name="subscribe-submit" id="subscribe-submit" class="button" value="<?php esc_attr_e('Subscribe', 'live-weather-station');?>">
    </p>
    <p>
        <i><?php echo sprintf(esc_attr__('Your email address is sacred. It will not be sold or ceded. It will only be used, via MailChimp services, to send you news from %s.', 'live-weather-station'), LWS_PLUGIN_NAME);?></i>
    </p>
</form>
