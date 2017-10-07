<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/WebApplication.twitter.2.png?1436558177/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This 6-part tutorial will show you how to build out a multi-server production application setup from scratch. The final setup will be supported by backups, monitoring, and centralized logging systems, which will help you ensure that you will be able to detect problems and recover from them. The ultimate goal of this series is to build on standalone system administration concepts, and introduce you to some of the practical considerations of creating a production server setup.</p>

<p>If you are interested in reviewing some of the concepts that will be covered in this series, read these tutorials:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/5-common-server-setups-for-your-web-application">5 Common Server Setups For Your Web Application</a></li>
<li><a href="https://indiareads/community/tutorials/5-ways-to-improve-your-production-web-application-server-setup">5 Ways to Improve your Production Web Application Server Setup</a></li>
</ul>

<p>While the linked articles provide general guidelines of a production application setup, this series will demonstrate how to plan and set up a sample application from start to finish. Hopefully, this will help you plan and implement your own production server environment, even if you are running a different application on a completely different technology stack. Because this tutorial covers many different system administration topics, it will often defer the detailed explanation to external supporting articles that provide supplemental information.</p>

<h2 id="our-goal">Our Goal</h2>

<p>By the end of this set of tutorials, we will have a production server setup for a PHP application, WordPress for demonstration purposes, that is accessible via https://www.example.com/. We will also include servers that will support the production application servers. The final setup will look something like this (private DNS and remote backups not pictured):</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/lamp/final.png" alt="Production Setup" /></p>

<p>In this setup, the servers in the <strong>Application</strong> box are considered to be essential for the application run properly. Aside from the recovery plan and the remote backup server, the remaining components—backups, monitoring, and logging—will be added to support the production application setup. Each component will be installed on a separate Ubuntu 14.04 server within the same IndiaReads region, NYC3 in our example, with Private Networking enabled.</p>

<p>The set of servers that compose application will be referred to as the following hostnames:</p>

<ul>
<li><strong>lb1:</strong> HAProxy Load Balancer, accessible via https://example.com/</li>
<li><strong>app1:</strong> Apache and PHP application server</li>
<li><strong>app2:</strong> Apache and PHP application server</li>
<li><strong>db1:</strong> MySQL database server</li>
</ul>

<p>It is important to note that this type setup was chosen to demonstrate how to components of an application can be built on multiple servers; your own setup should be customized based on your own needs. This particular server setup has single points of failure which could be eliminated by adding another load balancer (and <a href="https://indiareads/community/tutorials/how-to-configure-dns-round-robin-load-balancing-for-high-availability">round-robin DNS</a>) and <a href="https://indiareads/community/tutorials/how-to-set-up-mysql-master-master-replication">database server replication</a> or  adding a static IP that points to either an active or passive load balancer which is covered below which we will briefly cover.</p>

<p>The components that will support the Application servers will be referred to as the following hostnames:</p>

<ul>
<li><strong>backups:</strong> Bacula backups server</li>
<li><strong>monitoring:</strong> Nagios monitoring server</li>
<li><strong>logging:</strong> Elasticsearch, Logstash, Kibana (ELK) stack for centralized logging</li>
</ul>

<p>Additionally, the three following supporting components are not pictured in the diagram:</p>

<ul>
<li><strong>ns1:</strong> Primary BIND nameserver for private DNS</li>
<li><strong>ns2:</strong> Secondary BIND nameserver for private DNS</li>
<li><strong>remotebackups:</strong> Remote server, located in a different region, for storing copies of the Bacula backups in case of a physical disaster in the production datacenter-===\</li>
</ul>

<p>We will also develop basic recovery plans for failures in the various components of the application.</p>

<p>When we reach our goal setup, we will have a total of 10 servers. We'll create them all at once (this simplifies things such as setting up DNS), but feel free to create each one as needed. If you are planning on using IndiaReads backups as your backups solution, in addition to or in lieu of Bacula, be sure to select that option when creating your Droplets.</p>

<h3 id="high-availability-optional">High Availability (Optional)</h3>

<p>A single point of failure is when one part of your infrastructure going down can make your entire site or service unavailable. If you want to address the single points of failure you this setup, you can make it highly available by adding another load balancer. Highly available services automatically fail over to a backup or passive system in the event of a failure. Having two load balancers in a high availability setup protects against downtime by ensuring that one load balancer is always passively available to accept traffic if the active load balancer is unavailable.</p>

<p>There are a number of ways to implement a high availability setup. To learn more, read <a href="https://indiareads/community/tutorials/how-to-use-floating-ips-on-digitalocean#how-to-implement-an-ha-setup">this section of How To Use Floating IPs</a>.</p>

<h3 id="virtual-private-network-optional">Virtual Private Network (Optional)</h3>

