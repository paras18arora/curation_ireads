<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/SSLCertificate%28Apache%29_Create_twitter_mostov.png?1466190266/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><strong>TLS</strong>, or transport layer security, and its predecessor <strong>SSL</strong>, which stands for secure sockets layer, are web protocols used to wrap normal traffic in a protected, encrypted wrapper.</p>

<p>Using this technology, servers can send traffic safely between the server and clients without the possibility of the messages being intercepted by outside parties.  The certificate system also assists users in verifying the identity of the sites that they are connecting with.</p>

<p>In this guide, we will show you how to set up a self-signed SSL certificate for use with an Apache web server on an Ubuntu 16.04 server.</p>

<span class="note"><p>
<strong>Note:</strong> A self-signed certificate will encrypt communication between your server and any clients.  However, because it is not signed by any of the trusted certificate authorities included with web browsers, users cannot use the certificate to validate the identity of your server automatically.</p>

<p>A self-signed certificate may be appropriate if you do not have a domain name associated with your server and for instances where the encrypted web interface is not user-facing.  If you <em>do</em> have a domain name, in many cases it is better to use a CA-signed certificate.  You can find out how to set up a free trusted certificate with the Let's Encrypt project <a href="https://indiareads/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-ubuntu-16-04">here</a>.<br /></p></span>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin, you should have a non-root user configured with <code>sudo</code> privileges.  You can learn how to set up such a user account by following our <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">initial server setup for Ubuntu 16.04</a>.</p>

<p>You will also need to have the Apache web server installed.  If you would like to install an entire LAMP (Linux, Apache, MySQL, PHP) stack on your server, you can follow our guide on <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04">setting up LAMP on Ubuntu 16.04</a>.  If you just want the Apache web server, skip the steps pertaining to PHP and MySQL in the guide.</p>

<p>When you have completed the prerequisites, continue below.</p>

<h2 id="step-1-create-the-ssl-certificate">Step 1: Create the SSL Certificate</h2>

<p>TLS/SSL works by using a combination of a public certificate and a private key.  The SSL key is kept secret on the server.  It is used to encrypt content sent to clients.  The SSL certificate is publicly shared with anyone requesting the content.  It can be used to decrypt the content signed by the associated SSL key.</p>

<p>We can create a self-signed key and certificate pair with OpenSSL in a single command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt
</li></ul></code></pre>
<p>You will be asked a series of questions.  Before we go over that, let's take a look at what is happening in the command we are issuing:</p>

<ul>
<li><strong>openssl</strong>: This is the basic command line tool for creating and managing OpenSSL certificates, keys, and other files.</li>
<li><strong>req</strong>: This subcommand specifies that we want to use X.509 certificate signing request (CSR) management.  The "X.509" is a public key infrastructure standard that SSL and TLS adheres to for its key and certificate management.  We want to create a new X.509 cert, so we are using this subcommand.</li>
<li><strong>-x509</strong>: This further modifies the previous subcommand by telling the utility that we want to make a self-signed certificate instead of generating a certificate signing request, as would normally happen.</li>
<li><strong>-nodes</strong>: This tells OpenSSL to skip the option to secure our certificate with a passphrase.  We need Apache to be able to read the file, without user intervention, when the server starts up.  A passphrase would prevent this from happening because we would have to enter it after every restart.</li>
<li><strong>-days 365</strong>: This option sets the length of time that the certificate will be considered valid.  We set it for one year here.</li>
<li><strong>-newkey rsa:2048</strong>: This specifies that we want to generate a new certificate and a new key at the same time.  We did not create the key that is required to sign the certificate in a previous step, so we need to create it along with the certificate.  The <code>rsa:2048</code> portion tells it to make an RSA key that is 2048 bits long.</li>
<li><strong>-keyout</strong>: This line tells OpenSSL where to place the generated private key file that we are creating.</li>
<li><strong>-out</strong>: This tells OpenSSL where to place the certificate that we are creating.</li>
</ul>

<p>As we stated above, these options will create both a key file and a certificate.  We will be asked a few questions about our server in order to embed the information correctly in the certificate.</p>

<p>Fill out the prompts appropriately.  <strong>The most important line is the one that requests the <code>Common Name (e.g. server FQDN or YOUR name)</code>.  You need to enter the domain name associated with your server or, more likely, your server's public IP address.</strong></p>

