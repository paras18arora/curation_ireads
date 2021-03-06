<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When migrating from one server to another, it is often desirable to migrate the iptables firewall rules as part of the process. This tutorial will show you how to easily copy your active iptables rule set from one server to another.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial requires two servers. We will refer to the source server, which has the existing iptables rules, as <strong>Server A</strong>. The destination server, where the rules will be migrated to, will be referred to as <strong>Server B</strong>.</p>

<p>You will also need to have superuser, or <code>sudo</code>, access to both servers.</p>

<h2 id="view-existing-iptables-rules">View Existing Iptables Rules</h2>

<p>Before migrating your iptables rules, let's see what they are set to. You can do that with this command on <strong>Server A</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -S
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Example output:">Example output:</div>-P INPUT ACCEPT
-P FORWARD ACCEPT
-P OUTPUT ACCEPT
-A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
-A INPUT -p tcp -m tcp --dport 22 -j ACCEPT
-A INPUT -p tcp -m tcp --dport 80 -j ACCEPT
-A INPUT -s 15.15.15.51/32 -j DROP
</code></pre>
<p>The example rules above will be used to demonstrate the firewall migration process.</p>

<h2 id="export-iptables-rules">Export Iptables Rules</h2>

<p>The <code>iptables-save</code> command writes the current iptables rules to <code>stdout</code> (standard out). This gives us an easy way to export the firewall rules to file, by redirecting <code>stdout</code> to a file.</p>

<p>On the <strong>Server A</strong>, the one with the iptables rules that you want to migrate, use the <code>iptables-save</code> to export the current rules to a file named "iptables-export" like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">sudo iptables-save > <span class="highlight">iptables-export</span>
</li></ul></code></pre>
<p>This will create the <code>iptables-export</code> file, in your home directory. This file can be used on a different server to load the firewall rules into iptables.</p>

<h3 id="view-file-contents-optional">View File Contents (Optional)</h3>

<p>Let's take a quick look at the file's contents. We'll use the <code>cat</code> command to print it out to the terminal:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat iptables-export
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="iptables-export contents:">iptables-export contents:</div># Generated by iptables-save v1.4.21 on Tue Sep  1 17:32:29 2015
*filter
:INPUT ACCEPT [135:10578]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [8364:1557108]
-A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
-A INPUT -p tcp -m tcp --dport 22 -j ACCEPT
-A INPUT -p tcp -m tcp --dport 80 -j ACCEPT
-A INPUT -s 15.15.15.51/32 -j DROP
COMMIT
# Completed on Tue Sep  1 17:32:29 2015
</code></pre>
<p>As you can see, the file contains the configuration of the active iptables rules. Now we're ready to copy this file to our destination server, <strong>Server B</strong>.</p>

<h3 id="copy-exported-rules-to-destination-server">Copy Exported Rules to Destination Server</h3>

<p>We need to copy the rules file to our destination server, <strong>Server B</strong>. The easiest way to do this is to use <code>scp</code> or to  copy and paste the file contents to a new file on <strong>Server B</strong>. We will demonstrate how to use <code>scp</code> to copy the file over the network to the <code>/tmp</code> directory. </p>

<p>On <strong>Server A</strong>, run this <code>scp</code> command. Be sure to substitute the highlighted parts with your server's login and IP address:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">scp iptables-export <span class="highlight">user</span>@<span class="highlight">server_b_ip_address</span>:/tmp
</li></ul></code></pre>
<p>After providing proper authentication, the file will be copied to the <code>/tmp</code> directory on Server B. Note that the contents of <code>/tmp</code> are deleted upon a reboot—feel free to place it somewhere else if you want to preserve it.</p>

<h2 id="import-iptables-rules">Import Iptables Rules</h2>

<p>With the exported rules on the destination server, you can load them into iptables. However, depending on your situation, you may want update the rules in the file with new IP addresses and ranges, and perhaps update interface names. If you want to change the rules before loading them, be sure to edit the <code>/tmp/iptables-export</code> file now.</p>

<p>Once you are ready to load the rules from the <code>iptables-export</code> file into iptables, let's use the <code>iptables-restore</code> command to do so.</p>

<p>On <strong>Server B</strong>, the destination server, run this command to load the firewall rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables-restore < /tmp/iptables-export
</li></ul></code></pre>
<p>This will load the rules into iptables. You can verify this with the <code>sudo iptables -S</code> command.</p>

<h2 id="save-rules">Save Rules</h2>

<p>Iptables rules are ephemeral, so special care must be taken for them to persist after a reboot—it is likely that you will want to perform this step on <strong>Server B</strong>. We will show you how to save the rules on both Ubuntu and CentOS.</p>

<h3 id="ubuntu">Ubuntu</h3>

<p>On Ubuntu, the easiest way to save iptables rules, so they will survive a reboot, is to use the iptables-persistent package. Install it with apt-get like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install iptables-persistent
</li></ul></code></pre>
<p>During the installation, you will asked if you want to save your current firewall rules. Response <code>yes</code>, if you want to save the current rule set.</p>

<p>If you update your firewall rules in the future, and want to save the changes, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo invoke-rc.d iptables-persistent save
</li></ul></code></pre>
<h3 id="centos-6-and-older">CentOS 6 and Older</h3>

<p>On CentOS 6 and older—CentOS 7 uses FirewallD by default—you can use the iptables init script to save your iptables rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service iptables save
</li></ul></code></pre>
<p>This will save your current iptables rules to the <code>/etc/sysconfig/iptables</code> file, which gets loaded by iptables upon boot.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! Your firewall rules have been migrated from your original server to your new one.</p>

    