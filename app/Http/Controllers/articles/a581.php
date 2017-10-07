<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will install <a href="https://mailpile.is">Mailpile</a>, a fast, secure, and beautiful webmail client, on Ubuntu 14.04.</p>

<p><img src="https://assets.digitalocean.com/articles/mailpile/mailpile-home.png" alt="Mailpile’s Initial Launch Screen" /></p>

<p>A webmail client like Mailpile is a great way to ensure you can access your email from anywhere without the hassle of configuring a standard email client. Mailpile is just a mail client, meaning it only <em>manages</em> existing mail accounts.</p>

<p>By the end of this tutorial, you will have a fully-functional Droplet running Mailpile with Nginx as a reverse proxy.</p>

<p>Please keep in mind throughout this tutorial that <strong>Mailpile is still in beta</strong>, which means you may encounter bugs and other difficulties along the way. It does not save your information in between sessions. (That is, you'll have to re-enter your account details every time you restart the Mailpile service.)</p>

<p>It also lacks an easy way to run as a service. By default, it only runs as an interactive script in your SSH session. We've included an Upstart script that uses Screen to run it in the background, so you can leave the webmail client up as long as you'd like. This is not recommended for production, however.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Before we get started, we'll need a few things:</p>

<ul>
<li>A Droplet running <strong>Ubuntu 14.04</strong>. We recommend at least 512 MB of RAM for a Mailpile setup handling just a few mailboxes. If you expect more than a couple of users, you may want to increase the size</li>
<li>A user with root access. See <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">this tutorial</a> for instructions on setting up a user with sudo access on Ubuntu 14.04</li>
<li>An SSL certificate to keep your mail safe. You can purchase one from <a href="https://www.namecheap.com">Namecheap</a> or another certificate authority. If you don't want to spend any money, you can also <a href="https://indiareads/community/tutorials/how-to-create-an-ssl-certificate-on-nginx-for-ubuntu-14-04">make your own for use with Nginx</a> or get one from <a href="https://www.startssl.com/">StartSSL</a></li>
<li>A domain name</li>
<li>If you have a domain ready, create an A record to point to your Droplet (ex. mailpile.<span class="highlight">example.com</span>). For instructions on setting DNS records with IndiaReads, see <a href="https://indiareads/community/tutorials/how-to-set-up-a-host-name-with-digitalocean">this tutorial on DNS</a></li>
</ul>

<p>Make a note of your SSL certificate and key locations. If you followed the tutorial to make certificates for use with Nginx, they will be located at:</p>

<ul>
<li>/etc/nginx/ssl/nginx.crt</li>
<li>/etc/nginx/ssl/nginx.key</li>
</ul>

<p>That's it! If you have everything all ready to go, proceed to the first step.</p>

<h2 id="step-1-—-downloading-mailpile">Step 1 — Downloading Mailpile</h2>

<p>In this section we will prepare our working environment for the Mailpile installation.</p>

<p>First we need to log into our Droplet. If you haven't used SSH before, see <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">this tutorial on SSH</a>. Make sure you're logging into a user with sudo access.</p>

<p>First we need to install Git. We will use Git to clone the Mailpile source from GitHub.</p>

<p>Update Ubuntu's package lists:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install Git:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git
</li></ul></code></pre>
<p>Now that Git is installed, let's change our directory to somewhere we can work out of. In this case, we'll use the <code>/var</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var
</li></ul></code></pre>
<p>Clone Mailpile:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo git clone https://github.com/mailpile/Mailpile.git
</li></ul></code></pre>
<p>We need the sudo command to allow Git to create a directory inside of <code>/var</code>, which is a system directory.</p>

<p>We're almost ready to get Mailpile running. Proceed to Step 2 to begin tackling some more requirements.</p>

<h2 id="step-2-—-configuring-mailpile-39-s-requirements">Step 2 — Configuring Mailpile's Requirements</h2>

<p>In this section we'll get Mailpile's requirements installed and configured. </p>

<p>First, let's install pip. pip is a Python package manager with a few tricks up its sleeve:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install python-pip
</li></ul></code></pre>
<p>pip will allow us to install Mailpile's requirements more easily. You'll see how in a minute, but first we need to install a few more things. </p>

<p>Next we need to install lxml. lxml is a Mailpile requirement that would normally be instaled by pip, but we've found it to cause the installation to fail for unknown reasons. Because of this, we'll install it with apt-get:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install python-lxml
</li></ul></code></pre>
<p>Just a few more packages have to be installed manually, including GnuPG and OpenSSL. These will create a more secure environment for our mail. Some of these will likely be installed by default, but we'll make sure just in case:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install gnupg openssl libssl-dev
</li></ul></code></pre>
<p>Now change into Mailpile's directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/Mailpile
</li></ul></code></pre>
<p>We're now ready to harness pip's abilities to install the rest of our requirements.</p>

<p>Mailpile includes a file called <code>requirements.txt</code>, which is basically a list of requirements. pip has the ability to read through this list and automatically install each and every one of them. So let's do exactly that:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip install -r /var/Mailpile/requirements.txt
</li></ul></code></pre>
<p>You're done. All requirements have been installed and Mailpile is ready to use. But before we do, we need to take a few extra steps to tighten our security.</p>

<h2 id="step-3-—-configuring-a-reverse-proxy-with-nginx">Step 3 — Configuring a Reverse Proxy with Nginx</h2>

<p>In this section we'll configure Nginx as a reverse proxy for Mailpile. This will make Mailpile more secure, allow us to use an SSL certificate, and make it easier to access the webmail client.</p>

<p>With Nginx, instead of accessing Mailpile by visiting <code>https://<span class="highlight">example.com</span>:33411</code>, you can use <code>https://mailpile.<span class="highlight">example.com</span></code>. Let's get started!</p>

<p>First, we'll need to have Nginx installed since that's what's going to be doing most of the work. So let's get Nginx before anything else:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>Now that Nginx is installed, we can set up the reverse proxy. Let's edit Nginx's configuration to tell it to route our subdomain to Mailpile.</p>

<p>We want to delete the original Nginx config file since it's filled with a bunch of stuff we don't need. But first, let's make a backup. First make the directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /home/backup
</li></ul></code></pre>
<p>Now make the backup:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp -b /etc/nginx/sites-enabled/default /home/backup
</li></ul></code></pre>
<p>Now we are free to delete the file without consequences:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rm /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Let's make sure it's actually gone:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls /etc/nginx/sites-available/
</li></ul></code></pre>
<p>If you've just installed Nginx, the command should return nothing.</p>

<p>Now create a new file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/<span class="highlight">default</span>
</li></ul></code></pre>
<p>Now it's time to configure the reverse proxy. Let's start with the first part. Add the following to the beginning of the file (we'll explain what it does in a second):</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80;
    return 301 https://$host$request_uri;
}
</code></pre>
<p>This tells Nginx to redirect the requests it gets to HTTPS. But in reality, it will try to redirect to something that doesn't exist yet. Let's create somewhere for it to go:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {

    listen 443;
    server_name mailpile.<span class="highlight">example.com</span>;

    ssl_certificate           <span class="highlight">/etc/nginx/ssl/nginx.crt</span>;
    ssl_certificate_key       <span class="highlight">/etc/nginx/ssl/nginx.key</span>;

    ssl on;
    ssl_session_cache  builtin:1000  shared:SSL:10m;
    ssl_protocols  TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers HIGH:!aNULL:!eNULL:!EXPORT:!CAMELLIA:!DES:!MD5:!PSK:!RC4;
    ssl_prefer_server_ciphers on;

    access_log            /var/log/nginx/mailpile.access.log;
</code></pre>
<p>Note: make sure your certificate and key are located at <code>/etc/nginx/ssl/nginx.crt</code> and <code>/etc/nginx/ssl/nginx.key</code>. Otherwise, update the paths next to <code>ssl_certificate</code> and <code>ssl_certificate_key</code> to match your certificate and key locations. </p>

<p>What we just entered told Nginx to listen on port 443 (the port websites with SSL access, as opposed to port 80), apply our SSL certificate, and turn SSL on. Now we need to actually serve something to this new HTTPS URL we've redirected to and enabled SSL on. We'll do that next. </p>

<p>Add the following below the previous two blocks:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">    location / {

      proxy_set_header        Host $host;
      proxy_set_header        X-Real-IP $remote_addr;
      proxy_set_header        X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header        X-Forwarded-Proto $scheme;

      # Fix the "It appears that your reverse proxy set up is broken" error.
      proxy_pass          http://localhost:33411;
      proxy_read_timeout  90;

      proxy_redirect      http://localhost:33411 https://webmail.<span class="highlight">example.com</span>;
    }
   }
