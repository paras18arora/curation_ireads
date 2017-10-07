<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IndiaReads's Private Networking feature gives your Droplets a network interface which is only accessible to other Droplets in the same datacenter.  It doesn't, however, restrict traffic from Droplets you don't control.  This can leave you with a greater <a href="https://en.wikipedia.org/wiki/Attack_surface">attack surface</a>, particularly if you have services listening on the interface.</p>

<p>The <code>droplan</code> utility can help secure private network interfaces on a Droplet by adding <strong>iptables</strong> firewall rules that only allow traffic from your other Droplets in the same datacenter.  By installing and running the utility on each Droplet, you can ensure that your systems will only accept local traffic from one another.</p>

<p>This guide will cover installing <code>droplan</code> on an individual Droplet, scheduling a <code>cron</code> job to run it on a regular basis, and ensuring that firewall rules persist when the Droplet is rebooted or loses power.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This guide assumes that you have two or more Linux Droplets in the same region, each configured with a non-root user with <code>sudo</code> privileges for administrative tasks.  Specifically, it provides instructions for recent Debian, Ubuntu, and CentOS releases.  On CentOS systems, it will disable <code>firewalld</code>, so you should be aware that it may override any existing firewall configuration.</p>

<h2 id="retrieving-a-read-only-personal-access-token">Retrieving a Read-only Personal Access Token</h2>

<p>The <code>droplan</code> utility In order to ask the API for a list of your Droplets, the <code>droplan</code> command needs access to a <strong>personal access token</strong> with read scope.  You can retrieve a token by accessing the IndiaReads Control Panel, clicking on <strong>API</strong> in the top menu, and clicking the <strong>Generate New Token</strong> button.  Enter a descriptive name for the new token, in the <strong>Token Name</strong> field, such as "droplan readonly", and uncheck the <strong>Write (Optional)</strong> box:</p>

<p><img src="https://assets.digitalocean.com/articles/droplan/enter_name_small.png" alt="Generate New Token" /></p>

<p>Click <strong>Generate Token</strong>, and copy the resulting token to your local machine:</p>

<p><img src="https://assets.digitalocean.com/articles/droplan/display_token_small.png" alt="Personal Access Tokens" /></p>

<p><span class="note"><strong>Note</strong>: Make sure you keep a copy of the token, or you'll have to generate a new one.  It can't be retrieved from the Control Panel after the first time it's shown.<br /></span></p>

<p>For more details on this process, and the basics of API usage, see <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">How To Use the IndiaReads API v2</a>.</p>

<h2 id="installing-droplan">Installing Droplan</h2>

<h3 id="installing-debian-and-ubuntu-prerequisites">Installing Debian and Ubuntu Prerequisites</h3>

<p>If you are on Debian or a Debian-derived distribution such as Ubuntu, install the <code>unzip</code> package using <code>apt-get</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install unzip iptables-persistent
</li></ul></code></pre>
<p>We'll need <code>iptables-persistent</code> in a moment when we configure persistent firewall rules.  You'll likely be asked by the installer whether you want to save current firewall rules at the time of installation.  It shouldn't do any harm if you say yes.</p>

<h3 id="installing-centos-prerequisites">Installing CentOS Prerequisites</h3>

<p>If you are using CentOS 7, install the <code>unzip</code> and <code>iptables-services</code> package using <code>yum</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install unzip iptables-services
</li></ul></code></pre>
<p>We'll need <code>iptables-services</code> in a moment when we configure persistent firewall rules.</p>

<h3 id="retrieving-and-extracting-archive">Retrieving and Extracting Archive</h3>

