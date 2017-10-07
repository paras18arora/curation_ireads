<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The <em>Linux Auditing System</em> helps system administrators create an audit trail, a log for every action on the server. We can track security-relevant events, record the events in a log file, and detect misuse or unauthorized activities by inspecting the audit log files. We can choose which actions on the server to monitor and to what extent. Audit does not provide additional security to your system, rather, it helps track any violations of system policies and enables you to take additional security measures to prevent them.</p>

<p>This tutorial explains the audit system, how to configure it, how to generate reports, and how to read these reports. We will also see how to search the audit logs for specific events.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you need the following:</p>

<ul>
<li>CentOS 7 Droplet (works with CentOS 6 as well)</li>
<li>Non-root user with sudo privileges. To setup a user of this type, follow the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a> tutorial. All commands will be run as this user.</li>
</ul>

<h2 id="verifying-the-audit-installation">Verifying the Audit Installation</h2>

<p>There are two main parts to the audit system:</p>

<ol>
<li>The audit kernel component intercepts system calls from user applications, records events, and sends these audit messages to the audit daemon </li>
<li>The <code>auditd</code> daemon collects the information from the kernel and creates entries in a log file</li>
</ol>

<p>The audit system uses the following packages: <code>audit</code> and <code>audit-libs</code>. These packages are installed by default on a new CentOS 7 Droplet (and a new CentOS 6 Droplet). It is good to verify that you have them installed on your server using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum list audit audit-libs
</li></ul></code></pre>
<p>You should see both the packages under <code>Installed Packages</code> in the output:</p>
<pre class="code-pre "><code langs="">Installed Packages
audit.x86_64
audit-libs.x86_64
</code></pre>
<h2 id="configuring-audit">Configuring Audit</h2>

<p>The main configuration file for <code>auditd</code> is <code>/etc/audit/auditd.conf</code>. This file consists of configuration parameters that include where to log events, how to deal with full disks, and log rotation. To edit this file, you need to use sudo:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/audit/auditd.conf
</li></ul></code></pre>
<p>For example, to increase the number of audit log files kept on your server to 10, edit the following option:</p>
<div class="code-label " title="/etc/audit/auditd.conf">/etc/audit/auditd.conf</div><pre class="code-pre "><code langs="">num_logs = <span class="highlight">10</span>
</code></pre>
<p>You can also configure the maximum log file size in MB and what action to take once the size is reached:</p>
<div class="code-label " title="/etc/audit/auditd.conf">/etc/audit/auditd.conf</div><pre class="code-pre "><code langs="">max_log_file = <span class="highlight">30</span>
max_log_file_action = <span class="highlight">ROTATE</span>
</code></pre>
<p>When you make changes to the configuration, you need to restart the auditd service using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service auditd restart
</li></ul></code></pre>
<p>for the changes to take effect.</p>

<p>The other configuration file is <code>/etc/audit/rules.d/audit.rules</code>. (If you are on CentOS 6, the file is <code>/etc/audit/audit.rules</code> instead.) It is used for permanently adding auditing rules.</p>

<p>When <code>auditd</code> is running, audit messages will be recorded in the file <code>/var/log/audit/audit.log</code>. </p>

<h2 id="understanding-audit-log-files">Understanding Audit Log Files</h2>

<p>By default, the audit system logs audit messages to the <code>/var/log/audit/audit.log</code> file. Audit log files carry a lot of useful information, but reading and understanding the log files can seem difficult for many users due to the sheer amount of information provided, the abbreviations and codes used, etc. In this section, we will try to understand some of the fields in a typical audit message in the audit log files. </p>

<p><span class="note"><strong>'Note:</strong> If <code>auditd</code> is not running for whatever reason, audit messages will be sent to rsyslog.<br /></span></p>

<p>For this example, let us assume we have an audit rule configured on the server with the label (<code>key</code>) <code>sshconfigchange</code> to log every access or modification to the file <code>/etc/ssh/sshd_config</code>. If you wish, you can add this rule temporarily using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo auditctl -w /etc/ssh/sshd_config -p rwxa -k sshconfigchange
</li></ul></code></pre>
<p>Running the following command to view the <code>sshd_config</code> file creates a new <strong>event</strong> in the audit log file: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cat /etc/ssh/sshd_config
</li></ul></code></pre>
<p>This event in the <code>audit.log</code> file looks as follows:</p>
<div class="code-label " title="/var/log/audit/audit.log">/var/log/audit/audit.log</div><pre class="code-pre "><code langs="">
type=SYSCALL msg=audit(1434371271.277:135496): arch=c000003e syscall=2 success=yes exit=3 a0=7fff0054e929 a1=0 a2=1fffffffffff0000 a3=7fff0054c390 items=1 ppid=6265 pid=6266 auid=1000 uid=0 gid=0 euid=0 suid=0 fsuid=0 egid=0 sgid=0 fsgid=0 tty=pts0 ses=113 comm="cat" exe="/usr/bin/cat" key="sshconfigchange"

