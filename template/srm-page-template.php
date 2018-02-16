<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php wp_head(); ?>
<?php do_action('before-starfish-review-form'); ?>
<?php
  $srm_funnel_id = get_the_ID();
  wp_enqueue_style('srm-review-front');
  $funnel_id = $srm_funnel_id;
?>
<div id="srm_review_form" class="srm-review-form">
<?php
if(!get_post_status( $funnel_id )){
  echo __( 'This funnel is not valid or published yet!', 'starfish' );
  return;
}
if((get_post_meta( $funnel_id, '_srm_review_destination', true ) === null) || (get_post_meta( $funnel_id, '_srm_review_destination', true ) == '')){
  echo __( 'Please save starfish review setting and try again.', 'starfish' );
  return;
}
?>
<form class="srm_form" id="srm_review_form" action="" method="post">
<?php
$srm_yn_question = esc_html(get_post_meta( $funnel_id, '_srm_yn_question', true ));
$srm_review_destination_url = esc_url(get_post_meta( $funnel_id, '_srm_review_destination', true ));
$srm_review_auto_redirect_seconds = intval(get_post_meta( $funnel_id, '_srm_review_auto_redirect', true ));
$replace_site_name = get_bloginfo( 'name' );
$srm_yn_question = str_replace('{site-name}', $replace_site_name, $srm_yn_question);
$tracking_id = '';
if(isset($_GET['id'])){ $tracking_id = esc_html($_GET['id']); }
?>
<h2 class="question_heading"><?php echo $srm_yn_question; ?></h2>
<?php
	$lebel_yes = '<img src="'.SRM_LITE_PLUGIN_URL.'/img/thumbs-up-yes-positive-reivew.png" alt="'.__( 'Yes', 'starfish' ).'" />';
	$lebel_no = '<img src="'.SRM_LITE_PLUGIN_URL.'/img/thumbs-down-no-negative-reivew.png" alt="'.__( 'No', 'starfish' ).'" />';
	$srm_no_thank_you_msg = esc_html(get_post_meta( $funnel_id, '_srm_no_thank_you_msg', true ));
?>
<div class="review_submit_form_field">
	<div class="yes-no-checked" id="yes-no-checked">
			<div class="radio_item radio_item_yes"><input type="radio" name="yes_no_flag" class="srm-radio" id="srm_review_yes" value="Yes"> <label for="srm_review_yes"><?php echo $lebel_yes; ?></label></div>
			<div class="radio_item radio_item_no"><input type="radio" name="yes_no_flag" class="srm-radio" id="srm_review_no" value="No"> <label for="srm_review_no"><?php echo $lebel_no; ?></label></div>
	</div>
	<div class="review_yes_section review_yes_no_section" id="review_yes_section">
		<?php
      $destination_name = esc_html(get_option('srm_destination_name'));
      $review_prompt_text = get_post_meta( $funnel_id, '_srm_yes_review_prompt', true );
      $review_prompt_text = str_replace('{destination-name}', $destination_name, $review_prompt_text);
      $review_prompt_text = apply_filters('the_content', $review_prompt_text);
      echo $review_prompt_text;
    ?>
		<input type="hidden" name="reveiw_destination_url" id="reveiw_destination_url" value="<?php echo $srm_review_destination_url; ?>">
	</div>
	<div class="review_no_section review_yes_no_section" id="review_no_section">
		<?php echo get_post_meta( $funnel_id, '_srm_no_review_prompt', true ); ?>
		<textarea name="review_text" id="review_text" placeholder="<?php echo __( 'Leave your review.', 'starfish' ); ?>"></textarea>
		<input type="hidden" name="reveiw_no_thank_you" id="reveiw_no_thank_you" value="<?php echo $srm_no_thank_you_msg; ?>">
	</div>
	<input type="hidden" name="funnel_id" id="funnel_id" value="<?php echo $funnel_id; ?>">
	<input type="hidden" name="tracking_id" id="tracking_id" value="<?php echo $tracking_id; ?>">
	<?php $srm_reveiw_nonce = wp_create_nonce( "srm_reveiw_nonce" ); ?>
<input type="button" class="btn_review_submit" name="submit_review" id="submit_review" value="<?php echo esc_attr(get_post_meta( $funnel_id, '_srm_button_text', true )); ?>">
</div><!-- review_submit_form_field -->
</form>
<div class="review_under_processing">Sending...</div>

