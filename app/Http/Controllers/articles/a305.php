<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>Introduction</h3>

<p>MySQL query profiling is a useful technique when trying to analyze the overall performance of a database driven application.  When developing a mid to large size application, there tends to be hundreds of queries distributed throughout a large code base and potentially numerous queries ran against the database per second.  Without some sort of query profiling techniques, it becomes very difficult to determine locations and causes of bottlenecks and applications slow down. This article will demonstrate some useful query profiling techniques using tools that are built into MySQL server.</p>

<h2>What is the MySQL slow query log?</h2>

The MySQL slow query log is a log that MySQL sends slow, potentially problematic queries to.  This logging functionality comes with MySQL but is turned off by default.  What queries are logged is determined by customizable server variables that allow for query profiling based on an application’s performance requirements. Generally the queries that are logged are queries that take longer than a specified amount of time to execute or queries that do not properly hit indexes.

<h2>Setting up profiling variables</h2>

<p>The primary <a href="http://dev.mysql.com/doc/refman/5.5/en/slow-query-log.html" target="_blank">server variables</a> for setting up the MySQL slow query log are:</p>

<pre>
slow_query_log			G 
slow_query_log_file			G 
long_query_time			G / S
log_queries_not_using_indexes	G
min_examined_row_limit		G / S
</pre>

<p><em><strong>NOTE:</strong> (G) global variable, (S) session variable</em></p>

<p><strong>slow_query_log</strong> - Boolean for turning the slow query log on and off.</p>

<p><strong>slow_query_log_file</strong> - The absolute path for the query log file.  The file’s directory should be owned by the mysqld user and have the correct permissions to be read from and written to.  The mysql daemon will likely be running as `mysql` but to verify run the following in the Linux terminal:</p>

<pre>
 ps -ef | grep bin/mysqld | cut -d' ' -f1
</pre>

<p>The output will likely display the current user as well as the mysqld user.  An example of setting the directory path /var/log/mysql:</p>

<pre>
cd /var/log
mkdir mysql
chmod 755 mysql
chown mysql:mysql mysql
</pre>

<p><strong>long_query_time</strong> - The time, in seconds, for checking query length.  For a value of 5, any query taking longer than 5s to execute would be logged.</p>

<p><strong>log_queries_not_using_indexes</strong> - Boolean value whether to log queries that are not hitting indexes.  When doing query analysis, it is important to log queries that are not hitting indexes.</p>

<p><strong>min_examined_row_limit</strong> - Sets a lower limit on how many rows should be examined.  A value of 1000 would ignore any query that analyzes less than 1000 rows.</p>

<p>The MySQL server variables can be set in the MySQL conf file or dynamically via a MySQL GUI or MySQL command line.  If the variables are set in the conf file, they will be persisted when the server restarts but will also require a server restart to become active.  The MySQL conf file is usually located in `/etc or /usr`, typically `/etc/my.cnf` or `/etc/mysql/my.cnf`.  To find the conf file (may have to broaden search to more root directories):</p>

<pre>
find /etc -name my.cnf
find /usr -name my.cnf
</pre>

<p>Once the conf file has been found, simply append the desired values under the [mysqld] heading:</p>

<pre>
[mysqld]
….
slow-query-log = 1
slow-query-log-file = /var/log/mysql/localhost-slow.log
long_query_time = 1
log-queries-not-using-indexes
</pre>

<p>Again, the changes will not take affect until after a server restart, so if the changes are needed immediately then set the variables dynamically:</p>

<pre>
mysql> SET GLOBAL slow_query_log = 'ON';
mysql> SET GLOBAL slow_query_log_file = '/var/log/mysql/localhost-slow.log';
mysql> SET GLOBAL log_queries_not_using_indexes = 'ON';
mysql> SET SESSION long_query_time = 1;
mysql> SET SESSION min_examined_row_limit = 100;
</pre>

<p>To check the variable values:</p>

<pre>
mysql> SHOW GLOBAL VARIABLES LIKE 'slow_query_log';
mysql> SHOW SESSION VARIABLES LIKE 'long_query_time';
</pre>

<p>One drawback to setting MySQL variables dynamically is that the variables will be lost upon server restart.  It is advisable to add any important variables that you need to be persisted to the MySQL conf file.</p>

<p><em><strong>NOTE</strong>: The syntax for setting variables dynamically via SET and placing them into the conf file are slightly different, e.g. `slow_query_log` vs. `slow-query-log`. View MySQL's <a href="http://dev.mysql.com/doc/refman/5.5/en/dynamic-system-variables.html" target="_blank">dynamic system variables</a> page for the different syntaxes. The Option-File Format is the format for the conf file and System Variable Name is the variable name for setting the variables dynamically.</em></p>

<h2>Generating query profile data</h2>

<p>Now that the MySQL slow query log configurations have been outlined, it is time to generate some query data for profiling.  This example was written on a running MySQL instance with no prior slow log configurations set. The example’s queries can be run via a MySQL GUI or through the MySQL command prompt. When monitoring the slow query log, it is useful to have two connection windows open to the server: one connection for writing the MySQL statements and one connection for watching the query log.</p>

