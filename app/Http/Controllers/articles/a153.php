<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>One way to send and receive email through your own custom domain is to use a third-party mail service, such as Zoho or Google Apps. The main benefit of using a third-party mail service, as opposed to managing your own mail server, is that you can avoid performing the ongoing maintenance that running a mail server entails. Of course, mail services for custom domains typically come with a fee, but Zoho offers a free mail service for up to 10 users on a single custom domain.</p>

<p>This tutorial will show you how to set up Zoho Mail with your custom domain that is managed by IndiaReads's Domain Name Servers. Also, we will show you how to use the mail setup to send mail from your applications using your custom domain.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before proceeding, you should own the domain name that you want to use as your mail domain. This guide also assumes that you are using the IndiaReads DNS to manage your domain.</p>

<p>If you do not already own a domain, you may purchase one from any of the various domain registrars. Once you have a domain to use, you may use this guide to set it up under the IndiaReads DNS:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How to Point to IndiaReads Nameservers From Common Domain Registrars</a></li>
</ul>

<p>You may also want to point the domain to one of your droplets, such as one that hosts your web server. This link will help you set that up:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">How To Set Up a Host Name with IndiaReads</a></li>
</ul>

<p>Once you have a domain, and it's managed by IndiaReads's DNS, you are ready to proceed!</p>

<h2 id="sign-up">Sign Up</h2>

<p>Before using Zoho, you must register with them here: <a href="https://www.zoho.com/signup.html">Sign up</a>. You may register with an external email address or with a variety of OAuth options, such as Google Apps, Facebook, etc.</p>

<p>Once you are registered, ensure that you are signed in, then continue to choose your mail service plan.</p>

<h2 id="choose-plan">Choose Plan</h2>

<p>After you are signed in, you will be taken to the Zoho home page. From here, click on the <strong>Mail</strong> link.</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/home.png" alt="Zoho home page" /></p>

<p>Ensure that the <strong>Add your existing domain</strong> option is selected. Then select the desired plan.</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/plan.png" alt="Zoho plans" /></p>

<p>For this tutorial, we will select the <strong>Lite</strong> plan, which is free and provides up to 10 mailboxes. If you have the need for more than 10 mailboxes, or if you need more storage, feel free to select one of the priced plans.</p>

<p>After selecting your desired plan, you must provide the domain name that you want to use with the mail service:</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/domain.png" alt="Domain name" /></p>

<p>After providing your domain name, click the <strong>Add Domain</strong> button.</p>

<p>Next, click on the <strong>Proceed to verify domain ownership</strong> link. At this point, you are ready to verify your ownership of your domain to Zoho.</p>

<h2 id="verify-your-domain">Verify Your Domain</h2>

<p>Zoho provides three different ways to verify the ownership of your domain. We will use the <strong>CNAME Method</strong> which involves creating a specific CNAME record in your domain's DNS record.</p>

<p>At this point, you should be at the <strong>Domain Setup</strong> screen on the Zoho site, which should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/cname-info.png" alt="CNAME Info" /></p>

<p>Take a note of the <strong>CNAME</strong> and <strong>Destination</strong> items, as the next step is to create a CNAME record based on those values in the IndiaReads DNS manager.</p>

<p>In a separate browser tab or window, go to the <a href="https://cloud.digitalocean.com/domains">Networking section of the IndiaReads Control Panel</a>. Click on the <strong>View</strong> icon (blue magnifying glass), to the right of the domain in question:</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/dns.png" alt="IndiaReads DNS" /></p>

<p>Next, click the <strong>Add Record</strong> button, near the top of the page. Select the <strong>CNAME</strong> record type.</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/cname-entry.png" alt="Add CNAME Record" /></p>

<p>Next, paste the <strong>CNAME</strong> provided by Zoho (which looks something like <code>zb14217849</code>) into the <strong>Enter Name</strong> field. Paste the <strong>Destination</strong> provided by Zoho into the <strong>Enter Hostname</strong> field, and add a dot to the end of it (i.e. <code>zmverify.zoho.com.</code>). Now click the green <strong>Create</strong> button.</p>

<p>Now go back to the Zoho <strong>Domain Setup</strong> page, and click the <strong>Verify</strong> button at the bottom.</p>

<p>Click the <strong>Proceed</strong> button at the prompt:</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/proceed.png" alt="Proceed" /></p>

<p>If you entered the CNAME record properly, you should see the following message:</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/create-admin.png" alt="Success! Create Admin email account" /></p>

<p>After you have verified ownership of your domain, you may delete the CNAME record that you created via the IndiaReads DNS manager. Click the red <strong>X</strong> to the right of the record and click the <strong>OK</strong> button in the confirmation prompt.</p>