<p>The entirety of the prompts will look something like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Country Name (2 letter code) [AU]:<span class="highlight">US</span>
State or Province Name (full name) [Some-State]:<span class="highlight">New York</span>
Locality Name (eg, city) []:<span class="highlight">New York City</span>
Organization Name (eg, company) [Internet Widgits Pty Ltd]:<span class="highlight">Bouncy Castles, Inc.</span>
Organizational Unit Name (eg, section) []:<span class="highlight">Ministry of Water Slides</span>
Common Name (e.g. server FQDN or YOUR name) []:<span class="highlight">server_IP_address</span>
Email Address []:<span class="highlight">admin@your_domain.com</span>
</code></pre>
<p>Both of the files you created will be placed in the appropriate subdirectories of the <code>/etc/ssl</code> directory.</p>

<p>While we are using OpenSSL, we should also create a strong Diffie-Hellman group, which is used in negotiating <a href="https://en.wikipedia.org/wiki/Forward_secrecy">Perfect Forward Secrecy</a> with clients.</p>

<p>We can do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048
</li></ul></code></pre>
<p>This may take a few minutes, but when it's done you will have a strong DH group at <code>/etc/ssl/certs/dhparam.pem</code> that we can use in our configuration.</p>

<h2 id="step-2-configure-apache-to-use-ssl">Step 2: Configure Apache to Use SSL</h2>

<p>We have created our key and certificate files under the <code>/etc/ssl</code> directory.  Now we just need to modify our Apache configuration to take advantage of these.</p>

<p>We will make a few adjustments to our configuration:</p>

<ol>
<li>We will create a configuration snippet to specify strong default SSL settings.</li>
<li>We will modify the included SSL Apache Virtual Host file to point to our generated SSL certificates.</li>
<li>(Recommended) We will modify the unencrypted Virtual Host file to automatically redirect requests to the encrypted Virtual Host.</li>
</ol>

<p>When we are finished, we should have a secure SSL configuration.</p>

<h3 id="create-an-apache-configuration-snippet-with-strong-encryption-settings">Create an Apache Configuration Snippet with Strong Encryption Settings</h3>

<p>First, we will create an Apache configuration snippet to define some SSL settings.  This will set Apache up with a strong SSL cipher suite and enable some advanced features that will help keep our server secure.  The parameters we will set can be used by any Virtual Hosts enabling SSL.</p>

<p>Create a new snippet in the <code>/etc/apache2/conf-available</code> directory.  We will name the file <code>ssl-params.conf</code> to make its purpose clear:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/conf-available/ssl-params.conf
</li></ul></code></pre>
<p>To set up Apache SSL securely, we will be using the recommendations by <a href="https://raymii.org/s/static/About.html">Remy van Elst</a> on the <a href="https://cipherli.st">Cipherli.st</a> site.  This site is designed to provide easy-to-consume encryption settings for popular software.  You can read more about his decisions regarding the Apache choices <a href="https://raymii.org/s/tutorials/Strong_SSL_Security_On_Apache2.html">here</a>.</p>

<span class="note"><p>
The suggested settings on the site linked to above offer strong security.  Sometimes, this comes at the cost of greater client compatibility.  If you need to support older clients, there is an alternative list that can be accessed by clicking the link on the page labelled "Yes, give me a ciphersuite that works with legacy / old software."  That list can be substituted for the items copied below.</p>

<p>The choice of which config you use will depend largely on what you need to support.  They both will provide great security.<br /></p></span>

<p>For our purposes, we can copy the provided settings in their entirety.  We will also go ahead and set the <code>SSLOpenSSLConfCmd DHParameters</code> setting to point to the Diffie-Hellman file we generated earlier:</p>
<div class="code-label " title="/etc/apache2/conf-available/ssl-params.conf">/etc/apache2/conf-available/ssl-params.conf</div><pre class="code-pre "><code langs=""># from https://cipherli.st/
# and https://raymii.org/s/tutorials/Strong_SSL_Security_On_Apache2.html

SSLCipherSuite EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
SSLProtocol All -SSLv2 -SSLv3
SSLHonorCipherOrder On
Header always set Strict-Transport-Security "max-age=63072000; includeSubdomains; preload"
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
# Requires Apache >= 2.4
SSLCompression off 
SSLSessionTickets Off
SSLUseStapling on 
SSLStaplingCache "shmcb:logs/stapling-cache(150000)"

<span class="highlight">SSLOpenSSLConfCmd DHParameters "/etc/ssl/certs/dhparam.pem"</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="modify-the-default-apache-ssl-virtual-host-file">Modify the Default Apache SSL Virtual Host File</h3>

<p>Next, let's modify <code>/etc/apache2/sites-available/default-ssl.conf</code>, the default Apache SSL Virtual Host file.  If you are using a different server block file, substitute it's name in the commands below.</p>

