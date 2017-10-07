<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/MySQLdolphin_twitter.png?1458582560/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="https://www.mysql.com/">MySQL</a> is an open-source database management system, commonly installed as part of the popular <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">LAMP</a> (Linux, Apache, MySQL, PHP/Python/Perl) stack. It uses a relational database and SQL (Structured Query Language) to manage its data.</p>

<p>The short version of the installation is simple: update your package index, install the <code>mysql-server</code> package, and then run the included security and database initialization scripts.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install mysql-server
</li><li class="line" prefix="$">sudo mysql_secure_installation
</li><li class="line" prefix="$">sudo mysql_install_db
</li></ul></code></pre>
<p>This tutorial will explain how to install MySQL version 5.5, 5.6, or 5.7 on a Ubuntu 14.04 server. If you want more detail on these installation instructions, or if you want to install a specific version of MySQL, read on. However, if you're looking to update an existing MySQL installation to version 5.7, you can read <a href="https://indiareads/community/tutorials/how-to-prepare-for-your-mysql-5-7-upgrade">this MySQL 5.7 update guide</a> instead.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li>One Ubuntu 14.04 Droplet with a <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">sudo non-root user</a>.</li>
</ul>

<h2 id="step-1-—-installing-mysql">Step 1 — Installing MySQL</h2>

<p>There are two ways to install MySQL. You can either use one of the versions included in the APT package repository by default (which are 5.5 and 5.6), or you can install the latest version (currently 5.7) by manually adding MySQL's repository first.</p>

<p>If you want to install a specific version of MySQL, follow the appropriate section below. To help you decide which version is best for you, you can read <a href="https://dev.mysql.com/tech-resources/articles/introduction-to-mysql-55.html">MySQL's introduction to MySQL 5.5</a>, then <a href="http://dev.mysql.com/tech-resources/articles/whats-new-in-mysql-5.6.html">what's new in MySQL 5.6</a> and <a href="http://dev.mysql.com/doc/refman/5.7/en/mysql-nutshell.html">what's new in MySQL 5.7</a>.</p>

<p>If you're not sure, you can just use the <code>mysql-server</code> APT package, which just installs the latest version for your Linux distribution. At the time of writing, that's 5.5, but you can always update to another version later.</p>

<p>To install MySQL this way, update the package index on your server and install the package with <code>apt-get</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p>You'll be prompted to create a root password during the installation. Choose a secure one and make sure you remember it, because you'll need it later. Move on to step two from here.</p>

<h3 id="installing-mysql-5-5-or-5-6">Installing MySQL 5.5 or 5.6</h3>

<p>If you want to install MySQL 5.5 or 5.6 specifically, the process is still very straightforward. First, update the package index on your server.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Then, to install MySQL 5.5, install the <code>mysql-server-5.5</code> package.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server-5.5
</li></ul></code></pre>
<p>To install MySQL 5.6, install the <code>mysql-server-5.6</code> package instead.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server-5.6
</li></ul></code></pre>
<p>For both options, you'll be prompted to create a root password during the installation. Choose a secure one and make sure you remember it, because you'll need it later.</p>

<h3 id="installing-mysql-5-7">Installing MySQL 5.7</h3>

<p>If you want to install MySQL 5.7, you'll need to add the newer APT package repository from <a href="http://dev.mysql.com/downloads/repo/apt/">the MySQL APT repository page</a>. Click <strong>Download</strong> on the bottom right, then copy the link on the next page from <strong>No thanks, just start my download</strong>. Download the <code>.deb</code> package to your server.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget http://dev.mysql.com/get/<span class="highlight">mysql-apt-config_0.6.0-1_all.deb</span>
</li></ul></code></pre>
<p>Next, install it using <code>dpkg</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo dpkg -i <span class="highlight">mysql-apt-config_0.6.0-1_all.deb</span>
</li></ul></code></pre>
<p>You'll see a prompt that asks you which MySQL product you want to configure. The <strong>MySQL Server</strong> option, which is highlighted, should say <strong>mysql-5.7</strong>. If it doesn't, press <code>ENTER</code>, then scroll down to <strong>mysql-5.7</strong> using the arrow keys, and press <code>ENTER</code> again.</p>

<p>Once the option says <strong>mysql-5.7</strong>, scroll down on the main menu to <strong>Apply</strong> and press <code>ENTER</code> again. Now, update your package index.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Finally, install the <code>mysql-server</code> package, which now contains MySQL 5.7.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install mysql-server
</li></ul></code></pre>
<p>You'll be prompted to create a root password during the installation. Choose a secure one and make sure you remember it, because you'll need it later.</p>

