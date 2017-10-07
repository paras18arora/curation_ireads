<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>MediaWiki is a free and open-source wiki application written in PHP. It was originally created for WikiPedia, but it now allows everyone to create their own wiki sites. Currently thousands of websites are running MediaWiki, including Wikipedia, Wiktionary and Wikimedia Commons. MediaWiki's homepage is located at <a href="https://www.mediawiki.org/wiki/MediaWiki">https://www.mediawiki.org</a>.</p>

<p>This tutorial goes through how to set up MediaWiki on a CentOS 7 Droplet.</p>

<h2 id="prerequisites">Prerequisites</h2>

<ul>
<li>A CentOS 7 server with SSH access. For more information, visit <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">this tutorial</a>.</li>
<li>A LAMP stack, which you can install by following <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7">this tutorial</a>.</li>
</ul>

<h2 id="step-1-—-setting-up-your-server">Step 1 — Setting Up Your Server</h2>

<p>After you have installed the LAMP stack, we will first need to install a few additional PHP 5 modules. All of them are optional except for the first one (the XML extension).</p>

<p>The first one we will be installing is the <a href="http://php.net/manual/en/book.xml.php">XML</a> extension, and it is required for MediaWiki to run:</p>
<pre class="code-pre "><code langs="">sudo yum install php-xml
</code></pre>
<p>The second one we will be installing is the <a href="http://php.net/manual/en/book.intl.php">Intl</a> extension, for internationalization support:</p>
<pre class="code-pre "><code langs="">sudo yum install php-intl
</code></pre>
<p>Secondly, we will install <a href="http://php.net/manual/en/book.image.php">GD</a> for image thumbnailing:</p>
<pre class="code-pre "><code langs="">sudo yum install php-gd
</code></pre>
<p>These last two modules are really optional. These are not necessary for most wikis, unless you have a high performance or math-heavy wiki. The first one is Tex Live for in-line display of mathematical formulae:</p>
<pre class="code-pre "><code langs="">sudo yum install texlive
</code></pre>
<p>For added performance, you can install XCache. For this, however, you also need to install an extra repository, as XCache is not available in the CentOS repository by default:</p>
<pre class="code-pre "><code langs="">sudo yum install epel-release
</code></pre>
<p>Now, you can install XCache:</p>
<pre class="code-pre "><code langs="">sudo yum install php-xcache
</code></pre>
<p>To finish these installations, restart Apache HTTPD.</p>
<pre class="code-pre "><code langs="">sudo systemctl restart httpd.service
</code></pre>
<h2 id="step-2-—-downloading-mediawiki">Step 2 — Downloading MediaWiki</h2>

<p>In this section we will download MediaWiki from source. MediaWiki can be downloaded from its official website. At time of writing, the latest version is <strong>1.24.1</strong>, but you can double check via the download link on <a href="https://www.mediawiki.org/wiki/Download">this page</a>.</p>

