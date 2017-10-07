<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Nginx is one of the leading web servers in active use. It and its commercial edition, Nginx Plus, are developed by Nginx, Inc.</p>

<p>In this tutorial, you'll learn how to restrict access to an Nginx-powered website using the HTTP basic authentication method on Ubuntu 14.04. HTTP basic authentication is a simple username and (hashed) password authentication method.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To complete this tutorial, you'll need the following:</p>

<ul>
<li><p>One Ubuntu 14.04 Droplet with a sudo non-root user, which you can set up by following <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">this initial server setup tutorial</a>.</p></li>
<li><p>Nginx installed and configured on your server, which you can do by following <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-14-04-lts">this Nginx article</a>.</p></li>
</ul>

<h2 id="step-1-—-installing-apache-tools">Step 1 — Installing Apache Tools</h2>

<p>You'll need the <code>htpassword</code> command to configure the password that will restrict access to the target website. This command is part of the <code>apache2-utils</code> package, so the first step is to install that package.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install apache2-utils
</li></ul></code></pre>
<h2 id="step-2-—-setting-up-http-basic-authentication-credentials">Step 2 — Setting Up HTTP Basic Authentication Credentials</h2>

<p>In this step, you'll create a password for the user running the website.</p>

<p>That password and the associated username will be stored in a file that you specify. The password will be encrypted and the name of the file can be anything you like. Here, we use the file <code>/etc/nginx/.htpasswd</code> and the username <strong>nginx</strong>.</p>

<p>To create the password, run the following command. You'll need to authenticate, then specify and confirm a password.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo htpasswd -c /etc/nginx/.htpasswd nginx
</li></ul></code></pre>
<p>You can check the contents of the newly-created file to see the username and hashed password.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /etc/nginx/.htpasswd
</li></ul></code></pre><div class="code-label " title="Example /etc/nginx/.htpasswd">Example /etc/nginx/.htpasswd</div><pre class="code-pre "><code langs="">nginx:$apr1$ilgq7ZEO$OarDX15gjKAxuxzv0JTrO/
</code></pre>
<h2 id="step-3-—-updating-the-nginx-configuration">Step 3 — Updating the Nginx Configuration</h2>

<p>Now that you've created the HTTP basic authentication credential, the next step is to update the Nginx configuration for the target website to use it.</p>

<p>HTTP basic authentication is made possible by the <code>auth_basic</code> and <code>auth_basic_user_file</code> directives. The value of <code>auth_basic</code> is any string, and will be displayed at the authentication prompt; the value of <code>auth_basic_user_file</code> is the path to the password file that was created in Step 2.</p>

<p>Both directives should be in the configuration file of the target website, which is normally located in <code>/etc/nginx/sites-available</code> directory. Open that file using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Under the <strong>location</strong> section, add both directives:</p>
<div class="code-label " title="/etc/nginx/sites-available/default.conf">/etc/nginx/sites-available/default.conf</div><pre class="code-pre "><code langs="">. . .
server_name localhost;

location / {
        # First attempt to serve request as file, then
        # as directory, then fall back to displaying a 404.
        try_files $uri $uri/ =404;
        # Uncomment to enable naxsi on this location
        # include /etc/nginx/naxsi.rules
        <span class="highlight">auth_basic "Private Property";</span>
        <span class="highlight">auth_basic_user_file /etc/nginx/.htpasswd;</span>
}
. . .
</code></pre>
<p>Save and close the file.</p>

<h2 id="step-4-—-testing-the-setup">Step 4 — Testing the Setup</h2>

<p>To apply the changes, first reload Nginx.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>Now try accessing the website you just secured by going to <code>http://<span class="highlight">your_server_ip</span>/</code> in your favorite browser. You should be presented with an authentication window (which says "Private Property", the string we set for <code>auth_basic</code>), and you will not be able to access the website until you enter the correct credentials. If you enter the username and password you set, you'll see the default Nginx home page.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You've just completed basic access restriction for an Nginx website. More information about this technique and other means of access restriction are available <a href="https://www.nginx.com/resources/admin-guide/restricting-access/">in Nginx's documentation</a>.</p>

    