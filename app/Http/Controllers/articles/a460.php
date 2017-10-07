<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/five-ways.png?1426699816/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Once your application is up and running in a cloud server environment, you may be wondering how you can improve your server environment to make the leap from "it works" to a full-fledged production environment. This article will help you get started with planning and implementing a production environment by creating a loose definition of "production", in the context of a web application in a cloud server environment, and by showing you some components that you can add to your existing architecture to make the transition.</p>

<p>For the purposes of this demonstration, let's assume that we're starting with a setup similar to one described in <a href="https://indiareads/community/tutorials/5-common-server-setups-for-your-web-application">5 Common Server Setups</a>, like this two-server environment that simply serves a web application:</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/it_works.png" alt="Application Setup" /></p>

<p>Your actual setup might be simpler or more complex, but the general ideas and components discussed herein should apply to any server environment to a some extent.</p>

<p>Let's get started with defining what we mean when we say "production environment".</p>

<h2 id="what-is-a-production-environment">What is a Production Environment?</h2>

<p>A server environment for a web application, in a general sense, consists of the hardware, software, data, operational plans, and personnel that are necessary to keep the application working. A production environment typically refers to a server environment that was designed and implemented with utmost consideration for acceptable levels of these factors:</p>

<ul>
<li><strong>Availability</strong>: The ability for the application to be usable by its intended users during advertised hours. Availability can be disrupted by any failure that affects a critical component severely enough (e.g. the application crashes due to a bug, the database storage device fails, or the system administrator accidentally powers off the application server).</li>
</ul>

<p>One way to promote availability is to decrease the number of <em>single points of failure</em> in an environment. For example, using a static IP and a monitoring failover service ensures that users only access healthy load balancers.To learn more, read <a href="https://indiareads/community/tutorials/how-to-use-floating-ips-on-digitalocean#how-to-implement-an-ha-setup">this section of How To Use Floating IPs</a> and this <a href="https://indiareads/community/tutorials/how-to-set-up-nginx-load-balancing">article on load balancing</a>.</p>

<ul>
<li><p><strong>Recoverability</strong>: The ability to recover an application environment in the event of system failure or data loss. If a critical component fails, and is not recoverable, availability will become non-existent. Improving <em>maintainability</em>, a related concept, reduces the time needed to perform a given recovery process in the event of a failure, and therefore can improve availability in the event of a failure</p></li>
<li><p><strong>Performance</strong>: The application performs as expected under average or peak load (e.g. it is reasonably responsive). While very important to your users, performance only matters if the application is available</p></li>
</ul>

<p>Take some time to define acceptable levels for each of the items just mentioned, in the context of your application. This will vary depending on the importance and nature of the application in question. For example, it is probably acceptable for a personal blog that serves few visitors to suffer from occasional downtime or poor performance, as long as the blog can be recovered, but a company's online store should strive very high marks across the board. Of course, it would be nice to achieve 100% in every category, for every application, but that is often not feasible due to time and money constraints.</p>

<p>Note that we have not mentioned (a) hardware reliability, the probability that a given hardware component will function properly for a specified amount of time before failure, or (b) security as factors. This is because we are assuming (a) the cloud servers you are using are generally reliable but have the potential for failure (as they run on physical servers), and (b) you are following security best practices to the best of your abilities—simply put, they are outside of the scope of this article. You should be aware, however, that reliability and security are factors that can directly affect availability, and both can contribute the need for recoverability.</p>

<p>Instead of showing you a step-by-step procedure for creating a production environment, which is impossible due the varying needs and nature of every application, we will present some tangible components that can utilize to transform your existing setup into a production environment.</p>

<p>Let's take a look at the components!</p>

<h2 id="1-backup-system">1. Backup System</h2>

<p>A backup system will grant you with the ability to create periodic backups of your data, and restore data from backups. Backups also allow for rollbacks in your data, to a previous state, in the event of accidental deletion or undesired modification, which can occur due to a variety of reasons including human error. All computer hardware has a chance of failure at some point in time, which can potentially cause data loss. With this in mind, you should maintain recent backups of all your important data.</p>

<p><strong>Required for Production?</strong> Yes. A backup system can mitigate the effects of data loss, which is necessary to achieve recoverability and, therefore, aids availability in the event of data loss—but it must be used in conjunction with solid <em>recovery plans</em>, which are discussed in the next section. Note that IndiaReads's snapshot-based backups may not be sufficient for all of your backup needs, as it is not well-suited for making backups of active databases and other applications with high disk write I/O—if you run these types of applications, or want more backup scheduling flexibility, be sure to use another backup system such as Bacula.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/backup_system.png" alt="Example Backup System" /></p>

