<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>If your workplace or school uses Microsoft Exchange for E-mail, you may wish to access your Exchange E-mail account from E-mail clients that do not support the Exchange protocol. <a href="http://davmail.sourceforge.net/">DavMail</a> provides a solution, translating Microsoft Exchange to open protocols like POP, IMAP, SMTP, Caldav, Carddav, and LDAP.</p>

<h2 id="installation">Installation</h2>

<hr />

<p>Installing DavMail on CentOS 6 will require adding a 3rd party repository. Download the .repo file and update your yum cache:</p>
<pre class="code-pre "><code langs="">sudo curl -o /etc/yum.repos.d/home:marcindulak.repo http://download.opensuse.org/repositories/home:/marcindulak/CentOS_CentOS-6/home:marcindulak.repo
sudo yum update
</code></pre>
<p>Then, install DavMail with yum:</p>
<pre class="code-pre "><code langs="">sudo yum install davmail
</code></pre>
<p>You will have to install an additional package so the included init script functions properly:</p>
<pre class="code-pre "><code langs="">sudo yum install redhat-lsb-core
</code></pre>
<h2 id="basic-configuration">Basic Configuration</h2>

<hr />

<p>DavMail's configuration file is located at <code>/etc/davmail.properties</code>. Open it in your favorite text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/davmail.properties
</code></pre>
<p>Set DavMail to server mode so it doesn't require X11:</p>
<pre class="code-pre "><code langs="">davmail.server=true
</code></pre>
<p>Enable remote mode and set the bind address to your droplet's IP address or set it blank:</p>
<pre class="code-pre "><code langs="">davmail.allowRemote=true
davmail.bindAddress=
</code></pre>
<p>Set <code>davmail.url</code> to your Outlook Web App/Outlook Web Access URL, which usually ends in <code>/owa</code>:</p>
<pre class="code-pre "><code langs="">davmail.url=https://yourcompany.com/owa 
</code></pre>
<p>The default ports that DavMail uses are non-standard, and you will probably want to change them to ease the process of setting up E-mail clients. To configure DavMail to use the default ports for SSL encrypted IMAP and SMTP, change the <code>davmail.imapPort</code> and <code>davmail.smtpPort</code> options:</p>
<pre class="code-pre "><code langs="">davmail.imapPort=993
davmail.smtpPort=465
</code></pre>
<p>Save and close the configuration file. </p>

<h2 id="create-a-ssl-certificate">Create A SSL Certificate</h2>

<hr />

<p>In order to enable SSL encryption, you will need a SSL certificate and SSL private key in the PEM format. If you have purchased a certificate from a Certificate Authority, then you should already have your certificate and key. If so, continue to the Configuring SSL section below. Otherwise, you can generate a self-signed certificate by following these steps.</p>

