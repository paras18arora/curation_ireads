<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>The Apache web server is the most popular web server in the world.  It can be used to deliver static and dynamic web content to visitors in a multitude of different contexts.</p>

<p>One of the most common ways of generating dynamic content is through the use of <code>CGI</code>, or the common gateway interface.  This provides a standard way of executing scripts that generate web content that can written in a variety of programming languages.</p>

<p>Running any kind of executable code within a web-space comes with a certain amount of risk.  In this guide, we will demonstrate how to implement CGI scripting with the <code>suexec</code> module, which allows you to run scripts in a way that doesn't elevate privileges unnecessarily.</p>

<h2 id="prerequisites">Prerequisites</h2>

<hr />

<p>In this guide, we will be configuring an Ubuntu 12.04 VPS with a standard LAMP (Linux, Apache, MySQL, PHP) installation.  We assume that you have already installed these basic components and have them working in a basic configuration.</p>

<p>To learn <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">how to install a LAMP stack on Ubuntu</a>, click here.</p>

<p>We will be referencing the software as it is in its initial state following that tutorial.</p>

<h2 id="how-to-enable-cgi-scripts">How To Enable CGI Scripts</h2>

<hr />

<p>In Ubuntu's Apache configuration, CGI scripts are actually already configured within a specific CGI directory.  This directory is empty by default.</p>

<p>CGI scripts can be any program that has the ability to output HTML or other objects or formats that a web browser can render.</p>

<p>If we go to the Apache configuration directory, and look at the modules that Apache has enabled in the <code>mods-enabled</code> directory, we will find a file that enables this functionality:</p>
<pre class="code-pre "><code langs="">less /etc/apache2/mods-enabled/cgi.load
</code></pre>
<hr />
<pre class="code-pre "><code langs="">LoadModule cgi_module /usr/lib/apache2/modules/mod_cgi.so
</code></pre>
<p>This file contains the directive that enables the CGI module.  This allows us to use this functionality in our configurations.</p>

<p>Although the module is loaded, it does not actually serve any script content on its own.  It must be enabled within a specific web environment explicitly.</p>

<p>We will look at the default Apache virtual host file to see how it does this:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-enabled/000-default
</code></pre>
<p>While we are in here, let's set the server name to reference our domain name or IP address:</p>

<pre>
<VirutalHost *:80>
    ServerName <span class="highlight">your_domain_or_IP_address</span>
    ServerAdmin <span class="highlight">your_email_address</span>
. . .
</pre>

<p>We can see a bit down in the file the part that is applicable to CGI scripts:</p>
<pre class="code-pre "><code langs="">ScriptAlias /cgi-bin/ /usr/lib/cgi-bin/
<Directory "/usr/lib/cgi-bin">
    AllowOverride None
    Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch
    Order allow,deny
    Allow from all
</Directory>
</code></pre>
<p>Let's break down what this portion of the configuration is doing.</p>

<p>The <code>ScriptAlias</code> directive gives Apache permission to execute the scripts contained in a specific directory.  In this case, the directory is <code>/usr/lib/cgi-bin/</code>.  While the second argument gives the file path to the script directory, the first argument, <code>/cgi-bin/</code>, provides the URL path.</p>

<p>This means that a script called "script.pl" located in the <code>/usr/lib/cgi-bin</code> directory would be executed when you access:</p>

<pre>
<span class="highlight">your_domain.com</span>/cgi-bin/script.pl
</pre>

<p>Its output would be returned to the web browser to render a page.</p>

<p>The <code>Directory</code> container contains rules that apply to the <code>/usr/lib/cgi-bin</code> directory.  You will notice an option that mentions CGI:</p>
<pre class="code-pre "><code langs="">Options +ExecCGI ...
</code></pre>
<p>This option is actually unnecessary since we are setting up options for a directory that has already been declared a CGI directory by <code>ScriptAlias</code>.  It does not hurt though, so you can keep it as it is.</p>

