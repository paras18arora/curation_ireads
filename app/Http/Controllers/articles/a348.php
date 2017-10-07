<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Mytop is an open source, command line tool used for monitoring MySQL performance. It was inspired by the Linux system monitoring tool named top and is similar to it in look and feel. Mytop connects to a MySQL server and periodically runs the <code>show processlist</code> and <code>show global status</code> commands. It then summarizes the information in a useful format. Using mytop, we can monitor (in real-time) MySQL threads, queries, and uptime as well as see which user is running queries on which database, which are the slow queries, and more. All this information can be used to optimize the MySQL server performance.</p>

<p>In this tutorial, we will discuss how to install, configure, and use mytop.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you get started with this tutorial, you should have the following:</p>

<ul>
<li>CentOS 7 64-bit Droplet (works with CentOS 6 as well)</li>
<li>Non-root user with sudo privileges. To setup a user of this type, follow the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup with CentOS 7</a> tutorial. All commands will be run as this user.</li>
<li>MySQL server running on the Droplet. To install MySQL, please follow Step #2 of the <a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-centos-7">How To Install Linux, Apache, MySQL, PHP (LAMP) stack on CentOS</a> article.</li>
</ul>

<h2 id="step-1-—-installing-mytop">Step 1 — Installing Mytop</h2>

<p>Let us install the packages required for mytop. </p>

<p>First, we need to install the EPEL (Extra Packages for Enterprise Linux) yum repository on the server. EPEL is a Fedora Special Interest Group that creates, maintains, and manages a high quality set of open source add-on software packages for Enterprise Linux. Run the following command to install and enable the EPEL repository on your server:</p>

<p>On CentOS 7:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm -ivh http://dl.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-5.noarch.rpm
</li></ul></code></pre>
<p>On CentOS 6:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo rpm -ivh http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
</li></ul></code></pre>
<p>Before proceeding, verify that the EPEL repo is enabled using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum repolist
</li></ul></code></pre>
<p>If enabled, you will see the following repo listed in the output:</p>
<pre class="code-pre "><code langs="">epel/x86_64                                                            Extra Packages for Enterprise Linux 7 - x86_64
</code></pre>
<p>Next, let us protect the base packages from EPEL using the yum plugin <strong>protectbase</strong>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install yum-plugin-protectbase.noarch -y
</li></ul></code></pre>
<p>The purpose of the <strong>protectbase</strong> plugin is to protect certain yum repositories from updates from other repositories. Packages in the protected repositories will not be updated or overridden by packages in non-protected repositories even if the non-protected repository has a later version.</p>

<p>Now we are ready to install mytop package. Run the following command to install it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install mytop -y
</li></ul></code></pre>
<p>This will install the mytop package as well as all its dependencies, mostly perl modules.</p>

<h2 id="step-2-—-configuring-mytop">Step 2 — Configuring Mytop</h2>

<p>Before using mytop, create a customized configuration file for mytop named <code>.mytop</code>. Run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /root/.mytop
</li></ul></code></pre>
<p>and add the following content in the file and save and exit.</p>
<div class="code-label " title="/root/.mytop">/root/.mytop</div><pre class="code-pre "><code langs="">host=localhost
db=mysql
delay=5
port=3306
socket=
batchmode=0
color=1
idle=1
</code></pre>
<p>This configuration file will be used when you run mytop directly as root and when you run it with the <code>sudo</code> command in front of it as a non-root sudo user.</p>

<p>You can make changes to this configuration file depending on your needs. For example, the <code>delay</code> option specifies the amount of time in seconds between display refreshes. If you wish to refresh the mytop display every 3 seconds, you could edit the file  <code>/root/.mytop</code> using</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /root/.mytop
</li></ul></code></pre>
<p>and change the following:</p>
<div class="code-label " title="/root/.mytop">/root/.mytop</div><pre class="code-pre "><code langs="">delay=<span class="highlight">3</span>
</code></pre>
<p>The <code>idle</code> parameter specifies whether to allow idle (sleeping) threads to appear in the list in mytop display screen. The default is to show idle threads. If idle threads are omitted, the default sorting order is reversed so that the longest running queries appear at the top of the list. If you wish to do this, edit the <code>/root/.mytop</code> file and change the following:</p>
<div class="code-label " title="/root/.mytop">/root/.mytop</div><pre class="code-pre "><code langs="">idle=<span class="highlight">0</span>
</code></pre>
<p>You can refer the manual pages of mytop for information on all the parameters in the configuration file — it contains a description of each parameter. To access the manual page, use the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">man mytop
</li></ul></code></pre>
<p>You can type <code>q</code> to quit the manual.</p>

