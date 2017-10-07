<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Memcached is a distributed object caching system which stores information in memory, rather than on disk, for faster access. PHP's Memcache module can be used to handle sessions which would otherwise be stored on the file system. Storing PHP sessions in Memcached has the advantage of being able to distribute them to multiple cloud servers running Memcached, so as to maintain session redundancy.</p>

<p>Without this Memcached setup, if your application is being load balanced on multiple servers, it would be necessary to configure session stickiness on the load balancer. This maintains user experience and prevents them from being logged off suddenly. Configuring Memcached to handle sessions will ensure all cloud servers in the Memcached pool have the same set of session data, which eliminates the need to be sticky with one server to preserve the session.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>This tutorial assumes you are familiar with <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">setting up LAMP servers in Ubuntu</a>.<br />
This setup will make use of 3 Droplets with the <strong>Ubuntu 14.04</strong> image.</p>

<p><strong>Droplet 1</strong>  </p>

<ul>
<li>Name: lamp01</li>
<li>Public IP: 1.1.1.1<br /></li>
<li>Private IP: 10.1.1.1</li>
</ul>

<p><strong>Droplet 2</strong></p>

<ul>
<li>Name: lamp02</li>
<li>Public IP: 2.2.2.2<br /></li>
<li>Private IP: 10.2.2.2</li>
</ul>

<p><strong>Droplet 3</strong> </p>

<ul>
<li>Name: lamp03</li>
<li>Public IP: 3.3.3.3<br /></li>
<li>Private IP: 10.3.3.3</li>
</ul>

<p>Ensure that the <a href="https://indiareads/community/tutorials/how-to-set-up-and-use-digitalocean-private-networking">Private Networking</a> checkbox is ticked when creating the Droplets. Also, make note of the private IP addresses as we will need them later.</p>

<p>Install LAMP on all three servers. </p>

<p>First, update the repository and install Apache.</p>
<pre class="code-pre "><code langs="">apt-get update
apt-get install apache2
</code></pre>
<p>Install PHP and Apache's mod_php extension.</p>
<pre class="code-pre "><code langs="">apt-get install php5 libapache2-mod-php5 php5-mcrypt
</code></pre>
<p>For more information, see <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">this article</a>.</p>

<h2 id="step-one-install-memcache-packages">Step One - Install Memcache Packages</h2>

<p>On <strong>lamp01</strong>, install the Memcached daemon and PHP's Memcache module.</p>
<pre class="code-pre "><code langs="">apt-get install php5-memcache memcached
</code></pre>
<p>PHP has two packages: php5-memcache and php5-memcached (notice the "d" at the end). We will be using the first package (memcache) as it is lighter without any dependencies. Read the comparison between <a href="https://code.google.com/p/memcached/wiki/PHPClientComparison">memcache and memcached</a>.</p>

<p>The Memcached service listens only on localhost (127.0.0.1). This has to be changed to accept connections from the private network.</p>
<pre class="code-pre "><code langs="">nano /etc/memcached.conf
</code></pre>
<p>Find the following line:</p>
<pre class="code-pre "><code langs="">-l 127.0.0.1
</code></pre>
<p>Change it to listen on this server's private IP address.</p>
<pre class="code-pre "><code langs="">-l <span class="highlight">10.1.1.1</span>
</code></pre>
<p>Restart the <code>memcached</code> service.</p>
<pre class="code-pre "><code langs="">service memcached restart
</code></pre>
<p>Repeat these steps on the other two servers, replacing <code>127.0.0.1</code> with the appropriate private IP address.</p>

<p><strong>lamp02</strong></p>
<pre class="code-pre "><code langs="">-l <span class="highlight">10.2.2.2</span>
</code></pre>
<p><strong>lamp03</strong></p>
<pre class="code-pre "><code langs="">-l <span class="highlight">10.3.3.3</span>
</code></pre>
<p>Restart the <code>memcached</code> service on the second two servers.</p>

<h2 id="step-two-set-memcache-as-php-39-s-session-handler">Step Two - Set Memcache as PHP's Session Handler</h2>

<p>On <strong>lamp01</strong>, open the <code>php.ini</code> file for editing.</p>
<pre class="code-pre "><code langs="">nano /etc/php5/apache2/php.ini
</code></pre>
<p>This file is located at <code>/etc/php5/fpm/php.ini</code> on PHP-FPM installations.</p>

