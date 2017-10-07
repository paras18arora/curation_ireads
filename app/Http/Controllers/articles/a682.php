<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/discourse.png?1443797112/> <br> 
      <h2 id="introduction">Introduction</h2>

<p><a href="http://discourse.org">Discourse</a> is a popular piece of discussion forum software written in Ruby on Rails.  Discourse provides a number of built in improvements over previous generation forum software including mobile support, oAuth authentication options, a user trust system, notifications and many other features.  </p>

<p>IndiaReads's Discourse one-click application image provides an easy way to get started with a Discourse forum.  This tutorial will walk you through creating and setting up a new Discourse droplet.</p>

<h2 id="included-components">Included Components</h2>

<p>Based on Ubuntu 14.04, the Discourse One-Click Application Image uses the official Discourse Docker container to provide all the components required to run Discourse which includes</p>

<ul>
<li>Ruby on Rails</li>
<li>Redis</li>
<li>Nginx Web Server</li>
<li>PostgreSQL Database Server</li>
</ul>

<h2 id="requirements">Requirements</h2>

<p>When launching your new Droplet you will be prompted for several pieces of information that are used to configure Discourse.  Before beginning you should have the following information available:</p>

<ul>
<li>An email address to use for the administrator account.</li>
<li>SMTP credentials to allow Discourse to send email</li>
<li>A hostname (domain or subdomain) for your forum.</li>
</ul>

<p><span class="note">You can find more information on setting up a hostname on your IndiaReads account <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">here</a>.<br /></span></p>

<p>The SMTP credentials you provide are critical for account creation and notifications in discourse. If you have an existing mail server you can provide those credentials, otherwise there are several providers where you can set up a free (but limited) SMTP account such as: <a href="https://www.sparkpost.com/">SparkPost</a> (10k emails/month) <a href="http://www.mailgun.com/">Mailgun</a> (10k emails/month), <a href="https://www.mailjet.com/pricing">Mailjet</a> (200 emails/day) or <a href="https://mandrillapp.com/">Mandrill</a>, and use the credentials provided in the dashboard.</p>

<h2 id="create-a-discourse-droplet">Create a Discourse Droplet</h2>

<p>To get started, log into the <a href="https://cloud.digitalocean.com">IndiaReads Control Panel</a>.</p>

<p>Then click the <strong>Create Droplet</strong> button.</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/cp_create.png" alt="" /></p>

<p>At the droplet creation page, specify your new droplet's hostname and select a droplet size.  Discourse requires a droplet with at least <strong>2GB</strong> of RAM.</p>

<p><img src="https://assets.digitalocean.com/articles/discourse-1click/create1.png" alt="" /></p>

<p>Select your desired region</p>

<p><img src="https://assets.digitalocean.com/articles/discourse-1click/region.png" alt="" /></p>

<p>Now, in the <strong>Select Image</strong> section, click the <strong>Applications</strong> tab and choose the <strong>Discourse on 14.04</strong> image:</p>

<p>Next, select any additional settings, such as private networking, IPv6, or backups.</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/cp_create_settings.png" alt="" /></p>

<p>Finally, select which SSH keys, if any, you want to use for accessing your new Droplet, and click the <strong>Create Droplet</strong> button.</p>

<p>Your Discourse droplet will now be created.  For more details about creating Droplets, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-create-your-first-digitalocean-droplet-virtual-server">How to Create your First IndiaReads Droplet</a>.</p>

<h2 id="access-your-new-droplet">Access your new Droplet</h2>

<p>Before you begin using your new Discourse forum you will first need to log into your droplet via SSH in order to complete the setup.</p>

<p><span class="note">If you haven't used SSH or PuTTY before, you may want to refer to this tutorial for more details: <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">How To Connect To Your Droplet with SSH</a>.<br /></span></p>

<p>On your computer, open a terminal and log into your droplet as <code>root</code> with this command (substitute your droplet's IP address):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh root@droplet.ip.address
</li></ul></code></pre>
<p>If you are prompted for a password, enter the password that was emailed to you when the Droplet was created. Alternatively, if you set up the Droplet with SSH keys, the keys will be used for authentication instead.</p>

<p>Once you are connected to your droplet via SSH you will be prompted to complete the configuration of your new Discourse forum.</p>

<p><img src="https://assets.digitalocean.com/articles/discourse-1click/config1.png" alt="" /></p>

<p>Once you provide these details, Discourse will be configured and you can visit your Droplet's IP or hostname via a web browser.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have your new Discourse forum up and running you may want to learn about ways to customize or extend it.  The official discourse forums include a <a href="https://meta.discourse.org/c/howto">howto section</a> with lots of great ways to get the most out of your Discourse installation.</p>

    