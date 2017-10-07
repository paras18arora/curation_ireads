<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>The psqlrc file customizes the behavior of the psql interactive command line client. psql enables you to type in queries interactively, issue them to PostgreSQL, and see the query results. The file comes in three flavors: </p>

<p>1) The system-wide psqlrc file is found in PostgreSQL's system configuration directory. </p>

<p>The location of this directory depends on your PostgreSQL installation but can be found by using the pg_config tool.</p>
<pre class="code-pre "><code langs="">pg_config --sysconfdir
</code></pre>
<p>2) The user psqlrc file is found or can be created in the user's home directory.</p>
<pre class="code-pre "><code langs="">touch ~/.psqlrc
</code></pre>
<p>3) Version-specific psqlrc files can be created if there are multiple PostgreSQL installations. Just add the version number to the end.</p>
<pre class="code-pre "><code langs="">touch ~/.psqlrc-9.1
touch ~/.psqlrc-9.3
</code></pre>
<h2 id="installation">Installation</h2>

<p>Before you can use psql, you must have PostgreSQL installed.</p>
<pre class="code-pre "><code langs="">sudo apt-get install -y postgresql postgresql-contrib
</code></pre>
<p>This will install PostgreSQL 9.3. Now you can switch to the postgres user and start psql.</p>
<pre class="code-pre "><code langs="">su - postgres

psql
</code></pre>
<p>This should display the standard psql prompt.</p>
<pre class="code-pre "><code langs="">psql (9.3.4)
Type "help" for help.

postgres=#
</code></pre>
<h2 id="editing-the-prompt">Editing the prompt</h2>

<p>By editing the user psqlrc file you can customize the main psql prompt (PROMPT1) and create useful shortcuts. Edit the .psqlrc file with the editor of your choice to add the following lines (here we'll use vim).</p>
<pre class="code-pre "><code langs="">vi ~/.psqlrc

\set PROMPT1 '%M:%> %n@%/%R%#%x '
</code></pre>
<ul>
<li>%M refers to the database server's hostname -- is "[local]" if the connection is over a Unix domain socket</li>
<li>%> refers to the listening port</li>
<li>%n refers to the session username</li>
<li>%/ refers the current database</li>
<li>%R refers to whether you're in single-line mode (^) or disconnected (!) but is normally =</li>
<li>%# refers to whether you're a superuser (#) or a regular user (>)</li>
<li>%x refers to the transaction status -- usually blank unless in a transaction block (*)</li>
</ul>

<p>If logged into a machine with hostname "trident" as user "john" and accessing the database "orange" as a regular user, you would see</p>
<pre class="code-pre "><code langs="">[trident]:5432 john@orange=>
</code></pre>
<p>You can also edit the secondary psql prompt (PROMPT2).</p>
<pre class="code-pre "><code langs="">postgres-#
</code></pre>
<p>You'll run into the secondary prompt when you have an unfinished query.</p>
<pre class="code-pre "><code langs="">postgres=# select * from
postgres-# peel limit 1;
</code></pre>
<p>Editing the secondary psql prompt is mostly similar to editing the primary psql prompt.</p>
<pre class="code-pre "><code langs="">\set PROMPT2 '%M %n@%/%R %# '
</code></pre>
<ul>
<li>%R is represented by '-' instead of '='</li>
</ul>

<p>When in the middle of a transaction on the machine with hostname "trident" as user "john" and accessing the database "orange" as a regular user, you would see</p>
<pre class="code-pre "><code langs="">[trident]:5432 john@orange=> select * from
[trident] john@orange-> peel limit 1;
</code></pre>
<p>Of course, you can add, remove, or rearrange these options to include information that is useful for you.</p>

<h2 id="colors">Colors</h2>

<p>The prompt color can be edited with the psqlrc. To make the port number red add the following.</p>
<pre class="code-pre "><code langs="">\set PROMPT1 '%M:%[%033[1;31m%]%>%[%033[0m%] %n@%/%R%#%x '
</code></pre>
<p>There are various colors you can use – change the value 31 to:</p>

<ul>
<li>32 for green</li>
<li>33 for yellow</li>
<li>34 for blue</li>
<li>35 for magenta</li>
<li>36 for cyan</li>
<li>37 for white</li>
</ul>

<h2 id="display-options">Display options</h2>

<p>When querying a PostgreSQL database null values return a blank. If instead you want it to return the value NULL you can edit the null option.</p>
<pre class="code-pre "><code langs="">\pset null '[null]'
</code></pre>
<p>To complete SQL keywords such as "SELECT" and "FROM" as either uppercase or lowercase, you can set the COMP<em>KEYWORD</em>CASE option with the options upper or lower.</p>
<pre class="code-pre "><code langs="">\set COMP_KEYWORD_CASE upper
</code></pre>
<p>To have all queries display query times using enable the timing option.</p>
<pre class="code-pre "><code langs="">\timing
</code></pre>
<p>As in the bash prompt, on the psql prompt you can press the up arrow key to access previously executed commands via the history. To set the size of the history you can edit HISTSIZE.</p>
<pre class="code-pre "><code langs="">\set HISTSIZE 2000
</code></pre>
<p>When querying large tables sometimes the output renders text that is difficult to read. You can switch to expanded table format.</p>
<pre class="code-pre "><code langs="">\x auto
</code></pre>
<p>You can also set verbosity of error reports with options "default", "verbose", or "terse".</p>
<pre class="code-pre "><code langs="">\set VERBOSITY verbose
</code></pre>
<p>You can setup shortcuts with the set command as well. If you want to setup a shortcut for seeing the PostgreSQL version and available extensions add the following:</p>
<pre class="code-pre "><code langs="">\set version 'SELECT version();'
\set extensions 'select * from pg_available_extensions;'
</code></pre>
<p>If you want to display messages when starting the psql prompt you can use the echo command.</p>
<pre class="code-pre "><code langs="">\echo 'Welcome to PostgreSQL\n'
</code></pre>
<p>Lastly, editing the psqlrc creates outputs when you startup psql. If you want to hide these set the QUIET flag at the top and bottom of the psql file. </p>

<h2 id="wrap-up">Wrap up</h2>

<p>The complete file is below.</p>
<pre class="code-pre "><code langs="">\set QUIET 1

\set PROMPT1 '%M:%[%033[1;31m%]%>%[%033[0m%] %n@%/%R%#%x '

\set PROMPT2 '%M %n@%/%R %# '

\pset null '[null]'

\set COMP_KEYWORD_CASE upper

\timing

\set HISTSIZE 2000

\x auto

\set VERBOSITY verbose

\set QUIET 0

\echo 'Welcome to PostgreSQL! \n'
\echo 'Type :version to see the PostgreSQL version. \n' 
\echo 'Type :extensions to see the available extensions. \n'
\echo 'Type \\q to exit. \n'
\set version 'SELECT version();'
\set extensions 'select * from pg_available_extensions;'
</code></pre>
<p>Now when you start psql you will see a different prompt.</p>
<pre class="code-pre "><code langs="">$ psql

Welcome to PostgreSQL!

Type :version to see the version.

Type :extensions to see the available extensions.

Type \q to exit.

psql (9.3.4)
Type "help" for help.

[local]:5432 postgres@postgres=#    
</code></pre>
<p>There are many more customizations you can make, but these should be a good start to improving your psql experience.</p>

    