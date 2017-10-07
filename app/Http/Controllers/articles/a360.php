<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="an-article-from-the-mysql-team-at-oracle">An Article from the <a href="http://www.oracle.com/us/products/mysql/mysqlcommunityserver/overview/index.html">MySQL Team at Oracle</a></h3>

<h2 id="introduction">Introduction</h2>

<p>MySQL 5.7 is the most current release candidate of the popular open-source database. It offers new scalability features that should have you eager to make the change.</p>

<p>To highlight one of the changes, scalability has been greatly improved. On the high end, MySQL 5.7 scales linearly on 48-core servers. On the low end, MySQL 5.7 also works out of the box on a 512 MB IndiaReads Droplet (something that was not possible without configuration changes in MySQL 5.6).</p>

<p>The new peak performance for a MySQL server is over 640K queries per second, and the memcached API, which speaks directly to the InnoDB storage engine, is capable of sustaining <a href="https://blogs.oracle.com/mysqlinnodb/entry/mysql_5_7_3_deep">over 1.1 million requests per second</a>.</p>

<p><a href="https://blogs.oracle.com/mysqlinnodb/entry/mysql_5_7_3_deep"><img src="https://assets.digitalocean.com/articles/mysql_57_upgrade/1mm-memcached-api.png" alt="MySQL 5.7 Performance using the Memcached NoSQL API" /></a></p>

<p>Before you rush to run <code>mysql_upgrade</code>, though, you should make sure you're prepared. This tutorial can help you do just that.</p>

<h2 id="data-integrity-changes-with-examples">Data Integrity Changes, with Examples</h2>

<p>One major change in MySQL 5.7 is that data integrity has been improved to be more in line with what veteran developers and DBAs expect. Previously, MySQL would adjust incorrect values to the closest possible correct value, but under the new defaults it will instead return an error.</p>

<p>Here are five examples of queries that will require modification to work in MySQL 5.7 out of the box. Does your application use any of these behaviors?</p>

<h3 id="1-inserting-a-negative-value-into-an-unsigned-column">1) Inserting a negative value into an unsigned column</h3>

<p>Create a table with an unsigned column:</p>
<pre class="code-pre "><code langs="">CREATE TABLE test (  
 id int unsigned  
);
</code></pre>
<p>Insert a negative value.</p>

<p>Previous behavior:</p>
<pre class="code-pre "><code langs="">INSERT INTO test VALUES (-1);
Query OK, 1 row affected, 1 warning (0.01 sec)
</code></pre>
<p>MySQL 5.7:</p>
<pre class="code-pre "><code langs="">INSERT INTO test VALUES (-1);  
ERROR 1264 (22003): Out of range value for column 'a' at row 1
</code></pre>
<h3 id="2-division-by-zero">2) Division by zero</h3>

<p>Create a test table:</p>
<pre class="code-pre "><code langs="">CREATE TABLE test2 (  
 id int unsigned  
);
</code></pre>
<p>Attempt to divide by zero.</p>

<p>Previous behavior:</p>
<pre class="code-pre "><code langs="">INSERT INTO test2 VALUES (0/0);  
Query OK, 1 row affected (0.01 sec)
</code></pre>
<p>MySQL 5.7:</p>
<pre class="code-pre "><code langs="">INSERT INTO test2 VALUES (0/0);  
ERROR 1365 (22012): Division by 0
</code></pre>
<h3 id="3-inserting-a-20-character-string-into-a-10-character-column">3) Inserting a 20 character string into a 10 character column</h3>

<p>Create a table with a 10-character column:</p>
<pre class="code-pre "><code langs="">CREATE TABLE test3 (  
a varchar(10)  
);
</code></pre>
<p>Try to insert a longer string.</p>

<p>Previous behavior:</p>
<pre class="code-pre "><code langs="">INSERT INTO test3 VALUES ('abcdefghijklmnopqrstuvwxyz'); 
Query OK, 1 row affected, 1 warning (0.00 sec)
</code></pre>
<p>MySQL 5.7:</p>
<pre class="code-pre "><code langs="">INSERT INTO test3 VALUES ('abcdefghijklmnopqrstuvwxyz');  
ERROR 1406 (22001): Data too long for column 'a' at row 1
</code></pre>
<h3 id="4-inserting-the-non-standard-zero-date-into-a-datetime-column">4) Inserting the non standard zero date into a datetime column</h3>

