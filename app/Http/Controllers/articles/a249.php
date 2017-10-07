<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Nginx_with_SSL-twitter.png?1426699732/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>By default, Jenkins comes with its own built in web server, which listens on port 8080. This is convenient if you run a private Jenkins instance, or if you just need to get something up quickly and don't care about security. Once you have real production data going to your host, though, it's a good idea to use a more secure web server like Nginx.</p>

<p>This post will detail how to wrap your site with SSL using the Nginx web server as a reverse proxy for your Jenkins instance.  <strong>This tutorial assumes some familiarity with Linux commands, a working Jenkins installation, and a Ubuntu 14.04 installation.</strong> </p>

<p>You can install Jenkins later in this tutorial, if you don't have it installed yet.</p>

<h2 id="step-one-—-configure-nginx">Step One — Configure Nginx</h2>

<p>Nginx has become a favored web server for its speed and flexibility in recents years, so that is the web server we will be using.</p>

<p>The commands in this section assume that you have a user set up with <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo access</a>.</p>

<h3 id="install-nginx">Install Nginx</h3>

<p>Update your package lists and install Nginx:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install nginx
</code></pre>
<p>It's not crucial, but you may want to check Nginx's version in case you need to do any troubleshooting down the road.  Newer versions of Nginx provide a few more features as well.</p>
<pre class="code-pre "><code langs="">nginx -v
</code></pre>
<h3 id="get-a-certificate">Get a Certificate</h3>

<p>Next, you will need to purchase or create an SSL certificate.  These commands are for a self-signed certificate, but you should get an officially <a href="https://indiareads/community/tutorials/openssl-essentials-working-with-ssl-certificates-private-keys-and-csrs">signed certificate</a> if you want to avoid browser warnings.</p>

<p>Move into the proper directory and generate a certificate:</p>
<pre class="code-pre "><code langs="">cd /etc/nginx
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/cert.key -out /etc/nginx/cert.crt
</code></pre>
<p>You will be prompted to enter some information about the certificate. You can fill this out however you'd like; just be aware the information will be visible in the certificate properties. We've set the number of bits to 2048 since that's the minimum needed to get it signed by a CA. If you want to get the certificate signed, you will need to create a CSR.</p>

<h3 id="edit-the-configuration">Edit the Configuration</h3>

<p>Next you will need to edit the default Nginx configuration file.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/nginx/sites-enabled/default
</code></pre>
<p>Here is what the final config might look like; the sections are broken down and briefly explained below. You can update or replace the existing config file, although you may want to make a quick copy first.</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    return 301 https://$host$request_uri;
}

server {

    listen 443;
    server_name <span class="highlight">jenkins.domain.com</span>;

    ssl_certificate           /etc/nginx/cert.crt;
    ssl_certificate_key       /etc/nginx/cert.key;

    ssl on;
    ssl_session_cache  builtin:1000  shared:SSL:10m;
    ssl_protocols  TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers HIGH:!aNULL:!eNULL:!EXPORT:!CAMELLIA:!DES:!MD5:!PSK:!RC4;
    ssl_prefer_server_ciphers on;

    access_log            /var/log/nginx/jenkins.access.log;

    location / {

      proxy_set_header        Host $host;
      proxy_set_header        X-Real-IP $remote_addr;
      proxy_set_header        X-Forwarded-For $proxy_add_x_forwarded_for;
      proxy_set_header        X-Forwarded-Proto $scheme;

      # Fix the “It appears that your reverse proxy set up is broken" error.
      proxy_pass          http://localhost:8080;
      proxy_read_timeout  90;

      proxy_redirect      http://localhost:8080 https://<span class="highlight">jenkins.domain.com</span>;
    }
  }
</code></pre>
<p>In our configuration, the <span class="highlight">cert.crt</span> and <span class="highlight">cert.key</span> settings reflect the location where we created our SSL certificate. You will need to update the <span class="highlight">server<em>name</em></span> and `proxyredirect` lines with your own domain name. There is some additional Nginx magic going on as well that tells requests to be read by Nginx and rewritten on the response side to ensure the reverse proxy is working.</p>

<p>The first section tells the Nginx server to listen to any requests that come in on port 80 (default HTTP) and redirect them to HTTPS.</p>
<pre class="code-pre "><code langs="">...
server {
   listen 80;
   return 301 https://$host$request_uri;
}
...
</code></pre>
<p>Next we have the SSL settings. This is a good set of defaults but can definitely be expanded on. For more explanation, please read <a href="https://indiareads/community/tutorials/how-to-create-an-ssl-certificate-on-nginx-for-ubuntu-14-04">this tutorial</a>.</p>
<pre class="code-pre "><code langs="">...
  listen 443;
  server_name <span class="highlight">jenkins.domain.com</span>;

  ssl_certificate           /etc/nginx/cert.crt;
  ssl_certificate_key       /etc/nginx/cert.key;

  ssl on;
  ssl_session_cache  builtin:1000  shared:SSL:10m;
  ssl_protocols  TLSv1 TLSv1.1 TLSv1.2;
  ssl_ciphers HIGH:!aNULL:!eNULL:!EXPORT:!CAMELLIA:!DES:!MD5:!PSK:!RC4;
  ssl_prefer_server_ciphers on;
  ...
