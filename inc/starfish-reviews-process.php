<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
**Process Submitted Reviews
**/
function srm_send_starfish_review_data() {
	$funnel_id = intval($_POST['funnel_id']);
	$tracking_id = sanitize_text_field($_POST['tracking_id']);
	$yes_no_flag = sanitize_text_field($_POST['yes_no_flag']);
	$review_text = sanitize_textarea_field($_POST['review_text']);
	$reveiw_destination_url = sanitize_text_field($_POST['reveiw_destination_url']);

	$current_local_time = date( 'j M Y g:i a', current_time( 'timestamp', 0 ) );

	if (!check_ajax_referer( 'srm_reveiw_nonce', 'security' )) {
		echo json_encode(array("msg" => "Error"));
		exit;
	}else{
		$defaults_review_arg = array(
					  'post_type'      => 'starfish_review',
					  'post_title'     => $current_local_time,
					  'post_content'  =>   $review_text,
					  'post_status'    => 'publish'
					);
					if($review_id = wp_insert_post( $defaults_review_arg )) {
			  		// add user to profile cpt
			  		add_post_meta($review_id, '_srm_feedback', $yes_no_flag);
						add_post_meta($review_id, '_srm_funnel_id', $funnel_id);
						add_post_meta($review_id, '_srm_tracking_id', $tracking_id);
						add_post_meta($review_id, '_srm_destination_url', $reveiw_destination_url);
			  	}
		//Send email if negative feedback
		if($yes_no_flag != 'Yes'){
			srm_send_feedback_email($funnel_id, $review_text, $tracking_id, $review_id);
		}
		echo json_encode(array("msg" => "Complete"));
		exit;
	}

}
add_action('wp_ajax_send-starfish-review-data', 'srm_send_starfish_review_data');
add_action('wp_ajax_nopriv_send-starfish-review-data', 'srm_send_starfish_review_data');


function srm_send_feedback_email($funnel_id, $reveiw_message, $tracking_id, $review_id){

	$admin_email = get_option('admin_email');
	$blogname = get_option('blogname');

	$funnel_name = get_the_title($funnel_id);
	$email_subject = get_option('srm_email_subject');
	$search_subject = array();
	$replace_subject = array();
	$search_subject[] = '{funnel-name}';
	$replace_subject[] = $funnel_name;
	$email_subject = str_replace($search_subject, $replace_subject, $email_subject);


	//process email message
	$email_message = get_option('srm_email_template');
	$search = array();
	$replace = array();
	$search[] = '{admin-email}';
	$replace[] = $admin_email;
	$search[] = '{site-name}';
	$replace[] = $blogname;
	$search[] = '{review-message}';
	$replace[] = $reveiw_message;
	$get_message = str_replace($search, $replace, $email_message);
	$get_message = 	stripslashes($get_message);
	$message_send = apply_filters('the_content', $get_message);

	//process email addresses
	$srm_email_feedback = esc_html(get_post_meta( $funnel_id, '_srm_email_feedback', true ));
	$search = array();
	$replace = array();
	$search[] = '{admin-email}';
	$replace[] = $admin_email;
	$get_email_addresses = str_replace($search, $replace, $srm_email_feedback);
	$get_email_address_arr = explode(",", $get_email_addresses);

	$srm_email_from_name = esc_html(get_option('srm_email_from_name'));
	$search = array();
	$replace = array();
	$search[] = '{site-name}';
	$replace[] = $blogname;
	$srm_email_from_name = str_replace($search, $replace, $srm_email_from_name);

	$srm_email_from_email = esc_html(get_option('srm_email_from_email'));
	$search = array();
	$replace = array();
	$search[] = '{admin-email}';
	$replace[] = $admin_email;
	$srm_email_from_email = str_replace($search, $replace, $srm_email_from_email);

	$headers[]  = 'MIME-Version: 1.0' . "\r\n";
	$headers[] = 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
	if(($srm_email_from_name != '') && ($srm_email_from_email != '')){
		$headers[] = 'From: '.$srm_email_from_name.' <'.$srm_email_from_email.'>';
	}

	if(isset($get_email_address_arr) && is_array($get_email_address_arr) && (count($get_email_address_arr) > 0)){
		foreach ($get_email_address_arr as $key => $email_address) {
				$email_address = trim($email_address);
				add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
				@wp_mail( $email_address, $email_subject, $message_send, $headers );
		}
	}

}
