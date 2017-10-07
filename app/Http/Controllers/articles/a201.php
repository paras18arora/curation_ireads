<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/7ways-twitter-02.jpg?1426790322/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When setting up infrastructure, getting your applications up and running will often be your primary concern.  However, making your applications to function correctly without addressing the security needs of your infrastructure could have devastating consequences down the line.</p>

<p>In this guide, we will talk about some basic security practices that are best to configure before or as you set up your applications.</p>

<h2 id="ssh-keys">SSH Keys</h2>

<p>SSH keys are a pair of cryptographic keys that can be used to authenticate to an SSH server as an alternative to password-based logins.  A private and public key pair are created prior to authentication.  The private key is kept secret and secure by the user, while the public key can be shared with anyone.</p>

<p><img src="https://assets.digitalocean.com/articles/7_security_measures/1-ssh-key-auth.png" alt="SSH Keys diagram" /></p>

<p>To configure the SSH key authentication, you must place the user's public key on the server in a special directory.  When the user connects to the server, the server will ask for proof that the client has the associated private key.  The SSH client will use the private key to respond in a way that proves ownership of the private key.  The server will then let the client connect without a password.  To learn more about how SSH keys work, check out our article <a href="https://indiareads/community/tutorials/understanding-the-ssh-encryption-and-connection-process">here</a>.</p>

<h3 id="how-do-they-enhance-security">How Do They Enhance Security?</h3>

<p>With SSH, any kind of authentication, including password authentication, is completely encrypted.  However, when password-based logins are allowed, malicious users can repeatedly attempt to access the server.  With modern computing power, it is possible to gain entry to a server by automating these attempts and trying combination after combination until the right password is found.</p>

<p>Setting up SSH key authentication allows you to disable password-based authentication.  SSH keys generally have many more bits of data than a password, meaning that there are significantly more possible combinations that an attacker would have to run through.  Many SSH key algorithms are considered uncrackable by modern computing hardware simply because they would require too much time to run through possible matches.</p>

<h3 id="how-difficult-is-this-to-implement">How Difficult Is This to Implement?</h3>

<p>SSH keys are very easy to set up and are the recommended way to log into any Linux or Unix server environment remotely.  A pair of SSH keys can be generated on your machine and you can transfer the public key to your servers within a few minutes.</p>

<p>To learn about how to set up keys, follow <a href="https://indiareads/community/tutorials/how-to-set-up-ssh-keys--2">this guide</a>.  If you still feel that you need password authentication, consider implementing a solution like <a href="https://indiareads/community/tutorials/how-to-install-and-use-fail2ban-on-ubuntu-14-04">fail2ban</a> on your servers to limit password guesses.</p>

<h2 id="firewalls">Firewalls</h2>

<p>A firewall is a piece of software (or hardware) that controls what services are exposed to the network.  This means blocking or restricting access to every port except for those that should be publicly available.</p>

<p><img src="https://assets.digitalocean.com/articles/7_security_measures/2-firewall.png" alt="Firewall diagram" /></p>

<p>On a typical server, a number services may be running by default.  These can be categorized into the following groups:</p>

<ul>
<li>Public services that can be accesses by anyone on the internet, often anonymously.  A good example of this is a web server that might allow access to your site.</li>
<li>Private services that should only be accessed by a select group of authorized accounts or from certain locations.  An example of this may be a database control panel.</li>
<li>Internal services that should be accessible only from within the server itself, without exposing the service to the outside world.  For example, this may be a database that only accepts local connections.</li>
</ul>

<p>Firewalls can ensure that access to your software is restricted according to the categories above.  Public services can be left open and available to everyone and private services can be restricted based on different criteria.  Internal services can be made completely inaccessible to the outside world.  For ports that are not being used, access is blocked entirely in most configurations.</p>

<h3 id="how-do-they-enhance-security">How Do They Enhance Security?</h3>

