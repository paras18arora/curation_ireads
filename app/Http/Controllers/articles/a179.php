<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="https://indiareads/community/tutorials/what-is-a-firewall-and-how-does-it-work">Firewalls</a> are an important tool that can be configured to protect your servers and infrastructure.  In the Linux ecosystem, <code>iptables</code> is a widely used firewall tool that interfaces with the kernel's <code>netfilter</code> packet filtering framework.  For users and administrators who don't understand the architecture of these systems, creating reliable firewall policies can be daunting, not only due to challenging syntax, but also because of number of interrelated parts present in the framework.</p>

<p>In this guide, we will dive into the <code>iptables</code> architecture with the aim of making it more comprehensible for users who need to build their own firewall policies.  We will discuss how <code>iptables</code> interacts with <code>netfilter</code> and how the various components fit together to provide a comprehensive filtering and mangling system.</p>

<h2 id="what-are-iptables-and-netfilter">What Are IPTables and Netfilter?</h2>

<p>The basic firewall software most commonly used in Linux is called <code>iptables</code>.  The <code>iptables</code> firewall works by interacting with the packet filtering hooks in the Linux kernel's networking stack.  These kernel hooks are known as the <code>netfilter</code> framework.</p>

<p>Every packet that enters networking system (incoming or outgoing) will trigger these hooks as it progresses through the stack, allowing programs that register with these hooks to interact with the traffic at key points.  The kernel modules associated with <code>iptables</code> register at these hooks in order to ensure that the traffic conforms to the conditions laid out by the firewall rules.</p>

<h2 id="netfilter-hooks">Netfilter Hooks</h2>

<p>There are five <code>netfilter</code> hooks that programs can register with.  As packets progress through the stack, they will trigger the kernel modules that have registered with these hooks.  The hooks that a packet will trigger depends on whether the packet is incoming or outgoing, the packet's destination, and whether the packet was dropped or rejected at a previous point.</p>

<p>The following hooks represent various well-defined points in the networking stack:</p>

<ul>
<li><code>NF_IP_PRE_ROUTING</code>: This hook will be triggered by any incoming traffic very soon after entering the network stack.  This hook is processed before any routing decisions have been made regarding where to send the packet.</li>
<li><code>NF_IP_LOCAL_IN</code>: This hook is triggered after an incoming packet has been routed if the packet is destined for the local system.</li>
<li><code>NF_IP_FORWARD</code>: This hook is triggered after an incoming packet has been routed if the packet is to be forwarded to another host.</li>
<li><code>NF_IP_LOCAL_OUT</code>: This hook is triggered by any locally created outbound traffic as soon it hits the network stack.</li>
<li><code>NF_IP_POST_ROUTING</code>: This hook is triggered by any outgoing or forwarded traffic after routing has taken place and just before being put out on the wire.</li>
</ul>

<p>Kernel modules that wish to register at these hooks must provide a priority number to help determine the order in which they will be called when the hook is triggered.  This provides the means for multiple modules (or multiple instances of the same module) to be connected to each of the hooks with deterministic ordering.  Each module will be called in turn and will return a decision to the <code>netfilter</code> framework after processing that indicates what should be done with the packet.</p>

<h2 id="iptables-tables-and-chains">IPTables Tables and Chains</h2>

<p>The <code>iptables</code> firewall uses tables to organize its rules.  These tables classify rules according to the type of decisions they are used to make.  For instance, if a rule deals with network address translation, it will be put into the <code>nat</code> table.  If the rule is used to decide whether to allow the packet to continue to its destination, it would probably be added to the <code>filter</code> table.</p>

<p>Within each <code>iptables</code> table, rules are further organized within separate "chains".  While tables are defined by the general aim of the rules they hold, the built-in chains represent the <code>netfilter</code> hooks which trigger them.  Chains basically determine <em>when</em> rules will be evaluated.</p>

<p>As you can see, the names of the built-in chains mirror the names of the <code>netfilter</code> hooks they are associated with:</p>

<ul>
<li><code>PREROUTING</code>: Triggered by the <code>NF_IP_PRE_ROUTING</code> hook.</li>
<li><code>INPUT</code>: Triggered by the <code>NF_IP_LOCAL_IN</code> hook.</li>
<li><code>FORWARD</code>: Triggered by the <code>NF_IP_FORWARD</code> hook.</li>
<li><code>OUTPUT</code>: Triggered by the <code>NF_IP_LOCAL_OUT</code> hook.</li>
<li><code>POSTROUTING</code>: Triggered by the <code>NF_IP_POST_ROUTING</code> hook.</li>
</ul>