<p>If you wished to put CGI files in a directory outside of the ScriptAlias, you will have to add these two options to the directory section:</p>

<pre>
Options +ExecCGI
AddHandler cgi-script <span class="highlight">.pl .rb [extensions to be treated as CGI scripts]</span>
</pre>

<p>When you are done examining the file, save and close it.  If you made any changes, restart the web server:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<h2 id="make-a-test-cgi-script">Make a Test CGI Script</h2>

<hr />

<p>We will create a basic, trivial CGI script to show the steps necessary to get a script to execute correctly.</p>

<p>As we saw in the last section, the directory designated in our configuration for CGI scripts is <code>/usr/lib/cgi-bin</code>.  This directory is not writeable by non-root users, so we will have to use sudo:</p>
<pre class="code-pre "><code langs="">sudo nano /usr/lib/cgi-bin/test.pl
</code></pre>
<p>We gave the file a ".pl" extension because this will be a Perl script, but Apache will attempt to run any file within this directory and will pass it to the appropriate program based on its first line.</p>

<p>We will specify that the script should be interpreted by Perl by starting the script with:</p>
<pre class="code-pre "><code langs="">#!/usr/bin/perl
</code></pre>
<p>Following this, the first thing that the script <strong>must</strong> output is the content-type that will be generated.  This is necessary so that the web browser knows how to display the output it is given.  We will print out the HTML content type, which is "text/html", using Perl's regular print function.</p>
<pre class="code-pre "><code langs="">print "Content-type: text/html\n\n";
</code></pre>
<p>After this, we can do whatever functions or calculations are necessary to produce the text that we want on our website.  In our example, we will not produce anything that wouldn't be easier as just plain HTML, but you can see that this allows for dynamic content if your script was more complex.</p>

<p>The previous two components and our actual HTML content combine to make the following script:</p>
<pre class="code-pre "><code langs="">#!/usr/bin/perl
print "Content-type: text/html\n\n";

print "<html><head><title>Hello There...</title></head>";

print "<body>";

print "<h1>Hello, World.</h1><hr>";
print "<p>This is some regular text.</p>";
print "<p>The possibilities are great.</p>";

print "</body></html>";
</code></pre>
<p>Save and close the file.</p>

<p>Now, we have a file, but it isn't marked as executable.  Let's change that:</p>
<pre class="code-pre "><code langs="">sudo chmod 755 /usr/lib/cgi-bin/test.pl
</code></pre>
<p>Now, if we navigate to our domain name, followed by the CGI directory (/cgi-bin/), followed by our script name (test.pl), we should see the output of our script.</p>

<p>Point your browser to:</p>

<pre>
<span class="highlight">your_domain.com</span>/cgi-bin/test.pl
</pre>

<p>You should see something that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_cgi/test_script.png" alt="Sample CGI application" /></p>

<p>Not very exciting, but rendered correctly.</p>

<p>If we choose to view the source of the page, we will see only the arguments given to the print functions, minus the content-type header:</p>

<p><img src="https://assets.digitalocean.com/articles/apache_cgi/page_source.png" alt="CGI page source" /></p>

<h2 id="how-to-enable-suexec">How To Enable SuExec</h2>

<hr />

<p>There are some security concerns implicit in setting a script as executable by anybody.  Ideally, a script should only be able to be executed by a single, locked down user.  We can set up this situation by using the <code>suexec</code> module.</p>

<p>We will actually install a modified suexec module that allows us to configure the directories in which it operates.  Normally, this would not be configurable without recompiling from source.</p>

<p>Install the alternate module with this command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install apache2-suexec-custom
</code></pre>
<p>Now, we can enable the module by typing:</p>
<pre class="code-pre "><code langs="">sudo a2enmod suexec
</code></pre>
<p>Next, we will create a new user that will own our script files.  If we have multiple sites being served, each can have their own user and group:</p>
<pre class="code-pre "><code langs="">sudo adduser script_user
</code></pre>
<p>Feel free to enter through all of the prompts (including the password prompt).  This user does not need to be fleshed out.</p>

