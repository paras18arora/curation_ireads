<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Some of the most compelling advantages of <code>systemd</code> are those involved with process and system logging.  When using other tools, logs are usually dispersed throughout the system, handled by different daemons and processes, and can be fairly difficult to interpret when they span multiple applications.  <code>Systemd</code> attempts to address these issues by providing a centralized management solution for logging all kernel and userland processes.  The system that collects and manages these logs is known as the journal.</p>

<p>The journal is implemented with the <code>journald</code> daemon, which handles all of the messages produced by the kernel, initrd, services, etc.  In this guide, we will discuss how to use the <code>journalctl</code> utility, which can be used to access and manipulate the data held within the journal.</p>

<h2 id="general-idea">General Idea</h2>

<p>One of the impetuses behind the <code>systemd</code> journal is to centralize the management of logs regardless of where the messages are originating.  Since much of the boot process and service management is handled by the <code>systemd</code> process, it makes sense to standardize the way that logs are collected and accessed.  The <code>journald</code> daemon collects data from all available sources and stores them in a binary format for easy and dynamic manipulation.</p>

<p>This gives us a number of significant advantages.  By interacting with the data using a single utility, administrators are able to dynamically display log data according to their needs.  This can be as simple as viewing the boot data from three boots ago, or combining the log entries sequentially from two related services to debug a communication issue.</p>

<p>Storing the log data in a binary format also means that the data can be displayed in arbitrary output formats depending on what you need at the moment.  For instance, for daily log management you may be used to viewing the logs in the standard <code>syslog</code> format, but if you decide to graph service interruptions later on, you can output each entry as a JSON object to make it consumable to your graphing service.  Since the data is not written to disk in plain text, no conversion is needed when you need a different on-demand format.</p>

<p>The <code>systemd</code> journal can either be used with an existing <code>syslog</code> implementation, or it can replace the <code>syslog</code> functionality, depending on your needs.  While the <code>systemd</code> journal will cover most administrator's logging needs, it can also complement existing logging mechanisms.  For instance, you may have a centralized <code>syslog</code> server that you use to compile data from multiple servers, but you also may wish to interleave the logs from multiple services on a single system with the <code>systemd</code> journal.  You can do both of these by combining these technologies.</p>

<h2 id="setting-the-system-time">Setting the System Time</h2>

<p>One of the benefits of using a binary journal for logging is the ability to view log records in UTC or local time at will.  By default, <code>systemd</code> will display results in local time.</p>

<p>Because of this, before we get started with the journal, we will make sure the timezone is set up correctly.  The <code>systemd</code> suite actually comes with a tool called <code>timedatectl</code> that can help with this.</p>

<p>First, see what timezones are available with the <code>list-timezones</code> option:</p>
<pre class="code-pre "><code langs="">timedatectl list-timezones
</code></pre>
<p>This will list the timezones available on your system.  When you find the one that matches the location of your server, you can set it by using the <code>set-timezone</code> option:</p>
<pre class="code-pre "><code langs="">sudo timedatectl set-timezone <span class="highlight">zone</span>
</code></pre>
<p>To ensure that your machine is using the correct time now, use the <code>timedatectl</code> command alone, or with the <code>status</code> option.  The display will be the same:</p>
<pre class="code-pre "><code langs="">timedatectl status
</code></pre><pre class="code-pre "><code langs="">      <span class="highlight">Local time: Thu 2015-02-05 14:08:06 EST</span>
  Universal time: Thu 2015-02-05 19:08:06 UTC
        RTC time: Thu 2015-02-05 19:08:06
       Time zone: America/New_York (EST, -0500)
     NTP enabled: no
NTP synchronized: no
 RTC in local TZ: no
      DST active: n/a
</code></pre>
<p>The first line should display the correct time.</p>

<h2 id="basic-log-viewing">Basic Log Viewing</h2>

<p>To see the logs that the <code>journald</code> daemon has collected, use the <code>journalctl</code> command.</p>

