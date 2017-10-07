<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/WebApplication.recovery-twitter.png?1436557982/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Now that we have our example application setup, we should devise a recovery plan. A recovery plan is a set of documented procedures to recover from potential failures or administration errors within your server setup. Creating a recovery plan will also help you identify the essential components and data of your application server setup.</p>

<p>A very basic recovery plan for a server failure could consist of the list of steps that you took to perform your initial server deployment, with extra procedures for restoring application data from backups. A better recovery plan might, in addition to good documentation, leverage deployment scripts and configuration management tools, such as Ansible, Chef, or Puppet, to help automate and quicken the recovery process.</p>

<p><img src="https://assets.digitalocean.com/articles/architecture/production/recovery_plans.png" alt="Recovery Plan Diagram" /></p>

<p>In this part of the tutorial, we will demonstrate how to create a basic recovery plan for the example WordPress application that we set up. Your needs will probably differ, but this should help you get started with devising a recovery plan of your own.</p>

<h2 id="recovery-plan-requirements">Recovery Plan Requirements</h2>

<p>Our basic requirements are that we are able to recover from the loss of any server in the setup, and restore the application functionality and data (up to a reasonable point in time). To fulfill this goal, we will create an inventory of each server, determine which data needs to be backed up, and write a recovery plan based on our available assets. Of course, if any of these recovery plans are executed, the application should be tested to verify that it was restored properly.</p>

<p>We will come up with a recovery plan for each type of server that our application consists of:</p>

<ul>
<li>Database Server</li>
<li>Application Servers</li>
<li>Load Balancer Server</li>
</ul>

<p>Let's get started with the database server.</p>

<h2 id="database-server">Database Server</h2>

<p>By retracing our steps (and looking at the preceding tutorial), we know that our database server was created by following these steps:</p>

<ol>
<li>Install MySQL</li>
<li>Configure MySQL</li>
<li>Restart MySQL</li>
<li>Create database and users</li>
</ol>

<h3 id="database-server-recovery-plan">Database Server Recovery Plan</h3>

<p>By looking at how we created the database server, we know that it can be recreated from scratch aside from the contents of the database itself (created in step 4). In our WordPress example, most of the application data (i.e. blog posts) is stored in the database. This means that we must maintain backups of the database if we want to be able to recover the database server. We will also back up the MySQL configuration file since it was modified slightly.</p>

<p>Based on our inventory of the database server, here is an outline of our database server recovery plan:</p>

<blockquote>
<p>Required Backups:</p>

<ul>
<li>MySQL database</li>
<li>MySQL configuration</li>
</ul>

<p>Recovery Steps:</p>

<ol>
<li>Install MySQL</li>
<li>Restore MySQL configuration file, and (if it changes) update listening IP address</li>
<li>Restore database</li>
<li>Restart MySQL</li>
</ol>
</blockquote>

<p>Now that we have an outline of the database server recovery plan, we should work out the details of the recovery steps and ensure that we maintain the required backups. We will leave it as an exercise to the reader to detail the recovery steps, as they will differ depending on the actual setup. In the example case, we can use the application deployment tutorial as the documentation to base our recovery steps on.</p>

<h2 id="application-servers">Application Servers</h2>

<p>By retracing our steps (and looking at the preceding tutorial), we know that the application servers were created by following these steps:</p>

<ol>
<li>Install and configure Apache and PHP</li>
<li>Download and configure application (WordPress)</li>
<li>Copy application files to DocumentRoot</li>
<li>Replicate application files across all application servers</li>
</ol>

<h3 id="application-server-recovery-plan">Application Server Recovery Plan</h3>

<p>By looking at the set up steps, we know that our application server can be recreated from scratch aside from the application files. In our WordPress example, the application files include the WordPress configuration files (which includes the database connection information), installed WordPress plugins, and file uploads. This means that we must maintain backups of the application files if we want to be able to recover an application server.</p>

<p>Because the application files are set up to be replicated across multiple application servers, we only need to restore the data from backups if all of the application servers fail or if the data is corrupted somehow. If at least one application server is running fine, with the correct application files, setting up file replication again will restore the proper files to the new application server.</p>

<p>Based on our inventory of the application servers, let's make an outline of our application server recovery plan:</p>

<blockquote>
<p>Required Backups:</p>

<ul>
<li>Application files (<code>/var/www/html/</code>, in our example)</li>
</ul>

<p>Recovery Steps:</p>

<ol>
<li>Install and configure Apache and PHP</li>
<li>Replicate application files from working application server</li>
<li>If application files can't be replicated (all application servers are dead), restore from backups</li>
</ol>
</blockquote>

<p>Now that we have an outline of the application server recovery plan, we should work out the details of the recovery steps and ensure that we maintain the required backups. We will leave it as an exercise to the reader to detail the recovery steps, as they will differ depending on the actual setup. In the example case, we can use the application deployment tutorial as the documentation to base our recovery steps on.</p>

<h2 id="load-balancer-server">Load Balancer Server</h2>

<p>By retracing our steps (and looking at the preceding tutorial), we know that the load balancer server was created by following these steps:</p>

<ol>
<li>Obtained SSL Certificate and related files</li>
<li>Installed HAProxy</li>
<li>Configured HAProxy</li>
<li>Restarted HAProxy</li>
</ol>

<h3 id="load-balancer-server-recovery-plan">Load Balancer Server Recovery Plan</h3>

<p>By looking at this inventory, we know that our load balancer server can be recreated from scratch aside from the files related to the SSL certificate. This means that we must maintain backups of the SSL certificate files if we want to be able to recover the load balancer server. We will also include the HAProxy configuration file in our backups.</p>

<p>Based on our inventory of the load balancer server, let's make an outline of our load balancer server recovery plan:</p>

<blockquote>
<p>Required Backups:</p>

<ul>
<li>SSL Certificate (PEM) and related files</li>
<li>HAProxy configuration file</li>
</ul>

<p>Recovery Steps:</p>

<ol>
<li>Restore SSL Certificate files</li>
<li>Install HAProxy</li>
<li>Restore HAProxy configuration file</li>
<li>Restart HAProxy</li>
</ol>
</blockquote>

<p>Now that we have an outline of the load balancer server recovery plan, we should work out the details of the recovery steps and ensure that we maintain the required backups. We will leave it as an exercise to the reader to detail the recovery steps, as they will differ depending on the actual setup. In the example case, we can use the application deployment tutorial as the documentation to base our recovery steps on.</p>

<h2 id="other-considerations">Other Considerations</h2>

<p>If the recovery of one of the components requires you to reconfigure any other components, e.g. the database server IP address changes, make sure to include the appropriate steps in your recovery plans.</p>

<p>You will also want to write up recovery plans for all of the other components that exist in your setup, such as your DNS, and for all the components that you will add in the future, such as your backups servers, monitoring, and logging. As your server setup evolves, you should iterate on your existing recovery plans.</p>

<p>We also haven't covered how to create and restore backups yet, so we will have to fill those details in later. We will cover backups in the next part of this tutorial.</p>

<h2 id="conclusion">Conclusion</h2>

<p>After preparing the recovery plans for your various servers, you should keep this information somewhere that is accessible to whoever needs to be able to perform a recovery, completely separate from your server setup.</p>

<p>Continue to the next tutorial to start setting up the backups that are required to support your new recovery plan: <a href="https://indiareads/community/tutorials/building-for-production-web-applications-backups">Building for Production: Web Applications — Backups</a>.</p>

    