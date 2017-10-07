<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>PowerDNS is an advanced, high performance authoritative nameserver compatible with a number of backends. PowerDNS can use BIND configuration files, read information from MariaDB, MySQL, Oracle, PostgreSQL, and many other databases. Backends can easily be written in any language. In this case we will use MariaDB to store our zone file records.</p>

<p>MariaDB is a fork of MySQL, a relational database management system. Being a fork of a leading open source software system, it is notable for being led by its original developers. MariaDB retains full drop-in replacement capability with MySQL APIs and commands.</p>

<p>At the end of this tutorial, you will have a working PowerDNS nameserver that you can use to host DNS for any number of domains.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you start the tutorial, please follow these prerequisites.</p>

<h3 id="droplet-requirements">Droplet Requirements</h3>

<ul>
<li>512MB Droplet or larger</li>
<li>Ubuntu 14.04 64-bit</li>
</ul>

<p>PowerDNS is designed to be high performance, and low on resource usage. A 512MB Droplet should be plenty to run a PowerDNS server with a moderate amount of zones/records. This Droplet will be running Ubuntu 14.04 64bit.</p>

<h3 id="root-access">Root Access</h3>

<p>The rest of this tutorial will assume you are connected to your server with the <strong>root</strong> user account, or a user account with sudo privileges.</p>

<p>To enter the <strong>root</strong> shell from another account:</p>
<pre class="code-pre "><code langs="">sudo su
</code></pre>
<h3 id="register-your-nameservers-set-nameservers-for-other-domains">Register Your Nameservers, Set Nameservers for Other Domains</h3>

<p>You can do this before or after completing the technical setup, but for your new nameserver to be able to process real DNS requests, you have to register the nameserver domain or subdomain(s) as a nameserver at your registrar, using a glue record. Glue records are discussed in the tutorial linked below, although you will likely want to look up the process for registering nameservers / creating glue records at your registrar.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-create-vanity-or-branded-nameservers-with-digitalocean-cloud-servers">How To Create Vanity or Branded Nameservers with IndiaReads Cloud Servers</a></li>
</ul>

<blockquote>
<p><strong>Note:</strong> When you're setting up a DNS server, it helps to keep your domain names straight. You'll most likely pick three subdomains for use with the nameserver itself. This tutorial uses <strong>hostmaster.example-dns.com</strong>, <strong>ns1.example-dns.com</strong>, and <strong>ns2.example-dns.com</strong>.</p>

<p>We'll also present a domain that uses this nameserver as its SOA. In this tutorial, we'll set up a zone file for <strong>example.com</strong> on your new PowerDNS nameserver.</p>
</blockquote>

<p>This tutorial uses the following domain names as examples.</p>

<p>These three subdomains should have glue records that point to your PowerDNS Droplet's IP address:</p>

<ul>
<li><strong>hostmaster.example-dns.com</strong> </li>
<li><strong>ns1.example-dns.com</strong></li>
<li><p><strong>ns2.example-dns.com</strong></p></li>
<li><p>Then, you should set <strong>example.com</strong>'s nameservers to the three nameservers shown above</p></li>
</ul>

<h2 id="step-1-—-install-updates">Step 1 — Install Updates</h2>

<p>It is always a good idea to make sure you have the latest updates installed.</p>

<p>Install updates:</p>
<pre class="code-pre "><code langs="">apt-get update && apt-get upgrade -y
</code></pre>
<h2 id="step-2-—-install-mariadb">Step 2 — Install MariaDB</h2>