<p>Chains allow the administrator to control where in a packet's delivery path a rule will be evaluated.  Since each table has multiple chains, a table's influence can be exerted at multiple points in processing.  Because certain types of decisions only make sense at certain points in the network stack, every table will not have a chain registered with each kernel hook.</p>

<p>There are only five <code>netfilter</code> kernel hooks, so chains from multiple tables are registered at each of the hooks.  For instance, three tables have <code>PREROUTING</code> chains.  When these chains register at the associated <code>NF_IP_PRE_ROUTING</code> hook, they specify a priority that dictates what order each table's <code>PREROUTING</code> chain is called.  Each of the rules inside the highest priority <code>PREROUTING</code> chain is evaluated sequentially before moving onto the next <code>PREROUTING</code> chain.  We will take a look at the specific order of each chain in a moment.</p>

<h2 id="which-tables-are-available">Which Tables are Available?</h2>

<p>Let's step back for a moment and take a look at the different tables that <code>iptables</code> provides.  These represent distinct sets of rules, organized by area of concern, for evaluating packets. </p>

<h3 id="the-filter-table">The Filter Table</h3>

<p>The filter table is one of the most widely used tables in <code>iptables</code>.  The <code>filter</code> table is used to make decisions about whether to let a packet continue to its intended destination or to deny its request.  In firewall parlance, this is known as "filtering" packets.  This table provides the bulk of functionality that people think of when discussing firewalls.</p>

<h3 id="the-nat-table">The NAT Table</h3>

<p>The <code>nat</code> table is used to implement network address translation rules.  As packets enter the network stack, rules in this table will determine whether and how to modify the packet's source or destination addresses in order to impact the way that the packet and any response traffic are routed.  This is often used to route packets to networks when direct access is not possible.</p>

<h3 id="the-mangle-table">The Mangle Table</h3>

<p>The <code>mangle</code> table is used to alter the IP headers of the packet in various ways.  For instance, you can adjust the TTL (Time to Live) value of a packet, either lengthening or shortening the number of valid network hops the packet can sustain.  Other IP headers can be altered in similar ways.</p>

<p>This table can also place an internal kernel "mark" on the packet for further processing in other tables and by other networking tools.  This mark does not touch the actual packet, but adds the mark to the kernel's representation of the packet.</p>

<h3 id="the-raw-table">The Raw Table</h3>

<p>The <code>iptables</code> firewall is stateful, meaning that packets are evaluated in regards to their relation to previous packets.  The connection tracking features built on top of the <code>netfilter</code> framework allow <code>iptables</code> to view packets as part of an ongoing connection or session instead of as a stream of discrete, unrelated packets.  The connection tracking logic is usually applied very soon after the packet hits the network interface.</p>

<p>The <code>raw</code> table has a very narrowly defined function.  Its only purpose is to provide a mechanism for marking packets in order to opt-out of connection tracking.</p>

<h3 id="the-security-table">The Security Table</h3>

<p>The <code>security</code> table is used to set internal SELinux security context marks on packets, which will affect how SELinux or other systems that can interpret SELinux security contexts handle the packets.  These marks can be applied on a per-packet or per-connection basis.</p>

<h2 id="which-chains-are-implemented-in-each-table">Which Chains are Implemented in Each Table?</h2>

<p>We have talked about tables and chains separately.  Let's go over which chains are available in each table.  Implied in this discussion is a further discussion about the evaluation order of chains registered to the same hook.  If three tables have <code>PREROUTING</code> chains, in which order are they evaluated?</p>

<p>The following table indicates the chains that are available within each <code>iptables</code> table when read from left-to-right.  For instance, we can tell that the <code>raw</code> table has both <code>PREROUTING</code> and <code>OUTPUT</code> chains.  When read from top-to-bottom, it also displays the order in which each chain is called when the associated <code>netfilter</code> hook is triggered.</p>

<p>A few things should be noted.  In the representation below, the <code>nat</code> table has been split between <code>DNAT</code> operations (those that alter the destination address of a packet) and <code>SNAT</code> operations (those that alter the source address) in order to display their ordering more clearly.  We have also include rows that represent points where routing decisions are made and where connection tracking is enabled in order to give a more holistic view of the processes taking place:</p>