<p>Enter your desired username for your administrator email account, then click the <strong>Create Account</strong> button.</p>

<h2 id="add-users-and-groups">Add Users and Groups</h2>

<p>At the <strong>Add Users</strong> page, we will click <strong>Next</strong> to proceed to the next step in the setup. This is assuming you only need one user or you will set up additional mail users later. If you wish to add users now, click the <strong>Proceed to Add Users</strong> link instead.</p>

<p>We will proceed with the <strong>Groups</strong> page in a similar manner, by clicking <strong>Next</strong>. If you wish to add groups now, click the <strong>Proceed to Create Groups</strong> link instead.</p>

<h2 id="add-mx-records">Add MX Records</h2>

<p>Now you must add the Zoho MX records to your Domain's DNS record.</p>

<p>Go back to the IndiaReads DNS management page, and ensure you have your desired domain selected.</p>

<p>Click the <strong>Add Record</strong> button, then select the <strong>MX</strong> record type. Next, enter <code>mx.zoho.com.</code> (with a dot at the end) into the <strong>Enter Hostname</strong> field, and <code>10</code> into the <strong>Enter Priority</strong> field, then click the <strong>Create</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/mx.png" alt="MX Record" /></p>

<p>To add the second MX record, follow the previous step but enter <code>mx2.zoho.com.</code> into the <strong>Enter Hostname</strong> field, and <code>20</code> into the <strong>Enter Priority</strong> field.</p>

<p>Then add the third MX record, <code>mx3.zoho.com.</code>with a priority of `50.</p>

<p>Your Zoho mail with a custom domain setup is now complete! Feel free to proceed with the rest of steps that Zoho provides to perform email migration or set up mobile access.</p>

<h2 id="use-zoho-mail-to-send-email-from-application">Use Zoho Mail to Send Email from Application</h2>

<p>If you want to use your new mail setup to send mail from one of your applications, using <strong>SMTP</strong> (Simple Mail Transfer Protocol), it is very easy to set up.</p>

<p>Typically, you will want to create a new mail user for this purpose, so we'll do that now.</p>

<h3 id="create-mail-user-for-application">Create Mail User for Application</h3>

<p>In a web browser, logged in as your admin mail user, go to the <a href="https://mail.zoho.com/cpanel/index.do">Zoho Mail  Control Panel</a>.</p>

<p>Click <strong>User Details</strong> in the navigation menu (left side), then click <strong>Add User</strong> (right side).</p>

<p>Create a user with your desired first name, last name, email address, and password:</p>

<p><img src="https://assets.digitalocean.com/articles/zoho/add_user.png" alt="Add user" /></p>

<p>For our example, we will use the following details:</p>

<ul>
<li><strong>First Name</strong>: Application</li>
<li><strong>Last Name</strong>: Mail</li>
<li><strong>Mail ID</strong>: application</li>
</ul>

<p>With a Mail ID of "application", and "example.com" as our domain name, the email address will be "application@example.com". Take note of these user details (including the password), as you will need them when you configure the mail settings in your application.</p>

<h3 id="configure-your-application-39-s-mail-settings">Configure Your Application's Mail Settings</h3>

<p>The actual configuration of your application's mail settings will vary, depending on the software, but the details that you will need to set up working outgoing mail are generally the same.</p>

<p>With this Zoho setup, you will need to specify the following details when configuring your application to send email. Obviously, some of your details will differ from the example, so substitute them as appropriate:</p>

<ul>
<li><strong>SMTP Host</strong>: <code>smtp.zoho.com</code></li>
<li><strong>SMTP Port</strong>: <code>465</code></li>
<li><strong>Use SSL Encryption</strong>: Yes, use SSL encryption</li>
<li><strong>SMTP Authentication</strong>: Yes, use SMTP authentication</li>
<li><strong>Email Address or Username</strong>: The email address that you set up in the previous step. In our example, the email address is "application@example.com"</li>
<li><strong>Email Name</strong>: The name associated with the email address. In our example, the name of the email is "Application Mail"</li>
<li><strong>Email Password</strong>: The password that you set when you created the application mail account</li>
</ul>

<p>Once you configure your application with these settings, your application should be able to send mail through the new user under your custom domain.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You now should be able to send and receive email with your custom domain—test it out by sending and receiving mail in the Zoho mail app, as the admin mail user that you created. Now is a good time to add additional mail users and groups, if you need them. You may access your mail accounts through <a href="https://mail.zoho.com/">Zoho Mail</a>, and you may perform mail administration through the <a href="https://mail.zoho.com/cpanel/index.do">Zoho Control Panel</a>.</p>

<p>Good luck!</p>

    