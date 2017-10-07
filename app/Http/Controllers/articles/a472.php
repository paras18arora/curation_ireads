<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ajenti_tw.jpg?1437677531/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://ajenti.org">Ajenti</a> is an open source, web-based control panel that can be used for a large variety of server management tasks. The add-on package called Ajenti V allows you to manage multiple websites from the same control panel. By now you should have Ajenti and Ajenti V installed. </p>

<p>In this tutorial we will setup a basic website using Ajenti V from the Ajenti control panel and create an email account on your website's domain.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you will need:</p>

<ul>
<li>A registered domain name that resolves to the Droplet with Ajenti and Ajenti V installed (<span class="highlight">example.com</span> is used throughout this tutorial)</li>
<li>Ajenti and Ajenti V installed from <a href="https://indiareads/community/tutorials/how-to-install-the-ajenti-control-panel-and-ajenti-v-on-ubuntu-14-04">How to Install the Ajenti Control Panel and Ajenti V on Ubuntu 14.04</a></li>
</ul>

<h2 id="configuring-your-domain">Configuring your Domain</h2>

<p>So far, <code>https://panel.<span class="highlight">your_domain_name</span>:8000/</code> opens the Ajenti control panel.  Before we can setup your website at <code>http://<span class="highlight">your_domain_name</span>/</code> and configure email addresses for your domain, there are a few DNS modifications that need to be made.</p>

<p>We need to add 2 records to make sure your website and email addresses work properly. Go to <a href="https://cloud.digitalocean.com/domains">cloud.digitalocean.com/domains/</a> and click on the blue <strong>View</strong> button (it looks like a magnifying glass) for the domain you configured when setting up the Ajenti control panel and Ajenti V.</p>

<p>Click the blue <strong>Add Record</strong> button, and select <strong>MX</strong>. In the <strong>Enter Hostname</strong> text box, enter <code>@</code>. In the <strong>Enter Priority</strong> text box, enter <code>10</code>. Then click the blue <strong>Create MX Record</strong> button.</p>

<p>If you are configuring the same domain that has the Ajenti control panel on it, you also need to add a TXT record. For instance, if you access Ajenti on panel.<span class="highlight">example.com</span> you should add this record. If you access it on panel.<span class="highlight">otherdomain.com</span>, you do not need to add the TXT record.</p>

