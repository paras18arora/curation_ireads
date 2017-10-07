<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Mirroring is a way of scaling a download site, so the download load can be spread across many servers in many parts of the world. Mirrors host copies of the files and are managed by a mirror director. A mirror director is the center of any mirror system. It is responsible for directing traffic to the closest appropriate mirror so users can download more quickly.</p>

<p>Mirroring is a unique system with its own advantages and disadvantages.  Unlike a DNS based system, mirroring is more much more flexible.  There is no need to wait for DNS or even to trust the mirroring server (the mirror director can scan the mirror to check its validity and completeness). This is one reason many open source projects use mirrors to harness the generosity of ISPs and server owners to take the load off the open source project's own servers for downloads.</p>

<p>Unfortunately, a mirroring system will increase the overhead of any HTTP request, as the request must travel to the mirror director before being redirected to the real file.  Therefore, mirroring is commonly used for hosting downloads (single large files), but is not recommended for websites (many small files).</p>

<p>This tutorial will show how to set up a MirrorBrain instance (a popular, feature-rich mirror director) and an rsync server (rsync lets mirrors sync files with the director) on one server.  Then we will set up one mirror on a different server.</p>

<p><strong>Required:</strong></p>

<ul>
<li>Two Ubuntu 14.04 Droplets in different regions; one director and at least one mirror.</li>
</ul>

<h2 id="step-one-— setting-up-apache">Step One — Setting Up Apache</h2>

<p>First we need to compile and install MirrorBrain. The entire first part of this tutorial should be done on the <strong>mirror director</strong> server. We'll let you know when to switch to the mirror.</p>

<p>Perform these steps <strong>as root</strong>.  If necessary, use sudo to access a root shell:</p>
<pre class="code-pre "><code langs="">sudo -i
</code></pre>
<p>MirrorBrain is a large Apache module, so we will need to use Apache to serve our files.  First install Apache and the modules we require:</p>
<pre class="code-pre "><code langs="">apt-get install apache2 libapache2-mod-geoip libgeoip-dev apache2-dev
</code></pre>
<p>GeoIP is an IP address to location service and will power MirrorBrain's ability to redirect users to the best download location. We need to change GeoIP's configuration file to make it work with MirrorBrain. First open the configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/apache2/mods-available/geoip.conf
</code></pre>
<p>Modify it to look like the following. Add the <span class="highlight">GeoIPOutput Env</span> line, and uncomment the <span class="highlight">GeoIPDBFile</span> line, and add the <span class="highlight">MMapCache</span> setting:</p>
<pre class="code-pre "><code langs=""><IfModule mod_geoip.c>
        GeoIPEnable On
        <span class="highlight">GeoIPOutput Env</span>
        GeoIPDBFile /usr/share/GeoIP/GeoIP.dat <span class="highlight">MMapCache</span>
</IfModule>
</code></pre>
<p>Close and save the file (<strong>Ctrl-x</strong>, then <strong>y</strong>, then <strong>Enter</strong>).</p>

<p>Link the GeoIP database to where MirrorBrain expects to find it:</p>
<pre class="code-pre "><code langs="">ln -s /usr/share/GeoIP /var/lib/GeoIP
</code></pre>
<p>Next let's enable the modules we just installed and configured:</p>
<pre class="code-pre "><code langs="">a2enmod dbd
a2enmod geoip
</code></pre>
<p>The geoip module may already be enabled; that's fine.</p>

<h2 id="step-two-—-installing-and-compiling-mirrorbrain">Step Two — Installing and Compiling MirrorBrain</h2>

<p>Now we need to compile the MirrorBrain module. First install some dependencies:</p>
<pre class="code-pre "><code langs="">apt-get install python-pip python-dev libdbd-pg-perl python-SQLObject python-FormEncode python-psycopg2 libaprutil1-dbd-pgsql