<p>Before we go any further, let's back up the original SSL Virtual Host file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf.bak
</li></ul></code></pre>
<p>Now, open the SSL Virtual Host file to make adjustments:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/default-ssl.conf
</li></ul></code></pre>
<p>Inside, with most of the comments removed, the Virtual Host file should look something like this by default:</p>
<div class="code-label " title="/etc/apache2/sites-available/default-ssl.conf">/etc/apache2/sites-available/default-ssl.conf</div><pre class="code-pre "><code langs=""><IfModule mod_ssl.c>
        <VirtualHost _default_:443>
                ServerAdmin webmaster@localhost

                DocumentRoot /var/www/html

                ErrorLog ${APACHE_LOG_DIR}/error.log
                CustomLog ${APACHE_LOG_DIR}/access.log combined

                SSLEngine on

                SSLCertificateFile      /etc/ssl/certs/ssl-cert-snakeoil.pem
                SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key

                <FilesMatch "\.(cgi|shtml|phtml|php)$">
                                SSLOptions +StdEnvVars
                </FilesMatch>
                <Directory /usr/lib/cgi-bin>
                                SSLOptions +StdEnvVars
                </Directory>

                # BrowserMatch "MSIE [2-6]" \
                #               nokeepalive ssl-unclean-shutdown \
                #               downgrade-1.0 force-response-1.0

        </VirtualHost>
</IfModule>
</code></pre>
<p>We will be making some minor adjustments to the file.  We will set the normal things we'd want to adjust in a Virtual Host file (ServerAdmin email address, ServerName, etc.), adjust the SSL directives to point to our certificate and key files, and uncomment one section that provides compatibility for older browsers.</p>

<p>After making these changes, your server block should look similar to this:</p>
<div class="code-label " title="/etc/apache2/sites-available/default-ssl.conf">/etc/apache2/sites-available/default-ssl.conf</div><pre class="code-pre "><code langs=""><IfModule mod_ssl.c>
        <VirtualHost _default_:443>
                ServerAdmin <span class="highlight">your_email@example.com</span>
                <span class="highlight">ServerName server_domain_or_IP</span>

                DocumentRoot /var/www/html

                ErrorLog ${APACHE_LOG_DIR}/error.log
                CustomLog ${APACHE_LOG_DIR}/access.log combined

                SSLEngine on

                SSLCertificateFile      /etc/ssl/certs/<span class="highlight">apache-selfsigned.crt</span>
                SSLCertificateKeyFile /etc/ssl/private/<span class="highlight">apache-selfsigned.key</span>

                <FilesMatch "\.(cgi|shtml|phtml|php)$">
                                SSLOptions +StdEnvVars
                </FilesMatch>
                <Directory /usr/lib/cgi-bin>
                                SSLOptions +StdEnvVars
                </Directory>

                <span class="highlight">BrowserMatch "MSIE [2-6]" \</span>
                               <span class="highlight">nokeepalive ssl-unclean-shutdown \</span>
                               <span class="highlight">downgrade-1.0 force-response-1.0</span>

        </VirtualHost>
</IfModule>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="recommended-modify-the-unencrypted-virtual-host-file-to-redirect-to-https">(Recommended) Modify the Unencrypted Virtual Host File to Redirect to HTTPS</h3>

<p>As it stands now, the server will provide both unencrypted HTTP and encrypted HTTPS traffic.  For better security, it is recommended in most cases to redirect HTTP to HTTPS automatically.  If you do not want or need this functionality, you can safely skip this section.</p>

<p>To adjust the unencrypted Virtual Host file to redirect all traffic to be SSL encrypted, we can open the <code>/etc/apache2/sites-available/000-default.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/000-default.conf
</li></ul></code></pre>
<p>Inside, within the <code>VirtualHost</code> configuration blocks, we just need to add a <code>Redirect</code> directive, pointing all traffic to the SSL version of the site:</p>
<div class="code-label " title="/etc/apache2/sites-available/000-default.conf">/etc/apache2/sites-available/000-default.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
        . . .

        Redirect "/" "https://<span class="highlight">your_domain_or_IP</span>"

        . . .
</VirtualHost>
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="step-3-adjust-the-firewall">Step 3: Adjust the Firewall</h2>

<p>If you have the <code>ufw</code> firewall enabled, as recommended by the prerequisite guides, might need to adjust the settings to allow for SSL traffic.  Luckily, Apache registers a few profiles with <code>ufw</code> upon installation.</p>

<p>We can see the available profiles by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw app list
</li></ul></code></pre>
<p>You should see a list like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Available applications:
  Apache
  Apache Full
  Apache Secure
  OpenSSH
