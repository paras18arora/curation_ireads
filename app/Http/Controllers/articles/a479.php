<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/DO_GmailDomain_Twitter_01.png?1426699803/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>One way to send and receive email through your own custom domain is to use a third-party mail service, such as the mail service included with Google Apps (Gmail) or Zoho. The main benefit of using a third-party mail service, as opposed to managing your own mail server, is that you can avoid performing the ongoing maintenance that running a mail server entails. The biggest trade off with using Google Apps is that it has a monthly fee of $5-$10 a month per user.</p>

<p>This tutorial will show you how to set up Gmail with your own domain that is managed by IndiaReads's Domain Name Servers. Also, we will show you how to use the Google Apps mail setup to send mail from your applications using your custom domain.</p>

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

<p>In a web browser, go to the <a href="https://www.google.com/a/signup/">Google Apps Signup page</a>.</p>

<p>For the first step, you must enter information about your business. Fill in the forms then click the <strong>Continue</strong> button.</p>

<p>In step two, select the <strong>Use a domain I already own</strong> option, and enter your domain name into the input box. Click the <strong>Continue</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/step2.png" alt="Step 2" /></p>

<p>In step three, you will create a new Google Apps account by submitting details such as your name, username (which, with your domain name, will determine your email address), and password. After completing the form, and agreeing to the Google Apps agreement, click the <strong>Create your account</strong> button.</p>

<h3 id="billing">Billing</h3>

<p>At this point, you will have the opportunity to enter your billing information. For this tutorial, we will click the <strong>Set up billing later</strong>, as Google offers a free 30-day trial—you may also set up billing now, if you wish.</p>

<h2 id="verify-domain-ownership">Verify Domain Ownership</h2>

<p>Before setting up Gmail with your custom domain, Google must verify that you own the domain. </p>

<p>Under the <strong>Set up on your own</strong> box, click the <strong>Continue</strong> button.</p>

<p>Near the top of the Google Apps Admin console, click the <strong>Setup Google Apps</strong> button. Then click the <strong>Verify Domain</strong> button that appears.</p>

<p>You should be taken to the <strong>Domain Setup</strong> page. Click the <strong>Get started</strong> button that appears.</p>

<p>At the <strong>Verify your domain ownership</strong> step, click the <strong>Choose a different method</strong> dropdown and select <strong>Add a domain host record (TXT or CNAME)</strong>. You should see the following page:</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/txt_method.png" alt="TXT verification method" /></p>

<p>Tick the <strong>I have successfully logged in</strong> checkbox.</p>

<p>Now tick the <strong>I have opened the control panel for my domain</strong> checkbox.</p>

<p>You should now see a screen that looks like the following:</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/txt_value.png" alt="TXT Value" /></p>

<p>Copy the text under the <strong>Value / Answer / Destination</strong> header. This will be used to create a TXT record for your domain, in the IndiaReads DNS manager.</p>

<h3 id="create-txt-record">Create TXT Record</h3>

<p>In a different browser tab, log into the IndiaReads Control Panel and go to the <a href="https://cloud.digitalocean.com/domains">IndiaReads Networking page</a>. Click on the <strong>View</strong> icon (blue magnifying glass) next to your domain:</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/dns.png" alt="IndiaReads DNS" /></p>

<p>Next, click the <strong>Add Record</strong> button, near the top of the page. Select the <strong>TXT</strong> record type.</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/txt_entry.png" alt="Add TXT Record" /></p>

<p>Next, enter <code>@</code> into the <strong>Enter Name</strong> field, and paste the <strong>Value</strong> provided by Google into the <strong>Enter Text</strong> field. Now click the green <strong>Create</strong> button.</p>

<h3 id="initiate-verification-process">Initiate Verification Process</h3>

<p>Now go back to the Google Apps Admin Console browser tab, and tick the <strong>I have created the TXT record</strong> box.</p>

<p>Tick the <strong>I have saved the TXT record</strong> box, then click the <strong>Verify</strong> button.</p>

