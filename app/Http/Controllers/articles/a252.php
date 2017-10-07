<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This article shows you how to set up Nginx load balancing with SSL termination with just one SSL certificate on the load balancer. This will reduce your SSL management overhead, since the OpenSSL updates and the keys and certificates can now be managed from the load balancer itself.</p>

<h3 id="about-ssl-termination">About SSL Termination</h3>

<p>Nginx can be configured as a load balancer to distribute incoming traffic around several backend servers. SSL termination is the process that occurs on the load balancer which handles the SSL encryption/decryption so that traffic between the load balancer and backend servers is in HTTP. The backends must be secured by restricting access to the load balancer's IP, which is explained later in this article.</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_ssl_termination_load_balancing/nginx_ssl.png" alt="SSL Termination Diagram" /></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>In this tutorial the commands must be run as the root user or as a user with sudo privileges. You can see how to set that up in the <a href="https://indiareads/community/articles/how-to-add-and-delete-users-on-ubuntu-12-04-and-centos-6">Users Tutorial</a>.</p>

<p>The following guides can be used as reference:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">Setting up a LAMP Server on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-multiple-ssl-certificates-on-one-ip-with-nginx-on-ubuntu-12-04">Setting up SSL on Nginx</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-nginx-load-balancing">Setting up Load Balancing on Nginx</a></li>
</ul>

<p>A LAMP server is not required, but we'll be using it as an example in this tutorial.</p>

<h2 id="setup">Setup</h2>

<p>This tutorial makes use of the following 3 droplets:</p>

<p><strong>Droplet 1 (Frontend)</strong>  </p>

<ul>
<li>Image: Ubuntu 14.04<br /></li>
<li>Hostname: loadbalancer<br /></li>
<li>Private IP: 10.130.227.33 </li>
</ul>

<p><strong>Droplet 2 (Backend)</strong>  </p>

<ul>
<li>Image: Ubuntu 14.04<br /></li>
<li>Hostname: web1<br /></li>
<li>Private IP: 10.130.227.11</li>
</ul>

<p><strong>Droplet 3 (Backend)</strong>  </p>

<ul>
<li>Image: Ubuntu 14.04<br /></li>
<li>Hostname: web2<br /></li>
<li>Private IP: 10.130.227.22</li>
</ul>

<p><strong>Domain name</strong> - example.com</p>

<p>All these Droplets must have <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">private networking</a> enabled.</p>

<p>Update and upgrade the software on all three servers:</p>
<pre class="code-pre "><code langs="">apt-get update && apt-get upgrade -y
</code></pre>
<p><strong>Reboot each server to apply the upgrades.</strong> This is important, since OpenSSL needs to be on its latest version to be secure.</p>

<p>We will be setting up a new Nginx virtual host for the domain name with the upstream module load balancing the backends.</p>

<p>Prior to setting up Nginx loadbalancing, you should have Nginx installed on your VPS. You can install it quickly with <code>apt-get</code>:</p>
<pre class="code-pre "><code langs="">apt-get install nginx
</code></pre>
<p>On the two backend servers, update your repositories and install Apache:</p>
<pre class="code-pre "><code langs="">apt-get install apache2
</code></pre>
<p>Install PHP on both backend servers:</p>
<pre class="code-pre "><code langs="">apt-get install php5 libapache2-mod-php5 php5-mcrypt
</code></pre>
<p>For more information, see <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">this article</a>.</p>

<h2 id="generate-keys-and-create-an-ssl-certificate">Generate Keys And Create An SSL Certificate</h2>

<p>In this section, you will run through the steps needed to create an SSL certificate. <a href="https://indiareads/community/tutorials/how-to-set-up-multiple-ssl-certificates-on-one-ip-with-nginx-on-ubuntu-12-04">This article</a> explains in detail about SSL certificates on Nginx.</p>