</code></pre>
<p>You can see the current setting by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre>
<p>If you allowed only regular HTTP traffic earlier, your output might look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
Apache                     ALLOW       Anywhere
OpenSSH (v6)               ALLOW       Anywhere (v6)
Apache (v6)                ALLOW       Anywhere (v6)
</code></pre>
<p>To additionally let in HTTPS traffic, we can allow the "Apache Full" profile and then delete the redundant "Apache" profile allowance:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow 'Apache Full'
</li><li class="line" prefix="$">sudo ufw delete allow 'Apache'
</li></ul></code></pre>
<p>Your status should look like this now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
Apache Full                ALLOW       Anywhere
OpenSSH (v6)               ALLOW       Anywhere (v6)
Apache Full (v6)           ALLOW       Anywhere (v6)
</code></pre>
<h2 id="step-4-enable-the-changes-in-apache">Step 4: Enable the Changes in Apache</h2>

<p>Now that we've made our changes and adjusted our firewall, we can enable the SSL and headers modules in Apache, enable our SSL-ready Virtual Host, and restart Apache.</p>

<p>We can enable <code>mod_ssl</code>, the Apache SSL module, and <code>mod_headers</code>, needed by some of the settings in our SSL snippet, with the <code>a2enmod</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enmod ssl
</li><li class="line" prefix="$">sudo a2enmod headers
</li></ul></code></pre>
<p>Next, we can enable our SSL Virtual Host with the <code>a2ensite</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2ensite default-ssl
</li></ul></code></pre>
<p>We will also need to enable our <code>ssl-params.conf</code> file, to read in the values we set:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo a2enconf ssl-params
</li></ul></code></pre>
<p>At this point, our site and the necessary modules are enabled.  We should check to make sure that there are no syntax errors in our files.  We can do this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apache2ctl configtest
</li></ul></code></pre>
<p>If everything is successful, you will get a result that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 127.0.1.1. Set the 'ServerName' directive globally to suppress this message
Syntax OK
</code></pre>
<p>The first line is just a message telling you that the <code>ServerName</code> directive is not set globally.  If you want to get rid of that message, you can set <code>ServerName</code> to your server's domain name or IP address in <code>/etc/apache2/apache2.conf</code>.  This is optional as the message will do no harm.</p>

<p>If your output has <code>Syntax OK</code> in it, your configuration file has no syntax errors.  We can safely restart Apache to implement our changes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart apache2
</li></ul></code></pre>
<h2 id="step-5-test-encryption">Step 5: Test Encryption</h2>

<p>Now, we're ready to test our SSL server.</p>

<p>Open your web browser and type <code>https://</code> followed by your server's domain name or IP into the address bar:</p>
<pre class="code-pre "><code langs="">https://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>Because the certificate we created isn't signed by one of your browser's trusted certificate authorities, you will likely see a scary looking warning like the one below:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_ssl_1604/self_signed_warning.png" alt="Apache self-signed cert warning" /></p>

<p>This is expected and normal.  We are only interested in the encryption aspect of our certificate, not the third party validation of our host's authenticity.  Click "ADVANCED" and then the link provided to proceed to your host anyways:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_ssl_1604/warning_override.png" alt="Apache self-signed override" /></p>

<p>You should be taken to your site.  If you look in the browser address bar, you will see a lock with an "x" over it.  In this case, this just means that the certificate cannot be validated.  It is still encrypting your connection.</p>

<p>If you configured Apache to redirect HTTP to HTTPS, you can also check whether the redirect functions correctly:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>If this results in the same icon, this means that your redirect worked correctly.</p>

<h2 id="step-6-change-to-a-permanent-redirect">Step 6: Change to a Permanent Redirect</h2>

<p>If your redirect worked correctly and you are sure you want to allow only encrypted traffic, you should modify the unencrypted Apache Virtual Host again to make the redirect permanent.</p>

<p>Open your server block configuration file again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/apache2/sites-available/000-default.conf
</li></ul></code></pre>
<p>Find the <code>Redirect</code> line we added earlier.  Add <code>permanent</code> to that line, which changes the redirect from a 302 temporary redirect to a 301 permanent redirect:</p>
<div class="code-label " title="/etc/apache2/sites-available/000-default.conf">/etc/apache2/sites-available/000-default.conf</div><pre class="code-pre "><code langs=""><VirtualHost *:80>
        . . .

        Redirect <span class="highlight">permanent</span> "/" "https://<span class="highlight">your_domain_or_IP</span>"

        . . .
</VirtualHost>
</code></pre>
<p>Save and close the file.</p>

<p>Check your configuration for syntax errors:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apache2ctl configtest
</li></ul></code></pre>
<p>When you're ready, restart Apache to make the redirect permanent:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart apache2
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You have configured your Apache server to use strong encryption for client connections.  This will allow you serve requests securely, and will prevent outside parties from reading your traffic.</p>

    