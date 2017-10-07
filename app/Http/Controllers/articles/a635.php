<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h2>Introduction</h2>

<p>Perl is a popular programming language that allows you to quickly create scripts and install additional libraries.</p>

<p>We have previously covered <a href="https://indiareads/community/articles/how-to-install-nagios-on-centos-6">how to install Nagios monitoring server on CentOS 6 x64</a>.
This time, we will expand on this idea and create Nagios plugins using Perl.
These plugins will be running on client VPS, and be executed via NRPE.</p>

<h2>Step 1 - Install RPMForge Repository and NRPE on client VPS</h2>

<pre>
rpm -ivh http://pkgs.repoforge.org/rpmforge-release/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm
yum -y install perl nagios-nrpe
useradd nrpe && chkconfig nrpe on
</pre>

<h2>Step 2 - Create your Perl Script</h2>

<p>It would be a good idea to keep your plugins in same directory as other Nagios plugins (<B>/usr/lib64/nagios/plugins/</B> for example).</p>  

<p>For our example, we will create a script that checks current disk usage by calling "df" from shell, and throw an alert if it is over 85% used:</p>

<pre>
#!/usr/bin/perl
use strict;
use warnings;
use feature qw(switch say);

my $used_space = `df -h / \|awk 'FNR == 2 {print \$5}'`;

given ($used_space) {
    chomp($used_space);
    when ($used_space lt '85%') { print "OK - $used_space of disk space used."; exit(0);      }
    when ($used_space eq '85%') { print "WARNING - $used_space of disk space used."; exit(1);      }
    when ($used_space gt '85%') { print "CRITICAL - $used_space of disk space used."; exit(2); }
    default { print "UNKNOWN - $used_space of disk space used."; exit(3); }
}
</pre>

<img src="https://assets.digitalocean.com/articles/community/usedspace.pl.png" width="680" />

<p>We will save this script in <B>/usr/lib64/nagios/plugins/usedspace.pl</B> and make it executable:</p>

<pre>
chmod +x /usr/lib64/nagios/plugins/usedspace.pl
</pre>

<p>The entire Nagios NRPE plugin boils down to using exit codes to trigger alerts.</p>

<p>You introduce your level of logic to the script, and if you want to trigger an alert (whether it is OK, WARNING, CRITICAL, or UNKNOWN) - you specify an exit code.</p>

<p>Refer to the following Nagios Exit Codes:</p>

<h3>Nagios Exit Codes</h3>

<table border="0" cellpadding="0">
<thead>
<tr>
<td width="120" align="center"><B>Exit Code</B></td>
<td align="center"><B>Status</B></td>
</tr>
<tr align="center">
<td width="120">0</td>
<td align="center">OK</td>
</tr>
<tr align="center">
<td width="120">1</td>
<td align="center">WARNING</td>
</tr>
<tr align="center">
<td width="120">2</td>
<td align="center">CRITICAL</td>
</tr>
<tr align="center">
<td width="120">3</td>
<td align="center">UNKNOWN</td>
</tr>
</thead>
</table>

<h2>Step 3 - Add Your Script to NRPE configuration on client host</h2>

<p>Delete original <B>/etc/nagios/nrpe.cfg</B> and add the following lines to it:</p>

<pre>
log_facility=daemon
pid_file=/var/run/nrpe/nrpe.pid
server_port=5666
nrpe_user=nrpe
nrpe_group=nrpe
allowed_hosts=198.211.117.251
dont_blame_nrpe=1
debug=0
command_timeout=60
connection_timeout=300
include_dir=/etc/nrpe.d/

command[usedspace_perl]=/usr/lib64/nagios/plugins/usedspace.pl
</pre>

<p>Where 198.211.117.251 is our monitoring server from previous articles.  Change these to your own values.</p>

<p>Make sure to restart Nagios NRPE service:</p>

<pre>
service nrpe restart
</pre>

<h2>Step 4 - Add Your New Command to Nagios Checks on Nagios Monitoring Server</h2>

<p>Define new command in <B>/etc/nagios/objects/commands.cfg</B></p>

<pre>
define command{
        command_name    usedspace_perl
        command_line    $USER1$/check_nrpe -H $HOSTADDRESS$ -c usedspace_perl
        }
</pre>

<p>As you can see, it uses NRPE to make TCP connections to port 5666 and run command 'usedspace_perl', which we defined in <B>/etc/nagios/nrpe.cfg</B> on that remote host.</p>

<p>Add this check to your Nagios configuration file for client VPS.</p>

<p>For our example, we will monitor a server called CentOSDroplet and edit <B>/etc/nagios/servers/CentOSDroplet.cfg</B></p>

<pre>
define service {
        use                             generic-service
        host_name                       CentOSDroplet
        service_description             Custom Disk Checker In Perl
        check_command                   usedspace_perl
        }
</pre>

<img src="https://assets.digitalocean.com/articles/community/CentOSDroplet.cfg-perl.png" width="680" />

<p>Restart Nagios:</p>

<pre>
service nagios restart
</pre>

<p>Verify that the new check is working:</p>

<img src="https://assets.digitalocean.com/articles/community/nagios-centos-perl.png" width="680" />

<p>And you are all done!</p></div>
    