<p>If you want to secure the network communications amongst your servers, you may want to consider setting up a VPN. Securing network transmissions with encryption is especially important when the data is traveling over the Internet. Another benefit of using a VPN is that the identities of hosts are validated by the key authentication process, which will protect your services from unauthorized sources.</p>

<p>If you are looking for an open source VPN solution, you may want to consider Tinc or OpenVPN. In this particular case, Tinc, which uses mesh routing, is the better solution. Tutorials on both VPN solutions can be found here:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-tinc-and-set-up-a-basic-vpn-on-ubuntu-14-04">How To Install Tinc and Set Up a Basic VPN on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-secure-traffic-between-vps-using-openvpn">How To Secure Traffic Between VPS Using OpenVPN</a></li>
</ul>

<h2 id="prerequisites">Prerequisites</h2>

<p>Each Ubuntu 14.04 server should have a non-root superuser, which can be set up by following this tutorial: <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a>. All commands will be run as this user, on each server.</p>

<p>We will assume that you have some knowledge of basic Linux security concepts, which we will not cover in detail. If you need a quick Linux security primer, read this article: <a href="https://indiareads/community/tutorials/7-security-measures-to-protect-your-servers">7 Security Measures to Protect your Servers</a>.</p>

<h3 id="domain-name">Domain Name</h3>

<p>We will assume that your application will be served via a domain name, such as "example.com". If you don't already own one, purchase one from a domain name registrar.</p>

<p>Once you have your domain name of choice, you can follow this tutorial to use it with the IndiaReads DNS: <a href="https://indiareads/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars">How to Point to IndiaReads Nameservers From Common Domain Registrars</a>.</p>

<p>In addition to making your site easier to reach (compared to an IP address), a domain name is required to achieve the domain and identity validation benefits of using SSL certificates, which also provide encryption for communication between your application and its users.</p>

<h3 id="ssl-certificate">SSL Certificate</h3>

<p>TLS/SSL provides encryption and domain validation between your application and its users, so we will use an SSL certificate in our setup. In our example, because we want users to access our site at "www.example.com", that is what we will specify as the certificate's Common Name (CN). The certificate will be installed on the HAProxy server, <strong>lb1</strong>, so you may want to generate the certificate keys and CSR there for convenience.</p>

<p>If you require a certificate that provides identity validation, you will need to purchase an SSL certificate. There are a variety of commercial SSL Certificate Authorities from which you can purchase certificates. If you are unsure of how to purchase an SSL certificate, read this tutorial: <a href="https://indiareads/community/tutorials/how-to-install-an-ssl-certificate-from-a-commercial-certificate-authority">How To Install an SSL Certificate from a Commercial Certificate Authority</a>. Skip the <strong>Install Certificate on Web Server</strong> section.</p>

<p>If you don't want to pay for a certificate, consider using StartSSL's free certificate by following the <strong>Prerequisites</strong> section of this tutorial:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-apache-with-a-free-signed-ssl-certificate-on-a-vps">How To Set Up Apache with a Free Signed SSL Certificate on a VPS</a></li>
</ul>

<p>Alternatively, you may also use a self-signed SSL certificate, which can be generated with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ~/<span class="highlight">www.example.com</span>.key -out ~/<span class="highlight">www.example.com</span>.crt
</li></ul></code></pre>
<h2 id="steps-to-reach-our-goal">Steps to Reach Our Goal</h2>

<p>Now that we have an outline of our production application setup, let's create a general plan to achieve our goal.</p>

<p>The components that comprise the application are the most important, so we want those up and running early. However, because we are planning on using name-based address resolution of our private network connections, <strong>we should set up our DNS first</strong>.</p>

<p>Once our DNS is ready, in order to get things up and running, we will set up the servers that comprise the application. Because the database is required by the application, and the application is required by the load balancer, we will set up the components in this order:</p>

<ol>
<li>Database Server</li>
<li>Application Servers</li>
<li>Load Balancer</li>
</ol>

<p>Once we have gone through the steps of setting up our application, we will be able to devise a <strong>recovery plan</strong> for various scenarios. This plan will be useful in determining our backups strategy.</p>

<p>After we have our various recovery plans, we will want to support it by setting up <strong>backups</strong>. Following that, we can set up <strong>monitoring</strong> to make sure our servers and services are in an OK state. Lastly, we will set up <strong>centralized logging</strong> so we can to help us view our logs, troubleshoot issues, and identify trends.</p>

<h2 id="conclusion">Conclusion</h2>

<p>With our general plan ready, we are ready to implement our production application setup. Remember that this setup, while completely functional, is an example that you should be able to glean useful information from, and use what you learned to improve your own application setup.</p>

<p>Continue to the next tutorial to get started with setting up the application: <a href="https://indiareads/community/tutorials/building-for-production-web-applications-deploying">Building for Production: Web Applications — Deploying</a>.</p>

    