<p>Generate a RSA key with OpenSSL:</p>
<pre class="code-pre "><code langs="">sudo openssl genrsa -out /etc/pki/tls/private/davmail.key 2048
</code></pre>
<p>Make sure the key is owned by root and permissions are set properly:</p>
<pre class="code-pre "><code langs="">sudo chown root:root /etc/pki/tls/private/davmail.key
sudo chmod 600 /etc/pki/tls/private/davmail.key
</code></pre>
<p>Now, create a certificate signing request:</p>
<pre class="code-pre "><code langs="">sudo openssl req -new -key /etc/pki/tls/private/davmail.key -out /etc/pki/tls/certs/davmail.csr
</code></pre>
<p>OpenSSL will now ask you several questions. The only important field is Common Name, which should be set to the domain name or IP address of your droplet which will be accessed by your E-mail clients (for example davmail.mydomain.com or 123.123.123.123). The other fields can be left at their defaults by just pressing enter or can be filled in with anything:</p>
<pre class="code-pre "><code langs="">You are about to be asked to enter information that will be incorporated
into your certificate request.
What you are about to enter is what is called a Distinguished Name or a DN.
There are quite a few fields but you can leave some blank
For some fields there will be a default value,
If you enter '.', the field will be left blank.
-----
Country Name (2 letter code) [XX]:US
State or Province Name (full name) []:New York
Locality Name (eg, city) [Default City]:New York City
Organization Name (eg, company) [Default Company Ltd]:Lolcats United
Organizational Unit Name (eg, section) []:Keyboard Cat Department 
Common Name (eg, your name or your server's hostname) []:mydomain.com
Email Address []:me@mydomain.com

Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:
An optional company name []:
</code></pre>
<p>Sign the certificate request using your private key, setting the expiration date with the <code>-days</code> argument:</p>
<pre class="code-pre "><code langs="">sudo openssl x509 -req -signkey /etc/pki/tls/private/davmail.key -in /etc/pki/tls/certs/davmail.csr -out /etc/pki/tls/certs/davmail.crt -days 365
</code></pre>
<p>With the settings above, the certificate will expire in 365 days (a year).</p>

<p><strong>You now have your own SSL certificate!</strong></p>

<h2 id="configuring-ssl">Configuring SSL</h2>

<hr />

<p>Now that you have your SSL certificate, you will have to convert it into a format DavMail understands. The following examples will use the key and certificate we generated above. If you purchased a certificate from a Certificate Authority, then use those files in place of <code>davmail.key</code> and <code>davmail.crt</code>.</p>

<p>Start by combining your certificate and key file with cat:</p>
<pre class="code-pre "><code langs="">sudo cat /etc/pki/tls/private/davmail.key /etc/pki/tls/certs/davmail.crt > /etc/pki/tls/certs/davmail.pem
</code></pre>
<p>Once again, set permissions so only root can access the key file:</p>
<pre class="code-pre "><code langs="">sudo chown root:root /etc/pki/tls/certs/davmail.pem
sudo chmod 600 /etc/pki/tls/certs/davmail.pem
</code></pre>
<p>Now convert your combined key and certificate to a pkcs12 file:</p>
<pre class="code-pre "><code langs="">openssl pkcs12 -export -in /etc/pki/tls/certs/davmail.pem -out /etc/pki/tls/certs/davmail.p12 -name “davmail”
</code></pre>
<p>You will be prompted to enter an export password. This can not be blank! You must set a password, or DavMail will not work properly.</p>

<p>Set permissions:</p>
<pre class="code-pre "><code langs="">sudo chown root:root /etc/pki/tls/certs/davmail.pem
sudo chmod 600 /etc/pki/tls/certs/davmail.pem
</code></pre>
<p>Now open your DavMail configuration again:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/davmail.properties
</code></pre>
<p>Add the following configuration options to inform DavMail of the location of the pkcs12 file you just generated and the passphrase you set:</p>
<pre class="code-pre "><code langs="">davmail.ssl.keystoreType=PKCS12
davmail.ssl.keystoreFile=/etc/pki/tls/certs/davmail.p12
davmail.ssl.keyPass=password
davmail.ssl.keystorePass=password
</code></pre>
<p>Both <code>davmail.ssl.keyPass</code> and <code>davmail.ssl.keystorePass</code> should should have the same value. Save the configuration file.</p>

<h2 id="start-davmail">Start DavMail</h2>

<hr />

<p>Because of the way Linux systems work, the ports we are using (993 and 465) require root access to open. This means the DavMail must be run as root. By default, the init script shipped with the DavMail package starts Davmail as the "davmail" user and will fail to start with our configuration. This can be fixed with a small tweak to the init script.</p>

<p>Make a copy of the default init script:</p>
<pre class="code-pre "><code langs="">sudo cp /etc/init.d/davmail /etc/init.d/davmail-root
</code></pre>
<p>Open the copy in your favorite text editor:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/init.d/davmail-root
</code></pre>
<p>Search for the line in the <code>start()</code> function that starts with <code>su - davmail</code> and replace <code>davmail</code> with <code>root</code>. The line should now look like this:</p>
<pre class="code-pre "><code langs="">[...]
su - root -s /bin/sh -c "exec nohup $DAVMAIL_HOME/davmail $DAVMAIL_CONF >> $LOGFILE 2>&1 &"
[...]
</code></pre>
<p>Save and close the file. Start DavMail using your modified init script:</p>
<pre class="code-pre "><code langs="">service davmail-root start
</code></pre>
<p>And finally, configure DavMail to start at boot:</p>
<pre class="code-pre "><code langs="">chkconfig davmail-root on
</code></pre>
<h2 id="client-configuration">Client Configuration</h2>

<p>Now that the virtal server is running, you are ready to configure your E-mail clients. Create a new account using the "manual" options of your E-mail client. Both the IMAP and SMTP server will be the domain name or IP address of your droplet, depending on what you used for the Common Name on your SSL certificate. The username for IMAP and SMTP will both be your E-mail address without the domain name. <em>Example: Your E-mail is bob@yourcompany.com, so your username is bob.</em> Make sure both IMAP and SMTP are set to use SSL/TLS and <strong>not</strong>* STARTTLS.</p>

<p>You will get warnings from your E-mail clients because you are using a self-signed certificate. It is <strong>safe</strong> to accept the certificate in this case, because you are the one who created it.</p>

<p>Specific instructions for Thunderbird, Mac OSX, and iOS are available at <a href="http://davmail.sourceforge.net/">DavMail's website</a>.</p>

<p><strong>You should now be able to send/recieve E-mail using your Microsoft Exchange E-mail account using open technologies!</strong></p>

<div class="author">Submitted by: <a href="http://jtekrony.com">Jesse TeKrony</a></div>

    