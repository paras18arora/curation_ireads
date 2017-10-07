<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this tutorial, we will demonstrate how to use <strong>Ansible</strong>, a configuration management tool, to set up a mesh VPN with <strong>Tinc</strong> to secure network communications between your Ubuntu and CentOS servers.</p>

<p>A mesh VPN is especially useful if your servers are using a shared network, like <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">IndiaReads's datacenter-wide private networking</a> feature, because it enables your servers to communicate as if they were isolated on a truly private network. The extra layer of security provided by the authentication and encryption features of the VPN will protect the network communication of your private services—databases, Elasticsearch clusters, and more—from unauthorized access or attacks.</p>

<p><img src="https://assets.digitalocean.com/articles/tinc/ansible-tinc-mesh.png" alt="Mesh VPN Diagram" /></p>

<p>Manually configuring and maintaining a VPN across multiple servers is difficult and error-prone, because multiple configuration and key files need distributed amongst all VPN members. For this reason, a configuration management tool should be used for any practical mesh VPN setup whose members might change at some point. Any configuration management tool can be used, but this tutorial uses Ansible because it is popular and easy to use. The Ansible <em>Playbook</em> that this tutorial uses, <a href="https://github.com/thisismitch/ansible-tinc">ansible-tinc</a>, has been tested on Ubuntu 14.04 and CentOS 7 servers.</p>

<h2 id="background-reading">Background Reading</h2>

<p>You should be able to follow this tutorial and set up a mesh VPN without knowing too much about Ansible or Tinc, as the included Playbook will do most of the work for you. However, you may want to read up on how they work, at some point, so that you understand the details of what you are setting up.</p>

<p><a href="https://indiareads/community/tutorials/how-to-install-tinc-and-set-up-a-basic-vpn-on-ubuntu-14-04">This Tinc VPN tutorial</a> covers how to install and configure Tinc VPN manually. Using Ansible to automate the process makes it a lot easier to manage.</p>

<p><a href="https://indiareads/community/tutorials/how-to-install-and-configure-ansible-on-an-ubuntu-12-04-vps">How to Install and Configure Ansible</a> provides a very high-level introduction to how Ansible works. If you want to start writing Ansible Playbooks to automate system administrator tasks, check out <a href="https://indiareads/community/tutorials/how-to-create-ansible-playbooks-to-automate-system-configuration-on-ubuntu">this tutorial</a>.</p>

<h2 id="prerequisites">Prerequisites</h2>

<h3 id="local-machine">Local Machine</h3>

<p>The local machine is where you will execute the Ansible Playbook from. This could be your local machine (e.g. laptop) or some other server that you use to manage your servers. As mentioned earlier, it needs to be able to connect to each remote server as <code>root</code>.</p>

<p>Your local machine needs to have Ansible 2.0+ installed. Refer to the <a href="http://docs.ansible.com/ansible/intro_installation.html">official Ansible installation documentation</a> if you need to install it, as the installation process varies depending on your operating system or distribution. </p>

<p>Your local machine also needs to have Git installed, so you can easily download a copy of the <a href="https://github.com/thisismitch/ansible-tinc">ansible-tinc</a> Playbook. Again, because the installation instructions depend on your local machine, refer to the <a href="https://git-scm.com/book/en/v2/Getting-Started-Installing-Git">official Git installation guide</a>.</p>

<h3 id="remote-servers">Remote Servers</h3>

<p>The remote servers are the hosts that you want to configure to use Tinc VPN. You should start with at least two. To work with the Ansible Playbook, they must be:</p>

<ul>
<li>Running Ubuntu 14.04 or CentOS 7</li>
<li>Accessible to the local machine (where Ansible is installed) via the <code>root</code> user, <a href="https://indiareads/community/tutorials/how-to-use-ssh-keys-with-digitalocean-droplets">with public key authentication</a></li>
</ul>

<p><span class="note"><strong>Note:</strong> It is not possible to use a different remote user at this time due to a <a href="https://github.com/ansible/ansible/issues/13825">bug with the Ansible Synchronize module</a>, which the Playbook uses.<br /></span></p>

