=== SSO with Azure AD ===
Contributors: justingreerbbi
Donate link: http://dash10.digital/
Tags: azure, oauth, sso
Requires at least: 5.1
Tested up to: 5.2.2
Requires PHP: 5.3
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple way to enable Single Sign On with Azure Active Directory.

== Description ==

Simple yet powerful plugin that is designed solely for Single Sign On using Azure Active Directory.

**Setup**
1. Create a Directory in Azure AD.
1. Create a Secret.
1. Enter the credentials in the plugins settings page.

**Manually Trigger Single Sign On Process**
1. Simple link to `your-site.com?azure-auth=trigger`

== Installation ==

1. Search for "Authenticate with Azure AD" in WordPress's plugin manager or Download "Authenticate with Azure AD" from wordpress.org
1. Install and Activate "Authenticate with Azure AD".
1. Go to Settings -> Azure AD and configure the client using your Azure app credentials.

== Frequently Asked Questions ==

= Does SSO with Azure AD transfer the roles from Azure? =

No. Currently all accounts created become the default role set in WordPress.

= Is the password secure or exposed? =

Not at all. As a matter of fact, there is no transfer of the password from Azure AD to WordPress.

== Changelog ==

= 1.0.0 =
* Initial Build