<p>Create a table with a datetime column:</p>
<pre class="code-pre "><code langs="">CREATE TABLE test3 (  
a datetime  
);
</code></pre>
<p>Insert <code>0000-00-00 00:00:00</code>.</p>

<p>Previous behavior:</p>
<pre class="code-pre "><code langs="">INSERT INTO test3 VALUES ('0000-00-00 00:00:00');  
Query OK, 1 row affected, 1 warning (0.00 sec)
</code></pre>
<p>MySQL 5.7:</p>
<pre class="code-pre "><code langs="">INSERT INTO test3 VALUES ('0000-00-00 00:00:00');  
ERROR 1292 (22007): Incorrect datetime value: '0000-00-00 00:00:00' for column 'a' at row 1
</code></pre>
<h3 id="5-using-group-by-and-selecting-an-ambiguous-column">5) Using GROUP BY and selecting an ambiguous column</h3>

<p>This happens when the description is not part of the <code>GROUP BY</code>, and there is no aggregate function (such as <code>MIN</code> or <code>MAX</code>) applied to it.</p>

<p>Previous Behaviour:</p>
<pre class="code-pre "><code langs="">SELECT id, invoice_id, description FROM invoice_line_items GROUP BY invoice_id;  
+----+------------+-------------+  
| id | invoice_id | description |  
+----+------------+-------------+  
| 1 | 1 | New socks             |  
| 3 | 2 | Shoes                 |  
| 5 | 3 | Tie                   |  
+----+------------+-------------+  
3 rows in set (0.00 sec)
</code></pre>
<p>MySQL 5.7:</p>
<pre class="code-pre "><code langs="">SELECT id, invoice_id, description FROM invoice_line_items GROUP BY invoice_id;  
ERROR 1055 (42000): Expression #3 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'invoice_line_items.description' which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by
</code></pre>
<h2 id="understanding-behaviors-set-by-sql_mode">Understanding Behaviors Set by sql_mode</h2>

<p>In MySQL terms, each of the behaviors shown in the previous section is influenced by what is known as an <code>sql_mode</code>.</p>

<p>The feature debuted in MySQL 4.1 (2004), but has not been compiled in by default. MySQL 5.7 features the following modes turned on by default:</p>

<ul>
<li><a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_only_full_group_by"><code>ONLY_FULL_GROUP_BY</code></a></li>
<li><a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_strict_trans_tables"><code>STRICT_TRANS_TABLES</code></a></li>
<li><a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_no_engine_substitution"><code>NO_ENGINE_SUBSTITUTION</code></a></li>
<li><a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_no_auto_create_user"><code>NO_AUTO_CREATE_USER</code></a></li>
</ul>

<p>The mode <a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_strict_trans_tables"><code>STRICT_TRANS_TABLES</code></a> has also become more strict, and enables the behaviour previously specified under the modes <a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_error_for_division_by_zero"><code>ERROR_FOR_DIVISION_BY_ZERO</code></a>, <a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_no_zero_date"><code>NO_ZERO_DATE</code></a> and <a href="http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sqlmode_no_zero_in_date"><code>NO_ZERO_IN_DATE</code></a>.</p>

<p>Click on any of these mode names to visit the MySQL manual, to find out more information.</p>

<h2 id="suggestions-on-how-to-transition">Suggestions on How to Transition</h2>

<p>If you are using a recent version of Wordpress, Drupal, or Magento the good news is that you do not need to do anything. These applications are already aware of MySQL's <code>sql_mode</code> feature and upon connecting to MySQL will set the options that they are compatible with.</p>

<p>If you are currently <strong>building a new application</strong>, then it may be a good idea to change the configuration of your existing MySQL 5.6 server to behave with the <code>sql_mode</code> settings that are shipped in MySQL 5.7.</p>

<p>If you have an <strong>existing application</strong>, you may want to work through your updates more gradually. These suggestions may help you to transition:</p>

