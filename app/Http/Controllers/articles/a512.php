<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The ports system is one of FreeBSD's greatest assets for users who want flexibility and control over their software.  It allows administrators to easily create and maintain source-based installations using a system designed to be robust and predictable.</p>

<p>While the advantages of this feature are great, some of the most common complaints levied against ports-based administration are regarding the time and resources required to compile each piece of software.  This becomes even more of an issue if you are managing a large number of servers, each of which compiles its own ports.  While FreeBSD packages provide an alternative that speeds up installation, it sacrifices the control that ports grant.</p>

<p>To alleviate this issue, administrators can use an application called <strong>poudriere</strong> to build and maintain custom packages.  While technically created to build packages for a variety of architectures, <code>poudriere</code> is often used as a package building environment to create and host packages for an entire infrastructure of FreeBSD servers.</p>

<p>By leveraging <code>poudriere</code>, administrators can customize software as necessary using the ports system and compile packages for easy installation and software management.  Any number of FreeBSD servers can use a host with <code>poudriere</code> installed as their package source, allowing them to download and install customized, pre-compiled executables quickly and easily.</p>

<p>In this guide, we will demonstrate how to set up a server with <code>poudriere</code> as a build machine.  We can then use this server as the repository for packages for any additional servers.  While this setup can be beneficial for a single server, the real efficiency gains are seen when additional servers begin using the same <code>poudriere</code> host as a package source.</p>

<p>Since port building is a resource intensive process, it may be worthwhile to set this up on a more powerful system than usual.</p>

<h2 id="install-the-necessary-ports-management-software">Install the Necessary Ports Management Software</h2>

<p>To start off, we will install all of the ports that we need.</p>

<p>As always, before beginning any ports-related tasks, we will update our ports tree to make sure that the references on the filesystem are fresh:</p>
<pre class="code-pre "><code langs="">sudo portsnap fetch update
</code></pre>
<p>After the ports tree is updated, we can begin installing software.  First, we need to install <code>poudriere</code> itself.  This is found in the <code>ports-mgmt</code> category of the ports tree.  To build and install, go to that directory and use <code>make</code> to compile and install:</p>
<pre class="code-pre "><code langs="">cd /usr/ports/ports-mgmt/poudriere
sudo make install clean
</code></pre>
<p>Feel free to select any of the options presented.  For a standard build, none are essential.</p>

<p>Next, if you don't already have it installed, we will need to install the <code>portmaster</code> port.  We will use this to easily generate a list of software on our machine that we want <code>poudriere</code> to build.  This is in the <code>ports-mgmt</code> category as well:</p>
<pre class="code-pre "><code langs="">cd /usr/ports/ports-mgmt/portmaster
sudo make install clean
</code></pre>
<p>Finally, we will also want to install a web server.  This will serve two purposes.  First, this will be the method in which our machines will be able to download the packages we will be compiling.  Secondly, <code>poudriere</code> provides a web interface so that we can keep track of the build process and monitor logs.</p>

<p>For this guide, we will be using <code>nginx</code> as our web server.  This is found in the <code>www</code> category of the ports tree:</p>
<pre class="code-pre "><code langs="">cd /usr/ports/www/nginx
sudo make install clean
</code></pre>
<p>You can accept the default values or customize if you have specific needs for another purpose.</p>

<p>When you are finished compiling and installing the software, make sure to re-evaluate your PATH if you are using the default <code>tcsh</code> or <code>csh</code> shells:</p>
<pre class="code-pre "><code langs="">rehash
</code></pre>
<p>Now that our software is installed, we can begin to configure each of our components.</p>

<h2 id="create-an-ssl-certificate-and-key">Create an SSL Certificate and Key</h2>

<p>When we build packages with <code>poudriere</code>, we want to be able to sign them with a private key.  This will ensure all of our machines that the packages created are legitimate and that nobody is intercepting the connection to the build machine to serve malicious packages.</p>

<p>To start off, we will create a directory structure for our key and certificate.  Since all of our optional software configuration takes place within the <code>/usr/local/etc</code> directory, and because other software uses the <code>/usr/local/etc/ssl</code> location, this is where we will place our files.</p>

