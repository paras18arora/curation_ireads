<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://hhvm.com/">HHVM</a> is an open source virtual machine for executing PHP and <a href="http://hacklang.org/">Hack</a> code. HHVM is developed and supported by Facebook, a fact which draws more and more attention to HHVM lately. </p>

<p>HHVM is different from other PHP engines because of its just-in-time (JIT) compilation approach. HHVM compiles PHP code into an intermediate byte code and then directly into x64 machine instructions. This allows more optimizations and higher performance compared to the way other engines work.</p>

<p>HHVM is powerful and fast, but it's also demanding in terms of resources, just as any other virtual machine (e.g. JVM). Thus, HHVM requires more RAM and CPU in comparison to other more lightweight PHP interpreters such as PHP-FPM. Our tests showed that decent performance requires a Droplet with at least 1 GB RAM.</p>

<p>In this article we'll show you how to install HHVM and integrate it with Nginx. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide has been tested on Ubuntu 14.04. The described installation and configuration would be similar on other OS or OS versions, but the commands and location of configuration files may vary.</p>

<p>For this tutorial, you will need:</p>

<ul>
<li>Ubuntu 14.04 Droplet with a minimum of 1 GB of RAM</li>
<li>A non-root sudo user (see <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>)</li>
<li>Nginx installed (Follow step one from the article <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04">How To Install Linux, Nginx, MySQL, PHP (LEMP) stack on Ubuntu 14.04</a>)</li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>.</p>

<h2 id="installation">Installation</h2>

<p>For Ubuntu 14.04 there is an officially supported HHVM repository. To add this repository you have to import its GnuPG public keys with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
</li></ul></code></pre>
<p>After that you can safely install HHVM's repository with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo add-apt-repository "deb http://dl.hhvm.com/ubuntu $(lsb_release -sc) main"
</li></ul></code></pre>
<p>Once you have the repository added you have to make apt, Ubuntu's software manager, aware that there are new packages which can be installed with it. This can be done by updating apt's cache with the command: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Finally, you can install HHVM with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install hhvm
</li></ul></code></pre>
<p>The above command installs HHVM and starts it for the first time. To make sure HHVM starts and stops automatically with the Droplet, add HHVM to the default runlevels with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo update-rc.d hhvm defaults
</li></ul></code></pre>
<h2 id="configuration">Configuration</h2>

<p>HHVM comes with a script which makes the integration with Nginx very easy. Provided you have a default Nginx installation, you can run the script without any arguments like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /usr/share/hhvm/install_fastcgi.sh
</li></ul></code></pre>
<p>When run, this script adds the configuration file <code>/etc/nginx/hhvm.conf</code> to the default Nginx server block configuration <code>/etc/nginx/sites-enabled/default</code>. It works only with a default Nginx configuration without any FastCGI configurations.</p>

<p>If you have already modified your default server block with custom FastCGI configuration, such as the one for PHP-FPM, then you will have to manually replace your previous FastCGI configuration with this one:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">location ~ \.(hh|php)$ {
    fastcgi_keep_conn on;
    fastcgi_pass   127.0.0.1:9000;
    fastcgi_index  index.php;
    fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include        fastcgi_params;
}
</code></pre>
<p>The above means that Nginx should use HHVM to process any <code>.php</code> or <code>.hh</code> (hack) requested file.</p>

<p>You will also need to restart Nginx to enable the change:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p>It also assumes that you haven't changed the default HHVM configuration that makes the service accessible at <code>127.0.0.1:9000</code>. This setting comes from the main HHVM configuration file <code>/etc/hhvm/server.ini</code> which contains:</p>
<div class="code-label " title="/etc/hhvm/server.ini">/etc/hhvm/server.ini</div><pre class="code-pre "><code langs="">; php options

pid = /var/run/hhvm/pid

; hhvm specific

hhvm.server.port = 9000
hhvm.server.type = fastcgi
hhvm.server.default_document = index.php
hhvm.log.use_log_file = true
hhvm.log.file = /var/log/hhvm/error.log
hhvm.repo.central.path = /var/run/hhvm/hhvm.hhbc
</code></pre>
<p>In the above configuration you may notice the variable <code>hhvm.server.port</code> which determines that HHVM will be listening on TCP port 9000. Furthermore, unless otherwise specified, it will listen on localhost by default. </p>

<p>HHVM is considered for environments under heavy load so the first configuration change you can do is to make HHVM listen to a socket instead of a TCP port. Thus, the communication between Nginx and HHVM will require less CPU and memory. </p>

<p>To configure HHVM to listen on a socket, open the file <code>/etc/hhvm/server.ini</code> in your favorite editor such as with <code>vim</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/hhvm/server.ini
</li></ul></code></pre>
<p>Then remove the line starting with <code>hhvm.server.port</code>, and in its place add the following one:</p>
<div class="code-label " title="/etc/hhvm/server.ini">/etc/hhvm/server.ini</div><pre class="code-pre "><code langs="">hhvm.server.file_socket=/var/run/hhvm/hhvm.sock
</code></pre>
<p>Save the configuration file, and restart HHVM with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service hhvm restart
</li></ul></code></pre>
<p>Next, you have to make Nginx aware of this change. For this purpose open the file <code>/etc/nginx/hhvm.conf</code> for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/nginx/hhvm.conf
</li></ul></code></pre>
<p>In this file make sure that the <code>fastcgi_pass</code> directive points to the HHVM socket and looks like this:</p>
<pre class="code-pre "><code langs="">fastcgi_pass <span class="highlight">unix:/var/run/hhvm/hhvm.sock</span>;
</code></pre>
<p>You will have to restart Nginx too for this change to take effect. For this purpose use the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p>The script <code>/usr/share/hhvm/install_fastcgi.sh</code> can save you some time, but there are things you have to adapt manually, especially in regards to your Nginx server blocks. For example, the default server block configuration opens as index files only <code>index.html</code> and <code>index.htm</code> files while directory listing is forbidden. This is one thing you should change for sure and include <code>index.php</code> files as index files too. To do this open again the configuration file for the default server block with your favorite editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Then go to the <code>server</code> part and add <code>index.php</code> at the line with indexes so that it looks like this: </p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">index index.html index.htm index.php;
</code></pre>
<p>Restart again Nginx for this setting to take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<h2 id="testing-and-tweaking-hhvm">Testing and Tweaking HHVM</h2>

