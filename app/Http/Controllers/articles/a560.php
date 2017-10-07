<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Resizing your servers can be an effective way of increasing their capacity, by allowing them to utilize more memory (RAM), CPU, and disk storage. The ability to resize a server, also known as vertical scaling, can be useful in a variety of situations that prompt the need for a more powerful server, such as if your concurrent user base increases or if you need to store more data. In this tutorial, we will show you how to resize your server, also known as a droplet, on IndiaReads via the Control Panel and API.</p>

<p>Before diving right into resizing your droplets, let's take a look at the different resize options that are available for droplets.</p>

<h2 id="permanent-vs-flexible-resize">Permanent vs Flexible Resize</h2>

<p>There are two distinct options when performing a resize operation on IndiaReads droplets, <strong>permanent</strong> and <strong>flexible</strong>, and it is important to know the difference between them; a permanent resize increases a droplet's RAM, CPU, and SSD disk, while the flexible resize only increases a droplet's RAM and CPU (not the storage). While this seems to imply that a permanent resize is always better, because it upgrades all of a server's resources, there are some situations where a flexible resize may be more appropriate.</p>

<p>With a permanent resize, you must specify a new droplet size that has more storage than your droplet currently has. For example, if your droplet currently has a 30 GB of SSD, you can permanently resize it to any droplet size that grants more than 30 GB of SSD. To ensure the integrity of your server's data, disk sizes are not allowed to be decreased through a resize operation. Thus, a droplet that has been permanently resized cannot be resized to a smaller size at a later time.</p>

<p>A flexible resize, which does not modify the storage capacity of a droplet, allows you to choose any new droplet size that offers at least as much storage capacity as the droplet currently has. If you don't need additional disk space, a flexible resize allows you to boost the CPU and memory of your server while giving you the option to resize the droplet back to its original size at a later date. You also have the option to perform a permanent resize to "reclaim" the disk capacity that was left unallocated, following a flexible resize.</p>

<p>A good rule of thumb is to use the flexible resize if you need more CPU and memory but not additional storage, and you want the ability to rollback to a cheaper droplet size in the future. If you need more CPU, memory, and storage, use the permanent resize.</p>

<p>Permanent resize is available in all regions except AMS1. Flexible resize is available in all regions.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before resizing a droplet, it must be powered off. The best way to do this is to log into your droplet, and issue a <code>sudo poweroff</code> command. You may also perform a "hard" power off through the IndiaReads Control Panel or API.</p>

<p>Another important thing to note is that certain droplets in AMS1 may not be resizable. This can occur if the physical hardware the powers a droplet does not have enough resources to resize to. If you run into this situation, you may attempt to use snapshots to resize a droplet by following this tutorial: <a href="https://indiareads/community/tutorials/how-to-resize-droplets-using-snapshots">How To Resize Droplets Using Snapshots</a></p>

<h3 id="estimated-downtime">Estimated Downtime</h3>

<p>The estimated maximum downtime window for the resize process for a Droplet is: up to one minute per GB of used storage. For example, if your Droplet is using 20GB storage, the entire resize should take less than 20 minutes after it is initiated. The actual time of the process is typically very quick.</p>

<p>Now that we have the prerequisites out of the way, the next two sections will cover resizing your droplet via the IndiaReads Control Panel and API.</p>

<h2 id="resize-via-control-panel">Resize via Control Panel</h2>

<p>If you haven't already done so, log in to the <a href="https://cloud.digitalocean.com/droplets">IndiaReads Control Panel</a>.</p>

<p>In the <strong>Droplets</strong> page, click on the name of the droplet you want to resize.</p>

<p>Next, click the <strong>Resize</strong> option on the left navigation section:</p>

<p><img src="https://assets.digitalocean.com/articles/droplet/resize/resizeoption.png" alt="Resize Button" /></p>

<p>If you haven't done so already, power off your droplet. The best way to do this is to log in to the server and issue a <code>sudo poweroff</code> command.</p>

<p>At this point, you will be presented with the resize control panel:</p>

<p><img src="https://assets.digitalocean.com/articles/droplet/resize/resizechoice.png" alt="Resize" /></p>

<p><strong>Note:</strong> Droplets in AMS1 can only be resized with the flexible option.</p>

<p>Select either <strong>Permanent</strong> or <strong>Flexible</strong> resizing, based on your needs and the considerations discussed earlier. </p>

<p>Next, select size that you would like to resize the droplet to.</p>

<p>When you are satisfied with your resize selection, click the <strong>Resize</strong> button. This will initiate the resize event.</p>

<p>Once the resize event has been completed, your droplet will be in a powered off state. Click the <strong>Power On</strong> option on the left-hand navigation section, and then  use the <strong>Power On</strong> button to get it back online:</p>

<p><img src="https://assets.digitalocean.com/articles/droplet/resize/power_on.png" alt="Power On" /></p>

<p>Congratulations! You have resized your droplet!</p>

<p>Read on if you would like to learn how to perform a droplet resize using the API.</p>

<h2 id="resize-via-api">Resize via API</h2>

<p>If you prefer to resize your droplets via the IndiaReads API, you must know the ID of the droplet you want to resize, and the size you want to resize to. Of course, you will also need a IndiaReads API token with read and write access.</p>

<p>Once you have the droplet ID, you must use it with the droplet actions API endpoint, and set the <code>type</code> attribute to "resize". You must also specify the <code>size</code> attribute to an acceptable droplet size.</p>

<p>For example, if you want to make the API request using curl, you could use this command (substitute your API token, droplet ID, and desired size) to perform a <strong>flexible resize</strong>:</p>
<pre class="code-pre "><code langs="">curl -X POST -H 'Content-Type: application/json' -H 'Authorization: Bearer <span class="highlight">b7d03a6947b217efb6f3ec3bd3504582</span>' -d '{"type":"resize","size":"<span class="highlight">4gb</span>"}' "https://api.digitalocean.com/v2/droplets/<span class="highlight">droplet_id</span>/actions"
</code></pre>
<p>If you want to perform a <strong>permanent resize</strong>, with the same options as the last example, set a <code>disk</code> attribute to <code>true</code>, like so:</p>
<pre class="code-pre "><code langs="">curl -X POST -H 'Content-Type: application/json' -H 'Authorization: Bearer <span class="highlight">b7d03a6947b217efb6f3ec3bd3504582</span>' -d '{"type":"resize","size":"<span class="highlight">4gb</span>","disk":true}' "https://api.digitalocean.com/v2/droplets/<span class="highlight">droplet_id</span>/actions"
</code></pre>
<p>This will initiate the resize process for the specified droplet.</p>

<p>For full documentation about resizing a droplet via the API, visit the <a href="https://developers.digitalocean.com/documentation/v2/#resize-a-droplet">IndiaReads API documentation</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>If you haven't done so, be sure to power on any droplets that you have resized. Also be sure to check that all your services are running as expected.</p>

<p>If you have any issues, please leave them in the comments below!</p>

    