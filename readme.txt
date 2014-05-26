=== Password Vault ===
Contributors: mrdenny
Donate Link: http://dcac.co/go/password-vault
Tags: passwords, password vault, password repository, password locker, password, keypass, 7pass, lastpass, 1password, keeper
Requires at least: 3.0.1
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gives you an application within the Wordpress admin screen which allows you to securely save information for accounts 
which you use regularly.

== Description ==

Allows for the secure saving of passwords within the Wordpress Admin Interface.  Access to a accounts can be given based on 
wordpress users and/or groups.  Groups are defined within the plugin directly on the settings screen.  Plugin uses your sites 
specific SECURE_AUTH_KEY value from your wp-config.php file as your encryption key, so no two sites use the same key (a 
warning is shown if you are using the default value).

The plugin includes 5 user configurable fields ("Client Name" and "Account Type" in the screenshots) so you can customize
them for your needs.

All viewing of passwords as well as changing of passwords is logged for auditing purposes.

Encryption keys can be changed by putting the new key in the wp-config.php file and the old key in the settings page, then 
running through the key migration process.

Searching in the username field supports wildcard searching by default.  By default the five user defined fields are static 
matching when there is a value in the field.  Wildcard searching is supported on the user defined fields by using the %.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the contents of the zip file to the `/wp-content/plugins/password-vault` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set a secure password as the SECURE_AUTH_KEY value in the wp-config.php file
4. Configure the settings through the settings page.
5. Begin documenting account information through the tools page.


== Frequently Asked Questions ==

= What does this plugin do? =

This plugin allows you to securly store usernames and passwords online in a password protected repository.  

= How do I change the encryption key? =

To change the encryption key, first enter the new key in the wp-config.php file.  Then open the settings page,
and put the old key in the field at the bottom and save the settings.  A new menu option at the top of the page
will appear called "Complete Key Change".  Select this link, check the checkbox to verify that you have a backup
and click the "Complete Key Change" button.  Depending on how much data you have in the main table and the audit 
table this process may run quickly or it may take a long time.  No matter how long it takes do NOT stop the process.

Stopping the process can cause you to loose access to some or all of the data.

After the process is done, go back to the main page and remove the old encryption key and save the settings.  This will
remove the link from the menu at the top of the page.

= How many Groups can I have? =

Basically as many as you want.  The field is an INT(11) in MySQL so you should be able to have 2,147,483,647 groups.

= Where are the users configured? =

The users are simply users from Wordpress.

= Can a user see an account if they don't have access to it? =

Sort of.  They will be able to see that the account exists, but they won't be able to see the password.  Even if they
figure out the ID number and stick it in the URL field manually it still won't show them the password.

= Can someone have write permissions without read permissions? =

No. If you grant write permissions to a user, they get read permissions automatically.

= Can someone have owner permissions without read or write permissions? =

No. If you grant owner permissions to a user, they get read and write permissions automatically.

= What takes priority, group or user permissions? =

Neither, they are merged.

= My site is behind a load balancer, and I've got the "Requires SSL" setting checked, but it isn't working. =

This is because the application is using the <a href="http://codex.wordpress.org/Function_Reference/is_ssl">is_ssl()</a> function within Wordpress
which isn't correctly handle load balancers.  For now it is recommended that you follow the directions in the is_ssl() 
document and add the "Force SSL URL Scheme" plugin to your site so that the site forces SSL.

= Why does auditing turn itself on every time I upgrade or activate the plugin? =

This is done as a security precaution.  Every time the plugin is activated it turns auditing back on if it is disabled.

== Screenshots ==

1. Adding a new account.
2. Searching for an account.
3. Editing an account.
4. Editing permissions on an account.
5. Main settings page.
6. Group Management settings page.
7. Group Membership settings page.


== Changelog ==

= 1.2.2 =
* Fixing upgrade code.

= 1.2 =
* Made auditing optional
* Logs when auditing is enabled and disabled
* Tightened up the code a little
* Added an additional security check to ensure user is logged in when using the application

= 1.1 =
* Cleaned up buttons
* Added audit viewing screen.
* Add option to require SSL for plugin use.  Settings page doesn't require SSL.
* Made custom labels optional or required.
* Code cleanup.

= 1.0 =
* User Defined Fields are added.
* Group Management.
* Group Membership.
* Encryption for passwords everywhere they are stored.
* User level permissions.
* Group permissions.


== Upgrade Notice ==