<p>The first test you can perform is with the PHP command line interface (cli) <code>/usr/bin/php</code> which points to <code>/etc/alternatives/php</code>, which in term points to the HHVM binary <code>/usr/bin/hhvm</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">/usr/bin/php --version
</li></ul></code></pre>
<p>When you run the above command you should see printed HHVM's version and repository information like this this:</p>
<pre class="code-pre "><code langs="">HipHop VM 3.8.1 (rel)
Compiler: tags/HHVM-3.8.1-0-g3006bc45691762b5409fc3a510a43093968e9660
Repo schema: 253b3802ce1bcd19e378634342fc9c245ac76c33
</code></pre>
<p>If you have had PHP installed before HHVM, you may still see the output from the old PHP. To change this and make it point to HHVM run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /usr/bin/update-alternatives --install /usr/bin/php php /usr/bin/hhvm 60
</li></ul></code></pre>
<p>Next, you can use the well-known <code>phpinfo()</code> function to see HHVM's settings and options. For this purpose create a new file called <code>info.php</code> inside your default document root — <code>/usr/share/nginx/html</code> with your favorite editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vim /usr/share/nginx/html/info.php
</li></ul></code></pre>
<p>The new file should contain:</p>
<div class="code-label " title="/usr/share/nginx/html/info.php">/usr/share/nginx/html/info.php</div><pre class="code-pre "><code langs=""><?php
phpinfo();
?>
</code></pre>
<p>It is always a good practice to make sure that all Nginx web files are owner by the Nginx user <code>www-data</code>. Thus change the ownership of this file to <code>www-data</code> with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown www-data: /usr/share/nginx/html/info.php
</li></ul></code></pre>
<p>Now try to access this file at your Droplet's IP. The URL to put in your browser should look like <code>http://<span class="highlight">your_server_ip</span>/info.php</code>. </p>

<p>The result in your browser should look like this:</p>

<p><img src="https://assets.digitalocean.com/articles/HHVM_ubuntu1404/HHVMinfo.png" alt="HHVM's PHP info" /></p>

<p>If you don't see a similar page then first make sure that you have followed correctly the installation instructions from the prerequisites. Second, look for errors in the error log of Nginx (<code>/var/log/nginx/error.log</code>) and HHVM (<code>/var/log/hhvm/error.log</code>). </p>

<p>Going back to your browser, you may notice that this page is similar to the one produced by <code>phpinfo()</code> with the usual PHP. In fact, most of the variables are identical to those from the usual PHP with the exception of the HHVM-specific variables starting with the <code>hhvm.</code> prefix.</p>

<p>While exploring the variables note that <code>memory limit</code> is equal to 17179869184 bytes which is a little bit over 17 GB. Such a high memory resource limit will certainly kill a Droplet with a few GB of RAM, making it unresponsive. You should decrease this value to a value lower than the available RAM of your Droplet to ensure that other services on the Droplet will not suffer from lack of RAM.</p>

<p>As a general example, if your Droplet has 2GB of RAM it should be safe to dedicate around 1.2 GB to HHVM. To make this happen, edit the file <code>/etc/hhvm/php.ini</code> with your favorite editor  (<code>sudo vim /etc/hhvm/php.ini</code>) and add a new variable after the <code>; php options</code> section:</p>
<div class="code-label " title="/etc/hhvm/php.ini">/etc/hhvm/php.ini</div><pre class="code-pre "><code langs="">memory_limit = <span class="highlight">1200M</span>
</code></pre>
<p>In a similar way, you can modify any PHP setting and variable to your needs and liking. Just make sure to restart HHVM after every change with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service hhvm restart
</li></ul></code></pre>
<p>Next, you can perform a more complex test with a common web application. It's important to know that HHVM is not 100% compatible with the usual PHP nor with all popular PHP frameworks. Our tests during the writing of this article showed that many PHP web applications, such as WordPress, seem to work fine. However, officially, the number of <a href="http://hhvm.com/frameworks/">supported frameworks</a> is limited. </p>

<p>When you test with a complete framework/web application there should be nothing HHVM-specific to consider. The installation and operational instructions should be the same as for a regular LEMP stack. This is because, by default, HHVM comes bundled with all most PHP modules providing good compatibility.</p>

<p>Still, in some rather rare cases you may need to install an additional module for HHVM. For example, if you use PostreSQL as a database server you will need the <code>pgsql</code> module. In such cases consult first <a href="http://docs.hhvm.com/manual/en/index.php">HHVM's official documentation</a> even though it may forward you to a third party resource, such as in the case of <code>pgsql</code>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>As this article showed, HHVM can be easily installed, configured, and integrated with Nginx. If you have enough resources you should definitely give it a try and see how HHVM's unique JIT compiler works for you in terms of performance and stability. There must be a good reason for a site like Facebook with complex functionality and unmatched traffic to trust it. However, for smaller sites with less traffic you may find a lower memory footprint solution such as PHP-FPM still a better choice.</p>

    