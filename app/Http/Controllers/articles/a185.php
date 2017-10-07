<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Iptables is a firewall that plays an essential role in network security for most Linux systems. While many iptables tutorials will teach you <a href="https://indiareads/community/tutorials/iptables-essentials-common-firewall-rules-and-commands">how to create firewall rules to secure your server</a>, this one will focus on a different aspect of firewall management: listing and deleting rules.</p>

<p>In this tutorial, we will cover how to do the following iptables tasks:</p>

<ul>
<li>List rules</li>
<li>Clear Packet and Byte Counters</li>
<li>Delete rules</li>
<li>Flush chains (delete all rules in a chain)</li>
<li>Flush all chains and tables, delete all chains, and accept all traffic</li>
</ul>

<p><span class="note"><strong>Note:</strong> When working with firewalls, take care not to lock yourself out of your own server by blocking SSH traffic (port 22, by default). If you lose access due to your firewall settings, you may need to <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-console-to-access-your-droplet">connect to it via the console</a> to fix your access. Once you are connected via the console, you can change your firewall rules to allow SSH access (or allow all traffic). If your saved firewall rules allow SSH access, another method is to reboot your server.<br /></span></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you start using this tutorial, you should have a separate, non-root superuser account—a user with sudo privileges—set up on your server. If you need to set this up, follow the appropriate guide:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Initial Server Setup with Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-6">Initial Server Setup with CentOS 6</a></li>
</ul>

<p>Let's look at how to list rules first. There are two different ways to view your active iptables rules: in a table or as a list of rule specifications. Both methods provide roughly the same information in different formats.</p>

<h2 id="list-rules-by-specification">List Rules by Specification</h2>

<p>To list out all of the active iptables rules by specification, run the <code>iptables</code> command with the <code>-S</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -S
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Example: Rule Specification Listing">Example: Rule Specification Listing</div>-P INPUT DROP
-P FORWARD DROP
-P OUTPUT ACCEPT
-N ICMP
-N TCP
-N UDP
-A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
-A INPUT -i lo -j ACCEPT
-A INPUT -m conntrack --ctstate INVALID -j DROP
-A INPUT -p udp -m conntrack --ctstate NEW -j UDP
-A INPUT -p tcp -m tcp --tcp-flags FIN,SYN,RST,ACK SYN -m conntrack --ctstate NEW -j TCP
-A INPUT -p icmp -m conntrack --ctstate NEW -j ICMP
-A INPUT -p udp -j REJECT --reject-with icmp-port-unreachable
-A INPUT -p tcp -j REJECT --reject-with tcp-reset
-A INPUT -j REJECT --reject-with icmp-proto-unreachable
-A TCP -p tcp -m tcp --dport 22 -j ACCEPT
</code></pre>
<p>As you can see, the output looks just like the commands that were used to create them, without the preceding <code>iptables</code> command. This will also look similar to the iptables rules configuration files, if you've ever used <code>iptables-persistent</code> or <code>iptables save</code>.</p>

<h3 id="list-specific-chain">List Specific Chain</h3>

<p>If you want to limit the output to a specific chain (<code>INPUT</code>, <code>OUTPUT</code>, <code>TCP</code>, etc.), you can specify the chain name directly after the <code>-S</code> option. For example, to show all of the rule specifications in the <code>TCP</code> chain, you would run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -S <span class="highlight">TCP</span>
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Example: TCP Chain Rule Specification Listing">Example: TCP Chain Rule Specification Listing</div>-N TCP
-A TCP -p tcp -m tcp --dport 22 -j ACCEPT
</code></pre>
<p>Let's take a look at the alternative way to view the active iptables rules, as a table of rules.</p>

<h2 id="list-rules-as-tables">List Rules as Tables</h2>

<p>Listing the iptables rules in the table view can be useful for comparing different rules against each other, </p>

<p>To output all of the active iptables rules in a table, run the <code>iptables</code> command with the <code>-L</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L
</li></ul></code></pre>
<p>This will output all of current rules sorted by chain.</p>

<p>If you want to limit the output to a specific chain (<code>INPUT</code>, <code>OUTPUT</code>, <code>TCP</code>, etc.), you can specify the chain name directly after the <code>-L</code> option.</p>

<p>Let's take a look at an example INPUT chain:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L <span class="highlight">INPUT</span>
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Example: Input Chain Rule Table Listing">Example: Input Chain Rule Table Listing</div>Chain INPUT (policy DROP)
target     prot opt source               destination
ACCEPT     all  --  anywhere             anywhere             ctstate RELATED,ESTABLISHED
ACCEPT     all  --  anywhere             anywhere
DROP       all  --  anywhere             anywhere             ctstate INVALID
UDP        udp  --  anywhere             anywhere             ctstate NEW
TCP        tcp  --  anywhere             anywhere             tcp flags:FIN,SYN,RST,ACK/SYN ctstate NEW
ICMP       icmp --  anywhere             anywhere             ctstate NEW
REJECT     udp  --  anywhere             anywhere             reject-with icmp-port-unreachable
REJECT     tcp  --  anywhere             anywhere             reject-with tcp-reset
REJECT     all  --  anywhere             anywhere             reject-with icmp-proto-unreachable
</code></pre>
<p>The first line of output indicates the chain name (INPUT, in this case), followed by its default policy (DROP). The next line consists of the headers of each column in the table, and is followed by the chain's rules. Let's go over what each header indicates:</p>