<table class="pure-table"><thead>
<tr>
<th>Tables↓/Chains→</th>
<th style="text-align: center">PREROUTING</th>
<th style="text-align: center">INPUT</th>
<th style="text-align: center">FORWARD</th>
<th style="text-align: center">OUTPUT</th>
<th style="text-align: center">POSTROUTING</th>
</tr>
</thead><tbody>
<tr>
<td>(routing decision)</td>
<td style="text-align: center"></td>
<td style="text-align: center"></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
</tr>
<tr>
<td><strong>raw</strong></td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
</tr>
<tr>
<td>(connection tracking enabled)</td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
</tr>
<tr>
<td><strong>mangle</strong></td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
</tr>
<tr>
<td><strong>nat</strong> (DNAT)</td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
</tr>
<tr>
<td>(routing decision)</td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
</tr>
<tr>
<td><strong>filter</strong></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
</tr>
<tr>
<td><strong>security</strong></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
</tr>
<tr>
<td><strong>nat</strong> (SNAT)</td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
<td style="text-align: center"></td>
<td style="text-align: center"></td>
<td style="text-align: center">✓</td>
</tr>
</tbody></table>

<p>As a packet triggers a <code>netfilter</code> hook, the associated chains will be processed as they are listed in the table above from top-to-bottom.  The hooks (columns) that a packet will trigger depend on whether it is an incoming or outgoing packet, the routing decisions that are made, and whether the packet passes filtering criteria.</p>

<p>Certain events will cause a table's chain to be skipped during processing.  For instance, only the first packet in a connection will be evaluated against the NAT rules.  Any <code>nat</code> decisions made for the first packet will be applied to all subsequent packets in the connection without additional evaluation.  Responses to NAT'ed connections will automatically have the reverse NAT rules applied to route correctly.</p>

<h3 id="chain-traversal-order">Chain Traversal Order</h3>

<p>Assuming that the server knows how to route a packet and that the firewall rules permit its transmission, the following flows represent the paths that will be traversed in different situations:</p>

<ul>
<li><strong>Incoming packets destined for the local system</strong>: <code>PREROUTING</code> -> <code>INPUT</code></li>
<li><strong>Incoming packets destined to another host</strong>: <code>PREROUTING</code> -> <code>FORWARD</code> -> <code>POSTROUTING</code></li>
<li><strong>Locally generated packets</strong>: <code>OUTPUT</code> -> <code>POSTROUTING</code></li>
</ul>

<p>If we combine the above information with the ordering laid out in the previous table, we can see that an incoming packet destined for the local system will first be evaluated against the <code>PREROUTING</code> chains of the <code>raw</code>, <code>mangle</code>, and <code>nat</code> tables.  It will then traverse the <code>INPUT</code> chains of the <code>mangle</code>, <code>filter</code>, <code>security</code>, and <code>nat</code> tables before finally being delivered to the local socket.</p>

<h2 id="iptables-rules">IPTables Rules</h2>

<p>Rules are placed within a specific chain of a specific table.  As each chain is called, the packet in question will be checked against each rule within the chain in order.  Each rule has a matching component and an action component.</p>

<h3 id="matching">Matching</h3>

<p>The matching portion of a rule specifies the criteria that a packet must meet in order for the associated action (or "target") to be executed.</p>

<p>The matching system is very flexible and can be expanded significantly with <code>iptables</code> extensions available on the system.  Rules can be constructed to match by protocol type, destination or source address, destination or source port, destination or source network, input or output interface, headers, or connection state among other criteria.  These can be combined to create fairly complex rule sets to distinguish between different traffic.</p>

<h3 id="targets">Targets</h3>

<p>A target is the action that are triggered when a packet meets the matching criteria of a rule.  Targets are generally divided into two categories:</p>

<ul>
<li><strong>Terminating targets</strong>: Terminating targets perform an action which terminates evaluation within the chain and returns control to the <code>netfilter</code> hook.  Depending on the return value provided, the hook might drop the packet or allow the packet to continue to the next stage of processing.</li>
<li><strong>Non-terminating targets</strong>: Non-terminating targets perform an action and continue evaluation within the chain.  Although each chain must eventually pass back a final terminating decision, any number of non-terminating targets can be executed beforehand.</li>
</ul>

<p>The availability of each target within rules will depend on context.  For instance, the table and chain type might dictate the targets available.  The extensions activated in the rule and the matching clauses can also affect the availability of targets.</p>

<h2 id="jumping-to-user-defined-chains">Jumping to User-Defined Chains</h2>

<p>We should mention a special class of non-terminating target: the jump target.  Jump targets are actions that result in evaluation moving to a different chain for additional processing.  We've talked quite a bit about the built-in chains which are intimately tied to the <code>netfilter</code> hooks that call them.  However, <code>iptables</code> also allows administrators to create their own chains for organizational purposes.</p>