<p>Download MediaWiki.</p>
<pre class="code-pre "><code langs="">curl -O http://releases.wikimedia.org/mediawiki/1.24/mediawiki-1.24.1.tar.gz
</code></pre>
<p>Untar the package:</p>
<pre class="code-pre "><code langs="">tar xvzf mediawiki-*.tar.gz
</code></pre>
<p>Move to the <code>/var/www</code> directory:</p>
<pre class="code-pre "><code langs="">sudo mv mediawiki-1.24.1/* /var/www/html
</code></pre>
<h2 id="step-3-—-creating-a-database">Step 3 — Creating a Database</h2>

<p>In this section we will set up a MySQL database. This is not strictly required to successfully install MediaWiki, as you can use a SQLite database as well. Despite this, it is definitely a recommended measure.</p>

<p>We will first log in to the MySQL shell:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
</code></pre>
<p>This will change your prompt to <code>MariaDB [(none)]></code>.</p>

<p>Now, we will create the database. The database name does not matter for MediaWiki, but we will use <code>my_wiki</code> in this tutorial. You can choose another name if you prefer.</p>
<pre class="code-pre "><code langs="">CREATE DATABASE <span class="highlight">my_wiki</span>;
</code></pre>
<p>The output should be:</p>
<pre class="code-pre "><code langs="">Query OK, 1 row affected (0.00 sec)
</code></pre>
<p>We don't want to use the <code>root</code> user for MediaWiki, so we will create a new database user:</p>
<pre class="code-pre "><code langs="">GRANT INDEX, CREATE, SELECT, INSERT, UPDATE, DELETE, ALTER, LOCK TABLES ON <span class="highlight">my_wiki</span>.* TO '<span class="highlight">sammy</span>'@'localhost' IDENTIFIED BY '<span class="highlight">password</span>';
</code></pre>
<p>Change <code>my_wiki</code> to your chosen database name, <code>sammy</code> to your username, and <code>password</code> to a secure password. The output should be:</p>
<pre class="code-pre "><code langs="">Query OK, 0 rows affected (0.01 sec)
</code></pre>
<p>Next, we need to flush the MySQL privileges:</p>
<pre class="code-pre "><code langs="">FLUSH PRIVILEGES;
</code></pre>
<p>The output should be:</p>
<pre class="code-pre "><code langs="">Query OK, 0 rows affected (0.00 sec)
</code></pre>
<p>Last, we will need to exit the MySQL shell:</p>
<pre class="code-pre "><code langs="">exit;
</code></pre>
<p>The output should be:</p>
<pre class="code-pre "><code langs="">Bye
</code></pre>
<h2 id="step-4-setting-up-mediawiki">Step 4 - Setting Up MediaWiki</h2>

<p>In this section, we will set up MediaWiki so it is ready to use. Visit the homepage of your Droplet in your browser by pointing your browser to <code>http://<span class="highlight">your_server_ip</span></code>. On this page, select <strong>set up the wiki</strong>.</p>

<p>On the first page, select a language and click <strong>Continue</strong>. The next page should show your environment and it should say in green: <strong>The environment has been checked. You can install MediaWiki.</strong> Click <strong>Continue</strong>.</p>

<p>You will now get to the page with MySQL settings. For the <strong>Database type</strong> select <strong>MySQL (or compatible)</strong>. For the database host, type <strong>localhost</strong>. The database name, username, and password will be the values you chose before. We used <code>my_wiki</code> for the database name, <code>sammy</code> for the username, and <code>badpassword</code> for the password. The table prefix can be left empty. It will look like this:</p>

<p><img src="https://assets.digitalocean.com/articles/mediawiki_centos7/1.png" alt="MySQL settings" /></p>

<p>In the screen after the MySQL settings, the values can be left at their defaults. In the next screen, you will need to fill in the details of your wiki, like its name. You can also create the admin user for the wiki on this page.</p>

<p>In all the other screens, most, if not all, of the settings can be left untouched. If you want a specific setting enabled for your wiki, you might need to change something on one of these screens. Particularly if you have installed XCache before, you will need to check that to enable it.</p>

<p>When you have completed all steps, you should arrive at this page:</p>

<p><img src="https://assets.digitalocean.com/articles/mediawiki_centos7/2.png" alt="Completed installation" /></p>

<p>To successfully complete the installation, you will need to move a file called <code>LocalSettings.php</code> to your server, which should have started downloading automatically. You should download this file before closing the page.</p>

<p>Now, you will need to upload the file to <code>/var/www/html</code>. You could use an external program, but it is easiest to open the file on your local computer, copy the contents and paste them into your SSH session. To do this, first open the file on the server:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/LocalSettings.php
</code></pre>
<p>Now, open the file on your computer in your text editor of choice and copy the contents into your SSH window. After you have saved the file, you can click <strong>enter your wiki</strong> and your wiki should be ready to use.</p>

<h3 id="conclusion">Conclusion</h3>

<p>You will now see your own MediaWiki installation, ready for use. To further customize the page, visit the <a href="http://www.mediawiki.org/wiki/Manual:System_administration">System administration</a> page on the MediaWiki homepage. You can also start adding pages directly.</p>

    