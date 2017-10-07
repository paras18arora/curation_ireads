<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Introduction</h3>

<p>One of the first lines of defense in securing your cloud server is a functioning firewall. In the past, this was often done through complicated and arcane utilities. There is a lot of functionality built into these utilities, iptables being the most popular nowadays, but they require a decent effort on behalf of the user to learn and understand them. Firewall rules are not something you want yourself second-guessing.</p> 

<p>To this end, UFW is a considerably easier-to-use alternative.</p>

<h2>What is UFW?</h2>

<p>UFW, or Uncomplicated Firewall, is a front-end to iptables. Its main goal is to make managing your firewall drop-dead simple and to provide an easy-to-use interface. It’s well-supported and popular in the Linux community—even installed by default in a lot of distros. As such, it’s a great way to get started securing your sever.</p>

<h2>Before We Get Started</h2>

<p>First, obviously, you want to make sure UFW is installed. It should be installed by default in Ubuntu, but if for some reason it’s not, you can install the package using aptitude or apt-get using the following commands:</p>

 <pre>sudo aptitude install ufw</pre>

<p>or</p> 

 <pre>sudo apt-get install ufw</pre>

<h2>Check the Status</h2>

<p>You can check the status of UFW by typing:</p>

 <pre>sudo ufw status</pre> 

<p>Right now, it will probably tell you it is inactive. Whenever ufw is active, you’ll get a listing of the current rules that looks similar to this:</p>

<pre>Status: active

To               Action      From
--               ------      ----
22               ALLOW       Anywhere</pre>

<h2>Using IPv6 with UFW</h2>

<p>If your VPS is configured for IPv6, ensure that UFW is configured to support IPv6 so that will configure both your IPv4 and IPv6 firewall rules. To do this, open the UFW configuration with this command:</p>

<pre>sudo vi /etc/default/ufw</pre>

<p>Then make sure "IPV6" is set to "yes", like so:</p>

<pre>IPV6=<span class="highlight">yes</span></pre>

<p>Save and quit. Then restart your firewall with the following commands:</p>

<pre>sudo ufw disable
sudo ufw enable</pre>

<p>Now UFW will configure the firewall for both IPv4 and IPv6, when appropriate.</p>

<h2>Set Up Defaults</h2>

<p>One of the things that will make setting up any firewall easier is to define some default rules for allowing and denying connections. UFW’s defaults are to deny all incoming connections and allow all outgoing connections. This means anyone trying to reach your cloud server would not be able to connect, while any application within the server would be able to reach the outside world. To set the defaults used by UFW, you would use the following commands:</p>

 <pre>sudo ufw default deny incoming</pre>
 
<p>and</p>

 <pre>sudo ufw default allow outgoing</pre>

<p>Note: if you want to be a little bit more restrictive, you can also deny all outgoing requests as well. The necessity of this is debatable, but if you have a public-facing cloud server, it could help prevent against any kind of remote shell connections. It does make your firewall more cumbersome to manage because you’ll have to set up rules for all outgoing connections as well. You can set this as the default with the following:</p>

 <pre>sudo ufw default deny outgoing</pre> 

<h2>Allow Connections</h2>

<p>The syntax is pretty simple. You change the firewall rules by issuing commands in the terminal. If we turned on our firewall now, it would deny all incoming connections. If you’re connected over SSH to your cloud server, that would be a problem because you would be locked out of your server. Let’s enable SSH connections to our server to prevent that from happening:</p>

 <pre>sudo ufw allow ssh</pre> 

<p>As you can see, the syntax for adding services is pretty simple. UFW comes with some defaults for common uses. Our SSH command above is one example. It’s basically just shorthand for:</p>

 <pre>sudo ufw allow 22/tcp</pre> 

<p>This command allows a connection on port 22 using the TCP protocol. If our SSH server is running on port 2222, we could enable connections with the following command:</p>

 <pre>sudo ufw allow 2222/tcp</pre>

<h3>Other Connections We Might Need</h3>

<p>Now is a good time to allow some other connections we might need. If we’re securing a web server with FTP access, we might need these commands:</p>
<p>
<code>sudo ufw allow www</code>   or  <code>sudo ufw allow 80/tcp</code>
<code>sudo ufw allow ftp</code>  or  <code>sudo ufw allow 21/tcp</code> 
</p>

<p>You mileage will vary on what ports and services you need to open. There will probably be a bit of testing necessary. In addition, you want to make sure you leave your SSH connection allowed.</p>

<h3>Port Ranges</h3>

<p>You can also specify port ranges with UFW. To allow ports 1000 through 2000, use the command:</p>

 <pre>sudo ufw allow 1000:2000/tcp</pre>

<p>If you want UDP:</p>

 <pre>sudo ufw allow 1000:2000/udp</pre> 

<h3>IP Addresses</h3>

<p>You can also specify IP addresses. For example, if I wanted to allow connections from a specific IP address (say my work or home address), I’d use this command:</p>

<pre>sudo ufw allow from 192.168.255.255</pre>

<h2>Denying Connections</h2>

<p>Our default set up is to deny all incoming connections. This makes the firewall rules easier to administer since we are only selectively allowing certain ports and IP addresses through. However, if you want to flip it and open up all your server’s ports (not recommended), you could allow all connections and then restrictively deny ports you didn’t want to give access to by replacing “allow” with “deny” in the commands above. For example:</p>

 <pre>sudo ufw allow 80/tcp</pre>

<p>would allow access to port 80 while:</p>

 <pre>sudo ufw deny 80/tcp</pre>

<p>would deny access to port 80.</p> 

<h2>Deleting Rules</h2>

<p>There are two options to delete rules. The most straightforward one is to use the following syntax:</p>

<pre>sudo ufw delete allow ssh</pre>

<p>As you can see, we use the command “delete” and input the rules you want to eliminate after that. Other examples include:</p>

<pre>sudo ufw delete allow 80/tcp</pre>

<p>or</p>

<pre>sudo ufw delete allow 1000:2000/tcp</pre>

<p>This can get tricky when you have rules that are long and complex.</p>

<p>A simpler, two-step alternative is to type:</p>

<pre>sudo ufw status numbered</pre>

<p>which will have UFW list out all the current rules in a numbered list. Then, we issue the command:</p>

<pre>sudo ufw delete [number]</pre>

<p>where “[number]” is the line number from the previous command.</p>

<h2>Turn It On</h2>

<p>After we’ve gotten UFW to where we want it, we can turn it on using this command (remember: if you’re connecting via SSH, make sure you’ve set your SSH port, commonly port 22, to be allowed to receive connections):</p>

<pre>sudo ufw enable</pre> 

<p>You should see the command prompt again if it all went well. You can check the status of your rules now by typing:</p>

<pre>sudo ufw status</pre>

<p>or</p>

<pre>sudo ufw status verbose</pre>

<p>for the most thorough display.</p>

<p>To turn UFW off, use the following command:</p>

<pre>sudo ufw disable</pre>

<h2>Reset Everything</h2>

<p>If, for whatever reason, you need to reset your cloud server’s rules to their default settings, you can do this by typing this command:</p>

<pre>sudo ufw reset</pre> 

<h2>Conclusion</h2>

<p>You should now have a cloud server that is configured properly to restrict access to a subset of ports or IP addresses.</p>

<div class="author">Article Submitted by: Shaun Lewis</div></div>
    