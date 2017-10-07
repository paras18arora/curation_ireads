<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When you first create a new Debian 8 server, there are a few configuration steps that you should take early on as part of the basic setup.  This will increase the security and usability of your server and will give you a solid foundation for subsequent actions.</p>

<h2 id="step-one-—-root-login">Step One — Root Login</h2>

<p>To log into your server, you will need to know your server's public IP address and the password for the "root" user's account. If you have not already logged into your server, you may want to follow the first tutorial in this series, <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">How to Connect to Your Droplet with SSH</a>, which covers this process in detail.</p>

<p>If you are not already connected to your server, go ahead and log in as the <code>root</code> user using the following command (substitute the highlighted word with your server's public IP address):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh root@<span class="highlight">SERVER_IP_ADDRESS</span>
</li></ul></code></pre>
<p>Complete the login process by accepting the warning about host authenticity, if it appears, then providing your root authentication (password or private key). If it is your first time logging into the server, with a password, you will also be prompted to change the root password.</p>

<h3 id="about-root">About Root</h3>

<p>The root user is the administrative user in a Linux environment that has very broad privileges.  Because of the heightened privileges of the root account, you are actually <em>discouraged</em> from using it on a regular basis.  This is because part of the power inherent with the root account is the ability to make very destructive changes, even by accident.</p>

<p>The next step is to set up an alternative user account with a reduced scope of influence for day-to-day work.  We'll teach you how to gain increased privileges during the times when you need them.</p>

<h2 id="step-two-—-create-a-new-user">Step Two — Create a New User</h2>

<p>Once you are logged in as <code>root</code>, we're prepared to add the new user account that we will use to log in from now on.</p>

<p>This example creates a new user called "demo", but you should replace it with a user name that you like:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">adduser <span class="highlight">demo</span>
</li></ul></code></pre>
<p>You will be asked a few questions, starting with the account password. </p>

<p>Enter a strong password and, optionally, fill in any of the additional information if you would like.  This is not required and you can just hit "ENTER" in any field you wish to skip.</p>

<h2 id="step-three-—-root-privileges">Step Three — Root Privileges</h2>

<p>Now, we have a new user account with regular account privileges.  However, we may sometimes need to do administrative tasks.</p>

<p>To avoid having to log out of our normal user and log back in as the root account, we can set up what is known as "super user" or root privileges for our normal account.  This will allow our normal user to run commands with administrative privileges by putting the word <code>sudo</code> before each command.</p>

<h3 id="install-sudo">Install Sudo</h3>

<p>Debian 8 doesn't come with <code>sudo</code> installed, so let's install it with apt-get.</p>

<p>First, update the apt package index:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">apt-get update
</li></ul></code></pre>
<p>Then use this command to install sudo:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">apt-get install sudo
</li></ul></code></pre>
<p>Now you are able to use the <code>sudo</code> and <code>visudo</code> commands.</p>

<h3 id="grant-sudo-privileges">Grant Sudo Privileges</h3>

<p>To add these privileges to our new user, we need to add the new user to the "sudo" group. By default, on Debian 8, users who belong to the "sudo" group are allowed to use the <code>sudo</code> command.</p>

<p>As <code>root</code>, run this command to add your new user to the <em>sudo</em> group (substitute the highlighted word with your new user):</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">usermod -a -G sudo <span class="highlight">demo</span>
</li></ul></code></pre>
<p>Now your user can run commands with super user privileges! For more information about how this works, check out <a href="https://indiareads/community/tutorials/how-to-edit-the-sudoers-file-on-ubuntu-and-centos">this sudoers tutorial</a>.</p>

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

<h3 id="option-1-use-ssh-copy-id">Option 1: Use ssh-copy-id</h3>

<p>If your local machine has the <code>ssh-copy-id</code> script installed, you can use it to install your public key to any user that you have login credentials for.</p>

<p>Run the <code>ssh-copy-id</code> script by specifying the user and IP address of the server that you want to install the key on, like this:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh-copy-id <span class="highlight">demo</span>@<span class="highlight">SERVER_IP_ADDRESS</span>
</li></ul></code></pre>
<p>After providing your password at the prompt, your public key will be added to the remote user's <code>.ssh/authorized_keys</code> file. The corresponding private key can now be used to log into the server.</p>

<h3 id="option-2-manually-install-the-key">Option 2: Manually Install the Key</h3>

<p>Assuming you generated an SSH key pair using the previous step, use the following command at the terminal of your <strong>local machine</strong> to print your public key (<code>id_rsa.pub</code>):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">cat ~/.ssh/id_rsa.pub
</li></ul></code></pre>
<p>This should print your public SSH key, which should look something like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="id_rsa.pub contents">id_rsa.pub contents</div>ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDBGTO0tsVejssuaYR5R3Y/i73SppJAhme1dH7W2c47d4gOqB4izP0+fRLfvbz/tnXFz4iOP/H6eCV05hqUhF+KYRxt9Y8tVMrpDZR2l75o6+xSbUOMu6xN+uVF0T9XzKcxmzTmnV7Na5up3QM3DoSRYX/EP3utr2+zAqpJIfKPLdA74w7g56oYWI9blpnpzxkEd3edVJOivUkpZ4JoenWManvIaSdMTJXMy3MtlQhva+j9CgguyVbUkdzK9KKEuah+pFZvaugtebsU+bllPTB0nlXGIJk98Ie9ZtxuY3nCKneB+KjKiXrAvXUPCI9mWkYS/1rggpFmu3HbXBnWSUdf localuser@machine.local
</code></pre>
<p>Select the public key, and copy it to your clipboard.</p>

<h3 id="add-public-key-to-new-remote-user">Add Public Key to New Remote User</h3>

<p>To enable the use of SSH key to authenticate as the new remote user, you must add the public key to a special file in the user's home directory.</p>

<p><strong>On the server</strong>, as the <code>root</code> user, enter the following command to switch to the new user (substitute your own user name):</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">su - <span class="highlight">demo</span>
</li></ul></code></pre>
<p>Now you will be in your new user's home directory.</p>

<p>Create a new directory called <code>.ssh</code> and restrict its permissions with the following commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir .ssh
</li><li class="line" prefix="$">chmod 700 .ssh
</li></ul></code></pre>
<p>Now open a file in <em>.ssh</em> called <code>authorized_keys</code> with a text editor. We will use <em>nano</em> to edit the file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano .ssh/authorized_keys
</li></ul></code></pre>
<p>Now insert your public key (which should be in your clipboard) by pasting it into the editor.</p>

<p>Hit <code>CTRL-X</code> to exit the file, then <code>Y</code> to save the changes that you made, then <code>ENTER</code> to confirm the file name.</p>

<p>Now restrict the permissions of the <em>authorized_keys</em> file with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod 600 .ssh/authorized_keys
</li></ul></code></pre>
<p>Type this command <em>once</em> to return to the <code>root</code> user:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<p>Now you may SSH login as your new user, using the private key as authentication.</p>

<p>To read more about how key authentication works, read this tutorial: <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-linux-server">How To Configure SSH Key-Based Authentication on a Linux Server</a>.</p>

<h2 id="step-five-—-configure-ssh">Step Five — Configure SSH</h2>

<p>Now that we have our new account, we can secure our server a little bit by modifying its SSH daemon configuration (the program that allows us to log in remotely) to disallow remote SSH access to the <strong>root</strong> account.</p>

<p>Begin by opening the configuration file with your text editor as root:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">nano /etc/ssh/sshd_config
</li></ul></code></pre>
<p>Here, we have the option to disable root login through SSH.  This is generally a more secure setting since we can now access our server through our normal user account and escalate privileges when necessary.</p>

<p>To disable remote root logins, we need to find the line that looks like this:</p>
<div class="code-label " title="/etc/ssh/sshd_config (before)">/etc/ssh/sshd_config (before)</div><pre class="code-pre "><code langs="">#PermitRootLogin yes
</code></pre>
<p>You can modify this line to "no" like this if you want to disable root login:</p>
<div class="code-label " title="/etc/ssh/sshd_config (after)">/etc/ssh/sshd_config (after)</div><pre class="code-pre "><code langs="">PermitRootLogin no
</code></pre>
<p>Disabling remote root login is highly recommended on every server!</p>

<p>When you are finished making your changes, save and close the file using the method we went over earlier (<code>CTRL-X</code>, then <code>Y</code>, then <code>ENTER</code>).</p>

<h3 id="reload-ssh">Reload SSH</h3>

<p>Now that we have made our changes, we need to restart the SSH service so that it will use our new configuration.</p>

<p>Type this to restart SSH:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">systemctl restart ssh
</li></ul></code></pre>
<p>Now, before we log out of the server, we should <strong>test</strong> our new configuration.  We do not want to disconnect until we can confirm that new connections can be established successfully.</p>

<p>Open a <strong>new</strong> terminal window.  In the new window, we need to begin a new connection to our server.  This time, instead of using the root account, we want to use the new account that we created.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh <span class="highlight">demo</span>@<span class="highlight">SERVER_IP_ADDRESS</span>
</li></ul></code></pre>
<p>You will be prompted for the new user's password that you configured.  After that, you will be logged in as your new user.</p>

<p>Remember, if you need to run a command with root privileges, type "sudo" before it like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo <span class="highlight">command_to_run</span>
</li></ul></code></pre>
<p>If all is well, you can exit your sessions by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<h2 id="where-to-go-from-here">Where To Go From Here?</h2>

<p>At this point, you have a solid foundation for your Debian 8 server. You can install any of the software you need on your server now.</p>

    