<p>To add the TXT record, click the blue <strong>Add Record</strong> button again, and select <strong>TXT</strong>. In the <strong>Enter Name</strong> text box, enter <code>@</code>. In the <strong>Enter Text</strong> text box, paste this in: <code>v=spf1 a ip4:<span class="highlight">your_server_ip</span> ~all</code> (replacing the IP with your Droplet's IP). Click the <strong>Create TXT Record</strong> button.</p>

<p>Your domain DNS settings should now look like this. Note that the domain I used was <code>jonaharagon.me</code>. Your domain will vary.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-website-dns-settings.png" alt="Example DNS Settings" /></p>

<p>Your zone file will then look something like this:</p>
<pre class="code-pre "><code langs="">$ORIGIN <span class="highlight">example.com</span>.
$TTL 1800
<span class="highlight">example.com</span>. IN SOA ns1.digitalocean.com. hostmaster.<span class="highlight">example.com</span>. 1434177047 10800 3600 604800 1800
<span class="highlight">example.com</span>. 1800 IN NS ns1.digitalocean.com.
<span class="highlight">example.com</span>. 1800 IN NS ns2.digitalocean.com.
<span class="highlight">example.com</span>. 1800 IN NS ns3.digitalocean.com.
<span class="highlight">example.com</span>. 1800 IN A <span class="highlight">111.111.111.111</span>
<span class="highlight">example.com</span>. 1800 IN MX 10 <span class="highlight">example.com</span>.
<span class="highlight">example.com</span>. 1800 IN TXT v=spf1 a ip4:<span class="highlight">111.111.111.111</span> ~all
panel.<span class="highlight">example.com</span>. 1800 IN A <span class="highlight">111.111.111.111</span>
</code></pre>
<h2 id="creating-the-website-directory">Creating the Website Directory</h2>

<p>In your browser, browse to <code>https://panel.<span class="highlight">example.com</span>/</code> and log into Ajenti. In the sidebar to the right, under the <strong>Web</strong> section, click <strong>Websites</strong>. The first time it may give you a notice that it is not active yet. Click the <strong>Enable</strong> button to allow Ajenti V to make the necessary config changes.</p>

<p>There is a section called <strong>New Website</strong>. Under that there is a <strong>Name</strong> text field. You can type anything you want to identify your website with in this field. Click the <strong>Create</strong> button, and you will notice your website is now listed under the <strong>Websites</strong> section at the top of the page. Click <strong>Manage</strong> next to your website.</p>

<p>Under the <strong>Website Files</strong> section, change <code>/srv/new-website</code> to any directory, for example <code>/srv/<span class="highlight">example.com</span></code>. Click the <strong>Set</strong> button, and then click the <strong>Create Directory</strong> button. Remember this directory. You will need to upload files to it soon.</p>

<p>Under the <strong>General</strong> tab, uncheck the <strong>Maintenance mode</strong> setting. Click <strong>Apply changes</strong> at the bottom of the page.</p>

<p>At the top of the page click on the <strong>Domains</strong> tab. Click the <strong>Add</strong> button, and type your domain name in the text field that appears. Click the <strong>Apply Changes</strong> button.</p>

<h2 id="creating-uploading-the-website-files">Creating/Uploading the Website Files</h2>

<p>Now that you have a directory for your website files, you need some files to go in it. </p>

<p>Under the <strong>Tools</strong> section in the sidebar, click <strong>File Manager</strong>. Click on the folder names to navigate to the directory you created for the website files such as <code>/srv/<span class="highlight">example.com</span></code>. The folder should be empty. Here you can upload whatever files and folders you would like for your static website. </p>

<p>For the purposes of this tutorial, we are going to make a simple "Hello world!" document on the website. Click the <strong>New File</strong> button at the top of the screen. A file named <code>new file</code> should appear in the folder. At the end of the line for the file, click the menu button.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-website-menu-button.png" alt="Click the Menu Button" /></p>

<p>In the <strong>Name</strong> field, change <code>new file</code> to <code>index.html</code>. Click <strong>Save</strong>, and open that same menu again. This time, click the <strong>Edit</strong> button to open a Notepad. The following text is an example of what you could enter to ensure your website is working. Of course, you can enter anything you'd like here instead.</p>
<div class="code-label " title="index.html">index.html</div><pre class="code-pre "><code langs=""><!DOCTYPE html>
<html>
<head>
  <title>This website is working!</title>
</head>
<body>
<h1>Hello, world!</h1>
<p>If you can read this correctly, your website is functional!</p>
</body>
</html>
</code></pre>
<p>Click <strong>Save</strong> at the top of the Notepad, and the file will go live.</p>

<p>Repeat these steps as needed to build your website. You can also make use of the File Manager's upload function. Back in <strong>File Manager</strong>, towards the bottom of the screen there is a button titled <strong>Choose File</strong>. You can click that and a normal prompt to choose a file from your computer to upload will appear. From here, any files you select will be uploaded to the site.</p>

<h2 id="browse-to-your-website">Browse to Your Website</h2>

<p>Browse to your domain name such as <code>http://<span class="highlight">example.com</span></code> in your web browser. If you see the "Hello World!" page you made in the last step, everything works! </p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-website-helloworld.png" alt="Hello, world!" /></p>

<p>You can choose to finish now or read on to create your own email account for your own domain.</p>

<h2 id="creating-an-email-account">Creating an Email Account</h2>

<p>Now we will make an email account with your registered domain name. </p>

<p>Go to your Ajenti Control Panel. Under the <strong>Web</strong> section, click <strong>Mail</strong>. Click the <strong>Enable</strong> button if prompted. Under <strong>New Mailbox</strong> there are a few sections to fill out. The <strong>Address</strong> text box is whatever you want to come before the @ sign in your email address. If you enter <code>sammy</code> here, your email will be <code>sammy@<span class="highlight">example.com</span></code>. There is also a dropdown box to select a domain. If you have more than one website configured with Ajenti V, there will be multiple options here. Don't put anything in the <strong>Custom domain</strong> field. After you've filled out this information, click <strong>+ Mailbox</strong>.</p>

<p>As you can see, your new email address now appears under the <strong>Mailboxes</strong> section of this page. Click it, and then click the <strong>Change password</strong> link. Type a new password for your mailbox, press <strong>ENTER</strong>, and then click <strong>Apply Changes</strong> at the bottom of the screen.</p>

<p>Now click the <strong>Advanced</strong> tab at the top of the page. In the <strong>TLS</strong> section, check the box next to enable, then click the <strong>Generate new certificate</strong> button. After you're done, click <strong>Apply changes</strong> at the bottom of the page. This increases security when connecting to your mailbox and increases compatibility with email clients.</p>

<p>To retrieve your email, you can connect with an email client (like Outlook, Thunderbird, K-9 Mail, etc.) or you can install <a href="https://indiareads/community/tutorials/installing-the-rainloop-email-client-on-ajenti-v">RainLoop</a>, a webmail program for accessing your mail in a browser in Ajenti.</p>

<p>To connect to this mailbox in an email client, the following information should be useful:</p>
<pre class="code-pre "><code langs="">Username: <span class="highlight">user</span>@<span class="highlight">example.com</span>
Password: <span class="highlight">your_mailbox_password</span>
IMAP Server: panel.<span class="highlight">example.com</span>
IMAP Port: 143
IMAP Encryption: STARTTLS (Accept all certificates)
SMTP Server: panel.<span class="highlight">example.com</span>
SMTP Port: 25
SMTP Encryption: None
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You should now have a working email account and website for your domain name. They were both created within the Ajenti V Control Panel and can both be modified with the same control panel.</p>

<p>Check out <a href="https://indiareads/community/tutorials/installing-the-rainloop-email-client-on-ajenti-v">Installing the RainLoop Email Client on Ajenti V</a> if you want to learn how to install a web-based email client on your server.</p>

    