<p>We will ensure that we have an <code>ssl</code> directory that contains two subdirectories called <code>keys</code> and <code>certs</code>.  We can do this in one command by typing:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /usr/local/etc/ssl/{keys,certs}
</code></pre>
<p>Our private key, which must be kept secret, will be placed in the <code>keys</code> directory.  This will be used to sign the packages that we will be creating.  Keeping this secure is essential to ensuring that our packages are not being tampered with.  We can lock down the directory so that users without root or <code>sudo</code> privileges will be unable to interact with the directory or its contents:</p>
<pre class="code-pre "><code langs="">sudo chmod 0600 /usr/local/etc/ssl/keys
</code></pre>
<p>The <code>certs</code> directory will contain our publicly available certificate created with the key.  As such, we can leave the default permissions on that directory.</p>

<p>Next, we will generate a 4096 bit key called <code>poudriere.key</code>, and place it in our <code>keys</code> directory by typing:</p>
<pre class="code-pre "><code langs="">sudo openssl genrsa -out /usr/local/etc/ssl/keys/poudriere.key 4096
</code></pre>
<p>After the key is generated, we can create a public cert from it by typing:</p>
<pre class="code-pre "><code langs="">sudo openssl rsa -in /usr/local/etc/ssl/keys/poudriere.key -pubout -out /usr/local/etc/ssl/certs/poudriere.cert
</code></pre>
<p>We now have the SSL components we need to sign packages and verify the signatures.  Later on, we will configure our clients to use the generated certificate for package verification.</p>

<h2 id="configuring-poudriere">Configuring Poudriere</h2>

<p>Now that we have our SSL certificate and key, we can begin to configure <code>poudriere</code> itself.</p>

<p>The main configuration file is located at <code>/usr/local/etc/poudriere.conf</code>.  Open this file with <code>sudo</code> privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/poudriere.conf
</code></pre>
<p>The <code>poudriere</code> configuration file is very well commented and has most of the settings we need pre-defined.  We will be making a few specific changes, but leaving the majority of it intact.</p>

<p>If your FreeBSD server is running on IndiaReads, the filesystem will be UFS.  There are ZFS-specific options within <code>poudriere</code> that we should not set.  To indicate that we are using UFS, we must set the <code>NO_ZFS</code> flag to "yes".  Find and uncomment this option within the file:</p>
<pre class="code-pre "><code langs="">NO_ZFS=yes
</code></pre>
<p>If, on the other hand, your server uses ZFS, you can configure <code>poudriere</code> to use a specific pool by setting the <code>ZPOOL</code> option.  Within this pool, you can specify the root that <code>poudriere</code> will use for packages, logs, etc. with the <code>ZROOTFS</code> option.  Note that these two options <strong>should not</strong> be set if you have the <code>NO_ZFS</code> option set to "yes:</p>
<pre class="code-pre "><code langs=""><span class="highlight">#</span> NO_ZFS=yes
ZPOOL=<span class="highlight">tank</span>
ZROOTFS=<span class="highlight">/poudriere</span>
</code></pre>
<p>When building software, <code>poudriere</code> uses a type of jail in order to separate the build system from the main operating system.  Next, we must fill out a valid host where the build machine can download the software it needs for jails.  This is configured through the <code>FREEBSD_HOST</code> option.</p>

<p>This option should already be present, although it is not currently set to a valid host.  You can change this to the default <code>ftp://ftp.freebsd.org</code> location or use a closer mirror if you know of one:</p>
<pre class="code-pre "><code langs="">FREEBSD_HOST=<span class="highlight">ftp://ftp.freebsd.org</span>
</code></pre>
<p>Next, we want to be sure that our data directory within  the <code>poudriere</code> root is set correctly.  This is controlled with the <code>POUDRIERE_DATA</code> option and should be defaulted, but we will uncomment the option just to be sure:</p>
<pre class="code-pre "><code langs="">POUDRIERE_DATA=${BASEFS}/data
</code></pre>
<p>The next options we should uncomment are the <code>CHECK_CHANGED_OPTIONS</code> and <code>CHECK_CHANGED_DEPS</code> options.  The first option tells <code>poudriere</code> to rebuild packages when the options for it have changed.  The second option tells tells <code>poudriere</code> to rebuild packages when dependencies have changed since the last compilation.</p>

