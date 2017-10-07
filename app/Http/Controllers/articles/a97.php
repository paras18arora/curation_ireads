<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Wordpress_Ajenti_tutorial_sm.png?1460584791/> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="http://ajenti.org">Ajenti</a> is an open source, web-based control panel that can be used for a large variety of server management tasks. The add-on package called Ajenti V allows you to manage multiple websites from the same control panel. By now you should have Ajenti and Ajenti V installed. </p>

<p>In this tutorial we will install a WordPress blog, using the tools Ajenti V provides.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you will need:</p>

<ul>
<li>A registered domain name that resolves to the Droplet with Ajenti and Ajenti V installed (<span class="highlight">example.com</span> is used throughout this tutorial)</li>
<li>Ajenti and Ajenti V installed from <a href="https://indiareads/community/tutorials/how-to-install-the-ajenti-control-panel-and-ajenti-v-on-ubuntu-14-04">How to Install the Ajenti Control Panel and Ajenti V on Ubuntu 14.04</a></li>
</ul>

<h2 id="step-1-—-configuring-wordpress">Step 1 — Configuring WordPress</h2>

<p>In your browser, browse to your Ajenti control panel such as <code>https://panel.<span class="highlight">example.com</span>/</code>, and log into it using the admin account you created when installing Ajenti and Ajenti V. In the sidebar to the right, under the <strong>Web</strong> section, click <strong>Websites</strong>. The first time it may give you a notice that it is not active yet, just click the <strong>Enable</strong> button to allow Ajenti V to make the necessary config changes.</p>

<p>There will be a section called <strong>New Website</strong>. Under that there is a <strong>Name</strong> text field. You can type anything you want to identify your website with in there. Click the <strong>Create</strong> button, and you will notice your website is now listed under the <strong>Websites</strong> section. Click <strong>Manage</strong> next to your website.</p>

<p>Under the <strong>Website Files</strong> section, change <code>/srv/new-website</code> to any directory, for example <code>/srv/<span class="highlight">example.com</span></code>. Press the <strong>Set</strong> button, and then press the <strong>Create Directory</strong> button. Remember this directory. You will need to upload files to it soon.</p>

<p>Under the <strong>General</strong> section, uncheck the <strong>Maintenance mode</strong> setting. Then click <strong>Apply changes</strong> at the bottom of the page.</p>

<p>At the top of the page click on the <strong>Domains</strong> tab. Press the <strong>Add</strong> button, and type your domain name such as <code><span class="highlight">example.com</span></code> in the text field that appears, then click the <strong>Apply Changes</strong> button.</p>

<p>Click the <strong>Advanced</strong> tab now, and in the <strong>Custom configuration</strong> box, enter the following:</p>
<pre class="code-pre "><code langs=""># This order might seem weird - this is attempted to match last if rules below fail.
location / {
    try_files $uri $uri/ /index.php?$args;
}

# Add trailing slash to */wp-admin requests.
rewrite /wp-admin$ $scheme://$host$uri/ permanent;

# Directives to send expires headers and turn off 404 error logging.
location ~* ^.+\.(ogg|ogv|svg|svgz|eot|otf|woff|mp4|ttf|rss|atom|jpg|jpeg|gif|png|ico|zip|tgz|gz|rar|bz2|doc|xls|exe|ppt|tar|mid|midi|wav|bmp|rtf)$ {
       access_log off; log_not_found off; expires max;
}

location = /favicon.ico {
    log_not_found off;
    access_log off;
}
location = /robots.txt {
    allow all;
    log_not_found off;
    access_log off;
}
# Deny all attempts to access hidden files such as .htaccess, .htpasswd, .DS_Store (Mac).
# Keep logging the requests to parse later (or to pass to firewall utilities such as fail2ban)
location ~ /\. {
    deny all;
}
# Deny access to any files with a .php extension in the uploads directory
# Works in sub-directory installs and also in multisite network
# Keep logging the requests to parse later (or to pass to firewall utilities such as fail2ban)
location ~* /(?:uploads|files)/.*\.php$ {
    deny all;
}
</code></pre>
<p>Click <strong>Apply changes</strong>.</p>

