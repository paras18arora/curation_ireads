<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The Linux Audit System creates an audit trail, a way to track all kinds of information on your system. It can record a lot of data like types of events, the date and time, user IDs, system calls, processes, files used, SELinux contexts, and sensitivity levels. It can track whether a file has been accessed, edited, or executed. It can even track if changes to file attributes. It is capable of logging usage of system calls, commands executed by a user, failed login attempts, and many other events. By default, the audit system records only a few events in the logs such as users logging in, users using sudo, and SELinux-related messages. It uses audit rules to monitor for specific events and create related log entries. It is possible to create audit rules.</p>

<p>In this tutorial, we will discuss the different types of audit rules and how to add or remove custom rules on your server. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you get started with this tutorial, you should have the following:</p>

<ul>
<li>CentOS 7 Droplet (works with CentOS 6 as well)</li>
<li>Non-root user with sudo privileges. To setup a user of this type, follow the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a> tutorial. All commands will be run as this user.</li>
<li>A basic understanding of the Linux Audit System. Check out <a href="https://indiareads/community/tutorials/understanding-the-linux-auditing-system-on-centos-7">Understanding the Linux Auditing System on CentOS 7</a> for more information.</li>
</ul>

<h2 id="viewing-audit-rules">Viewing Audit Rules</h2>

<p>You can view the current set of audit rules using the command <code>auditctl -l</code>. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -l
</li></ul></code></pre>
<p>It will show no rules if none are present(this is the default):</p>
<pre class="code-pre "><code langs="">No rules
</code></pre>
<p>As you add rules in this tutorial, you can use this command to verify that they have been added.</p>

<p>The current status of the audit system can be viewed using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -s
</li></ul></code></pre>
<p>Output will be similar to:</p>
<pre class="code-pre "><code langs="">AUDIT_STATUS: enabled=1 flag=1 pid=9736 rate_limit=0 backlog_limit=320 lost=0 backlog=0
</code></pre>
<p>The <code>enabled=1</code> value shows that auditing is enabled on this server. The <code>pid</code> value is the process number of the audit daemon. A pid of 0 indicates that the audit daemon is not running. The <code>lost</code> entry will tell you how many event records have<br />
been discarded due to the kernel audit queue overflowing. The <code>backlog</code> field shows how many event records are currently queued waiting for auditd to read them. We will discuss the rest of the output fields in the next section of this tutorial.</p>

<h2 id="adding-audit-rules">Adding Audit Rules</h2>

<p>You can add custom audit rules using the command line tool <code>auditctl</code>. By default, rules will be added to the bottom of the current list, but could be inserted at the top too. To make your rules permanent, you need to add them to the file <code>/etc/audit/rules.d/audit.rules</code>. Whenever the <code>auditd</code> service is started, it will activate all the rules from the file. You can read more about the audit daemon and the audit system in our other article <a href="https://indiareads/community/tutorials/understanding-the-linux-auditing-system-on-centos-7">Understanding the Audit System on CentOS 7</a>. Audit rules work on a first match wins basis — when a rule matches, it will not evaluate rules further down. Correct ordering of rules is important. </p>

<p><span class="note">If you are on CentOS 6, the audit rules file is located at <code>/etc/audit/audit.rules</code> instead.<br /></span></p>

<p>There are three types of audit rules:</p>

<ul>
<li><p>Control rules: These rules are used for changing the configuration and settings of the audit system itself.</p></li>
<li><p>Filesystem rules: These are file or directory watches. Using these rules, we can audit any kind of access to specific files or directories.</p></li>
<li><p>System call rules: These rules are used for monitoring system calls made by any process or a particular user.</p></li>
</ul>

<h3 id="control-rules">Control Rules</h3>

<p>Let us look at some of the control rules we can add:</p>

