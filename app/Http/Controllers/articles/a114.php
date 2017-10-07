<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p><a href="http://vestacp.com/">Vesta</a> is an easy to use open source web hosting control panel that currently supports Ubuntu Linux (12.04 LTS, 13.04 and 13.10), Debian 7, CentOS (5 and 6), and RHEL (5 and 6). It packs a bunch of features by default to allow you to host and manage your websites with ease on your VPS.</p>

<h2 id="1-create-and-setup-a-droplet">1. Create and setup a droplet</h2>

<hr />

<p><a href="https://indiareads/community/articles/how-to-create-your-first-digitalocean-droplet-virtual-server">Create</a> and <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-12-04">configure</a> a new VPS.</p>

<p>Once you have your virtual server up and running, login via ssh with the user you created while configuring your droplet. If you decide to log in as root, you can leave out the "sudo" command in all of the following commands.</p>

<p>Seeing that we have a new VPS running, go ahead and refresh the package indexes:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Now may also be a good time to upgrade any packages that might need it:</p>
<pre class="code-pre "><code langs="">sudo apt-get dist-upgrade
</code></pre>
<h2 id="2-install-vesta">2. Install Vesta</h2>

<hr />

<p>Next, we download the Vesta installation script:</p>
<pre class="code-pre "><code langs="">curl -O http://vestacp.com/pub/vst-install.sh
</code></pre>
<p>After which we run this script:</p>
<pre class="code-pre "><code langs="">sudo bash vst-install.sh
</code></pre>
<p>The install script shows us some information on the software that will be installed:</p>

<p><img src="https://assets.digitalocean.com/articles/Vesta_Ubuntu/1.png" alt="" /></p>

<p>Press "y" and "enter" to proceed with the installation.</p>

<p>You will be prompted to enter a valid email address. Be careful to enter an address you have access to and double-check the address you entered for any typing errors, since you will not be prompted to confirm it. Vesta will use this address to try to send you your login credentials. Don't worry if you have entered the address incorrectly. All the information you will need will be displayed in the terminal window after installation, and you will be able to change the admin email address as soon as Vesta is installed.</p>

<p>Upon completion, you will be presented with the following information:</p>

<p><img src="https://assets.digitalocean.com/articles/Vesta_Ubuntu/2.png" alt="" /></p>

<p>Take note of the address, username, and password. You will need it to log in to the control panel.</p>

<hr />

<p><strong>NOTE</strong></p>

<p>If you gave the users sudo access, you might want to set that up again right now, since the Vesta installation overwrites the sudoers.tmp file to add some configurations. You can read about setting up sudo access in step four on the page.</p>

<hr />

<p>If you don't like the password that Vesta generated for you, you will be able to change it once you are logged in [or you can run the following command at any time]:</p>
<pre class="code-pre "><code langs="">sudo /usr/local/vesta/bin/v-change-user-password admin NEW_PASSWORD
</code></pre>
<p>Just replace NEW_PASSWORD with the password you would like to use for the admin user. The above command is also handy to reset your password, should you ever lose it.</p>

<h2 id="3-log-in-to-vesta">3. Log in to Vesta</h2>

<hr />

<p>Now that Vesta is installed, open your browser and go to the address that Vesta gave you in step two. If you didn't take note of it, the address will be as follows:</p>
<pre class="code-pre "><code langs="">https://<your-server-IP-address-or-URL>:8083
</code></pre>
<p>Notice that the URL starts with <strong>https://</strong> and not <strong>http://</strong>. This means that we are accessing Vesta over a secure connection. This secure (SSL) connection needs a certificate on the VPS to use for securing the data transport. Since we have not set up any certificates yet, an unsigned server generated certificate is used, which is why you will get a warning message from your browser similar to the one in the following screenshot:</p>

<p><img src="https://assets.digitalocean.com/articles/Vesta_Ubuntu/3.png" alt="" /></p>

<p>Just press "Proceed anyway" or the equivalent in your browser, after which you will reach the login page. Login using your credentials that you obtained in step 2:</p>

<p><img src="https://assets.digitalocean.com/articles/Vesta_Ubuntu/4.png" alt="" /></p>

<p>After logging in, you will be presented with the control panel where you can continue to configure users, web sites, DNS servers, databases, etc.</p>

<p><img src="https://assets.digitalocean.com/articles/Vesta_Ubuntu/5.png" alt="" /></p>

<div class="author">Submitted by: Francois De Wet</div>

    