<script type="text/javascript">
	var srm_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>
<script type="text/javascript">
	jQuery(document).ready(function(){
				jQuery('#srm_review_yes').change(function(e) {
					e.preventDefault();
					jQuery('#srm_review_no').attr('checked', false);
					jQuery('#srm_review_yes').attr('checked', true);
					jQuery( "#review_no_section" ).hide( "slow");
					jQuery( "#review_yes_section" ).show( "slow");
					jQuery( "#submit_review" ).show( "slow");
					jQuery( "#yes-no-checked" ).hide( "slow");
					jQuery( ".question_heading" ).hide( "slow");
				});
				jQuery('#srm_review_no').change(function (e) {
					e.preventDefault();
					jQuery('#srm_review_yes').attr('checked', false);
					jQuery('#srm_review_no').attr('checked', true);
					jQuery( "#review_yes_section" ).hide( "slow");
					jQuery( "#review_no_section" ).show( "slow");
					jQuery( "#submit_review" ).show( "slow");
					jQuery( "#yes-no-checked" ).hide( "slow");
					jQuery( ".question_heading" ).hide( "slow");
				});
	});
</script>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        jQuery('.review_under_processing').hide();
        jQuery('.btn_review_submit').click(function() {
            jQuery('.review_under_processing').show('slow');
						var yes_no_flag = jQuery(".yes-no-checked input[type='radio']:checked").val();
						var reveiw_destination_url = jQuery('#reveiw_destination_url').val();
						var reveiw_yes_thank_you = jQuery('#reveiw_yes_thank_you').val();
						var reveiw_no_thank_you = jQuery('#reveiw_no_thank_you').val();
						var reveiw_msg_thank_you = '';
						if(yes_no_flag !== 'Yes'){
								reveiw_msg_thank_you = reveiw_no_thank_you;
						}
            var dataContainer = {
                security: '<?php echo $srm_reveiw_nonce; ?>',
                yes_no_flag: yes_no_flag,
                funnel_id: jQuery('#funnel_id').val(),
								tracking_id: jQuery('#tracking_id').val(),
								review_text: jQuery('#review_text').val(),
								reveiw_destination_url: reveiw_destination_url,
				        action: 'send-starfish-review-data'
            };
            jQuery.ajax({
                action: "send-starfish-review-data",
                type: "POST",
                dataType: "json",
                url: srm_ajaxurl,
                data: dataContainer,
                success: function(data){
                	//alert(data.msg);
                  if(data.msg == 'Complete'){
                    jQuery('.review_under_processing').html('<div class="success">'+reveiw_msg_thank_you+'</div>');
                    //jQuery('.review_under_processing').delay(3000).fadeOut('slow');
										jQuery('.review_submit_form_field').delay(1000).fadeOut('slow');
										if(yes_no_flag === 'Yes'){
											window.location = reveiw_destination_url;
										}
                  }else{
                    jQuery('.review_under_processing').html('<span class="error">Sending error</span>');
                    jQuery('.review_under_processing').delay(3000).fadeOut('slow');
                  }
                }
            });
        });
    });
</script>
<?php
	if(get_option('srm_affiliate_text') != ''){
		//$srm_affiliate_text = esc_html(get_option('srm_affiliate_text'));
		$srm_affiliate_text = __( 'Powered by Starfish', 'starfish' );
	}else{
		$srm_affiliate_text = __( 'Powered by Starfish', 'starfish' );
	}
	if(get_option('srm_affiliate_url') != ''){
		$srm_affiliate_url = esc_url(get_option('srm_affiliate_url'));
	}else{
		$srm_affiliate_url = 'https://starfishwp.com';
	}
?>
<?php if(get_option('srm_show_powered_by') == 'yes'){ ?>
<div id="srm_powred_by_txt" class="srm-powered-by"><a href="<?php echo $srm_affiliate_url; ?>" target="_blank"><?php echo $srm_affiliate_text; ?></a></div><!-- srm-powered-by -->
<?php } ?>
</div><!-- srm-review-form -->

<?php do_action('after-starfish-review-form'); ?>
<div class="starfish_review_footer_section">
<?php wp_footer(); ?>
</div>
