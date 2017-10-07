<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Step 1 - Spin up a CentOS 6.3 x64 droplet</h3>

<img src="https://assets.digitalocean.com/articles/community/CentOS6-Squid1.png" width="680" />

<h3>Step 2 - Install Squid</h3>

<pre>
yum -y install squid
chkconfig squid on
</pre>

<h3>Step 3 - Setup Access Restrictions</h3>

<p>Since this Squid proxy would allow anyone using it to make connections from your droplet's IP address, you would want to restrict access to it.</p>

<p>You can register a free dynamic IP from services like noip.com</p>

<img src="https://assets.digitalocean.com/articles/community/DynDNSClient1.png" width="680" />

<p>If you would like to use this Squid proxy from your phone, you would have to install a dynamic DNS update client.</p>

<p>You can use applications like <I>Dynamic DNS Client for Android</I>, or <i>FreeDynPro for iOS</i>.</p>

<img src="https://assets.digitalocean.com/articles/community/DynDNSClient2.png" width="680" />

<p>Once you have a dynamic IP hostname, you can update it from your router at home, mobile device, or an API call.</p>

<p>This hostname should be added to <b>/etc/squid/squid.conf</b>.  Edit the file and add your hostname (nyproxy1.no-ip.org in our case):</p>

<pre>
acl localnet src nyproxy1.no-ip.org
</pre>

<p>Setup a crontab that reloads Squid every hour, in case your IP address changes:</p>

<pre>
echo 0 */1 * * * service squid reload >> /var/spool/cron/root
</pre>

<h3>Step 4 - Configure Squid Proxy</h3>

<p>By default, Squid listens on port 3128.  If you would like to use a different port, modify <b>/etc/squid/squid.conf</b></p>

<pre>
http_port 3128
</pre>

<p>If you would like to browse through this Squid proxy and not have it detected as a proxy, setup anonymous settings by adding these lines to <b>/etc/squid/squid.conf</b>:</p>

<pre>
via off
forwarded_for off

request_header_access Allow allow all 
request_header_access Authorization allow all 
request_header_access WWW-Authenticate allow all 
request_header_access Proxy-Authorization allow all 
request_header_access Proxy-Authenticate allow all 
request_header_access Cache-Control allow all 
request_header_access Content-Encoding allow all 
request_header_access Content-Length allow all 
request_header_access Content-Type allow all 
request_header_access Date allow all 
request_header_access Expires allow all 
request_header_access Host allow all 
request_header_access If-Modified-Since allow all 
request_header_access Last-Modified allow all 
request_header_access Location allow all 
request_header_access Pragma allow all 
request_header_access Accept allow all 
request_header_access Accept-Charset allow all 
request_header_access Accept-Encoding allow all 
request_header_access Accept-Language allow all 
request_header_access Content-Language allow all 
request_header_access Mime-Version allow all 
request_header_access Retry-After allow all 
request_header_access Title allow all 
request_header_access Connection allow all 
request_header_access Proxy-Connection allow all 
request_header_access User-Agent allow all 
request_header_access Cookie allow all 
request_header_access All deny all
</pre>

<h3>Step 5 - Start Squid proxy service</h3>

<pre>
service squid start
</pre>

<h3>Step 6 - Modify your browser's proxy settings</h3>

<p>Add your droplet's IP address and port to your browser's proxy settings.</p>

<h3>Step 7 - Verify Squid proxy works</h3>

<p>Navigate over to <a href="http://www.whatismyip.com/">whatismyip.com</a></p>

<img src="https://assets.digitalocean.com/articles/community/CentOS6-Squid4.png" width="680" />

<p>And you are all done!</p>

<div class="author">By Bulat Khamitov</div></div>
    