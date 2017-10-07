<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="an-article-from-the-peps-team-at-mlstate">An Article from the <a href="https://github.com/MLstate/PEPS/">PEPS Team at MLstate</a></h3>

<h3 id="introduction">Introduction</h3>

<p>We all use email and online file storage services like Gmail or Dropbox. However, these services may not be suitable for the storage of sensitive data, both personal and professional. Do we trust their privacy policies when attaching an important business contract or confidential information? Do we accept that all our data will be collected, processed, and analyzed?</p>

<p>There is a solution to this problem: PEPS is an email, file sharing, and chat platform that uses end-to-end encryption. End-to-end encryption ensures that encryption and decryption happen on your computer (the client) and not on the server, which never sees confidential data in clear text.</p>

<p><img src="https://assets.digitalocean.com/articles/peps_ubuntu_1404/peps-macbook.png" alt="PEPS: email, file sharing and chat you can run on your own cloud instance" /></p>

<p>This tutorial will guide you through the process of deploying your PEPS instance on a IndiaReads Droplet so you can safely store your data.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>PEPS is distributed as Docker containers to make setup easy. You will need a IndiaReads Droplet with Ubuntu 14.04 x64 and the Docker application installed on it. Specifically:</p>

<ul>
<li><p>An Ubuntu 14.04 x64 Droplet with 2 GB of memory if you have just a few users. Select 4 GB of RAM or more if you need more users or you just need more storage for your data.</p></li>
<li><p>Purchase an SSL certificate to use in place of the self-signed one; this is recommended for production environments. Alternatively, you can create a free signed SSL certificate. Instructions for creating the certificate are included later in this tutorial.</p></li>
</ul>

<p>The name of your Droplet matters: If you plan to send messages via email to external recipients, you want <em>Reverse DNS</em> configured to avoid your messages getting flagged as spam. Good news: IndiaReads <a href="https://indiareads/community/questions/how-do-i-set-up-reverse-dns-for-my-ip">automatically configures</a> the PTR record if your Droplet name is set to your FQDN (Fully Qualified Domain Name). If you plan to send email from <code>mail.example.com</code>, that should also be the name of your Droplet (even if your addresses are in the form of <code>user@example.com</code>).</p>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> explains how to add users and give them sudo access.</p>

<h2 id="step-1-—-installing-docker">Step 1 — Installing Docker</h2>

<p>The first step is to install Docker. This tutorial is based on Docker 1.6.2. You have 2 options for installing Docker:</p>

<ul>
<li>Follow the instructions for Ubuntu 14.04 in <a href="https://indiareads/community/tutorials/how-to-install-and-use-docker-getting-started#how-to-install-docker">How To Install and Use Docker: Getting Started</a></li>
<li>Add the Docker application when you create the Ubuntu 14.04 x64 Droplet</li>
</ul>

<p>You also need to add the non-root user you created (the one that will be running all the command in this tutorial) to the <code>docker</code> user group. Replace <span class="highlight">sammy</span> with your username:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo usermod -aG docker <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>You will also need to logout and log back in as your non-root user for this change to be active.</p>

<h2 id="step-2-—-deploying-peps">Step 2 — Deploying PEPS</h2>

<p>Connect to your Droplet via SSH using <code>ssh <span class="highlight">sammy</span>@<span class="highlight">your_server_ip</span></code> (replace your username and server IP), and run the following commands to prepare the environment.</p>

<p>First, clone the repository:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone https://github.com/MLstate/PEPS
</li></ul></code></pre>
<p>Change to the <code>PEPS</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd PEPS
</li></ul></code></pre>
<p>Configure your domain name, replacing <span class="highlight">example.com</span> with your domain name:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo <span class="highlight">example.com</span> > domain
</li></ul></code></pre>
<p>This command creates a text file named <code>domain</code> with your domain name as the first and only line in the file.</p>

