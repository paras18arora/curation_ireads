<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will demonstrate how to install Sentora, a free open source web control panel, which is easy to install and maintain. By the end of this tutorial we will have a working webserver, email account, and landing page for one of your domains.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li>One Ubuntu 14.04 Droplet.</li>
<li>A registered domain name. You can read <a href="https://indiareads/community/tutorial_series/an-introduction-to-managing-dns">this series on managing DNS</a> for background information.</li>
</ul>

<h2 id="step-one-—-installing-sentora">Step One — Installing Sentora</h2>

<p>In this section, we will install the Sentora control panel.</p>

<p>SSH into your Droplet as <strong>root</strong> and start the installation by running the following command.</p>
<pre class="code-pre "><code langs="">bash <(curl -L -Ss http://sentora.org/install)
</code></pre>
<p>You'll be prompted to choose your geographic area and then the city or region the server timezone should be set in. Use the arrow keys to scroll up and down, then press <strong>ENTER</strong> to proceed once your choice is highlighted.</p>

<p><img src="https://assets.digitalocean.com/articles/sentora/MtD7Uk8.png" alt="" /></p>

<p>After selecting your time zone, you will be prompted to enter a domain to access your control panel. <strong>This should not be your main domain!</strong> Use a subdomain, such as <code>panel.<span class="highlight">example.com</span></code>.</p>
<pre class="code-pre "><code langs="">Enter the sub-domain you want to access Sentora panel: <span class="highlight">panel.example.com</span>
</code></pre>
<p>Then press <strong>ENTER</strong>. Next you will be asked to confirm the IP address of your server.</p>
<pre class="code-pre "><code langs="">Enter (or confirm) the public IP for this server: <span class="highlight">your_server_ip</span>
</code></pre>
<p>Double check it is correct and press <strong>ENTER</strong> again to continue.</p>

<p><strong>Note:</strong> You may get a warning such as:</p>
<pre class="code-pre "><code langs="">WARNING: panel.example.com is not defined in your DNS!
</code></pre>
<p>This is fine; we will set up DNS records in the next step. Enter <strong>y</strong> to continue.</p>

<p>The installation process may take some time. Please be patient as it installs the necessary components onto your server.</p>

<p>When the installation is almost done, you will be prompted to reboot your server to complete it. Enter <strong>y</strong>. Once your server reboots, you will have to SSH back in again.</p>

<h2 id="step-two-—-setting-up-dns">Step Two — Setting Up DNS</h2>

<p>Setting up your DNS is relatively simple, but the steps may vary between DNS servers. Go to the IndiaReads <a href="https://cloud.digitalocean.com/domains/">DNS panel</a> and press <strong>Add Domain</strong> in the upper right corner.</p>

<p>Fill out your domain name and IP address in the boxes provided:</p>

<p><img src="https://assets.digitalocean.com/articles/sentora/oGcykkU.png" alt="Box One: example.com; Box Two: 111.111.111.111" /></p>

<p>Then press <strong>Create Domain</strong>.</p>

<p>Click the blue <strong>Add Record</strong> button, then select <strong>A</strong>.</p>

<p>In the <strong>Enter Name</strong> box, enter the subdomain you chose during installation. For example, if you chose <code>panel.<span class="highlight">example.com</span></code> in the installer, enter <strong>panel</strong> in this box. In the <strong>Enter IP Address</strong> box, enter the IP address for your Droplet.</p>

<p><img src="https://assets.digitalocean.com/articles/sentora/3ctpIfb.png" alt="Box one: panel; Box two: 111.111.111.111" /></p>

<p>Press the green <strong>Create</strong> button.</p>

<p>Press the <strong>Add Record</strong> button again, and this time press the <strong>MX</strong> option. In the <strong>Enter Hostname</strong> box, type <strong>@</strong>. In the <strong>Enter Priority</strong> box, enter <strong>10</strong>. Then press click <strong>Create</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/sentora/xB0b5k7.png" alt="Box one: @; Box two: 10" /></p>

<p>Press the <strong>Add Record</strong> button one more time, and this time press the <strong>TXT</strong> button In the <strong>Enter Name</strong> box, type <strong>@</strong>. In the <strong>Enter Text</strong> box, enter:</p>
<pre class="code-pre "><code langs="">v=spf1 a mx ip4:<span class="highlight">your_server_ip</span> ~all
</code></pre>
<p>This ensures that mail you send is not recognized as spam by the receiver. After entering that, click <strong>Create</strong>.</p>

<p>Your DNS zones should look like:</p>

<p><img src="https://assets.digitalocean.com/articles/sentora/WOnX4gE.png" alt="Zone File Picture" /></p>

<p>And the zone text below should look look similar to this:</p>
<pre class="code-pre "><code langs="">$ORIGIN example.com.
$TTL 1800
example.com. IN SOA ns1.digitalocean.com. hostmaster.example.com. 1426733517 10800 3600 604800 1800
example.com. 1800 IN NS ns1.digitalocean.com.
example.com. 1800 IN NS ns2.digitalocean.com.
example.com. 1800 IN NS ns3.digitalocean.com.
example.com. 1800 IN A 111.111.111.111
panel.example.com. 1800 IN A 111.111.111.111
example.com. 1800 IN MX 10 example.com.
example.com. 1800 IN TXT v=spf1 a mx ip4:111.111.111.111 ~all
</code></pre>
<h2 id="step-three-—-changing-the-admin-password">Step Three — Changing the Admin Password</h2>

<p>In this step, we will log in to Sentora and update the admin password.</p>

<p>Using your favorite web browser, navigate to <code>http://panel.<span class="highlight">example.com</span></code>, and you will reach the Sentora login screen. The default username is <strong>zadmin</strong>. To get the password, SSH back into your Droplet if you haven't already and enter the following command.</p>
<pre class="code-pre "><code langs="">cat /root/passwords.txt
</code></pre>
<p>Look for the line that begins with <strong>zadmin Password:</strong>. Copy the password and use that to log in.</p>

<p>Once you are logged in, you should change your password. In the main panel, inside the <strong>Account Information</strong> box, click <strong>Change Password</strong>. Enter the original password you used to log in and a new password, then click <strong>Change</strong>. You can use your new password to log in to your control panel from now on.</p>

<h2 id="step-four-—-using-the-sentora-control-panel">Step Four — Using the Sentora Control Panel</h2>

<p>In this step, we will create a website and set up an email address.</p>

<h3 id="creating-a-website">Creating a Website</h3>

<p>Now we can add a website. Return to the main panel by click <strong>Home</strong> at the top of the screen.</p>

<p>Inside the <strong>Domain Management</strong> box, click <strong>Domains</strong>. You will be greeted with a form to add a domain name. In the <strong>Domain Name</strong> box, enter your domain name, <code><span class="highlight">example.com</span></code>. Press the blue <strong>Create</strong> button to add your domain.</p>

<p>Next, click <strong>Home</strong> in the top navigation bar. Inside the <strong>File Management</strong> box, click <strong>FTP Accounts</strong>. In the form that appears, enter your desired username and password in the <strong>Username</strong> and <strong>Password</strong> boxes. These credentials will be used to login to your FTP servers, to upload and download files to and from your server. In the <strong>Access Type</strong> drop down menu, select <strong>Full Access</strong>. In the <strong>Home directory</strong> radio options, select <strong>Set master home directory</strong>. In the drop down box that appears, make sure <strong>/ (root)</strong> is selected, then click the blue <strong>Create</strong> button.</p>

<p>Now visit <code><span class="highlight">example.com</span></code>. You should see a Sentora-generated page which says <strong>Your hosting space is ready...</strong> at the top.</p>

<p><strong>Note</strong>: Sentora only supports FTP to upload files you want to show on your website. FTP is insecure, as it transmits usernames and passwords in plaintext. You can read <a href="https://indiareads/community/tutorials/how-to-use-filezilla-to-transfer-and-manage-files-securely-on-your-vps">this tutorial</a> for information on FTP security and FileZilla, a popular file transfer tool. However, you can manually add files to <code>/var/zpanel/hostdata/zadmin/public_html/<span class="highlight">example_com</span></code> to add them to your website.</p>

<h3 id="setting-up-email">Setting Up Email</h3>

<p>In the main panel, inside the <strong>Mail</strong> box, click <strong>Mailboxes</strong>.</p>

<p>In the text box next to <strong>Email address:</strong> enter an email username (this will go before the @). In the drop down box, select the domain you added, <code><span class="highlight">example.com</span></code> (this will go after the @). In the <strong>Password</strong> field, enter your desired password.</p>

<p>After you set up your email, you can access the webmail client either by clicking <strong>Webmail</strong> in the control panel homepage, or at <code>http://panel.<span class="highlight">example.com</span>/etc/apps/webmail/</code>. Use the email address you just chose as the username and the password you entered.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you have the Sentora control panel set up on your Droplet, you can spend some time to take a look around your new control panel. Updating Sentora to new versions is easy with the Updates module.</p>

<p>Sentora has many more features, including MySQL, Webalizer, backups, and even the ability to make user and reseller accounts.</p>

    