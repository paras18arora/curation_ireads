<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The Vesta Control Panel is a free, open source website control panel with website, email, database, and DNS functionalities built in. By the end of this tutorial we will have Vesta installed and running on Ubuntu 14.04 with a working website and email account.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>The following are required to complete this tutorial:</p>

<p>This tutorial uses <span class="highlight">example.com</span> as the example hostname. Replace it with <em>your</em> domain name throughout this tutorial.</p>

<ul>
<li>An Ubuntu 14.04 Droplet</li>
<li>A registered domain name pointed to this Droplet. You can read <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">this series</a> on hostnames for more information.</li>
<li>An <strong>A record</strong> pointing <code><span class="highlight">example.com</span></code> to your Droplet's IP</li>
<li>An <strong>A record</strong> pointing <code><span class="highlight">ns1.example.com</span></code> to your Droplet's IP</li>
<li>An <strong>A record</strong> pointing <code><span class="highlight">ns2.example.com</span></code> to your Droplet's IP</li>
<li>An <strong>A record</strong> pointing <code><span class="highlight">panel.example.com</span></code> to your Droplet's IP</li>
<li>A <strong>CNAME record</strong> pointing <code>www.<span class="highlight">example.com</span></code> to <code><span class="highlight">example.com</span></code></li>
<li>Filezilla or another FTP client installed on your computer</li>
<li>A non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to set this up.)</li>
</ul>

<p>Unless otherwise specified, all the commands in this tutorial should be run as a non-root user with sudo access.</p>

<h2 id="step-1-—-installing-vesta">Step 1 — Installing Vesta</h2>

<p>The first step is to download the installation script. The installation script requires direct root access, so make sure you are the root user before executing the commands in this step.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -O http://vestacp.com/pub/vst-install.sh
</li></ul></code></pre>
<p>Then, as root, execute the installation script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bash vst-install.sh
</li></ul></code></pre>
<p>When asked if you want to proceed, enter <code>y</code>. You will then be asked to enter a valid email address, enter your email address and press <code>ENTER</code>. Now you will be asked to enter a hostname. This can be whatever you want, but generally it's a domain name, like <code>panel.<span class="highlight">example.com</span></code>. </p>

<p><span class="note"><strong>Note:</strong> Whatever domain name you enter when installing Vesta will be used for the URL of the Vesta control panel. For example, if you enter <code>panel.<span class="highlight">example.com</span></code>, https://panel.<span class="highlight">example.com</span>:8083 will be used to access Vesta. If you are using Vesta to setup a website for <code><span class="highlight">example.com</span></code>, <em>do not</em> use <code><span class="highlight">example.com</span></code> during the installation process. Use <code>panel.<span class="highlight">example.com</span></code> and then setup the <code><span class="highlight">example.com</span></code> website domain using the Vesta control panel.<br /></span></p>

<p>The installation process will begin. It claims to take 15 minutes but I've found it to be around 5 with SSD and Gigabit Internet speeds, like on IndiaReads Droplets.</p>

<p>This installation script will install the control panel and all its dependencies to your server. This includes:</p>

<ul>
<li>Nginx Web Server</li>
<li>Apache Web Server (as backend)</li>
<li>Bind DNS Server</li>
<li>Exim mail server</li>
<li>Dovecot POP3/IMAP Server</li>
<li>MySQL Database Server</li>
<li>Vsftpd FTP Server</li>
<li>Iptables Firewall + Fail2Ban</li>
<li>Roundcube mail client</li>
</ul>

<p>It will also change your hostname to whatever hostname you entered at the beginning, however it will not change the hostname in your IndiaReads control panel. I recommend you change that hostname as well for Pointer DNS records to match your domain, which will at the very least help emails sent from your server not to get sent to spam.</p>

<p>After the script finishes its work you'll have some information displayed on your screen, which will look a bit like this:</p>
<pre class="code-pre "><code langs="">=======================================================

 _|      _|  _|_|_|_|    _|_|_|  _|_|_|_|_|    _|_|   
 _|      _|  _|        _|            _|      _|    _| 
 _|      _|  _|_|_|      _|_|        _|      _|_|_|_| 
   _|  _|    _|              _|      _|      _|    _| 
     _|      _|_|_|_|  _|_|_|        _|      _|    _| 


Congratulations, you have just successfully installed Vesta Control Panel

    https://panel.<span class="highlight">example.com</span>:8083
    username: admin
    password: <span class="highlight">v6qyJwSfSj</span>
</code></pre>
<p>This should conclude basic installation of your control panel. We can now continue to the web panel.</p>

<p>You no longer need to be logged in as the root user. Go back to your non-root sudo user now. For example:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">su - <span class="highlight">sammy</span>
</li></ul></code></pre>
<h2 id="step-2-—-setting-up-vesta">Step 2 — Setting up Vesta</h2>

<p>Now we will set up your Vesta control panel. Go to the URL given to you at the end of the install. In my case it was <code>https://panel.<span class="highlight">example.com</span>:8083/</code>, but yours will vary based on the hostname you entered at the beginning. You will get an SSL warning, like shown below:</p>

<p><img src="https://assets.digitalocean.com/articles/vestacp/vestacp-ssl-warning.png" alt="SSL Warning" /></p>

<p>This is completely normal because it is using a self-signed certificate. It is completely safe to continue. Click to proceed anyway. The exact steps vary by web browser. For Chrome, click <code>Advanced</code> and then click <code>Proceed</code>. Once you're at the login screen, enter the two credentials displayed in the server console after the installation finished. These credentials were also emailed to you using the email you entered at the beginning of the install.</p>

