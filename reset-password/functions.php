<?php

/**
 * Custom Gravity Forms field validation for "Forgot Password" form.
 *
 * This function checks if the entered email exists in the system before allowing form submission.
 * It runs a validation for the email address field and if the user doesn't exist, 
 * the validation fails, and a custom error message is displayed.
 *
 * @param array $result Validation result and status.
 * @param mixed $value Field value submitted by the user.
 * @param array $form The form object.
 * @param array $field The field object being validated.
 * @return array Modified validation result.
 */
add_filter('gform_field_validation', 'gform_forgot_password_field_validation', 10, 4);
function gform_forgot_password_field_validation($result, $value, $form, $field) {
	
	$form_id = rgar( $form, 'id' );
	$field_id = rgar( $field, 'id' );
	$allowed_form_field_ids = apply_filters( 'gform_forgot_password_field_validation_field_ids', [] );
	
	if( !in_array( $form_id . '_' . $field_id , $allowed_form_field_ids ) ){
		return $result;
	}
	
	$email = is_array( $value ) ? $value[0] : $value;
    $user = get_user_by( 'email', $email );
	if( empty($user) && $result['is_valid'] ) {
        $result['is_valid'] = false;
        $result['message'] = apply_filters( 'gform_forgot_password_field_validation_message', 'That email address does not exist in our system.' );
    }
	
    return $result;

}

/**
 * Customizes the "Forgot Password" notification email after form submission.
 *
 * This function dynamically generates and inserts the password reset link 
 * into the email notification sent to the user after successfully submitting 
 * a "Forgot Password" form.
 *
 * @param array $notification The current notification settings.
 * @param array $form The form object.
 * @param array $entry The entry object containing submitted form data.
 * @return array Modified notification settings with custom message.
 */
add_filter('gform_notification', 'gform_forgot_password_notification', 10, 3);
function gform_forgot_password_notification($notification, $form, $entry) {
	
	$form_id = rgar( $form, 'id' );	
	$allowed_form_ids = apply_filters( 'gform_forgot_password_notification_form_ids', [] );
	
	if( !in_array( $form_id , $allowed_form_ids ) ){
		return $notification;
	}

    // Send the forgot password email
    $user = get_user_by('email', rgar($entry, '1'));
    
    if($user->ID) {
        $displayName = $user->display_name;
        $reset_link = add_query_arg([
            'key' => get_password_reset_key($user),
            'action' => 'rp',
            'login' => urlencode($user->user_login)
        ], apply_filters( 'gform_forgot_password_notification_site_url', site_url('wp-login.php') ) );

        $notification['message'] = str_replace('{full_name}', $displayName, $notification['message']);
        $notification['message'] = str_replace('{password_link}', $reset_link, $notification['message']);
    }

    return $notification;

}

/**
 * Validates the "Reset Password" form before submission.
 *
 * This function checks the validity of the reset password key and user login 
 * before allowing the user to submit a new password. If the key or login is 
 * invalid, it returns an error and prevents form submission.
 *
 * @param array $validation_result The current form validation result.
 * @return array Updated validation result with possible error messages.
 */
add_filter( 'gform_validation', 'gform_reset_password_validation');
function gform_reset_password_validation( $validation_result ) {
    $form = $validation_result['form'];
	
	$form_id = rgar( $form, 'id' );	
	$allowed_form_ids = apply_filters( 'gform_reset_password_validation_form_ids', [] );
	$field_ids = apply_filters( 'gform_reset_password_fields_ids', [
		'user_key' => null,
		'user_login' => null,
		'user_password' => null,
	]);
	
	if( !in_array( $form_id , $allowed_form_ids ) ){
		return $validation_result;
	}
	
	$user_key = rgpost( 'input_' . $field_ids['user_key'] );
	$user_login = rgpost( 'input_' . $field_ids['user_login'] );

	if( is_wp_error( check_password_reset_key( $user_key, $user_login ) ) ){
		$validation_result['is_valid'] = false;
		foreach( $form['fields'] as &$field ) {
			// notify the user on the password field
            if ( $field->id == $field_ids['user_password'] ) {
                $field->failed_validation = true;
                $field->validation_message = apply_filters( 'gform_reset_password_validation_message', 'You do not have permission to reset the password. Please try requesting a new <a href="/login">forgot password</a> link again.' );
                break;
            }
        }
	}

    $validation_result['form'] = $form;
    return $validation_result;
  
}

/**
 * Resets the user's password after successful form submission.
 *
 * This function handles the actual password reset operation, updating the 
 * user's password in the database and logging the reset event.
 *
 * @param array $entry The entry object containing submitted form data.
 * @param array $form The form object.
 */
add_action( 'gform_after_submission', 'gform_reset_password_after_submission', 10, 2 );
function gform_reset_password_after_submission( $entry, $form ) {
	
	$form_id = rgar( $form, 'id' );	
	$allowed_form_ids = apply_filters( 'gform_reset_password_after_submission_form_ids', [] );
	$field_ids = apply_filters( 'gform_reset_password_fields_ids', [
		'user_key' => null,
		'user_login' => null,
		'user_password' => null,
	]);
	
	if( !in_array( $form_id , $allowed_form_ids ) ){
		return $result;
	}
 
	$user_key = rgar( $entry, $field_ids['user_key'] );
	$user_login = rgar( $entry, $field_ids['user_login'] );
	$password = rgar( $entry, $field_ids['user_password'] );
	$user_id = email_exists( $user_login );
	
	if ( !$user_id || is_wp_error( check_password_reset_key( $user_key, $user_login ) ) ) {
		return false;
	}
	
	wp_set_password( $password, $user_id );
	delete_user_meta( $user_id, 'password_reset_key' );
	
	$should_auto_login = apply_filters( 'gform_reset_password_after_submission_should_auto_login', '__return_false' );
	if( $should_auto_login ){
		// Set the authentication cookie and log the user in
        wp_clear_auth_cookie();
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

		// Optionally redirect after login
		$redirect_url = apply_filters( 'gform_reset_password_after_submission_auto_login_redirect_url', '' ); // Change the redirect URL if needed
		if( !empty($redirect_url) ){
			wp_safe_redirect( $redirect_url );
			exit();  // Stop further execution after redirect			
		}
	}

}