<h2 id="step-2-—-configuring-mysql">Step 2 — Configuring MySQL</h2>

<p>First, you'll want to run the included security script. This changes some of the less secure default options for things like remote root logins and sample users.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_secure_installation
</li></ul></code></pre>
<p>This will prompt you for the root password you created in step one. You can press <code>ENTER</code> to accept the defaults for all the subsequent questions, with the exception of the one that asks if you'd like to change the root password. You just set it in step one, so you don't have to change it now.</p>

<p>Next, we'll initialize the MySQL data directory, which is where MySQL stores its data. How you do this depends on which version of MySQL you're running. You can check your version of MySQL with the following command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysql --version
</li></ul></code></pre>
<p>You'll see some output like this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">mysql  Ver 14.14 Distrib <span class="highlight">5.7.11</span>, for Linux (x86_64) using  EditLine wrapper
</code></pre>
<p>If you're using a version of MySQL earlier than 5.7.6, you should initialize the data directory by running <code>mysql_install_db</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mysql_install_db
</li></ul></code></pre>
<span class="note"><p>
<strong>Note:</strong> In MySQL 5.6, you might get an error that says <strong>FATAL ERROR: Could not find my-default.cnf</strong>. If you do, copy the <code>/usr/share/my.cnf</code> configuration file into the location that <code>mysql_install_db</code> expects, then rerun it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/mysql/my.cnf /usr/share/mysql/my-default.cnf
</li><li class="line" prefix="$">sudo mysql_install_db
</li></ul></code></pre>
<p>This is due to some changes made in MySQL 5.6 and a minor error in the APT package.<br /></p></span>

<p> </p>

<p>The <code>mysql_install_db</code> command is deprecated as of MySQL 5.7.6. If you're using version 5.7.6 or later, you should use <code>mysqld --initialize</code> instead.</p>

<p>However, if you installed version 5.7 from the Debian distribution, like in step one, the data directory was initialized automatically, so you don't have to do anything. If you try running the command anyway, you'll see the following error:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">2016-03-07T20:11:15.998193Z 0 [ERROR] --initialize specified but the data directory has files in it. Aborting.
</code></pre>
<h2 id="step-3-—-testing-mysql">Step 3 — Testing MySQL</h2>

<p>Regardless of how you installed it, MySQL should have started running automatically. To test this, check its status.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">service mysql status
</li></ul></code></pre>
<p>You'll see the following output (with a different PID).</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">mysql start/running, process 2689
</code></pre>
<p>If MySQL isn't running, you can start it with <code>sudo service mysql start</code>.</p>

<p>For an additional check, you can try connecting to the database using the <code>mysqladmin</code> tool, which is a client that lets you run administrative commands. For example, this command says to connect to MySQL as <strong>root</strong> (<code>-u root</code>), prompt for a password (<code>-p</code>), and return the version.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mysqladmin -p -u root version
</li></ul></code></pre>
<p>You should see output similar to this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">mysqladmin  Ver 8.42 Distrib 5.5.47, for debian-linux-gnu on x86_64
Copyright (c) 2000, 2015, Oracle and/or its affiliates. All rights reserved.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Server version      5.5.47-0ubuntu0.14.04.1
Protocol version    10
Connection      Localhost via UNIX socket
UNIX socket     /var/run/mysqld/mysqld.sock
Uptime:         4 min 15 sec

Threads: 1  Questions: 602  Slow queries: 0  Opens: 189  Flush tables: 1  Open tables: 41  Queries per second avg: 2.360
</code></pre>
<p>This means MySQL is up and running.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You now have a basic MySQL setup installed on your server. Here are a few examples of next steps you can take:</p>

<ul>
<li>Implement some <a href="https://indiareads/community/tutorials/how-to-secure-mysql-and-mariadb-databases-in-a-linux-vps">additional security measures</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-create-hot-backups-of-mysql-databases-with-percona-xtrabackup-on-ubuntu-14-04">Create hot backups with Percona XtraBackup</a></li>
<li>Learn how to use MySQL with <a href="https://indiareads/community/tutorials/how-to-use-mysql-or-mariadb-with-your-django-application-on-ubuntu-14-04">Django applications</a> or <a href="https://indiareads/community/tutorials/how-to-use-mysql-with-your-ruby-on-rails-application-on-ubuntu-14-04">Ruby on Rails applications</a></li>
<li><a href="https://indiareads/community/tutorials/saltstack-infrastructure-creating-salt-states-for-mysql-database-servers">Manage your MySQL servers with SaltStack</a></li>
</ul>

    