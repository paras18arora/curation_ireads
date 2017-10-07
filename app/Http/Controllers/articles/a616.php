<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Tomcat is a popular implementation of the Java Servlet and JavaServer Pages technologies. It is released by the Apache Software Foundation under the popular Apache open source license. Its powerful features, favorable license, and great community makes it one of the best and most preferred Java servlets.</p>

<p>Tomcat almost always requires additional fine-tuning after its installation. Read this article to learn how to optimize your Tomcat installation so that it runs securely and efficiently.</p>

<p>This article continues the subject of running Tomcat on Ubuntu 14.04, and it is assumed that you have previously read <a href="https://indiareads/community/tutorials/how-to-install-apache-tomcat-7-on-ubuntu-14-04-via-apt-get">How To Install Apache Tomcat 7 on Ubuntu 14.04 via Apt-Get</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide has been tested on Ubuntu 14.04. The described installation and configuration would be similar on other OS or OS versions, but the commands and location of configuration files may vary.</p>

<p>For this tutorial, you will need:</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>A non-root sudo user (see <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>)</li>
<li>Tomcat installed and configured per the instructions in <a href="https://indiareads/community/tutorials/how-to-install-apache-tomcat-7-on-ubuntu-14-04-via-apt-get">How To Install Apache Tomcat 7 on Ubuntu 14.04 via Apt-Get</a></li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>.</p>

<h2 id="serving-requests-on-the-standard-http-port">Serving Requests on the Standard HTTP Port</h2>

<p>As you have probably already noticed, Tomcat listens on TCP port 8080 by default. This default port comes mainly because of the fact that Tomcat runs under the unprivileged user <code>tomcat7</code>. In Linux, only privileged users like <code>root</code> are allowed to listen on ports below 1024 unless otherwise configured. Thus, you cannot simply change Tomcat's listener port to 80 (HTTP).</p>

<p>So, the first task of optimizing your Tomcat installation is solving the above problem and making sure your Tomcat web applications are available on the standard HTTP port.</p>

<p>The simplest way (but not necessarily the best way) to resolve this is by creating a firewall (iptables) — forwarding from TCP port 80 to TCP port 8080. This can be done with the <code>iptables</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -t nat -A PREROUTING -p tcp --dport 80 -j REDIRECT --to-ports 8080
</li></ul></code></pre>
<p>To make this iptables rule permanent check the article <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using IPTables on Ubuntu 14.04</a> in the part <strong>Saving your Iptables Configuration</strong>. </p>

<p>To remove this iptables rule, you can simply replace the <code>-A</code> flag for adding rules with the <code>-D</code> flag for removing rules in the above command like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -t nat -D PREROUTING -p tcp --dport 80 -j REDIRECT --to-ports 8080
</li></ul></code></pre>
<p>Such a simple traffic forwarding is not optimal from a security or a performance point of view. Instead, a good practice is to add a web server, such as Nginx, in front of Tomcat. The reason for this is that Tomcat is just a Java servlet with basic functions of a web server while Nginx is a typical, powerful, fully functional web server. Here are some important benefits of using Nginx as a front end server:</p>

<ul>
<li>Nginx is more secure than Tomcat and could efficiently protect it from various attacks. In case of urgent security updates, it's much easier, faster, and safer to update the frontend Nginx web server than to worry about downtime and compatibility issues associated with Tomcat upgrades.</li>
<li>Nginx more efficiently serves HTTP and HTTPS traffic with better support for static content, caching, and SSL.</li>
<li>Nginx is easily configured to listen on any port, including 80 and 443.</li>
</ul>

<p>If are convinced of the above benefits, then first make sure that you have removed the previous iptables rule and then install Nginx with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install nginx
</li></ul></code></pre>
<p>After that, edit Nginx's default server block configuration (<code>/etc/nginx/sites-enabled/default</code>) with your favorite editor like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>Look for the <code>location /</code> part, which specifies how all requests should be served and make sure it looks like this:</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">location / {
    proxy_pass http://127.0.0.1:8080/;
}
</code></pre>
<p>The above <code>proxy_pass</code> directive means that all request should be forwarded to the local IP 127.0.0.1 on TCP port 8080 where Tomcat listens. Close the file, and restart Nginx with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p>After that, try accessing Tomcat by connecting to your Droplet's IP at the standard HTTP port in your browser. The URL should look like <code>http://<span class="highlight">your_droplet's_ip</span></code>. If everything works fine, Tomcat's default page should be opened. If not, make sure that you have removed the iptables rule and that Tomcat has been properly installed as per the prerequisites of this article.</p>