<p>Create the SSL certificate directory and switch to it.</p>
<pre class="code-pre "><code langs="">mkdir -p /etc/nginx/ssl/example.com
cd /etc/nginx/ssl/example.com
</code></pre>
<p>Create a private key:</p>
<pre class="code-pre "><code langs="">openssl genrsa -des3 -out server.key 2048
</code></pre>
<p>Remove its passphrase:</p>
<pre class="code-pre "><code langs="">openssl rsa -in server.key -out server.key
</code></pre>
<p>Create a CSR (Certificate Signing Request):</p>
<pre class="code-pre "><code langs="">openssl req -new -key server.key -out server.csr
</code></pre>
<p>Use this CSR to obtain a valid certificate from <a href="https://indiareads/community/tutorials/how-to-set-up-apache-with-a-free-signed-ssl-certificate-on-a-vps">a certificate authority</a> or generate a self-signed certificate with the following command.</p>
<pre class="code-pre "><code langs="">openssl x509 -req -days 365 -in server.csr -signkey server.key -out server.crt
</code></pre>
<p>Once this is done this directory will contain the following files:</p>

<ul>
<li>server.key - The private key</li>
<li>ca-certs.pem - A collection of your CA's root and intermediate certificates. Only present if you obtained a valid certificate from a CA.</li>
<li>server.crt - The SSL certificate for your domain name</li>
</ul>

<h2 id="virtual-host-file-and-upstream-module">Virtual Host File And Upstream Module</h2>