<p>The diagram above is an example of a basic backup system. The backup server resides in the same data center as the application servers, where the initial backups are created. Later, off-site copies of the backups are made to a server in a different data center to ensure the data is preserved in the case of, say, a natural disaster.</p>

<h4 id="considerations">Considerations</h4>

<ul>
<li><strong>Backup Selection:</strong> The data that you will back up. Minimally, back up any data that you can't reliably reproduce from an alternative source</li>
<li><strong>Backup Schedule:</strong> When and how frequently you will perform full or incremental backups. Special considerations must be taken for backups of certain types of data, such as active databases, which can affect your backup schedule</li>
<li><strong>Data Retention Period:</strong> How long you will keep your backups before deleting them</li>
<li><strong>Disk Space for Backups:</strong> The combination of three previous items affects the amount of disk space your backup system will require. Take advantage of compression and incremental backups to decrease the disk space required by your backups</li>
<li><strong>Off-site Backups:</strong> To protect your backups against local disasters, within a particular datacenter, it is advisable to maintain a copy of your backups in a geographically separate location. In the diagram above, the backups of NYC3 are copied to SFO1 for this purpose</li>
<li><strong>Backup Restoration Tests:</strong> Periodically test your backup restoration process to make sure that your backups work properly</li>
</ul>

<h4 id="related-tutorials">Related Tutorials</h4>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-choose-an-effective-backup-strategy-for-your-vps">How To Choose an Effective Backup Strategy for your VPS</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-bacula-server-on-ubuntu-14-04">How To Install Bacula Server on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-rsync-to-sync-local-and-remote-directories-on-a-vps">How To Use Rsync to Sync Local and Remote Directories on a VPS</a></li>
<li><a href="https://indiareads/community/tutorials/understanding-digitalocean-droplet-backups">Understanding IndiaReads Droplet Backups</a></li>
</ul>

<h2 id="2-recovery-plans">2. Recovery Plans</h2>

<p>Recovery plans are a set of documented procedures to recover from potential failures or administration errors within your production environment. At minimum, you will want a recovery plan for each crippling scenario that you deem will inevitably occur, such as server hardware failure or accidental data deletion. For example, a very basic recovery plan for a server failure could consist of a list of the steps that you took to perform your initial server deployment, with extra procedures for restoring application data from backups. A better recovery plan might, in addition to good documentation, leverage deployment scripts and configuration management tools, such as Ansible, Chef, or Puppet, to help automate and quicken the recovery process.</p>

<p><strong>Required for Production?</strong> Yes. Although recovery plans don't exist as software in your server environment, they are a necessary component for a production setup. They enable you to utilize your backups effectively, and provide a blueprint for rebuilding your environment or rolling back to a desired state when the need arises.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/recovery_plans.png" alt="Example Recovery Plans" /></p>

<p>The diagram above is an overview of a recovery plan for a failed database server. In this case, the database server will be replaced by a new one with the same software installed, and the last good backup will be used to restore the server configuration and data. Lastly, the app server will be configured to use the new database server.</p>

<h4 id="considerations">Considerations</h4>

<ul>
<li><strong>Procedure Documentation:</strong> The set of documents that should be followed in a failure event. A good starting point is building a step-by-step document that you can follow to rebuild a failed server, then adding steps for restoring the various application data and configuration from backups</li>
<li><strong>Automation Tools:</strong> Scripts and configuration management software provide automation, which can improve deployment and recovery processes. While step-by-step guides are often adequate for simply recovering from a failure, they must be executed by a person and therefore are not as fast or consistent as an automated process</li>
<li><strong>Critical Components:</strong> The components that are necessary for your application to function properly. In the example above, both the application and database servers are critical components because if either fails, the application will become unavailable</li>
<li><strong>Single Points of Failure:</strong> Critical components that do not have an automatic failover mechanism are considered to be a single point of failure. You should attempt to eliminate single points of failure, to the best of your ability, to improve availability</li>
<li><strong>Revisions:</strong> Update your documentation as your deployment and recovery process improves</li>
</ul>

<h2 id="3-load-balancing">3. Load Balancing</h2>

