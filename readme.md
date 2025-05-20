# Bulk Password Reset

A simple WordPress plugin that allows administrators to bulk-send password reset emails to selected users by role.  

---

## Description

**Bulk Password Reset** adds a new page under **Users > Bulk Password Reset** where you can:

- Select a user role (e.g., Subscriber, Contributor, Author, Editor, Administrator).  
- Filter all users of that role (up to 200 in this example, but adjustable).  
- Check off specific users (or select them all at once).  
- Customize the email subject and body, using placeholders like `{username}` and `{reset_link}` for personalization.  
- Send out secure password reset emails in bulk.

A column labeled **Last Password Reset Sent** also displays the timestamp of the last time each user received a reset email via this tool.

---

## Installation

1. **Download or Clone** the plugin files.  
2. Place the entire `bulk-password-reset` folder into your `wp-content/plugins/` directory (so the main plugin file is at `wp-content/plugins/bulk-password-reset/bulk-password-reset.php`).  
3. In the WordPress admin, go to **Plugins** and find **Bulk Password Reset**.  
4. Click **Activate**.

---

## Usage

1. Go to **Users > Bulk Password Reset** in your WordPress admin menu.  
2. Select the role you wish to filter by (e.g., *Subscriber*).  
3. Click the **Filter Users** button.  
4. Once the user list for that role appears:  
   - You can select specific users by checking the box next to each username, or use the “Select All” box in the header to select everyone.  
5. Scroll down to **Customize Email**.  
   - Enter a subject line for the email in **Subject**.  
   - In **Body**, you can customize the message.  
   - Use the placeholders:  
     - `{username}` will be replaced by the user’s login name.  
     - `{reset_link}` will be replaced by the unique password reset link.  
6. Click **Send Reset Links** to bulk-send the emails.  
7. The page will refresh, and you’ll see a success message. Each user’s **Last Password Reset Sent** timestamp will be updated.

---

## Notes & Tips

- The email includes a link to WordPress’s default password reset screen (`wp-login.php?action=rp...`).  
- Only administrators (or users with the `manage_options` capability) can access **Users > Bulk Password Reset**.  
- The plugin uses WordPress’s native `get_password_reset_key()` function to generate secure reset links, so it follows the same security protocols as the built-in lost-password flow.  
- If you need to reset more than 200 users at once, you can change the `'number' => 200,` argument in the plugin code to a higher value or implement pagination.  
- You can further customize or style the plugin to match your site’s needs.

---

## Frequently Asked Questions

**Q:** *Will this overwrite users’ existing passwords?*  
**A:** No. This only sends out a reset link. Each user must click the link and choose a new password.

**Q:** *Why am I seeing “Not sent yet” in the Last Password Reset Sent column?*  
**A:** This indicates that no email has been sent yet through this plugin. Once an email is sent, the timestamp will appear.

**Q:** *Do I need to configure SMTP or anything else for sending emails?*  
**A:** The plugin uses WordPress’s `wp_mail()` function. If you have issues with emails not sending, use an SMTP plugin or ensure your hosting environment is set up to send email successfully.

---

## Changelog

**1.0.0**
- Initial release.  
- Built by Darren Kandekore on behalf of Impression Communications

---

## Support

If you encounter any issues:
1. Confirm you’re running the latest version of WordPress.  
2. Check your server’s email deliverability or configure an SMTP plugin.  
3. Verify you have the correct user capabilities (must be an admin).  

**Thank you for using Bulk Password Reset!**
