<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>LDAP systems are often used to store user account information.  In fact, some of the most common methods of authenticating to LDAP involve account information stored within LDAP entries.</p>

<p>Whether your LDAP entries are used by external services for account information or are just used for LDAP-specific authorization binds, password management becomes important to understand.  In this guide, we will talk about how to go about modifying an LDAP entry's password.</p>

<h2 id="changing-your-own-user-password">Changing Your Own User Password</h2>

<p>The ability to change passwords is managed by the access controls for the LDAP server.  Typically, LDAP is configured to allow accounts the ability to change their own passwords.  This works well if you, as a user, know your previous password.</p>

<p>We can use the <code>ldappasswd</code> tool to modify user account passwords.  To change your password, you will need to bind to an LDAP user entry and authenticate with the current password.  This follows the same general syntax as the other OpenLDAP tools.</p>

<p>We will have to provide several arguments beyond the conventional bind arguments in order to change the password.  You should specify the old password using one of the following options:</p>

<ul>
<li><strong><code>-a [oldpassword]</code></strong>: The <code>-a</code> flag allows you to supply the old password as part of the request on the command line.</li>
<li><strong><code>-A</code></strong>: This flag is an alternative to the <code>-a</code> flag that will prompt you for the old password when the command is entered.</li>
<li><strong><code>-t [oldpasswordfile]</code></strong>: This flag can be used instead of the above to read the old password from a file.</li>
</ul>

<p>You also need to specify the new password using one of these options:</p>

<ul>
<li><strong><code>-s [newpassword]</code></strong>: The <code>-s</code> flag is used to supply the new password on the command line.</li>
<li><strong><code>-S</code></strong>: This variant of the <code>-s</code> flag will prompt you for the new password when the command is entered.</li>
<li><strong><code>-T [newpasswordfile]</code></strong>: This flag can be used instead of the above to read the new password from a file.</li>
</ul>

<p>Using one option from each group, along with the regular options to specify the server location and the bind entry and password, you can change your LDAP password.  Technically, OpenLDAP does not always need the old password since it is used to bind to the entry, but other LDAP implementations require this, so it is best to set anyways.</p>

<p>Typically, the command will look something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldappasswd -H ldap://<span class="highlight">server_domain_or_IP</span> -x -D "<span class="highlight">user_dn</span>" -W -A -S
</li></ul></code></pre>
<p>This will connect to the specified LDAP server, authenticate with the user DN entry, and then issue a series of prompts.  You will be asked to supply and confirm the old password, the new password, and then you will need to supply the old password again for the actual bind to take place.  Afterwards, your password will change.</p>

<p>Since you are going to be changing your password anyways, it might be easier give your old password on the command line instead of through prompts.  You could do that like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldappasswd -H ldap://<span class="highlight">server_domain_or_IP</span> -x -D "<span class="highlight">user's_dn</span>" -w <span class="highlight">old_passwd</span> -a <span class="highlight">old_passwd</span> -S
</li></ul></code></pre>
<h2 id="changing-a-user-39-s-password-using-the-rootdn-bind">Changing a User's Password Using the RootDN Bind</h2>

<p>The <code>ldappasswd</code> tool also allows you to change another user's password if needed as the LDAP administrator.  Technically, you can bind with any account that has write access to the account's password, but this access is usually limited to the rootDN (administrative) entry and the account itself.</p>

<p>To change another user's password, you need to bind to an entry with elevated privileges and then specify the entry you wish to change.  Usually, you'll be binding to the rootDN (see the next section if you need to find out how to find this account).</p>

<p>The basic <code>ldappasswd</code> command will look very similar, the only difference being that you must specify the entry to change at the end of the command.  You may use the <code>-a</code> or <code>-A</code> options if you have the old password available, but this is often not the case when changing the password for a user.  If you do not have the old password, just leave it off.</p>

<p>For example, if the rootDN for your LDAP server is <code>cn=admin,dc=example,dc=com</code>, and the password you wish to change is for the <code>uid=bob,ou=people,dc=example,dc=com</code> entry, you can type this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldappasswd -H ldap://<span class="highlight">server_domain_or_IP</span> -x -D "<span class="highlight">cn=admin,dc=example,dc=com</span>" -W -S "uid=bob,ou=people,dc=example,dc=com"
</li></ul></code></pre>
<p>You will be prompted for Bob's new password and then you will be prompted for the password needed to bind to the admin entry to make the change.</p>

<h2 id="changing-the-rootdn-password">Changing the RootDN Password</h2>

<p>In the event that you have forgotten your LDAP administrative password, you will need to have root or <code>sudo</code> access on the LDAP system's server to reset it.  Log into your server to get started.</p>

<h3 id="finding-the-current-rootdn-information">Finding the Current RootDN Information</h3>

<p>First, you will have to find the RootDN account and the current RootDN password hash.  This is available in the special <code>cn=config</code> configuration DIT.  We can find the information that we are looking for by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -LLL -Q -Y EXTERNAL -b "cn=config" "(olcRootDN=*)" dn olcRootDN olcRootPW | tee ~/newpasswd.ldif
</li></ul></code></pre>
<p>This should return the rootDN account and password for your DIT.  It will also tell you the configuration database where this is defined.  We also wrote this information to a file in our home directory so that we can modify it once we have the new password hash:</p>
<div class="code-label " title="RootDN and RootPW for DIT">RootDN and RootPW for DIT</div><pre class="code-pre "><code langs="">dn: olcDatabase={1}hdb,cn=config
olcRootDN: cn=admin,dc=example,dc=com
olcRootPW: {SSHA}ncCXAJ5DjfRWgxE9pz9TUCNl2qGQHQT3
</code></pre>
<h3 id="hashing-a-new-password">Hashing a New Password</h3>