<p>When used alone, every journal entry that is in the system will be displayed within a pager (usually <code>less</code>) for you to browse.  The oldest entries will be up top:</p>
<pre class="code-pre "><code langs="">journalctl
</code></pre><pre class="code-pre "><code langs="">-- Logs begin at Tue 2015-02-03 21:48:52 UTC, end at Tue 2015-02-03 22:29:38 UTC. --
Feb 03 21:48:52 localhost.localdomain systemd-journal[243]: Runtime journal is using 6.2M (max allowed 49.
Feb 03 21:48:52 localhost.localdomain systemd-journal[243]: Runtime journal is using 6.2M (max allowed 49.
Feb 03 21:48:52 localhost.localdomain systemd-journald[139]: Received SIGTERM from PID 1 (systemd).
Feb 03 21:48:52 localhost.localdomain kernel: audit: type=1404 audit(1423000132.274:2): enforcing=1 old_en
Feb 03 21:48:52 localhost.localdomain kernel: SELinux: 2048 avtab hash slots, 104131 rules.
Feb 03 21:48:52 localhost.localdomain kernel: SELinux: 2048 avtab hash slots, 104131 rules.
Feb 03 21:48:52 localhost.localdomain kernel: input: ImExPS/2 Generic Explorer Mouse as /devices/platform/
Feb 03 21:48:52 localhost.localdomain kernel: SELinux:  8 users, 102 roles, 4976 types, 294 bools, 1 sens,
Feb 03 21:48:52 localhost.localdomain kernel: SELinux:  83 classes, 104131 rules

. . .
</code></pre>
<p>You will likely have pages and pages of data to scroll through, which can be tens or hundreds of thousands of lines long if <code>systemd</code> has been on your system for a long while.  This demonstrates how much data is available in the journal database.</p>

<p>The format will be familiar to those who are used to standard <code>syslog</code> logging.  However, this actually collects data from more sources than traditional <code>syslog</code> implementations are capable of.  It includes logs from the early boot process, the kernel, the initrd, and application standard error and out.  These are all available in the journal.</p>

<p>You may notice that all of the timestamps being displayed are local time.  This is available for every log entry now that we have our local time set correctly on our system.  All of the logs are displayed using this new information.</p>

<p>If you want to display the timestamps in UTC, you can use the <code>--utc</code> flag:</p>
<pre class="code-pre "><code langs="">journalctl --utc
</code></pre>
<h2 id="journal-filtering-by-time">Journal Filtering by Time</h2>

<p>While having access to such a large collection of data is definitely useful, such a large amount of information can be difficult or impossible to inspect and process mentally.  Because of this, one of the most important features of <code>journalctl</code> is its filtering options.</p>

<h3 id="displaying-logs-from-the-current-boot">Displaying Logs from the Current Boot</h3>

<p>The most basic of these which you might use daily, is the <code>-b</code> flag.  This will show you all of the journal entries that have been collected since the most recent reboot.</p>
<pre class="code-pre "><code langs="">journalctl -b
</code></pre>
<p>This will help you identify and manage information that is pertinent to your current environment.</p>

<p>In cases where you aren't using this feature and are displaying more than one day of boots, you will see that <code>journalctl</code> has inserted a line that looks like this whenever the system went down:</p>
<pre class="code-pre "><code langs="">. . .

-- Reboot --

. . .
</code></pre>
<p>This can be used to help you logically separate the information into boot sessions.</p>

<h3 id="past-boots">Past Boots</h3>

<p>While you will commonly want to display the information from the current boot, there are certainly times when past boots would be helpful as well.  The journal can save information from many previous boots, so <code>journalctl</code> can be made to display information easily.</p>

<p>Some distributions enable saving previous boot information by default, while others disable this feature.  To enable persistent boot information, you can either create the directory to store the journal by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /var/log/journal
</li></ul></code></pre>
<p>Or you can edit the journal configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/systemd/journald.conf
</li></ul></code></pre>
<p>Under the <code>[Journal]</code> section, set the <code>Storage=</code> option to "persistent" to enable persistent logging:</p>
<div class="code-label " title="/etc/systemd/journald.conf">/etc/systemd/journald.conf</div><pre class="code-pre "><code langs="">. . .
[Journal]
Storage=<span class="highlight">persistent</span>
</code></pre>
<p>When saving previous boots is enabled on your server, <code>journalctl</code> provides some commands to help you work with boots as a unit of division.  To see the boots that <code>journald</code> knows about, use the <code>--list-boots</code> option with <code>journalctl</code>:</p>
<pre class="code-pre "><code langs="">journalctl --list-boots
</code></pre><pre class="code-pre "><code langs="">-2 caf0524a1d394ce0bdbcff75b94444fe Tue 2015-02-03 21:48:52 UTC—Tue 2015-02-03 22:17:00 UTC
-1 13883d180dc0420db0abcb5fa26d6198 Tue 2015-02-03 22:17:03 UTC—Tue 2015-02-03 22:19:08 UTC
 0 bed718b17a73415fade0e4e7f4bea609 Tue 2015-02-03 22:19:12 UTC—Tue 2015-02-03 23:01:01 UTC
</code></pre>
<p>This will display a line for each boot.  The first column is the offset for the boot that can be used to easily reference the boot with <code>journalctl</code>.  If you need an absolute reference, the boot ID is in the second column.  You can tell the time that the boot session refers to with the two time specifications listed towards the end.</p>

<p>To display information from these boots, you can use information from either the first or second column.</p>

<p>For instance, to see the journal from the previous boot, use the <code>-1</code> relative pointer with the <code>-b</code> flag:</p>
<pre class="code-pre "><code langs="">journalctl -b -1
</code></pre>
<p>You can also use the boot ID to call back the data from a boot:</p>
<pre class="code-pre "><code langs="">journalctl -b caf0524a1d394ce0bdbcff75b94444fe
</code></pre>
<h3 id="time-windows">Time Windows</h3>

<p>While seeing log entries by boot is incredibly useful, often you may wish to request windows of time that do not align well with system boots.  This may be especially true when dealing with long-running servers with significant uptime.</p>

<p>You can filter by arbitrary time limits using the <code>--since</code> and <code>--until</code> options, which restrict the entries displayed to those after or before the given time, respectively.</p>

<p>The time values can come in a variety of formats.  For absolute time values, you should use the following format:</p>
<pre class="code-pre "><code langs="">YYYY-MM-DD HH:MM:SS
</code></pre>
<p>For instance, we can see all of the entries since January 10th, 2015 at 5:15 PM by typing:</p>
<pre class="code-pre "><code langs="">journalctl --since "2015-01-10 17:15:00"
</code></pre>
<p>If components of the above format are left off, some defaults will be applied.  For instance, if the date is omitted, the current date will be assumed.  If the time component is missing, "00:00:00" (midnight) will be substituted.  The seconds field can be left off as well to default to "00":</p>
<pre class="code-pre "><code langs="">journalctl --since "2015-01-10" --until "2015-01-11 03:00"
</code></pre>
<p>The journal also understands some relative values and named shortcuts.  For instance, you can use the words "yesterday", "today", "tomorrow", or "now".  You do relative times by prepending "-" or "+" to a numbered value or using words like "ago" in a sentence construction.</p>

<p>To get the data from yesterday, you could type:</p>
<pre class="code-pre "><code langs="">journalctl --since yesterday
</code></pre>
<p>If you received reports of a service interruption starting at 9:00 AM and continuing until an hour ago, you could type:</p>
<pre class="code-pre "><code langs="">journalctl --since 09:00 --until "1 hour ago"
</code></pre>
<p>As you can see, it's relatively easy to define flexible windows of time to filter the entries you wish to see.</p>

<h2 id="filtering-by-message-interest">Filtering by Message Interest</h2>

<p>We learned above some ways that you can filter the journal data using time constraints.  In this section we'll discuss how to filter based on what service or component you are interested in.  The <code>systemd</code> journal provides a variety of ways of doing this.</p>

<h3 id="by-unit">By Unit</h3>

<p>Perhaps the most useful way of filtering is by the unit you are interested in.  We can use the <code>-u</code> option to filter in this way.</p>

<p>For instance, to see all of the logs from an Nginx unit on our system, we can type:</p>
<pre class="code-pre "><code langs="">journalctl -u nginx.service
</code></pre>
<p>Typically, you would probably want to filter by time as well in order to display the lines you are interested in.  For instance, to check on how the service is running today, you can type:</p>
<pre class="code-pre "><code langs="">journalctl -u nginx.service --since today
</code></pre>
<p>This type of focus becomes extremely helpful when you take advantage of the journal's ability to interleave records from various units.  For instance, if your Nginx process is connected to a PHP-FPM unit to process dynamic content, you can merge the entries from both in chronological order by specifying both units:</p>
<pre class="code-pre "><code langs="">journalctl -u nginx.service -u php-fpm.service --since today
</code></pre>
<p>This can make it much easier to spot the interactions between different programs and debug systems instead of individual processes.</p>

<h3 id="by-process-user-or-group-id">By Process, User, or Group ID</h3>

<p>Some services spawn a variety of child processes to do work.  If you have scouted out the exact PID of the process you are interested in, you can filter by that as well.</p>

<p>To do this we can filter by specifying the <code>_PID</code> field.  For instance if the PID we're interested in is 8088, we could type:</p>
<pre class="code-pre "><code langs="">journalctl _PID=8088
</code></pre>
<p>At other times, you may wish to show all of the entries logged from a specific user or group.  This can be done with the <code>_UID</code> or <code>_GID</code> filters.  For instance, if your web server runs under the <code>www-data</code> user, you can find the user ID by typing:</p>
<pre class="code-pre "><code langs="">id -u www-data
</code></pre><pre class="code-pre "><code langs="">33
</code></pre>
<p>Afterwards, you can use the ID that was returned to filter the journal results:</p>
<pre class="code-pre "><code langs="">journalctl _UID=<span class="highlight">33</span> --since today
</code></pre>
<p>The <code>systemd</code> journal has many fields that can be used for filtering.  Some of those are passed from the process being logged and some are applied by <code>journald</code> using information it gathers from the system at the time of the log.</p>

<p>The leading underscore indicates that the <code>_PID</code> field is of the latter type.  The journal automatically records and indexes the PID of the process that is logging for later filtering.  You can find out about all of the available journal fields by typing:</p>
<pre class="code-pre "><code langs="">man systemd.journal-fields
</code></pre>
<p>We will be discussing some of these in this guide.  For now though, we will go over one more useful option having to do with filtering by these fields.  The <code>-F</code> option can be used to show all of the available values for a given journal field.</p>

<p>For instance, to see which group IDs the <code>systemd</code> journal has entries for, you can type:</p>
<pre class="code-pre "><code langs="">journalctl -F _GID
</code></pre><pre class="code-pre "><code langs="">32
99
102
133
81
84
100
0
124
87
</code></pre>
<p>This will show you all of the values that the journal has stored for the group ID field.  This can help you construct your filters.</p>

<h3 id="by-component-path">By Component Path</h3>

<p>We can also filter by providing a path location.</p>

<p>If the path leads to an executable, <code>journalctl</code> will display all of the entries that involve the executable in question.  For instance, to find those entries that involve the <code>bash</code> executable, you can type:</p>
<pre class="code-pre "><code langs="">journalctl /usr/bin/bash
</code></pre>
<p>Usually, if a unit is available for the executable, that method is cleaner and provides better info (entries from associated child processes, etc).  Sometimes, however, this is not possible.</p>

<h3 id="displaying-kernel-messages">Displaying Kernel Messages</h3>

<p>Kernel messages, those usually found in <code>dmesg</code> output, can be retrieved from the journal as well.</p>

<p>To display only these messages, we can add the <code>-k</code> or <code>--dmesg</code> flags to our command:</p>
<pre class="code-pre "><code langs="">journalctl -k
</code></pre>
<p>By default, this will display the kernel messages from the current boot.  You can specify an alternative boot using the normal boot selection flags discussed previously.  For instance, to get the messages from five boots ago, you could type:</p>
<pre class="code-pre "><code langs="">journalctl -k -b -5
</code></pre>
<h3 id="by-priority">By Priority</h3>

<p>One filter that system administrators often are interested in is the message priority.  While it is often useful to log information at a very verbose level, when actually digesting the available information, low priority logs can be distracting and confusing.</p>

<p>You can use <code>journalctl</code> to display only messages of a specified priority or above by using the <code>-p</code> option.  This allows you to filter out lower priority messages.</p>

<p>For instance, to show only entries logged at the error level or above, you can type:</p>
<pre class="code-pre "><code langs="">journalctl -p err -b
</code></pre>
<p>This will show you all messages marked as error, critical, alert, or emergency.  The journal implements the standard <code>syslog</code> message levels.  You can use either the priority name or its corresponding numeric value.  In order of highest to lowest priority, these are:</p>

<ul>
<li>0: emerg</li>
<li>1: alert</li>
<li>2: crit</li>
<li>3: err</li>
<li>4: warning</li>
<li>5: notice</li>
<li>6: info</li>
<li>7: debug</li>
</ul>

<p>The above numbers or names can be used interchangeably with the <code>-p</code> option.  Selecting a priority will display messages marked at the specified level and those above it.</p>

<h2 id="modifying-the-journal-display">Modifying the Journal Display</h2>

<p>Above, we demonstrated entry selection through filtering.  There are other ways we can modify the output though.  We can adjust the <code>journalctl</code> display to fit various needs.</p>

<h3 id="truncate-or-expand-output">Truncate or Expand Output</h3>

<p>We can adjust how <code>journalctl</code> displays data by telling it to shrink or expand the output.</p>

<p>By default, <code>journalctl</code> will show the entire entry in the pager, allowing the entries to trail off to the right of the screen.  This info can be accessed by pressing the right arrow key.</p>

<p>If you'd rather have the output truncated, inserting an ellipsis where information has been removed, you can use the <code>--no-full</code> option:</p>
<pre class="code-pre "><code langs="">journalctl --no-full
</code></pre><pre class="code-pre "><code langs="">. . .

Feb 04 20:54:13 journalme sshd[937]: Failed password for root from 83.234.207.60...h2
Feb 04 20:54:13 journalme sshd[937]: Connection closed by 83.234.207.60 [preauth]
Feb 04 20:54:13 journalme sshd[937]: PAM 2 more authentication failures; logname...ot
</code></pre>
<p>You can also go in the opposite direction with this and tell <code>journalctl</code> to display all of its information, regardless of whether it includes unprintable characters.  We can do this with the <code>-a</code> flag:</p>
<pre class="code-pre "><code langs="">journalctl -a
</code></pre>
<h3 id="output-to-standard-out">Output to Standard Out</h3>

<p>By default, <code>journalctl</code> displays output in a pager for easier consumption.  If you are planning on processing the data with text manipulation tools, however, you probably want to be able to output to standard output.</p>

<p>You can do this with the <code>--no-pager</code> option:</p>
<pre class="code-pre "><code langs="">journalclt --no-pager
</code></pre>
<p>This can be piped immediately into a processing utility or redirected into a file on disk, depending on your needs.</p>

<h3 id="output-formats">Output Formats</h3>

<p>If you are processing journal entries, as mentioned above, you most likely will have an easier time parsing the data if it is in a more consumable format.  Luckily, the journal can be displayed in a variety of formats as needed.  You can do this using the <code>-o</code> option with a format specifier.</p>

<p>For instance, you can output the journal entries in JSON by typing:</p>
<pre class="code-pre "><code langs="">journalctl -b -u nginx -o json
</code></pre><pre class="code-pre "><code langs="">{ "__CURSOR" : "s=13a21661cf4948289c63075db6c25c00;i=116f1;b=81b58db8fd9046ab9f847ddb82a2fa2d;m=19f0daa;t=50e33c33587ae;x=e307daadb4858635", "__REALTIME_TIMESTAMP" : "1422990364739502", "__MONOTONIC_TIMESTAMP" : "27200938", "_BOOT_ID" : "81b58db8fd9046ab9f847ddb82a2fa2d", "PRIORITY" : "6", "_UID" : "0", "_GID" : "0", "_CAP_EFFECTIVE" : "3fffffffff", "_MACHINE_ID" : "752737531a9d1a9c1e3cb52a4ab967ee", "_HOSTNAME" : "desktop", "SYSLOG_FACILITY" : "3", "CODE_FILE" : "src/core/unit.c", "CODE_LINE" : "1402", "CODE_FUNCTION" : "unit_status_log_starting_stopping_reloading", "SYSLOG_IDENTIFIER" : "systemd", "MESSAGE_ID" : "7d4958e842da4a758f6c1cdc7b36dcc5", "_TRANSPORT" : "journal", "_PID" : "1", "_COMM" : "systemd", "_EXE" : "/usr/lib/systemd/systemd", "_CMDLINE" : "/usr/lib/systemd/systemd", "_SYSTEMD_CGROUP" : "/", "UNIT" : "nginx.service", "MESSAGE" : "Starting A high performance web server and a reverse proxy server...", "_SOURCE_REALTIME_TIMESTAMP" : "1422990364737973" }

. . .
</code></pre>
<p>This is useful for parsing with utilities.  You could use the <code>json-pretty</code> format to get a better handle on the data structure before passing it off to the JSON consumer:</p>
<pre class="code-pre "><code langs="">journalctl -b -u nginx -o json-pretty
</code></pre><pre class="code-pre "><code langs="">{
    "__CURSOR" : "s=13a21661cf4948289c63075db6c25c00;i=116f1;b=81b58db8fd9046ab9f847ddb82a2fa2d;m=19f0daa;t=50e33c33587ae;x=e307daadb4858635",
    "__REALTIME_TIMESTAMP" : "1422990364739502",
    "__MONOTONIC_TIMESTAMP" : "27200938",
    "_BOOT_ID" : "81b58db8fd9046ab9f847ddb82a2fa2d",
    "PRIORITY" : "6",
    "_UID" : "0",
    "_GID" : "0",
    "_CAP_EFFECTIVE" : "3fffffffff",
    "_MACHINE_ID" : "752737531a9d1a9c1e3cb52a4ab967ee",
    "_HOSTNAME" : "desktop",
    "SYSLOG_FACILITY" : "3",
    "CODE_FILE" : "src/core/unit.c",
    "CODE_LINE" : "1402",
    "CODE_FUNCTION" : "unit_status_log_starting_stopping_reloading",
    "SYSLOG_IDENTIFIER" : "systemd",
    "MESSAGE_ID" : "7d4958e842da4a758f6c1cdc7b36dcc5",
    "_TRANSPORT" : "journal",
    "_PID" : "1",
    "_COMM" : "systemd",
    "_EXE" : "/usr/lib/systemd/systemd",
    "_CMDLINE" : "/usr/lib/systemd/systemd",
    "_SYSTEMD_CGROUP" : "/",
    "UNIT" : "nginx.service",
    "MESSAGE" : "Starting A high performance web server and a reverse proxy server...",
    "_SOURCE_REALTIME_TIMESTAMP" : "1422990364737973"
}

. . .
</code></pre>
<p>The following formats can be used for display:</p>

<ul>
<li><strong>cat</strong>: Displays only the message field itself.</li>
<li><strong>export</strong>: A binary format suitable for transferring or backing up.</li>
<li><strong>json</strong>: Standard JSON with one entry per line.</li>
<li><strong>json-pretty</strong>: JSON formatted for better human-readability</li>
<li><strong>json-sse</strong>: JSON formatted output wrapped to make add server-sent event compatible</li>
<li><strong>short</strong>: The default <code>syslog</code> style output</li>
<li><strong>short-iso</strong>: The default format augmented to show ISO 8601 wallclock timestamps.</li>
<li><strong>short-monotonic</strong>: The default format with monotonic timestamps.</li>
<li><strong>short-precise</strong>: The default format with microsecond precision</li>
<li><strong>verbose</strong>: Shows every journal field available for the entry, including those usually hidden internally.</li>
</ul>

<p>These options allow you to display the journal entries in the whatever format best suits your current needs.</p>

<h2 id="active-process-monitoring">Active Process Monitoring</h2>

<p>The <code>journalctl</code> command imitates how many administrators use <code>tail</code> for monitoring active or recent activity.  This functionality is built into <code>journalctl</code>, allowing you to access these features without having to pipe to another tool.</p>

<h3 id="displaying-recent-logs">Displaying Recent Logs</h3>

<p>To display a set amount of records, you can use the <code>-n</code> option, which works exactly as <code>tail -n</code>.</p>

<p>By default, it will display the most recent 10 entries:</p>
<pre class="code-pre "><code langs="">journalctl -n
</code></pre>
<p>You can specify the number of entries you'd like to see with a number after the <code>-n</code>:</p>
<pre class="code-pre "><code langs="">journalctl -n 20
</code></pre>
<h3 id="following-logs">Following Logs</h3>

<p>To actively follow the logs as they are being written, you can use the <code>-f</code> flag.  Again, this works as you might expect if you have experience using <code>tail -f</code>:</p>
<pre class="code-pre "><code langs="">journalctl -f
</code></pre>
<h2 id="journal-maintenance">Journal Maintenance</h2>

<p>You may be wondering about the cost is of storing all of the data we've seen so far.  Furthermore, you may be interesting in cleaning up some older logs and freeing up space.</p>

<h3 id="finding-current-disk-usage">Finding Current Disk Usage</h3>

<p>You can find out the amount of space that the journal is currently occupying on disk by using the <code>--disk-usage</code> flag:</p>
<pre class="code-pre "><code langs="">journalctl --disk-usage
</code></pre><pre class="code-pre "><code langs="">Journals take up 8.0M on disk.
</code></pre>
<h3 id="deleting-old-logs">Deleting Old Logs</h3>

<p>If you wish to shrink your journal, you can do that in two different ways (available with <code>systemd</code> version 218 and later).</p>

<p>If you use the <code>--vacuum-size</code> option, you can shrink your journal by indicating a size.  This will remove old entries until the total journal space taken up on disk is at the requested size:</p>
<pre class="code-pre "><code langs="">sudo journalctl --vacuum-size=1G
</code></pre>
<p>Another way that you can shrink the journal is providing a cutoff time with the <code>--vacuum-time</code> option.  Any entries beyond that time are deleted.  This allows you to keep the entries that have been created after a specific time.</p>

<p>For instance, to keep entries from the last year, you can type:</p>
<pre class="code-pre "><code langs="">sudo journalctl --vacuum-time=1years
</code></pre>
<h3 id="limiting-journal-expansion">Limiting Journal Expansion</h3>

<p>You can configure your server to place limits on how much space the journal can take up.  This can be done by editing the <code>/etc/systemd/journald.conf</code> file.</p>

<p>The following items can be used to limit the journal growth:</p>

<ul>
<li><strong><code>SystemMaxUse=</code></strong>: Specifies the maximum disk space that can be used by the journal in persistent storage.</li>
<li><strong><code>SystemKeepFree=</code></strong>: Specifies the amount of space that the journal should leave free when adding journal entries to persistent storage.</li>
<li><strong><code>SystemMaxFileSize=</code></strong>: Controls how large individual journal files can grow to in persistent storage before being rotated.</li>
<li><strong><code>RuntimeMaxUse=</code></strong>: Specifies the maximum disk space that can be used in volatile storage (within the <code>/run</code> filesystem).</li>
<li><strong><code>RuntimeKeepFree=</code></strong>: Specifies the amount of space to be set aside for other uses when writing data to volatile storage (within the <code>/run</code> filesystem).</li>
<li><strong><code>RuntimeMaxFileSize=</code></strong>: Specifies the amount of space that an individual journal file can take up in volatile storage (within the <code>/run</code> filesystem) before being rotated.</li>
</ul>

<p>By setting these values, you can control how <code>journald</code> consumes and preserves space on your server.</p>

<h2 id="conclusion">Conclusion</h2>

<p>As you can see, the <code>systemd</code> journal is incredibly useful for collecting and managing your system and application data.  Most of the flexibility comes from the extensive metadata automatically recorded and the centralized nature of the log.  The <code>journalctl</code> command makes it easy to take advantage of the advanced features of the journal and to do extensive analysis and relational debugging of different application components.</p>

    