<p>Firewalls are an essential part of any server configuration.  Even if your services themselves implement security features or are restricted to the interfaces you'd like them to run on, a firewall serves as an extra layer of protection.</p>

<p>A properly configured firewall will restrict access to everything except the specific services you need to remain open.  Exposing only a few pieces of software reduces the attack surface of your server, limiting the components that are vulnerable to exploitation.</p>

<h3 id="how-difficult-is-this-to-implement">How Difficult Is This to Implement?</h3>

<p>There are many firewalls available for Linux systems, some of which have a steeper learning curve than others.  In general though, setting up the firewall should only take a few minutes and will only need to happen during your server's initial setup or when you make changes in what services are offered on your computer.</p>

<p>A simple choice is the <a href="https://indiareads/community/tutorials/how-to-setup-a-firewall-with-ufw-on-an-ubuntu-and-debian-cloud-server">UFW firewall</a>.  Other options are to use <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">iptables</a> or the <a href="https://indiareads/community/tutorials/how-to-install-and-configure-config-server-firewall-csf-on-ubuntu">CSF firewall</a>.</p>

<h2 id="vpns-and-private-networking">VPNs and Private Networking</h2>

<p>Private networks are networks that are only available to certain servers or users.  For instance, in IndiaReads, private networking is available in some regions as a data-center wide network.  </p>

<p>A VPN, or virtual private network, is a way to create secure connections between remote computers and present the connection as if it were a local private network.  This provides a way to configure your services as if they were on a private network and connect remote servers over secure connections.</p>

<p><img src="https://assets.digitalocean.com/articles/7_security_measures/3-vpn.png" alt="VPN diagram" /></p>

<h3 id="how-do-they-enhance-security">How Do They Enhance Security?</h3>

<p>Utilizing private instead of public networking for internal communication is almost always preferable given the choice between the two.  However, since other users within the data center are able to access the same network, you still must implement additional measures to secure communication between your servers.</p>

<p>Using a VPN is, effectively, a way to map out a private network that only your servers can see.  Communication will be fully private and secure.  Other applications can be configured to pass their traffic over the virtual interface that the VPN software exposes.  This way, only services that are meant to be consumable by clients on the public internet need to be exposed on the public network.</p>

<h3 id="how-difficult-is-this-to-implement">How Difficult Is This to Implement?</h3>

<p>Utilizing private networks in a datacenter that has this capability is as simple as enabling the interface during your server's creation and configuring your applications and firewall to use the private network.  Keep in mind that data center-wide private networks share space with other servers that use the same network.</p>

<p>As for VPN, the initial setup is a bit more involved, but the increased security is worth it for most use-cases.  Each server on a VPN must be install and configure the shared security and configuration data needed to establish the secure connection.  After the VPN is up and running, applications must be configured to use the VPN tunnel.  To learn about setting up a VPN to securely connect your infrastructure, check out our <a href="https://indiareads/community/tutorials/how-to-secure-traffic-between-vps-using-openvpn">OpenVPN tutorial</a>.</p>

<h2 id="public-key-infrastructure-and-ssl-tls-encryption">Public Key Infrastructure and SSL/TLS Encryption</h2>

<p>Public key infrastructure, or PKI, refers to a system that is designed to create, manage, and validate certificates for identifying individuals and encrypting communication.  SSL or TLS certificates can be used to authenticate different entities to one another.  After authentication, they can also be used to established encrypted communication.</p>

<p><img src="https://assets.digitalocean.com/articles/7_security_measures/4-ssl-tls.png" alt="SSL diagram" /></p>

<h3 id="how-do-they-enhance-security">How Do They Enhance Security?</h3>

<p>Establishing a certificate authority and managing certificates for your servers allows each entity within your infrastructure to validate the other members identity and encrypt their traffic.  This can prevent man-in-the-middle attacks where an attacker imitates a server in your infrastructure to intercept traffic.</p>