<h2 id="securing-tomcat">Securing Tomcat</h2>

<p>Securing Tomcat is probably the most important task which is often neglected. Luckily, in just a few steps you can have a fairly secure Tomcat setup. To follow this part of the article you should have Nginx installed and configured in front of Tomcat as previously described.</p>

<h3 id="removing-the-administrative-web-applications">Removing the Administrative Web Applications</h3>

<p>The usual trade-off between functionality and security is valid for Tomcat too. To increase security, you can remove the default web manager and host-manager applications. This will be inconvenient because you will have to do all the administration, including web application deployments, from the command line.</p>

<p>Removing Tomcat's web admin tools is good for security because you don't have to worry that someone may misuse them. This good security practice is commonly applied for production sites. </p>

<p>The administrative web applications are contained in Ubuntu's package <code>tomcat7-admin</code>. Thus, to remove them run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get remove tomcat7-admin
</li></ul></code></pre>
<h3 id="restricting-access-to-the-administrative-web-applications">Restricting Access to the Administrative Web Applications</h3>

<p>If you haven't removed the administrative web applications, as recommended in the previous part, then we can at least restrict the access to them. Their URLs should be <code>http://<span class="highlight">your_servlet_ip</span>/manager/</code> and <code>http://<span class="highlight">your_servlet_ip</span>/host-manager/</code>. If you see a <em>404 Not Found</em> error at these URLs, then it means they have already been removed, and you don't have to do anything. Still, you can read the following instructions to learn how to proceed with other sensitive resources you may wish to protect. </p>

<p>At this point, Nginx is accepting connections on port 80 so that you can access all web applications at <code>http://<span class="highlight">your_servlet_ip</span></code> from everywhere. Similarly, Tomcat listens on port 8080 globally, i.e. <code>http://<span class="highlight">your_servlet_ip</span>:8080</code>, where you can find the same applications. To improve the security, we will restrict the resources available on port 80 via Nginx. We will also make Tomcat and its exposed port 8080 available only locally to the server and Nginx. </p>

<p>Open the default server block configuration file <code>/etc/nginx/sites-enabled/default</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-enabled/default
</li></ul></code></pre>
<p>After the <code>server_name</code> directive but above the default root location (<code>location /</code>) add the following and replace <code><span class="highlight">your_local_ip</span></code> with your local computer's IP address: </p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">...
location /manager/ {
    allow <span class="highlight">your_local_ip</span>;
    deny all;
    proxy_pass http://127.0.0.1:8080/manager/;
}
...
</code></pre>
<p>You should apply the same restriction for the host-manager application by adding another configuration block in which <code>manager</code> is replaced by <code>host-manager</code> like this (again, replace <code><span class="highlight">your_local_ip</span></code> with your local IP address):</p>
<div class="code-label " title="/etc/nginx/sites-enabled/default">/etc/nginx/sites-enabled/default</div><pre class="code-pre "><code langs="">...
location /host-manager/ {
    allow <span class="highlight">your_local_ip</span>;
    deny all;
    proxy_pass http://127.0.0.1:8080/host-manager/;
}
...
</code></pre>
<p>Once you restart Nginx, access to the <code>manager</code> and <code>host-manager</code> web contexts will be limited only to your local IP address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<p>You can test it by opening in your browser <code>http://<span class="highlight">your_servlet_ip</span>/manager/</code> and <code>http://<span class="highlight">your_servlet_ip</span>/host-manager/</code>. The applications should be available, but if you try to access the same URLs using a public proxy or a different computer, then you should see a 403 Forbidden error. </p>

<p>Furthermore, as an extra measure you can also remove Tomcat's documentation and examples with the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get remove tomcat7-docs tomcat7-examples
</li></ul></code></pre>
<p>Please note that Tomcat still listens for external connections on TCP port 8080. Thus, Nginx and its security measures can be easily bypassed. To resolve this problem configure Tomcat to listen on the local interface 127.0.0.1 only. For this purpose open the file <code>/etc/tomcat7/server.xml</code> with your favorite editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/tomcat7/server.xml
</li></ul></code></pre>
<p>Add <code>address="127.0.0.1"</code> in the <code>Connector</code> configuration part like this:</p>
<div class="code-label " title="/etc/tomcat7/server.xml">/etc/tomcat7/server.xml</div><pre class="code-pre "><code langs="">...
<Connector <span class="highlight">address="127.0.0.1"</span> port="8080" protocol="HTTP/1.1"
    connectionTimeout="20000"
    URIEncoding="UTF-8"
    redirectPort="8443" />
