<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The <code>sudo</code> command provides a mechanism for granting administrator privileges, ordinarily only available to the root user, to normal users. This guide will show you the easiest way to create a new user with sudo access on CentOS, without having to modify your server's <code>sudoers</code> file. If you want to configure sudo for an existing user, simply skip to step 3.</p>

<h2 id="steps-to-create-a-new-sudo-user">Steps to Create a New Sudo User</h2>

<ol>
<li><p>Log in to your server as the <code>root</code> user.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh root@<span class="highlight">server_ip_address</span>
</li></ul></code></pre></li>
<li><p>Use the <code>adduser</code> command to add a new user to your system.</p>

<p>Be sure to replace <span class="highlight">username</span> with the user that you want to create.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">adduser <span class="highlight">username</span>
</li></ul></code></pre>
<ul>
<li><p>Use the <code>passwd</code> command to update the new user's password.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">passwd <span class="highlight">username</span>
</li></ul></code></pre></li>
<li><p>Set and confirm the new user's password at the prompt. A strong password is highly recommended!</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Set password prompts:">Set password prompts:</div>Changing password for user username.
New password:
Retype new password:
passwd: all authentication tokens updated successfully.
</code></pre></li>
</ul></li>
<li><p>Use the <code>usermod</code> command to add the user to the <code>wheel</code> group.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">usermod -aG wheel <span class="highlight">username</span>
</li></ul></code></pre>
<p>By default, on CentOS, members of the <code>wheel</code> group have sudo privileges.</p></li>
<li><p>Test sudo access on new user account</p>

<ul>
<li><p>Use the <code>su</code> command to switch to the new user account.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">su - <span class="highlight">username</span>
</li></ul></code></pre></li>
<li><p>As the new user, verify that you can use sudo by prepending "sudo" to the command that you want to run with superuser privileges.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="username$">sudo <span class="highlight">command_to_run</span>
</li></ul></code></pre></li>
<li><p>For example, you can list the contents of the <code>/root</code> directory, which is normally only accessible to the root user.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="username$">sudo ls -la /root
</li></ul></code></pre></li>
<li><p>The first time you use <code>sudo</code> in a session, you will be prompted for the password of the user account. Enter the password to proceed.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output:">Output:</div>[sudo] password for username:
</code></pre>
<p>If your user is in the proper group and you entered the password correctly, the command that you issued with sudo should run with root privileges.</p></li>
</ul></li>
</ol>

<h2 id="related-tutorials">Related Tutorials</h2>

<p>Here is a link to a more detailed user management tutorial:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-a-centos-7-server">How To Add and Delete Users on a CentOS 7 Server</a></li>
</ul>

    