<p>First we will import a key for the MariaDB repository:</p>
<pre class="code-pre "><code langs="">apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db
</code></pre>
<p>Next we will add the MariaDB APT repository:</p>
<pre class="code-pre "><code langs="">add-apt-repository 'deb http://ftp.kaist.ac.kr/mariadb/repo/5.5/ubuntu trusty main'
</code></pre>
<p>Now we can install the MariaDB packages and dependencies (primarily libraries) using <span class="highlight">apt-get</span>:</p>
<pre class="code-pre "><code langs="">apt-get -y install libaio1 libdbd-mysql-perl libdbi-perl libmariadbclient18 libmysqlclient18 libnet-daemon-perl libplrpc-perl mariadb-client-5.5 mariadb-client-core-5.5 mariadb-common mysql-common mariadb-server mariadb-server-5.5 mariadb-server-core-5.5
</code></pre>
<p>During the installation, you will be prompted to set a password for the MariaDB <strong>root</strong> user.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/1.png" alt="Enter a root database password" /> </p>

<p>Please enter a strong password for the database <strong>root</strong> user, and press ENTER.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/2.png" alt="Enter the same root database password" /> </p>

<p>You will be asked to confirm the new password. Enter the password again, and press ENTER to finish the setup process.</p>

<h2 id="step-3-—-secure-and-configure-mariadb">Step 3 — Secure and Configure MariaDB</h2>

<p>By default MariaDB allows anonymous users and root access from remote clients. We will run the secure installation utility to disable those features.</p>

<p>Run this secure installation wizard:</p>
<pre class="code-pre "><code langs="">mysql_secure_installation
</code></pre>
<p>You will be prompted to authenticate with the MariaDB <strong>root</strong> user password you created during the MariaDB setup. Then, press ENTER to continue. Entries are shown in <span class="highlight">red</span>.</p>
<pre class="code-pre "><code langs="">You already have a root password set, so you can safely answer **n**.

Change the root password? [Y/n] <span class="highlight">n</span>
</code></pre>
<p>In our example we do not want to change the root password; although, if you did not set a password when installing MariaDB, now would be a good time to do so. Otherwise enter N and press ENTER.</p>
<pre class="code-pre "><code langs="">Remove anonymous users? [Y/n]
</code></pre>
<p>It is recommended anonymous users be disabled. Press ENTER to accept the default answer of Y.</p>
<pre class="code-pre "><code langs="">Disallow root login remotely? [Y/n] 
</code></pre>
<p>It is recommended that root not be used to administrate a remote database server. Press ENTER to accept the default answer of Y.</p>
<pre class="code-pre "><code langs="">Remove test database and access to it? [Y/n]
</code></pre>
<p>You can keep the test database if you would like to experiment with MariaDB. In our example we decided to remove it. Press ENTER to accept the default answer of Y.</p>
<pre class="code-pre "><code langs="">Reload privilege tables now? [Y/n] 
</code></pre>
<p>Reloading the privilege tables within the wizard will save us a step. Press ENTER to accept the default answer of Y.</p>

<p>Next we will increase the InnoDB log file size to 64MB. This will help if you have to debug issues in the future.</p>

