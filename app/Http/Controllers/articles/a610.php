<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/linux_dash_tw.jpg?1426699819/> <br> 
      <h3 id="an-article-from-linux-dash">An Article from <a href="http://linuxdash.afaqtariq.com/#/system-status">Linux Dash</a></h3>

<h2 id="introduction">Introduction</h2>

<p>Linux Dash is an open-source dashboard to monitor Linux servers. It prides itself on its simplicity and ease of use. It can be very handy to have a high-level dashboard for a server instance. With a wide array of modules for server statistics, it also serves as a great visual debugging tool.</p>

<ul>
<li><p>Before installing the software, you can try the <a href="http://linuxdash.afaqtariq.com/">demo here</a>.</p></li>
<li><p>At the time of writing, Linux Dash supports PHP on Apache and Nginx, Go, and Node.js. For this tutorial, <strong>we will be covering a PHP and Apache stack installation</strong>. </p></li>
<li><p>For information on installing on a different stack, please refer to the <a href="https://github.com/afaqurk/linux-dash#installation">installation section of the GitHub Project</a>.</p></li>
</ul>

<h3 id="prerequisites">Prerequisites</h3>

<p>Please complete these prerequisites.</p>

<ul>
<li>Add a <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo user</a></li>
<li>Follow <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">this tutorial</a> to install Apache and PHP on your Droplet. Please note that MySQL is not required for Linux Dash, so you may skip that section</li>
<li><p>Install Git if you plan to use the Git installation method</p>
<pre class="code-pre "><code langs="">sudo apt-get install git
</code></pre></li>
</ul>

<h2 id="step-1-—-installing-linux-dash">Step 1 — Installing Linux Dash</h2>

<p>The following steps will install Linux Dash on your server.</p>

<p>First, you will need to SSH into your Droplet. See <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">this IndiaReads tutorial</a> if you need directions.</p>

<p>Next, navigate to the web root directory.</p>
<pre class="code-pre "><code langs="">cd /var/www/html/
</code></pre>
<p>Use Git to download Linux Dash.</p>
<pre class="code-pre "><code langs="">sudo git clone https://github.com/afaqurk/linux-dash.git
</code></pre>
<blockquote>
<p>Alternatively, for <a href="https://indiareads/community/tutorials/how-to-install-and-use-composer-on-your-vps-running-ubuntu">Composer</a>, you can run <code>composer create-project afaqurk/linux-dash -s dev</code>.</p>
</blockquote>

<p>Make sure <code>shell_exec</code> is enabled. If this is a fresh Apache installation, it should be already.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/php5/apache2/php.ini
</code></pre>
<p>Locate the <code>disable_functions</code> line and make sure <code>shell_exec</code> and <code>exec</code> are <strong>not</strong> listed.</p>

<p>At this point, you should be able to visit <code>http://<span class="highlight">your_server_ip</span>/linux-dash</code> and see the dashboard.</p>

<p><img src="https://assets.digitalocean.com/articles/linux_dash/1.png" alt="Linux Dash home page" /></p>

<h2 id="step-2-—-password-protecting-the-dashboard">Step 2 — Password-Protecting the Dashboard</h2>

<p>Linux Dash gives web access to private and sensitive information about your server. It is strongly suggested that you restrict access to this directory.</p>

<p>Follow these steps to password-protect Linux Dash. For more information on using the <code>.htaccess</code> file, please see <a href="https://indiareads/community/tutorials/how-to-use-the-htaccess-file">this tutorial</a>.</p>

<p>Open your Apache virtual hosts file for editing:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-available/000-default.conf
</code></pre>
<p>Add a new <code>Directory</code> block for the <code>linux-dash</code> directory. This can be anywhere within the <code><VirtualHost *:80></code> block:</p>
<pre class="code-pre "><code langs=""><Directory /var/www/html/linux-dash>
        Options FollowSymLinks
        AllowOverride All
        Order allow,deny
        allow from all
</Directory>
</code></pre>
<p>This enables the use of a <code>.htaccess</code> file. Now, create the <code>.htaccess</code> file in the Linux Dash directory:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/linux-dash/.htaccess
</code></pre>
<p>Enable basic password authentication for this directory, and designate the password file:</p>
<pre class="code-pre "><code langs="">AuthType Basic
AuthName "Restricted Files"
AuthUserFile /var/www/html/linux-dash/.htpasswd
Require valid-user
</code></pre>
<p>Create the password file with your chosen username and password.</p>
<pre class="code-pre "><code langs="">sudo htpasswd -c /var/www/html/linux-dash/.htpasswd <span class="highlight">sammy</span>
</code></pre>
<p>Enter your new password at the prompts:</p>
<pre class="code-pre "><code langs="">New password: 
Re-type new password: 
Adding password for user <span class="highlight">sammy</span>
</code></pre>
<p>Finally, restart Apache:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>If you refresh the page at <code>http://<span class="highlight">your_server_ip</span>/linux-dash</code>, you should now be prompted for your credentials. Enter the ones you created in the previous step.</p>

<p>You should be able to see the Linux Dash application. </p>

<h2 id="step-3-—-using-linux-dash">Step 3 — Using Linux Dash</h2>

<p>Linux Dash gives you a bird's-eye view of your server.</p>

<p>There are five sections to Linux Dash in the menu near the top of the page. Each section contains an ever-growing number of modules which display information about the server. Some modules are simple readouts of common commands and files on your server, while others are detailed tables and charts. </p>

<p>You can also filter the information inside a module using the search bar.</p>

<p><img src="https://assets.digitalocean.com/articles/linux_dash/2.png" alt="Search Results" /></p>

<p>When debugging issues, Linux Dash can be a very useful tool. </p>

<p>For example, if a website or application on your Droplet is experiencing lag, you can investigate the <strong>SYSTEM STATUS</strong> section of Linux Dash. Here you can see CPU and RAM usage charts which show live information. If, for instance, the RAM chart shows unusually high usage, you can check the <strong>RAM INTENSIVE PROCESSES</strong> module on the next row to see which processes are struggling. </p>

<p>You can follow the same process for investigating high CPU usage as well.</p>

<h3 id="conclusion">Conclusion</h3>

<p>Now you should have greater insight into your server's status through the Linux Dash dashboard.</p>

<p>For support, please use the following resources:</p>

<ul>
<li>For general community support and questions, please see <a href="https://gitter.im/afaqurk/linux-dash">https://gitter.im/afaqurk/linux-dash</a></li>
<li>To file a bug with the software, use the <a href="https://github.com/afaqurk/linux-dash/issues">GitHub issues list</a></li>
<li>To help build modules or extend Linux Dash features, <a href="https://github.com/afaqurk/linux-dash">fork the repo on GitHub</a></li>
</ul>

    