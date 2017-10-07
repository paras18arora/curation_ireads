<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Like most other Linux distributions, CentOS 7 uses the <code>netfilter</code> framework inside the Linux kernel in order to access packets that flow through the network stack.  This provides the necessary interface to inspect and manipulate packets in order to implement a firewall system.</p>

<p>Most distributions use the <code>iptables</code> firewall, which uses the <code>netfilter</code> hooks to enforce firewall rules.  CentOS 7 comes with an alternative service called <code>firewalld</code> which fulfills this same purpose.</p>

<p>While <code>firewalld</code> is a very capable firewall solution with great features, it may be easier for some users to stick with <code>iptables</code> if they are comfortable with its syntax and happy with its behavior and performance.  The <code>iptables</code> <em>command</em> is actually used by <code>firewalld</code> itself, but the <code>iptables</code> <em>service</em> is not installed on CentOS 7 by default.  In this guide, we'll demonstrate how to install the <code>iptables</code> service on CentOS 7 and migrate your firewall from <code>firewalld</code> to <code>iptables</code> (check out <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-firewalld-on-centos-7">this guide</a> if you'd like to learn how to use FirewallD instead).</p>

<h2 id="save-your-current-firewall-rules-optional">Save your Current Firewall Rules (Optional)</h2>

<p>Before making the switch to <code>iptables</code> as your server's firewall solution, it is a good idea to save the current rules that <code>firewalld</code> is enforcing.  We mentioned above that the <code>firewalld</code> daemon actually leverages the <code>iptables</code> command to speak to the <code>netfilter</code> kernel hooks.  Because of this, we can dump the current rules using the <code>iptables</code> command.</p>

<p>Dump the current set of rules to standard output and to a file in your home directory called <code>firewalld_iptables_rules</code> by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -S | tee ~/firewalld_iptables_rules
</li></ul></code></pre>
<p>Do the same with <code>ip6tables</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ip6tables -S | tee ~/firewalld_ip6tables_rules
</li></ul></code></pre>
<p>Depending on the <code>firewalld</code> zones that were active, the services that were enabled, and the rules that were passed from <code>firewall-cmd</code> directly to <code>iptables</code>, the dumped rule set might be quite extensive.</p>

<p>The <code>firewalld</code> service implements its firewall policies using normal <code>iptables</code> rules.It accomplishes this by building a management framework using <code>iptables</code> chains.  Most of the rules you are likely to see will be used to create these management chains and direct the flow of traffic in and out of these structures.</p>

<p>The firewall rules you end up moving over to your <code>iptables</code> service will not need to recreate the management framework that <code>firewalld</code> relies on.  Because of this, the rule set you end up implementing will likely be much simpler.  We are saving the entire set here in order to keep as much raw data intact as possible.</p>

<p>You can see some of the more essential lines to get an idea of the policy you'll have to recreate by typing something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">grep 'ACCEPT\|DROP\|QUEUE\|RETURN\|REJECT\|LOG' ~/firewalld_iptables_rules
</li></ul></code></pre>
<p>This will mostly display the rules that result in a final decision.  Rules that only jump to user-created chains will not be shown.</p>

<h2 id="download-and-install-the-iptables-service">Download and Install the Iptables Service</h2>

<p>To begin your server's transition, you need to download and install the <code>iptables-service</code> package from the CentOS repositories.</p>

<p>Download and install the service files by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install iptables-services
</li></ul></code></pre>
<p>This will download and install the <code>systemd</code> scripts used to manage the <code>iptables</code> service.  It will also write some default <code>iptables</code> and <code>ip6tables</code> configuration files to the <code>/etc/sysconfig</code> directory.</p>

<h2 id="construct-your-iptables-firewall-rules">Construct your Iptables Firewall Rules</h2>

<p>Next, you need to construct your <code>iptables</code> firewall rules by modifying the <code>/etc/sysconfig/iptables</code> and <code>/etc/sysconfig/ip6tables</code> files.  These files hold the rules that will be read and applied when we start the <code>iptables</code> service.</p>