</code></pre>
<p>When you're all done, the completed config file should look something like this:</p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">server {
    listen 80;
    return 301 https://$host$request_uri;
}

server {

    listen 443;
    server_name mailpile.<span class="highlight">example.com</span>;

    ssl_certificate           <span class="highlight">/etc/nginx/ssl/nginx.crt</span>;
    ssl_certificate_key       <span class="highlight">/etc/nginx/ssl/nginx.key</span>;

    ssl on;
    ssl_session_cache  builtin:1000  shared:SSL:10m;
    ssl_protocols  TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers HIGH:!aNULL:!eNULL:!EXPORT:!CAMELLIA:!DES:!MD5:!PSK:!RC4;
    ssl_prefer_server_ciphers on;

    access_log            /var/log/nginx/mailpile.access.log;

    location / {

      proxy_set_header        Host $host;
      proxy_set_header        X-Real-IP $remote_addr;
      proxy_set_header        X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header        X-Forwarded-Proto $scheme;

      # Fix the "It appears that your reverse proxy set up is broken" error.
      proxy_pass          http://localhost:33411;
      proxy_read_timeout  90;

      proxy_redirect      http://localhost:33411 https://webmail.<span class="highlight">example.com</span>;
    }
   }
</code></pre>
<p>If you did not replace the default site, but instead created a server block file with a different name, you will need to enable it with a command like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ln -s /etc/nginx/sites-available/<span class="highlight">mailpile.example.com</span> /etc/nginx/sites-enabled/
</li></ul></code></pre>
<p>The default site should already be enabled.</p>