<p>Both of these options exist in the form that we want them in the configuration file.  We only need to uncomment them:</p>
<pre class="code-pre "><code langs="">CHECK_CHANGED_OPTIONS=verbose
CHECK_CHANGED_DEPS=yes
</code></pre>
<p>Next, we will point <code>poudriere</code> to the SSL key that we created so that it can sign packages as it builds.  The option used to specify this is called <code>PKG_REPO_SIGNING_KEY</code>.  Uncomment this option and change the path to reflect the location of the SSL key you created earlier:</p>
<pre class="code-pre "><code langs="">PKG_REPO_SIGNING_KEY=<span class="highlight">/usr/local/etc/ssl/keys/poudriere.key</span>
</code></pre>
<p>Finally, we can set the <code>URL_BASE</code> string to the domain name or IP address where your server can be reached.  This will be used by <code>poudriere</code> to construct links in its output that can be clicked.  You should include the protocol and end the value with a slash:</p>
<pre class="code-pre "><code langs="">URL_BASE=http://<span class="highlight">server_domain_or_IP</span>/
</code></pre>
<p>When you are finished making your changes, save and close the file.</p>

<h2 id="creating-the-build-environment">Creating the Build Environment</h2>

<p>Next, we need to actually construct our build environment.  As mentioned earlier, <code>poudriere</code> will build ports in an isolated environment using jails.</p>

<p>We will have to create our jail and have <code>poudriere</code> install FreeBSD inside.  It's possible to have multiple jails, each with a different version of FreeBSD.  The jailed FreeBSD versions must be the same as or older than the version that the server itself is running.  For this guide, we'll be focusing on a single jail that mirrors the architecture and FreeBSD version of the host system.</p>

<p>We should choose a descriptive name for the jail we are creating.  This is important since it will be used in our repository configuration on the clients, which can be very important when building for different versions of FreeBSD.  The <code>man</code> pages also instruct you to avoid using dots in the name due to some interactions with other tools.  For instance, in this guide, we're operating on FreeBSD 10.1 on a 64-bit architecture, so we will call this "freebsd_10-1x64".</p>

<p>We specify the name for our jail with <code>-j</code> and we indicate the version of FreeBSD to install using the <code>-v</code> option.  You can find the format for the supported releases in the "Releases" column of the table on <a href="https://www.freebsd.org/security/">this</a> page.  If you are following <code>-CURRENT</code> or <code>-STABLE</code> instead of the release, you can use the format found on <a href="https://www.freebsd.org/snapshots/">this</a> page (such as <code>11-CURRENT</code>).</p>

<p>For our purposes, our jail construction command will look like this:</p>
<pre class="code-pre "><code langs="">sudo poudriere jail -c -j freebsd_10-1x64 -v 10.1-RELEASE
</code></pre>
<p>This will take awhile to complete, so be patient.  When you are finished, you can see the installed jail by typing:</p>
<pre class="code-pre "><code langs="">poudriere jail -l
</code></pre><pre class="code-pre "><code langs="">JAILNAME        VERSION         ARCH  METHOD TIMESTAMP           PATH
freebsd_10-1x64 10.1-RELEASE-p3 amd64 ftp    2015-01-06 20:43:48 /usr/local/poudriere/jails/freebsd_10-1x64
</code></pre>
<p>Once you have a jail created, we will have to install a ports tree.  It is possible to maintain multiple ports trees in order to serve different development needs.  We will be installing a single ports tree that our jail can utilize.</p>

<p>We can use the <code>-p</code> flag to name our ports tree.  We will call our tree "HEAD" as it accurately summarizes the use of this tree (the "head" or most up-to-date point of the tree).  We will be updating it regularly to match the most current version of the ports tree available:</p>
<pre class="code-pre "><code langs="">sudo poudriere ports -c -p HEAD
</code></pre>
<p>Again, this procedure will take awhile because the entire ports tree must be fetched and extracted.  When it is finished, we can view our ports tree by typing:</p>
<pre class="code-pre "><code langs="">poudriere ports -l
</code></pre>
<p>After this step is complete, we now have the structures in place to compile our ports and build packages.  Next, we can start to assemble our list of ports to build and configure the options we want for each piece of software.</p>

<h2 id="creating-a-port-building-list-and-setting-port-options">Creating a Port Building List and Setting Port Options</h2>

<p>When compiling with <code>poudriere</code>, we indicate the ports we wish to build when calling the build command.  It is possible to specify ports individually, but this is not a good solution for long-term management.  Instead, we will be creating a list of ports that we can pass directly to the command.</p>

<p>The file create should list the port category followed by a slash and the port name to reflect its location within the ports tree, like this:</p>
<pre class="code-pre "><code langs=""><span class="highlight">port_category</span>/<span class="highlight">first_port</span>
<span class="highlight">port_category</span>/<span class="highlight">second_port</span>
<span class="highlight">port_category</span>/<span class="highlight">third_port</span>

