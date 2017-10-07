<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-mariadb">About MariaDB</h3>

<hr />

<p>Currently, MariaDB is a drop-in replacement for MySQL. This article describes the installation of MariaDB version 5.5.34 x86_64 on an Ubuntu 13.10 VPS. Binary tarballs are used for installation instead of the software repositories available through apt-get. A potential rationale for this choice would be to have complete control over the installed version of MariaDB.</p>

<h2 id="downloading">Downloading</h2>

<hr />

<p>There are two 64-bit versions of MariaDB on the MariaDB <a href="https://downloads.mariadb.org/mariadb/">download page</a>. The difference between the two versions is that one version requires GLIBC 2.14+. </p>

<p>To check your installed GLIBC version:</p>
<pre class="code-pre "><code langs="">ldd --version
</code></pre>
<p>Output will be something like:</p>
<pre class="code-pre "><code langs="">ldd (Ubuntu EGLIBC 2.17-93ubuntu4) 2.17
Copyright (C) 2012 Free Software Foundation, Inc.
This is free software; see the source for copying conditions.  There is NO
warranty; not even for MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
Written by Roland McGrath and Ulrich Drepper.
</code></pre>
<p>In this case, version 2.17 is installed and we can proceed with downloading "mariadb-5.5.34-linux-x86<em>64.tar.gz" (requires GLIBC</em>2.14+).</p>

<p>You have to decide where you want to put the binaries, i.e. the application itself. Some opt for <code>/usr/local/</code> or <code>/opt/</code>. Here we choose the latter.</p>

<p>Let's create the directory and download the tarball:</p>
<pre class="code-pre "><code langs="">mkdir /opt/mariadb/
cd /opt/mariadb/
wget --output-document=mariadb-5.5.34-linux-x86_64.tar.gz https://downloads.mariadb.org/f/mariadb-5.5.34/kvm-bintar-quantal-amd64/mariadb-5.5.34-linux-x86_64.tar.gz/from/http:/mariadb.mirror.triple-it.nl/
</code></pre>
<p>Calculate the MD5 sum to verify whether the tar is valid:</p>
<pre class="code-pre "><code langs="">md5sum mariadb-5.5.34-linux-x86_64.tar.gz
</code></pre>
<p>The output should match the MD5 sum given by MariaDB on the download page: <code>14ca3e88eb67bced630569100173ef55</code>.</p>

<h2 id="installing">Installing</h2>

<hr />

<p>In <code>/opt/mariadb/</code>, extract the tar archive:</p>
<pre class="code-pre "><code langs=""># tar xf mariadb-5.5.34-linux-x86_64.tar.gz

</code></pre>
<p>Symbolic links are useful to link the used/installed version to a version specific MariaDB binary directory, for easy updating to a newer version, or to revert to a previously used version in case of failure. </p>

<p>To create the symlink:</p>
<pre class="code-pre "><code langs="">ln -s /opt/mariadb/mariadb-5.5.34-linux-x86_64 /opt/mariadb/mysql
</code></pre>
<p>Create a new user and group for MariaDB's process to run in:</p>
<pre class="code-pre "><code langs="">groupadd mysql
useradd -g mysql mysql
</code></pre>
<p>Change ownership of the binary files to the newly created user and group:</p>
<pre class="code-pre "><code langs="">chown -R mysql:mysql /opt/mariadb/mysql/
</code></pre>
<h2 id="my-cnf">my.cnf</h2>

<hr />

<p>Copy your my.cnf configuration file to <code>/etc/my.cnf</code>. If you do not have a configuration file already, there are some files in <code>/opt/mariadb/mysql/support-files/</code> to get you started. For demonstration purposes, <code>my-small.cnf</code> is used:</p>
<pre class="code-pre "><code langs="">cp /opt/mariadb/mysql/support-files/my-small.cnf /etc/my.cnf
</code></pre>
<p>At least set the following directives in <code>/etc/my.cnf</code>:</p>
<pre class="code-pre "><code langs="">basedir=/opt/mariadb/mysql
datadir=/var/lib/mysql
user=mysql
</code></pre>
<p><code>basedir</code> specifies the location of the binary files, <code>datadir</code> specifies where the actual database files are stored, and <code>user</code> specifies that MariaDB is run under the user mysql. Typically, not setting a <code>datadir</code> defaults to <code>/usr/local/mysql/data</code>.</p>