<ul>
<li><strong>target</strong>: If a packet matches the rule, the target specifies what should be done with it. For example, a packet can be accepted, dropped, logged, or sent to another chain to be compared against more rules</li>
<li><strong>prot</strong>: The protocol, such as <code>tcp</code>, <code>udp</code>, <code>icmp</code>, or <code>all</code></li>
<li><strong>opt</strong>: Rarely used, this column indicates IP options</li>
<li><strong>source</strong>: The source IP address or subnet of the traffic, or <code>anywhere</code></li>
<li><strong>destination</strong>: The destination IP address or subnet of the traffic, or <code>anywhere</code></li>
</ul>

<p>The last column, which is not labeled, indicates the <strong>options</strong> of a rule. That is, any part of the rule that isn't indicated by the previous columns. This could be anything from source and destination ports, to the connection state of the packet.</p>

<h3 id="show-packet-counts-and-aggregate-size">Show Packet Counts and Aggregate Size</h3>

<p>When listing iptables rules, it is also possible to show the number of packets, and the aggregate size of the packets in bytes, that matched each particular rule. This is often useful when trying to get a rough idea of which rules are matching against packets. To do so, simply use the <code>-L</code> and <code>-v</code> option together.</p>

<p>For example, let's look at the INPUT chain again, with the <code>-v</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L <span class="highlight">INPUT</span> -v
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Example: Verbose Listing">Example: Verbose Listing</div>Chain INPUT (policy DROP 0 packets, 0 bytes)
 pkts bytes target     prot opt in     out     source               destination
 284K   42M ACCEPT     all  --  any    any     anywhere             anywhere             ctstate RELATED,ESTABLISHED
    0     0 ACCEPT     all  --  lo     any     anywhere             anywhere
    0     0 DROP       all  --  any    any     anywhere             anywhere             ctstate INVALID
  396 63275 UDP        udp  --  any    any     anywhere             anywhere             ctstate NEW
17067 1005K TCP        tcp  --  any    any     anywhere             anywhere             tcp flags:FIN,SYN,RST,ACK/SYN ctstate NEW
 2410  154K ICMP       icmp --  any    any     anywhere             anywhere             ctstate NEW
  396 63275 REJECT     udp  --  any    any     anywhere             anywhere             reject-with icmp-port-unreachable
 2916  179K REJECT     all  --  any    any     anywhere             anywhere             reject-with icmp-proto-unreachable
    0     0 ACCEPT     tcp  --  any    any     anywhere             anywhere             tcp dpt:ssh ctstate NEW,ESTABLISHED
</code></pre>
<p>Note that the listing now has two additional columns, <code>pkts</code> and <code>bytes</code>.</p>

<p>Now that you know how to list the active firewall rules in a variety of ways, let's look at how you can reset the packet and byte counters.</p>

<h2 id="reset-packet-counts-and-aggregate-size">Reset Packet Counts and Aggregate Size</h2>

<p>If you want to clear, or zero, the packet and byte counters for your rules, use the <code>-Z</code> option. They also reset if a reboot occurs. This is useful if you want to see if your server is receiving new traffic that matches your existing rules.</p>

<p>To clear the counters for all chains and rules, use the <code>-Z</code> option by itself:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -Z
</li></ul></code></pre>
<p>To clear the counters for all rules in a specific chain, use the <code>-Z</code> option and specify the chain. For example, to clear the INPUT chain counters run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -Z INPUT
</li></ul></code></pre>
<p>If you want to clear the counters for a specific rule, specify the chain name and the rule number. For example, to zero the counters for the 1st rule in the INPUT chain, run this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -Z INPUT 1
</li></ul></code></pre>
<p>Now that you know how to reset the iptables packet and byte counters, let's look at the two methods that can be used to delete them.</p>

<h2 id="delete-rule-by-specification">Delete Rule by Specification</h2>

<p>One of the ways to delete iptables rules is by rule specification. To do so, you can run the <code>iptables</code> command with the <code>-D</code> option followed by the rule specification. If you want to delete rules using this method, you can use the output of the rules list, <code>iptables -S</code>, for some help.</p>

<p>For example, if you want to delete the rule that drops invalid incoming packets (<code>-A INPUT -m conntrack --ctstate INVALID -j DROP</code>), you could run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -D <span class="highlight">INPUT -m conntrack --ctstate INVALID -j DROP</span>
</li></ul></code></pre>
<p>Note that the <code>-A</code> option, which is used to indicate the rule position at creation time, should be excluded here.</p>