type=CWD msg=audit(1434371271.277:135496):  cwd="/home/sammy"

type=PATH msg=audit(1434371271.277:135496): item=0 name="/etc/ssh/sshd_config" inode=392210 dev=fd:01 mode=0100600 ouid=0 ogid=0 rdev=00:00 objtype=NORMAL
</code></pre>
<p>The above event consists of three records (each starting with the <code>type=</code> keyword), which share the same timestamp (<code>1434371271.277</code>) and id (<code>135496</code>). Each record consists of several <em>name=value</em> pairs separated by a white space or a comma. We will see in detail what some of those fields stand for.</p>

<p>In the first record:</p>

<ul>
<li><code>type=SYSCALL</code></li>
</ul>

<p>The <code>type</code> field contains the type of audit message. In this case, the <code>SYSCALL</code> value shows that this message was triggered by a system call to the kernel. </p>

<ul>
<li><code>msg=audit(1434371271.277:135496):</code></li>
</ul>

<p>The timestamp and ID of the audit message in the form <code>audit(time_stamp:ID)</code>. Multiple audit messages/records can share the same time stamp and ID if they were generated as part of the same audit event. In our example, we can see the same timestamp (1434371271.277) and ID (135496) on all three messages generated by the audit event.</p>

<ul>
<li><code>arch=c000003e</code></li>
</ul>

<p>The <code>arch</code> field contains information about the CPU architecture of the system. The value, c000003e, is in hexadecimal notation and stands for x86_64.</p>

<ul>
<li><code>syscall=2</code></li>
</ul>

<p>The <code>syscall</code> field denotes the type of the system call that was sent to the kernel. In this case, 2 is the <code>open</code> system call. The <code>ausyscall</code> utility allows you to convert system call numbers to their human-readable equivalents. For example, run the following command to convert the value 2 to its human-readable equivalent:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ausyscall 2
</li></ul></code></pre>
<p>The output shows:</p>
<pre class="code-pre "><code langs="">open
</code></pre>
<p><span class="note"><strong>Note:</strong> You can use the <code>sudo ausyscall --dump</code> command to view a list of all system calls along with their numbers.<br /></span></p>

<ul>
<li><code>success=yes</code></li>
</ul>

<p>The <code>success</code> field shows whether the system call in that particular event succeeded or failed. In this case, the call succeeded. The user sammy was able to open and read the file <code>sshd_config</code> when the <code>sudo cat /etc/ssh/sshd_config</code> command was run.</p>

<ul>
<li><code>ppid=6265</code> </li>
</ul>

<p>The <code>ppid</code> field records the Parent Process ID (PPID). In this case, <code>6265</code> was the PPID of the <code>bash</code> process. </p>

<ul>
<li><code>pid=6266</code></li>
</ul>

<p>The <code>pid</code> field records the Process ID (PID). In this case, <code>6266</code> was the PID of the <code>cat</code> process. </p>

<ul>
<li><code>auid=1000</code></li>
</ul>

<p><code>auid</code> is the audit UID or the original UID of the user who triggered this audit message. The audit system will remember your original UID even when you elevate privileges through su or sudo after initial login.</p>

<ul>
<li><code>uid=0</code></li>
</ul>

<p>The <code>uid</code> field records the user ID of the user who started the analyzed process. In this case, the <code>cat</code> command was started by user root with uid 0.</p>

<ul>
<li><code>comm="cat"</code></li>
</ul>

<p><code>comm</code> records the name of the command that triggered this audit message.</p>

<ul>
<li><code>exe="/usr/bin/cat"</code></li>
</ul>

<p>The <code>exe</code> field records the path to the command that was used to trigger this audit message.</p>

<ul>
<li><code>key="sshconfigchange"</code></li>
</ul>

<p>The <code>key</code> field records the administrator-defined string associated with the audit rule that generated this event in the log. Keys are usually set while creating custom auditing rules to make it easier to search for certain types of events from the audit logs.</p>

<p>For the second record: </p>

<ul>
<li><code>type=CWD</code></li>
</ul>

<p>In the second record, the type is <code>CWD</code> — Current Working Directory. This type is used to record the working directory from which the process that triggered the system call specified in the first record was executed. </p>

<ul>
<li><code>cwd="/home/sammy"</code></li>
</ul>