<p>Just to be sure the <code>datadir</code> directory is there:</p>
<pre class="code-pre "><code langs="">mkdir -p /var/lib/mysql
</code></pre>
<h2 id="initialize-system-tables">Initialize system tables</h2>

<hr />

<p>Like MySQL, MariaDB's system tables have to be initialized:</p>
<pre class="code-pre "><code langs="">/opt/mariadb/mysql/scripts/mysql_install_db --user=mysql --basedir=/opt/mariadb/mysql
</code></pre>
<h2 id="system-service">System service</h2>

<hr />

<p>For MariaDB to be started automatically after a system reboot, we can add a system service:</p>
<pre class="code-pre "><code langs="">ln -s /opt/mariadb/mysql/support-files/mysql.server /etc/init.d/mysql
update-rc.d mysql defaults
</code></pre>
<p>To start the service:</p>
<pre class="code-pre "><code langs="">service mysql start
</code></pre>
<p>If you prefer to start MariaDB manually, use:</p>
<pre class="code-pre "><code langs="">/opt/mariadb/mysql/bin/mysqld_safe --user=mysql --ledir=/opt/mariadb/mysql/bin &
</code></pre>
<h2 id="configure-mariadb">Configure MariaDB</h2>

<hr />

<p>Be sure that MariaDB is up and running.</p>

<p>A root account is required for further configuration, to set up a root account:</p>
<pre class="code-pre "><code langs="">/opt/mariadb/mysql/bin/mysqladmin -u root password '<pwd>'
</code></pre>
<p>Where <code><pwd></code> is the password desired for the root user.</p>

<p>Additional security configuration:</p>
<pre class="code-pre "><code langs="">/opt/mariadb/mysql/bin/mysql_secure_installation --basedir=/opt/mariadb/mysql
</code></pre>
<p>which asks a couple of questions after supplying it the previously specified root password. Provide the following configuration answers:</p>
<pre class="code-pre "><code langs="">change root pwd: n
remove anonymous users: y
disallow root login remotely: y
remote test database and access to it: y
reload privilege tables now: y
</code></pre>
<h2 id="manual-entries-and-global-execution-of-binaries">Manual entries and global execution of binaries</h2>

<hr />

<p>When manually installing MariaDB, there are no manual entries and typing a system wide command like <code>mysql</code> results in a <code>The program 'mysql' is currently not installed</code>-like error.</p>

<p>Put the following entries in .bashrc or similar environment file that is loaded at system level or user level. For example, <code>vim /root/.bashrc</code>:</p>
<pre class="code-pre "><code langs="">PATH=$PATH:/opt/mariadb/mysql/bin
MANPATH=$MANPATHL/opt/mariadb/mysql/man
</code></pre>
<h2 id="test-it">Test it</h2>

<hr />

<p>Reboot the machine to test if all works correctly:</p>
<pre class="code-pre "><code langs=""># reboot
</code></pre>
<p>That MariaDB is running can be verified by:</p>
<pre class="code-pre "><code langs=""># service mysql status
</code></pre>
<p>try and see if the manual works:</p>
<pre class="code-pre "><code langs="">man mysql
</code></pre>
<p>try and see if MariaDB works:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>Supply the root password and you should see something similar to:</p>
<pre class="code-pre "><code langs="">Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MariaDB connection id is 3
Server version: 5.5.34-MariaDB MariaDB Server

Copyright (c) 2000, 2013, Oracle, Monty Program Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MariaDB [(none)]>
</code></pre>
<p>Next step is to further configure the database with user accounts and import data.</p>

<div class="author">Article Submitted by: <a href="https://twitter.com/whazenberg">Wytze Hazenberg </a></div>
 

    