</code></pre>
<p>The final section is where the proxying happens. It basically takes any incoming requests and proxies them to the Jenkins instance that is bound/listening to port 8080 on the local network interface. This is a slightly different situation, but <a href="https://indiareads/community/tutorials/how-to-configure-nginx-as-a-front-end-proxy-for-apache">this tutorial</a> has some good information about the Nginx proxy settings.</p>
<pre class="code-pre "><code langs="">...
location / {

    proxy_set_header        Host $host;
    proxy_set_header        X-Real-IP $remote_addr;
    proxy_set_header        X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header        X-Forwarded-Proto $scheme;

    # Fix the “It appears that your reverse proxy set up is broken" error.
    proxy_pass          http://localhost:8080;
    proxy_read_timeout  90;

    proxy_redirect      http://localhost:8080 https://<span class="highlight">jenkins.domain.com</span>;
}
...
</code></pre>
<p>A few quick things to point out here. If you don't have a domain name that resolves to your Jenkins server, then the <span class="highlight">proxy_redirect</span> statement above won't function correctly without modification, so keep that in mind. Also, if you misconfigure the <span class="highlight">proxy_pass</span> (by adding a trailing slash for example), you will get something similar to the following in your Jenkins Configuration page.</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_jenkins/1.jpg" alt="Jenkins error: Reverse proxy set up is broken" /></p>

<p>So, if you see this error, double-check your <span class="highlight">proxy_pass</span> and <span class="highlight">proxy_redirect</span> settings in the Nginx configuration!</p>

<h2 id="step-two-—-configure-jenkins">Step Two — Configure Jenkins</h2>

<p>As stated previously, this tutorial assumes that Jenkins is already installed. <a href="https://indiareads/community/tutorials/how-to-install-and-use-jenkins-on-ubuntu-12-04">This tutorial</a> will show you how to install Jenkins if necessary. You will probably need to switch to the root user for that article.</p>

<p>For Jenkins to work with Nginx, we need to update the Jenkins config to listen only on the localhost interface instead of all (0.0.0.0), to ensure traffic gets handled properly.  This is an important step because if Jenkins is still listening on all interfaces, then it will still potentially be accessible via its original port (8080).  We will modify the <span class="highlight">/etc/default/jenkins</span> configuration file to make these adjustments.</p>
<pre class="code-pre "><code langs="">sudo nano /etc/default/jenkins
</code></pre>
<p>Locate the <code>JENKINS\_ARGS</code> line and update it to look like the folowing:</p>
<pre class="code-pre "><code langs="">JENKINS_ARGS="--webroot=/var/cache/jenkins/war --httpListenAddress=127.0.0.1 --httpPort=$HTTP_PORT -ajp13Port=$AJP_PORT"
</code></pre>
<p>Notice that the <strong>--httpListenAddress=127.0.0.1</strong> setting needs to be either added or modified.</p>

<p>Then go ahead and restart Jenkins and Nginx.</p>
<pre class="code-pre "><code langs="">sudo service jenkins restart
sudo service nginx restart
</code></pre>
<p>You should now be able to visit your domain using either HTTP or HTTPS, and the Jenkins site will be served securely. You will see a certificate warning if you used a self-signed certificate.</p>

<h2 id="optional-—-update-oauth-urls">Optional — Update OAuth URLs</h2>

<p>If you are using the GitHub or another OAuth plugin for authentication, it will probably be broken at this point. For example, when attempting to visit the URL, you will get a "Failed to open page" with a URL similar to the following:</p>
<pre class="code-pre "><code langs="">http://jenkins.domain.com:8080/securityRealm/finishLogin?code=random-string
</code></pre>
<p>To fix this you will need to update a few settings, including your OAuth plugin settings. First update the Jenkins URL (in the Jenkins GUI); it can be found  here:</p>

<p><strong>Jenkins -> Manage Jenkins -> Configure System -> Jenkins Location</strong></p>

<p>Update the Jenkins URL to use HTTPS - <code>https://<span class="highlight">jenkins.domain.com/</span></code></p>

<p><img src="https://assets.digitalocean.com/articles/nginx_jenkins/2.jpg" alt="Jenkins URL" /></p>

<p>Next, update your OAuth settings with the external provider. This example is for GitHub. On GitHub, this can be found under <strong>Settings -> Applications -> Developer applications</strong>, on the GitHub site.</p>

<p>There should be an entry for Jenkins. Update the <strong>Homepage URL</strong> and <strong>Authorization callback URL</strong> to reflect the HTTPS settings.  It might look similar to the following:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_jenkins/3.jpg" alt="Jenkins settings on GitHub; https:// has been used with both URLs" /></p>

<h3 id="conclusion">Conclusion</h3>

<p>The only thing left to do is verify that everything worked correctly. As mentioned above, you should now be able to browse to your newly configured URL - <span class="highlight">jenkins.domain.com</span> - over either HTTP or HTTPS. You should be redirected to the secure site, and should see some site information, including your newly updated SSL settings. As noted previously, if you are not using hostnames via DNS, then your redirection may not work as desired. In that case, you will need to modify the <span class="highlight">proxy_pass</span> section in the Nginx config file.</p>

<p>You may also want to use your browser to examine your certificate. You should be able to click the lock to look at the certificate properties from within your browser.</p>

    