<p>First we need to stop the MariaDB service:</p>
<pre class="code-pre "><code langs="">service mysql stop
</code></pre>
<p>Remove any existing log files (if this isn't a fresh MariaDB installation, you may want to back them up instead):</p>
<pre class="code-pre "><code langs="">rm -f /var/lib/mysql/ib_logfile*
</code></pre>
<p>Open the config file with nano:</p>
<pre class="code-pre "><code langs="">nano /etc/mysql/my.cnf
</code></pre>
<p>Press CTRL+W to search the file. Enter <span class="highlight">InnoDB</span> into the search field, then press ENTER to continue. You will be taken to the InnoDB portion of the config file. You will need to add the line highlighted in <span class="highlight">red</span> below.</p>
<pre class="code-pre "><code langs=""># * InnoDB
# 
# InnoDB is enabled by default with a 10MB datafile in /var/lib/mysql/.
# Read the manual for more InnoDB related options. There are many!

<span class="highlight">innodb_log_file_size = 64M</span>

# 
# * Security Features

</code></pre>
<p>Press CTRL+X, press Y to save the file, and press ENTER to overwrite.</p>

<p>Finally, start the MariaDB service again:</p>
<pre class="code-pre "><code langs="">service mysql start
</code></pre>
<p>If the startup script returns the status [OK], the log file size has been updated successfully and you are ready to proceed to the next section. </p>

<h2 id="step-4-—-create-the-powerdns-database-and-user-account-in-mariadb">Step 4 — Create the PowerDNS Database and User Account in MariaDB</h2>

<p>Throughout this section and the rest of the tutorial, we will use recommended names like "powerdns" and "powerdns_user". Feel free to substitute your own database and database user names, and make sure you use the updated names throughout.</p>

<p>You should definitely change the password. Be sure to replace text highlighted in <span class="highlight">red</span> with your own information.</p>

<blockquote>
<p><strong>Note:</strong> The MySQL shell will not process a command until you end the line with <strong>;</strong>. You will notice our table commands use multiple lines; this is normal.</p>
</blockquote>

<p>First, authenticate with the MariaDB <strong>root</strong> user:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>Enter the <strong>root</strong> database password, then press ENTER to access the database server.</p>

<p>Create the database. You can use whatever name you want, but we will use powerdns:</p>
<pre class="code-pre "><code langs="">CREATE DATABASE powerdns;
</code></pre>
<p>Create a new user called "powerdns_user" and grant access to the database. You should replace <span class="highlight">powerdns_user_password</span> with a unique password:</p>
<pre class="code-pre "><code langs="">GRANT ALL ON powerdns.* TO 'powerdns_user'@'localhost' IDENTIFIED BY '<span class="highlight">powerdns_user_password</span>';
</code></pre>
<p>Flush the privileges to update the user settings:</p>
<pre class="code-pre "><code langs="">FLUSH PRIVILEGES;
</code></pre>
<p>Use the new <span class="highlight">powerdns</span> database:</p>
<pre class="code-pre "><code langs="">USE powerdns;
</code></pre>
<p>Next, we will add some tables to the database that PowerDNS can use to store its zone file entries.</p>

<p>Create the <strong>domains</strong> table:</p>
<pre class="code-pre "><code langs="">CREATE TABLE domains (
id INT auto_increment,
name VARCHAR(255) NOT NULL,
master VARCHAR(128) DEFAULT NULL,
last_check INT DEFAULT NULL,
type VARCHAR(6) NOT NULL,
notified_serial INT DEFAULT NULL,
account VARCHAR(40) DEFAULT NULL,
primary key (id)
);
</code></pre>
<p>Set the unique index:</p>
<pre class="code-pre "><code langs="">CREATE UNIQUE INDEX name_index ON domains(name);
</code></pre>
<p>Create the <strong>records</strong> table:</p>
<pre class="code-pre "><code langs="">CREATE TABLE records (
id INT auto_increment,
domain_id INT DEFAULT NULL,
name VARCHAR(255) DEFAULT NULL,
type VARCHAR(6) DEFAULT NULL,
content VARCHAR(255) DEFAULT NULL,
ttl INT DEFAULT NULL,
prio INT DEFAULT NULL,
change_date INT DEFAULT NULL,
primary key(id)
);
</code></pre>
<p>Set the indexes:</p>
<pre class="code-pre "><code langs="">CREATE INDEX rec_name_index ON records(name);
CREATE INDEX nametype_index ON records(name,type);
CREATE INDEX domain_id ON records(domain_id);
</code></pre>
<p>Create the <strong>supermasters</strong> table:</p>
<pre class="code-pre "><code langs="">CREATE TABLE supermasters (
ip VARCHAR(25) NOT NULL,
nameserver VARCHAR(255) NOT NULL,
account VARCHAR(40) DEFAULT NULL
);
</code></pre>
<p>Now we can exit the MySQL shell:</p>
<pre class="code-pre "><code langs="">quit;
</code></pre>
<h2 id="step-5-—-install-powerdns">Step 5 — Install PowerDNS</h2>

<p>As mentioned earlier, MariaDB is a drop-in replacement for MySQL. So, we'll install the main PowerDNS module, as well as the corresponding MySQL backend module.</p>

<p>Install PowerDNS:</p>
<pre class="code-pre "><code langs="">apt-get install -y pdns-server pdns-backend-mysql
</code></pre>
<blockquote>
<p><strong>Note:</strong> If you are prompted with dependency errors regarding <span class="highlight">mysql-client</span>, the following command will remove the conflicting package and force the installation of PowerDNS packages.</p>
<pre class="code-pre "><code langs="">apt-get -f purge -y mysql-client
</code></pre></blockquote>

<p>You will be prompted to configure the MySQL backend. We will perform this process manually in a moment, so use the arrow keys to select <strong><No></strong>, and press ENTER to finish the installation.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/3.png" alt="Select <No>" /> </p>

<h2 id="step-6-—-configure-powerdns">Step 6 — Configure PowerDNS</h2>

<p>We have to configure PowerDNS to use our new database.</p>

<p>First, remove the existing configuration files:</p>
<pre class="code-pre "><code langs="">rm /etc/powerdns/pdns.d/*
</code></pre>
<p>Now we can create the MariaDB configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/powerdns/pdns.d/pdns.local.gmysql.conf
</code></pre>
<p>Enter the following data into the file. Remember to add your own database settings for <span class="highlight">gmysql-dbname</span>, <span class="highlight">gmysql-user</span>, and especially <span class="highlight">gmysql-password</span>.</p>
<pre class="code-pre "><code langs=""># MySQL Configuration file

launch=gmysql

gmysql-host=localhost
gmysql-dbname=powerdns
gmysql-user=powerdns_user
gmysql-password=<span class="highlight">powerdns_user_password</span>
</code></pre>
<p>Restart PowerDNS to apply changes:</p>
<pre class="code-pre "><code langs="">service pdns restart
</code></pre>
<h2 id="step-7-—-test-powerdns">Step 7 — Test PowerDNS</h2>

<p>These steps are a good sanity check to make sure PowerDNS is installed and can connect to the database. If you do not pass the following tests, then something is wrong with your database configuration. Repeat Steps 4 and 6 to resolve the problem.</p>

<p>Check if PowerDNS is listening:</p>
<pre class="code-pre "><code langs="">netstat -tap | grep pdns
</code></pre>
<p>You should see an output similar to:</p>
<pre class="code-pre "><code langs="">root@ns1:~# netstat -tap | grep pdns
tcp        0      0 *:domain                *:*                     LISTEN      5525/pdns_server-in
</code></pre>
<p>Check if PowerDNS responds correctly:</p>
<pre class="code-pre "><code langs="">dig @127.0.0.1
</code></pre>
<p>You should see an output similar to:</p>
<pre class="code-pre "><code langs="">root@ns1:~# dig @127.0.0.1

; <<>> DiG 9.9.5-3-Ubuntu <<>> @127.0.0.1
; (1 server found)
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 27248
;; flags: qr rd; QUERY: 1, ANSWER: 0, AUTHORITY: 0, ADDITIONAL: 1
;; WARNING: recursion requested but not available

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 2800
;; QUESTION SECTION:
;.              IN  NS

;; Query time: 1 msec
;; SERVER: 127.0.0.1#53(127.0.0.1)
;; WHEN: Sun Nov 02 18:58:20 EST 2014
;; MSG SIZE  rcvd: 29
</code></pre>
<p>Did everything check out? Great! Let's keep going.</p>

<h2 id="step-8-—-install-poweradmin">Step 8 — Install Poweradmin</h2>

<p>Poweradmin is a web-based DNS administration tool for PowerDNS. It has full support for all zone types (<a href="http://downloads.powerdns.com/documentation/html/master.html">master</a>, <a href="http://downloads.powerdns.com/documentation/html/replication.html#native-replication">native</a>, and <a href="http://downloads.powerdns.com/documentation/html/slave.html">slave</a>). It has full supermaster support for automatic provisioning of slave zones, full support for IPv6, and multiple languages. You can view the <a href="http://www.poweradmin.org/features.html">feature list</a> for more details.</p>

<p>Install Apache and the required dependencies for Poweradmin:</p>
<pre class="code-pre "><code langs="">apt-get install -y apache2 gettext libapache2-mod-php5 php5 php5-common php5-curl php5-dev php5-gd php-pear php5-imap  php5-ming php5-mysql php5-xmlrpc php5-mhash php5-mcrypt 
</code></pre>
<p>Install the required PEAR modules:</p>
<pre class="code-pre "><code langs="">pear install DB
</code></pre><pre class="code-pre "><code langs="">pear install pear/MDB2#mysql
</code></pre>
<p>Enable Mcrypt:</p>
<pre class="code-pre "><code langs="">php5enmod mcrypt
</code></pre>
<p>Restart Apache to apply the changes:</p>
<pre class="code-pre "><code langs="">service apache2 restart
</code></pre>
<p>Change to your home directory:</p>
<pre class="code-pre "><code langs="">cd ~
</code></pre>
<p>Download the compressed Poweradmin files:</p>
<pre class="code-pre "><code langs="">wget https://github.com/downloads/poweradmin/poweradmin/poweradmin-2.1.6.tgz
</code></pre>
<p>Extract the archive:</p>
<pre class="code-pre "><code langs="">tar xvzf poweradmin-2.1.6.tgz
</code></pre>
<p>Move the <code>poweradmin</code> directory to the Apache web directory:</p>
<pre class="code-pre "><code langs="">mv poweradmin-2.1.6 /var/www/html/poweradmin
</code></pre>
<p>Create the configuration file:</p>
<pre class="code-pre "><code langs="">touch /var/www/html/poweradmin/inc/config.inc.php
</code></pre>
<p>Give the Apache user ownership of the directory:</p>
<pre class="code-pre "><code langs="">chown -R www-data:www-data /var/www/html/poweradmin/
</code></pre>
<h2 id="step-9-—-configure-poweradmin">Step 9 — Configure Poweradmin</h2>

<p>To finish the installation of Poweradmin we will use the web-based configuration wizard.</p>

<p>Open your web browser and visit the URL below, substituting your own IP address or server hostname:</p>

<ul>
<li><code>http://<span class="highlight">your_server_ip</span>/poweradmin/install/</code></li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/4.png" alt="Select your preferred language" /> </p>

<p>Select your preferred language and click the <strong>Go to step 2</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/5.png" alt="Read this information" /> </p>

<p>There is some valuable information on the <strong>step 2</strong> page, especially for multiple installations of Poweradmin. This information does not directly apply to this tutorial. When you are done reading the page, click the <strong>Go to step 3</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/6.png" alt="Enter the database connection information and create a new password as explained below" /> </p>

<p>On the <strong>Installation step 3</strong> page, we will need to enter the following information:</p>

<ul>
<li><strong>Username</strong>: <span class="highlight">powerdns_user</span>, or whatever username you created for MariaDB </li>
<li><strong>Password</strong>: <span class="highlight">powerdns<em>user</em>password</span>, the database password you created earlier</li>
<li><strong>Database type</strong>: Select <strong>MySQL</strong> from the dropdown menu; remember that MariaDB acts like MySQL</li>
<li><strong>Hostname</strong>: <span class="highlight">127.0.0.1</span> because we are connecting from localhost</li>
<li><strong>DB Port</strong>: <span class="highlight">3306</span>; leave the default</li>
<li><strong>Database</strong>: <span class="highlight">powerdns</span>, or the database name you created earlier</li>
<li><strong>Poweradmin administrator password</strong>: Please set a unique password that you will use to log into the Poweradmin control panel later on; the username will be <strong>admin</strong></li>
</ul>

<p>Click the <strong>Go to step 4</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/7.png" alt="Enter the database and nameserver details as explained below" /> </p>

<p>On the <strong>Installation step 4</strong> page you have a choice between convenience and security. You can reuse the same database settings, or create a new less-privileged database user for Poweradmin. This example shows the same database user settings. You'll also choose your nameserver domains.</p>

<ul>
<li><strong>Username</strong>: Use a new or existing database user; in this case we're using <code>powerdns_user</code></li>
<li><strong>Password</strong>: Set a new password or use the existing database password of <span class="highlight">powerdns<em>user</em>password</span></li>
<li><strong>Hostmaster</strong>: Set the default hostmaster, such as <strong><span class="highlight">hostmaster.example-dns.com</span></strong></li>
<li><strong>Primary nameserver</strong>: Set the primary nameserver, such as <strong><span class="highlight">ns1.example-dns.com</span></strong></li>
<li><strong>Secondary nameserver</strong>: Set the secondary nameserver, such as <strong><span class="highlight">ns2.example-dns.com</span></strong></li>
</ul>

<p>Click the <strong>Go to step 5</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/8.png" alt="If you created a new user, add the new database user with the command shown on the page, starting with GRANT" /> </p>

<p>Verify that the database information is correct. If you chose to create a new user and password, then you should log into your MariaDB database and add the new user by copying and pasting the code block shown on the screen, starting with GRANT. Then click the <strong>Go to step 6</strong> button.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/9.png" alt="The installer was able to write to the file "../inc/config.inc.php" . . ." /> </p>

<p>You should see a message like <strong>The installer was able to write to the file "../inc/config.inc.php" . . .</strong>. If you have issues writing to the configuration file, that means you missed a step during the installation process.</p>

<p><strong>If this step failed</strong>, go back to your server and create the file:</p>
<pre class="code-pre "><code langs="">touch /var/www/html/poweradmin/inc/config.inc.php
</code></pre>
<p>Then restart the installation process again by refreshing the page.</p>

<p>Otherwise, click the <strong>Go to step 7</strong> button to finish the installation.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/10.png" alt="Now we have finished the configuration . . ." /> </p>

<p>You'll be given the username <strong>admin</strong> and your Poweradmin control panel password.</p>

<p>We are done with the configuration of Poweradmin.</p>

<p>To clean up, go back to your server and delete the installation directory. Poweradmin requires us to do this before we can log in:</p>
<pre class="code-pre "><code langs="">rm -rf /var/www/html/poweradmin/install/
</code></pre>
<h3 id="poweradmin-configuration-changes">Poweradmin Configuration Changes</h3>

<p>If you need to make changes to the Poweradmin settings after finishing the installation, edit this file:</p>
<pre class="code-pre "><code langs="">nano /var/www/html/poweradmin/inc/config.inc.php
</code></pre>
<p>Here you can update the database connection settings and other configuration settings for Poweradmin.</p>

<h2 id="step-10-—-create-your-first-dns-record">Step 10 — Create Your First DNS Record</h2>

<p>Access the Poweradmin control panel:</p>

<ul>
<li><code>http://<span class="highlight">your_server_ip</span>/poweradmin/</code></li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/11.png" alt="Poweradmin login page" /> </p>

<p>Log in to your Poweradmin control panel using the credentials you set up during the configuration. The username is <strong>admin</strong> and the password is the <strong>Poweradmin administrator password</strong> from the <strong>Installation step 3</strong>.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/12.png" alt="Poweradmin home page" /> </p>

<p>Click the <strong>Add Master Zone</strong> link.</p>

<p>Enter the domain name in the <strong>Zone name</strong> field. This domain should be one that for which you want to host a zone file. You can leave all other settings with their default entries. Click the <strong>Add zone</strong> button.</p>

<p>Click the <strong>List zones</strong> link from the top menu.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/13.png" alt="Add Master Zone page" /> </p>

<p>Click the edit button for your zone file, which looks like a small pencil on the left of the zone entry.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/14.png" alt="List zones page" /> </p>

<p>Add a DNS record for your domain.</p>

<p><img src="https://assets.digitalocean.com/articles/powerdns_mariadb/15.png" alt="Add a record page" /> </p>

<ul>
<li>You can add a subdomain in the <strong>Name</strong> field, or leave it blank for the primary domain.</li>
<li>Choose the <strong>Type</strong> of record from the dropdown menu.</li>
<li>Add the IP address, domain name, or other entry in the <strong>Content</strong> field.</li>
<li>Set the <strong>Priority</strong> if needed.</li>
<li>Set the <strong>TTL</strong> in seconds.</li>
</ul>

<p>Click the <strong>Add record</strong> button.</p>

<p>You can add additional records, or go back to the <strong>List zones</strong> page and the edit button for your domain to view all the current records for that domain.</p>

<p>Remember that for this record to actually function, you need to:</p>

<ul>
<li>Register the nameserver domains with glue records</li>
<li>Set the nameservers for this domain to be the new PowerDNS nameserver domains</li>
<li>Wait for propagation</li>
</ul>

<p>However, we can check that the records are correct locally right away.</p>

<h2 id="step-11-—-test-your-dns-record">Step 11 — Test Your DNS Record</h2>

<p>Note: Substitute <span class="highlight">example.com</span> with your own domain or subdomain record.</p>

<p>On your server, look up the record for your domain:</p>
<pre class="code-pre "><code langs="">dig <span class="highlight">example.com</span> A @127.0.0.1
</code></pre>
<p>You should see an output similar to:</p>
<pre class="code-pre "><code langs="">root@ns1:~# dig example.com A <a href="https://indiareads/community/users/127" class="username-tag">@127</a>.0.0.1

; <<>> DiG 9.9.5-3-Ubuntu <<>> example.com A <a href="https://indiareads/community/users/127" class="username-tag">@127</a>.0.0.1
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 20517
;; flags: qr aa rd; QUERY: 1, ANSWER: 1, AUTHORITY: 0, ADDITIONAL: 1
;; WARNING: recursion requested but not available

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 2800
;; QUESTION SECTION:
;example.com.           IN  A

;; ANSWER SECTION:
<span class="highlight">example.com.     86400   IN  A   104.131.174.136</span>

;; Query time: 4 msec
;; SERVER: 127.0.0.1#53(127.0.0.1)
;; WHEN: Sun Nov 02 19:14:48 EST 2014
;; MSG SIZE  rcvd: 56
</code></pre>
<p>You can check all the other records as well, if you added multiple zone entries.</p>

<p>If these are correct, this means that this nameserver has the correct information!</p>

<p>However, it doesn't mean that the nameserver domains are registered, that this domain is using your new nameservers as SOAs, or that the change has propagated globally yet.</p>

<h3 id="conclusion">Conclusion</h3>

<p>We set up a PowerDNS server with a MariaDB backend. We set up the Poweradmin control panel to manage the backend. We created our first DNS zone, and created an A record for that zone.</p>

<p><strong>Where do we go from here</strong></p>

<p>If you have not done so already, you need to register your nameservers.</p>

<p>You also need to choose these nameservers as the SOAs for any domains for which you want to host DNS.</p>

<p>If you need assistance configuring your domain(s), the tutorials below will help you get you started. You may also need to check for instructions from your registrar.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-create-vanity-or-branded-nameservers-with-digitalocean-cloud-servers">How To Create Vanity or Branded Nameservers with IndiaReads Cloud Servers</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-and-test-dns-subdomains-with-digitalocean-s-dns-panel">How To Set Up and Test DNS Subdomains with IndiaReads's DNS Panel</a> </li>
<li><a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How to Point to IndiaReads Nameservers From Common Domain Registrars</a>  (Keep in mind that you would want to set these to <strong><span class="highlight">ns1.example-dns.com</span></strong>, etc., NOT the IndiaReads nameservers.)</li>
</ul>

    