<ul>
<li><strong><a href="http://en.wikipedia.org/wiki/Whitelist">Whitelist</a></strong>: Have new parts of your application enable the new default <tt>sql<em>mode</tt> options. For example, if you are building a set of cron jobs to rebuild caches of data, these can set the <tt>sql</em>mode</tt> as soon as they connect to MySQL. Existing application code can initially stay with the existing non-strict behaviour.</li>
<li><strong><a href="http://en.wikipedia.org/wiki/blacklist">Blacklist</a></strong>: When you have made some headway in converting applications, it is time to make the new <tt>sql<em>mode</tt> the default for your server. It is possible to still have legacy applications the previous behaviour by having them change the `sql</em>mode<code>when they connect to MySQL. On an individual statement basis, MySQL also supports the</code>IGNORE<code>modifier to downgrade errors. For example:</code>INSERT IGNORE INTO my_table <span class="highlight">. . .</span>`</li>
<li><strong>Staged Rollout</strong>: If you are in control of your application, you may be able to implement a feature to change the <code>sql_mode</code> on a per user-basis. A good use case for this would be to allow internal users to beta test everything to allow for a more gradual transition.</li>
</ul>

<h3 id="step-1-—-finding-incompatible-statements-that-produce-warnings-or-errors">Step 1 — Finding Incompatible Statements that Produce Warnings or Errors</h3>

<p>First, see if any of your current queries are producing warnings or errors. This is useful because the behavior for several queries has changed from a warning in 5.6 to an error in 5.7, so you can catch the warnings now before upgrading.</p>

<p>The MySQL <a href="http://dev.mysql.com/doc/refman/5.6/en/performance-schema.html"><code>performance_schema</code></a> is a diagnostic feature which is enabled by default on MySQL 5.6 and above. Using the <code>performance_schema</code>, it's possible to write a query to return all the statements the server has encountered that have produced errors or warnings.</p>

<p><strong>MySQL 5.6+ query to report statements that produce errors or warnings:</strong></p>
<pre class="code-pre "><code langs="">SELECT 
`DIGEST_TEXT` AS `query`,
`SCHEMA_NAME` AS `db`,
`COUNT_STAR` AS `exec_count`,
`SUM_ERRORS` AS `errors`,
(ifnull((`SUM_ERRORS` / nullif(`COUNT_STAR`,0)),0) * 100) AS `error_pct`,
`SUM_WARNINGS` AS `warnings`,
(ifnull((`SUM_WARNINGS` / nullif(`COUNT_STAR`,0)),0) * 100) AS `warning_pct`,
`FIRST_SEEN` AS `first_seen`,
`LAST_SEEN` AS `last_seen`,
`DIGEST` AS `digest`
FROM
 performance_schema.events_statements_summary_by_digest
WHERE
((`SUM_ERRORS` &gt; 0) OR (`SUM_WARNINGS` &gt; 0))
ORDER BY
 `SUM_ERRORS` DESC,
 `SUM_WARNINGS` DESC;
</code></pre>
<p><strong>MySQL 5.6+ query to report statements that produce errors:</strong>  </p>
<pre class="code-pre "><code langs="">SELECT 
`DIGEST_TEXT` AS `query`,
`SCHEMA_NAME` AS `db`,
`COUNT_STAR` AS `exec_count`,
`SUM_ERRORS` AS `errors`,
(ifnull((`SUM_ERRORS` / nullif(`COUNT_STAR`,0)),0) * 100) AS `error_pct`,
`SUM_WARNINGS` AS `warnings`,
(ifnull((`SUM_WARNINGS` / nullif(`COUNT_STAR`,0)),0) * 100) AS `warning_pct`,
`FIRST_SEEN` AS `first_seen`,
`LAST_SEEN` AS `last_seen`,
`DIGEST` AS `digest`
FROM
 performance_schema.events_statements_summary_by_digest
WHERE
 `SUM_ERRORS` &gt; 0
ORDER BY
 `SUM_ERRORS` DESC,
 `SUM_WARNINGS` DESC;
</code></pre>
<h3 id="step-2-—-making-mysql-5-6-behave-like-mysql-5-7">Step 2 — Making MySQL 5.6 Behave Like MySQL 5.7</h3>

<p>You can also do a test run with MySQL 5.6 to make it behave like 5.7.</p>

<p>The author, Morgan Tocker from the MySQL team, has a <a href="https://github.com/morgo/mysql-compatibility-config">GitHub project</a> available with a <a href="https://github.com/morgo/mysql-compatibility-config/blob/master/mysql-56/mysql-57.cnf">sample configuration file</a> that will allow you to do this. By using the upcoming defaults in MySQL 5.6 you will be able to eliminate the chance that your application will depend on the less-strict behaviour.</p>