<p>If you haven't already disabled password authentication for <code>root</code>, you can do so by adding <code>PermitRootLogin without-password</code> to your <code>/etc/ssh/sshd_config</code> file, then restarting SSH.</p>

<p>If you are using IndiaReads Droplets that are within the same datacenter, you should <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">enable Private Networking</a> on all of them. This will allow you to use the private network interface, <code>eth1</code>, for the encrypted VPN communication. The provided Playbook assumes that every VPN node will use the same network device name.</p>

<h2 id="download-ansible-tinc-playbook">Download Ansible-Tinc Playbook</h2>

<p>Once you're ready to get started, use <code>git clone</code> to download a copy of the Playbook. We'll clone it to our home directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">git clone https://github.com/thisismitch/ansible-tinc
</li></ul></code></pre>
<p>Now change to the newly-downloaded <code>ansible-tinc</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ansible-tinc
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> The rest of this tutorial assumes that you are in the <code>ansible-tinc</code> directory, on your local machine. All Ansible commands must be executed from this directory. Also, all files referenced, with the exception of <code>/etc/hosts</code>, are relative to this path—e.g. <code>hosts</code> refers to <code>~/ansible-tinc/hosts</code>.<br /></span></p>

<p>Next, we will show you how to use the Playbook to create your mesh VPN. If you are familiar with Ansible, you may want to take some time to browse the contents of the Playbook. Essentially, it installs and configures a mesh VPN using Tinc, and it also adds convenience entries into each server's <code>/etc/hosts</code>.</p>

<h2 id="create-host-inventory-file">Create Host Inventory File</h2>

<p>Before running the Playbook, you must create a <code>hosts</code> file that contains information about the servers you want to include in your Tinc VPN. We'll go over the contents of the hosts file now.</p>
<div class="code-label " title="~/ansible-tinc/hosts example">~/ansible-tinc/hosts example</div><pre class="code-pre "><code langs="">[vpn]
node01 vpn_ip=10.0.0.1 ansible_host=192.0.2.55
node02 vpn_ip=10.0.0.2 ansible_host=192.0.2.240
node03 vpn_ip=10.0.0.3 ansible_host=198.51.100.4
node04 vpn_ip=10.0.0.4 ansible_host=198.51.100.36

[removevpn]
</code></pre>
<p>The first line, <code>[vpn]</code>, specifies that the host entries directly below it are part of the "vpn" group. Members of this group will have the Tinc mesh VPN configured on them.</p>

<ul>
<li>The first column is where you set the inventory name of a host, "node01" in the first line of the example, how Ansible will refer to the host. This value is used to configure Tinc connections, and to generate <code>/etc/hosts</code> entries. Do not use hyphens here, as Tinc does not support them in host names</li>
<li><code>vpn_ip</code> is the IP address that the node will use for the VPN. Assign this to the IP address that you want the server to use for its VPN connections</li>
<li><code>ansible_host</code> must be set to a value that your local machine can reach the node at (i.e. a real IP address or hostname)</li>
</ul>

<p>Therefore, in the example, we have four hosts that we want to configure in a mesh VPN that looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/tinc/ansible-tinc-mesh-2.png" alt="Example Mesh VPN Diagram" /></p>

<p>Once your <code>hosts</code> file contains all of the servers you want to include in your VPN, save your changes. Be sure that it does not contain duplicate entries (hostnames, <code>vpn_ip</code> addresses, or <code>ansible_host</code> values).</p>

<p>At this point, you should test that Ansible can connect to all of the hosts in your inventory file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible all -m ping
</li></ul></code></pre>
<p>They should all respond with a green "SUCCESS" message. If any of the connections fail, check your hosts file for errors and ensure that all of the servers in question meet the requirements listed in the <a href="https://indiareads/community/tutorials/how-to-use-ansible-and-tinc-vpn-to-secure-your-server-infrastructure#prerequisites">prerequisites section</a> before moving on.</p>

<h2 id="review-group-variables">Review Group Variables</h2>

