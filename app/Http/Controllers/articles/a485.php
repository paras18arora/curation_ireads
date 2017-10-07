<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h2>Introduction</h2>

<p>In this tutorial, we are going to configure a mail server using Postfix, Dovecot, MySQL and SpamAssassin on Ubuntu 12.04.</p>

<p>Following this tutorial you'll be able to add virtual domains, users, and aliases. Moreover, your virtual server will be secure from spam hub.</p>

<h3>Prerequisites</h3>
	
<p>Before setting up your mail server, it's necessary your VPS has the following:</p>

<p>
* Domain is forwarding to your server (<a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean" target="_blank">setup domain</a>)<br />
* MySQL installed and configured (<a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu" target="_blank">setup mysql</a>)<br />
<span>* User with root privileges (<a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-12-04" target="_blank">setup new users</a>- omit step 5)<br />
*Configure and identify your FQDN (<a href="https://github.com/IndiaReads-User-Projects/Articles-and-Tutorials/blob/master/set_hostname_fqdn_on_ubuntu_centos.md#setting-the-fully-qualified-domain-name-fqdn" target="_blank">setup FQDN</a>)</span></p>

<p><b>Optional</b>: SSL certificate (<a href="https://indiareads/community/articles/how-to-set-up-apache-with-a-free-signed-ssl-certificate-on-a-vps" target="_blank">setup free signed ssl certificate</a>)</p>

<p><b>Optional</b> ( Log in as root user )</p>

<p>Installing packages as the root user is useful because you have all privileges.</p>

<p></p><pre><code>sudo -i</code></pre>

<p>Introduce your user's password. Once it's successful, you will see that <code>$</code> symbol changes to <code>#</code>.</p>

<h2>Step 1: Install Packages</h2>

<pre><code>apt-get install postfix postfix-mysql dovecot-core dovecot-imapd dovecot-lmtpd dovecot-mysql</code></pre>

<p>When Postfix configuration is prompted choose Internet Site:</p>

<pre><code>
<a href="https://assets.digitalocean.com/tutorial_images/0Z5WbrL.png" imageanchor="1" style="margin-left: 1em; margin-right: 1em;"><span style="font-family: Georgia, Times New Roman, serif;"><img border="0" src="https://assets.digitalocean.com/tutorial_images/0Z5WbrL.png" height="458" width="640" /></span></a>
</code></pre>

<p>Postfix configuration will ask about System mail name – you could use your FDQN or main domain.</p>

<pre><code>
<a href="https://assets.digitalocean.com/tutorial_images/l5KPaWt.png" imageanchor="1" style="margin-left: 1em; margin-right: 1em;"><span style="font-family: Georgia, Times New Roman, serif;"><img border="0" src="https://assets.digitalocean.com/tutorial_images/l5KPaWt.png" height="176" width="640" /></span></a>
</code></pre>
	
<h2>Step 2: Create a MySQL Database, Virtual Domains, Users and Aliases</h2>

<p>After the installation finishes, we are going to create a MySQL database to configure three different tables: one for domains, one for users and the last one for aliases.</p>

<p>We are going to name the database <code>servermail</code>, but you can use whatever name you want.</p>

<p>Create the servermail database:</p>

<p></p><pre><code>mysqladmin -p create servermail</code></pre>

<p>Log in as MySQL root user</p>

<pre><code>mysql -u root -p</code></pre>

<p>Enter your MySQL root's password; if it's successful you will see:</p>

<pre><code>mysql ></code></pre>

<p>First we need to create a new user, specific for mail authentication,  and we are going to give SELECT permission.</p>

<pre><code>mysql > GRANT SELECT ON servermail.* TO 'usermail'@'127.0.0.1' IDENTIFIED BY 'mailpassword';</code></pre>

<p>After that, we need to reload MySQL privileges to ensure it applies those permissions successfully:</p>

<pre><code>mysql > FLUSH PRIVILEGES;</code></pre>

<p>Finally we need to use the database for creating tables and introduce our data:</p>

<pre><code>mysql> USE servermail;</code></pre>

<p>We are going to create a table for the specific domains recognized as authorized domains.</p>

<pre><code>
CREATE TABLE `virtual_domains` (
`id`  INT NOT NULL AUTO_INCREMENT,
`name` VARCHAR(50) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
</code></pre>

<p>We are going to create a table to introduce the users. Here you will add the email address and passwords. It is necessary to associate each user with a domain.</p>

<pre><code>CREATE TABLE `virtual_users` (
`id` INT NOT NULL AUTO_INCREMENT,
`domain_id` INT NOT NULL,
`password` VARCHAR(106) NOT NULL,
`email` VARCHAR(120) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `email` (`email`),
FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;</code></pre>

<p>Finally we are going to create a virtual aliases table to specify all the emails that you are going to forward to the other email.</p> 

<pre><code>
CREATE TABLE `virtual_aliases` (
`id` INT NOT NULL AUTO_INCREMENT,
`domain_id` INT NOT NULL,
`source` varchar(100) NOT NULL,
`destination` varchar(100) NOT NULL,
PRIMARY KEY (`id`),
FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
</code></pre>

<p>We have created the three tables successfully. Now we are going to introduce the data.</p>
	
<h3>Virtual Domains</h3>

<p>Here we are going to introduce your domains inside the virtual_domains table. You can add all the domains you want, but in this tutorial we are going to introduce just the primary domain (example.com) and your FQDN (hostname.example.com).</p>

<pre><code>
INSERT INTO `servermail`.`virtual_domains`
(`id` ,`name`)
VALUES
('1', 'example.com'),
('2', 'hostname.example.com');
</code></pre>

<h3>Virtual Emails</h3>

<p>We are going to introduce the email address and passwords associated for each domain. Make sure you change all the info with your specific information.</p>

<pre><code>
INSERT INTO `servermail`.`virtual_users`
(`id`, `domain_id`, `password` , `email`)
VALUES
('1', '1', ENCRYPT('firstpassword', CONCAT('$6$', SUBSTRING(SHA(RAND()), -16))), 'email1@example.com'),
('2', '1', ENCRYPT('secondpassword', CONCAT('$6$', SUBSTRING(SHA(RAND()), -16))), 'email2@example.com');
</code></pre>

<h3>Virtual Aliases</h3>

<p>We are going to introduce the email address (source) that we are going to forward to the other email address (destination).</p>

<pre><code>
INSERT INTO `servermail`.`virtual_aliases`
(`id`, `domain_id`, `source`, `destination`)
VALUES
('1', '1', 'alias@example.com', 'email1@example.com');
</code></pre>

<p>Exit MySQL</p>

<pre><code> mysql > exit</code></pre>

<h2>Step 3: Configure Postfix</h2>

<p>We are going to configure Postfix to handle the SMTP connections and send the messages for each user introduced in the MySQL Database.</p>

<p>First we need to create a copy of the default file, in case you want to revert to the default configuration.</p>

<pre><code>cp /etc/postfix/main.cf /etc/postfix/main.cf.orig</code></pre>

<p>Open the main.cf file to modify it:</p>

<pre><code>nano /etc/postfix/main.cf</code></pre>

<p>First we need to comment the TLS Parameters and append other parameters. In this tutorial, we are using the Free SSL certificates and the paths that are suggested in the tutorial (<a href="https://indiareads/community/articles/how-to-set-up-apache-with-a-free-signed-ssl-certificate-on-a-vps">link</a>), but you could modify depending your personal configurations.</p>

<pre><code>
# TLS parameters
#smtpd_tls_cert_file=/etc/ssl/certs/ssl-cert-snakeoil.pem
#smtpd_tls_key_file=/etc/ssl/private/ssl-cert-snakeoil.key
#smtpd_use_tls=yes
#smtpd_tls_session_cache_database = btree:${data_directory}/smtpd_scache
#smtp_tls_session_cache_database = btree:${data_directory}/smtp_scache 
smtpd_tls_cert_file=/etc/ssl/certs/dovecot.pem
smtpd_tls_key_file=/etc/ssl/private/dovecot.pem
smtpd_use_tls=yes
smtpd_tls_auth_only = yes
</code></pre>

<p>Then we are going to append the following parameters below the TLS settings that we have changed in the previous step:</p>

<pre><code>
smtpd_sasl_type = dovecot
smtpd_sasl_path = private/auth
smtpd_sasl_auth_enable = yes
smtpd_recipient_restrictions =
permit_sasl_authenticated,
permit_mynetworks,
reject_unauth_destination
</code></pre>

<p>We need to comment the <code>mydestination</code> default settings and replace it with <code>localhost</code>. This change allows your VPS to use the virtual domains inside the MySQL table.</p>

<pre><code>
#mydestination = example.com, hostname.example.com, localhost.example.com, localhost
mydestination = localhost 
</code></pre>

<p>Verify that myhostname parameter is set with your FQDN.</p> 
	
<pre><code>
myhostname = hostname.example.com
</code></pre>

<p>Append the following line for local mail delivery to all virtual domains listed inside the MySQL table.</p>

<pre><code>virtual_transport = lmtp:unix:private/dovecot-lmtp</code></pre>

<p>Finally, we need to add these three parameters to tell Postfix to configure the virtual domains, users and aliases.</p>

<pre><code>
virtual_mailbox_domains = mysql:/etc/postfix/mysql-virtual-mailbox-domains.cf
virtual_mailbox_maps = mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf
virtual_alias_maps = mysql:/etc/postfix/mysql-virtual-alias-maps.cf
</code></pre>

<p>Note: Compare these changes with this file to detect mistakes or errors:</p>

<pre><code><a href="https://www.dropbox.com/s/x9fpm9v1dr86gkw/etc-postfix-main.cf.txt">https://www.dropbox.com/s/x9fpm9v1dr86gkw/etc-postfix-main.cf.txt</a></code></pre>

<p>We are going to create the final three files that we append in the main.cf file to tell Postfix how to connect with MySQL.</p>

<p>First we need to create the <code>mysql-virtual-mailbox-domains.cf</code> file. It's necessary to change the values depending your personal configuration.</p>

<pre><code>
nano /etc/postfix/mysql-virtual-mailbox-domains.cf
		
user = usermail
password = mailpassword
hosts = 127.0.0.1
dbname = servermail
query = SELECT 1 FROM virtual_domains WHERE name='%s'
</code></pre>

<p>Then we need to restart Postfix.</p>

<pre><code>
service postfix restart
</code></pre>

<p>We need to ensure that Postfix finds your domain, so we need to test it with the following command. If it is successful, it should returns 1:</p>

<pre><code>
postmap -q example.com mysql:/etc/postfix/mysql-virtual-mailbox-domains.cf
</code></pre>

<p>Then we need to create the mysql-virtual-mailbox-maps.cf file.</p>

<pre><code>
nano /etc/postfix/mysql-virtual-mailbox-maps.cf 
		
user = usermail
password = mailpassword
hosts = 127.0.0.1
dbname = servermail
query = SELECT 1 FROM virtual_users WHERE email='%s'
</code></pre>

<p>We need to restart Postfix again.</p>
	
<pre><code>service postfix restart</code></pre>

<p>At this moment we are going to ensure Postfix finds your first email address with the following command. It should return 1 if it's successful:</p>

<pre><code>postmap -q email1@example.com mysql:/etc/postfix/mysql-virtual-mailbox-maps.cf</code></pre>

<p>Finally, we are going to create the last file to configure the connection between Postfix and MySQL.</p>

<pre><code>
nano /etc/postfix/mysql-virtual-alias-maps.cf
		
user = usermail
password = mailpassword
hosts = 127.0.0.1
dbname = servermail
query = SELECT destination FROM virtual_aliases WHERE source='%s'
</code></pre>

<p>Restart Postfix</p>

<pre><code>service postfix restart</code><br /><br /></pre>

<p>We need to verify Postfix can find your aliases. Enter the following command and it should return the mail that's forwarded to the alias:</p>

<pre><code>postmap -q alias@example.com mysql:/etc/postfix/mysql-virtual-alias-maps.cf</code></pre>

<p>If you want to enable port 587 to connect securely with email clients, it is necessary to modify the /etc/postfix/master.cf file</p>

<pre><code>
nano /etc/postfix/master.cf
</code></pre>

<p>We need to uncomment these lines and append other parameters:</p>

<pre><code>
submission inet n       -       -       -       -       smtpd
-o syslog_name=postfix/submission
-o smtpd_tls_security_level=encrypt
-o smtpd_sasl_auth_enable=yes
-o smtpd_client_restrictions=permit_sasl_authenticated,reject
</code></pre>

<p>In some cases, we need to restart Postfix to ensure port 587 is open.</p>

<pre><code>service postfix restart</code></pre>

<p>Note: You can use this tool to scan your domain ports and verify that port 25 and 587 are open (<a href="http://mxtoolbox.com/SuperTool.aspx">http://mxtoolbox.com/SuperTool.aspx</a>)</p>

<h2> Step 4: Configure Dovecot</h2>
	
<p>We are going to copy the 7 files we're going to modify, so that you could revert it to default if you needed to. Enter the following commands one by one:</p>

<pre><code>
cp /etc/dovecot/dovecot.conf /etc/dovecot/dovecot.conf.orig
cp /etc/dovecot/conf.d/10-mail.conf /etc/dovecot/conf.d/10-mail.conf.orig
cp /etc/dovecot/conf.d/10-auth.conf /etc/dovecot/conf.d/10-auth.conf.orig
cp /etc/dovecot/dovecot-sql.conf.ext /etc/dovecot/dovecot-sql.conf.ext.orig
cp /etc/dovecot/conf.d/10-master.conf /etc/dovecot/conf.d/10-master.conf.orig
cp /etc/dovecot/conf.d/10-ssl.conf /etc/dovecot/conf.d/10-ssl.conf.orig
</code></pre>

<p>Edit configuration file from Dovecot.</p>

<pre><code>nano /etc/dovecot/dovecot.conf</code></pre>

<p>Verify this option is uncommented.</p>

<pre><code>!include conf.d/*.conf</code></pre>

<p>We are going to enable protocols (add pop3 if you want to) below the <code>!include_try /usr/share/dovecot/protocols.d/*.protocol line</code>.</p>

<pre><code>
!include_try /usr/share/dovecot/protocols.d/*.protocol
protocols = imap lmtp
</code></pre>

<p>Note: Compare these changes with this file to detect mistakes or errors:</p>

<pre><code>
<a href="https://www.dropbox.com/s/wmbe3bwy0vcficj/etc-dovecot-dovecot.conf.txt">https://www.dropbox.com/s/wmbe3bwy0vcficj/etc-dovecot-dovecot.conf.txt</a>
</code></pre>

<p>Then we are going to edit the mail configuration file:</p>

<pre><code>nano /etc/dovecot/conf.d/10-mail.conf</code></pre>

<p>Find the <code>mail_location</code> line, uncomment it, and put the following parameter:</p>

<pre><code>mail_location = maildir:/var/mail/vhosts/%d/%n</code></pre>

<p>Find the <code>mail_privileged_group</code> line, uncomment it, and add the mail parameter like so:</p>

<pre><code>mail_privileged_group = mail</code></pre>

<p>Note: Compare these changes with this file to detect mistakes or errors:</p>

<pre><code>
<a href="https://www.dropbox.com/s/hnfeieuy77m5b0a/etc.dovecot.conf.d-10-mail.conf.txt">https://www.dropbox.com/s/hnfeieuy77m5b0a/etc.dovecot.conf.d-10-mail.conf.txt</a>
</code></pre>

<h3>Verify permissions</h3>

<p>Enter this command:</p>

<pre><code>
ls -ld /var/mail
</code></pre>

<p>Ensure permissions are like this:</p>

<pre><code>drwxrwsr-x 3 root vmail 4096 Jan 24 21:23 /var/mail</code></pre>

<p>We are going to create a folder for each domain that we register in the MySQL table:</p>

<pre><code>mkdir -p /var/mail/vhosts/example.com</code></pre>

<p>Create a vmail user and group with an id of 5000</p>

<pre><code>
groupadd -g 5000 vmail 
useradd -g vmail -u 5000 vmail -d /var/mail
</code></pre>

<p>We need to change the owner of the <code>/var/mail</code> folder to the vmail user.</p>

<pre><code>chown -R vmail:vmail /var/mail</code></pre>

<p>Then we need to edit the <code>/etc/dovecot/conf.d/10-auth.conf</code> file:</p>

<pre><code>nano /etc/dovecot/conf.d/10-auth.conf</code></pre>
	
<p>Uncomment plain text authentication and add this line:</p>

<pre><code>disable_plaintext_auth = yes</code></pre>

<p>Modify <code>auth_mechanisms</code> parameter:</p>

<pre><code>auth_mechanisms = plain login</code></pre>

<p>Comment this line:</p>

<pre><code>#!include auth-system.conf.ext</code></pre>

<p>Enable MySQL authorization by uncommenting this line:</p>

<pre><code>!include auth-sql.conf.ext</code></pre>

<p>Note: Compare these changes with this file to detect mistakes or errors:</p>

<pre><code><a href="https://www.dropbox.com/s/4h472nqrj700pqk/etc.dovecot.conf.d.10-auth.conf.txt">https://www.dropbox.com/s/4h472nqrj700pqk/etc.dovecot.conf.d.10-auth.conf.txt</a></code></pre>

<p>We need to create the /etc/dovecot/dovecot-sql.conf.ext file with your information for authentication:</p>

<pre><code>nano /etc/dovecot/conf.d/auth-sql.conf.ext</code></pre>

<p>Enter the following code in the file:</p>

<pre><code>
passdb {
  driver = sql
  args = /etc/dovecot/dovecot-sql.conf.ext
}
userdb {
  driver = static
  args = uid=vmail gid=vmail home=/var/mail/vhosts/%d/%n
} 
</code></pre>

<p>We need to modify the <code>/etc/dovecot/dovecot-sql.conf.ext</code> file with our custom MySQL information:</p>

<pre><code>nano /etc/dovecot/dovecot-sql.conf.ext</code></pre>

<p>Uncomment the driver parameter and set mysql as parameter:</p>

<pre><code>driver = mysql</code></pre>

<p>Uncomment the connect line and introduce your MySQL specific information:</p>

<pre><code>connect = host=127.0.0.1 dbname=servermail user=usermail password=mailpassword</code></pre>

<p>Uncomment the <code>default_pass_scheme</code> line and change it to <code>SHA-512</code>.</p>

<pre><code>default_pass_scheme = SHA512-CRYPT</code></pre>

<p>Uncomment the <code>password_query</code> line and add this information:</p>

<pre><code>password_query = SELECT email as user, password FROM virtual_users WHERE email='%u';</code></pre>

<p>Note: Compare these changes with this file to detect mistakes or errors:</p>

<pre><code><a href="https://www.dropbox.com/s/48a5r0mtgdz25cz/etc.dovecot.dovecot-sql.conf.ext.txt">https://www.dropbox.com/s/48a5r0mtgdz25cz/etc.dovecot.dovecot-sql.conf.ext.txt</a></code></pre>

<p>Change the owner and the group of the dovecot folder to vmail user:</p>

<pre><code>
chown -R vmail:dovecot /etc/dovecot
chmod -R o-rwx /etc/dovecot 
</code></pre>

<p>Open and modify the <code>/etc/dovecot/conf.d/10-master.conf</code> file (be careful because different parameters will be changed).</p>

<pre><code>
nano /etc/dovecot/conf.d/10-master.conf

##Uncomment inet_listener_imap and modify to port 0
service imap-login {
  inet_listener imap {
    port = 0
}

#Create LMTP socket and this configurations
service lmtp {
   unix_listener /var/spool/postfix/private/dovecot-lmtp {
	   mode = 0600
	   user = postfix
	   group = postfix
   }
  #inet_listener lmtp {
    # Avoid making LMTP visible for the entire internet
    #address =
    #port =
  #}
} 
</code></pre>
		
<p>Modify <code>unix_listener</code> parameter to <code>service_auth</code> like this:</p>

<pre><code>
service auth {

  unix_listener /var/spool/postfix/private/auth {
  mode = 0666
  user = postfix
  group = postfix
  }

  unix_listener auth-userdb {
  mode = 0600
  user = vmail
  #group =
  }

  #unix_listener /var/spool/postfix/private/auth {
  # mode = 0666
  #}

  user = dovecot
}
</code></pre>
		
<p>Modify <code>service auth-worker</code> like this:</p>

<pre><code>
service auth-worker {
  # Auth worker process is run as root by default, so that it can access
  # /etc/shadow. If this isn't necessary, the user should be changed to
  # $default_internal_user.
  user = vmail
}
</code></pre>

<p> Note: Compare these changes with this file to detect mistakes or errors:</p>

<pre><code><a href="https://www.dropbox.com/s/g0vnt233obh6v2h/etc.dovecot.conf.d.10-master.conf.txt">https://www.dropbox.com/s/g0vnt233obh6v2h/etc.dovecot.conf.d.10-master.conf.txt</a></code></pre>

<p>Finally, we are going to modify the SSL configuration file from Dovecot (skip this step if you are going to use default configuration).</p>

<pre><code># nano /etc/dovecot/conf.d/10-ssl.conf</code></pre>

<p>Change the ssl parameter to required:</p>

<pre><code>ssl = required</code></pre>

<p>And modify the path for <code>ssl_cert</code> and <code>ssl_key</code>:</p>

<pre><code>ssl_cert = <<span>/etc/ssl/certs/dovecot.pem</span></code><br />
ssl_key = <<span>/etc/ssl/private/dovecot.pem</span></pre>

<p>Restart Dovecot</p>

<pre><code>service dovecot restart</code></pre>

<p>You should check that port 993 is open and working (in case you enable pop3; you should check also port 995).</p>
	
<pre><code>telnet example.com 993</code></pre>
	
<p><b>Congratulations.</b> You have successfully configured your mail server and you may test your account using an email client:</p>

<pre>
- Username: email1@example.com
- Password: email1's password
- IMAP: example.com
- SMTP: example.com
</pre>

<p>Note: use port 993 for secure IMAP and port 587 or 25 for SMTP.</p>

<h2>Step 5: Configure SpamAssassin</h2>

<p>First we need to install SpamAssassin.</p>

<pre><code>apt-get install spamassassin spamc</code></pre>
	
<p>Then we need to create a user for SpamAssassin.</p>

<pre><code>adduser spamd --disabled-login</code></pre>
	
<p>To successfully configure SpamAssassin, it's necessary to open and modify the configuration settings.</p>

<pre><code>nano /etc/default/spamassassin</code></pre>

<p>We need to change the <code>ENABLED</code> parameter to enable SpamAssassin daemon.</p>

<pre><code>ENABLED=1</code></pre>

<p>We need to configure the home and options parameters.</p>

<pre><code>
SPAMD_HOME="/home/spamd/"
OPTIONS="--create-prefs --max-children 5 --username spamd --helper-home-dir ${SPAMD_HOME} -s ${SPAMD_HOME}spamd.log" 
</code></pre>

<p>Then we need to specify the <code>PID_File</code> parameter like this:</p>

<pre><code>PIDFILE="${SPAMD_HOME}spamd.pid"</code></pre>

<p>Finally, we need to specify that SpamAssassin's rules will be updated automatically.</p>

<pre><code>CRON=1</code></pre>

<p>Note: Compare these changes with this file to detect mistakes or errors:</p>

<pre><code><a href="https://www.dropbox.com/s/ndvpgc2jipdd4bk/etc.default.spamassassin.txt">https://www.dropbox.com/s/ndvpgc2jipdd4bk/etc.default.spamassassin.txt</a></code></pre>

<p>We need to open <code>/etc/spamassassin/local.cf</code> to set up the anti-spam rules.</p>

<pre><code>nano /etc/spamassassin/local.cf</code></pre>
	
<p>SpamAssassin will score each mail and if it determines this email is greater than 5.0 on its spam check, then it automatically will be considered spam. You could use the following parameters to configure the anti-spam rules:</p>

<pre><code>
rewrite_header Subject ***** SPAM _SCORE_ *****
report_safe             0
required_score          5.0
use_bayes               1
use_bayes_rules         1
bayes_auto_learn        1
skip_rbl_checks         0
use_razor2              0
use_dcc                 0
use_pyzor               0
</code></pre>

<p>We need to change the Postfix <code>/etc/postfix/master.cf</code> file to tell it that each email will be checked with SpamAssassin.</p>

<pre><code>nano /etc/postfix/master.cf</code></pre>

<p>Then we need to find the following line and add the spamassassin filter:</p>

<pre><code>
smtp      inet  n       -       -       -       -       smtpd
-o content_filter=spamassassin
</code></pre>

<p>Finally we need to append the following parameters:</p>

<pre><code>
spamassassin unix -     n       n       -       -       pipe
user=spamd argv=/usr/bin/spamc -f -e  
/usr/sbin/sendmail -oi -f ${sender} ${recipient}
</code></pre>

<p>It is necessary to start SpamAssassin and restart Postfix to begin verifying spam from emails.</p>

<pre><code>
service spamassassin start
service postfix restart
</code></pre>

<p><b>Congratulations!</b> You have successfully set up your mail server with Postfix and Dovecot with MySQL authentication and spam filtering with SpamAssassin!</p>
</div>