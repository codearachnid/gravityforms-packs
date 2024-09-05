<?php
/**
 * These are the sample configurations you can programatically customize the behaviour and content of the workflow
 **/

add_filter('gform_forgot_password_field_validation_field_ids', function( $value ){
	return [ '1_1' ];
});

add_filter('gform_forgot_password_notification_form_ids', function( $value ){
	return [ 1 ];
});

add_filter('gform_reset_password_validation_form_ids', function( $value ){
	return [ 1 ];
});

add_filter('gform_reset_password_after_submission_form_ids', function( $value ){
	return [ 1 ];
});

add_filter('gform_reset_password_after_submission_should_auto_login', '__return_true');

// define the field ids from the `reset password` form
add_filter('gform_reset_password_fields_ids', function( $value ){
	return [
		'user_key' => 1,
		'user_login' => 2,
		'user_password' => 3,
	];
});
