<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Relational database management systems like MySQL and MariaDB are needed for a significant portion of web sites and applications.  However, not all users feel comfortable administering their data from the command line.</p>

<p>To solve this problem, a project called phpMyAdmin was created in order to offer an alternative in the form of a web-based management interface.  In this guide, we will demonstrate how to install and secure a phpMyAdmin configuration on a CentOS 7 server.  We will build this setup on top of the Nginx web server, which has a good performance profile and can handle heavy loads better than some other web servers.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before we begin, there are a few requirements that need to be settled.</p>

<p>To ensure that you have a solid base to build this system upon, you should run through our <a href="https://indiareads/community/articles/initial-server-setup-with-centos-7">initial server setup guide for CentOS 7</a>.  Among other things, this will walk you through setting up a non-root user with <code>sudo</code> access for administrative commands.</p>

<p>The second prerequisite that must be fulfilled in order to start on this guide is to install a LEMP (Linux, Nginx, MariaDB, and PHP) stack on your CentOS 7 server.  This is the platform that we will use to serve our phpMyAdmin interface (MariaDB is also the database management software that we are wishing to manage).  If you do not yet have a LEMP installation on your server, follow our tutorial on <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-centos-7">installing LEMP on CentOS 7</a>.</p>

<p>When your server is in a properly functioning state after following these guides, you can continue on with the rest of this page.</p>

<h2 id="step-one-—-install-phpmyadmin">Step One — Install phpMyAdmin</h2>

<p>With our LEMP platform already in place, we can begin right away with installing the phpMyAdmin software.  Unfortunately, phpMyAdmin is not available in CentOS 7's default repository.</p>

<p>To get the packages we need, we'll have to add an additional repo to our system.  The EPEL repo (<strong>E</strong>xtra <strong>P</strong>ackages for <strong>E</strong>nterprise <strong>L</strong>inux) contains many additional packages, including the phpMyAdmin package we are looking for.</p>

<p>Luckily, the procedure for adding the EPEL repository has gotten a lot easier.  There is actually a package called <code>epel-release</code> that reconfigures our package manager to use the EPEL repos.</p>

<p>We can install that now by typing:</p>
<pre class="code-pre "><code langs="">sudo yum install epel-release
</code></pre>
<p>Now that you have access to the EPEL repository, you can install phpMyAdmin through yum:</p>
<pre class="code-pre "><code langs="">sudo yum install phpmyadmin
</code></pre>
<p>The installation will now complete.  For the Nginx web server to find and serve the phpMyAdmin files correctly, we just need to create a symbolic link from the installation files to our Nginx document root directory by typing this:</p>
<pre class="code-pre "><code langs="">sudo ln -s /usr/share/phpMyAdmin /usr/share/nginx/html
</code></pre>
<p>We should also restart our PHP processor to be sure that it can load the additional PHP modules that we installed:</p>
<pre class="code-pre "><code langs="">sudo systemctl restart php-fpm
</code></pre>
<p>With that, our phpMyAdmin installation is now operational.  To access the interface, go to your server's domain name or public IP address followed by <code>/phpMyAdmin</code>, in your web browser:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>/phpMyAdmin
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_lemp_centos7/login.png" alt="phpMyAdmin login screen" /></p>

<p>To sign in, use a username/password pair of a valid MariaDB user.  The <code>root</code> user and the MariaDB administrative password is a good choice to get started.  You will then be able to access the administrative interface:</p>

<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_lemp_centos7/main_page.png" alt="phpMyAdmin admin interface" /></p>

<p>Click around to get familiar with the interface.  In the next section, we will take steps to secure our new interface.</p>

<h2 id="step-two-—-secure-your-phpmyadmin-instance">Step Two — Secure your phpMyAdmin Instance</h2>

<p>The phpMyAdmin instance installed on our server should be completely usable at this point.  However, by installing a web interface, we have exposed our MySQL system to the outside world.</p>

<p>Even with the included authentication screen, this is quite a problem.  Because of phpMyAdmin's popularity combined with the large amount of data it provides access to, installations like these are common targets for attackers.</p>

<p>We will implement two simple strategies to lessen the chances of our installation being targeted and compromised.  We will change the location of the interface from <code>/phpMyAdmin</code> to something else to sidestep some of the automated bot brute-force attempts.  We will also create an additional, web server-level authentication gateway that must be passed before even getting to the phpMyAdmin login screen.</p>

<h3 id="changing-the-application-39-s-access-location">Changing the Application's Access Location</h3>

<p>In order for our Nginx web server to find and serve our phpMyAdmin files, we created a symbolic link from the phpMyAdmin directory to our document root in an earlier step.</p>

<p>To change the URL where our phpMyAdmin interface can be accessed, we simply need to rename the symbolic link.  Move into the Nginx document root directory to get a better idea of what we are doing:</p>
<pre class="code-pre "><code langs="">cd /usr/share/nginx/html
ls -l
</code></pre><pre class="code-pre "><code langs="">-rw-r--r-- 1 root root 537 Aug  5 08:15 50x.html
-rw-r--r-- 1 root root 612 Aug  5 08:15 index.html
lrwxrwxrwx 1 root root  21 Aug  6 17:29 <span class="highlight">phpMyAdmin</span> -> /usr/share/phpMyAdmin
</code></pre>
<p>As you can see, we have a symbolic link called <code>phpMyAdmin</code> in this directory.  We can change this link name to whatever we would like.  This will change the location where phpMyAdmin can be accessed from a browser, which can help obscure the access point from hard-coded bots.</p>

