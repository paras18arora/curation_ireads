<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>RainLoop is a free email client that can access any IMAP/SMTP emails, including Ajenti V's built-in email. It supports multiple accounts, social logins (log in with Twitter, Facebook, etc.), two factor authentication, and more. RainLoop is a great program to install with Ajenti V because the Ajenti V platform itself has no default webmail.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop.png" alt="Screenshot of Rainloop" /></p>

<p>At the end of this tutorial we will have RainLoop installed on <code>mail.<span class="highlight">example.com</span></code> with IMAP/SMTP access to mailboxes on <code><span class="highlight">example.com</span></code>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<ul>
<li>Ajenti with Ajenti V installed (read <a href="https://indiareads/community/tutorials/how-to-install-the-ajenti-control-panel-and-ajenti-v-on-ubuntu-14-04">How to Install the Ajenti Control Panel and Ajenti V on Ubuntu 14.04</a>)</li>
<li>A registered domain name that resolves to the Droplet (<span class="highlight">example.com</span> is used throughout this tutorial)</li>
<li>A subdomain (<code>mail.<span class="highlight">example.com</span></code>) that resolves to the Droplet. (follow the directions for setting up an A record in <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">How To Set Up a Host Name with IndiaReads</a>)</li>
<li>An email mailbox set up in Ajenti (read <a href="https://indiareads/community/tutorials/creating-a-website-and-an-email-account-on-ajenti-v">Creating a Website and an Email Account on Ajentu V</a>)</li>
</ul>

<h2 id="step-1-—-creating-the-rainloop-website-in-ajenti-v">Step 1 — Creating the RainLoop Website in Ajenti V</h2>

<p>In your browser, browse to your Ajenti control panel (usually located at <code>https://panel.<span class="highlight">example.com</span>/</code>), and log in. In the sidebar to the right, under the <strong>Web</strong> section, click <strong>Websites</strong>. </p>

<p>Under the <strong>New Website</strong> section there is a <strong>Name</strong> text field. Type <code>RainLoop</code> and click the <strong>Create</strong> button. Under the <strong>Websites</strong> section on that same page, click <strong>Manage</strong> on the new <code>RainLoop</code> line. </p>

<p>On the page that appears, uncheck the box next to <strong>Maintenance mode</strong>. In the <strong>Website Files</strong> section below that, change <strong>Path</strong> from <code>/srv/new-website</code> to <code>/srv/RainLoop</code>. Press the <strong>Set</strong> button next to that text field. Then press the <strong>Create Directory</strong> button below that. Click <strong>Apply Changes</strong> at the bottom of the screen.</p>

<p>On the top of the page, click the <strong>Domains</strong> tab. Click <strong>Add</strong> and replace <code>example.com</code> with <code>mail.<span class="highlight">example.com</span></code>, replacing <span class="highlight">example.com</span> with your domain name. Click <strong>Apply Changes</strong> at the bottom of the screen.</p>

<p>RainLoop is PHP-based, so now we need to enable PHP for the RainLoop website we are creating. Click the <strong>Content</strong> tab. Change the dropdown box to <code>PHP FastCGI</code>, and click <strong>Create</strong>. </p>

<p>Now click the <strong>Advanced</strong> tab. In the <strong>Custom Configuration</strong> box, enter:</p>
<pre class="code-pre "><code langs="">location ^~ /data {
  deny all;
}
</code></pre>
<p>This addition denies web access to information stored in the <code>/data</code> directory.</p>

<p>Click <strong>Apply Changes</strong> at the bottom of the screen. Configuration should now be complete. The next step is to install RainLoop.</p>

<h2 id="step-2-—-installing-rainloop">Step 2 — Installing RainLoop</h2>

<p>In the Ajenti sidebar, under the <strong>Tools</strong> section, click <strong>Terminal</strong>. Click the <strong>New</strong> button at the top of the screen, then click the black box that appears.</p>

<p><span class="note"><strong>Note:</strong> All commands here are run as root.<br /></span></p>

<p>At the terminal prompt, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /srv/RainLoop
</li></ul></code></pre>
<p>Press <strong>ENTER</strong>. Then, type the following to install some software dependencies:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">apt-get install php5-cli php5-curl
</li></ul></code></pre>
<p>Press <strong>ENTER</strong> to start the installation process. Press <code>Y</code> if prompted. Finally, enter the following to install RainLoop:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget -qO- http://repository.rainloop.net/installer.php | php
</li></ul></code></pre>
<p>Press <strong>ENTER</strong>. This command extracts all RainLoop files and installs them on the server.</p>

<p>Now we should set the correct permissions for the files and make them owned by the correct user. Enter these commands in the same terminal, pressing <strong>ENTER</strong> after each one:</p>
<pre class="code-pre "><code langs="">find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chown -R www-data:www-data .
</code></pre>
<p>Now that all the files and directories are setup, you can press the <strong>X</strong> on the <strong>Terminal 0</strong> tab to leave the terminal.</p>

<h2 id="step-3-—-changing-the-admin-password">Step 3 — Changing the Admin Password</h2>

<p>Browse to <code>http://mail.<span class="highlight">example.com</span>/?admin</code> in your web browser, replacing <span class="highlight">example.com</span> with your domain name. The default username is <code>admin</code>, and the default password is <code>12345</code>. Log in with those credentials.</p>

<p>When you login for the first time, you will be greeted with a warning:</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-password-warning.png" alt="Warning shown in RainLoop after installation" /></p>

<p>The <strong>change</strong> word in the warning is a link. Click it, and change the admin password to make your installation more secure. Click <strong>Update Password</strong> to finish changing it. The button will turn green, signifying your change was accepted.</p>

<h2 id="step-4-—-setting-up-your-domain">Step 4 — Setting Up Your Domain</h2>

<p>In the sidebar of RainLoop, click <strong>Login</strong>. In the <strong>Default Domain</strong> text field, you should enter the domain that comes after the @ in your Ajenti V email (usually this is your registered domain name). Press <strong>ENTER</strong>, and a green check mark will appear temporarily, showing it's saved.</p>

<p>In the sidebar, now click <strong>Domains</strong>. There is a list of default email domains already added. This list includes commercial domains such as <code>gmail.com</code>, <code>outlook.com</code>, <code>qq.com</code>, and <code>yahoo.com</code>. If you have an account with one of those services and you would like to be able to check them within RainLoop, you can leave them. Otherwise, you can click the trash can icon next to them to remove them.  </p>

<p>Now, click <strong>Add Domain</strong> at the top of that page. In the <strong>Name</strong> field, enter your domain name such as <code><span class="highlight">example.com</span></code>. Under the <strong>IMAP</strong> section, below <strong>Server</strong>, enter <code>mail.<span class="highlight">your_domain_name</span></code>. Under <strong>Secure</strong>, use the dropdown to select <code>STARTTLS</code>. Now under the <strong>SMTP</strong> section, below <strong>Server</strong> enter <code>mail.<span class="highlight">your_domain_name</span></code>. </p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-add-domain.png" alt="Example configuration" /></p>

<p>At the bottom of this screen click the <strong>Test</strong> button. If it turns green, click the <strong>Add</strong> button at the bottom of the modal to add your domain. If it turns red, read the error messages to figure out why it can't connect.</p>

<h2 id="step-5-—-enabling-two-factor-authentication-optional">Step 5 — Enabling Two Factor Authentication (Optional)</h2>

<p>If you use a service like Google Authenticator or Authy, this section may be of interest to you. </p>

<p>Browse to the <strong>Security</strong> option in the sidebar. Check the box next to <strong>Allow 2-Step Verification</strong>. Optionally, you can check the box next to <strong>Enforce 2-Step Verification</strong> as well, but this isn't necessary unless you have multiple users using this and you want them all to be forced to use Two Factor Authentication.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-2fa.png" alt="Enabling 2FA" /></p>

<p>You will learn how to configure Two Factor Authentication later in this tutorial.</p>

<h2 id="step-6-—-logging-in-to-rainloop">Step 6 — Logging in to RainLoop</h2>

<p>Browse to <code>http://mail.<span class="highlight">your_domain_name</span></code>. Enter your entire email address (not just your username) in the first box and your email password in the next. This is not your admin login. Rather, it is the account information you specified when setting up your mailbox in Ajenti. Check the <strong>Remember Me</strong> box if you do not want to have to log in every time you visit RainLoop on your computer.</p>

<p>Once you login, you should be done! You can now read your email messages and compose. You can choose to finish here or continue with some optional settings.</p>

<h2 id="step-7-—-customizing-rainloop-optional">Step 7 — Customizing RainLoop (Optional)</h2>

<p>At the top of the page, click the dropdown button with the silhouette person icon.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-customizing.png" alt="Profile Menu" /></p>

<p>Click <strong>Settings</strong>. In the first screen that shows up, you will have the option to configure general details to your liking. You can choose between vertical and horizontal layouts, the default text editor, etc. </p>

<p>One option that may be useful is the Notifications options. If this is your primary email client, you might want to enable notifications upon email arrival. Just check both boxes under the <strong>Notifications</strong> section to start getting popups from your browser when you receive an email:</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-notifications.png" alt="Email notification popup" /></p>

<p>As you can see, it shows who sent the email and the subject line. Simply click it to read the full message in your browser. RainLoop will need to be open in a browser tab at all times for this to work.</p>

<h2 id="step-8-—-setting-up-two-factor-authentication-optional">Step 8 — Setting Up Two Factor Authentication (Optional)</h2>

<p>If you would like to set up Two Factor Authentication, follow these steps. Log into the RainLoop user interface at <code>http://mail.your_domain_name</code> and go to <strong>Settings</strong>. In  <strong>Settings</strong>, click <strong>Security</strong> in the sidebar. There should be a link titled <strong>Configurate 2-Step Authentication</strong>. Click it, and click the <strong>Activate</strong> button that appears. </p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-2fa2.png" alt="2FA Setup" /></p>

<p><span class="note"><strong>Note:</strong> The following screenshots are for Google Authenticator. The steps for your authentication app may vary.<br /></span></p>

<p>In Google Authenticator, under <strong>Manually Activate an Account</strong>, click <strong>Scan a barcode</strong>. You may be prompted to install a barcode scanner app. If so, install it and return to the app.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-scan-barcode.png" alt="Scan a Barcode" /></p>

<p>Now scan the QR code displayed on the webpage.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-scan-barcode2.png" alt="Scan the code" /></p>

<p>There should now be a six-digit code displayed on your phone. You will need this code to sign into your webmail. </p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-google-auth.png" alt="2FA on Phone" /></p>

<p>Back in the Two Factor Authentication modal on your computer, next to <strong>Enable 2-step verification</strong> at the top, there will be a link that says <strong>test</strong>. Click it, enter the 6-digit code on your phone, and press <strong>ENTER</strong>. If the button turns green, click the <strong>X</strong> on that window, and check the box next to <strong>Enable 2-Step verification</strong>. Then press <strong>Done</strong> at the bottom of the screen.</p>

<p>From now on, every time you sign into RainLoop, you will need to lookup the six-digit code in Google Authenticator and enter it along with your username and password combination.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-enter-auth-code.png" alt="2FA login" /></p>

<h2 id="writing-an-email">Writing an Email</h2>

<p>Back at the homepage of RainLoop, where you can view your emails, there is an icon of a paper airplane in the top left corner of the page. Simply click it and the compose email modal box will appear, allowing you to write an email.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-rainloop-write-email.png" alt="Compose email modal" /></p>

<p>When you finish, click <strong>Send</strong>, and your email will be sent to its recipient.</p>

<h2 id="conclusion">Conclusion</h2>

<p>RainLoop should now be installed on your server, granting easy email account access to your mailboxes from any web browser.</p>

    