pip install cmdln
</code></pre>
<p>Use Perl to install some more dependencies.</p>
<pre class="code-pre "><code langs="">perl -MCPAN -e 'install Bundle::LWP'
</code></pre>
<p>Pay attention to the questions asked here. You should be able to press <strong>Enter</strong> or say <strong>y</strong> to accept the defaults.</p>

<p>You should see quite a bit of output, ending with the line:</p>
<pre class="code-pre "><code langs="">  /usr/bin/make install  -- OK
</code></pre>
<p>If you get warnings or errors, you may want to run through the configuration again by executing the <span class="highlight">perl -MCPAN -e 'install Bundle::LWP'</span> command again.</p>

<p>Install the last dependency.</p>
<pre class="code-pre "><code langs="">perl -MCPAN -e 'install Config::IniFiles'
</code></pre>
<p>Now we can download and extract the MirrorBrain source:</p>
<pre class="code-pre "><code langs="">wget http://mirrorbrain.org/files/releases/mirrorbrain-2.18.1.tar.gz
tar -xzvf mirrorbrain-2.18.1.tar.gz 
</code></pre>
<p>Next we need to add the forms module source to MirrorBrain:</p>
<pre class="code-pre "><code langs="">cd mirrorbrain-2.18.1/mod_mirrorbrain/
wget http://apache.webthing.com/svn/apache/forms/mod_form.h
wget http://apache.webthing.com/svn/apache/forms/mod_form.c
</code></pre>
<p>Now we can compile and enable the MirrorBrain and forms modules:</p>
<pre class="code-pre "><code langs="">apxs -cia -lm mod_form.c
apxs -cia -lm mod_mirrorbrain.c
</code></pre>
<p>And then the MirrorBrain autoindex module:</p>
<pre class="code-pre "><code langs="">cd ~/mirrorbrain-2.18.1/mod_autoindex_mb
apxs -cia mod_autoindex_mb.c
</code></pre>
<p>Let's compile the MirrorBrain GeoIP helpers:</p>
<pre class="code-pre "><code langs="">cd ~/mirrorbrain-2.18.1/tools

gcc -Wall -o geoiplookup_city geoiplookup_city.c -lGeoIP
gcc -Wall -o geoiplookup_continent geoiplookup_continent.c -lGeoIP
</code></pre>
<p>Copy the helpers into the commands directory:</p>
<pre class="code-pre "><code langs="">cp geoiplookup_city /usr/bin/geoiplookup_city
cp geoiplookup_continent /usr/bin/geoiplookup_continent
</code></pre>
<p>Install the other internal tools:</p>
<pre class="code-pre "><code langs="">install -m 755 ~/mirrorbrain-2.18.1/tools/geoip-lite-update /usr/bin/geoip-lite-update
install -m 755 ~/mirrorbrain-2.18.1/tools/null-rsync /usr/bin/null-rsync
install -m 755 ~/mirrorbrain-2.18.1/tools/scanner.pl /usr/bin/scanner
install -m 755 ~/mirrorbrain-2.18.1/mirrorprobe/mirrorprobe.py /usr/bin/mirrorprobe
</code></pre>
<p>Then add the logging file for mirrorprobe (mirrorprobe checks that the mirrors are online):</p>
<pre class="code-pre "><code langs="">mkdir /var/log/mirrorbrain
touch /var/log/mirrorbrain/mirrorprobe.log
</code></pre>
<p>Now, we can install the MirrorBrain command line management tool:</p>
<pre class="code-pre "><code langs="">cd ~/mirrorbrain-2.18.1/mb
python setup.py install
</code></pre>
<h2 id="step-three-—-installing-postgresql">Step Three — Installing PostgreSQL</h2>