<ul>
<li><code>auditctl -b <backlog></code> - Set maximum number of outstanding audit buffers allowed. If all buffers are full, the failure flag is consulted by the kernel for action. The default backlog limit set on a CentOS server is 320.  You can view this using:</li>
</ul>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -s
</li></ul></code></pre>
<p>In the output, you can see the current <strong>backlog_limit</strong> value:</p>
<pre class="code-pre "><code langs="">AUDIT_STATUS: enabled=1 flag=1 pid=9736 rate_limit=0 <span class="highlight">backlog_limit=320</span> lost=0 backlog=0
</code></pre>
<p>If your backlog value is more than the <strong>backlog_limit</strong> currently set, you might need to increase the <strong>backlog_limit</strong> for the audit logging to function correctly. For example, to increase the value to 1024, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -b 1024
</li></ul></code></pre>
<p>Output will show the status:</p>
<pre class="code-pre "><code langs="">AUDIT_STATUS: enabled=1 flag=1 pid=9736 rate_limit=0 <span class="highlight">backlog_limit=1024</span> lost=0 backlog=0
</code></pre>
<ul>
<li><p><code>auditctl -f [0 1 2]</code> - Set failure flag (0=silent, 1=printk. 2=panic). This option lets you determine how you want the kernel to handle critical errors. If set to 0, audit messages which could not be logged will be silently discarded. If set to 1, messages are sent to the kernel log subsystem. If set to 2, it will trigger a kernel panic. Example conditions where this flag is consulted include backlog limit exceeded, out of kernel memory, and rate limit exceeded. The default value is 1. Unless you have any major problems with auditing daemon on your server, you will not need to change this value.</p></li>
<li><p><code>auditctl -R <filename></code> - Read audit rules from the file specified. This is useful when you are testing some temporary rules and want to use the old rules again from the <code>audit.rules</code> file.</p></li>
</ul>

