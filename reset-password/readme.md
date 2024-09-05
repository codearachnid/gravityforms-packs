# Custom Gravity Forms Password Reset Functionality

This application provides custom Gravity Forms integration for password reset functionality on your WordPress site. It includes two forms, a "Forgot Password" and a "Reset Password" form, which can be imported into your Gravity Forms installation. These forms allow users to request a password reset link and set a new password. 

The code provided includes custom validation, email notifications, and password reset logic. It is designed to be placed in your theme's `functions.php` file or managed via [Code Snippets Pro](https://codesnippets.pro/) for better organization.

## Features

- **Custom Email Validation**: Validates if the entered email exists in your WordPress system before sending the reset link.
- **Dynamic Password Reset Link**: Sends a custom reset password link to the user after successful form submission.
- **Password Reset Validation**: Validates the password reset key and login credentials before allowing users to reset their passwords.
- **Auto Login (Optional)**: After resetting the password, users can be automatically logged in if enabled.
  
## How to Use

1. **Import the Forms**
   - Download the JSON files provided below and import them into your Gravity Forms using the [Gravity Forms Import Tool](https://docs.gravityforms.com/importing-a-form/).
   - [Forgot Password Form + Reset Passord Form](https://github.com/codearachnid/gravityforms-packs/blob/main/reset-password/form.json)

2. **Add the Custom Code**

   - Copy the code provided below into your theme's `functions.php` file or use the [Code Snippets Pro](https://codesnippets.pro/) plugin to manage it.

3. **Filter Configuration**

   Customize the form and field IDs as necessary by defining filters in your theme's `functions.php`. The sample filter definitions included in the code allow you to specify which forms and fields the validation applies to.

   ```php
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

   // Define field IDs for the Reset Password form
   add_filter('gform_reset_password_fields_ids', function( $value ){
       return [
           'user_key' => 1,
           'user_login' => 2,
           'user_password' => 3,
       ];
   });
   ```

4. **Modify Notifications**

   Customize the email notification content by editing the notification message in Gravity Forms. The reset link can be inserted dynamically using the `{password_link}` placeholder.

## Notes

- The provided forms and code require the [Gravity Forms](https://www.gravityforms.com/) plugin.
- Ensure that the email templates and reset links are customized to match your site's design and functionality.

### License

This code is free to use and modify as needed. Please refer to [Gravity Forms Terms of Use](https://www.gravityforms.com/terms/) for more details.