. . .
</code></pre>
<p>Any needed dependencies will be automatically built as well, so do not worry about tracking down the entire dependency tree of the ports you wish to install.  You can build this file manually, but if your base system already has most of the software want to build, you can use <code>portmaster</code> to create this list for you.</p>

<p>Before you do this, it's usually a good idea to remove any unneeded dependencies from your system to keep the port list as clean as possible.  You can do this by typing:</p>
<pre class="code-pre "><code langs="">sudo pkg autoremove
</code></pre>
<p>Afterwards, we can get a list of the software we've explicitly installed on our build system using <code>portmaster</code>.  The <code>portmaster</code> command can output a list of explicitly installed ports (not dependencies) in the correct format by using the <code>--list-origins</code> option.</p>

<p>We can pipe this output into <code>sort</code> to alphabetize the list to make it easier to find items.  We can output the results to a file in the <code>/usr/local/etc/poudriere.d</code> directory.  We will call this file <code>port-list</code>:</p>
<pre class="code-pre "><code langs="">portmaster --list-origins | sort -d | sudo tee /usr/local/etc/poudriere.d/port-list 
</code></pre>
<p>Review the list.  If there are any ports that you do not wish to include, remove their associated line.  This is also an opportunity to add additional ports that you might need.</p>

<p>If you use specific <code>make.conf</code> options to build your ports, you can create a <code>make.conf</code> file for each jail within your <code>/usr/local/etc/poudriere.d</code> directory.  For example, for our jail, we can create a <code>make.conf</code> file with this name:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/poudriere.d/<span class="highlight">freebsd_10-1x64</span>-make.conf
</code></pre>
<p>Inside, you can put any options you would like to use when building your ports.  For instance, if you do not want to build any documentation, examples, native language support, or X11 support you can set:</p>
<pre class="code-pre "><code langs="">OPTIONS_UNSET+= DOCS NLS X11 EXAMPLES
</code></pre>
<p>Afterwards, we can configure each of our ports, which will create files with the options we selected.</p>

