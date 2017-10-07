<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>One of the most basic tasks that you should know how to do on a fresh Linux server is add and remove users.  When you create a new system, you are often (such as on IndiaReads Droplets) only given the <strong>root</strong> account by default.</p>

<p>While running as the <strong>root</strong> user gives you a lot of power and flexibility, it is also dangerous and can be destructive.  It is almost always a better idea to add an additional, unprivileged user to do common tasks.  You also should create additional accounts for any other users you may have on your system.  Each user should have a different account.</p>

<p>You can still acquire administrator privileges when you need them through a mechanism called <code>sudo</code>.  In this guide we will cover how to create user accounts, assign <code>sudo</code> privileges, and delete users.</p>

<h2 id="how-to-add-a-user">How To Add a User</h2>

<p>If you are signed in as the <strong>root</strong> user, you can create a new user at any time by typing:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">adduser <span class="highlight">newuser</span>
</li></ul></code></pre>
<p>If you are signed in as a non-root user who has been given <code>sudo</code> privileges, as demonstrated <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">in the initial server setup guide</a>, you can add a new user by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo adduser <span class="highlight">newuser</span>
</li></ul></code></pre>
<p>Either way, you will be asked a series of questions.  The procedure will be:</p>

<ul>
<li>Assign and confirm a password for the new user</li>
<li>Enter any additional information about the new user.  This is entirely optional and can be skipped by hitting <strong>Enter</strong> if you don't wish to utilize these fields.</li>
<li>Finally, you'll be asked to confirm that the information you provided was correct.  Enter <strong>Y</strong> to continue.</li>
</ul>

<p>Your new user is now ready for use!  You can now log in using the password you set up.</p>

<p><span class="note"><strong>Note</strong>: Continue if you need your new user to have access to administrative functionality.<br /></span></p>

<h2 id="how-to-grant-a-user-sudo-privileges">How To Grant a User Sudo Privileges</h2>

<p>If your new user should have the ability to execute commands with root (administrative) privileges, you will need to give the new user access to <code>sudo</code>.  Let's examine two approaches to this problem:  Adding the user to a pre-defined <code>sudo</code> <em>user group</em>, and specifying privileges on a per-user basis in <code>sudo</code>'s configuration.</p>

<h3 id="add-the-new-user-to-the-sudo-group">Add the New User to the Sudo Group</h3>

<p>By default, <code>sudo</code> on Ubuntu 16.04 systems is configured to extend full privileges to any user in the <strong>sudo</strong> group.</p>

<p>You can see what groups your new user is in with the <code>groups</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">groups newuser
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>newuser : newuser
</code></pre>
<p>By default, a new user is only in their own group, which is created at the time of account creation, and shares a name with the user.  In order to add the user to a new group, we can use the <code>usermod</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">usermod -aG sudo <span class="highlight">newuser</span>
</li></ul></code></pre>
<p>The <code>-aG</code> option here tells <code>usermod</code> to add the user to the listed groups.</p>

<h3 id="test-your-user-39-s-sudo-privileges">Test Your User's Sudo Privileges</h3>

<p>Now, your new user is able to execute commands with administrative privileges.</p>

<p>When signed in as the new user, you can execute commands as your regular user by typing commands as normal:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">some_command
</li></ul></code></pre>
<p>You can execute the same command with administrative privileges by typing <code>sudo</code> ahead of the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo some_command
</li></ul></code></pre>
<p>You will be prompted to enter the password of the regular user account you are signed in as.</p>

<h3 id="specifying-explicit-user-privileges-in-etc-sudoers">Specifying Explicit User Privileges in /etc/sudoers</h3>

<p>As an alternative to putting your user in the <strong>sudo</strong> group, you can use the <code>visudo</code> command, which opens a configuration file called <code>/etc/sudoers</code> in the system's default editor, and explicitly specify privileges on a per-user basis.</p>

<p>Using <code>visudo</code> is the only recommended way to make changes to <code>/etc/sudoers</code>, because it locks the file against multiple simultaneous edits and performs a sanity check on its contents before overwriting the file.  This helps to prevent a situation where you misconfigure <code>sudo</code> and are prevented from fixing the problem because you have lost <code>sudo</code> privileges.</p>

<p>If you are currently signed in as <strong>root</strong>, type:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">visudo
</li></ul></code></pre>
<p>If you are signed in using a non-root user with <code>sudo</code> privileges, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo visudo
</li></ul></code></pre>
<p>Traditionally, <code>visudo</code> opened <code>/etc/sudoers</code> in the <code>vi</code> editor, which can be confusing for inexperienced users.  By default on new Ubuntu installations, it should instead use <code>nano</code>, which provides a more familiar text editing experience.  Use the arrow keys to move the cursor, and search for the line that looks like this:</p>
<div class="code-label " title="/etc/sudoers">/etc/sudoers</div><pre class="code-pre "><code langs="">root    ALL=(ALL:ALL) ALL
</code></pre>
<p>Below this line, copy the format you see here, changing only the word "root" to reference the new user that you would like to give sudo privileges to:</p>
<div class="code-label " title="/etc/sudoers">/etc/sudoers</div><pre class="code-pre "><code langs="">root    ALL=(ALL:ALL) ALL
<span class="highlight">newuser</span> ALL=(ALL:ALL) ALL
</code></pre>
<p>You should add a new line like this for each user that should be given full sudo privileges.  When you are finished, you can save and close the file by hitting <strong>Ctrl-X</strong>, followed by <strong>Y</strong>, and then <strong>Enter</strong> to confirm.</p>

<h2 id="how-to-delete-a-user">How To Delete a User</h2>

<p>In the event that you no longer need a user, it is best to delete the old account.</p>

<p>You can delete the user itself, without deleting any of their files, by typing this as root:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">deluser <span class="highlight">newuser</span>
</li></ul></code></pre>
<p>If you are signed in as another non-root user with sudo privileges, you could instead type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo deluser <span class="highlight">newuser</span>
</li></ul></code></pre>
<p>If, instead, you want to delete the user's home directory when the user is deleted, you can issue the following command as root:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">deluser --remove-home <span class="highlight">newuser</span>
</li></ul></code></pre>
<p>If you're running this as a non-root user with sudo privileges, you would instead type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo deluser --remove-home <span class="highlight">newuser</span>
</li></ul></code></pre>
<p>If you had previously configured sudo privileges for the user you deleted, you may want to remove the relevant line again by typing:</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">visudo
</li></ul></code></pre>
<p>Or use this if you are a non-root user with sudo privileges:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo visudo
</li></ul></code></pre><pre class="code-pre "><code langs="">root    ALL=(ALL:ALL) ALL
newuser ALL=(ALL:ALL) ALL   # DELETE THIS LINE
</code></pre>
<p>This will prevent a new user created with the same name from being accidentally given sudo privileges.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a fairly good handle on how to add and remove users from your Ubuntu 16.04 system.  Effective user management will allow you to separate users and give them only the access that they are required to do their job.</p>

<p>For more information about how to configure <code>sudo</code>, check out our guide on <a href="https://indiareads/community/articles/how-to-edit-the-sudoers-file-on-ubuntu-and-centos">how to edit the sudoers file</a> here.</p>

    