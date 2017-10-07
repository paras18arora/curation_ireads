<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/ubuntu16.04_twitter.png?1461608114/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When you first create a new Ubuntu 16.04 server, there are a few configuration steps that you should take early on as part of the basic setup.  This will increase the security and usability of your server and will give you a solid foundation for subsequent actions.</p>

<h2 id="step-one-—-root-login">Step One — Root Login</h2>

<p>To log into your server, you will need to know your server's public IP address. You will also need the password or, if you installed an SSH key for authentication, the private key for the "root" user's account. If you have not already logged into your server, you may want to follow the first tutorial in this series, <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">How to Connect to Your Droplet with SSH</a>, which covers this process in detail.</p>

<p>If you are not already connected to your server, go ahead and log in as the <code>root</code> user using the following command (substitute the highlighted word with your server's public IP address):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh root@<span class="highlight">SERVER_IP_ADDRESS</span>
</li></ul></code></pre>
<p>Complete the login process by accepting the warning about host authenticity, if it appears, then providing your root authentication (password or private key). If it is your first time logging into the server with a password, you will also be prompted to change the root password.</p>

<h3 id="about-root">About Root</h3>

<p>The root user is the administrative user in a Linux environment that has very broad privileges.  Because of the heightened privileges of the root account, you are actually <em>discouraged</em> from using it on a regular basis.  This is because part of the power inherent with the root account is the ability to make very destructive changes, even by accident.</p>

<p>The next step is to set up an alternative user account with a reduced scope of influence for day-to-day work.  We'll teach you how to gain increased privileges during the times when you need them.</p>

<h2 id="step-two-—-create-a-new-user">Step Two — Create a New User</h2>

<p>Once you are logged in as <code>root</code>, we're prepared to add the new user account that we will use to log in from now on.</p>

<p>This example creates a new user called "sammy", but you should replace it with a username that you like:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">adduser <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>You will be asked a few questions, starting with the account password. </p>

<p>Enter a strong password and, optionally, fill in any of the additional information if you would like.  This is not required and you can just hit <code>ENTER</code> in any field you wish to skip.</p>

<h2 id="step-three-—-root-privileges">Step Three — Root Privileges</h2>

<p>Now, we have a new user account with regular account privileges.  However, we may sometimes need to do administrative tasks.</p>

<p>To avoid having to log out of our normal user and log back in as the root account, we can set up what is known as "superuser" or root privileges for our normal account.  This will allow our normal user to run commands with administrative privileges by putting the word <code>sudo</code> before each command. </p>

<p>To add these privileges to our new user, we need to add the new user to the "sudo" group. By default, on Ubuntu 16.04, users who belong to the "sudo" group are allowed to use the <code>sudo</code> command.</p>

<p>As <code>root</code>, run this command to add your new user to the <em>sudo</em> group (substitute the highlighted word with your new user):</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">usermod -aG sudo <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>Now your user can run commands with superuser privileges! For more information about how this works, check out <a href="https://indiareads/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos">this sudoers tutorial</a>.</p>

<p>If you want to increase the security of your server, follow the rest of the steps in this tutorial.</p>

<h2 id="step-four-—-add-public-key-authentication-recommended">Step Four — Add Public Key Authentication (Recommended)</h2>

<p>The next step in securing your server is to set up public key authentication for your new user. Setting this up will increase the security of your server by requiring a private SSH key to log in.</p>

<h3 id="generate-a-key-pair">Generate a Key Pair</h3>

<p>If you do not already have an SSH key pair, which consists of a public and private key, you need to generate one. If you already have a key that you want to use, skip to the <em>Copy the Public Key</em> step.</p>

<p>To generate a new key pair, enter the following command at the terminal of your <strong>local machine</strong> (ie. your computer):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh-keygen
</li></ul></code></pre>
<p>Assuming your local user is called "localuser", you will see output that looks like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="ssh-keygen output">ssh-keygen output</div>Generating public/private rsa key pair.
Enter file in which to save the key (/Users/<span class="highlight">localuser</span>/.ssh/id_rsa):
</code></pre>
<p>Hit return to accept this file name and path (or enter a new name).</p>

<p>Next, you will be prompted for a passphrase to secure the key with. You may either enter a passphrase or leave the passphrase blank.</p>

<p><strong>Note:</strong> If you leave the passphrase blank, you will be able to use the private key for authentication without entering a passphrase. If you enter a passphrase, you will need both the private key <em>and</em> the passphrase to log in. Securing your keys with passphrases is more secure, but both methods have their uses and are more secure than basic password authentication.</p>

<p>This generates a private key, <code>id_rsa</code>, and a public key, <code>id_rsa.pub</code>, in the <code>.ssh</code> directory of the <em>localuser</em>'s home directory. Remember that the private key should not be shared with anyone who should not have access to your servers!</p>

<h3 id="copy-the-public-key">Copy the Public Key</h3>

<p>After generating an SSH key pair, you will want to copy your public key to your new server. We will cover two easy ways to do this.</p>

<h4 id="option-1-use-ssh-copy-id">Option 1: Use ssh-copy-id</h4>

<p>If your local machine has the <code>ssh-copy-id</code> script installed, you can use it to install your public key to any user that you have login credentials for.</p>

<p>Run the <code>ssh-copy-id</code> script by specifying the user and IP address of the server that you want to install the key on, like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh-copy-id <span class="highlight">sammy</span>@<span class="highlight">SERVER_IP_ADDRESS</span>
</li></ul></code></pre>
<p>After providing your password at the prompt, your public key will be added to the remote user's <code>.ssh/authorized_keys</code> file. The corresponding private key can now be used to log into the server.</p>

<h4 id="option-2-manually-install-the-key">Option 2: Manually Install the Key</h4>

<p>Assuming you generated an SSH key pair using the previous step, use the following command at the terminal of your <strong>local machine</strong> to print your public key (<code>id_rsa.pub</code>):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cat ~/.ssh/id_rsa.pub
</li></ul></code></pre>
<p>This should print your public SSH key, which should look something like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="id_rsa.pub contents">id_rsa.pub contents</div>ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDBGTO0tsVejssuaYR5R3Y/i73SppJAhme1dH7W2c47d4gOqB4izP0+fRLfvbz/tnXFz4iOP/H6eCV05hqUhF+KYRxt9Y8tVMrpDZR2l75o6+xSbUOMu6xN+uVF0T9XzKcxmzTmnV7Na5up3QM3DoSRYX/EP3utr2+zAqpJIfKPLdA74w7g56oYWI9blpnpzxkEd3edVJOivUkpZ4JoenWManvIaSdMTJXMy3MtlQhva+j9CgguyVbUkdzK9KKEuah+pFZvaugtebsU+bllPTB0nlXGIJk98Ie9ZtxuY3nCKneB+KjKiXrAvXUPCI9mWkYS/1rggpFmu3HbXBnWSUdf localuser@machine.local
</code></pre>
<p>Select the public key, and copy it to your clipboard.</p>

<p>To enable the use of SSH key to authenticate as the new remote user, you must add the public key to a special file in the user's home directory.</p>

<p><strong>On the server</strong>, as the <strong>root</strong> user, enter the following command to temporarily switch to the new user (substitute your own user name):</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">su - <span class="highlight">sammy</span>
</li></ul></code></pre>
<p>Now you will be in your new user's home directory.</p>

<p>Create a new directory called <code>.ssh</code> and restrict its permissions with the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/.ssh
</li><li class="line" prefix="$">chmod 700 ~/.ssh
</li></ul></code></pre>
<p>Now open a file in <code>.ssh</code> called <code>authorized_keys</code> with a text editor. We will use <code>nano</code> to edit the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/.ssh/authorized_keys
</li></ul></code></pre>
<p>Now insert your public key (which should be in your clipboard) by pasting it into the editor.</p>

<p>Hit <code>CTRL-x</code> to exit the file, then <code>y</code> to save the changes that you made, then <code>ENTER</code> to confirm the file name.</p>

<p>Now restrict the permissions of the <em>authorized_keys</em> file with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod 600 ~/.ssh/authorized_keys
</li></ul></code></pre>
<p>Type this command <strong>once</strong> to return to the <code>root</code> user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<p>Now your public key is installed, and you can use SSH keys to log in as your user.</p>

<p>To read more about how key authentication works, read this tutorial: <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-linux-server">How To Configure SSH Key-Based Authentication on a Linux Server</a>.</p>

<p>Next, we'll show you how to increase your server's security by disabling password authentication.</p>

<h2 id="step-five-—-disable-password-authentication-recommended">Step Five — Disable Password Authentication (Recommended)</h2>

<p>Now that your new user can use SSH keys to log in, you can increase your server's security by disabling password-only authentication. Doing so will restrict SSH access to your server to public key authentication only. That is, the only way to log in to your server (aside from the console) is to possess the private key that pairs with the public key that was installed.</p>

<p><span class="note"><strong>Note:</strong> Only disable password authentication if you installed a public key to your user as recommended in the previous section, step four. Otherwise, you will lock yourself out of your server!<br /></span></p>

<p>To disable password authentication on your server, follow these steps.</p>

<p>As <strong>root</strong> or <strong>your new sudo user</strong>, open the SSH daemon configuration:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/ssh/sshd_config
</li></ul></code></pre>
<p>Find the line that specifies <code>PasswordAuthentication</code>, uncomment it by deleting the preceding <code>#</code>, then change its value to "no". It should look like this after you have made the change:</p>
<div class="code-label " title="sshd_config — Disable password authentication">sshd_config — Disable password authentication</div><pre class="code-pre "><code langs="">PasswordAuthentication no
</code></pre>
<p>Here are two other settings that are important for key-only authentication and are set by default.  If you haven't modified this file before, you <em>do not</em> need to change these settings:</p>
<div class="code-label " title="sshd_config — Important defaults">sshd_config — Important defaults</div><pre class="code-pre "><code langs="">PubkeyAuthentication yes
ChallengeResponseAuthentication no
</code></pre>
<p>When you are finished making your changes, save and close the file using the method we went over earlier (<code>CTRL-X</code>, then <code>Y</code>, then <code>ENTER</code>).</p>

<p>Type this to reload the SSH daemon:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl reload sshd
</li></ul></code></pre>
<p>Password authentication is now disabled. Your server is now only accessible with SSH key authentication.</p>

<h2 id="step-six-—-test-log-in">Step Six — Test Log In</h2>

<p>Now, before you log out of the server, you should test your new configuration.  Do not disconnect until you confirm that you can successfully log in via SSH.</p>

<p>In a new terminal on your <strong>local machine</strong>, log in to your server using the new account that we created.  To do so, use this command (substitute your username and server IP address):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh <span class="highlight">sammy</span>@<span class="highlight">SERVER_IP_ADDRESS</span>
</li></ul></code></pre>
<p>If you added public key authentication to your user, as described in steps four and five, your private key will be used as authentication. Otherwise, you will be prompted for your user's password.</p>

<p><span class="note"><strong>Note about key authentication:</strong> If you created your key pair with a passphrase, you will be prompted to enter the passphrase for your key. Otherwise, if your key pair is passphrase-less, you should be logged in to your server without a password.<br /></span></p>

<p>Once authentication is provided to the server, you will be logged in as your new user.</p>

<p>Remember, if you need to run a command with root privileges, type "sudo" before it like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo <span class="highlight">command_to_run</span>
</li></ul></code></pre>
<h2 id="step-seven-—-set-up-a-basic-firewall">Step Seven — Set Up a Basic Firewall</h2>

<p>Ubuntu 16.04 servers can use the UFW firewall to make sure only connections to certain services are allowed.  We can set up a basic firewall very easily using this application.</p>

<p>Different applications can register their profiles with UFW upon installation.  These profiles allow UFW to manage these applications by name.  OpenSSH, the service allowing us to connect to our server now, has a profile registered with UFW.</p>

<p>You can see this by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw app list
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Available applications:
  OpenSSH
</code></pre>
<p>We need to make sure that the firewall allows SSH connections so that we can log back in next time.  We can allow these connections by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw allow OpenSSH
</li></ul></code></pre>
<p>Afterwards, we can enable the firewall by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw enable
</li></ul></code></pre>
<p>Type "y" and press ENTER to proceed.  You can see that SSH connections are still allowed by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ufw status
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
OpenSSH (v6)               ALLOW       Anywhere (v6)
</code></pre>
<p>If you install and configure additional services, you will need to adjust the firewall settings to allow acceptable traffic in.  You can learn some common UFW operations  in <a href="https://indiareads/community/tutorials/ufw-essentials-common-firewall-rules-and-commands">this guide</a>.</p>

<h2 id="where-to-go-from-here">Where To Go From Here?</h2>

<p>At this point, you have a solid foundation for your server. You can install any of the software you need on your server now.</p>

    