<p>MirrorBrain uses PostgreSQL, which is easy to set up on Ubuntu.  First, let's install PostgreSQL:</p>
<pre class="code-pre "><code langs="">apt-get install postgresql postgresql-contrib
</code></pre>
<p>Now let's go into the PostgreSQL admin shell:</p>
<pre class="code-pre "><code langs="">sudo -i -u postgres
</code></pre>
<p>Let's create a MirrorBrain database user. Create a password for this user, and make a note of it, since you'll need it later:</p>
<pre class="code-pre "><code langs="">createuser -P mirrorbrain
</code></pre>
<p>Then, set up a database for MirrorBrain:</p>
<pre class="code-pre "><code langs="">createdb -O mirrorbrain mirrorbrain
createlang plpgsql mirrorbrain
</code></pre>
<p>If you get a notice that the language is already installed, that's fine:</p>
<pre class="code-pre "><code langs="">createlang: language "plpgsql" is already installed in database "mirrorbrain"
</code></pre>
<p>We need to allow password authentication for the database from the local machine (this is required by MirrorBrain).  First open the configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/postgresql/9.3/main/pg_hba.conf
</code></pre>
<p>Then locate line 90 (it should be the second line that looks like this):</p>
<pre class="code-pre "><code langs=""> # "local" is for Unix domain socket connections only
 local   all             all                                     peer
</code></pre>
<p>Update it to use md5-based password authentication:</p>
<pre class="code-pre "><code langs="">local   all             all                                     <span class="highlight">md5</span>
</code></pre>
<p>Save your changes and restart PostgreSQL:</p>
<pre class="code-pre "><code langs="">service postgresql restart
</code></pre>
<p>Now let's quit the PostgreSQL shell (<strong>Ctrl-D</strong>).</p>

<p>Next, complete the database setup by importing MirrorBrain's database schema:</p>
<pre class="code-pre "><code langs="">cd ~/mirrorbrain-2.18.1
psql -U mirrorbrain -f sql/schema-postgresql.sql mirrorbrain
</code></pre>
<p>When prompted, enter the password we set earlier for the <strong>mirrorbrain</strong> database user.</p>

<p>The output should look like this:</p>
<pre class="code-pre "><code langs="">BEGIN
CREATE TABLE
CREATE TABLE
CREATE TABLE
CREATE VIEW
CREATE TABLE
CREATE INDEX
CREATE TABLE
CREATE TABLE
CREATE TABLE
CREATE FUNCTION
CREATE FUNCTION
CREATE FUNCTION
CREATE FUNCTION
CREATE FUNCTION
CREATE FUNCTION
CREATE FUNCTION
CREATE FUNCTION
CREATE FUNCTION
COMMIT
</code></pre>
<p>Add the initial data:</p>
<pre class="code-pre "><code langs="">psql -U mirrorbrain -f sql/initialdata-postgresql.sql mirrorbrain
</code></pre>
<p>Expected output:</p>
<pre class="code-pre "><code langs="">INSERT 0 1
INSERT 0 6
INSERT 0 246
</code></pre>
<p>You have now installed MirrorBrain and set up a database!</p>

<h2 id="step-four-—-publishing-the-mirror">Step Four — Publishing the Mirror</h2>

