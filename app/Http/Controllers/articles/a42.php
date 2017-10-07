<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p>Nginx is a very secure and reliable web server even with a default setup. However, there are many ways to secure Nginx further.</p>

<p>In this article, we will use open source software exclusively while trying to follow some popular web server hardening approaches and security standards. Namely, we'll be talking about preventing information disclosure, enforcing encryption, performing audits, and limiting access.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before following this tutorial, please make sure you complete the following prerequisites:</p>

<ul>
<li><p>Ubuntu 14.04 Droplet (see (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> for details)</p></li>
<li><p>Nginx web server installed and configured as explained in <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-14-04-lts">How To Install Nginx on Ubuntu 14.04 LTS</a></p></li>
<li><p>A registered domain or subdomain pointed to the Droplet's IP. You will need this to test the SSL settings. For more information read this article on <a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How to Point to IndiaReads Nameservers From Common Domain Registrars</a>.</p></li>
<li><p>Non-root sudo user (check out <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a> for details)</p></li>
</ul>

<p>Except otherwise noted, all of the commands that require root privileges in this tutorial should be run as a non-root user with sudo privileges.</p>

<h2 id="step-1-—-updating-all-software">Step 1 — Updating All Software</h2>

<p>Updating your software to the latest version is a great first step in securing your entire system, not just Nginx. </p>

<p>Warning: before updating all the packages on your system, be sure to determine whether or not this will cause issues with anything running on your system other than Nginx. It is a good idea to make a backup of the entire system before performing an operation that affect so many packages at once. You can revert to the backup if issues arise after updating all packages. To update the repository package list and then all the currently installed packages managed with <code>apt-get</code> on your Ubuntu server, run the commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update && sudo apt-get upgrade
</li></ul></code></pre>
<p>Alternatively, you can just upgrade Nginx to the latest version in the Ubuntu repository. This will upgrade the Nginx package and any necessary dependencies:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get upgrade nginx
</li></ul></code></pre>
<h2 id="step-2-—-preventing-information-disclosure">Step 2 — Preventing Information Disclosure</h2>

<p>To begin hardening your Nginx web server, let's begin with limiting the information it discloses. Valuable information is leaked on every level from the HTTP server headers to the application error reporting. </p>

<p>So let's start with the HTTP headers. By default, Nginx shows its name and version in the HTTP headers. You can check this information with <code>curl</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -I http://localhost
</li></ul></code></pre>
<p>The output should look like:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of curl -I http://localhost">Output of curl -I http://localhost</div>HTTP/1.1 200 OK
<span class="highlight">Server: nginx/1.4.6 (Ubuntu)</span>
...
</code></pre>
<p>As you can see, the version of Nginx and the name of the operating system can be seen in the above output. This is not necessarily a serious problem but rather a part of the puzzle that an attacker will try to solve to compromise your Nginx server. That's why we'll hide this information by opening Nginx's main configuration file <code>/etc/nginx/nginx.conf</code> with nano like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/nginx.conf
</li></ul></code></pre>
<p>Then, inside the <code>http</code> configuration part add the line <code>server_tokens   off;</code> like this: </p>
<div class="code-label " title="/etc/nginx/nginx.conf">/etc/nginx/nginx.conf</div><pre class="code-pre "><code langs="">http {

        ##
        # Basic Settings
        ##
        <span class="highlight">server_tokens off;</span>
...
</code></pre>
<p>After that, save and exit the file, and reload Nginx for the change to take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>Now, if you try again the same curl command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -I http://localhost
</li></ul></code></pre>
<p>You will see less information:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of curl -I http://localhost">Output of curl -I http://localhost</div>HTTP/1.1 200 OK
<span class="highlight">Server: nginx</span>
...
</code></pre>
<p>The above output discloses only the fact that this is an Nginx server. You may wonder if you can remove this too. Unfortunately, this is not easily accomplished because there is no configuration option for it. Instead, you will have to recompile Nginx from source which is not worth the efforts.</p>

<p>Besides the <code>Server</code> header, there is another header with sensitive information — <code>X-Powered-By</code>. This header usually shows the version of PHP, Tomcat or any server-side engine behind Nginx. If you run Nginx with PHP the output of <code>curl</code> will look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of curl -I http://localhost on nginx with php">Output of curl -I http://localhost on nginx with php</div>HTTP/1.1 200 OK
Server: nginx
...
<span class="highlight">X-Powered-By: PHP/5.5.9-1ubuntu4.14</span>
...
</code></pre>
<p>The above <code>X-Powered-By</code> header shows that the server is Ubuntu 14 running PHP version 5.5.9. It's very important to hide this information from the <code>X-Powered-By</code> header. You can't do this in Nginx, but instead you should find the corresponding option in the backend engine. For example, with the case of PHP, you have to set the option <code>expose_php = Off</code> in the main <code>php.ini</code> configuration file. By default, this option is set to <code>On</code>.</p>

<p>The next thing to do is to change the 4xx (client-side) error pages, the information from which could be used by an attacker. Commonly, these are <code>Unauthorized 401</code> and <code>Forbidden 403</code> error pages. Unless you are debugging a problem, usually there is no need to show these errors to regular visitors. If you need to know about these errors, you will be still able to find them in the Nginx error log (<code>/var/log/nginx/error.log</code>).</p>

<p>To change these two error pages open the configuration file for your server block, for example the default one:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Inside the main server <code>server</code> configuration part specify:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">server {
...
        <span class="highlight">error_page 401 403 404 /404.html;</span>
...
</code></pre>
<p>After saving the changes to the file, make sure to reload Nginx so that it takes effect with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>The above hints give you the idea of preventing information disclosure — show as little as possible non-essential web content. You should hide service and debugging information not only in Nginx but also in the backend engines (PHP, Tomcat, etc.) and, of course, in the web applications.</p>

<h2 id="step-2-—-configuring-ssl">Step 2 — Configuring SSL</h2>

<p>Running the secure HTTPS protocol with SSL on Nginx is a must for any site which handles sensitive information such as users' credentials, private data, etc. SSL is the only means to make sure that no matter where your site users are and what Internet connection they use, the information they receive and send will be protected.</p>

<p>The article <a href="https://indiareads/community/tutorials/how-to-create-an-ssl-certificate-on-nginx-for-ubuntu-14-04">How To Create an SSL Certificate on Nginx for Ubuntu 14.04</a> explains how to easily setup a free-of-charge SSL with default HTTPS configuration. While this article is a good beginning, it will not efficiently protect your data. Nowadays the default SSL settings and algorithms are not strong enough to prevent an attacker from decrypting your traffic.</p>

<p>That's why we will configure an SSL certificate for Nginx with stronger encryption algorithms and settings. This will ensure a higher level of protection for your data and your HTTPS service will be compliant with the highest security standards and practices.</p>

<p>Let's start with creating a directory for our SSL certificates with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /etc/nginx/ssl/
</li></ul></code></pre>
<p>For our SSL we'll need a certificate with a strong signature algorithm, SHA256. For testing purposes or non-production environments you can use a self-signed certificate and ignore the SSL warnings. Let's create one with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl req -x509 -nodes -sha256 -days 365 -newkey rsa:2048 -keyout /etc/nginx/ssl/nginx.key -out /etc/nginx/ssl/nginx.crt
</li></ul></code></pre>
<p>This command will ask you a few simple questions about the details of your site and business. After that it will create a 2048 bit RSA encrypted key in the file <code>/etc/nginx/ssl/nginx.key</code> and a SHA256 certificate in the file <code>/etc/nginx/ssl/nginx.crt</code>.</p>

<p>Next, you will have to generate stronger, 4096 bit long <a href="https://wiki.openssl.org/index.php/Diffie-Hellman_parameters">DH parameters</a>. Prepare yourself to wait some time, depending on your Droplet, it may take up to 30 minutes. Run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl dhparam -out /etc/nginx/ssl/dhparam.pem 4096
</li></ul></code></pre>
<p>Now you can configure the SSL part of your server block. As an example, let's configure the default server block. Open its configuration file for editing with nano:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>In this file, edit the server configuration part adding the SSL part after the <code>server_name</code> directive like this:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">server {
...
       server_name localhost;

        <span class="highlight">### SSL Part</span>
        <span class="highlight">listen 443 ssl;</span>
        <span class="highlight">ssl_protocols TLSv1 TLSv1.1 TLSv1.2;</span>
        <span class="highlight">ssl_ciphers 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';</span>
        <span class="highlight">ssl_prefer_server_ciphers on;</span>
        <span class="highlight">ssl_dhparam /etc/nginx/ssl/dhparam.pem;</span>
        <span class="highlight">ssl_certificate /etc/nginx/ssl/nginx.crt;</span>
        <span class="highlight">ssl_certificate_key /etc/nginx/ssl/nginx.key;</span>

...
</code></pre>
<p>Here is what instructions we have specified with the above directives:</p>

<ul>
<li><code>listen</code> — enable the SSL listener on port 443, i.e. the HTTPS port.</li>
<li><code>ssl_protocols</code> — enable only these three, considered currently secure protocols - <code>TLSv1 TLSv1.1 TLSv1.2</code>.</li>
<li><code>ssl_ciphers</code> — enable only these secure SSL ciphers: <code>EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH</code></li>
<li><code>ssl_prefer_server_ciphers</code> — make sure that the client respects the server's ciphers preferences.</li>
<li><code>ssl_dhparam</code> — use the custom, strong DH parameters which we have generated earlier.</li>
<li><code>ssl_certificate</code> — use our self-signed SSL certificate. Make sure to change it if you are using another certificate.</li>
<li><code>ssl_certificate_key</code> — use our SSL private key, which we have previously generated.</li>
</ul>

<p>For the above settings to take effect, you will have to reload again nginx with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>To test your new SSL configuration it's best to use an external tool such as the one provided by <a href="https://www.ssllabs.com/ssltest/analyze.html">SSL Labs</a>. There you should disregard the warning that the SSL is not trusted. This is natural because it is a self-signed certificate. Note that this site will only test sites with a registered domain name. You can not test the SSL connection with just your Droplet's IP address.</p>

<p>The overall result should be "T" like for "Testing" but essentially it's an A (the highest possible) and it should say <code>"If trust issues are ignored: A"</code> like this:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_security_1404/ssltest.png" alt="SSL Check" /></p>

<p>Later on, you would probably wish to remove the SSL warning and make the SSL test a clean "A". One option is to use <strong>Let's Encrypt</strong> as described in the article <a href="https://indiareads/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-14-04">How To Secure Nginx with Let's Encrypt on Ubuntu 14.04</a>. It is free, allows you to specify an RSA key size up to 4096, and doesn't give a warning about being self-signed. Otherwise, you can choose any of commercial SSL providers out there. When you choose one just make sure that you opt for a SHA256 certificate.</p>

<h2 id="step-3-—-restricting-access-by-ip">Step 3 — Restricting Access by IP</h2>

<p>Password authentication is not always enough to ensure the security of sensitive areas of your site such as site control panels, phpmyadmin, etc. Sometimes attackers exploit weak passwords or software vulnerabilities in such areas to gain unauthorized access. That's why it's highly recommended to add additional IP restriction provided you can determine the IPs of legitimate user(s).</p>

<p>For example, if you have a WordPress site and its admin area is at <code>/wp-admin/</code>, you should limit the access to it only to your IP or to the IPs of all the administrators. For this purpose, open the corresponding server block — the default server block for Nginx is <code>/etc/nginx/sites-enabled/default</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Inside the <code>server</code> configuration part add:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">server {
...
    location /wp-admin/ {
        allow <span class="highlight">192.168.1.1</span>/24;
     allow <span class="highlight">10.0.0.1</span>/24;
        deny  all;
}
...
...
</code></pre>
<p>In the above please make sure to replace <code>192.168.1.1</code> and <code>10.0.0.1</code> with your IPs. Similarly, you can allow access for other IPs or even networks by changing the network mask (<code>/24</code>).</p>

<p>For such settings to take effect you will have to reload Nginx again with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx reload
</li></ul></code></pre>
<p>Now if you try to access the <code>/wp-admin/</code> part of your site with a browser outside of the allowed IP address ranges, you will get an error. This error will be 403 Forbidden (unless you have changed this error to 404 Not found as previously explained). At the same time you will see the true error code in the error log with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail /var/log/nginx/error.log
</li></ul></code></pre>
<p>The <code>access forbidden</code> error will show like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of sudo tail -f /var/log/nginx/error.log">Output of sudo tail -f /var/log/nginx/error.log</div>...
2016/01/02 04:16:12 [error] 4767#0: *13 <span class="highlight">access forbidden</span> by rule, client: X.X.X.X, server: localhost, request: "GET /wp-admin/ HTTP/1.1", host: "Y.Y.Y.Y"
...
</code></pre>
<p>The combination of applying more than one approach to security, such as changing the error page and restricting access by IP, shows the cumulative effect of hardening Nginx. As per the example, instead of the usual WordPress admin page, attackers and the automated tools they use will see a 404 not found page. This is confusing and may discourage them from trying other approaches to compromising your WordPress.  </p>

<h2 id="step-4-— performing-a-security-audit">Step 4 — Performing a Security Audit</h2>

<p>It's always a good idea to have a security check independent from your own opinion. For this purpose you can use a security auditing tool which scans for web vulnerabilities. There are many such tools, including commercial ones, and for a start you can use wapiti which is free and open source. Wapiti may lack some of the features of the more advanced tools, but it will give you the idea of what security auditing is.</p>

<p>You can install wapiti on Ubuntu via apt:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install wapiti
</li></ul></code></pre>
<p>Then start scanning your site with wapiti with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wapiti http://<span class="highlight">example.org</span> -n 10 -b folder
</li></ul></code></pre>
<p>Make sure to replace <code>example.org</code> with the name of your site. We have given two additional arguments to the command. The first <code>-n 10</code> limits the number of URLs with the same pattern to 10 so that endless loops are prevented. The second argument <code>-b folder</code> sets the scope of the scan only to the given domain.</p>

<p>After the scan completes you will have the results inside a directory called <code>generated_report</code> inside the directory from which you have run the scan. For best viewing, download this directory to your local computer and open the <code>index.html</code> file with a web browser. </p>

<p>Inside the report you will see the vulnerabilities sorted in 10 different categories: SQL Injection, Blind SQL Injection, File Handling, Cross Site Scripting, CRLF, Commands execution, Resource consumption, Htaccess Bypass, Backup file, and Potentially dangerous file.</p>

<p>Ideally, your report should look like this with no vulnerabilities found:</p>

<p><img src="https://assets.digitalocean.com/articles/nginx_security_1404/wapiti_report.png" alt="Wapiti Report" /></p>

<p>If there are vulnerabilities, you can expand the corresponding part of the scan for more information.</p>

<p>Make sure to run such scans frequently and with different tools to ensure to the most complete and thorough auditing of your Nginx and websites.</p>

<h2 id="step-5-— taking-additional-security-measures">Step 5 — Taking Additional Security Measures</h2>

<p>Some topics regarding Nginx security are not covered in this article because there are already excellent article about them. Please get acquainted with the following ones:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-and-configure-naxsi-on-ubuntu-14-04">How to Install and Configure Naxsi on Ubuntu 14.04</a></li>
</ul>

<p>Naxsi is a web application firewall for Nginx. It protects you both from known and unknown web vulnerabilities by using a compilation of malicious signatures.</p>

<p>You should know that Naxsi is a complex piece of software and its tweaking takes some time and efforts. Luckily, there are readily available configurations for most popular web applications which you can customize further if needed. </p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-protect-an-nginx-server-with-fail2ban-on-ubuntu-14-04">How To Protect an Nginx Server with Fail2Ban on Ubuntu 14.04</a></li>
</ul>

<p>Fail2ban is a great tool to take web security to the next level and proactively protect your nginx server. So far, we have restricted users from finding certain information and accessing parts of our site. With fail2ban you can further block attackers for certain periods of time when you detect that they are performing malicious activity. </p>

<ul>
<li><a href="https://indiareads/community/tutorials/lemp-stack-monitoring-with-monit-on-ubuntu-14-04">LEMP Stack Monitoring with Monit on Ubuntu 14.04</a></li>
</ul>

<p>Monitoring is essential to security, and Monit is a great tool for this purpose with good support for Nginx. Not only do web logs show traces of malicious activities, but they also show spikes in CPU load and memory usage. </p>

<p>In this article pay special attention to Step 5 — Monitor Logs for Errors and Keywords. There you can configure custom alerts to be sent upon security events such as when someone accesses or tries to access sensitive parts of your site(s).</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using Iptables on Ubuntu 14.04</a></li>
</ul>

<p>Having a firewall is very important for the security of your nginx and your droplet as a whole. Make sure that you add the https (tcp 443) port to the allowed incoming connections besides the standard http (tcp 80) port.</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-aide-on-a-digitalocean-vps">How To Install Aide on a IndiaReads VPS</a></li>
</ul>

<p>A file and directory integrity checker, such as AIDE, alerts for changes in files and directories. This is especially handy for web files because you should be aware when parts of your site changes and new files/directories are added. To learn more about AIDE you can start with the article.</p>

<p>The above article is a bit outdated and not specifically written for Ubuntu. However, you should be able to easily adapt it and apply it for Ubuntu 14.04 as well. When you configure AIDE, or another similar tool, make sure to exclude your web logs and temporary files such as web cache from being monitored. </p>

<h2 id="conclusion">Conclusion</h2>

<p>After reading this article you should feel more confident about Nginx security. Just make sure to look for the balance between functionality and security so that you have peace of mind that your web environment operates as designed yet secure. Also, keep in mind that securing Nginx is an ongoing task which requires regular updating, reconfiguration, scanning, etc.</p>

    