<p>The <code>cwd</code> field contains the path to the directory from which the system call was invoked. In our case, the <code>cat</code> command which triggered the <code>open</code> syscall in the first record was executed from the directory <code>/home/sammy</code>.</p>

<p>For the third record:</p>

<ul>
<li><code>type=PATH</code></li>
</ul>

<p>In the third record, the type is <code>PATH</code>. An audit event contains a <code>PATH</code> record for every path that is passed to the system call as an argument. In our audit event, only one path (<code>/etc/ssh/sshd_config</code>) was used as an argument. </p>

<ul>
<li><code>msg=audit(1434371271.277:135496):</code></li>
</ul>

<p>The <code>msg</code> field shows the same timestamp and ID combination as in the first and second records since all three records are part of the same audit event.</p>

<ul>
<li><code>name="/etc/ssh/sshd_config"</code></li>
</ul>

<p>The <code>name</code> field records the full path of the file or directory that was passed to the system call (open) as an argument. In this case, it was the <code>/etc/ssh/sshd_config</code> file. </p>

<ul>
<li><code>ouid=0</code></li>
</ul>

<p>The <code>ouid</code> field records the user ID of the object's owner. Here the object is the file <code>/etc/ssh/sshd_config</code>.</p>

<p><span class="note"><strong>Note:</strong> More information on audit record types is available from the links at the end of this tutorial.<br /></span></p>

<h2 id="searching-the-audit-logs-for-events">Searching the Audit Logs for Events</h2>

<p>The Linux Auditing System ships with a powerful tool called <code>ausearch</code> for searching audit logs. With <code>ausearch</code>, you can filter and search for event types. It can also interpret events for you by translating numeric values to human-readable values like system calls or usernames. </p>

<p>Let us look at a few examples. </p>

<p>The following command will search the audit logs for all audit events of the type LOGIN from today and interpret usernames.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ausearch -m LOGIN --start today -i
</li></ul></code></pre>
<p>The command below will search for all events with event id 27020 (provided there is an event with that id).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ausearch -a 27020
</li></ul></code></pre>
<p>This command will search for all events (if any) touching the file <code>/etc/ssh/sshd_config</code> and interpret them:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ausearch -f /etc/ssh/sshd_config -i
</li></ul></code></pre>
<h2 id="generating-audit-reports">Generating Audit Reports</h2>

<p>Instead of reading the raw audit logs, you can get a summary of audit messages using the tool <code>aureport</code>. It provides reports in human-readable format. These reports can be used as building blocks for more complicated analysis. When <code>aureport</code> is run without any options, it will show a summary of the different types of events present in the audit logs. When used with search options, it will show the list of events matching the search criteria.</p>

<p>Let us try a few examples for <code>aureport</code>. If you want to generate a summary report on all command executions on the server, run: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aureport -x --summary
</li></ul></code></pre>
<p>The output will look something like this with different values:</p>
<pre class="code-pre "><code langs="">Executable Summary Report
=================================
total  file
=================================
117795  /usr/sbin/sshd
1776  /usr/sbin/crond
210  /usr/bin/sudo
141  /usr/bin/date
24  /usr/sbin/autrace
18  /usr/bin/su
</code></pre>
<p>The first column shows the number of times the command was executed, and the second column shows the command that was executed. Please note that not all commands are logged by default. Only security-related ones are logged.</p>

<p>The following command will give you the statistics of all failed events:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aureport --failed
</li></ul></code></pre>
<p>Output looks similar to:</p>
<pre class="code-pre "><code langs="">Failed Summary Report
======================
Number of failed logins: 11783
Number of failed authentications: 41679
Number of users: 3
Number of terminals: 4
Number of host names: 203
Number of executables: 3
Number of files: 4
Number of AVC's: 0
Number of MAC events: 0
Number of failed syscalls: 9
</code></pre>
<p>To generate a report about files accessed with system calls and usernames:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aureport -f -i
</li></ul></code></pre>
<p>Sample output:</p>
<pre class="code-pre "><code langs="">File Report
===============================================
# date time file syscall success exe auid event
===============================================
1. Monday 15 June 2015 08:27:51 /etc/ssh/sshd_config open yes /usr/bin/cat sammy 135496
2. Tuesday 16 June 2015 00:40:15 /etc/ssh/sshd_config getxattr no /usr/bin/ls root 147481
3. Tuesday 16 June 2015 00:40:15 /etc/ssh/sshd_config lgetxattr yes /usr/bin/ls root 147482
4. Tuesday 16 June 2015 00:40:15 /etc/ssh/sshd_config getxattr no /usr/bin/ls root 147483
5. Tuesday 16 June 2015 00:40:15 /etc/ssh/sshd_config getxattr no /usr/bin/ls root 147484
6. Tuesday 16 June 2015 05:40:08 /bin/date execve yes /usr/bin/date root 148617
</code></pre>
<p>To view the same in summary format, you can run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo aureport -f -i --summary
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> The <code>aureport</code> tool can also take input from stdin instead of log files as long as the input is in the raw log data format.<br /></span></p>