<p>Before running the Playbook, you may want to review the contents of the <code>/group_vars/all</code> file:</p>
<div class="code-label " title="/group_vars/all">/group_vars/all</div><pre class="code-pre line_numbers"><code langs=""><ul class="prefixed"><li class="line" prefix="1">---
</li><li class="line" prefix="2">
</li><li class="line" prefix="3">netname: <span class="highlight">nyc3</span>
</li><li class="line" prefix="4">physical_ip: "{{ ansible_<span class="highlight">eth1</span>.ipv4.address }}"
</li><li class="line" prefix="5">
</li><li class="line" prefix="6">vpn_interface: <span class="highlight">tun0</span>
</li><li class="line" prefix="7">
</li><li class="line" prefix="8">vpn_netmask: <span class="highlight">255.255.255.0</span>
</li><li class="line" prefix="9">vpn_subnet_cidr_netmask: 32
</li></ul></code></pre>
<p>The two most important variables are <code>physical_ip</code> and <code>vpn_netmask</code>:</p>

<ul>
<li><code>physical_ip</code> specifies which IP address you want tinc to bind to. Here, we are leveraging an <em>Ansible Fact</em> to set it to the IP address of the <code>eth1</code> network device. On IndiaReads, <code>eth1</code> is the private network interface, so <em>Private Networking</em> must be enabled unless you would rather use the public network interface, <code>eth0</code>, by changing its value to <code>{{ ansible_eth0.ipv4.address }}</code></li>
<li><code>vpn_netmask</code> specifies the netmask that the will be applied to the VPN interface. By default, it's set to <code>255.255.255.0</code>, which means that each <code>vpn_ip</code> is a Class C address which can only communicate with other hosts within the same subnet. For example, a <code>10.0.0.x</code> will not be able to communicate with a <code>10.0.1.x</code> host unless the subnet is enlarged by changing <code>vpn_netmask</code> to something like <code>255.255.0.0</code>.</li>
</ul>

<p><span class="note"><strong>Note:</strong> The security benefits of the VPN can be extended to your servers over the public Internet, but keep in mind that the communication will still have the same latency and bandwidth limitations as the non-VPN connection.<br /></span></p>

<p>Here's an explanation of the other settings:</p>

<ul>
<li><code>netname</code> specifies the tinc netname. It's set to <code>nyc3</code> by default.</li>
<li><code>vpn_interface</code> is the name of the virtual network interface that tinc will use. It is <code>tun0</code> by default.</li>
<li><code>vpn_subnet_cidr_netmask</code> is set to 32, which indicates a single-host subnet (point-to-point) because we are configuring a mesh VPN. Don't change this value.</li>
</ul>

<p>Once you're done reviewing the group variables, you should be ready move on to the next step.</p>

<h2 id="deploy-tinc-vpn">Deploy Tinc VPN</h2>

<p>Now that have created an inventory hosts file and reviewed the group variables, you're ready to deploy Tinc and set up the VPN across your servers by running the Playbook.</p>

<p>From the <code>ansible-tinc</code> directory, run this command to run the Playbook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook site.yml
</li></ul></code></pre>
<p>While the Playbook runs, it should provide the output of each task that is executed. If everything is configured correctly, you should see several <code>ok</code> and <code>changed</code> statuses, and zero <code>failed</code> statuses:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="PLAY RECAP *********************************************************************">PLAY RECAP *********************************************************************</div>node01                     : ok=18   changed=15   unreachable=0    failed=0
node02                     : ok=18   changed=15   unreachable=0    failed=0
node03                     : ok=21   changed=19   unreachable=0    failed=0
node04                     : ok=21   changed=19   unreachable=0    failed=0
</code></pre>
<p>If there are no failed tasks, all of the hosts in the inventory file should be able to communicate with each other over the VPN network.</p>

<h2 id="test-the-vpn">Test the VPN</h2>