<p>The rules we add via <code>auditctl</code> are not permanent. To make them persistent across reboots, you can add them to the file <code>/etc/audit/rules.d/audit.rules</code>. This file uses the same <code>auditctl</code> command line syntax to specify the rules but without the <code>auditctl</code> command itself in front. Any empty lines or any text following a hash sign (#) is ignored. The default rules file looks like this:</p>
<div class="code-label " title="/etc/audit/rules.d/audit.rules">/etc/audit/rules.d/audit.rules</div><pre class="code-pre "><code langs=""># This file contains the auditctl rules that are loaded
# whenever the audit daemon is started via the initscripts.
# The rules are simply the parameters that would be passed
# to auditctl.

# First rule - delete all
-D

# Increase the buffers to survive stress events.
# Make this bigger for busy systems
-b 320

# Feel free to add below this line. See auditctl man page
</code></pre>
<p>To change the backlog value to say, 8192, you can change <strong>-b 320</strong> to <strong>-b 8192</strong> and restart the audit daemon using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service auditd restart
</li></ul></code></pre>
<p>If you don't restart the daemon, it will still set the new value from the configuration at the next server reboot.</p>

<h3 id="filesystem-rules">Filesystem Rules</h3>

<p>Filesystem watches can be set on files and directories. We can also specify what type of access to watch for. The syntax for a filesystem rule is:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">auditctl -w <span class="highlight">path_to_file</span> -p <span class="highlight">permissions</span> -k <span class="highlight">key_name</span>
</li></ul></code></pre>
<p>where </p>

<p><code>path_to_file</code> is the file or directory that is audited. <code>permissions</code> are the permissions that are logged. This value can be one or a combination of r(read), w(write), x(execute), and a(attribute change). <code>key_name</code> is an optional string that helps you identify which rule(s) generated a particular log entry. </p>

<p>Let us look at some examples. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -w /etc/hosts -p wa -k hosts_file_change
</li></ul></code></pre>
<p>The above rule asks the audit system to watch for any write access or attribute change to the file <code>/etc/hosts</code> and log them to the audit log with the custom key string specified by us — <code>hosts_file_change</code>. </p>

<p>If you wish to make this rule permanent, then add it to the file <code>/etc/audit/rules.d/audit.rules</code> at the bottom like this:</p>
<div class="code-label " title="/etc/audit/rules.d/audit.rules">/etc/audit/rules.d/audit.rules</div><pre class="code-pre "><code langs="">-w /etc/hosts -p wa -k hosts_file_change
</code></pre>
<p>To make sure the rule was added successfully, you can run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -l
</li></ul></code></pre>
<p>If all goes well, output should show:</p>
<pre class="code-pre "><code langs="">LIST_RULES: exit,always watch=/etc/hosts perm=wa key=hosts_file_change
</code></pre>
<p>We can also add watches to directories.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -w /etc/sysconfig/ -p rwa -k configaccess
</li></ul></code></pre>
<p>The above rule will add a watch to the directory <code>/etc/sysconfig</code> and all files and directories beneath it for any read, write, or attribute change access. It will also label log messages with a custom key <strong>configaccess</strong>.</p>

<p>To add a rule to watch for execution of the <code>/sbin/modprobe</code> command (this command can add/remove kernel modules from the server):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -w /sbin/modprobe -p x -k kernel_modules
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> You can't insert a watch to the top level directory. This is prohibited by the kernel. Wildcards are not supported either and will generate a warning.<br /></span></p>

<p>To search the audit logs for specific events, you can use the command <code>ausearch</code>. For example, to search the audit logs for all events labeled with the key <code>configaccess</code>, you can run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ausearch -k configaccess
</li></ul></code></pre>
<p><code>ausearch</code> is discussed in detail in our other tutorial <a href="https://indiareads/community/tutorials/understanding-the-linux-auditing-system-on-centos-7">Understanding the Audit System on CentOS 7</a>. </p>

<h3 id="system-call-rules">System Call Rules</h3>

<p>By auditing system calls, you can track activities on the server well beyond the application level. The syntax for system call rules is:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">auditctl -a <span class="highlight">action,filter</span> -S <span class="highlight">system_call</span> -F <span class="highlight">field=value</span> -k <span class="highlight">key_name</span>`
</li></ul></code></pre>
<p>where:</p>

<ul>
<li><p>Replacing <code>-a</code> with <code>-A</code> in the above command will insert the rule at the top instead of at the bottom.</p></li>
<li><p><code>action</code> and <code>filter</code> specify when a certain event is logged. <code>action</code> can be either <code>always</code> or <code>never</code>. <code>filter</code> specifies which kernel rule-matching filter is applied to the event. The rule-matching filter can be one of the following: <code>task</code>, <code>exit</code>, <code>user</code>, and <code>exclude</code>. <code>action,filter</code> will be <code>always,exit</code> in most cases, which tells <code>auditctl</code> that you want to audit this system call when it exits.</p></li>
<li><p><code>system_call</code> specifies the system call by its name. Several system calls can be grouped into one rule, each specified after a <code>-S</code> option. The word <code>all</code> may also be used. You can use the <code>sudo ausyscall --dump</code> command to view a list of all system calls along with their numbers.  </p></li>
<li><p><code>field=value</code> specifies additional options that modify the rule to match events based on a specified architecture, user ID, process ID, path, and others.</p></li>
<li><p><code>key_name</code> is an optional string that helps you identify later which rule or a set of rules generated a particular log entry. </p></li>
</ul>

<p>Let us now look at some example system call rules.</p>

<p>To define an audit rule that creates a log entry labelled <code>rename</code> every time a file is renamed by a user whose ID is 1000 or larger, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -a always,exit -F arch=b64 -F "auid>=1000" -S rename -S renameat -k rename
</li></ul></code></pre>
<p>The <code>-F arch=b64</code> says to audit the 64-bit version of the system calls in the rule.</p>

<p>To define a rule that logs what files a particular user (with UID 1001) accessed and labels the log entries with <code>userfileaccess</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -a always,exit -F arch=b64 -F auid=1001 -S open -k userfileaccess
</li></ul></code></pre>
<p>If you wish to make this rule permanent, then add it to the file <code>/etc/audit/rules.d/audit.rules</code> at the bottom like this:</p>
<div class="code-label " title="/etc/audit/rules.d/audit.rules">/etc/audit/rules.d/audit.rules</div><pre class="code-pre "><code langs="">-a always,exit -F arch=b64 -F auid=1001 -S open -k userfileaccess
</code></pre>
<p>You can also define a filesystem rule using the system call rule syntax. For example, the following rule:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -a always,exit -F path=/etc/hosts -F perm=wa -k hosts_file_change
</li></ul></code></pre>
<p>does the same job as the filesystem rule we saw in the earlier section:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -w /etc/hosts -p wa -k hosts_file_change
</li></ul></code></pre>
<p>To watch a directory recursively using a system call rule, you can use the option <code>-F "dir=/path/to/dir"</code>.</p>

<p><span class="note"><strong>Note:</strong> Please note that all processes started earlier than the audit daemon itself will have an <code>auid</code> of <code>4294967295</code>. To exclude those from your rules, you can add <code>-F "auid!=4294967295"</code> to your rules. To avoid this problem, you can add <code>audit=1</code> to the kernel boot parameters. This enables the kernel audit system at boot even before the audit daemon starts and all processes will have the correct login uid.<br /></span></p>

<h2 id="removing-audit-rules">Removing Audit Rules</h2>

<p>To remove all the current audit rules, you can use the command <code>auditctl -D</code>. To remove filesystem watch rules added using the <code>-w</code>option, you can replace <code>-w</code> with <code>-W</code> in the original rule. System call rules added using the options <code>-a</code> or <code>-A</code> can be deleted using the <code>-d</code> option with the original rule. For example, say we have added the following rule:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -w /etc/passwd -p wa -k passwdaccess
</li></ul></code></pre>
<p>View the rule set using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -l
</li></ul></code></pre>
<p>The output should include:</p>
<pre class="code-pre "><code langs="">LIST_RULES: exit,always watch=/etc/passwd perm=wa key=passwdaccess
</code></pre>
<p>To remove this rule, we can use the following command, just replacing <code>-w</code> with <code>-W</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -W /etc/passwd -p wa -k passwdaccess
</li></ul></code></pre>
<p>Now, view the rule set using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -l
</li></ul></code></pre>
<p>The rule should not be in the list now.</p>

<p><span class="note"><strong>Note:</strong> If there are any permanent audit rules added inside the <code>audit.rules</code> file, an audit daemon restart or system reboot will load all the rules from the file. To permanently delete audit rules, you need to remove them from the file.<br /></span></p>

<h2 id="locking-audit-rules">Locking Audit Rules</h2>

<p>It is possible to disable or enable the audit system and lock the audit rules using <code>auditctl -e [0 1 2]</code>. For example, to disable auditing temporarily, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">auditctl -e 0
</li></ul></code></pre>
<p>When <code>1</code> is passed as an argument, it will enable auditing. To lock the audit configuration so that it cannot be changed, pass <code>2</code> as the argument. This makes the current set of audit rules immutable. Rules can no longer be added, removed, or edited, and the audit daemon can no longer be stopped. Locking the configuration is intended to be the last command in <code>audit.rules</code> for anyone wishing this feature to be active. Any attempt to change the configuration in this mode will be audited and denied. The configuration can only be changed by rebooting the server.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Information provided by the Linux Auditing System is very useful for intrusion detection. You should now be able to add custom audit rules so that you can log particular events.</p>

<p>Remember that you can always refer to the <code>auditctl</code> man page when adding custom logging rules. It offers a full list of command line options, performance tips, and examples. The <code>/usr/share/doc/audit-<span class="highlight"><version></span>/</code> directory contains files with pre-configured audit rules based on some common certification standards.</p>

    