<p>If you have customized your ports on your host system, you can copy the configurations over to <code>poudriere</code> to utilize those settings.  To do this, create a new directory within the <code>/usr/local/etc/poudriere.d</code> directory named after your jail with <code>-options</code> appended to the end.  For our guide, we can accomplish this by typing:</p>
<pre class="code-pre "><code langs="">sudo mkdir /usr/local/etc/poudriere.d/<span class="highlight">freebsd_10-1x64</span>-options
</code></pre>
<p>Now, you can copy the options you have already been using on your host system by typing:</p>
<pre class="code-pre "><code langs="">sudo cp -r /var/db/ports/* /usr/local/etc/poudriere.d/<span class="highlight">freebsd_10-1x64</span>-options
</code></pre>
<p>If you complete the above step, you will have a baseline for the options we will be configuring, but many of your dependencies will still need to be configured. </p>

<p>You can configure anything which has not been already configured using the <code>options</code> command.  We should pass in both the port tree we created (using the <code>-p</code> option) and the jail we are setting these options for (using the <code>-j</code> option).  We also must specify the list of ports we want to configure using the <code>-f</code> option.</p>

<p>Our command will look like this:</p>
<pre class="code-pre "><code langs="">sudo poudriere options -j freebsd_10-1x64 -p HEAD -f /usr/local/etc/poudriere.d/port-list
</code></pre>
<p>You will see a dialog for each of the ports on the list and any dependencies that do not have corresponding options set in the <code>-options</code> directory.  The specifications in your <code>make.conf</code> file will be preselected in the selection screens.  Select all of the options you would like to use.</p>

<p>If you wish to reconfigure the options for your ports in the future, you can re-run the command above with the <code>-c</code> option.  This will show you all of the available configuration options, regardless of whether you have made a selection in the past:</p>
<pre class="code-pre "><code langs="">sudo poudriere options -c -j freebsd_10-1x64 -p HEAD -f /usr/local/etc/poudriere.d/port-list
</code></pre>
<h2 id="building-the-ports">Building the Ports</h2>

<p>Now, we are finally ready to start building ports.</p>

<p>The last thing we need to do is ensure that both our jail and ports tree are up-to-date.  This probably won't be an issue the first time you build ports since we just created both the ports tree and the jail, but it is good to get in the habit to do this each time you run a build.</p>

<p>To update your jail, type:</p>
<pre class="code-pre "><code langs="">sudo poudriere jail -u -j freebsd_10-1x64
</code></pre>
<p>To update your ports tree, type:</p>
<pre class="code-pre "><code langs="">sudo poudriere ports -u -p HEAD
</code></pre>
<p>Once that is complete, we can kick off the bulk build process.</p>

<p><strong>Note</strong>: This can be a very long running process.  If you are connected to your server over SSH, we recommend that you install <code>screen</code> and start a session:</p>
<pre class="code-pre "><code langs="">cd /usr/ports/sysutils/screen
sudo make install clean

rehash
screen
</code></pre>
<p>To start the build, we just need to use the <code>bulk</code> command and point to all of our individual pieces that we have been configuring.  If you've been using the values from this guide, the command will look like this:</p>
<pre class="code-pre "><code langs="">sudo poudriere bulk -j freebsd_10-1x64 -p HEAD -f /usr/local/etc/poudriere.d/port-list
</code></pre>
<p>This will start up a number of workers (depending on your <code>poudriere.conf</code> file or the number of CPUs available) and begin building the ports.</p>

<p>At any time during the build process, you can get information about the progress by holding the <code>CTRL</code> key and hitting <code>t</code>:</p>
<pre class="code-pre "><code langs="">CTRL-t
</code></pre>
<p>Certain parts of the process will produce more output than others.</p>

<p>If you need to step away, you can detach the screen session by hitting <code>CTRL</code> with <code>a</code> to move control to <code>screen</code>, followed by the <code>d</code> key to detach the session:</p>
<pre class="code-pre "><code langs="">CTRL-a d
</code></pre>
<p>When you wish to return to the session you can type:</p>
<pre class="code-pre "><code langs="">screen -x
</code></pre>
<h2 id="setting-up-nginx-to-serve-the-front-end-and-repository">Setting Up Nginx to Serve the Front End and Repository</h2>

<p>While your packages are building, we can take the opportunity to configure Nginx.  Open another terminal, detach your <code>screen</code> session as shown above, or start a new <code>screen</code> window by typing <code>CTRL-a c</code> (you can switch between windows by typing <code>CTRL-a n</code> and <code>CTRL-a p</code>).</p>

<p>The web server will be used for two distinct purposes:</p>

<ul>
<li>It will serve the actual package repository that other hosts can use to download your custom compiled packages</li>
<li>It will serve the <code>poudriere</code> web front-end that can be used to monitor the build process</li>
</ul>

<p>We installed Nginx at the beginning of this guide, but have not configured it.</p>

<p>To begin, enable the service by adding the <code>nginx_enable="YES"</code> line to the <code>/etc/rc.conf</code> file.  This will start the server at boot and will allow us to use the conventional <code>service</code> commands to manage the process:</p>
<pre class="code-pre "><code langs="">sudo sh -c "echo 'nginx_enable="YES"' >> /etc/rc.conf"
</code></pre>
<p>Now, we can adjust the default configuration file.  Open it with <code>sudo</code> privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/nginx/nginx.conf
</code></pre>
<p>Within this file, we will remove everything from the <code>server {}</code> block and replace it with our own configuration.  Make sure that you leave the matching braces ("{" and "}") intact to ensure that your file is valid.  </p>

<p>Within the <code>server</code> context, we can set up some basic directives to allow our web server to respond to conventional HTTP traffic on port 80 and to respond to our server's domain name or IP address.  We will also set the document root of the server to the <code>poudriere</code> web directory found at <code>/usr/local/share/poudriere/html</code>.  Change the value of the <code>server_name</code> directive below to match your server's domain name or IP address:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># http context

. . .

    server {
        listen 80 default;
        server_name <span class="highlight">server_domain_or_IP</span>;
        root /usr/local/share/poudriere/html;
    }

}
</code></pre>
<p>Next, we will add two <code>location</code> blocks.</p>

<p>While the basic <code>root</code> directive we defined above will handle the majority of the web interface, we need to tell Nginx which directory we store logs and the actual data.  Poudriere will use the <code>/data</code> endpoint for this.  Our logs are all written to a specific directory, so we can turn the <code>autoindex</code> directive on for this location so that we can view the list of logs.</p>

<p>In the end, our first <code>location</code> block will look like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx"># http context

. . .

    server {
        listen 80 default;
        server_name <span class="highlight">server_domain_or_IP</span>;
        root /usr/local/share/poudriere/html;

        location /data {
            alias /usr/local/poudriere/data/logs/bulk;
            autoindex on;
        }
    }
}
</code></pre>
<p>This should make our web interface function correctly (after an additional modification to the <code>mime.types</code> file that we will make in a moment).  Next, we need to add our second location block which will be used to serve the actual packages we've been building.</p>

<p>The packages will be stored in a directory under <code>/usr/local/poudriere</code> again, this time under <code>data/packages</code>.  We can can make this available at the <code>/packages</code> location.  Again, we can turn on <code>autoindex</code> to view the contents of the directory, allowing us to also view the files in a web browser.</p>

<p>Once this final modification is complete, the <code>server</code> block should look like this:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">#http context

. . .

    server {
        listen 80 default;
        server_name <span class="highlight">server_domain_or_IP</span>;
        root /usr/local/share/poudriere/html;

        location /data {
            alias /usr/local/poudriere/data/logs/bulk;
            autoindex on;
        }

        location /packages {
            root /usr/local/poudriere/data;
            autoindex on;
        }
    }
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Next, we will make one small modification to our <code>mime.types</code> file.  With the current settings, if you click on a log in the web browser, it will download the file instead of displaying it as plain text.  We can change this behavior by marking files ending in <code>.log</code> as plain text files.</p>

<p>Open the Nginx <code>mime.types</code> file with sudo privileges in your text editor:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/nginx/mime.types
</code></pre>
<p>Find the entry that specifies the <code>text/plain</code> content type and append <code>log</code> to the end of the current list of filetypes, separated by a space:</p>
<pre class="code-pre "><code class="code-highlight language-nginx">. . .

text/mathml                         mml;
text/plain                          txt <span class="highlight">log</span>;
text/vnd.sun.j2me.app-descriptor    jad;

. . .
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, check the syntax of your configuration files by typing:</p>
<pre class="code-pre "><code langs="">sudo service nginx configtest
</code></pre>
<p>If you have any errors, fix them before proceeding.  If your configuration test reports no syntax errors, start Nginx by typing:</p>
<pre class="code-pre "><code langs="">sudo service nginx start
</code></pre>
<p>If you have a firewall enabled, remember to configure your rules to allow traffic to port 80 and restart the service.</p>

<p>Now, you can view the <code>poudriere</code> web interface by going to your server's domain name or IP address in your web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p>You should see the main <code>poudriere</code> page:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_poudriere/main.png" alt="Poudriere main page" /></p>

<p>If you click through, you should be able to view the process or results of your port building.  You should also be able to click through to the logs of any build that has finished.</p>

<p>If you want to view your compiled packages in the browser, those should be available through a hierarchy starting at <code>/packages</code>:</p>

<p><img src="https://assets.digitalocean.com/articles/freebsd_poudriere/packages.png" alt="Poudriere packages repo" /></p>

<p>You may have to wait until the entire build is complete in order to see the packages.  Clicking through the links will show you the compiled packages that you produced with your <code>poudriere</code> bulk build command.</p>

<h2 id="configuring-package-clients">Configuring Package Clients</h2>

<p>Now that you have packages built and a repository configured to serve your packages, you can configure your clients to use your the server as the source of their packages.</p>

<h3 id="configuring-the-build-server-to-use-its-own-package-repo">Configuring the Build Server to Use Its Own Package Repo</h3>

<p>We can begin by configuring the build server to use the packages it has been building.</p>

<p>First, we need to make a directory to hold our repository configuration files:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /usr/local/etc/pkg/repos
</code></pre>
<p>Inside this directory, we can create our repository configuration file.  It must end in <code>.conf</code>, so we will call it <code>poudriere.conf</code> to reflect its purpose:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/pkg/repos/poudriere.conf
</code></pre>
<p>We will define the repository name as <code>poudriere</code> once again.  Inside the definition, we will point to the location on disk where our packages are stored.  This should be a directory that combines your jail name and port tree name with a dash.  Check your filesystem to be certain.  We will also set up signature validation of our packages by pointing to the certificate we created.</p>

<p>In the end, your file should look something like this:</p>
<pre class="code-pre "><code langs="">poudriere: {
    url: "file:///usr/local/poudriere/data/packages/<span class="highlight">freebsd_10-1x64-HEAD</span>",
    mirror_type: "srv",
    signature_type: "pubkey",
    pubkey: "/usr/local/etc/ssl/certs/poudriere.cert",
    enabled: yes
}
</code></pre>
<p>At this point, you need to make a decision.  If you want to <em>prefer</em> your compiled packages and fall back on the packages provided by the main FreeBSD repositories, you can set a priority here, telling it to prefer packages out of this repository.  This will cause our local repository to take priority over the official repositories.  </p>

<p>Keep in mind that mixing packages in this way can have some complicated consequences.  If the official repositories have a package version that is higher than your local repository version, your compiled package may be replaced by the generic one from the official repositories (until you rebuild with <code>poudriere</code> and reinstall with <code>pkg</code>).  Also, the official packages may assume that dependent packages are built in a certain way and may not function when mixed with your custom packages.</p>

<p>If you choose to mix these two package sources, be prepared to carefully audit each install to ensure that you are not accidentally causing undesirable behavior.</p>

<p>To mix packages, add a <code>priority</code> setting to your repository definition, specifying that the local repo has a higher precedence:</p>
<pre class="code-pre "><code langs="">poudriere: {
    url: "file:///usr/local/poudriere/data/packages/<span class="highlight">freebsd_10-1x64-HEAD</span>",
    mirror_type: "srv",
    signature_type: "pubkey",
    pubkey: "/usr/local/etc/ssl/certs/poudriere.cert",
    enabled: yes,
    priority: 100
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>If you chose <strong>only</strong> to install packages that you have custom built yourself (the safer route), you can leave out the priority setting, but you will want to disable the default repositories.  You can do this by creating a different repo file that overrides the default repository file and disables it:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/pkg/repos/freebsd.conf
</code></pre>
<p>Inside, use the name <code>FreeBSD</code> in order to match the default repository definition.  Disable the repository by defining it like this:</p>
<pre class="code-pre "><code langs="">FreeBSD: {
    enabled: no
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Regardless of your configuration choice, you should now be ready to use your repository.  Update your package list by typing:</p>
<pre class="code-pre "><code langs="">sudo pkg update
</code></pre>
<p>Now, your server can use the <code>pkg</code> command to install packages from your local repository.</p>

<h3 id="configuring-remote-clients-to-use-your-build-machine-39-s-repository">Configuring Remote Clients to Use your Build Machine's Repository</h3>

<p>One of the most compelling reasons to set up <code>poudriere</code> on a build machine is to use that host as the repository for many other machines.  All we need to do to make this work is download the public SSL cert from our build machine and set up a similar repository definition.</p>

<p>In order to connect to our build host from our client machines, you should start an SSH agent on your <strong>local computer</strong> to store your SSH key credentials.</p>

<p>OpenSSL comes with an SSH agent that can be started by typing this on your home computer:</p>
<pre class="code-pre "><code langs="">eval $(ssh-agent)
</code></pre>
<p>Next, you will need to add your SSH key to it by typing:</p>
<pre class="code-pre "><code langs="">ssh-add
</code></pre>
<p>Afterwards, you can forward your local SSH credentials to your client machines when you connect by using the <code>-A</code> flag.  This will allow you to access any machine from your client machine as if you were accessing it from your home machine:</p>
<pre class="code-pre "><code langs="">ssh -A <span class="highlight">freebsd</span>@<span class="highlight">client_domain_or_IP</span>
</code></pre>
<p>Once you are on your remote client machine, the first step is to create the directory structure (if it doesn't exist) for you to store the certificate.  We will go ahead and create a directory for keys too so that we can use that for future tasks:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /usr/local/etc/ssl/{keys,certs}
</code></pre>
<p>Now we can connect to our build machine with SSH and pipe the certificate file back to our client machine.  Since we forwarded our SSH credentials, we should be able to do this without being prompted for a password:</p>
<pre class="code-pre "><code langs="">ssh freebsd@<span class="highlight">server_domain_or_IP</span> 'cat /usr/local/etc/ssl/certs/poudriere.cert' | sudo tee /usr/local/etc/ssl/certs/poudriere.cert
</code></pre>
<p>This command will connect to the build machine from your client machine using your local SSH credentials.  Once connected, it will display the contents of your certificate file and pipe it through the SSH tunnel back to your remote client machine.  From there, we use the <code>sudo tee</code> combination to write the certificate to our directory.</p>

<p>Once this is complete, we can make our repository directory structure just as we did on the build machine itself:</p>
<pre class="code-pre "><code langs="">sudo mkdir -p /usr/local/etc/pkg/repos
</code></pre>
<p>Now, we can create a repository file that is very similar to the one we used on the build machine:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/pkg/repos/poudriere.conf
</code></pre>
<p>The differences are the URL location and the mirror type.  Again, we can either choose to mix packages or use <em>only</em> our custom compiled packages.  The same warnings apply in regards to mixing package sources.</p>

<p>If you want to mix your custom packages with those of the official repositories, your file should look something like this:</p>
<pre class="code-pre "><code langs="">poudriere: {
    url: "http://<span class="highlight">server_domain_or_IP</span>/packages/<span class="highlight">freebsd_10-1x64-HEAD</span>/",
    mirror_type: "http",
    signature_type: "pubkey",
    pubkey: "/usr/local/etc/ssl/certs/poudriere.cert",
    enabled: yes,
    priority: 100
}
</code></pre>
<p>If you want to only use your compiled packages, your file should look something like this:</p>
<pre class="code-pre "><code langs="">poudriere: {
    url: "http://<span class="highlight">server_domain_or_IP</span>/packages/<span class="highlight">freebsd_10-1x64-HEAD</span>/",
    mirror_type: "http",
    signature_type: "pubkey",
    pubkey: "/usr/local/etc/ssl/certs/poudriere.cert",
    enabled: yes
</code></pre>
<p>In addition, if you are using only your own packages, remember to create another repository config file to override the default FreeBSD repository configuration:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/pkg/repos/freebsd.conf
</code></pre>
<p>Place the following content in the file to disable the official repositories:</p>
<pre class="code-pre "><code langs="">FreeBSD: {
    enabled: no
}
</code></pre>
<p>After you are finished, update your <code>pkg</code> database to begin using your custom compiled packages:</p>
<pre class="code-pre "><code langs="">sudo pkg update
</code></pre>
<p>This procedure can be repeated on as many FreeBSD client machines as you would like.</p>

<h2 id="rebuilding-your-packages-when-updates-are-available">Rebuilding Your Packages When Updates are Available</h2>

<p>You should now have your entire <code>poudriere</code> setup running.  However, you will need to rebuild your packages from time-to-time when new updates become available, especially if they are security related.</p>

<p>Fortunately, the procedure for rebuilding packages is rather straight forward.  First, you should update your FreeBSD jail so that your packages are built against the up-to-date operating system.  You can do that by typing:</p>
<pre class="code-pre "><code langs="">sudo poudriere jail -u -j freebsd_10-1x64
</code></pre>
<p>Next, you should update your ports tree so that the latest version of each port is available to your jail.  You can do that by typing:</p>
<pre class="code-pre "><code langs="">sudo poudriere ports -u -p HEAD
</code></pre>
<p>After the jail and ports tree are updated, you can modify your port list if there are any changes that you want to make:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/poudriere.d/port-list
</code></pre>
<p>If you need to adjust any <code>make.conf</code> options, you can do so by editing the file associated with your build:</p>
<pre class="code-pre "><code langs="">sudo vi /usr/local/etc/poudriere.d/<span class="highlight">freebsd_10-1x64</span>-make.conf
</code></pre>
<p>You can check for any new options for your ports by typing this:</p>
<pre class="code-pre "><code langs="">sudo poudriere options -j freebsd_10-1x64 -p HEAD -f /usr/local/etc/poudriere.d/port-list
</code></pre>
<p>If you would like to instead review <em>all</em> of the options for your ports, you can add the <code>-c</code> flag.  This can be helpful when troubleshooting build or runtime issues:</p>
<pre class="code-pre "><code langs="">sudo poudriere options -c -j freebsd_10-1x64 -p HEAD -f /usr/local/etc/poudriere.d/port-list
</code></pre>
<p>When you have completed the above preparatory steps, you can recompile any ports that were altered or have been updated by typing:</p>
<pre class="code-pre "><code langs="">sudo poudriere bulk -j freebsd_10-1x64 -p HEAD -f /usr/local/etc/poudriere.d/port-list
</code></pre>
<p>Feel free to monitor the progress in the web interface.  Once the new packages are compiled, you can updated the packages on each machine by typing:</p>
<pre class="code-pre "><code langs="">sudo pkg upgrade
</code></pre>
<p>This will allow you to update your custom packages on your entire FreeBSD infrastructure quite easily.</p>

<h2 id="conclusion">Conclusion</h2>

<p>In this guide, we covered how to configure <code>poudriere</code> to compile and package a custom set of ports for both our build machine and external clients.  The process might seem lengthy at first glance, but it is rather simple to manage once you have it up and running.</p>

<p>By leveraging <code>poudriere</code>, you are able to take advantage of both of FreeBSD's optional software management systems.  For many users, this constitutes the best of both worlds.  A <code>poudriere</code> build system allows you to customize software in as you see fit on a single machine while utilizing the fast <code>pkg</code> system for actual installation and management.</p>

    