<p>Find the following configuration directives:</p>
<pre class="code-pre "><code langs="">session.save_handler =
session.save_path =
</code></pre>
<p>Modify them to use Memcache as follows. Use all three private IP addresses in the <code>session.save_path</code>.</p>
<pre class="code-pre "><code langs="">session.save_handler = memcache
session.save_path = 'tcp://<span class="highlight">10.1.1.1</span>:11211,tcp://<span class="highlight">10.2.2.2</span>:11211,tcp://<span class="highlight">10.3.3.3</span>:11211'
</code></pre>
<p>You may need to uncomment <code>session.save_path</code> by removing the semicolon at the beginning. Remember to enter the port number <strong>11211</strong> after each IP address, as Memcached listens on this port.</p>

<p>Add exactly the same settings on the other two servers.</p>

<p>On <strong>lamp02</strong>:</p>
<pre class="code-pre "><code langs="">session.save_handler = memcache
session.save_path = 'tcp://<span class="highlight">10.1.1.1</span>:11211,tcp://<span class="highlight">10.2.2.2</span>:11211,tcp://<span class="highlight">10.3.3.3</span>:11211'
</code></pre>
<p>On <strong>lamp03</strong>:</p>
<pre class="code-pre "><code langs="">session.save_handler = memcache
session.save_path = 'tcp://<span class="highlight">10.1.1.1</span>:11211,tcp://<span class="highlight">10.2.2.2</span>:11211,tcp://<span class="highlight">10.3.3.3</span>:11211'
</code></pre>
<p>This configuration has to be exactly same on all the Droplets for session sharing to work properly.</p>

<h2 id="step-three-configure-memcache-for-session-redundancy">Step Three - Configure Memcache for Session Redundancy</h2>

<p>On <strong>lamp01</strong>, edit the <code>memcache.ini</code> file.</p>
<pre class="code-pre "><code langs="">nano /etc/php5/mods-available/memcache.ini
</code></pre>
<p>Add the following configuration directives to the end of this file.</p>
<pre class="code-pre "><code langs="">memcache.allow_failover=1
memcache.session_redundancy=4
</code></pre>
<p>The <code>memcache.session_redundancy</code> directive must be equal to the number of memcached servers + 1 for the session information to be replicated to all the servers. This is due to a <a href="https://bugs.php.net/bug.php?id=58585">bug in PHP</a>.</p>

<p>These directives enable session failover and redundancy, so PHP writes the session information to all servers specified in <code>session.save_path</code>; similar to a RAID-1 setup.</p>

<p>Restart the web server or the PHP FPM daemon depending on what is being used.</p>
<pre class="code-pre "><code langs="">service apache2 reload
</code></pre>
<p>Repeat these steps exactly on <strong>lamp02</strong> and <strong>lamp03</strong>.</p>

<h2 id="step-four-test-session-redundancy">Step Four - Test Session Redundancy</h2>

<p>To test this setup create the following PHP script on all the Droplets.</p>

<p><span class="highlight">/var/www/html/session.php</span></p>
<pre class="code-pre "><code langs=""><?php
    header('Content-Type: text/plain');
    session_start();
    if(!isset($_SESSION['visit']))
    {
        echo "This is the first time you're visiting this server\n";
        $_SESSION['visit'] = 0;
    }
    else
            echo "Your number of visits: ".$_SESSION['visit'] . "\n";

    $_SESSION['visit']++;

    echo "Server IP: ".$_SERVER['SERVER_ADDR'] . "\n";
    echo "Client IP: ".$_SERVER['REMOTE_ADDR'] . "\n";
    print_r($_COOKIE);
?>
</code></pre>
<p>This script is for testing only and can be removed once the Droplets are set up.</p>