<p>Please read this <a href="https://indiareads/community/tutorials/how-to-set-up-nginx-server-blocks-virtual-hosts-on-ubuntu-14-04-lts">article about Nginx server blocks</a> if you would like to learn more.</p>

<p>Now restart Nginx to reload the configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p>That's it. Now Mailpile is ready to be reached at <code>https://mailpile.<span class="highlight">example.com</span></code>. You may have to accept the SSL warning if you used a self-signed certificate.</p>

<p>Also, accessing <code>http://mailpile.<span class="highlight">example.com</span></code> will automatically redirect to the SSL version of the site.</p>

<p>We haven't run Mailpile yet, so if you visit those URLs now, you'll see a 502 Bad Gateway error. The most common reason for this error is that the Mailpile application is not running.</p>

<p>Proceed to Step 4 to run Mailpile.</p>

<h2 id="step-4-—-configuring-and-running-mailpile">Step 4 — Configuring and Running Mailpile</h2>

<p>In this section we'll start Mailpile, and configure it to work with our reverse proxy. </p>

<p>Make sure we're in the correct directory: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /var/Mailpile
</li></ul></code></pre>
<p>To run Mailpile, enter:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./mp
</li></ul></code></pre>
<p>You can start exploring Mailpile through the command line or the web interface now.</p>

<p><span class="warning"><strong>A word of warning:</strong> Mailpile will <strong>not save</strong> your settings after it stops. So, before you spend time configuring it, you may want to complete the optional next step of running it like a service.<br /></span></p>

<p>Mailpile should now be live at <code>https://mailpile.<span class="highlight">example.com</span></code>, and even redirect to HTTPS using your SSL certificate. Congratulations!</p>

<p>You can use <code>CTRL-C</code> and then type <code>quit</code> to quit Mailpile.</p>

<h3 id="optional-—-make-mailpile-a-service-with-upstart">Optional — Make Mailpile a Service with Upstart</h3>

<p>To ensure Mailpile is always active and ready to handle your mail, you can convert Mailpile to a service, using Upstart. Follow <a href="https://indiareads/community/tutorials/the-upstart-event-system-what-it-is-and-how-to-use-it">this</a> wonderful tutorial for instructions.</p>