<p><img src="https://assets.digitalocean.com/articles/vestacp/vestacp-homepage.png" alt="Vesta Homepage" /></p>

<p>The first thing we'll do is change the admin user password. In the top right-hand corner of the web panel click the <strong>admin</strong> link:</p>

<p>In the <strong>Password</strong> field, enter any password you'd like, or click <strong>Generate</strong> to make Vesta generate a secure password for you. </p>

<p>While you're on this screen, you can optionally change other settings as well such as name and language. Additionally, at the bottom of the screen, you should set Nameservers for your server. These will be subdomains of your own domain, and you will point future domains you want to set up on Vesta to them. Generally you would choose <code>ns1.<span class="highlight">example.com</span></code>, and <code>ns2.<span class="highlight">example.com</span></code>. </p>

<p>Press <strong>Save</strong> at the bottom of the page when you're finished.</p>

<h2 id="step-3-—-setting-up-a-website">Step 3 — Setting up a Website</h2>

<p>Now we can set up your first website. On the homepage of Vesta, click <strong>WEB</strong> at the top.</p>

<p>Then click the green <strong>+</strong> button. In the <strong>Domain</strong> field on the next screen, enter the domain you'd like your website to be accessible from, or the one you registered to point to this Droplet's IP address such as <code><span class="highlight">example.com</span></code>. Also in some situations you may have multiple IP addresses under the <strong>IP Address</strong> dropdown, usually if you have Private Networking enabled. Make sure the IP address listed is your public IP address for your Droplet. Now click the <strong>Advanced Options</strong> link. Under <strong>Aliases</strong> enter any subdomains you also want this website to be accessible from, such as <code>www.<span class="highlight">example.com</span></code>. You can also choose <em>webalizer</em> as a statistics option under <strong>Web Statistics</strong> for server side analytics. This option will give you accurate analytics for your website. </p>

<p>You should also choose <strong>Additional FTP</strong> so you can easily upload files to your hosting. Enter a <strong>Username</strong> and a <strong>Password</strong> in their respective fields. Note that whatever you enter in the username field will have <code>admin_</code> added as a prefix (entering <span class="highlight">example</span> will result in admin_<span class="highlight">example</span>).</p>

<p>Be sure to click <strong>Add</strong> at the bottom of the page after making any configurations you'd like.</p>

<p><span class="note"><strong>Note:</strong> FTP connections are not encrypted. The username, password, and any files sent over an FTP connection can be intercepted and read. Use a unique password and do not send sensitive files over this connection.<br /></span></p>

<p>On your computer, you now need to connect via FTP to your Droplet:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ftp <span class="highlight">your_droplet_ip</span>
</li></ul></code></pre>
<p>Alternatively, you can use a program such as Filezilla to connect to your website via FTP.</p>

<p>There will be a bunch of files in the directory, but we only need to worry about the <code>public_html</code> directory. That's where all the files that are web accessible are stored. You can edit the <code>index.html</code> file to whatever you'd like, or upload your own. Anything uploaded will be instantly available at <code><span class="highlight">example.com</span></code>. Be warned, any files you upload with the same filename will overwrite existing files on your server. Otherwise, by default, your website landing page will show up like this:</p>

<p><img src="https://assets.digitalocean.com/articles/vestacp/vestacp-default-page.png" alt="example.com" /></p>

<p>Try visiting <code>http://<span class="highlight">example.com</span></code> now to make sure it works.</p>

<p>If you want to make changes to your domain later, click *<em>WEB</em> at the top of the Vesta control panel. You will see the domain you just created and the domain name for the Vesta control panel, such as <span class="highlight">panel.example.com</span>.</p>

<h2 id="step-4-—-setting-up-an-email-account">Step 4 — Setting Up an Email Account</h2>

<p>Now we can set up an email account, something personalized like <code><span class="highlight">username</span>@<span class="highlight">example.com</span></code>. In Vesta, click <strong>MAIL</strong> at the top of the screen. On the mail screen hover over the domain you'd like your email on and click <strong>ADD ACCOUNT</strong> when the button shows up. On the following screen, enter a username in the <strong>Account</strong> field and a password for the account in the <strong>Password</strong> field. You can press <strong>Add</strong> now or check out the <strong>Advanced Options</strong>. In those options you have three fields.</p>

<ul>
<li><p><strong>Quota</strong> allows you to set a mailbox size limit. This is useful if you want to conserve disk space or you're making an account for another user. You can press the infinity symbol also to give it 'unlimited' storage.</p></li>
<li><p><strong>Aliases</strong> allows you to add other email addresses that forward to that main account.</p></li>
<li><p><strong>Forward to</strong> allows you to enter an email address to forward all this email to. For instance if you have an email account on another service and you want to keep your emails there, you can enter that email, so emails from <code><span class="highlight">username@example.com</span></code> go to <code><span class="highlight">username@emailservice.net</span></code>. If you use this option, it might be good to check the <strong>Do not store forwarded email</strong> checkbox as well, to make sure storage isn't wasted on your server.</p></li>
</ul>

<p>The email you just set up can be easily accessed from <code>http://panel.<span class="highlight">example.com</span>/webmail/</code>. Simply login on that screen with the username and password you just set up. It's important to note you need to include the domain in the <strong>Username</strong> field. If your account name was <code>hello</code> you should enter <code>hello@<span class="highlight">example.com</span></code>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations, you now have a fully functioning web and email server installed on your Droplet. You can repeat Steps 3 and 4 to add more websites and emails. Also check out the <a href="https://vestacp.com/docs/">Vesta documentation</a> if you have any issues. Or if you need further help, ask a question at IndiaReads's great <a href="https://indiareads/community/questions">Community Q/A center</a>.</p>

    