<p>Install make:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install make
</li></ul></code></pre>
<p>Now it's time to build the containers, which will take about 10-20 minutes, so you can enjoy a coffee or schedule a stand-up meeting:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">make build
</li></ul></code></pre>
<p>If everything runs fine, it ends with something like the following (the ids are randomly generated and will differ):</p>
<pre class="code-pre "><code langs="">Removing intermediate container 38d212189d43
Successfully built 24fd74241e48
</code></pre>
<p>For the first launch, we are going to create temporary SSL/TLS certificates and run the containers. (Both steps are almost instant, so don't think you were going to take another coffee break.)</p>

<p>If you already have SSL certificates at hand for your domain, skip this and copy your certificate and key instead (see Step 5).</p>

<p>Create temporary SSL certificates with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">make certificate
</li></ul></code></pre>
<p>Choose a simple passphrase, since you will be asked to type it 4 times, and the certificates are placeholders. Most of the questions can be skipped: The only question that matters is <code>Common Name (e.g. server FQDN or YOUR name) []:</code> which should be the same as your domain.</p>

<p>Here is an example dialog:</p>
<pre class="code-pre "><code langs="">openssl genrsa -des3 -out server.key 1024
Generating RSA private key, 1024 bit long modulus [...]
Enter pass phrase for server.key:
Verifying - Enter pass phrase for server.key:
openssl req -new -key server.key -out server.csr
Enter pass phrase for server.key:
You are about to be asked to enter information that will be [...]
Country Name (2 letter code) [AU]: <span class="highlight">DE</span>
State or Province Name (full name) [Some-State]:
Locality Name (eg, city) []:
Organization Name (eg, company) [Internet Widgits Pty Ltd]:
Organizational Unit Name (eg, section) []:
Common Name (e.g. server FQDN or YOUR name) []: <span class="highlight">example.com</span>
Email Address []:
Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:
An optional company name []:
cp server.key server.key.org
openssl rsa -in server.key.org -out server.key # strip passphrase
Enter pass phrase for server.key.org:
writing RSA key [...]
Getting Private key
</code></pre>
<p>Now, we're ready to launch PEPS with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo make run
</li></ul></code></pre>
<h2 id="step-3-—-logging-in-for-the-first-time">Step 3 — Logging in for the First Time</h2>

<p>Connect to your Droplet using its IP address by visiting <code>https://<span class="highlight">your_server_ip</span></code> from your browser where <code><span class="highlight">your_server_ip</span></code> is the IP address of your Droplet.</p>

<p>Since we are using temporary SSL certificates for now, your browser will warn you that the site is insecure. Accept it anyway. With Chrome, click <strong>Advanced</strong> to proceed.</p>

<p><img src="https://assets.digitalocean.com/articles/peps_ubuntu_1404/ssl_chrome.png" alt="Chrome warns you about the wrong SSL certificate, we'll fix that later" /></p>

<p>At first run, you will be prompted to create an admin password. Choose any of your liking, provided its complexity is sufficient.</p>

<p><img src="https://assets.digitalocean.com/articles/peps_ubuntu_1404/first_run.png" alt="PEPS is working, choose your admin password" /></p>

<p>Due to end-to-end encryption in PEPS, the admin account can create and delete users but will not be able to access any existing encrypted user data.</p>

<p>Once your admin password is set up, the main PEPS interface is shown.</p>

<p><img src="https://assets.digitalocean.com/articles/peps_ubuntu_1404/peps_interface.png" alt="The PEPS interface with no data" /></p>

<p>Next, let's focus on setting up the domain and certificates properly.</p>

<h2 id="step-4-—-setting-up-your-domain">Step 4 — Setting Up Your Domain</h2>

<p>Now that your instance runs fine, we still need to set the domain properly, which involves using real SSL certificates, configuring DNS, and more.</p>

<p>Let's start with the DNS. Depending on your domain name provider, either use their own interface to set up the DNS entries for your domain or set up your own DNS server. If you want to setup your own DNS server, you can use the <a href="https://indiareads/community/tutorials/how-to-configure-bind-as-a-private-network-dns-server-on-ubuntu-14-04">How To Configure BIND as a Private Network DNS Server on Ubuntu 14.04</a> article, which is part of the <a href="https://indiareads/community/tutorial_series/an-introduction-to-managing-dns">An Introduction to Managing DNS</a> article series.</p>

<p>You must set both A and MX records. For instance, for the fictitious <code>example.com</code> domain hosted on <code>mail.example.com</code>:</p>
<pre class="code-pre "><code langs="">mail.example.com.   10799   IN   A   <span class="highlight">your_server_ip</span>
mail.example.com.   10799   IN   MX  example.com.
</code></pre>
<p>Your Droplet name should be <code>mail.example.com</code>. Don't worry. You can rename the Droplet from your IndiaReads account. Click on the Droplet name to see its details, click the <strong>Settings</strong> tab, and then click the <strong>Rename</strong> tab. You might have to wait for DNS to get updated.</p>

<p>You may also set additional records. Online checker <a href="http://mxtoolbox.com/">MXToolBox</a> is useful to verify your domain is set up properly and gives advice on several points.</p>

<p>Note that DNS propagation can be a bit slow, but after a while (often 1 hour) you will be able to access PEPS from <code>https://example.com</code>.</p>

<p><span class="note"><strong>Note:</strong> After you have finished configuring PEPS, if you can't send or receive email from external domains, double check your A and MX records. If they aren't set correctly, you will not be able to send or receive email from domains other than your own.<br /></span></p>

<h2 id="step-5-—-setting-up-ssl-certificates">Step 5 — Setting up SSL Certificates</h2>

<p>You will still have an invalid SSL certificate warning from your browser.</p>

<p>It's now time to set up SSL certificates. If you don't already have SSL certificates you can buy them from a provider or even <a href="https://indiareads/community/tutorials/how-to-set-up-apache-with-a-free-signed-ssl-certificate-on-a-vps">set up a free SSL certificate</a> for non-commercial purposes.</p>

<p>The <a href="https://indiareads/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority">How To Install an SSL Certificate from a Commercial Certificate Authority</a> article explains everything about SSL certificates, including how to purchase one.</p>

<p>Be sure to copy both the key and certificate named <code>server.key</code> and <code>server.crt</code> in the <code>/etc/peps/</code> directory.</p>

<p>Prepare them on your local computer, and copy the files to your server by running from the directory that contains the certificates:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">scp server.key server.crt <span class="highlight">your_server_ip</span>:/etc/peps/
</li></ul></code></pre>
<p>where <code><span class="highlight">your_server_ip</span></code> is the IP address of your Droplet.</p>

<p>When done, check that your browser can access <code>https://example.com</code> without SSL errors.</p>

<h2 id="step-6-—-testing">Step 6 — Testing</h2>

<p>To create more users, log in as the admin user with <code>admin</code> as the username and with the password you created in <em>Step 3: Logging in for the First Time</em>. The admin user can create email accounts for your domain. Go to the <a href="https://github.com/MLstate/PEPS/wiki/Admin-Manual">PEPS Admin Manual</a> to learn how.</p>

<p>First, try to send and receive email between two different users within your domain. For example, try sending an email from admin@example.com to sammy@example.com. If that is successful, try having sammy respond to admin to make sure the reverse operation succeeds.</p>

<p>Now, send an email to an account outside of your domain. If this fails, your A and MX records have not been configured correctly. Go back to <em>Step 4: Setting Up Your Domain</em>. Don't forget to test receiving email from a user outside your domain as well.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You now have an instance of PEPS running on a IndiaReads Droplet. You can send messages, share files, and more (by running plugins such as <a href="https://github.com/MLstate/PEPS-chat">chat</a>) securely.</p>

<p>There are several manuals available:</p>

<ul>
<li><a href="https://github.com/MLstate/PEPS/wiki/User-Manual">User Manual</a></li>
<li><a href="https://github.com/MLstate/PEPS/wiki/Admin-Manual">Admin Manual</a></li>
<li>More documentation for developers wanting to use the PEPS API or for operators regarding backup and more are available from the project wiki on <a href="https://github.com/MLstate/PEPS/wiki">GitHub</a>.</li>
</ul>

<p>Also visit the <a href="https://facebook.com/endtoend">PEPS Facebook page</a> for the latest news about PEPS.</p>

    