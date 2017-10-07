<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>MySQL and MariaDB are popular choices for database management systems.  Both use the SQL querying language to input and query data.</p>

<p>Although SQL queries are simple commands that are easy to learn, not all queries and database functions operate with the same efficiency.  This becomes increasingly important as the amount of information you are storing grows and, if your database is backing a website, as your site's popularity increases.</p>

<p>In this guide, we will discuss some simple measures you can take to speed up your MySQL and MariaDB queries.  We will assume that you have already installed MySQL or MariaDB using one of our guides that is appropriate for your operating system.</p>

<h2 id="table-design-generalities">Table Design Generalities</h2>

<hr />

<p>One of the most fundamental ways to improve querying speed begins with the table structure design itself.  This means that you need to begin considering the best way to organize your data <em>before</em> you begin using the software.</p>

<p>These are some questions that you should be asking yourself:</p>

<h3 id="how-will-your-table-primarily-be-used">How Will your Table Primarily be Used?</h3>

<hr />

<p>Anticipating how you will use the table's data often dictates the best approach to designing a data structure.</p>

<p>If you will be updating certain pieces of data often, it is often best to have those in their own table.  Failure to do this can cause the query cache, an internal cache maintained within the software, to be dumped and rebuilt over and over again because it recognizes that there is new information.  If this happens in a separate table, the other columns can continue to take advantage of the cache.</p>

<p>Updating operations are, in general, faster on smaller tables, while in-depth analysis of complex data is usually a task best relegated to large tables, as joins can be costly operations.</p>

<h3 id="what-kind-of-data-types-are-required">What Kind of Data Types are Required?</h3>

<hr />

<p>Sometimes, it can save you significant time in the long run if you can provide some restraints for your data sizes upfront.</p>

<p>For instance, if there are a limited number of valid entries for a specific field that takes string values, you could use the "enum" type instead of "varchar".  This data type is compact and thus quick to query.</p>

<p>For instance, if you have only a few different kinds of users, you could make the column that handles that "enum" with the possible values: admin, moderator, poweruser, user.</p>

<h3 id="which-columns-will-you-be-querying">Which Columns Will You be Querying?</h3>

<hr />

<p>Knowing ahead of time which fields you will be querying repeatedly can dramatically improve your speed.</p>

<p>Indexing columns that you expect to use for searching helps immensely.  You can add an index when creating a table using the following syntax:</p>

<pre>
CREATE TABLE example_table (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(50),
    address VARCHAR(150),
    username VARCHAR(16),
    PRIMARY KEY (id),
    <span class="highlight">INDEX (username)</span>
);
</pre>

<p>This would be useful if we knew that our users were going to be searching for information by username.  This will create a table with these properties:</p>

<pre>
explain example_table;
</pre>

<pre>
+----------+--------------+------+-----+---------+----------------+
| Field    | Type         | Null | Key | Default | Extra          |
+----------+--------------+------+-----+---------+----------------+
| id       | int(11)      | NO   | <span class="highlight">PRI</span> | NULL    | auto_increment |
| name     | varchar(50)  | YES  |     | NULL    |                |
| address  | varchar(150) | YES  |     | NULL    |                |
| username | varchar(16)  | YES  | <span class="highlight">MUL</span> | NULL    |                |
+----------+--------------+------+-----+---------+----------------+
4 rows in set (0.00 sec)
</pre>

<p>As you can see, we have two indices for our table.  The first is the primary key, which in this case is the <code>id</code> field.  The second is the index we've added for the <code>username</code> field.  This will improve queries that utilize this field.</p>

<p>Although it is useful from a conceptual standpoint to think about which fields should be indexed during creation, it is simple to add indices to pre-existing tables as well.  You can add one like this:</p>

<pre>
CREATE INDEX <span class="highlight">index_name</span> ON <span class="highlight">table_name</span>(<span class="highlight">column_name</span>);
</pre>

<p>Another way of accomplishing the same thing is this:</p>

<pre>
ALTER TABLE <span class="highlight">table_name</span> ADD INDEX ( <span class="highlight">column_name</span> );
</pre>

<p><strong>Use Explain to Find Points to Index in Queries</strong></p>

<p>If your program is querying in a very predictable way, you should be analysing your queries to ensure that they are using indices whenever possible.  This is easy with the <code>explain</code> function.</p>

<p>We will import a MySQL sample database to see how some of this works:</p>
<pre class="code-pre "><code langs="">wget https://launchpad.net/test-db/employees-db-1/1.0.6/+download/employees_db-full-1.0.6.tar.bz2
tar xjvf employees_db-full-1.0.6.tar.bz2
cd employees_db
mysql -u root -p -t < employees.sql
</code></pre>
<p>We can now log back into MySQL so that we can run some queries:</p>
<pre class="code-pre "><code langs="">mysql -u root -p
use employees;
</code></pre>
<p>First, we need to specify that MySQL should not be using its cache, so that we can accurately judge the time these tasks take to complete:</p>
<pre class="code-pre "><code langs="">SET GLOBAL query_cache_size = 0;
SHOW VARIABLES LIKE "query_cache_size";

