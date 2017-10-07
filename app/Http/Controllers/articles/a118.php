<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h2>About cPanel</h2>

<p>cPanel is a convenient application that allows users to administer servers through a GUI interface instead of the traditional command line. Although the installation for cPanel is relatively simple, the script does take several hours to run.</p> 

<h3>Notes</h3>

<ul><li>Once cPanel is installed, it cannot be removed from the server without a complete server restore. cPanel does <i>not</i> offer an uninstaller</li>
<li>Additionally, cPanel is subject to a licensing fee which may come out to be around $200 a year. IndiaReads does not cover the cost of cPanel. You can find out more about cPanel pricing <a href="http://cPanel.net/plans-pricing/">here</a></li>

<h2>Setup</h2>

<p>Before installing cPanel on our droplet, we need to take two additional steps.</p>

<p>First we need to make sure that Perl is installed on the server</p>

<pre>sudo yum install perl</pre>

<p>After installing perl we need to take one more preliminary step. cPanel is very picky about making sure that server that it is installed on has a Fully Qualified Domain Name.  To that effect, we need to provide it with a valid hostname. Skipping this step will inevitably get you the following, very common, error.</p> 

<pre>2012-11-01 16:00:54  461 (ERROR): Your hostname () is not set properly. Please
2012-11-01 16:00:54  462 (ERROR): change your hostname to a fully qualified domain name,
2012-11-01 16:00:54  463 (ERROR): and re-run this installer.</pre>

<p>Luckily this error has a very easy solution. If you have a FQDN, you can type it in with the command:</p>

<pre>hostname <i>your FQDN</i></pre>

<p>Otherwise, if you want to proceed with the cPanel installation but do still lack the hostname, you can input a temporary one. Once cPanel is installed, you will be able to change the hostname to the correct one on one of the first setup pages.</p> 

<pre>hostname  host.example.com</pre>

<h2>Install cPanel</h2>

<p>Although the cPanel installation only has several steps, the installation does take a long time. Although using program "screen" is not necessary in order to install cPanel, it can be a very helpful addition to the installation process. It can be especially useful if you know that you may have issues with intermittent internet or that you will need to pause the lengthy install process.</p> 

<p>To start off, go ahead and install screen and wget:</p>

<pre>sudo yum install screen wget</pre>

<p>Once screen is installed, start a new session running:</p>

<pre>screen</pre>

<p>After opening screen, you can proceed to install cPanel with WHM or a DNS only version of cPanel.</p> 

<ul><li>Use this this command to install cPanel with WHM: 
<pre>wget -N http://httpupdate.cPanel.net/latest</pre></li>
<li>Use this command to install the DNS only version of cPanel:
<pre>wget -N http://httpupdate.cPanel.net/latest-dnsonly</pre></li></ul>

<p>With the requested package downloaded, we can go ahead and start the script running:</p>

<pre>sh latest</pre>

<p>Then close out of screen. The script, which may take one to two hours to complete will continue running while in the background—even if you close out the of server.</p>

<p>In order to detach screen type: <code>Cntrl-a-d</code></p>

<p>To reattach to your screen you can use the command:</p>

<pre>screen -r</pre>

<p>Once cPanel finally installs, you can access the login by going to your ip address:2087 (eg. 12.34.45.678:2087l) or domain (example.com:2087)</p>

<p>Your login will be:</p>

<pre>username: <i>your_server_user</i>
password: <i>your_password</i></pre>

<p>From there, you can create your cpanel user and finally login in at ipaddress/cpanel or domain/cpanel</p>

<div class="author">By Etel Sverdlov</div></ul></div>
    