<p>Next, we can use the <code>slappasswd</code> utility to hash a new password.  We want to use the same hash that was in the <code>olcRootPW</code> line that we queried, indicated by the prefixed value with braces.  In our case, this is <code>{SSHA}</code>.</p>

<p>Use the <code>slappasswd</code> utility to generate a correct hash for the password we want to use.  We will append our new hash to the end of the file we created with the last command.  You will need to specify the full path to the command if you are using a non-root account:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">/usr/sbin/slappasswd -h <span class="highlight">{SSHA}</span> >> ~/newpasswd.ldif
</li></ul></code></pre>
<p>You will be prompted to enter and confirm the new password you wish to use.  The hashed value will be appended to the end of our file.</p>

<h3 id="changing-the-password-in-the-config-dit">Changing the Password in the Config DIT</h3>

<p>Now, we can edit the file to construct a valid LDIF command to change the password.  Open the file we've been writing to:</p>
<pre class="code-pre "><code langs="">nano ~/newpasswd.ldif
</code></pre>
<p>It should look something like this:</p>
<div class="code-label " title="~/newpasswd.ldif">~/newpasswd.ldif</div><pre class="code-pre "><code langs="">dn: olcDatabase={1}hdb,cn=config
olcRootDN: cn=admin,dc=example,dc=com
olcRootPW: {SSHA}ncCXAJ5DjfRWgxE9pz9TUCNl2qGQHQT3

{SSHA}lieJW/YlN5ps6Gn533tJuyY6iRtgSTQw
</code></pre>
<p>You could possibly have multiple values depending on if your LDAP server has more than one DIT.  If that is the case, use the <code>olcRootDN</code> value to find the correct account that you wish to modify.  Delete the other <code>dn</code>, <code>olcRootDN</code>, <code>olcRootPW</code> triplets if there are any.</p>

<p>After you've confirmed that the <code>olcRootDN</code> line matches the account you are trying to modify, comment it out.  Below it, we will add two lines.  The first one should specify <code>changetype: modify</code>, and the second line should tell LDAP that you are trying to <code>replace: olcRootPW</code>.  It will look like this:</p>
<div class="code-label " title="~/newpasswd.ldif">~/newpasswd.ldif</div><pre class="code-pre "><code langs="">dn: olcDatabase={1}hdb,cn=config
<span class="highlight">#</span>olcRootDN: cn=admin,dc=example,dc=com
<span class="highlight">changetype: modify</span>
<span class="highlight">replace: olcRootPW</span>
olcRootPW: {SSHA}ncCXAJ5DjfRWgxE9pz9TUCNl2qGQHQT3

{SSHA}lieJW/YlN5ps6Gn533tJuyY6iRtgSTQw
</code></pre>
<p>Now, delete the hash that is in the <code>olcRootPW</code> line and replace it with the one you generated below.  Remove any extraneous lines.  It should now look like this:</p>
<div class="code-label " title="~/newpasswd.ldif">~/newpasswd.ldif</div><pre class="code-pre "><code langs="">dn: olcDatabase={1}hdb,cn=config
#olcRootDN: cn=admin,dc=example,dc=com
changetype: modify
replace: olcRootPW
olcRootPW: <span class="highlight">{SSHA}lieJW/YlN5ps6Gn533tJuyY6iRtgSTQw</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, we can apply the change by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapmodify -H ldapi:// -Y EXTERNAL -f ~/newpasswd.ldif
</li></ul></code></pre>
<p>This will change the administrative password within the <code>cn=config</code> DIT. </p>

<h3 id="changing-the-password-in-the-normal-dit">Changing the Password in the Normal DIT</h3>

<p>This has changed the password for the entry within the administrative DIT.  However, we still need to modify the entry within the regular DIT.  Currently both the old and new passwords are valid.  We can fix this by modifying the regular DIT entry using our new credentials.</p>

<p>Open up the LDIF file again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/newpasswd.ldif
</li></ul></code></pre>
<p>Replace the value in the <code>dn:</code> line with the RootDN value that you commented out earlier.  This entry is our new target for the password change.  We will also need to change <strong>both</strong> occurrences of <code>olcRootPW</code> with <code>userPassword</code> so that we are modifying the correct value.  When you are finished, the LDIF file should look like this:</p>
<pre class="code-pre "><code langs="">[output ~/newpasswd.ldif]
dn: <span class="highlight">cn=admin,dc=example,dc=com</span>
changetype: modify
replace: <span class="highlight">userPassword</span>
<span class="highlight">userPassword</span>: {SSHA}lieJW/YlN5ps6Gn533tJuyY6iRtgSTQw
</code></pre>
<p>Save and close the file.</p>

<p>Now, we can modify the password for that entry by binding to it using the new password we set in the config DIT.  You will need to bind to the RootDN entry to perform the operation:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -H ldap:// -x -D "<span class="highlight">cn=admin,dc=example,dc=com</span>" -W -f ~/newpasswd.ldif
</li></ul></code></pre>
<p>You will be prompted for the new password you set in the config DIT.  Once authenticated, the password will be changed, leaving only the new password for authentication purposes.</p>

<h2 id="conclusion">Conclusion</h2>

<p>LDAP is often used for storing account information, so it is important to know how to properly manage passwords.  Most of the time the process is relatively simple, but for more intensive operations, you should still be able to modify the passwords with a little work.</p>

    