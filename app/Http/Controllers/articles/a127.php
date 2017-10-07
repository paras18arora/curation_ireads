<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Joomla-Twitter3.png?1429200499/> <br> 
      <p>IndiaReads's Joomla One-Click application allows you to quickly spin up a robust content management system. Deployed on top of Apache and MySql, Joomla provides you with a solid base to build out your web properties. It is a versatile platform used for <a href="http://community.joomla.org/showcase/">everything from newspapers to web portals</a>.</p>

<h2 id="create-a-joomla-droplet">Create a Joomla! Droplet</h2>

<p>You can launch a new Joomla instance by selecting <strong>Joomla! on Ubuntu 14.04</strong> from the Applications menu during droplet creation:</p>

<p><img src="https://assets.digitalocean.com/articles/joomla/select-joomla.png" alt="Select Joomla" /></p>

<h2 id="access-your-joomla-credentials">Access Your Joomla! Credentials</h2>

<p>Once your server has been spun up, you will be able to access the Joomla installation from your IP address. However, in order to retrieve the login credentials for the admin account, you will need to access the server via the command line. </p>

<p>You can log into your droplet with the following command or use the web console in the IndiaReads control panel:</p>
<pre class="code-pre "><code langs="">ssh root@<span class="highlight">your_ip_address</span>
</code></pre>
<p>Once you are logged in, you will see the message of the day (MOTD) which contains your username and password. It will look like this:</p>
<pre class="code-pre "><code langs="">-----------------------------------------------------------------------------------
Thank you for using IndiaReads's Joomla! Application.

You can log in to your Joomla! instance with the following credentials:

  * Username: admin
  * Password: 8Wtd5FqJmHF7

The control panel is available at http://111.111.111.11/administrator/ 
-----------------------------------------------------------------------------------
</code></pre>
<p>Now that you have your login credentials, you can visit your Joomla site by entering its IP address in your browser.</p>

<p><img src="https://assets.digitalocean.com/articles/joomla/homepage.png" alt="" /></p>

<h2 id="setting-up-your-account">Setting Up Your Account</h2>

<p>As a first step, you will want to change the details of the administrator account including your contact address, username, and password. In the control panel, select <strong>User Manager</strong>. Then select the <strong>Admin User</strong> from the user list.</p>

<p><img src="https://assets.digitalocean.com/articles/joomla/edit-user.gif" alt="" /></p>

<h2 id="publishing-your-first-article">Publishing Your First Article</h2>

<p>Adding new content is simple. In the control panel, select <strong>Add New Article</strong>. This will open the editor window. After composing your post, select <strong>Save & Close</strong>. Your new article is now published. In order to make it accessible directly from your homepage, toggle its "featured" status by clicking the star next to it on the article list.</p>

<p><img src="https://assets.digitalocean.com/articles/joomla/first-post.gif" alt="" /></p>

<h2 id="configuration-details">Configuration Details</h2>

<p>The Joomla One-Click application is built on top of a standard LAMP (Linux, Apache, MySql, and PHP) stack. The Apache configuration is located in:</p>
<pre class="code-pre "><code langs="">/etc/apache2/sites-enabled/000-joomla.conf
</code></pre>
<p>Joomla specific log files can be found in <code>/var/log/joomla/</code> while the Apache logs are located in their standard location in <code>/var/log/apache2/</code></p>

<h2 id="next-steps">Next Steps</h2>

<p>Joomla is a flexible platform allowing you to build many types of sites. There are <a href="http://extensions.joomla.org/">thousands of extensions</a> available and an <a href="http://community.joomla.org/">active user community</a>. Beyond configuring Joomla, there are some tasks that you should strongly consider doing on every server you administer. The following tutorials should point you in the right direction for setting up and securing you server:</p>

<ul>
<li><p><a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a></p></li>
<li><p><a href="https://indiareads/community/tutorials/7-security-measures-to-protect-your-servers">7 Security Measures to Protect your Servers</a></p></li>
</ul>

    