<p>Each server can be configured to trust a centralized certificate authority.  Afterwards, any certificate that the authority signs can be implicitly trusted.  If the applications and protocols you are using to communicate support TLS/SSL encryption, this is a way of encrypting your system without the overhead of a VPN tunnel (which also often uses SSL internally).</p>

<h3 id="how-difficult-is-this-to-implement">How Difficult Is This to Implement?</h3>

<p>Configuring a certificate authority and setting up the rest of the public key infrastructure can involve quite a bit of initial effort.  Furthermore, managing certificates can create an additional administration burden when new certificates need to be created, signed, or revoked.</p>

<p>For many users, implementing a full-fledged public key infrastructure will make more sense as their infrastructure needs grow.  Securing communications between components using VPN may be a good stop gap measure until you reach a point where PKI is worth the extra administration costs.</p>

<h2 id="service-auditing">Service Auditing</h2>

<p>Up until now, we have discussed some technology that you can implement to improve your security.  However, a big portion of security is analyzing your systems, understanding the available attack surfaces, and locking down the components as best as you can.</p>

<p>Service auditing is a process of discovering what services are running on the servers in your infrastructure.  Often, the default operating system is configured to run certain services at boot.  Installing additional software can sometimes pull in dependencies that are also auto-started.</p>

<p><img src="https://assets.digitalocean.com/articles/7_security_measures/5-service-audit.png" alt="Service auditing diagram" /></p>

<p>Service auditing is a way of knowing what services are running on your system, which ports they are using for communication, and what protocols are accepted.  This information can help you configure your firewall settings.</p>

<h3 id="how-does-it-enhance-security">How Does It Enhance Security?</h3>

<p>Servers start many processes for internal purposes and to handle external clients.  Each of these represents an expanded attack surface for malicious users.  The more services that you have running, the greater chance there is of a vulnerability existing in your accessible software.</p>

<p>Once you have a good idea of what network services are running on your machine, you can begin to analyze these services.  Some questions that you will want to ask yourself for each one are:</p>

<ul>
<li>Should this service be running?</li>
<li>Is the service running on interfaces that it doesn't needs to?  Should it be bound to a single IP?</li>
<li>Are your firewall rules structured to allow legitimate traffic pass to this service?</li>
<li>Are your firewall rules blocking traffic that is not legitimate?</li>
<li>Do you have a method of receiving security alerts about vulnerabilities for each of these services?</li>
</ul>

<p>This type of service audit should be standard practice when configuring any new server in your infrastructure.</p>

<h3 id="how-difficult-is-this-to-implement">How Difficult Is This to Implement?</h3>

<p>Doing a basic service audit is incredibly simple.  You can find out which services are listening to ports on each interface by using the <code>netstat</code> command.  A simple example that shows the program name, PID, and addresses being used for listening for TCP and UDP traffic is:</p>
<pre class="code-pre "><code langs="">sudo netstat -plunt
</code></pre>
<p>You will see output that looks like this:</p>
<pre class="code-pre "><code langs="">Active Internet connections (only servers)
Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name
tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN      887/sshd        
tcp        0      0 0.0.0.0:80              0.0.0.0:*               LISTEN      919/nginx       
tcp6       0      0 :::22                   :::*                    LISTEN      887/sshd        
tcp6       0      0 :::80                   :::*                    LISTEN      919/nginx
</code></pre>
<p>The main columns you need to stay attention to are <code>Proto</code>, <code>Local Address</code>, and <code>PID/Program name</code>.  If the address is <code>0.0.0.0</code>, then the service is accepting connections on all interfaces. </p>

<h2 id="file-auditing-and-intrusion-detection-systems">File Auditing and Intrusion Detection Systems</h2>

<p>File auditing is the process of comparing the current system against a record of the files and file characteristics of your system when it is a known-good state.  This is used to detect changes to the system that may have been authorized.</p>