<p>Access this file on the first Droplet using curl and extract the cookie information.</p>
<pre class="code-pre "><code langs="">curl -v -s http://<span class="highlight">1.1.1.1</span>/session.php 2>&1 | grep 'Set-Cookie:'
</code></pre>
<p>This will return output similar to the following.</p>
<pre class="code-pre "><code langs="">< Set-Cookie: PHPSESSID=8lebte2dnqegtp1q3v9pau08k4; path=/
</code></pre>
<p>Copy the <code>PHPSESSID</code> cookie and send the request to the other Droplets using this cookie. This session will be removed by PHP if no requests are made for 1440 seconds, so make sure you complete the test within this timeframe. Read about PHP's <a href="http://php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime">session.gc-maxlifetime</a> to learn more about this.</p>
<pre class="code-pre "><code langs="">curl --cookie "<span class="highlight">PHPSESSID=8lebte2dnqegtp1q3v9pau08k4</span>" http://<span class="highlight">1.1.1.1</span>/session.php http://<span class="highlight">2.2.2.2</span>/session.php http://<span class="highlight">3.3.3.3</span>/session.php
</code></pre>
<p>You will find that the session is being carried over across all Droplets.</p>
<pre class="code-pre "><code langs="">Your number of visits: 1
Server IP: 1.1.1.1
Client IP: 117.193.121.130
Array
(
    [PHPSESSID] => 8lebte2dnqegtp1q3v9pau08k4
)
Your number of visits: 2
Server IP: 2.2.2.2
Client IP: 117.193.121.130
Array
(
    [PHPSESSID] => 8lebte2dnqegtp1q3v9pau08k4
)
Your number of visits: 3
Server IP: 3.3.3.3
Client IP: 117.193.121.130
Array
(
    [PHPSESSID] => 8lebte2dnqegtp1q3v9pau08k4
)
</code></pre>
<p>To test failover, stop the <code>memcached</code> service and access this file on it.</p>
<pre class="code-pre "><code langs="">service memcached stop
</code></pre>
<p>The Droplet transparently uses the session information stored on the other two servers.</p>
<pre class="code-pre "><code langs="">curl --cookie "<span class="highlight">PHPSESSID=8lebte2dnqegtp1q3v9pau08k4</span>" http://<span class="highlight">1.1.1.1</span>/session.php
</code></pre>
<p>Output:    </p>
<pre class="code-pre "><code langs="">Your number of visits: 4
Server IP: 1.1.1.1
Client IP: 117.193.121.130
Array
(
    [PHPSESSID] => 8lebte2dnqegtp1q3v9pau08k4
)
</code></pre>
<p>Now a load balancer can be configured to distribute requests evenly without the hassle of configuring session stickyness.</p>

<p>Start <code>memcached</code> again once you are done testing:</p>
<pre class="code-pre "><code langs="">service memcached start
</code></pre>
<h2 id="step-five-secure-memcached-with-iptables">Step Five - Secure Memcached with IPTables</h2>

<p>Even if Memcached is using the private network, other IndiaReads users in the same data center can connect to your Droplet if they know your private IP. So, we will <a href="https://indiareads/community/tutorials/how-to-set-up-a-firewall-using-iptables-on-ubuntu-14-04">set up IPTables rules</a> to allow only the cloud servers in our Memcached pool to communicate with each other.</p>

<p>We are doing this step after testing for session redundancy so that it is easier to troubleshoot problems which may arise if incorrect rules are applied.</p>

<p>Create firewall rules on <strong>lamp01</strong> with the private IP addresses of <strong>lamp02</strong> and <strong>lamp03</strong>.</p>
<pre class="code-pre "><code langs="">iptables -A INPUT -s <span class="highlight">10.2.2.2</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
iptables -A INPUT -s <span class="highlight">10.3.3.3</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
</code></pre>
<p>On a typical LAMP server the following would be the complete set of rules:</p>
<pre class="code-pre "><code langs="">iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A INPUT -p tcp -m state --state NEW -m tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp -m state --state NEW -m tcp --dport 443 -j ACCEPT
iptables -A INPUT -p tcp -m state --state NEW -m tcp --dport 22 -j ACCEPT
iptables -A INPUT -s <span class="highlight">10.2.2.2</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
iptables -A INPUT -s <span class="highlight">10.3.3.3</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
iptables -A INPUT -i lo -j ACCEPT
iptables -A INPUT -p icmp -m icmp --icmp-type 8 -j ACCEPT
iptables -P INPUT DROP
</code></pre>
<p>Enter the firewall rules on <strong>lamp02</strong> with the private IP addresses of <strong>lamp01</strong> and <strong>lamp03</strong>.</p>
<pre class="code-pre "><code langs="">iptables -A INPUT -s <span class="highlight">10.1.1.1</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
iptables -A INPUT -s <span class="highlight">10.3.3.3</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
</code></pre>
<p>Do the same on <strong>lamp03</strong> with the private IP addresses of <strong>lamp01</strong> and <strong>lamp02</strong>.</p>
<pre class="code-pre "><code langs="">iptables -A INPUT -s <span class="highlight">10.1.1.1</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
iptables -A INPUT -s <span class="highlight">10.2.2.2</span> -i eth1 -p tcp -m state --state NEW -m tcp --dport 11211 -j ACCEPT
</code></pre>
<p>Repeat the tests in <strong>Step 4</strong> to confirm that the firewall is not blocking our traffic.</p>

<h3 id="additional-reading">Additional Reading</h3>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">How To Install LAMP stack on Ubuntu 14.04</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-isolate-servers-within-a-private-network-using-iptables">How To Isolate Servers Within A Private Network Using IPTables</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-store-php-sessions-in-memcached-on-a-centos-vps">How To Store PHP Sessions in Memcached</a></li>
</ul>

    