<h2 id="step-3-—-connecting-to-mytop">Step 3 — Connecting to Mytop</h2>

<p>In this section, we will discuss how to connect to mytop and use it to view MySQL queries.</p>

<p>Mytop requires credentials to access the database, which can be provided via a prompt, on the command line, or stored in the configuration file. For better security, we will use the <code>--prompt</code> option to mytop, which asks for the password each time.<br />
Let us connect to mytop using:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mytop --prompt
</li></ul></code></pre>
<p>and enter the MySQL root password at the prompt. You can also use several command line arguments with the <code>mytop</code> command. Please refer the manual page for the complete list. For example, if you want to use a different mysql user such as <strong>sammy</strong> to connect to mytop, run the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mytop -u sammy --prompt
</li></ul></code></pre>
<p>To connect and monitor only a specific database, you can use the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mytop -d <span class="highlight">databasename</span> --prompt
</li></ul></code></pre>
<p>To quit mytop and return to your shell prompt, type <code>q</code>.</p>

<h2 id="step-4-—-viewing-and-interpreting-the-mytop-display">Step 4 — Viewing and Interpreting the Mytop Display</h2>

<p>In this section, we will see how to interpret mytop display and the different features offered by the tool.</p>

<p>Once we connect to mytop using <code>mytop --prompt</code> we will be taken to the <strong>thread view</strong>. It will show something similar to:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output of mytop">Output of mytop</div>MySQL on localhost (5.5.41-MariaDB)                    up 0+00:05:52 [01:33:15]
 Queries: 148  qps:    0 Slow:     0.0         Se/In/Up/De(%):    09/00/00/00 
             qps now:    2 Slow qps: 0.0  Threads:    6 (   5/   0) 67/00/00/00 
 Key Efficiency: 2.0%  Bps in/out:  14.7/320.7k   Now in/out: 192.5/731.8k

      Id      User         Host/IP         DB      Time    Cmd Query or State
       --      ----         -------         --      ----    --- ----------
        2      root       localhost      mysql         0  Query show full processlist                          
       16      root       localhost                    0  Sleep
       17      root       localhost     testdb         0  Query SELECT * FROM dept_emp
       18      root       localhost     testdb         0  Query SELECT * FROM dept_emp
       19      root       localhost     testdb         0  Query SELECT * FROM dept_emp
       20      root       localhost     testdb         0  Query SELECT * FROM dept_emp
</code></pre>
<p>You can get back to this view if you are in another view by typing <code>t</code>.</p>

<p>The above display screen is broken into two parts. The top four lines comprises the <strong>header</strong> which can be toggled on or off by pressing <strong>SHIFT-H</strong>. The header contains summary information about your MySQL server. </p>

<ul>
<li><p>The first line identifies the hostname of the server and the version of MySQL it is running. The right-hand side shows the uptime of the MySQL server process in days+hours:minutes:seconds format as well as the current time. </p></li>
<li><p>The second line displays the total number of queries the server has processed (148 in our case), the average number of queries per second, the number of slow queries, and the percentage of Select, Insert, Update, and Delete queries. </p></li>
<li><p>The third line shows real-time values since last mytop refresh. The normal refresh (delay) time for mytop is 5 seconds, so if 100 queries were run in the last 5 seconds since the refresh, then the <code>qps now</code> number would be 20. The first field is the number of queries per second (<code>qps now:    2</code>). The second value is the number of slow queries per second. The <code>Threads:    6 (   5/   0)</code> segment indicates there are total 6 connected threads, 5 are active (one is sleeping), and there are 0 threads in the thread cache. The last field in the third line shows the query percentages, like in the previous line, but since last mytop refresh.</p></li>
<li><p>The fourth line displays key buffer efficiency (how often keys are read from the buffer rather than disk) and the number of bytes that MySQL has sent and received, both overall and in the last mytop cycle. <code>Key Efficiency: 2.0%</code> shows 2% of keys are read from the buffer, not from disk. <code>Bps in/out:  14.7/320.7k</code> shows that since startup, MySQL has averaged 14.7kbps of inbound traffic and 320.7kbps for outbound traffic. <code>Now in/out</code> shows the traffic again, but since last mytop refresh.</p></li>
</ul>