<p>You will see a page that says your domain ownership is processing. Once it is complete, you will see a message that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/verified.png" alt="Verified" /></p>

<p>Click the <strong>Continue Setup</strong> button to move on to the next step.</p>

<p><strong>Note:</strong> After you have verified ownership of your domain, you may delete the TXT record that you created via the IndiaReads DNS manager. Click the red <strong>X</strong> to the right of the record and click the <strong>OK</strong> button in the confirmation prompt.</p>

<h2 id="add-mx-records">Add MX Records</h2>

<p>At this point, you must log into the Google Admin Console using the credentials you created earlier.</p>

<p>Near the top of the admin console, you will see this:</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/create_accounts.png" alt="Create Accounts for your team" /></p>

<p>We will click the <strong>Next</strong> button to proceed to the next step in the setup. This is assuming that you only need one mail user or that you will set up additional users later.</p>

<p>Click the <strong>Set up Gmail</strong> button to go to the Domain Setup page.</p>

<p>At the Domain Setup page, click the <strong>Set up email</strong> button.</p>

<p>Next, tick the <strong>I have successfully logged in</strong> checkbox.</p>

<p>Tick the <strong>I have opened the control panel for my domain</strong> checkbox.</p>

<p>Now the Domain Setup page will ask you to create new MX records, and you will see this:</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/mx_information.png" alt="MX Information" /></p>

<p>Go back to the IndiaReads DNS management page, and ensure you have the desired domain selected.</p>

<p>Click the <strong>Add Record</strong> button, then select the <strong>MX</strong> record type.</p>

<p><img src="https://assets.digitalocean.com/articles/google_apps/add_gmail.png" alt="Add Gmail MX Records" /></p>

<p>Next, click the <strong>Add GMail MX Records</strong> button. This button will conveniently add all five Google MX records.</p>

<p>Go back to the Google Domain Setup. Tick the <strong>I have saved the MX records</strong> checkbox, then click the <strong>Verify</strong> button.</p>

<p>Once the MX records are verified, click the <strong>Continue Setup</strong> button.</p>

<p>Your Google Apps mail with a custom domain setup is now complete! Make sure to update your billing information if you want to continue using the service past the trial period.</p>

<h2 id="use-gmail-to-send-email-from-application">Use Gmail to Send Email from Application</h2>

<p>If you want to use your new mail setup to send mail from one of your application, using Gmail's SMTP (Simple Mail Transfer Protocol) server, it is very easy to set up.</p>

<p>You may want to create a new user for this purpose, or use the one that you created initially.</p>

<h3 id="configure-your-application-39-s-mail-settings">Configure Your Application's Mail Settings</h3>

<p>The actual configuration of your application's mail settings will vary, depending on the software, but the details that you will need to set up working outgoing mail are generally the same.</p>

<p>With this Gmail setup, you will need to specify the following details when configuring your application to send email. Obviously, some of your details will differ from the example, so substitute them as is appropriate:</p>

<ul>
<li><strong>SMTP Host</strong>: <code>smtp.gmail.com</code></li>
<li><strong>SMTP Port</strong>: <code>465</code></li>
<li><strong>Use SSL Encryption</strong>: Yes, use SSL encryption</li>
<li><strong>SMTP Authentication</strong>: Yes, use SMTP authentication</li>
<li><strong>Email Address or Username</strong>: The email address under your domain that you want to use</li>
<li><strong>Email Name</strong>: The name associated with the email address</li>
<li><strong>Email Password</strong>: The password associated with the email address</li>
</ul>

<p>Once you configure your application with these settings, your application should be able to send mail through custom domain.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now you should be able to send and receive mail with your custom domain—test it out by sending and receiving mail via your new Gmail account. Now is a good time to add additional mail users, and configure other mail settings, if you need to. You may access your mail accounts through <a href="https://mail.google.com">Gmail</a>, and you may perform Google Apps administration through the <a href="https://admin.google.com/">Google Apps Admin Console</a>.</p>

<p>Good luck!</p>

    