<p>Next, click the <strong>Content</strong> tab on the top. In the dropdown menu select <strong>PHP FastCGI</strong>, and click <strong>Create</strong>. Click the <strong>Advanced</strong> menu under the new PHP entry, and enter the following content in <strong>Custom configuration</strong>. Note this is not the same <strong>Custom configuration</strong> as the previous step.</p>
<pre class="code-pre "><code langs="">try_files $uri =404;
fastcgi_split_path_info ^(.+\.php)(/.+)$;
</code></pre>
<p>Click <strong>Apply Changes</strong>.</p>

<p>Browse to the <strong>MySQL</strong> tab. Under the <strong>Databases</strong> section, enter a name for your database, this can be anything. It will be referenced later in the tutorial as <code><span class="highlight">database_name</span></code>. Click <strong>Create</strong>. Now under the <strong>Users</strong> section, you can just click <strong>Create</strong> and use the automatically generated Name and Password provided, or you can use your own. The username and password will be referenced later as <code><span class="highlight">db_user</span></code> and <code><span class="highlight">db_password</span></code>, respectively. When you are finished, click <strong>Apply Changes</strong>. Remember the database name, user, and password. You will need them later in this tutorial to finish setting up WordPress.</p>

<h2 id="step-2-—-uploading-the-files">Step 2 — Uploading the Files</h2>

<p>Before you can unpack the WordPress files, you need to install the zip utilities. In the sidebar, under <strong>Tools</strong>, click <strong>Terminal</strong>. Click <strong>New</strong>, and click the black box that appears. Type the following into the terminal:</p>
<pre class="code-pre "><code langs="">apt-get install zip unzip
</code></pre>
<p>When the process completes, click the <strong>X</strong> next to <strong>Terminal 0</strong> at the top of the page.</p>

<p>Go back to the <strong>Websites</strong> section again now, and click <strong>Manage</strong> next to your website. Then go to the <strong>General</strong> tab of your website. Under the <strong>Automatic Downloader</strong> section, input <code>http://wordpress.org/latest.zip</code>, then press <strong>Download and Unpack</strong>. A terminal will appear. When it finishes unpacking you can exit the terminal by clicking the <strong>X</strong> next to <strong>Terminal 0</strong> at the top of the page.</p>

<p>Back under the <strong>Tools</strong> section in the sidebar, click <strong>File Manager</strong>. Navigate to the directory you previously created in the <code>/srv/</code> directory by clicking the folder names. Inside there should be a directory named <code>wordpress</code> that was created when the Automatic Downloader unpacked the WordPress files. Open it and select all the files, like so:</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-wordpress-selectall.png" alt="Select Files in /srv/example.com/wordpress" /></p>

<p>Then press the <strong>Cut</strong> option in the toolbar at the top of the screen.</p>

<p><img src="https://assets.digitalocean.com/articles/ajenti_ajenti_v_ubuntu1404/ajenti-wordpress-cutfiles.png" alt=""Cut" the WordPress Files" /></p>

<p>Navigate back to your directory in the <code>/srv/</code> directory such as <code>/srv/<span class="highlight">example.com</span></code>, and paste all the files in the root of the directory. You can then delete the empty <code>wordpress</code> folder. It may take a while for it to paste all the files, so be patient.</p>

<p>Now all the WordPress files are in the root of your website. We can now continue to the next step.</p>

<h2 id="step-3-—-installing-wordpress">Step 3 — Installing WordPress</h2>

<p>Browse to your domain name such as <code>http://<span class="highlight">example.com</span></code> in your web browser. You will be greeted by the initial WordPress installation page. Select your language in the selection field and click <strong>Continue</strong>, then click <strong>Let's go!</strong>.</p>

<p>In the database name field, change <code>wordpress</code> to the <code><span class="highlight">database_name</span></code> you picked earlier in the tutorial. Also provide the database username and password you decided on earlier in the tutorial.</p>

<p><strong>Database host</strong> should be left as <code>localhost</code>. <strong>Table Prefix</strong> can be left as <code>wp_</code>, but changing this from its default value might make your installation more secure from certain MySQL attacks. Consider changing it to something like <code>blog_</code> or even just something random as long as it's followed by the <code>_</code> character. Keep it relatively short — no more than 5 characters or so.</p>

<p>Now you can click <strong>Run the install</strong>, and WordPress will finish installing! You will be prompted to fill in a bit more site information such as the title and your admin credentials. Fill these in as you see fit. </p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a functional WordPress website installed on your Ajenti control panel.</p>

    