<h2 id="analyzing-a-process-using-autrace">Analyzing a Process Using autrace</h2>

<p>To audit an individual process, we can use the <code>autrace</code> tool. This tool traces the system calls performed by a process. This can be useful in investigating a suspected trojan or a problematic process. The output of <code>autrace</code> is written to <code>/var/log/audit/audit.log</code> and looks similar to the standard audit log entries. After execution, <code>autrace</code> will present you with an example <code>ausearch</code> command to investigate the logs. Always use the full path to the binary to track with autrace, for example <code>sudo autrace /bin/ls /tmp</code>.</p>

<p><span class="note"><strong>Note:</strong> Please note that running <code>autrace</code> will remove all custom auditing rules. It replaces them with specific rules needed for tracing the process you specified. After <code>autrace</code> is complete, it will clear the new rules it added. For the same reason, <code>autrace</code> will not work when your auditing rules are set immutable. <br /></span></p>

<p>Let us try an example, say, we want to trace the process <code>date</code> and view the files and system calls used by it. Run the following:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo autrace /bin/date
</li></ul></code></pre>
<p>You should see something similar to the following:</p>
<pre class="code-pre "><code langs="">Waiting to execute: /bin/date
Wed Jun 17 07:22:03 EDT 2015
Cleaning up...
Trace complete. You can locate the records with 'ausearch -i -p 27020'
</code></pre>
<p>You can use the <code>ausearch</code> command from the above output to view the related logs or even pass it to <code>aureport</code> to get well-formatted human-readable output:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ausearch -p 27020 --raw | aureport -f -i
</li></ul></code></pre>
<p>This command searches for the event with event ID <code>27020</code> from the audit logs, extracts it in raw log format, and passes it to <code>aureport</code>, which in turn interprets and gives the results in a better format for easier reading.</p>

<p>You should see output similar to the following:</p>
<pre class="code-pre "><code langs="">File Report
===============================================
# date time file syscall success exe auid event
===============================================
1. Wednesday 17 June 2015 07:22:03 /bin/date execve yes /usr/bin/date sammy 169660
2. Wednesday 17 June 2015 07:22:03 /etc/ld.so.preload access no /usr/bin/date sammy 169663
3. Wednesday 17 June 2015 07:22:03 /etc/ld.so.cache open yes /usr/bin/date sammy 169664
4. Wednesday 17 June 2015 07:22:03 /lib64/libc.so.6 open yes /usr/bin/date sammy 169668
5. Wednesday 17 June 2015 07:22:03 /usr/lib/locale/locale-archive open yes /usr/bin/date sammy 169683
6. Wednesday 17 June 2015 07:22:03 /etc/localtime open yes /usr/bin/date sammy 169691
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>We have covered the basics of the Linux Auditing System in this tutorial. You should now have a good understanding of how the audit system works, how to read the audit logs, and the different tools available to make it easier for you to audit your server. </p>

<p>By default, the audit system records only a few events in the logs such as users logging in and users using sudo. SELinux-related messages are also logged. The audit daemon uses rules to monitor for specific events and create related log entries. It is possible to create custom audit rules to monitor and record in the logs whatever we want. This is where the audit system becomes powerful for a system administrator. We can add rules using either the command line tool <code>auditctl</code> or permanently in the file <code>/etc/audit/rules.d/audit.rules</code>. Writing custom rules and using predefined rule sets are discussed in detail in the <a href="https://indiareads/community/tutorials/writing-custom-system-audit-rules-on-centos-7">Writing Custom System Audit Rules on CentOS 7</a> tutorial.</p>

<p>You can also check out the following resources for even more information on the audit system:</p>

<ul>
<li><p><a href="https://access.redhat.com/documentation/en-US/Red_Hat_Enterprise_Linux/7/html/Security_Guide/sec-Audit_Record_Types.html">Types of audit records</a></p></li>
<li><p><a href="https://access.redhat.com/documentation/en-US/Red_Hat_Enterprise_Linux/7/html/Security_Guide/sec-configuring_the_audit_service.html">Configuring auditd for a CAPP Environment</a></p></li>
<li><p><a href="https://access.redhat.com/documentation/en-US/Red_Hat_Enterprise_Linux/7/html/Security_Guide/app-Audit_Reference.html#sec-Audit_Events_Fields">Audit Event Fields and their definitions</a></p></li>
</ul>

    