<p>In the MySQL console tab, log into MySQL server with a user who has SUPER ADMIN privileges. To start, create a test database and table, add some dummy data, and turn on the slow query log. This example should be run in a development environment, ideally with no other applications using MySQL to help avoid cluttering the query log as it is being monitored:</p>

<pre>
$> mysql -u <user_name> -p

mysql> CREATE DATABASE profile_sampling;
mysql> USE profile_sampling;
mysql> CREATE TABLE users ( id TINYINT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255) );
mysql> INSERT INTO users (name) VALUES ('Walter'),('Skyler'),('Jesse'),('Hank'),('Walter Jr.'),('Marie'),('Saul'),('Gustavo'),('Hector'),('Mike');

mysql> SET GLOBAL slow_query_log = 1;
mysql> SET GLOBAL slow_query_log_file = '/var/log/mysql/localhost-slow.log';
mysql> SET GLOBAL log_queries_not_using_indexes = 1;
mysql> SET long_query_time = 10;
mysql> SET min_examined_row_limit = 0;
</user_name></pre>

<p>There is now a test database and table with a small amount of test data.  The slow query log was turned on but the query time was intentionally set high and the minimum row examined flag kept off. In the console tab for viewing the log:</p>

<pre>
cd /var/log/mysql
ls -l
</pre>

<p>There should be no slow query log in the folder yet, as no queries have been run. If there is, that means that the slow query log has been turned on and configured in the past, which may skew some of this example’s results. Back in the MySQL tab, run the following SQL:</p>

<pre>
mysql> USE profile_sampling;
mysql> SELECT * FROM users WHERE id = 1;
</pre>

<p>The query executed was a simple select using the Primary Key index from the table. This query was fast and used an index, so there will be no entries in the slow query log for this query. Look back in the query log directory and verify that no log was created. Now back in your MySQL window run:</p>

<pre>
mysql> SELECT * FROM users WHERE name = 'Jesse';
</pre>

<p>This query was run on a non indexed column – name.  At this point there will be a query in the log with the following info (may not be exactly the same):</p>

<p><strong>/var/log/mysql/localhost-slow.log</strong></p>

<pre>
# Time: 140322 13:54:58
# User@Host: root[root] @ localhost []
# Query_time: 0.000303  Lock_time: 0.000090 Rows_sent: 1  Rows_examined: 10
use profile_sampling;
SET timestamp=1395521698;
SELECT * FROM users WHERE name = 'Jesse';
</pre>

<p>The query has been successfully logged. One more example. Raise the minimum examined row limit and run a similar query:</p>

<pre>
mysql> SET min_examined_row_limit = 100;
mysql> SELECT * FROM users WHERE name = 'Walter';
</pre>

<p>No data will be added to the log because the minimum of 100 rows was not analyzed.</p>

<em><strong>NOTE:</strong> If there is no data being populated into the log, there are a couple of things that can be checked. First the permissions of the directory in which the log is being created in. The owner/group should be the same as the mysqld user (see above for example) as well as have correct permissions, chmod 755 to be sure.  Second, there may have been existing slow query variable configurations that are interfering with the example. Reset the defaults by removing any slow query variables from the conf file and restarting the server, or set the global variables dynamically back to their default values. If the changes are made dynamically, logout and log back into MySQL to ensure the global updates take effect.</em>

<h2>Analyzing query profile information</h2>

<p>Looking at the query profile data from the above example:</p>

<pre>
# Time: 140322 13:54:58
# User@Host: root[root] @ localhost []
# Query_time: 0.000303  Lock_time: 0.000090 Rows_sent: 1  Rows_examined: 10
use profile_sampling;
SET timestamp=1395521698;
SELECT * FROM users WHERE name = 'Jesse';
</pre>

<p>The entry displays:</p>

<ul>
<li>Time at which the query was ran</li>
<li>Who ran it</li>
<li>How long the query took</li>
<li>Length of the lock</li>
<li>How many rows where returned</li>
<li>How many rows where examined</li>
</ul>

<p>This is useful because any query that violates the performance requirements specified with the server variables will end up in the log.  This allows a developer, or admin, to have MySQL alert them when a query is not performing as well as it should [opposed to reading through source code and trying to find poorly written queries].  Also, the query profiling data can be useful when it is profiled over a period of time, which can help determine what circumstances are contributing to poor application performance.</p>

<h2>Using mysqldumpslow</h2>

<p>In a more realistic example, profiling would be enabled on a database driven application, providing a moderate stream of data to profile against. The log would be continually getting written to, likely more frequently than anybody would be watching.  As the log size grows, it becomes difficult to parse through all the data and problematic queries easily get lost in the log.  MySQL offers another tool, mysqldumpslow, that helps avoid this problem by breaking down the slow query log. The binary is bundled with MySQL (on Linux) so to use it simply run the command and pass in the log path:</p>

<pre>
mysqldumpslow -t 5 -s at /var/log/mysql/localhost-slow.log
</pre>

