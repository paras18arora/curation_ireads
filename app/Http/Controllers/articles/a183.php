<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Using a firewall is as much about making intelligent policy decisions as it is about learning the syntax.  <a href="https://indiareads/community/tutorials/what-is-a-firewall-and-how-does-it-work">Firewalls</a> like <code>iptables</code> are capable of enforcing policies by interpreting rules set by the administrator.  However, as an administrator, you need to know what types of rules make sense for your infrastructure.</p>

<p>While <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">other guides</a> focus on the commands needed to get up and running, in this guide, we will discuss some of the decisions you will have to make when implementing a firewall.  These choices will affect how your firewall behaves, how locked down your server is, and how it will respond to various conditions that are likely to occur from time to time.  We will be using <code>iptables</code> as an example to discuss specifics, but most of the actual decisions will be relevant regardless of the tools used.</p>

<h2 id="deciding-on-a-default-policy">Deciding on a Default Policy</h2>

<p>When constructing a firewall, one of the fundamental decisions that you must make is the default policy.  This determines what happens when traffic is not matched by any other rules.  By default, a firewall can either <strong>accept</strong> any traffic unmatched by previous rules, or <strong>deny</strong> that traffic.</p>

<h3 id="default-drop-vs-default-accept">Default Drop vs Default Accept</h3>

<p>A default policy of "accept" means that any unmatched traffic is allowed to enter the server.  This is generally not advised because it means that, effectively, you will be maintaining a black list.  Black lists are difficult to manage because you must anticipate and block <em>every</em> type of unwanted traffic explicitly.  This can lead to maintenance headaches and is generally prone to mistakes, mis-configurations, and unanticipated holes in the established policy.</p>

<p>The alternative is a default policy of "drop".  This means that any traffic not matched by an explicit rule will not be allowed.  This is akin to a white list ACL.  Each and every service must be explicitly allowed, which might seem like a significant amount of research and work at first.  However, this means that your policy tends towards security and that you know exactly what is permitted to receive traffic on your server.</p>

<p>Basically the choice comes down to a tendency towards security by default or reachable services out-of-the-box.  While it may be tempting to implement a firewall that leans towards service availability, it is almost always a better idea to block traffic unless explicitly allowed.</p>

<h3 id="default-drop-policy-vs-final-drop-rule">Default Drop Policy vs Final Drop Rule</h3>

<p>The above choice of a default drop policy leads to another subtle decision.  With <code>iptables</code> and other similar firewalls, the default policy can be set using the built-in policy functionality of the firewall, or implemented by adding a catch-all drop rule at the end of the list of rules.</p>

<p>The distinction between these two methods lies in what happens if the firewall rules are flushed.</p>

<p>If your firewall's built-in policy function is set to "drop" and your firewall rules are ever flushed (reset), or if certain matching rules are removed, your services will instantly become inaccessible remotely.  This is often a good idea when setting policy for non-critical services so that your server is not exposed to malicious traffic if the rules are removed.</p>