<p><img src="https://assets.digitalocean.com/articles/7_security_measures/6-file-audit.png" alt="File audit diagram" /></p>

<p>An intrusion detection system, or IDS, is a piece of software that monitors a system or network for unauthorized activity.  Many host-based IDS implementations use file auditing as a method of checking whether the system has changed.</p>

<h3 id="how-do-they-enhance-security">How Do They Enhance Security?</h3>

<p>Similar to the above service-level auditing, if you are serious about ensuring a secure system, it is very useful to be able to perform file-level audits of your system.  This can be done periodically by the administrator or as part of an automated processes in an IDS.</p>

<p>These strategies are some of the only ways to be absolutely sure that your filesystem has not been altered by some user or process.  For many reasons, intruders often wish to remain hidden so that they can continue to exploit the server for an extended period of time.  They might replace binaries with compromised versions.  Doing an audit of the filesystem will tell you if any of the files have been altered, allowing you to be confident in the integrity of your server environment.</p>

<h3 id="how-difficult-is-this-to-implement">How Difficult Is This to Implement?</h3>

<p>Implementing an IDS or conducting file audits can be quite an intensive process.  The initial configuration involves telling the auditing system about any non-standard changes you've made to the server and defining paths that should be excluded to create a baseline reading.</p>

<p>It also makes day-to-day operations more involved.  It complicates updating procedures as you will need to re-check the system prior to running updates and then recreate the baseline after running the update to catch changes to the software versions.  You will also need to offload the reports to another location so that an intruder cannot alter the audit to cover their tracks.</p>

<p>While this may increase your administration load, being able to check your system against a known-good copy is one of the only ways of ensuring that files have not been altered without your knowledge.  Some popular file auditing / intrusion detection systems are <a href="https://indiareads/community/tutorials/how-to-use-tripwire-to-detect-server-intrusions-on-an-ubuntu-vps">Tripwire</a> and <a href="https://indiareads/community/tutorials/how-to-install-aide-on-a-digitalocean-vps">Aide</a>.</p>

<h2 id="isolated-execution-environments">Isolated Execution Environments</h2>

<p>Isolating execution environments refers to any method in which individual components are run within their own dedicated space.</p>

<p><img src="https://assets.digitalocean.com/articles/7_security_measures/7-isolation.png" alt="Isolated environments diagram" /></p>

<p>This can mean separating out your discrete application components to their own servers or may refer to configuring your services to operate in <code>chroot</code> environments or containers.  The level of isolation depends heavily on your application's requirements and the realities of your infrastructure. </p>

<h3 id="how-do-they-enhance-security">How Do They Enhance Security?</h3>

<p>Isolating your processes into individual execution environments increases your ability to isolate any security problems that may arise.  Similar to how <a href="http://en.wikipedia.org/wiki/Bulkhead_(partition)">bulkheads</a> and compartments can help contain hull breaches in ships, separating your individual components can limit the access that an intruder has to other pieces of your infrastructure.</p>

<h3 id="how-difficult-is-this-to-implement">How Difficult Is This to Implement?</h3>

<p>Depending on the type of containment you choose, isolating your applications can be relatively simple.  By packaging your individual components in containers, you can quickly achieve some measure of isolation, but note that Docker does not consider its containerization a security feature.</p>

<p>Setting up a <code>chroot</code> environment for each piece can provide some level of isolation as well, but this also is not foolproof method of isolation as there are often ways of breaking out of a <code>chroot</code> environment.  Moving components to dedicated machines is the best level of isolation, and in many cases may be the easiest, but may cost more for the additional machines.</p>

<h2 id="conclusion">Conclusion</h2>

<p>The strategies outlined above are only some of the enhancements you can make to improve the security of your systems.  It is important to recognize that, while it's better late than never, security measures decrease in their effectiveness the longer you wait to implement them.  Security cannot be an afterthought and must be implemented from the start alongside the services and applications you are providing.</p>

    