There are <a href="http://dev.mysql.com/doc/refman/5.5/en/mysqldumpslow.html" target="_blank">various parameters</a> that can be used with the command to help customize output. In the above example the top 5 queries sorted by the average query time will be displayed. The resulting rows are more readable as well as grouped by query (this output is different from the example to demonstrate high values):

<pre>
Count: 2  Time=68.34s (136s)  Lock=0.00s (0s)  Rows=39892974.5 (79785949), root[root]@localhost
  SELECT PL.pl_title, P.page_title
  FROM page P
  INNER JOIN pagelinks PL
  ON PL.pl_namespace = P.page_namespace
  WHERE P.page_namespace = N
… 
</pre>

<p>The data being displayed:</p>

<ul>
<li>Count - How many times the query has been logged</li>
<li>Time - Both the average time and the total time in the ()</li>
<li>Lock - Table lock time</li>
<li>Rows - Number of rows returned</li>
</ul>

<p>The command abstracts numbers and strings, so the same queries with different WHERE clauses will be counted as the same query (notice the page_namespace = N). Having a tool like mysqldumpslow prevents the need to constantly watch the slow query log, instead allowing for periodic or automated checks. The parameters to the mysqldumpslow command allow for some complex expression matching which help drill down into the various queries in the log.</p>

<p>There are also 3rd party log analysis tools available that offer different data views, a popular one being <a href="http://www.percona.com/doc/percona-toolkit/2.2/pt-query-digest.html" target="_blank">pt-query-digest</a>.</p>

<h2>Query breakdown</h2>

<p>One last profiling tool to be aware of is the tool which allows for a complex break down of a query. A good use case for the tool is grabbing a problematic query from the slow query log and running it directly in MySQL. First profiling must be turned on, then the query is ran:</p>

<pre>
mysql> SET SESSION profiling = 1;
mysql> USE profile_sampling;
mysql> SELECT * FROM users WHERE name = 'Jesse';
mysql> SHOW PROFILES;
</pre>

<p>After profiling has been turned on, the SHOW PROFILES will show a table linking a Query_ID to a SQL statement.  Find the Query_ID corresponding to the query ran and run the following query (replace # with your Query_ID):</p>

<pre>
mysql> SELECT * FROM INFORMATION_SCHEMA.PROFILING WHERE QUERY_ID=#;
</pre>

<p>Sample Output:</p>

<table class="table table-bordered">
<thead>
<tr>
<th>SEQ</th>
<th>STATE</th>
<th>DURATION</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>starting</td>
<td>0.000046</td>
</tr>
<tr>
<td>2</td>
<td>checking permissions</td>
<td>0.000005</td>
</tr>
<tr>
<td>3</td>
<td>opening tables</td>
<td>0.000036</td>
</tr>
<tr>
<td>...</td>
<td>...</td>
<td>...</td>
</tr>
</tbody>
</table>

<p>The STATE is the "step" in the process of executing the query, and the DURATION is how long that step took to complete, in seconds.  This isn't an overly useful tool, but it is interesting and can help determine what part of the query execution is causing the most latency.</p>

<p>For a detailed outline of the various columns:
<a href="http://dev.mysql.com/doc/refman/5.5/en/profiling-table.html" target="_blank">http://dev.mysql.com/doc/refman/5.5/en/profiling-table.html</a></p>

<p>For a detailed overview of the various "steps": 
<a href="http://dev.mysql.com/doc/refman/5.5/en/general-thread-states.html" target="_blank">http://dev.mysql.com/doc/refman/5.5/en/general-thread-states.html</a></p>

<p><em><strong>NOTE:</strong> This tool should NOT be used in a production environment rather for analyzing specific queries.</em></p>

<h2>Slow query log performance</h2>

<p>One last question to address is how the slow query log will affect performance. In general it is safe to run the slow query log in a production environment; neither the CPU nor the I/O load should be a concern <a href="http://www.mysqlperformanceblog.com/2009/02/10/impact-of-logging-on-mysql%E2%80%99s-performance/" target="_blank">¹</a> <a href="http://www.amazon.com/High-Performance-MySQL-Optimization-Replication/dp/1449314287" target="_blank">²</a>. However, there should be some strategy for monitoring the log size to ensure the log file size does not get too big for the file system. Also, a good rule of thumb when running the slow query log in a production environment is to leave long_query_time at 1s or higher.</p>  

<p><em><strong>IMPORTANT:</strong> It is not a good idea to use the profiling tool, SET profiling=1, nor to log all queries, i.e. the general_log variable, in a production, high workload environment</em>.</p>

<h3>Conclusion</h3>

<p>The slow query log is extremely helpful in singling out problematic queries and profiling overall query performance. When query profiling with the slow query log, a developer can get an in-depth understanding of how an application's MySQL queries are performing. Using a tool such as mysqldumpslow, monitoring and evaluating the slow query log becomes manageable and can easily be incorporated into the development process. Now that problematic queries have been identified, the next step is to tune the queries for maximum performance.</p>

<div class="author">Article Submitted by: <a href="http://jessesnet.com/development-notes">Jesse Cascio</a></div></div>
    