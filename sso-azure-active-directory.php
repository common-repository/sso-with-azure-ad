<?php
/**
 * Plugin Name: SSO with Azure AD
 * Description: Allows users from an Azure AD to log into WordPress using their accounts.
 * Version: 1.0.0
 * Author: Dash10 Digital
 * Author URI: https://dash10.digital
 *
 * @todo Add check to ensure trigger only works when there are settings
 * @todo Add ability to predefine roles for known users
 * @todo Add ability to define default role (no admin)
 * @todo Add ability to change/disable azure login button text on the login screen/color etc
 * @todo Add name capture with the user creation
 * @todo Add option to update/sync account on profile
 * @todo Add sign in log for admin
 */

/**
 * Adds/registers query vars
 *
 * @return void
 */
function azure_ad_auth_register_query_vars() {
	azure_ad_auth_register_rewrites();

	global $wp;
	$wp->add_query_var( 'azure-auth' );
}

add_action( 'init', 'azure_ad_auth_register_query_vars' );

/**
 * Registers rewrites for OAuth2 Server
 *
 * - authorize
 * - token
 * - .well-known
 * - wpoauthincludes
 *
 * @return void
 */
function azure_ad_auth_register_rewrites() {
	add_rewrite_rule( '^azure-auth/(.+)', 'index.php?azure-auth=$matches[1]', 'top' );
}

/**
 * [template_redirect_intercept description]
 *
 * @return [type] [description]
 */
function azure_ad_auth_template_redirect_intercept( $template ) {
	global $wp_query;

	if ( $wp_query->get( 'azure-auth' ) ) {
		define( 'DOING_AZURE_AUTH', true );
		require_once dirname( __FILE__ ) . '/includes/callback.php';
		exit;
	}

	return $template;
}

add_filter( 'template_include', 'azure_ad_auth_template_redirect_intercept', 100 );

/**
 * Register Options Page For Azure
 *
 */
function azure_ad_auth_register_settings() {
	add_option( 'azure_authenticate', '' );
	register_setting( 'azure_ad_auth_options_group', 'azure_authenticate', null );
}

add_action( 'admin_init', 'azure_ad_auth_register_settings' );

/**
 * Add Plugin Options Page
 *
 */
function azure_ad_auth_register_options_page() {
	add_options_page( 'Azure AD Authentication', 'Azure AD', 'manage_options', 'azureadauth', 'azure_ad_auth_options_page' );
}

add_action( 'admin_menu', 'azure_ad_auth_register_options_page' );

/**
 * Plugin Options Page Content
 *
 */
function azure_ad_auth_options_page() {
	?>
    <div>
        <h2>Azure Active Directory Authentication Configuration</h2>
        <p>
            Setup Azure Active Directory and ensure you set the redirect URL to
            <strong><?php echo site_url( '/azure-oauth/callback' ); ?></strong>
        </p>
        <form method="post" action="options.php">
			<?php settings_fields( 'azure_ad_auth_options_group' ); ?>
			<?php $settings = get_option( 'azure_authenticate' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="myplugin_option_name">Application ID</label>
                    </th>
                    <td>
                        <input type="text" class="regular-text"
                               name="azure_authenticate[application_id]"
                               value="<?php echo $settings['application_id']; ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="myplugin_option_name">Directory ID</label>
                    </th>
                    <td>
                        <input type="text" class="regular-text"
                               name="azure_authenticate[directory_id]"
                               value="<?php echo $settings['directory_id']; ?>"/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="myplugin_option_name">Application Secret</label>
                    </th>
                    <td>
                        <input type="text" class="regular-text" id="myplugin_option_name"
                               name="azure_authenticate[application_secret]"
                               value="<?php echo $settings['application_secret']; ?>"/>
                    </td>
                </tr>
            </table>
			<?php submit_button(); ?>
        </form>
    </div>
	<?php
}

/**
 * Function wp_sso_login_form_button
 *
 * Add login button for SSO on the login form.
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/login_form
 */
function wp_sso_login_form_button() {
	?>
    <a style="color:#FFF; width:100%; text-align:center; margin-bottom:1em;" class="button button-primary button-large"
       href="<?php echo site_url( '?azure-auth=trigger' ); ?>">Sign in using Azure AD</a>
    <div style="clear:both;"></div>
	<?php
}

add_action( 'login_form', 'wp_sso_login_form_button' );