<p>Visit the <a href="https://github.com/tam7t/droplan/releases">releases page</a> on the <code>droplan</code> GitHub project, and find a URL for the latest release which supports your architecture.  Copy the URL, log in to one of your Droplets, and retrieve the file with <code>wget</code> or <code>curl</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">wget https://github.com/tam7t/droplan/releases/download/<span class="highlight">v1.0.0</span>/<span class="highlight">droplan_1.0.0_linux_amd64.zip</span>
</li></ul></code></pre>
<p>Now, use the <code>unzip</code> command to extract the <code>droplan</code> binary from the release archive:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">unzip <span class="highlight">droplan_1.0.0_linux_amd64.zip</span>
</li></ul></code></pre>
<p>Create a directory in <code>/opt</code> for <code>droplan</code>, and move the binary there:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /opt/droplan
</li><li class="line" prefix="$">sudo mv ./droplan /opt/droplan/
</li></ul></code></pre>
<p>The <code>/opt</code> directory is a standard location for software installed from sources other than a distribution's official package repositories.</p>

<h2 id="creating-iptables-rules">Creating Iptables Rules</h2>

<p>With the <code>droplan</code> binary in place, you can use it to create rules.  Run the command under <code>sudo</code>, setting the <code>DO_KEY</code> environment variable to your token:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo DO_KEY=<span class="highlight">personal_access_token</span> /opt/droplan/droplan
</li></ul></code></pre>
<p>Now, check your iptables rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L
</li></ul></code></pre>
<p>Assuming that you have two other Droplets in the same region, you should see something like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Chain INPUT (policy ACCEPT)
target     prot opt source               destination
droplan-peers  all  --  anywhere             anywhere
DROP       all  --  anywhere             anywhere

Chain FORWARD (policy ACCEPT)
target     prot opt source               destination

Chain OUTPUT (policy ACCEPT)
target     prot opt source               destination

Chain droplan-peers (1 references)
target     prot opt source               destination
ACCEPT     all  --  <span class="highlight">droplet_ip1</span>       anywhere
ACCEPT     all  --  <span class="highlight">droplet_ip2</span>        anywhere
</code></pre>
<p>To confirm that these rules are applied only to <strong>eth1</strong>, you can add the <code>-v</code> option for more verbose output, which will include interfaces:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L -v
</li></ul></code></pre>
<h2 id="persisting-iptables-rules">Persisting Iptables Rules</h2>

<p>For now, all of your other Droplets in the same region can connect to the current system, while traffic from systems you don't control is blocked.  If the system reboots, however, the iptables rules will disappear.  It's also likely that you will create new Droplets (or delete the existing ones) at some point in the future.  In order to deal with these problems, we'll make sure that rules persist on restart, and schedule <code>droplan</code> to run on a regular basis and make any necessary changes to the firewall.</p>

<h3 id="persisting-rules-on-debian-or-ubuntu">Persisting Rules on Debian or Ubuntu</h3>

<p>Firewall rules are kept in <code>/etc/iptables/rules.v4</code> (and <code>/etc/iptables/rules.v6</code> for ipv6 rules).  You can generate a new version of this file using the <code>iptables-save</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables-save | sudo tee /etc/iptables/rules.v4
</li></ul></code></pre>
<h3 id="persisting-rules-on-centos-7">Persisting Rules on CentOS 7</h3>

<p>By default, CentOS 7 uses the firewalld service in place of iptables.  Since we already installed the <code>iptables-services</code> package above, we can use <code>systemctl</code> to stop this service and mask it, ensuring that it won't be restarted:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl stop firewalld
</li><li class="line" prefix="$">sudo systemctl mask firewalld
</li></ul></code></pre>
<p>Now enable the <code>iptables</code> service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">systemctl enable iptables
</li></ul></code></pre>
<p>With the <code>iptables</code> service in place, save the current firewall rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service iptables save
</li></ul></code></pre>
<h3 id="testing-rule-persistence">Testing Rule Persistence</h3>

<p>You may wish to reboot the system, reconnect, and check that the rules have persisted.  First, reboot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo reboot
</li></ul></code></pre>
<p>Now, reconnect to your Droplet (this will take a few seconds), and check the rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L
</li></ul></code></pre>
<h3 id="scheduling-a-cron-job-to-update-iptables-rules">Scheduling a Cron Job to Update Iptables Rules</h3>

