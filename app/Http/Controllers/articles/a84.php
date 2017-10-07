<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/06022014_5_Common_Server_Setups_Twitter-01.png?1426699692/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When deciding which server architecture to use for your environment, there are many factors to consider, such as performance, scalability, availability, reliability, cost, and ease of management.</p>

<p>Here is a list of commonly used server setups, with a short description of each, including pros and cons. Keep in mind that all of the concepts covered here can be used in various combinations with one another, and that every environment has different requirements, so there is no single, correct configuration.</p>

<h2 id="1-everything-on-one-server">1. Everything On One Server</h2>

<p>The entire environment resides on a single server. For a typical web application, that would include the web server, application server, and database server. A common variation of this setup is a LAMP stack, which stands for Linux, Apache, MySQL, and PHP, on a single server.</p>

<p><strong>Use Case</strong>: Good for setting up an application quickly, as it is the simplest setup possible, but it offers little in the way of scalability and component isolation.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/single_server.png" alt="Everything On a Single Server" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Simple</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>Application and database contend for the same server resources (CPU, Memory, I/O, etc.) which, aside from possible poor performance, can make it difficult to determine the source (application or database) of poor performance</li>
<li>Not readily horizontally scalable</li>
</ul>

<p><strong>Related Tutorials</strong>:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">How To Install LAMP On Ubuntu 14.04</a></li>
</ul>

<h2 id="2-separate-database-server">2. Separate Database Server</h2>

<p>The database management system (DBMS) can be separated from the rest of the environment to eliminate the resource contention between the application and the database, and to increase security by removing the database from the DMZ, or public internet.</p>

<p><strong>Use Case</strong>: Good for setting up an application quickly, but keeps application and database from fighting over the same system resources.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/separate_database.png" alt="Separate Database Server" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Application and database tiers do not contend for the same server resources (CPU, Memory, I/O, etc.)</li>
<li>You may vertically scale each tier separately, by adding more resources to whichever server needs increased capacity</li>
<li>Depending on your setup, it may increase security by removing your database from the DMZ</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>Slightly more complex setup than single server</li>
<li>Performance issues can arise if the network connection between the two servers is high-latency (i.e. the servers are geographically distant from each other), or the bandwidth is too low for the amount of data being transferred</li>
</ul>

<p><strong>Related Tutorials</strong>:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-set-up-a-remote-database-to-optimize-site-performance-with-mysql">How To Set Up a Remote Database to Optimize Site Performance with MySQL</a></li>
<li><a href="https://indiareads/community/articles/how-to-migrate-a-mysql-database-to-a-new-server-on-ubuntu-14-04">How to Migrate A MySQL Database To A New Server On Ubuntu 14.04</a></li>
</ul>

<h2 id="3-load-balancer-reverse-proxy">3. Load Balancer (Reverse Proxy)</h2>

<p>Load balancers can be added to a server environment to improve performance and reliability by distributing the workload across multiple servers. If one of the servers that is load balanced fails, the other servers will handle the incoming traffic until the failed server becomes healthy again. It can also be used to serve multiple applications through the same domain and port, by using a layer 7 (application layer) reverse proxy.</p>

<p>Examples of software capable of reverse proxy load balancing: HAProxy, Nginx, and Varnish.</p>

<p><strong>Use Case</strong>: Useful in an environment that requires scaling by adding more servers, also known as horizontal scaling.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/load_balancer.png" alt="Load Balancer" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Enables horizontal scaling, i.e. environment capacity can be scaled by adding more servers to it</li>
<li>Can protect against DDOS attacks by limiting client connections to a sensible amount and frequency</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>The load balancer can become a performance bottleneck if it does not have enough resources, or if it is configured poorly</li>
<li>Can introduce complexities that require additional consideration, such as where to perform SSL termination and how to handle applications that require sticky sessions</li>
<li>The load balancer is a single point of failure; if it goes down, your whole service can go down. A <em>high availability</em> (HA) setup is an infrastructure without a single point of failure. To learn how to implement an HA setup, you can read <a href="https://indiareads/community/tutorials/how-to-use-floating-ips-on-digitalocean#how-to-implement-an-ha-setup">this section of How To Use Floating IPs</a>.</li>
</ul>

<p><strong>Related Tutorials</strong>:</p>

<ul>
<li><a href="https://indiareads/community/articles/an-introduction-to-haproxy-and-load-balancing-concepts">An Introduction to HAProxy and Load Balancing Concepts</a></li>
<li><a href="https://indiareads/community/articles/how-to-use-haproxy-as-a-layer-4-load-balancer-for-wordpress-application-servers-on-ubuntu-14-04">How To Use HAProxy As A Layer 4 Load Balancer for WordPress Application Servers</a></li>
<li><a href="https://indiareads/community/articles/how-to-use-haproxy-as-a-layer-7-load-balancer-for-wordpress-and-nginx-on-ubuntu-14-04">How To Use HAProxy As A Layer 7 Load Balancer For WordPress and Nginx</a></li>
</ul>

<h2 id="4-http-accelerator-caching-reverse-proxy">4. HTTP Accelerator (Caching Reverse Proxy)</h2>