<p>How you construct your firewall rules depends on whether the <code>system-config-firewall</code> process is installed and being used to manage these files.  Check the top of the <code>/etc/sysconfig/iptables</code> file to see whether it recommends against manual editing or not:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo head -2 /etc/sysconfig/iptables
</li></ul></code></pre>
<p>If the output looks like this, feel free to manually edit the <code>/etc/sysconfig/iptables</code> and <code>/etc/sysconfig/ip6tables</code> files to implement the policies for your <code>iptables</code> firewall:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="output">output</div># sample configuration for iptables service
# you can edit this manually or use system-config-firewall
</code></pre>
<p>Open and edit the files with <code>sudo</code> privileges to add your rules:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/sysconfig/iptables
</li><li class="line" prefix="$">sudo nano /etc/sysconfig/ip6tables
</li></ul></code></pre>
<p>After you've made your rules, you can test your IPv4 and IPv6 rules using these commands:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sh -c 'iptables-restore -t < /etc/sysconfig/iptables'
</li><li class="line" prefix="$">sudo sh -c 'ip6tables-restore -t < /etc/sysconfig/ip6tables'
</li></ul></code></pre>
<p>If, on the other hand, the output from examining the <code>/etc/sysconfig/iptables</code> file looks like this, you should not manually edit the file:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="output">output</div># Firewall configuration written by system-config-firewall
# Manual customization of this file is not recommended.
</code></pre>
<p>This means that the <code>system-config-firewall</code> management tool is installed and being used to manage this file.  Any manual changes will be overwritten by the tool.  If you see this, you should make changes to your firewall using one of the associated tools.  For the text UI, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo system-config-firewall-tui
</li></ul></code></pre>
<p>If you have the graphical UI installed, you can launch it by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo system-config-firewall
</li></ul></code></pre>
<p>If you need some help learning about <code>iptables</code> rules and syntax, the following guides may be helpful even though they are mainly targeted at Ubuntu systems:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using Iptables on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/iptables-essentials-common-firewall-rules-and-commands">Iptables Essentials: Common Firewall Rules and Commands</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">How To Implement a Basic Firewall Template with Iptables on Ubuntu 14.04</a></li>
</ul>

<h2 id="stop-the-firewalld-service-and-start-the-iptables-service">Stop the FirewallD Service and Start the Iptables Service</h2>

<p>Next, we need to stop the current <code>firewalld</code> firewall and bring up our <code>iptables</code> services.  We will use the <code>&&</code> construct to start the new firewall services as soon as the <code>firewalld</code> service successfully shuts down:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl stop firewalld && sudo systemctl start iptables; sudo systemctl start ip6tables
</li></ul></code></pre>
<p>You can verify that <code>firewalld</code> is not running by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo firewall-cmd --state
</li></ul></code></pre>
<p>You can also see that the rules you set up in the <code>/etc/sysconfig</code> directory have been loaded and applied by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -S
</li><li class="line" prefix="$">sudo ip6tables -S
</li></ul></code></pre>
<p>At this point, the <code>iptables</code> and <code>ip6tables</code> services are active for the current session.  However, currently, the <code>firewalld</code> service is still the one that will start automatically when the server reboots.</p>

<p>This is best time to test your firewall policies to make sure that you have the level of access that you need, because you can restart the server to revert to your old firewall if there are any issues.</p>

<h2 id="disable-the-firewalld-service-and-enable-the-iptables-services">Disable the FirewallD Service and Enable the Iptables Services</h2>

<p>After testing your firewall rules to ensure that your policy is correctly being enforced, you can go ahead and disable the <code>firewalld</code> service by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl disable firewalld
</li></ul></code></pre>
<p>This will prevent the service from starting automatically at boot.  Since the <code>firewalld</code> service should not be started manually while the <code>iptables</code> services are running either, you can take an extra step by masking the service.  This will prevent the <code>firewalld</code> service from being started manually as well:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl mask firewalld
</li></ul></code></pre>
<p>Now, you can enable your <code>iptables</code> and <code>ip6tables</code> services so that they will start automatically at boot:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl enable iptables
</li><li class="line" prefix="$">sudo systemctl enable ip6tables
</li></ul></code></pre>
<p>This should complete your firewall transition.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Implementing a firewall is an essential step towards keeping your servers secure.  While <code>firewalld</code> is a great firewall solution, sometimes using the most familiar tool or using the same systems across more diverse infrastructure makes the most sense.</p>

    