<p>As a final step, we'll make sure that <code>droplan</code> runs periodically so that it catches changes in your collection of Droplets.</p>

<p>Begin by creating a new script called <code>/opt/droplan/refresh.sh</code>, using <code>nano</code> (or your editor of choice):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /opt/droplan/refresh.sh
</li></ul></code></pre>
<p>Paste the following, uncommenting the appropriate line for your distribution by deleting the leading <code><span class="highlight">#</span></code>:</p>
<div class="code-label " title="/opt/droplan/refresh.sh">/opt/droplan/refresh.sh</div><pre class="code-pre "><code langs="">#!/usr/bin/env bash

/opt/droplan/droplan

# Uncomment for Centos:
<span class="highlight"># service iptables save</span>

# Uncomment for Debian or Ubuntu:
<span class="highlight"># iptables-save > /etc/iptables/rules.v4</span>
</code></pre>
<p>Exit and save the file, then mark it executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod +x /opt/droplan/refresh.sh
</li></ul></code></pre>
<p>Next, create a new file at <code>/etc/cron.d/droplan</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/cron.d/droplan
</li></ul></code></pre>
<p>Add the following line to the file in order to run the script as <strong>root</strong> every 5 minutes:</p>
<div class="code-label " title="crontab">crontab</div><pre class="code-pre "><code langs="">*/5 * * * * root PATH=/sbin:/usr/bin:/bin DO_KEY=<span class="highlight">personal_access_token</span> /opt/droplan/refresh.sh > /var/log/droplan.log 2>&1
</code></pre>
<p>This will run the <code>refresh.sh</code> script once every 5 minutes, as indicated by <code>*/5</code> in the first field, and log its most recent output to <code>/var/log/droplan.log</code>.</p>

<p>Exit and save the file.  You can now use the <code>watch</code> command, which displays the output of another command every few seconds, to make sure that the script runs successfully:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo watch cat /var/log/droplan.log
</li></ul></code></pre>
<p>Once the script runs, you should see output something like the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Sample CentOS Output">Sample CentOS Output</div>Every 2.0s: cat droplan.log                      Fri Mar 25 01:14:45 2016

2016/03/25 01:14:02 Added 2 peers to droplan-peers
iptables: Saving firewall rules to /etc/sysconfig/iptables: [  OK  ]
</code></pre>
<p>On Debian-derived systems, <code>systemctl iptables save</code> won't display any output.</p>

<p>Press <strong>Ctrl-C</strong> to exit <code>watch</code>.</p>

<p><span class="note"><strong>Note</strong>: Since the API is rate-limited, you may need to tune the frequency of updates if you have many Droplets.  You can <a href="https://indiareads/community/tutorials/how-to-schedule-routine-tasks-with-cron-and-anacron-on-a-vps">read more about <code>cron</code></a> or <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">the API itself</a>.<br /></span></p>

<h2 id="conclusion-and-next-steps">Conclusion and Next Steps</h2>

<p>Now that you've configured the firewall on a single Droplet, you'll want to repeat this process with the rest of your infrastructure.  For more than a handful of Droplets, it would probably be easiest to automate this process.  If you're using Hashicorp's <a href="https://www.terraform.io/">Terraform</a> for provisioning systems, you can find <a href="https://github.com/tam7t/droplan/tree/master/examples">example templates on the Droplan GitHub project</a>.  For a broad overview of automating tasks like this one, see <a href="https://indiareads/community/tutorials/an-introduction-to-configuration-management">An Introduction to Configuration Management</a>.</p>

<p>For more detail on firewalls, see <a href="https://indiareads/community/tutorials/what-is-a-firewall-and-how-does-it-work">What is a Firewall and How Does It Work?</a></p>

    