<p>Log in to your first host and ping the second host:</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="node01$">ping 10.0.0.2
</li></ul></code></pre>
<p>Because the Playbook automatically creates <code>/etc/hosts</code> entries that point the inventory hostname to the VPN IP address of each member, you can also do something like this (assuming one of your hosts is named <code>node02</code> in the Ansible <code>hosts</code> file):</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="node01$">ping node02
</li></ul></code></pre>
<p>Either way, you should see valid ping responses:</p>
<pre class="code-pre "><code langs="">[secondary_label Output:
PING node02 (10.0.0.2) 56(84) bytes of data.
64 bytes from node02 (10.0.0.2): icmp_seq=1 ttl=64 time=1.42 ms
64 bytes from node02 (10.0.0.2): icmp_seq=2 ttl=64 time=1.03 ms
...
</code></pre>
<p>Feel free to test the VPN connections between the other nodes.</p>

<p><span class="note"><strong>Note:</strong> Tinc uses port <code>655</code>. If your ping test isn't working, be sure that the firewall of each node allows the appropriate traffic over the real network device that the VPN is using.<br /></span></p>

<p>Once you complete your testing, your mesh VPN is ready to be used!</p>

<h2 id="configure-services-and-applications">Configure Services and Applications</h2>

<p>Now that your mesh VPN is set up, you need to be sure to configure your backend services and applications to use it (where appropriate). This means that any services that should be communicating over the VPN need to use the appropriate VPN IP addresses (<code>vpn_ip</code>) instead of the normal private IP address.</p>

<p>For example, assume you're running a LEMP stack with Nginx on <strong>node01</strong> and a MySQL database on <strong>node02</strong>. MySQL should be configured to bind to the VPN IP address <code>10.0.0.2</code>, the PHP application should connect to the database at <code>10.0.0.2</code>, and Nginx should listen on <code>192.0.2.55</code> (node01's public IP address).</p>

<p>For another example, if <strong>node01</strong>, <strong>node02</strong>, and <strong>node03</strong> are nodes in an Elasticsearch cluster, Elasticsearch should be configured to use <code>10.0.0.1</code>, <code>10.0.0.2</code>, and <code>10.0.0.3</code> as the node IP addresses. Likewise, any clients that connect to the cluster should use the VPN addresses as well.</p>

<h3 id="firewall-considerations">Firewall Considerations</h3>

<p>You may need to update your firewall rules to allow traffic on the VPN network device, "tun0", or the VPN IP addresses.</p>

<h2 id="how-to-add-or-remove-servers">How to Add or Remove Servers</h2>

<h3 id="add-new-servers">Add New Servers</h3>

<p>All servers listed in the the <code>[vpn]</code> group in the <code>hosts</code> file will be part of the VPN. To add new VPN members, simply add the new servers to the <code>[vpn]</code> group then re-run the Playbook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook site.yml
</li></ul></code></pre>
<h3 id="remove-servers">Remove Servers</h3>

<p>To remove VPN members, move <code>hosts</code> entries of the servers you want to remove under the <code>[removevpn]</code> group towards the bottom of the file.</p>

<p>For example, if we wanted to remove <strong>node04</strong>, the <code>hosts</code> file would look like this:</p>
<div class="code-label " title="hosts — remove node04 from VPN">hosts — remove node04 from VPN</div><pre class="code-pre "><code langs="">[vpn]
node01 vpn_ip=10.0.0.1 ansible_host=192.0.2.55
node02 vpn_ip=10.0.0.2 ansible_host=192.0.2.240
node03 vpn_ip=10.0.0.3 ansible_host=198.51.100.4

[removevpn]
node04 vpn_ip=10.0.0.4 ansible_host=198.51.100.36
</code></pre>
<p>Save the hosts file. Note that the <code>vpn_ip</code> is optional and unused for <code>[removevpn]</code> group members.</p>

<p>Then re-run the Playbook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook site.yml
</li></ul></code></pre>
<p>This will stop Tinc and delete the Tinc configuration and host key files from the members of the <code>[removevpn]</code> group, removing them from the VPN.</p>

<p>Note that removing hosts from the VPN will result in orphaned tinc hosts files and /etc/hosts entries on the remaining VPN members. This should not affect anything unless you later add new servers to the VPN but reuse the decommissioned names. Delete the appropriate <code>/etc/hosts</code> entries on each server, if this is a problem for you.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Your server infrastructure should now be secure by a mesh VPN, by using Tinc and Ansible! If you need to modify the playbook to meet your specific needs, feel free to <a href="https://github.com/thisismitch/ansible-tinc">fork it on GitHub</a>.</p>

<p>Good luck!</p>

    