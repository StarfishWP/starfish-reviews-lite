<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
update_option('srm_show_powered_by', '');
update_option('srm_alart_plugin_review', '');
update_option('srm_alart_pro_promotion', '');
update_option('srm_default_review_prompt', '');
$existing_funnel_id = get_option('srm_funnel_id');
$srm_yes_review_prompt = __( '<h3>Excellent! Please Rate us 5-stars</h3>...and leave a helpful review.', 'starfish' );
update_post_meta( $existing_funnel_id, '_srm_yes_review_prompt', $srm_yes_review_prompt );
if(get_option('srm_clean_on_deactive') === 'yes'){
  update_option('srm_review_slug', '');
  update_option('srm_yes_redirect_seconds', '');
  update_option('srm_default_yn_question', '');
  update_option('srm_default_submit_button', '');
  update_option('srm_default_no_review_prompt', '');
  update_option('srm_msg_no_thank_you', '');
  update_option('srm_default_feedback_email', '');
  update_option('srm_email_from_name', '');
  update_option('srm_email_from_email', '');
  update_option('srm_to_email', '');
  update_option('srm_email_subject', '');
  update_option('srm_email_template', '');
  update_option('srm_review_destination', '');
  update_option('srm_destination_name', '');
  update_option('srm_affiliate_url', '');
  srm_clear_all_generated_post_data();
  update_option('srm_clean_on_deactive', '');
}
function srm_clear_all_generated_post_data(){
	$args = array(
		'post_type'  => array('funnel', 'starfish_review'),
		'post_status' => 'any',
		'orderby' => 'post_date',
		'order' => 'ASC',
		'posts_per_page' => -1,
	);
	$starfish_query = new WP_Query( $args );
	if ( $starfish_query->have_posts() ) {
		while ( $starfish_query->have_posts() ) {
			$starfish_query->the_post();
			$srm_post_id = get_the_ID();
      wp_delete_post( $srm_post_id, true );
		}
	}
	wp_reset_postdata();
}