<p>Rules can be placed in user-defined chains in the same way that they can be placed into built-in chains.  The difference is that user-defined chains can only be reached by "jumping" to them from a rule (they are not registered with a <code>netfilter</code> hook themselves).</p>

<p>User-defined chains act as simple extensions of the chain which called them.  For instance, in a user-defined chain, evaluation will pass back to the calling chain if the end of the rule list is reached or if a <code>RETURN</code> target is activated by a matching rule.  Evaluation can also jump to additional user-defined chains.</p>

<p>This construct allows for greater organization and provides the framework necessary for more robust branching.</p>

<h2 id="iptables-and-connection-tracking">IPTables and Connection Tracking</h2>

<p>We introduced the connection tracking system implemented on top of the <code>netfilter</code> framework when we discussed the <code>raw</code> table and connection state matching criteria.  Connection tracking allows <code>iptables</code> to make decisions about packets viewed in the context of an ongoing connection.  The connection tracking system provides <code>iptables</code> with the functionality it needs to perform "stateful" operations.</p>

<p>Connection tracking is applied very soon after packets enter the networking stack.  The <code>raw</code> table chains and some basic sanity checks are the only logic that is performed on packets prior to associating the packets with a connection.</p>

<p>The system checks each packet against a set of existing connections.  It will update the state of the connection in its store if needed and will add new connections to the system when necessary.  Packets that have been marked with the <code>NOTRACK</code> target in one of the <code>raw</code> chains will bypass the connection tracking routines.</p>

<h3 id="available-states">Available States</h3>

<p>Connections tracked by the connection tracking system will be in one of the following states:</p>

<ul>
<li><code>NEW</code>: When a packet arrives that is not associated with an existing connection, but is not invalid as a first packet, a new connection will be added to the system with this label.  This happens for both connection-aware protocols like TCP and for connectionless protocols like UDP.</li>
<li><code>ESTABLISHED</code>: A connection is changed from <code>NEW</code> to <code>ESTABLISHED</code> when it receives a valid response in the opposite direction.  For TCP connections, this means a <code>SYN/ACK</code> and for UDP and ICMP traffic, this means a response where source and destination of the original packet are switched.</li>
<li><code>RELATED</code>: Packets that are not part of an existing connection, but are associated with a connection already in the system are labeled <code>RELATED</code>.  This could mean a helper connection, as is the case with FTP data transmission connections, or it could be ICMP responses to connection attempts by other protocols.</li>
<li><code>INVALID</code>: Packets can be marked <code>INVALID</code> if they are not associated with an existing connection and aren't appropriate for opening a new connection, if they cannot be identified, or if they aren't routable among other reasons.</li>
<li><code>UNTRACKED</code>: Packets can be marked as <code>UNTRACKED</code> if they've been targeted in a <code>raw</code> table chain to bypass tracking.</li>
<li><code>SNAT</code>: A virtual state set when the source address has been altered by NAT operations.  This is used by the connection tracking system so that it knows to change the source addresses back in reply packets.</li>
<li><code>DNAT</code>: A virtual state set when the destination address has been altered by NAT operations.  This is used by the connection tracking system so that it knows to change the destination address back when routing reply packets.</li>
</ul>

<p>The states tracked in the connection tracking system allow administrators to craft rules that target specific points in a connection's lifetime.  This provides the functionality needed for more thorough and secure rules.</p>

<h2 id="conclusion">Conclusion</h2>

<p>The <code>netfilter</code> packet filtering framework and the <code>iptables</code> firewall are the basis for most firewall solutions on Linux servers.  The <code>netfilter</code> kernel hooks are close enough to the networking stack to provide powerful control over packets as they are processed by the system.  The <code>iptables</code> firewall leverages these capabilities to provide a flexible, extensible method of communicating policy requirements to the kernel.  By learning about how these pieces fit together, you can better utilize them to control and secure your server environments.</p>

<p>If you would like to know more about how to choose effective <code>iptables</code> policies, check out <a href="https://indiareads/community/tutorials/how-to-choose-an-effective-firewall-policy-to-secure-your-servers">this guide</a>.</p>

<p>These guides can help you get started implementing your <code>iptables</code> firewall rules:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">How To Set Up a Firewall Using Iptables on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/iptables-essentials-common-firewall-rules-and-commands">Iptables Essentials: Common Firewall Rules and Commands</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-implement-a-basic-firewall-template-with-iptables-on-ubuntu-14-04">How To Implement a Basic Firewall Template with Iptables on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-set-up-an-iptables-firewall-to-protect-traffic-between-your-servers">How To Set Up an Iptables Firewall to Protect Traffic Between your Servers</a></li>
</ul>

    