<p>Since Mailpile is in beta, it hasn't been properly daemonized yet. It also requires an interactive command line, so you can't just directly run the Python script. This Upstart script is a <strong>hacky</strong> way of running the Python app as a service through <a href="https://indiareads/community/tutorials/how-to-install-and-use-screen-on-an-ubuntu-cloud-server">Screen</a>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/init/mailpile.conf
</li></ul></code></pre><div class="code-label " title="/etc/init/mailpile.conf">/etc/init/mailpile.conf</div><pre class="code-pre "><code langs="">description "Mailpile Webmail Client"
author      "Sharon Campbell"

start on filesystem or runlevel [2345]
stop on shutdown

script

    echo $$ > /var/run/mailpile.pid
    exec /usr/bin/screen -dmS mailpile_init /var/Mailpile/mp

end script

pre-start script
    echo "[`date`] Mailpile Starting" >> /var/log/mailpile.log
end script

pre-stop script
    rm /var/run/mailpile.pid
    echo "[`date`] Mailpile Stopping" >> /var/log/mailpile.log
end script
</code></pre>
<p>This script will start Mailpile and keep it up as long as the Screen session is running. It does not properly stop the Screen session, so you'll have to stop the Screen session manually if you want to stop Mailpile.</p>

<p>With this script, you can start Mailpile with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo start mailpile
</li></ul></code></pre>
<p>This will result in a Screen session called <strong><span class="highlight">12345</span>.mailpile_init</strong> owned by the <strong>root</strong> user.</p>

<p>However, the other Upstart commands will not work. You will have to end the Screen session manually. Also, if the service crashes or is stopped, you'll have to start it again and reset all your preferences.</p>

<h3 id="step-4-—-getting-started-with-mailpile">Step 4 — Getting Started with Mailpile</h3>

<p>This section covers basic Mailpile use from the webmail interface, at <code>https://mailpile.<span class="highlight">example.com</span></code>.</p>

<p>Here is the screen you’ll see when you visit Mailpile for the first time.</p>

<p><img src="https://assets.digitalocean.com/articles/mailpile/mailpile-home.png" alt="Mailpile’s Initial Launch Screen" /></p>

<p>Choose a language from the dropdown menu.</p>

<p>Click the <strong>Begin</strong> button.</p>

<p>Create a new password, then enter it twice.</p>

<p>Click the <strong>Start using Mailpile</strong> button.</p>

<p>The Login Screen: please enter the password you just created.</p>

<p><img src="https://assets.digitalocean.com/articles/mailpile/YH043iM.png" alt="The Login Screen" /></p>

<p>Add a new account with the <strong>+ Add Account</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/mailpile/9OPj3Or.png" alt="Add a New Account" /></p>

<p>From here, you'll need to enter details for a mail account you own. You should enter the email address and password for that specific mail account. Mailpile will then attempt to connect to your account with those credentials, which can take a few minutes.</p>

<p>You can also enter the <strong>Sending Mail</strong> and <strong>Receiving Mail</strong> credentials manually, if Mailpile can't figure them out itself.</p>

<p><span class="note">Gmail blocks Mailpile from using your Gmail account credentials, so you can't add a Gmail account to Mailpile — at least not easily.</span></p>

<p>Once you log in, you’ll be presented with this screen:</p>

<p><img src="https://assets.digitalocean.com/articles/mailpile/ueJqKlG.png" alt="Mailpile Inbox" /></p>

<p>Try sending and receiving a test email for the account you added to Mailpile to a different email account. If this is successful, you'll know Mailpile is working with your email address.</p>

<p><strong>Other Mailpile features</strong></p>

<p>Mailpile also offers a wide variety of encryption options:</p>

<p><img src="https://assets.digitalocean.com/articles/mailpile/Nh2yptT.png" alt="Mailpile’s Encryption Options" /></p>

<h2 id="conclusion">Conclusion</h2>

<p>To get started with Mailpile, see the <a href="https://www.mailpile.is/faq/">FAQ</a>.</p>

<p>For more configuration options, run <code>help</code> from the Mailpile command line. </p>

<p>Congratulations, you now have your very own webmail client, Mailpile, running on a Ubuntu 14.04 Droplet. It is fully equipped with SSL and automatically redirects to the HTTPS version of your site. You may now set up your email accounts and manage your contacts, mail, categories, and more with Mailpile's beautiful interface. Have fun!</p>

    