<p>Create a virtual hosts file inside the Nginx directory</p>
<pre class="code-pre "><code langs="">nano /etc/nginx/sites-available/<span class="highlight">example.com</span>
</code></pre>
<p>Add the upstream module containing the private IP addresses of the backend servers</p>
<pre class="code-pre "><code langs="">upstream <span class="highlight">mywebapp1</span> {
    server <span class="highlight">10.130.227.11</span>;
    server <span class="highlight">10.130.227.22</span>;
}
</code></pre>
<p>Begin the server block <strong>after</strong> this line. This block contains the domain name, references to the upstream servers, and headers that should be passed to the backend.</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    server_name <span class="highlight">example.com www.example.com</span>;

    location / {
        proxy_pass http://<span class="highlight">mywebapp1</span>;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
</code></pre>
<p>The <code>proxy_set_header</code> directive is used to pass vital information about the request to the upstream servers.</p>

<p>Save this file and create a symbolic link to the <code>sites-enabled</code> directory.</p>
<pre class="code-pre "><code langs="">ln -s /etc/nginx/sites-available/example.com /etc/nginx/sites-enabled/example.com
</code></pre>
<p>Perform a configuration test to check for errors.</p>
<pre class="code-pre "><code langs="">service nginx configtest
</code></pre>
<p>If no errors are displayed, reload the nginx service.</p>
<pre class="code-pre "><code langs="">service nginx reload
</code></pre>
<p>Load balancing has now been configured for HTTP.</p>

<h2 id="enable-ssl">Enable SSL</h2>

<p>Add the following directives to the virtual hosts file (/etc/nginx/sites-available/example.com) inside the <code>server {}</code> block. These lines will be shown in context in the next example.</p>
<pre class="code-pre "><code langs="">listen 443 ssl;
ssl on;
ssl_certificate         <span class="highlight">/etc/nginx/ssl/example.com/server.crt</span>;
ssl_certificate_key     <span class="highlight">/etc/nginx/ssl/example.com/server.key</span>;
ssl_trusted_certificate <span class="highlight">/etc/nginx/ssl/example.com/ca-certs.pem</span>;
</code></pre>
<p>Ignore the <code>ssl_trusted_certificate</code> directive if you are using self-signed certificates. Now the <code>server</code> block should look like this:</p>
<pre class="code-pre "><code langs="">server {
    listen 80;
    listen 443 ssl;
    server_name <span class="highlight">example.com www.example.com</span>;

    ssl on;
    ssl_certificate         /etc/nginx/ssl/<span class="highlight">example.com</span>/server.crt;
    ssl_certificate_key     /etc/nginx/ssl/<span class="highlight">example.com</span>/server.key;
    ssl_trusted_certificate /etc/nginx/ssl/<span class="highlight">example.com</span>/ca-certs.pem;

    location / {
        proxy_pass http://<span class="highlight">mywebapp1</span>;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
</code></pre>
<p>Check for configuration errors and reload the Nginx service.</p>
<pre class="code-pre "><code langs="">service nginx configtest && service nginx reload
</code></pre>
<h2 id="securing-the-backend-servers">Securing The Backend Servers</h2>

<p>Currently, the website hosted on the backend servers can be directly accessed by anyone who knows its public IP address. This can be prevented by configuring the web servers on the backends to listen only on the private interface. The steps to do this in Apache are as follows.</p>

<p>Edit the <code>ports.conf</code> file.</p>
<pre class="code-pre "><code langs="">nano /etc/apache2/ports.conf
</code></pre>
<p>Find the following line:</p>
<pre class="code-pre "><code langs="">Listen 80
</code></pre>
<p>Replace it with the <strong>backend server's</strong> own private IP address:</p>
<pre class="code-pre "><code langs="">Listen <span class="highlight">10.130.227.22</span>:80
</code></pre>
<p>Do this on all the backend servers and restart Apache.</p>
<pre class="code-pre "><code langs="">service apache2 restart
</code></pre>
<p>The next step is to restrict HTTP access to the <strong>load balancer's</strong> private IP. The following firewall rule achieves this.</p>
<pre class="code-pre "><code langs="">iptables -I INPUT -m state --state NEW -p tcp --dport 80 ! -s <span class="highlight">10.130.227.33</span> -j DROP
</code></pre>
<p>Replace the example with the load balancer's private IP address and execute this rule on all the backend servers.</p>

<h2 id="testing-the-setup">Testing The Setup</h2>

<p>Create a PHP file on all the backend servers (web1 and web2 in this example). This is for testing and can be removed once the setup is complete.</p>
<pre class="code-pre "><code langs="">nano /var/www/html/test.php
</code></pre>
<p>It should print the accessed domain name, the IP address of the server, the user's IP address, and the accessed port.</p>
<pre class="code-pre "><code langs=""><?php
    header( 'Content-Type: text/plain' );
    echo 'Host: ' . $_SERVER['HTTP_HOST'] . "\n";
    echo 'Remote Address: ' . $_SERVER['REMOTE_ADDR'] . "\n";
    echo 'X-Forwarded-For: ' . $_SERVER['HTTP_X_FORWARDED_FOR'] . "\n";
    echo 'X-Forwarded-Proto: ' . $_SERVER['HTTP_X_FORWARDED_PROTO'] . "\n";
    echo 'Server Address: ' . $_SERVER['SERVER_ADDR'] . "\n";
    echo 'Server Port: ' . $_SERVER['SERVER_PORT'] . "\n\n";
?>
</code></pre>
<p>Access this file several times with your browser or using <code>curl</code>. Use <code>curl -k</code> on self-signed certificate setups to make curl ignore SSL errors.</p>
<pre class="code-pre "><code langs="">curl https://<span class="highlight">example.com</span>/test.php https://<span class="highlight">example.com</span>/test.php https://<span class="highlight">example.com</span>/test.php
</code></pre>
<p>The output will be similar to the following.</p>
<pre class="code-pre "><code langs="">   Host: example.com
   Remote Address: 10.130.245.116
   X-Forwarded-For: 117.193.105.174
   X-Forwarded-Proto: https
   Server Address: 10.130.227.11
   Server Port: 80

   Host: example.com
   Remote Address: 10.130.245.116
   X-Forwarded-For: 117.193.105.174
   X-Forwarded-Proto: https
   Server Address: 10.130.227.22
   Server Port: 80

   Host: example.com
   Remote Address: 10.130.245.116
   X-Forwarded-For: 117.193.105.174
   X-Forwarded-Proto: https
   Server Address: 10.130.227.11
   Server Port: 80
</code></pre>
<p>Note that the <strong>Server Address</strong> changes on each request, indicating that a different server is responding to each request.</p>

<h2 id="hardening-ssl-configuration">Hardening SSL Configuration</h2>

<p>This section explains configuring SSL according to best practices to eliminate vulnerabilities with older ciphers and protocols. Individual lines are shown in this section and the complete configuration file is shown in the last section of this tutorial.</p>

<p>Enabling SSL session cache improves the performance of HTTPS websites. The following directives must be placed <strong>after</strong> <code>ssl_trusted_certificate</code>. They enable shared caching of size <em>20MB</em> with a cache lifetime of <em>10 minutes</em>.</p>
<pre class="code-pre "><code langs="">ssl_session_cache shared:SSL:20m;
ssl_session_timeout 10m;
</code></pre>
<p>Specify the protocols and ciphers to be used in the SSL connection. Here we have omitted SSLv2 and disabled insecure ciphers like MD5 and DSS.</p>
<pre class="code-pre "><code langs="">ssl_prefer_server_ciphers       on;
ssl_protocols                   TLSv1 TLSv1.1 TLSv1.2;
ssl_ciphers                     ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:ECDH+3DES:DH+3DES:RSA+AESGCM:RSA+AES:RSA+3DES:!aNULL:!MD5:!DSS;
</code></pre>
<p><a href="http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security">Strict Transport Security</a> instructs all supporting web browsers to use only HTTPS. Enable it with the <code>add_header</code> directive.</p>
<pre class="code-pre "><code langs="">add_header Strict-Transport-Security "max-age=31536000";
</code></pre>
<p>Check for configuration errors and reload the Nginx service.</p>
<pre class="code-pre "><code langs="">service nginx configtest && service nginx reload
</code></pre>
<h2 id="complete-configuration">Complete Configuration</h2>

<p>After configuring and hardening SSL termination, the complete configuration file will look like this:</p>

<p><code>/etc/nginx/sites-available/<span class="highlight">example.com</span></code></p>
<pre class="code-pre "><code langs="">upstream <span class="highlight">mywebapp1</span> {
    server <span class="highlight">10.130.227.11</span>;
    server <span class="highlight">10.130.227.22</span>;
}

server {
    listen 80;
    listen 443 ssl;
    server_name <span class="highlight">example.com www.emxaple.com</span>;

    ssl on;
    ssl_certificate         <span class="highlight">/etc/nginx/ssl/example.com/server.crt</span>;
    ssl_certificate_key     <span class="highlight">/etc/nginx/ssl/example.com/server.key</span>;
    ssl_trusted_certificate <span class="highlight">/etc/nginx/ssl/example.com/ca-certs.pem</span>;

    ssl_session_cache shared:SSL:20m;
    ssl_session_timeout 10m;

    ssl_prefer_server_ciphers       on;
    ssl_protocols                   TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers                     ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:ECDH+3DES:DH+3DES:RSA+AESGCM:RSA+AES:RSA+3DES:!aNULL:!MD5:!DSS;

    add_header Strict-Transport-Security "max-age=31536000";

    location / {
        proxy_pass http://<span class="highlight">mywebapp1</span>;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
</code></pre>
<p>Do a <a href="https://www.ssllabs.com/ssltest/">SSL Server Test</a> and this setup should get an A+ grade. Run the curl test again to check if everything is working properly.</p>
<pre class="code-pre "><code langs="">curl https://<span class="highlight">example.com</span>/test.php https://<span class="highlight">example.com</span>/test.php https://<span class="highlight">example.com</span>/test.php
</code></pre>
<h3 id="further-reading">Further Reading</h3>

<p>To learn more about load-balancing algorithms read <a href="https://indiareads/community/tutorials/how-to-set-up-nginx-load-balancing">this article</a>.</p>

    