<p>The second part of the display lists current MySQL threads, sorted according to their idle time (least idle first). You can reverse the sort order by pressing <strong>O</strong> if needed. The thread id, username, host from which the user is connecting, database to which the user is connected, number of seconds of idle time, the command the thread is executing (or the state of the thread), and first part of the query info are all displayed here. If the thread is in a <strong>Query</strong> state(i.e. <code>Cmd</code> displays <strong>Query</strong>) then the next column <code>Query or State</code> will show the first part of the query that is being run. If the command state is <strong>Sleep</strong> or <strong>Idle</strong> then the <code>Query or State</code> column will usually be blank. In our example output above, thread with id <strong>2</strong> is actually mytop running the <code>show processlist</code> query to collect information. The thread with id <strong>16</strong> is sleeping (not processing a query, but still connected). The thread with id <strong>17</strong> is running a SELECT query on <strong>testdb</strong> database.</p>

<p>Now that we have understood the basic display of mytop, we will see how to use it to collect more information on the MySQL threads and queries. Let us take a look at the following mytop display:</p>
<pre class="code-pre "><code langs="">[secondary_output Output of mytop]
MySQL on localhost (5.5.41-MariaDB)                    up 0+00:13:10 [23:54:45]
 Queries: 2.8k   qps:    4 Slow:    51.0         Se/In/Up/De(%):    45/00/00/00 
             qps now:   17 Slow qps: 0.0  Threads:   52 (  51/   0) 96/00/00/00 
 Key Efficiency: 100.0%  Bps in/out: 215.4/ 7.6M   Now in/out:  2.0k/16.2M

      Id      User         Host/IP         DB      Time    Cmd Query or State
       --      ----         -------         --      ----    --- ----------
       34      root       localhost     testdb         0  Query show full processlist
     1241      root       localhost                    1  Sleep
     1242      root       localhost     testdb         1  Query SELECT * FROM dept_emp
     1243      root       localhost     testdb         1  Query SELECT * FROM dept_emp
     1244      root       localhost     testdb         1  Query SELECT * FROM dept_emp
     1245      root       localhost     testdb         1  Query SELECT * FROM dept_emp
     1246      root       localhost     testdb         1  Query SELECT * FROM dept_emp
     1247      root       localhost     testdb         1  Query SELECT * FROM dept_emp
</code></pre>
<p>In the mytop <strong>thread view</strong> (default view) shown above, the queries are truncated. To view the entire query, you can press <strong>F</strong>, and it will ask:</p>
<pre class="code-pre "><code langs="">Full query for which thread id:
</code></pre>
<p>Enter the thread id for the query you want to see. For example, enter <code>1244</code>. Then it will show the following:</p>
<pre class="code-pre "><code langs="">Thread 1244 was executing following query:

SELECT * FROM dept_emp WHERE ...

-- paused. press any key to resume or (e) to explain --
</code></pre>
<p>We can type <code>e</code> to explain the query. This will explain the query that is being run so that we can figure out if the query is optimized. EXPLAIN is one of the most powerful tools for understanding and optimizing troublesome MySQL queries. For example:</p>
<pre class="code-pre "><code langs="">EXPLAIN SELECT * FROM dept_emp:

*** row 1 ***
          table:  dept_emp
           type:  ALL
  possible_keys:  NULL
            key:  NULL
        key_len:  NULL
            ref:  NULL
           rows:  332289
          Extra:  NULL
-- paused. press any key to resume --
</code></pre>
<p>You can press any key to exit this mode or type <code>t</code> to go back to the default thread view.</p>

<p>Another useful view available in mytop is the command view. To access command view, type <code>c</code>. It will look similar to the following:</p>
<pre class="code-pre "><code langs="">           Command      Total  Pct  |  Last  Pct
           -------      -----  ---  |  ----  ---
            select       1782  55%  |   100   8%
       show status        723  22%  |   533  45%
  show processlist        708  22%  |   532  45%
         change db          2   0%  |     0   0%
    show variables          1   0%  |     0   0%
       Compression          0   0%  |     0   0%

</code></pre>
<p>The <code>Command</code> column shows the type of command or query being run. The <code>Total</code> column stands for the total number of that type of command run since the server started, and the <code>Pct</code> column shows the same in percentage. On the other side of the vertical line we have the <code>Last</code> column which tells us the number of that type of command run since the last refresh of mytop. This information gives us insight into what the MySQL server is doing in the short term and the long term. </p>

<p>We have discussed some of the important and useful features of mytop in this tutorial. There are many others available. To view a complete list of options, you can press the <strong>?</strong> key while mytop is running.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a good understanding of how to use mytop to monitor your MySQL server. It is also a starting point to finding problem SQL queries and optimizing them, thus increasing the overall performance of the server. You can get more information on how to optimize MySQL queries and tables on your server in <a href="https://indiareads/community/tutorials/how-to-optimize-queries-and-tables-in-mysql-and-mariadb-on-a-vps">this tutorial</a>.</p>

    