...
</code></pre>
<p>After that restart Tomcat for the new setting to take effect:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service tomcat7 restart
</li></ul></code></pre>
<p>Following the above steps would ensure that you have a good, basic level of security for Tomcat.</p>

<h2 id="fine-tuning-the-jvm-settings">Fine Tuning the JVM settings</h2>

<p>Naturally, the universal Java Virtual Machine (JVM) fine-tuning principles are applicable to Tomcat too. While the JVM tuning is a whole science of itself, there are some basic, good practices which anyone can easily apply:</p>

<ul>
<li>The maximum heap size,<code>Xmx</code>, is the maximum memory Tomcat can use. It should be set to a value which leaves enough free memory for the Droplet itself to run and any other services you may have on the Droplet. For example, if your Droplet has 2 GB of RAM, then it might be safe to allocate 1GB of RAM to xmx. However, please bear in mind that the actual memory Tomcat uses will be a little bit higher than the size of <code>Xmx</code>. </li>
<li>The minimal heap size,<code>Xms</code>, is the amount of memory allocated at startup. It should be equal to the xmx value in most cases. Thus, you will avoid having the costly memory allocation process running because the size of the allocated memory will be constant all the time.</li>
<li>The memory where classes are stored permanently, <code>MaxPermSize</code>, should allow Tomcat to load your applications' classes and leave spare memory from the <code>Xmx</code> value for the instantiation of these classes. If you are not sure how much memory your applications' classes require, then you could set the <code>MaxPermSize</code> to half the size of <code>Xmx</code> as a start — 512 MB in our example. </li>
</ul>

<p>On Ubuntu 14.04 you can customize Tomcat's JVM options by editing the file <code>/etc/default/tomcat7</code>. So, to apply the above tips please open this file with your favorite editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/default/tomcat7
</li></ul></code></pre>
<p>If you have followed Tomcat's installation instructions from the prerequisites you should find the following line:</p>
<div class="code-label " title="/etc/default/tomcat7">/etc/default/tomcat7</div><pre class="code-pre "><code langs="">...
JAVA_OPTS="-Djava.security.egd=file:/dev/./urandom -Djava.awt.headless=true -Xmx512m -XX:MaxPermSize=256m -XX:+UseConcMarkSweepGC"
...
</code></pre>
<p>Provided your Droplet has 2 GB of RAM and you want to allocate around 1 GB to Tomcat, this line should be changed to:</p>
<div class="code-label " title="/etc/default/tomcat7">/etc/default/tomcat7</div><pre class="code-pre "><code langs="">...
JAVA_OPTS="-Djava.security.egd=file:/dev/./urandom -Djava.awt.headless=true <span class="highlight">-Xms1024m</span> -Xmx<span class="highlight">1024m</span> -XX:MaxPermSize=<span class="highlight">512m</span> -XX:+UseConcMarkSweepGC"
...
</code></pre>
<p>For this setting to take effect, you have to restart Tomcat:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service tomcat7 restart
</li></ul></code></pre>
<p>The above JVM configuration is a good start, but you should monitor Tomcat's log (<code>/var/log/tomcat7/catalina.out</code>) for problems, especially after restarting Tomcat or doing deployments. To monitor the log use the <code>tail</code> command like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo tail -f /var/log/tomcat7/catalina.out
</li></ul></code></pre>
<p>If you are new to <code>tail</code>, you have to press the key combination <code>Ctrl-C</code> on your keyboard to stop tailing the log.</p>

<p>Search for errors like <code>OutOfMemoryError</code>. Such an error would indicate that you have to adapt the JVM settings and more specifically increase the <code>Xmx</code> size. </p>

<h2 id="conclusion">Conclusion</h2>

<p>That's it! Now you have secured and optimized Tomcat in just a few easy-to-follow steps. These basic optimizations are recommended, not only for production, but even for test and development environments which are exposed to the Internet. </p>

    