<p>Load balancing can be added to a server environment to improve performance and availability by distributing the workload across multiple servers. If one of the servers that is load balanced fails, the other servers will handle the incoming traffic until the failed server becomes healthy again. In a cloud server environment, load balancing typically can be implemented by adding a load balancer server, that runs load balancer (reverse proxy) software, in front of multiple servers that run a particular component of an application.</p>

<p><strong>Required for Production?</strong> Not necessarily. Load balancing is not always required for a production environment but it can be an effective way to reduce the number of single points of failure in a system, if implemented correctly. It can also improve performance by adding more capacity through horizontal scaling.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/load_balancing.png" alt="Load Balancing" /></p>

<p>The diagram above adds an additional app server to share the load, and a load balancer to spread user requests across both app servers. This setup can help with performance, if the single app server was struggling to keep up with the traffic, and it can also help keep the application available if one of the application servers fails. However, it still has two single points of failure in the database server and the load balancer server itself.</p>

<h4 id="considerations">Considerations</h4>

<ul>
<li><strong>Load Balanceable Components:</strong> Not all components in an environment can be load balanced easily. Special consideration must be made for certain types of software such as databases or stateful applications</li>
<li><strong>Application Data Replication:</strong> If a load balanced application server stores application data locally, such as uploaded files, this data must be made available to the other application servers via methods such as replication or shared file systems. This is necessary to ensure that the application data will be available no matter which application server is chosen to serve a user request</li>
<li><strong>Performance Bottlenecks:</strong> If a load balancer does not have enough resources or is not configured properly, it can actually decrease the performance of your application</li>
<li><strong>Single Points of Failure:</strong> While a load balancing can be used to eliminate single points of failure, poorly planned load balancing can actually add more single points of failure. Load balancing is enhanced with the inclusion of a second load-balancer with a static IP in front of the pair that sends traffic to one or the other depending on availability.</li>
</ul>

<h4 id="related-tutorials">Related Tutorials</h4>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-haproxy-and-load-balancing-concepts">An Introduction to HAProxy and Load Balancing Concepts</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-implement-ssl-termination-with-haproxy-on-ubuntu-14-04">How To Implement SSL Termination With HAProxy on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-haproxy-and-load-balancing-concepts">How To Use HAProxy As A Layer 7 Load Balancer For WordPress and Nginx On Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/understanding-nginx-http-proxying-load-balancing-buffering-and-caching">Understanding Nginx HTTP Proxying, Load Balancing, Buffering, and Caching</a>
*<a href="https://indiareads/community/tutorials/how-to-use-floating-ips-on-digitalocean#how-to-implement-an-ha-setup">How To Use Floating IPs</a>.</li>
</ul>

<h2 id="4-monitoring">4. Monitoring</h2>

<p>Monitoring can support a server environment by tracking the status of services and the trends of your server resource utilization, thus providing great visibility into your environment. One of the biggest benefits of monitoring systems is that they can be configured to trigger an action, such as running a script or sending a notification, when a service or server goes down, or if a certain resource, such as CPU, memory, or storage, becomes over-utilized. These notifications enable you to react to any issues as soon as they occur, which can help minimize or prevent the downtime of your application.</p>

<p><strong>Required for Production?</strong> Not necessarily, but the need for monitoring increases as a production environment grows in size and complexity. It provides an easy way to keep track of your critical services and server resources. In turn, monitoring can improve the recoverability, and inform the planning and maintenance of your setup.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/monitoring.png" alt="Monitoring Example" /></p>

<p>The diagram above is an example of a monitoring system. Typically, the monitoring server will request status data from agent software running on the application and database servers, and each agent will respond with software and hardware status information. The administrator(s) of the system can then use the monitoring console to look at the overall state of the application, and drill down to more detailed information, as needed.</p>

<h4 id="considerations">Considerations</h4>

<ul>
<li><strong>Services to Monitor:</strong> The services and software that you will monitor. Minimally, you should monitor the state of all of the services that need to be in a healthy running state for your application to function properly</li>
<li><strong>Resources to Monitor:</strong> The resources that you will monitor. Examples of resources include CPU, memory, storage, and network utilization, and the state of server as a whole</li>
<li><strong>Data Retention:</strong> The period of time that you retain monitoring data before discarding it. This, along with your choice of items to monitor, will affect the amount of disk space that your monitoring system will require</li>
<li><strong>Problem Detection Rules:</strong> The thresholds and rules that determine whether a service or resource is in a OK state. For example, a service or server may be considered to be healthy if it is running and serving requests, whereas a resource, such as storage, might trigger a warning if its utilization reaches a certain threshold for a certain amount of time</li>
<li><strong>Notification Rules:</strong> The thresholds and rules that determine if a notification should be sent. While notifications are important, it is equally important to tune your notification rules so that you don't receive too many; an inbox full of warnings and alerts will often go ignored, making them almost as useless as no notifications at all</li>
</ul>