<p>The file is rather short, so we're also including it here:</p>
<pre class="code-pre "><code langs=""># This makes a MySQL 5.6 server behave similar to the new defaults
# in MySQL 5.7

[mysqld]

# MySQL 5.7 enables more SQL modes by default, but also
# merges ERROR_FOR_DIVISION_BY_ZERO, NO_ZERO_DATE, NO_ZERO_IN_DATE
# into the definition of STRICT_TRANS_TABLES.
# Context: http://dev.mysql.com/worklog/task/?id=7467

sql-mode="ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE"

# The optimizer changes the default from 10 dives to 200 dives by default
# Context: http://mysqlserverteam.com/you-asked-for-it-new-default-for-eq_range_index_dive_limit/

eq_range_index_dive_limit=200

# MySQL 5.7 contains a new internal server logging API.
# The setting log_warnings is deprecated in 5.7.2 in favour of log_error_verbosity.
# *But* the default fo log_warnings also changes to 2 as well:

log_warnings=2

# MySQL 5.7.7 changes a number of replication defaults
# Binary logging is still disabled, but will default to ROW when enabled.

binlog_format=ROW
sync_binlog=1
slave_net_timeout=60

# InnoDB defaults to the new Dynamic Row format with Barracuda file format.
# large_prefix is also enabled, which allows for longer index values.

innodb_strict_mode=1
innodb_file_format=Barracuda
innodb_large_prefix=1
innodb_purge_threads=4 # coming in 5.7.8
innodb_checksum_algorithm=crc32

# In MySQL 5.7 only 20% of the pool will be dumped, 
# But 5.6 does not support this option

innodb_buffer_pool_dump_at_shutdown=1
innodb_buffer_pool_load_at_startup=1

# These two options had different names in previous versions
# (binlogging_impossible_mode,simplified_binlog_gtid_recovery)
# This config file targets 5.6.23+, but includes the 'loose' modifier to not fail
# prior versions.

loose-binlog_error_action=ABORT_SERVER
loose-binlog_gtid_recovery_simplified=1

# 5.7 enable additional P_S consumers by default
# This one is supported in 5.6 as well.
performance-schema-consumer-events_statements_history=ON

</code></pre>
<h3 id="optional-step-3-—-changing-sql_mode-on-a-per-session-basis">(Optional) Step 3 — Changing sql_mode on a Per Session Basis</h3>

<p>Sometimes you want to test or upgrade your server in stages. Rather than changing your server-wide configuration file for MySQL to use new SQL modes, it is also possible to change them on a per session basis. Here is an example:</p>
<pre class="code-pre "><code langs="">CREATE TABLE sql_mode_test (a int);
</code></pre>
<p>No SQL mode set:</p>
<pre class="code-pre "><code langs="">set sql_mode = '';
INSERT INTO sql_mode_test (a) VALUES (0/0);
Query OK, 1 row affected (0.01 sec)
</code></pre>
<p>Stricter SQL mode set:</p>
<pre class="code-pre "><code langs="">set sql_mode = 'STRICT_TRANS_TABLES';
INSERT INTO sql_mode_test (a) VALUES (0/0);
ERROR 1365 (22012): Division by 0
</code></pre>
<h2 id="ready-to-upgrade">Ready to Upgrade</h2>

<p>At this point, you should be confident that you're prepared to upgrade to MySQL 5.7. Follow along with <a href="http://dev.mysql.com/doc/refman/5.7/en/upgrading-from-previous-series.html">MySQL's official upgrade guide</a> to flip the switch.</p>

<h3 id="conclusion">Conclusion</h3>

<p>MySQL 5.7 takes a big step forward in improving the default configuration and data integrity for modern applications. We hope this article helps you make a smooth transition!</p>

<p>For an overview of all the changes in 5.7 (so far), check out the MySQL Server Team's blog posts: </p>

<ul>
<li><a href="http://mysqlserverteam.com/whats-new-in-mysql-5-7-so-far/">What's New in MySQL 5.7? (So Far)</a></li>
<li><a href="http://mysqlserverteam.com/whats-new-in-mysql-5-7-first-release-candidate/">What's New in MySQL 5.7? (First Release Candidate)</a></li>
</ul>

    