<p>Now add some files to the mirror. We suggest naming the download directory after your domain. Let's create a directory to serve these files (still as root):</p>
<pre class="code-pre "><code langs="">mkdir /var/www/<span class="highlight">download.example.org</span>
</code></pre>
<p>Enter that directory:</p>
<pre class="code-pre "><code langs="">cd /var/www/<span class="highlight">download.example.org</span>
</code></pre>
<p>Now we need to add some files.  If you already have the files on your server, you will want to <span class="highlight">cp</span> or <span class="highlight">mv</span> them into this folder:</p>
<pre class="code-pre "><code langs="">cp <span class="highlight">/var/www/example.org/downloads/</span>* /var/www/<span class="highlight">download.example.org</span>
</code></pre>
<p>If they are on a different server you could use <span class="highlight">scp</span> (the mirror director server needs SSH access to the other server):</p>
<pre class="code-pre "><code langs="">scp <span class="highlight">root</span>@<span class="highlight">other.server.example.org</span>:<span class="highlight">/var/www/example.org/downloads/*</span> <span class="highlight">download.example.org</span>
</code></pre>
<p>You can also just upload new files as you would any other files; for example, by using <a href="https://indiareads/community/tutorials/how-to-use-sshfs-to-mount-remote-file-systems-over-ssh">SSHFS</a> or <a href="https://indiareads/community/tutorials/how-to-use-sftp-to-securely-transfer-files-with-a-remote-server">SFTP</a>.</p>

<p>For testing, you can add three sample files:</p>
<pre class="code-pre "><code langs="">cd /var/www/<span class="highlight">download.example.org</span>
touch apples.txt bananas.txt carrots.txt
</code></pre>
<p>Next, we need to set up rsync. rsync is a UNIX tool that allows us to sync files between servers. We will be using it to keep our mirrors in sync with the mirror director. Rsync can operate over SSH or a public <code>rsync://</code> URL.  We will set up the rsync daemon (the <span class="highlight">rsync://</span> URL) option.  First we need to make a configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/rsyncd.conf
</code></pre>
<p>Let's add this configuration. The <span class="highlight">path</span> should be to your download directory, and the <span class="highlight">comment</span> can be whatever you want:</p>
<pre class="code-pre "><code langs="">[main]
    path = /var/www/<span class="highlight">download.example.org</span>
    comment = <span class="highlight">My Mirror Director with Very Fast Download Speed!</span>
    read only = true
    list = yes
</code></pre>
<p>Save the file. Start the rsync daemon:</p>
<pre class="code-pre "><code langs=""> rsync --daemon --config=/etc/rsyncd.conf
</code></pre>
<p>Now we can test this by running the following on a *NIX system. You can use a domain that resolves to your server, or your server's IP address:</p>
<pre class="code-pre "><code langs="">rsync rsync://<span class="highlight">server.example.org</span>/main
</code></pre>
<p>You should see a list of your files.</p>

<h2 id="step-five-—-enabling-mirrorbrain">Step Five — Enabling MirrorBrain</h2>

<p>Now that we have our files ready, we can enable MirrorBrain. First we need a MirrorBrain user and group:</p>
<pre class="code-pre "><code langs="">groupadd -r mirrorbrain
useradd -r -g mirrorbrain -s /bin/bash -c "MirrorBrain user" -d /home/mirrorbrain mirrorbrain
</code></pre>
<p>Now, let's make the MirrorBrain configuration file that will allow the MirrorBrain management tool to connect to the database:</p>
<pre class="code-pre "><code langs="">nano /etc/mirrorbrain.conf
</code></pre>
<p>Then add this configuration. Most of these settings are to set up the database connection. Be sure to add the <strong>mirrorbrain</strong> database user's password for the <span class="highlight">dbpass</span> setting:</p>
<pre class="code-pre "><code langs="">[general]
instances = main

[main]
dbuser = mirrorbrain
dbpass = <span class="highlight">password</span>
dbdriver = postgresql
dbhost = 127.0.0.1
dbname = mirrorbrain

[mirrorprobe]
</code></pre>
<p>Save the file.  Now let's set up our Apache VirtualHost file for MirrorBrain:</p>
<pre class="code-pre "><code langs="">nano /etc/apache2/sites-available/<span class="highlight">download.example.org</span>.conf
</code></pre>
<p>Then add this VirtualHost configuration. You'll need to modify all of the locations where <span class="highlight">download.example.org</span> is used to have your own domain or IP address that resolves to your server. You should also set up your own email address for the <span class="highlight">ServerAdmin</span> setting. Make sure you use the <strong>mirrorbrain</strong> database user's password on the <span class="highlight">DBDParams</span> line:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerName <span class="highlight">download.example.org</span>
    ServerAdmin <span class="highlight">webmaster@example.org</span>
    DocumentRoot /var/www/<span class="highlight">download.example.org</span>

    ErrorLog     /var/log/apache2/<span class="highlight">download.example.org</span>/error.log
    CustomLog    /var/log/apache2/<span class="highlight">download.example.org</span>/access.log combined

    DBDriver pgsql
    DBDParams "host=localhost user=mirrorbrain password=<span class="highlight">database password</span> dbname=mirrorbrain connect_timeout=15"       

    <Directory /var/www/<span class="highlight">download.example.org</span>>
        MirrorBrainEngine On
        MirrorBrainDebug Off
        FormGET On

        MirrorBrainHandleHEADRequestLocally Off
        MirrorBrainMinSize 2048
        MirrorBrainExcludeMimeType application/pgp-keys  

        Options FollowSymLinks Indexes
        AllowOverride None
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
</code></pre>
<p>It is worth looking at some of the MirrorBrain options available under the Directory tag:</p>

<table class="pure-table"><thead>
<tr>
<th>Name</th>
<th>Usage</th>
</tr>
</thead><tbody>
<tr>
<td>MirrorBrainMinSize</td>
<td>Sets the minimum size file (in bytes) to be redirected to a mirror to download.  This prevents MirrorBrain for redirecting people to download really small files, where the time taken to run the database lookup, GeoIP, etc. is longer than to just serve the file.</td>
</tr>
<tr>
<td>MirrorBrainExcludeMimeType</td>
<td>Sets which mime types should not be served  from a mirror.  Consider enabling this for key files or similar; small files that must be delivered 100% accurately.  Use this option multiple times in your configuration file to enable it for multiple mime types.</td>
</tr>
<tr>
<td>MirrorBrainExcludeUserAgent</td>
<td>This option stops redirects for a given user agent.  Some clients (e.g. curl) require special configuration to work with redirects, and it may be easier to just serve the files directly to those users.  You can use wildcards (e.g. <code>*Chrome/*</code> will disable redirection for any Chrome user).</td>
</tr>
</tbody></table>

<p>A full list of configuration options can be found <a href="http://svn.mirrorbrain.org/viewvc/mirrorbrain/trunk/mod_mirrorbrain/mod_mirrorbrain.conf?view=markup">on the MirrorBrain website</a>.</p>

<p>If you would like more information about basic Apache VirtualHost settings, please check <a href="https://indiareads/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts">this tutorial</a>.</p>

<p><strong>Save and exit the file.</strong></p>

<p>Make sure your log directory exists:</p>
<pre class="code-pre "><code langs="">mkdir  /var/log/apache2/<span class="highlight">download.example.org</span>/
</code></pre>
<p>Make a link to the configuration file in the enabled sites directory:</p>
<pre class="code-pre "><code langs="">ln -s /etc/apache2/sites-available/<span class="highlight">download.example.org</span>.conf /etc/apache2/sites-enabled/<span class="highlight">download.example.org</span>.conf
</code></pre>
<p>Now restart Apache:</p>
<pre class="code-pre "><code langs="">service apache2 restart
</code></pre>
<p>Congratulations, you now have MirrorBrain up and running!</p>

<p>To test that MirrorBrain is working, first visit your download site in a web browser to view the file index. Then click on one of the files to view it. Append ".mirrorlist" to the end of the URL. (Example URL: <strong>http://download.example.org/apples.txt.mirrorlist</strong>.) If all is working, you should see a page like this:</p>

<p><img src="https://assets.digitalocean.com/tutorial_images/CPrIui0.png" alt="MirrorBrain Mirror List Example" /></p>

<h3 id="cron-job-configuration">Cron Job Configuration</h3>

<p>Before we can start adding mirrors, we still need to set up some mirror scanning and maintenance cron jobs.  First, let's set MirrorBrain to check which mirrors are online (using the mirrorprobe command) every minute:</p>
<pre class="code-pre "><code langs="">echo "* * * * * mirrorbrain mirrorprobe" | crontab
</code></pre>
<p>And a cron job to scan the mirrors' content (for availability and correctness of files) every hour:</p>
<pre class="code-pre "><code langs="">echo "0 * * * * mirrorbrain mb scan --quiet --jobs 4 --all" | crontab
</code></pre>
<p>If you have very quickly changing content, it would be wise to add more scan often, e.g., <code>0,30 * * * *</code> for every half an hour. If you have a very powerful server, you could increase the number of <code>--jobs</code> to scan more mirrors at the same time.</p>

<p>Clean up the database at 1:30 on Monday mornings:</p>
<pre class="code-pre "><code langs="">echo "30 1 * * mon mirrorbrain mb db vacuum" | crontab
</code></pre>
<p>And update the GeoIP data around about 2:30 on Monday mornings (the sleep statement is to reduce unneeded load spikes on the GeoIP servers):</p>
<pre class="code-pre "><code langs="">echo "31 2 * * mon root sleep $(($RANDOM/1024)); /usr/bin/geoip-lite-update" | crontab
</code></pre>
<h2 id="step-six-—-mirroring-the-content-on-another-server">Step Six — Mirroring the Content on Another Server</h2>

<p>Now that we have a mirror director set up, let's create our first mirror. You can follow this section for every mirror you want to add.</p>

<p>For this section, use a different Ubuntu 14.04 server, preferably in a different region.</p>

<p>Once you've logged in (as root or using <span class="highlight">sudo -i</span>), create a mirror content directory:</p>
<pre class="code-pre "><code langs="">mkdir -p /var/www/<span class="highlight">download.example.org</span>
</code></pre>
<p>Then copy the content into that directory using the rsync URL that we set up earlier:</p>
<pre class="code-pre "><code langs="">rsync -avzh rsync://<span class="highlight">download.example.org</span>/main /var/www/<span class="highlight">download.example.org</span>
</code></pre>
<p>If you encounter issues with space (IO Error) while using rsync, there is a way around it. You can add the <span class="highlight">--exclude</span> option to exclude directories which are not as important to your visitors.  MirrorBrain will scan your server and not send users to the excluded files, instead sending them to the closest server which has the file. For example, you could exclude old movies and old songs:</p>
<pre class="code-pre "><code langs="">rsync -avzh rsync://<span class="highlight">download.example.org</span>/main /var/www/<span class="highlight">download.example.org</span> --exclude "movies/old" --exclude "songs/old"
</code></pre>
<p>Then we can set your mirror server to automatically sync with the main server every hour using cron (remember to include the <code>--exclude</code> options if you used any):</p>
<pre class="code-pre "><code langs="">echo '0 * * * * root rsync -avzh rsync://<span class="highlight">download.example.org</span>/main /var/www/<span class="highlight">download.example.org</span>' | crontab
</code></pre>
<p>Now we need to publish our mirror over HTTP (for users) and over rsync (for MirrorBrain scanning).</p>

<h3 id="apache">Apache</h3>

<p>If you already have an HTTP server on your server, you should add a VirtualHost (or equivalent) to serve the <code>/var/www/<span class="highlight">download.example.org</span></code> directory. Otherwise, let's install Apache:</p>
<pre class="code-pre "><code langs="">apt-get install apache2
</code></pre>
<p>Then let's add a VirtualHost file:</p>
<pre class="code-pre "><code langs="">nano /etc/apache2/sites-available/<span class="highlight">london1.download.example.org</span>.conf
</code></pre>
<p>Add the following contents. Make sure you set your own values for the <span class="highlight">ServerName</span>, <span class="highlight">ServerAdmin</span>, and <span class="highlight">DocumentRoot</span> directives:</p>
<pre class="code-pre "><code langs=""><VirtualHost *:80>
    ServerName <span class="highlight">london1.download.example.org</span>
    ServerAdmin <span class="highlight">webmaster@example.org</span>
    DocumentRoot /var/www/<span class="highlight">download.example.org</span>
</VirtualHost>
</code></pre>
<p>Save the file. Enable the new VirtualHost:</p>
<pre class="code-pre "><code langs="">ln -s /etc/apache2/sites-available/<span class="highlight">london1.download.example.org</span>.conf /etc/apache2/sites-enabled/<span class="highlight">london1.download.example.org</span>.conf
</code></pre>
<p>Now restart Apache:</p>
<pre class="code-pre "><code langs="">service apache2 restart
</code></pre>
<h3 id="rsync">rsync</h3>

<p>Next, we need to set up the rsync daemon (for MirrorBrain scanning).  First open the configuration file:</p>
<pre class="code-pre "><code langs="">nano /etc/rsyncd.conf
</code></pre>
<p>Then add the configuration, making sure the <em>path</em> matches your download directory. The <em>comment</em> can be whatever you want:</p>
<pre class="code-pre "><code langs="">[main]
    path = /var/www/<span class="highlight">download.example.org</span>
    comment = <span class="highlight">My Mirror Of Some Cool Files</span>
    read only = true
    list = yes
</code></pre>
<p>Save this file.</p>

<p>Start the rsync daemon:</p>
<pre class="code-pre "><code langs="">rsync --daemon --config=/etc/rsyncd.conf
</code></pre>
<h3 id="enabling-the-mirror-on-the-director">Enabling the Mirror on the Director</h3>

<p>Now, <strong>back on the MirrorBrain server</strong>, we need to add the mirror. We can use the <span class="highlight">mb</span> command (as root). There are quite a few variables in this command, which we'll explain below:</p>
<pre class="code-pre "><code langs="">mb new <span class="highlight">london1.download.example.org</span>
       -H http://<span class="highlight">london1.download.example.org</span>
       -R rsync://<span class="highlight">london1.download.example.org</span>/main
       --operator-name=<span class="highlight">Example</span> --operator-url=<span class="highlight">example.org</span>
       -a <span class="highlight">"Pat Admin"</span> -e <span class="highlight">pat@example.org</span> 
</code></pre>
<ul>
<li>Replace <span class="highlight">london1.download.example.org</span> with the nickname for this mirror. It doesn't have to resolve</li>
<li><strong>-H</strong> should resolve to your server; you can use a domain or IP address</li>
<li><strong>-R</strong> should resolve to your server; you can use a domain or IP address</li>
<li>The <code>--operator-name</code>, <code>--operator-url</code>, <code>-a</code>, and <code>-e</code> settings should be your preferred administrator contact information that you want to publish</li>
</ul>

<p>Then, let's scan and enable the mirror. You'll need to use the same nickname you used in the <code>new</code> command:</p>
<pre class="code-pre "><code langs="">mb scan --enable <span class="highlight">london1.download.example.org</span>
</code></pre>
<p>Note: If you run into an error like <span class="highlight">Can't locate LWP/UserAgent.pm in <a href="https://indiareads/community/users/inc" class="username-tag">@INC</a></span> you should go back to the <strong>Step Two</strong> section and run <code>perl -MCPAN -e 'install Bundle::LWP'</code> again.</p>

<p>Assuming the scan is successful (MirrorBrain can connect to the server), the mirror will be added to the database.</p>

<h3 id="testing">Testing</h3>

<p>Now try going to the MirrorBrain instance on the director server (e.g., download.example.org - not london1.download.example.org). Again, click on a file, and append ".mirrorlist" to the end of the URL. You should now see the new mirror listed under the available mirrors section.</p>

<p>You can add more mirrors with your own servers in other places in the world, or you can use <strong>mb new</strong> to add a mirror that somebody else is running for you.</p>

<h2 id="disabling-and-re-enabling-mirrors">Disabling and Re-enabling Mirrors</h2>

<p>If you wish to disable a mirror, that is as simple as running:</p>
<pre class="code-pre "><code langs="">mb disable <span class="highlight">london1.download.example.org</span>
</code></pre>
<p>Re-enable the mirror using the <code>mb scan --enable <span class="highlight">london1.download.example.org</span></code> command as used above.</p>

    