<p>An HTTP accelerator, or caching HTTP reverse proxy, can be used to reduce the time it takes to serve content to a user through a variety of techniques. The main technique employed with an HTTP accelerator is caching responses from a web or application server in memory, so future requests for the same content can be served quickly, with less unnecessary interaction with the web or application servers.</p>

<p>Examples of software capable of HTTP acceleration: Varnish, Squid, Nginx.</p>

<p><strong>Use Case</strong>: Useful in an environment with content-heavy dynamic web applications, or with many commonly accessed files.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/http_accelerator.png" alt="HTTP Accelerator" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Increase site performance by reducing CPU load on web server, through caching and compression, thereby increasing user capacity</li>
<li>Can be used as a reverse proxy load balancer</li>
<li>Some caching software can protect against DDOS attacks</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>Requires tuning to get best performance out of it</li>
<li>If the cache-hit rate is low, it could reduce performance</li>
</ul>

<p><strong>Related Tutorials</strong>:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-install-wordpress-nginx-php-and-varnish-on-ubuntu-12-04">How To Install Wordpress, Nginx, PHP, and Varnish on Ubuntu 12.04</a></li>
<li><a href="https://indiareads/community/articles/how-to-configure-a-clustered-web-server-with-varnish-and-nginx-on-ubuntu-13-10">How To Configure a Clustered Web Server with Varnish and Nginx</a></li>
<li><a href="https://indiareads/community/articles/how-to-configure-varnish-for-drupal-with-apache-on-debian-and-ubuntu">How To Configure Varnish for Drupal with Apache on Debian and Ubuntu</a></li>
</ul>

<h2 id="5-master-slave-database-replication">5. Master-Slave Database Replication</h2>

<p>One way to improve performance of a database system that performs many reads compared to writes, such as a CMS, is to use master-slave database replication. Master-slave replication requires a master and one or more slave nodes. In this setup, all updates are sent to the master node and reads can be distributed across all nodes.</p>

<p><strong>Use Case</strong>: Good for increasing the read performance for the database tier of an application.</p>

<p>Here is an example of a master-slave replication setup, with a single slave node:</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/master_slave_database_replication.png" alt="Master-Slave Database Replication" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Improves database read performance by spreading reads across slaves</li>
<li>Can improve write performance by using master exclusively for updates (it spends no time serving read requests)</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>The application accessing the database must have a mechanism to determine which database nodes it should send update and read requests to</li>
<li>Updates to slaves are asynchronous, so there is a chance that their contents could be out of date</li>
<li>If the master fails, no updates can be performed on the database until the issue is corrected</li>
<li>Does not have built-in failover in case of failure of master node</li>
</ul>

<p><strong>Related Tutorials</strong>:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-optimize-wordpress-performance-with-mysql-replication-on-ubuntu-14-04">How To Optimize WordPress Performance With MySQL Replication On Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/articles/how-to-set-up-master-slave-replication-in-mysql">How To Set Up Master Slave Replication in MySQL</a></li>
</ul>

<h2 id="example-combining-the-concepts">Example: Combining the Concepts</h2>

<p>It is possible to load balance the caching servers, in addition to the application servers, and use database replication in a single environment. The purpose of combining these techniques is to reap the benefits of each without introducing too many issues or complexity. Here is an example diagram of what a server environment could look like:</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/combined.png" alt="Load Balancer, HTTP Accelerator, and Database Replication Combined" /></p>

<p>Let's assume that the load balancer is configured to recognize static requests (like images, css, javascript, etc.) and send those requests directly to the caching servers, and send other requests to the application servers.</p>

<p>Here is a description of what would happen when a user sends a requests dynamic content:</p>

<ol>
<li>The user requests dynamic content from <em>http://example.com/</em> (load balancer)</li>
<li>The load balancer sends request to app-backend</li>
<li>app-backend reads from the database and returns requested content to load balancer</li>
<li>The load balancer returns requested data to the user</li>
</ol>

<p>If the user requests static content:</p>

<ol>
<li>The load balancer checks cache-backend to see if the requested content is cached (cache-hit) or not (cache-miss)</li>
<li><em>If cache-hit</em>: return the requested content to the load balancer and jump to Step 7. <em>If cache-miss</em>: the cache server forwards the request to app-backend, through the load balancer</li>
<li>The load balancer forwards the request through to app-backend</li>
<li>app-backend reads from the database then returns requested content to the load balancer</li>
<li>The load balancer forwards the response to cache-backend</li>
<li>cache-backend <em>caches the content</em> then returns it to the load balancer</li>
<li>The load balancer returns requested data to the user</li>
</ol>

<p>This environment still has two single points of failure (load balancer and master database server), but it provides the all of the other reliability and performance benefits that were described in each section above.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Now that you are familiar with some basic server setups, you should have a good idea of what kind of setup you would use for your own application(s). If you are working on improving your own environment, remember that an iterative process is best to avoid introducing too many complexities too quickly.</p>

<p>Let us know of any setups you recommend or would like to learn more about in the comments below! </p>

<div class="author">By Mitchell Anicas</div>

    