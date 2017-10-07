<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/elixir-one-click.png?1451927188/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IndiaReads's Elixir one-click installation provides a convenient way to get your Elixir application up and running on a Ubuntu server. This tutorial will give you all the details you need to get your project off the ground.</p>

<p>In addition to what comes with a plain Ubuntu 14.04 Droplet, the Elixir one-click image includes the following components:</p>

<ul>
<li><strong>Elixir version 1.1</strong>: Elixir is a programming language that runs on the Erlang VM, and provides more extensibility on top of Erlang’s speed and dependability. </li>
<li><strong>Phoenix version 1.0</strong>: Phoenix is a MVC framework for Elixir that helps with the structure and usability of your app.</li>
<li><strong>PostgreSQL 9.4.4</strong>: PostgreSQL is a commonly used database server for production Elixir application deployments, and it stores your application’s data.</li>
<li><strong>NodeJS 4.1.1</strong>: NodeJS is a front-end JavaScript platform for when you need JavaScript’s flexibility in your application or site.</li>
<li><strong>Cowboy 1.0.3</strong>: Cowboy is a light-weight HTTP server built in Erlang that serves your application to users.</li>
</ul>

<p>This tutorial will cover how to create an Elixir Droplet, how the components are configured, where the login names and passwords can be found, and how to launch your application.</p>

<h2 id="creating-your-elixir-droplet">Creating Your Elixir Droplet</h2>

<p>We'll guide you through the steps to creating your own Elixir Droplet.</p>

<p>First, log in to the <a href="https://cloud.digitalocean.com/droplets">IndiaReads Control Panel</a>, then click the <strong>Create Droplet</strong> button.</p>

<p>On the Droplet creation page, specify your desired hostname and size. For a basic application that won't receive much traffic, a 1GB Droplet should be fine.</p>

<p><img src="https://assets.digitalocean.com/articles/Elixir_OneClick/dropletselect.png" alt="Droplet Hostname and Size" /></p>

<p>Select your desired region.</p>

<p><img src="https://assets.digitalocean.com/articles/Elixir_OneClick/region.png" alt="Select region and options" /></p>

<p>Now, in the <strong>Select Image</strong> section, click the <strong>Applications</strong> tab and select the <strong>Elixir on 14.04</strong> image:</p>

<p><img src="https://assets.digitalocean.com/site/ControlPanel/one-click.png" alt="Select Elixir One-Click Application Image" /></p>

<p>Next, select any additional settings, such as private networking, IPv6, or backups.</p>

<p>Finally, select which SSH keys, if any, you want to use for accessing this Droplet, and hit the <strong>Create Droplet</strong> button.</p>

<p>Your Elixir Droplet will be ready soon. For more details about creating Droplets, check out this tutorial: <a href="https://indiareads/community/tutorials/how-to-create-your-first-digitalocean-droplet-virtual-server">How To Create Your First IndiaReads Droplet</a>.</p>

<h2 id="starting-and-accessing-your-elixir-application">Starting and Accessing Your Elixir Application</h2>

<p>The one-click application ships with a placeholder Elixir application. You can start it by running these commands to move into the application directory, and run Phoenix on the Cowboy web server in the background:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd test
</li><li class="line" prefix="$">elixir --detached -S mix phoenix.server
</li></ul></code></pre>
<p>If you'd prefer to run this application in the foreground, run this command instead:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd test
</li><li class="line" prefix="$">mix phoenix.server
</li></ul></code></pre>
<p>You can then open <code>http://<span class="highlight">your_server_ip</span>:4000</code> in a web browser to verify that Elixir is running.</p>

<p><img src="https://assets.digitalocean.com/articles/Elixir_OneClick/default.png" alt="Default Elixir/Phoenix page" /></p>

<p>If you see the sample Phoenix page, you're ready to log into your server.</p>

<h3 id="accessing-your-elixir-instance-via-a-web-browser">Accessing Your Elixir Instance Via a Web Browser</h3>

<p>You can access your Elixir instance as a user of the application by visiting the Droplet's IP address in a web browser. Your Droplet's public IP address can be found in the <a href="https://cloud.digitalocean.com/droplets">IndiaReads Control Panel</a>.</p>

<p>Browse to <code>http://<span class="highlight">your_server_ip</span></code> in your browser of choice, or use a domain name if you have created a DNS record to connect your domain to your IP address. </p>

<h3 id="accessing-your-elixir-instance-via-ssh">Accessing Your Elixir Instance Via SSH</h3>

<p>To deploy your own Elixir application, you will need to connect to your Droplet  as <strong>root</strong> via SSH. On your computer, open a terminal and enter this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh root@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>If you are prompted for a password, enter the password that was emailed to you when the Droplet was created. Alternatively, if you set up the Droplet with SSH keys, the keys will be used for authentication instead.</p>

<p>If you haven't used SSH or PuTTY before, you may want to refer to this tutorial for more details: <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">How To Connect To Your Droplet with SSH</a>.</p>

<h2 id="locating-login-names-and-passwords">Locating Login Names and Passwords</h2>

<p>The Elixir one-click application comes preconfigured with the following:</p>

<ul>
<li>A system user named <strong>elixir</strong></li>
<li>A PostgreSQL database user also named <strong>elixir</strong></li>
</ul>

<p>The passwords for both of these logins are randomly generated, and can be found in the message of the day (MOTD) that will appear whenever you log into the server via SSH. The MOTD should look something like this:</p>
<div class="code-label " title="MOTD">MOTD</div><pre class="code-pre "><code langs="">-------------------------------------------------------------------------------------
Thank you for using IndiaReads's Elixir Application.
We have created a default Elixir application that can be seen from http://111.111.11.111/
-------------------------------------------------------------------------------------
You can use the following SFTP credentials to upload your webpages (using FileZilla/WinSCP/Rsync):
  * Host: 111.111.11.111
  * User: elixir
  * Pass: <span class="highlight">PzAaUykNL4</span>
-------------------------------------------------------------------------------------
You can use the following Postgres database credentials:
  * User: elixir
  * Pass: <span class="highlight">temq0AtHj7</span>
-------------------------------------------------------------------------------------
## Cowboy information goes here?
</code></pre>
<p>You can also view the MOTD in the file <code>/etc/motd.tail</code>.</p>

<h2 id="uploading-files">Uploading Files</h2>

<p>We recommend that you use <a href="https://indiareads/community/tutorials/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">SFTP (Secure FTP)</a>, SCP, or <a href="https://indiareads/community/tutorials/how-to-use-rsync-to-sync-local-and-remote-directories-on-a-vps">Rsync</a> to upload your files, as they all use encryption for transmission of data.</p>

<p>Windows users can upload files with WinSCP.  For all other operating systems (including Windows) you can use <a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">FileZilla</a> and Rsync.</p>

<p>For seasoned developers, we have an article on <a href="https://indiareads/community/articles/how-to-use-git-effectively">how to set up Git</a></p>

<p>Your SFTP credentials are provided in the MOTD, like the user passwords. These credentials are different and randomly generated for every Droplet.</p>

<h2 id="next-steps">Next Steps</h2>

<ul>
<li>Follow our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">Initial Server Setup guide</a> to give <code>sudo</code> privileges to your user, lock down root login, and take other steps to make your VPS ready for production.</li>
</ul>

    