+------------------+-------+
| Variable_name    | Value |
+------------------+-------+
| query_cache_size | 0     |
+------------------+-------+
1 row in set (0.00 sec)
</code></pre>
<p>Now, we can run a simple query on a large dataset:</p>
<pre class="code-pre "><code langs="">SELECT COUNT(*) FROM salaries WHERE salary BETWEEN 60000 AND 70000;
</code></pre>
<hr />
<pre class="code-pre "><code langs="">+----------+
| count(*) |
+----------+
|   588322 |
+----------+
1 row in set (0.60 sec)
</code></pre>
<p>To see how MySQL executes the query, you can add the <code>explain</code> keyword directly before the query:</p>
<pre class="code-pre "><code langs="">EXPLAIN SELECT COUNT(*) FROM salaries WHERE salary BETWEEN 60000 AND 70000;
</code></pre>
<hr />
<pre class="code-pre "><code langs="">+----+-------------+----------+------+---------------+------+---------+------+---------+-------------+
| id | select_type | table    | type | possible_keys | key  | key_len | ref  | rows    | Extra       |
+----+-------------+----------+------+---------------+------+---------+------+---------+-------------+
|  1 | SIMPLE      | salaries | ALL  | NULL          | NULL | NULL    | NULL | 2844738 | Using where |
+----+-------------+----------+------+---------------+------+---------+------+---------+-------------+
1 row in set (0.00 sec)
</code></pre>
<p>If you look at the <code>key</code> field, you will see that it's value is <code>NULL</code>.  This means that no index is being used for this query.</p>

<p>Let's add one and run the query again to see if it speeds it up:</p>
<pre class="code-pre "><code langs="">ALTER TABLE salaries ADD INDEX ( salary );
SELECT COUNT(*) FROM salaries WHERE salary BETWEEN 60000 AND 70000;
</code></pre>
<hr />
<pre class="code-pre "><code langs="">+----------+
| count(*) |
+----------+
|   588322 |
+----------+
1 row in set (0.14 sec)
</code></pre>
<p>As you can see, this significantly improves our querying performance.</p>

<p>Another general rule to use with indices is to pay attention to table joins.  You should create indices and specify the same data type on any columns that will be used to join tables.</p>

<p>For instance, if you have a table called "cheeses" and a table called "ingredients", you may want to join on a similar ingredient_id field in each table, which could be an INT.</p>

<p>We could then create indices for both of these fields and our joins would speed up.</p>

<h2 id="optimizing-queries-for-speed">Optimizing Queries for Speed</h2>

<hr />

<p>The other half of the equation when trying to speed up queries is optimizing the queries themselves.  Certain operations are more computationally intensive than others.  There are often multiple ways of getting the same result, some of which will avoid costly operations.</p>

<p>Depending on what you are using the query results for, you may only need a limited number of the results. For instance, if you only need to find out if there is anyone at the company making less than $40,000, you can use:</p>
<pre class="code-pre "><code langs="">SELECT * FROM SALARIES WHERE salary < 40000 LIMIT 1;
</code></pre>
<hr />
<pre class="code-pre "><code langs="">+--------+--------+------------+------------+
| emp_no | salary | from_date  | to_date    |
+--------+--------+------------+------------+
|  10022 |  39935 | 2000-09-02 | 2001-09-02 |
+--------+--------+------------+------------+
1 row in set (0.00 sec)
</code></pre>
<p>This query executes extremely fast because it basically short circuits at the first positive result.</p>

<p>If your queries use "or" comparisons, and the two components parts are testing different fields, your query can be longer than necessary.</p>

<p>For example, if you are searching for an employee whose first or last name starts with "Bre", you will have to search two separate columns.</p>
<pre class="code-pre "><code langs="">SELECT * FROM employees WHERE last_name like 'Bre%' OR first_name like 'Bre%';
</code></pre>
<p>This operation may be faster if we perform the search for first names in one query, perform the search for matching last names in another, and then combine the output.  We can do this with the union operator:</p>
<pre class="code-pre "><code langs="">SELECT * FROM employees WHERE last_name like 'Bre%' UNION SELECT * FROM employees WHERE first_name like 'Bre%';
</code></pre>
<p>In some instances, MySQL will use a union operation automatically.  The example above is actually a case where MySQL will do this automatically.  You can see if this is the case by checking for the kind of sorting being done by using <code>explain</code> again.</p>

<h2 id="conclusion">Conclusion</h2>

<hr />

<p>There are an extraordinary amount of ways that you can fine-tune your MySQL and MariaDB tables and databases according on your use case.  This article contains just a few tips that might be useful to get you started.</p>

<p>These database management systems have great documentation on how to optimize and fine-tune different scenarios.  The specifics depend greatly on what kind of functionality you wish to optimize, otherwise they would have been completely optimized out-of-the-box.  Once you've solidified your requirements and have a handle on what operations are going to be performed repeatedly, you can learn to tweak your settings for those queries.</p>

<div class="author">By Justin Ellingwood</div>

    