<p>Next, let's create a scripts directory within this new user's home directory:</p>
<pre class="code-pre "><code langs="">sudo mkdir /home/script_user/scripts
</code></pre>
<p>Suexec requires very strict control over who can write to the directory.  Let's transfer ownership to the script_user user and change the permissions so that no one else can write to the directory:</p>
<pre class="code-pre "><code langs="">sudo chown script_user:script_user /home/script_user/scripts
sudo chmod 755 /home/script_user/scripts
</code></pre>
<p>Next, let's create a script file and copy and paste our script from above into it:</p>
<pre class="code-pre "><code langs="">sudo -u script_user nano /home/script_user/scripts/attempt.pl
</code></pre>
<hr />
<pre class="code-pre "><code langs="">#!/usr/bin/perl
print "Content-type: text/html\n\n";

print "<html><head><title>Hello There...</title></head>";

print "<body>";

print "<h1>Hello, World.</h1><hr>";
print "<p>This is some regular text.</p>";
print "<p>The possibilities are great.</p>";

print "</body></html>";
</code></pre>
<p>Make it executable next.  We will only let our script_user have any permissions on the file.  This is what the suexec module allows us to do:</p>
<pre class="code-pre "><code langs="">sudo chmod 700 /home/script_user/scripts/attempt.pl
</code></pre>
<p>Next, we will edit our Apache virtual host configuration to allow scripts to be executed by our new user.</p>

<p>Open the default virtual host file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/sites-enabled/000-default
</code></pre>
<p>First, let's make our CGI directory.  Instead of using the <code>ScriptAlias</code> directive, as we did above, let's demonstrate how to use the regular <code>Alias</code> directory combined with the <code>ExecCGI</code> option and the <code>SetHandler</code> directive.</p>

<p>Add this section:</p>
<pre class="code-pre "><code langs="">Alias /scripts/ /home/script_user/scripts/
<Directory "/home/script_user/scripts">
    Options +ExecCGI
    SetHandler cgi-script
</Directory>
</code></pre>
<p>This allows us to access our CGI scripts by going to the "/scripts" sub-directory.  To enable the suexec capabilities, add this line outside of the "Directory" section, but within the "VirtualHost" section:</p>
<pre class="code-pre "><code langs="">SuexecUserGroup script_user script_user
</code></pre>
<p>Save and close the file.</p>

<p>We also need to specify the places that suexec will consider a valid directory.  This is what our customizable version of suexec allows us to do.  Open the suexec configuration file:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/apache2/suexec/www-data
</code></pre>
<p>At the top of this file, we just need to add the path to our scripts directory.</p>
<pre class="code-pre "><code langs="">/home/script_user/scripts/
</code></pre>
<p>Save and close the file.</p>

<p>Now, all that's left to do is restart the web server:</p>
<pre class="code-pre "><code langs="">sudo service apache2 restart
</code></pre>
<p>If we open our browser and navigate here, we can see the results of our script:</p>

<pre>
<span class="highlight">your_domain.com</span>/scripts/attempt.pl
</pre>

<p><img src="https://assets.digitalocean.com/articles/apache_cgi/suexec.png" alt="Suexec example page" /></p>

<p>Please note that with suexec configured, your normal CGI directory will not work properly, because it does not pass the rigorous tests that suexec requires.  This is intended behavior to control what permissions scripts have.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>You can now create scripts and execute them in a relatively secure way.  CGI scripts are very helpful for quickly including dynamic content on your site.  Suexec allows you to lock down this ability for greater security.</p>

<p>Be careful when using suexec, because it can actually create more security vulnerabilities if it is configured incorrectly.  To learn about the potential vulnerabilities of this set up, research setuid configuration.</p>

<div class="author">By Justin Ellingwood</div>

    