<h4 id="related-tutorials">Related Tutorials</h4>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-nagios-4-and-monitor-your-servers-on-ubuntu-14-04">How To Install Nagios 4 and Monitor Your Servers on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-icinga-to-monitor-your-servers-and-services-on-ubuntu-14-04">How To Use Icinga To Monitor Your Servers and Services On Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-zabbix-on-ubuntu-configure-it-to-monitor-multiple-vps-servers">How To Install Zabbix on Ubuntu & Configure it to Monitor Multiple VPS Servers</a></li>
<li><a href="https://indiareads/community/tutorial_series/monitoring-and-managing-your-network-with-snmp">Monitoring and Managing your Network with SNMP</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-configure-sensu-monitoring-rabbitmq-and-redis-on-ubuntu-14-04">How To Configure Sensu Monitoring, RabbitMQ, and Redis on Ubuntu 14.04</a></li>
</ul>

<h2 id="5-centralized-logging">5. Centralized Logging</h2>

<p>Centralized logging can support a server environment by providing an easy way to view and search your logs, which are normally stored locally on individual servers across your entire environment, in a single place. Aside from the convenience of not having to log in to individual servers to read logs, centralized logging also allows you to easily identify issues that span multiple servers by correlating their logs and metrics during a specific time frame. It also grants more flexibility in terms of log retention because local logs can be off-loaded from application servers to a centralized log server that has its own, independent storage.</p>

<p><strong>Required for Production?</strong> No, but like monitoring, centralized logging can provide invaluable insight into your server environment as it grows in size and complexity. In addition to being more convenient than traditional logging, it enables you to rapidly audit your server logs with greater visibility.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/centralized_logging.png" alt="Centralized Logging" /></p>

<p>The diagram above is a simplified example of a centralized logging system. A log shipping agent is installed on each server, and configured to send important app and database logs to the centralized logging server. The administrator(s) of the system can then view, filter, and search all of the important logs from a single console.</p>

<h4 id="considerations">Considerations</h4>

<ul>
<li><strong>Logs to Gather:</strong> The particular logs that you will ship from your servers to the centralized logging server. You should gather the important logs from all of your servers</li>
<li><strong>Data Retention:</strong> The period of time that you retain logs before discarding them. This, along with your choice of logs to gather, will affect the amount of disk space that your centralized logging system will require</li>
<li><strong>Log Filters:</strong> The filters that parse plain logs into structured log data. Filtering logs will improve your ability to query, analyze, and graph the data in meaningful ways</li>
<li><strong>Server Clocks:</strong> Ensure that the clocks of your servers are synchronized and using set to the same time zone, so your log timeline is accurate across your entire environment</li>
</ul>

<h4 id="related-tutorials">Related Tutorials</h4>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-elasticsearch-logstash-and-kibana-4-on-ubuntu-14-04">How To Install Elasticsearch, Logstash, and Kibana 4 on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-elk-stack-one-click-application">How To Use the IndiaReads ELK Stack One-Click Application</a></li>
<li><a href="https://indiareads/community/tutorial_series/introduction-to-tracking-statistics-on-servers">Introduction to Tracking Statistics on Servers</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-graylog2-and-centralize-logs-on-ubuntu-14-04">How To Install Graylog2 And Centralize Logs On Ubuntu 14.04</a></li>
</ul>

<h2 id="conclusion">Conclusion</h2>

<p>When you put all the components together, your production environment might look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/production.png" alt="Production" /></p>

<p>Now that you are familiar with components that can be used to support and improve a production server setup, you should consider how you can integrate them your own server environments. Of course, we didn't cover every possibility, but this should give you an idea of where to get started. Remember to design and implement your server environment based on a balance of your available resources and your own production goals.</p>

<p>If you are interested in setting up an environment like the one above, check out this tutorial: <a href="https://indiareads/community/tutorials/building-for-production-web-applications-overview">Building for Production: Web Applications</a>.</p>

    