<p>Choose a name that does not indicate the purpose of the location.  In this guide, we will name our access location <code>/nothingtosee</code>.  To accomplish this, we will just rename the link:</p>
<pre class="code-pre "><code langs="">sudo mv phpMyAdmin <span class="highlight">nothingtosee</span>
ls -l
</code></pre><pre class="code-pre "><code langs="">total 8
-rw-r--r-- 1 root root 537 Aug  5 08:15 50x.html
-rw-r--r-- 1 root root 612 Aug  5 08:15 index.html
lrwxrwxrwx 1 root root  21 Aug  6 17:29 nothingtosee -> /usr/share/phpMyAdmin
</code></pre>
<p>Now, if you go to the previous location of your phpMyAdmin installation, you will get a 404 error:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>/phpMyAdmin
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_lemp_centos7/404_error.png" alt="phpMyAdmin 404 error" /></p>

<p>However, your phpMyAdmin interface will be available at the new location we selected:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>/nothingtosee
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_lemp_centos7/login.png" alt="phpMyAdmin login screen" /></p>

<h3 id="setting-up-a-web-server-authentication-gate">Setting up a Web Server Authentication Gate</h3>

<p>The next feature we wanted for our installation was an authentication prompt that a user would be required to pass before ever seeing the phpMyAdmin login screen.</p>

<p>Fortunately, most web servers, including Nginx, provide this capability natively.  We will just need to modify our Nginx configuration file with the details.</p>

<p>Before we do this, we will create a password file that will store our the authentication credentials.  Nginx requires that passwords be encrypted using the <code>crypt()</code> function.  The OpenSSL suite, which should already be installed on your server, includes this functionality.</p>

<p>To create an encrypted password, type:</p>
<pre class="code-pre "><code langs="">openssl passwd
</code></pre>
<p>You will be prompted to enter and confirm the password that you wish to use.  The utility will then display an encrypted version of the password that will look something like this:</p>
<pre class="code-pre "><code langs=""><span class="highlight">O5az.RSPzd.HE</span>
</code></pre>
<p>Copy this value, as you will need to paste it into the authentication file we will be creating.</p>

<p>Now, create an authentication file.  We will call this file <code>pma_pass</code> and place it in the Nginx configuration directory:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/pma_pass
</code></pre>
<p>Within this file, you simply need to specify the username you would like to use, followed by a colon (:), followed by the encrypted version of your password you received from the <code>openssl passwd</code> utility.</p>

<p>We are going to name our user <code>demo</code>, but you should choose a different username.  The file for this guide looks like this:</p>
<pre class="code-pre "><code langs=""><span class="highlight">demo</span>:<span class="highlight">O5az.RSPzd.HE</span>
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>Now, we are ready to modify our Nginx configuration file.  Open this file in your text editor to get started:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/conf.d/default.conf
</code></pre>
<p>Within this file, we need to add a new location section.  This will target the location we chose for our phpMyAdmin interface (we selected <code>/nothingtosee</code> in this guide).</p>

<p>Create this section within the <code>server</code> block, but outside of any other blocks.  We will put our new location block below the <code>location /</code> block in our example:</p>
<pre class="code-pre "><code langs="">server {
    . . .

    location / {
        try_file $uri $uri/ =404;
    }

    <span class="highlight">location /nothingtosee {</span>
    <span class="highlight">}</span>

    . . .
}
</code></pre>
<p>Within this block, we need to set the value of a directive called <code>auth_basic</code> to an authentication message that our prompt will display to users.  We do not want to indicate to unauthenticated users what we are protecting, so do not give specific details.  We will just use "Admin Login" in our example.</p>

<p>We then need to use a directive called <code>auth_basic_user_file</code> to point our web server to the authentication file that we created.  Nginx will prompt the user for authentication details and check that the inputted values match what it finds in the specified file.</p>

<p>After we are finished, the file should look like this:</p>
<pre class="code-pre "><code langs="">server {
    . . .

    location / {
        try_file $uri $uri/ =404;
    }

    location /nothingtosee {
        <span class="highlight">auth_basic "Admin Login";</span>
        <span class="highlight">auth_basic_user_file /etc/nginx/pma_pass;</span>
    }

    . . .
}
</code></pre>
<p>Save and close the file when you are finished.</p>

<p>To implement our new authentication gate, we must restart the web server:</p>
<pre class="code-pre "><code langs="">sudo systemctl restart nginx
</code></pre>
<p>Now, if we visit our phpMyAdmin location in our web browser (you may have to clear your cache or use a different browser session if you have already been using phpMyAdmin), you should be prompted for the username and password you added to the <code>pma_pass</code> file:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>/nothingtosee
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/phpmyadmin_lemp_centos7/auth_gate.png" alt="Nginx authentication page" /></p>

<p>Once you enter your credentials, you will be taken to the normal phpMyAdmin login page.  This added layer of protection will help keep your MySQL logs clean of authentication attempts in addition to the added security benefit.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You can now manage your MySQL databases from a reasonably secure web interface.  This UI exposes most of the functionality that is available from the MySQL command prompt.  You can view databases and schema, execute queries, and create new data sets and structures.</p>

    