<h2 id="delete-rule-by-chain-and-number">Delete Rule by Chain and Number</h2>

<p>The other way to delete iptables rules is by its <strong>chain</strong> and <strong>line number</strong>. To determine a rule's line number, list the rules in the table format and add the <code>--line-numbers</code> option:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -L --line-numbers
</li></ul></code></pre><pre class="code-pre "><code langs="">[secondary_output Example Output: Rules with Line Numbers]
Chain INPUT (policy DROP)
num  target     prot opt source               destination
1    ACCEPT     all  --  anywhere             anywhere             ctstate RELATED,ESTABLISHED
2    ACCEPT     all  --  anywhere             anywhere
3    DROP       all  --  anywhere             anywhere             ctstate INVALID
4    UDP        udp  --  anywhere             anywhere             ctstate NEW
5    TCP        tcp  --  anywhere             anywhere             tcp flags:FIN,SYN,RST,ACK/SYN ctstate NEW
6    ICMP       icmp --  anywhere             anywhere             ctstate NEW
7    REJECT     udp  --  anywhere             anywhere             reject-with icmp-port-unreachable
8    REJECT     tcp  --  anywhere             anywhere             reject-with tcp-reset
9    REJECT     all  --  anywhere             anywhere             reject-with icmp-proto-unreachable
10   ACCEPT     tcp  --  anywhere             anywhere             tcp dpt:ssh ctstate NEW,ESTABLISHED
...
</code></pre>
<p>This adds the line number to each rule row, indicated by the <strong>num</strong> header.</p>

<p>Once you know which rule you want to delete, note the chain and line number of the rule. Then run the <code>iptables -D</code> command followed by the chain and rule number.</p>

<p>For example, if we want to delete the input rule that drops invalid packets, we can see that it's rule <code>3</code> of the <code>INPUT</code> chain. So we should run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -D <span class="highlight">INPUT 3</span>
</li></ul></code></pre>
<p>Now that you know how to delete individual firewall rules, let's go over how you can <strong>flush</strong> chains of rules.</p>

<h2 id="flush-chains">Flush Chains</h2>

<p>Iptables offers a way to delete all rules in a chain, or <strong>flush</strong> a chain. This section will cover the variety of ways to do this.</p>

<p><span class="note"><strong>Note:</strong> Be careful to not lock yourself out of your server, via SSH, by flushing a chain with a default policy of <strong>drop</strong> or <strong>deny</strong>. If you do, you may need to connect to it via the console to fix your access.<br /></span></p>

<h3 id="flush-a-single-chain">Flush a Single Chain</h3>

<p>To flush a specific chain, which will delete all of the rules in the chain, you may use the <code>-F</code>, or the equivalent <code>--flush</code>, option and the name of the chain to flush.</p>

<p>For example, to delete all of the rules in the <code>INPUT</code> chain, run this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -F <span class="highlight">INPUT</span>
</li></ul></code></pre>
<h3 id="flush-all-chains">Flush All Chains</h3>

<p>To flush all chains, which will delete all of the firewall rules, you may use the <code>-F</code>, or the equivalent <code>--flush</code>, option by itself:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -F
</li></ul></code></pre>
<h2 id="flush-all-rules-delete-all-chains-and-accept-all">Flush All Rules, Delete All Chains, and Accept All</h2>

<p>This section will show you how to flush all of your firewall rules, tables, and chains, and allow all network traffic.</p>

<p><span class="note"><strong>Note:</strong> This will effectively disable your firewall. You should only follow this section if you want to start over the configuration of your firewall.<br /></span></p>

<p>First, set the default policies for each of the built-in chains to <code>ACCEPT</code>. The main reason to do this is to ensure that you won't be locked out from your server via SSH:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -P INPUT ACCEPT
</li><li class="line" prefix="$">sudo iptables -P FORWARD ACCEPT
</li><li class="line" prefix="$">sudo iptables -P OUTPUT ACCEPT
</li></ul></code></pre>
<p>Then flush the <code>nat</code> and <code>mangle</code> tables, flush all chains (<code>-F</code>), and delete all non-default chains (<code>-X</code>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo iptables -t nat -F
</li><li class="line" prefix="$">sudo iptables -t mangle -F
</li><li class="line" prefix="$">sudo iptables -F
</li><li class="line" prefix="$">sudo iptables -X
</li></ul></code></pre>
<p>Your firewall will now allow all network traffic. If you list your rules now, you will will see there are none, and only the three default chains (INPUT, FORWARD, and OUTPUT) remain.</p>

<h2 id="conclusion">Conclusion</h2>

<p>After going through this tutorial, you should be familiar with how to list and delete your iptables firewall rules.</p>

<p>Remember that any iptables changes via the <code>iptables</code> command are ephemeral, and need to be saved to persist through server reboots. This is covered in the <a href="https://indiareads/community/tutorials/iptables-essentials-common-firewall-rules-and-commands#saving-rules">Saving Rules section</a> of the Common Firewall Rules and Commands tutorial.</p>

    