<p>The downside to this approach is that your services will be completely unavailable to your clients until you re-establish permissive rules.  You could even potentially lock yourself out of the server if you do not have local or out-of-band access to skirt the issue (IndiaReads servers are accessible regardless of network settings by using the "Console Access" button located in the "Access" portion of your Droplet's page in the control panel).  If your firewall flush is intentional, this can be avoided by simply switching the default policy to "accept" just prior to resetting the rules.</p>

<p>The alternative to setting a drop policy using the built-in policy functionality is to set your firewall's default policy to "accept" and then implement a "drop" policy with regular rules.  You can add a normal firewall rule at the end of your chain that matches and denies all remaining unmatched traffic.</p>

<p>In this case, if your firewall rules are flushed, your services will be accessible but unprotected.  Depending on your options for local or alternative access, this might be a necessary evil in order to ensure that you can re-enter your server if the rules are flushed.  If you decide to use this option, you must ensure that the catch-all rule always remains the <em>last</em> rule in your rule set.</p>

<h2 id="dropping-vs-rejecting-traffic">Dropping vs Rejecting Traffic</h2>

<p>There are a few different ways of denying a packet passage to its intended destination.  The choice between these has an impact on how the client perceives its connection attempt and how quickly they are able to determine that their request will not be served.</p>

<p>The first way that packets can be denied is with "drop".  Drop can be used as a default policy or as a target for match rules.  When a packet is dropped, <code>iptables</code> basically just throws it away.  It sends no response back to the client trying to connect and does not give any indication that it has ever even received the packets in question.  This means that clients (legitimate or not) will not receive any confirmation of the receipt of their packets.</p>

<p>For TCP connection attempts, the connection will stall until the timeout limit has been reached.  Since UDP is a connectionless protocol, the lack of response for clients is even more ambiguous.  In fact, not receiving a packet back in this case is often an in indication that the packet was accepted.  If the UDP client cares about receipt of its packets, it will have to resend them to try to determine whether they were accepted, lost in transit, or dropped.  This can increase the amount of time that a malicious actor will have to spend to get proper information about the state of your server ports, but it could also cause problems with legitimate traffic.</p>

<p>An alternative to dropping traffic is to explicitly reject packets that you do not allow.  ICMP, or Internet Control Message Protocol, is a meta-protocol used throughout the internet to send status, diagnostic, and error messages between hosts as an out-of-band channel that does not rely on the conventional communication protocols like TCP or UDP.  When you use the "reject" target, the traffic is denied and an ICMP packet is returned to the sender to inform them that their traffic was received but will not be accepted.  The status message can hint as to the reason.</p>

<p>This has a number of consequences.  Assuming that ICMP traffic is allowed to flow out to the client, they will immediately be informed that their traffic is blocked.  For legitimate clients, this means that they can contact the administrator or check their connection options to ensure that they are reaching out to the correct port.  For malicious users, this means that they can complete their scans and map out the open, closed, and filtered ports in a shorter period of time.</p>

<p>There is a lot to consider when deciding whether to drop or reject traffic.  One important consideration is that most malicious traffic will actually be perpetrated by automated scripts.  Since scripts are typically not time-sensitive, dropping illegitimate traffic will not have desired disincentive while it will have the negative effects for legitimate users.  More on this subject can be found <a href="http://www.chiark.greenend.org.uk/%7Epeterb/network/drop-vs-reject">here</a>.</p>

<h3 id="drop-vs-reject-response-table">Drop vs Reject Response Table</h3>

<p>The table below shows how a server protected by a firewall will react to different requests depending on the policy being applied to the destination port.</p>

<table class="pure-table"><thead>
<tr>
<th>Client Packet Type</th>
<th>NMap Command</th>
<th>Port Policy</th>
<th>Response</th>
<th>Inferred Port State</th>
</tr>
</thead><tbody>
<tr>
<td>TCP</td>
<td>nmap [-sT | -sS] -Pn <server></td>
<td>Accept</td>
<td>TCP SYN/ACK</td>
<td>Open</td>
</tr>
<tr>
<td>TCP</td>
<td>nmap [-sT | -sS] -Pn <server></td>
<td>Drop</td>
<td>(none)</td>
<td>Filtered</td>
</tr>
<tr>
<td>TCP</td>
<td>nmap [-sT | -sS] -Pn <server></td>
<td>Reject</td>
<td>TCP RESET</td>
<td>Closed</td>
</tr>
<tr>
<td>UDP</td>
<td>nmap -sU -Pn <server></td>
<td>Accept</td>
<td>(none)</td>
<td>Open or Filtered</td>
</tr>
<tr>
<td>UDP</td>
<td>nmap -sU -Pn <server></td>
<td>Drop</td>
<td>(none)</td>
<td>Open or Filtered</td>
</tr>
<tr>
<td>UDP</td>
<td>nmap -sU -Pn <server></td>
<td>Reject</td>
<td>ICMP Port Unreachable</td>
<td>Closed</td>
</tr>
</tbody></table>

<p>The first column indicates the packet type sent by the client.  In the second column, we've included the <code>nmap</code> commands that can be used to test each scenario.  The third column indicates the port policy being applied to the port.  The fourth column is the response the server will send back and the fifth column is what the client can infer about the port based on the response it has received.</p>

<h2 id="icmp-policies">ICMP Policies</h2>

<p>Similar to the question about whether to drop or reject denied traffic, there are differing opinions on whether to accept ICMP packets destined for your server.</p>

<p>ICMP is a protocol used for many things.  It is often sent back, as we saw above, to give status information about requests using other protocols.  Perhaps its most recognized function to send and respond to network pings to verify connectability to remote hosts.  There are many other uses for ICMP however that are not as well known, but still useful.</p>

<p>ICMP packets are organized by "type" and then further by "code".  A type specifies the general meaning of the message.  For instance, Type 3 means that the destination was unreachable.  A code is often used to give further information about a type.  For example, ICMP Type 3 Code 3 means that the destination port was unavailable, while ICMP Type 3 Code 0 means that the destination network could not be reached.</p>

<h3 id="types-that-can-always-be-blocked">Types that Can Always Be Blocked</h3>

<p>Some ICMP types are deprecated, so they should probably be blocked unconditionally.  Among these are ICMP source quench (type 4 code 0) and alternate host (type 6). Types 1, 2, 7 and type 15 and above are all deprecated, reserved for future use, or experimental.</p>

<h3 id="types-to-block-depending-on-network-configuration">Types to Block Depending on Network Configuration</h3>

<p>Some ICMP types are useful in certain network configurations, but should be blocked in others.</p>

<p>For instance, ICMP redirect messages (type 5) can be useful to illuminate bad network design.  An ICMP redirect is sent when a better route is directly available to the client.  So if a router receives a packet that will have to be routed to another host on the same network, it sends an ICMP redirect message to tell the client to send the packets through the other host in the future.</p>

<p>This is useful if you trust your local network and want to spot inefficiencies in your routing tables during initial configuration (fixing your routes is a better long-term solution).  On an untrusted network however, a malicious user could potentially send ICMP redirects to manipulate the routing tables on hosts.</p>

<p>Other ICMP types that are useful in some networks and potentially harmful in others are ICMP router advertisement (type 9) and router solicitation (type 10) packets.  Router advertisement and solicitation packets are used as part of IRDP (ICMP Internet Router Discovery Protocol), a system that allows hosts, upon booting up or joining a network, to dynamically discover available routers.</p>

<p>In most cases, it is better for a host to have static routes configured for the gateways it will use.  These packets should be accepted in the same situations as the ICMP redirect packets.  In fact, since the host will not know the preferred route for traffic of any discovered routes, redirect messages are often needed directly after discovery.  If you are not running a service that sends router solicitation packets or modifies your routes based on advertisement packets (like <code>rdisc</code>), you can safely block these packets.</p>

<h3 id="types-that-are-often-safe-to-allow">Types that are Often Safe to Allow</h3>

<p>ICMP types that are usually safe to allow are below, but you may want to disable them if you want to be extra careful.</p>

<ul>
<li>Type 8 — Echo request: These are ping requests directed at your server.  It is usually safe to allow these (denying these packets doesn't hide your server.  There are plenty of other ways for users to find out if your host is up), but you can block them or limit the source addresses you respond to if you'd like.</li>
<li>Type 13 — Timestamp request: These packets can be used by clients to collect latency information.  They can be used in some OS fingerprinting techniques, so block them if you'd like or limit the range of addresses that you respond to.</li>
</ul>

<p>The types below can usually be allowed without explicit rules by configuring your firewall to allow responses to requests it has made (by using the <code>conntrack</code> module to allow <code>ESTABLISHED</code> and <code>RELATED</code> traffic).</p>

<ul>
<li>Type 0 — Echo replies: These are responses to echo requests (pings).</li>
<li>Type 3 — Destination Unreachable: Legitimate destination unreachable packets are responses to requests created by your server indicating that the packet could not be delivered.</li>
<li>Type 11 — Time exceeded: This is a diagnostic error returned if a packet generated by your server died before reaching the destination because of exceeding its TTL value.</li>
<li>Type 12 — Parameter problem: This means that an outgoing packet from your server was malformed.</li>
<li>Type 14 — Timestamp responses: These are the responses for timestamp queries generated by your server.</li>
</ul>

<p>Blocking all incoming ICMP traffic is still recommended by some security experts, however many people now encourage intelligent ICMP acceptance policies.  The links <a href="http://security.stackexchange.com/questions/22711/is-it-a-bad-idea-for-a-firewall-to-block-icmp/22713#22713">here</a> and <a href="http://serverfault.com/questions/84963/why-not-block-icmp/84981#84981">here</a> have more information.</p>

<h2 id="connection-limiting-and-rate-limiting">Connection Limiting and Rate Limiting</h2>

<p>For some services and traffic patterns, you may want to allow access provided that the client is not abusing that access.  Two ways of constraining resource usage are connection limiting and rate limiting.</p>

<h3 id="connection-limiting">Connection Limiting</h3>

<p>Connection limiting can be implemented using extensions like <code>connlimit</code> to check how many active connections a client has open.  This can be used to restrict the number of connections allowed at one time.  If you decide to impose connection limiting, you will have some decisions to make in regards to how it is applied.  The general breakdown of decisions is:</p>

<ul>
<li>Limit on a per-address, per-network, or global basis?</li>
<li>Match and restrict traffic for a specific service or to the server as a whole?</li>
</ul>

<p>Connections can be limited on a host-by-host basis, or a limit can be set for a network segment by supplying a network prefix.  You can also set a global maximum number of connections for a service or the entire machine.  Keep in mind that it is possible to mix and match these to create more complex policies to control your connection numbers.</p>

<h3 id="rate-limiting">Rate Limiting</h3>

<p>Rate limiting allows you to construct rules that govern the rate or frequency at which traffic will be accepted by your server.  There are a number of different extensions that can be used for rate limiting including <code>limit</code>, <code>hashlimit</code>, and <code>recent</code>.  The choice of the extension you use will depend largely on the <em>way</em> that you want to limit traffic.</p>

<p>The <code>limit</code> extension will cause the rule in question to be matched until the limit is hit, after which further packets are dropped.  If you set a limit like "5/sec", and the rule will allow 5 packets to match per second, after which the rule no longer matches.  This is good for setting a global rate-limit for a service.</p>

<p>The <code>hashlimit</code> extension is more flexible, allowing you to specify some of the values <code>iptables</code> will hash to evaluate a match.  For instance, it can look at the source address, source port, destination address, destination port, or an arbitrary combination of those four values to hash each entry.  It can limit by packets or bytes received.  Basically, this provides flexible per-client or per-service rate limiting.</p>

<p>The <code>recent</code> extension dynamically adds client IP addresses to a list or checks against an existing list when the rule matches.  This allows you to spread your limiting logic between a number of different rules for complex patterns.  It has the ability to specify a hit count and a time range like the other limiters, but can also reset the time range if additional traffic is seen, effectively forcing a client to stop all traffic if they are being limited.</p>

<p>The choice of which rate limiting extension to use depends on the exact policies you wish to enforce.</p>

<h2 id="monolithic-vs-chain-based-management">Monolithic vs Chain-Based Management</h2>

<p>All <code>iptables</code> firewall policy is rooted in extending the built-in chains.  For simple firewalls, this often takes the form of changing the default policy for the chains and adding rules.  For more complex firewalls, it is often a good idea to extend the management framework by creating additional chains.</p>

<p>User-created chains are inherently tied to their calling chain.  User-created chains have no default policy, so if a packet falls through a user-created chain, it will return to the calling chain and continue evaluation.  With that in mind, user-created chains are mainly useful for organizational purposes, to make rule match conditions more DRY, and to improve readability by splitting match conditions.</p>

<p>If you find yourself repeating certain match criteria for a significant number of rules, it might be worthwhile to instead create a jump rule with the shared match criteria to a new chain.  Inside the new chain, you can add that set of rules with the shared match criteria removed.</p>

<p>Beyond simple organization, this can have some beneficial side effects.  For instance, intelligent use of chains for very similar sets of rules means that adding rules in the correct location can be easier and less error-prone.  It can also be easier to display and understand the parts of the policy you care about by limiting by chain.</p>

<p>The decision as to whether to lump all of your rules into one of the built-in chains or whether to create and utilize additional chains will largely depend on how complex and easy to manage your rule set is.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now, you should have a fairly good idea about some of the decisions you'll have to make when designing firewall policies for your servers.  Usually the time investment involved with firewalls leans heavily towards the initial setup, leaving management fairly simple.  While it may take some time, thought, and experimentation to come up with a policy that best serves your needs, doing so will give you more control over the security of your server.</p>

<p>If you would like to know more about firewalls and <code>iptables</code> specifically, check out the following articles:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-the-iptables-firewall-works">How the Iptables Firewall Works</a></li>
<li><a href="https://indiareads/community/tutorials/a-deep-dive-into-iptables-and-netfilter-architecture">A Deep Dive into Iptables and Netfilter Architecture</a></li>
</ul>

<p>The following guides can help you implement your policies.  Choose the guide that matches your firewall to get started:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using Iptables on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-14-04">How To Set Up a Firewall with UFW on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-configure-firewalld-to-protect-your-centos-7-server">How To Set Up a Firewall Using FirewallD on CentOS 7</a></li>
</ul>

    