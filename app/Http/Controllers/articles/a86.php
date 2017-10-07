<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p>Amongst the many features that the incredible <a href="http://www.yiiframework.com">Yii framework</a> offers, a cache management system is something that couldn't have been missing.</p>

<p>Yii framework allows us to save both static data and your SQL/Active Record queries, which -if used wisely- can lead to a lot of page loading time saving.</p>

<p>In particular, in this tutorial we are going to learn how to cache Data and Queries.</p>

<p>So here's how we enable cache in Yii.</p>

<h2 id="activate-the-cache-component">Activate the Cache Component</h2>

<hr />

<p>The first step consists in activating the cache component. Just open your configuration file (located under protected/config/), go to the <em>components</em> array, and add the following code right within the array:</p>
<pre class="code-pre "><code langs="">'cache'=>array( 
    'class'=>'system.caching.CDbCache'
)
</code></pre>
<p>By doing so, we are choosing to use <strong>CDbCache</strong>, which is just one of the Caching Components available with Yii. This particular one stores the cached data in a <a href="http://www.sqlite.org">SQLite</a> database, which makes it extremely easy to set up. And while not being the best choice in terms of performance, it will still make our web application slightly faster. </p>

<p>Another viable and more powerful option is to use the <strong>CApcCache</strong> component, which makes use of <a href="http://www.php.net/apc/">APC</a>, the built-in caching system that comes with the newest versions of <a href="http://www.php.net">PHP</a>.</p>

<p>Since all these Cache components are based on top of the <strong>CCache</strong> class, you can easily switch from a cache component to another by changing the name of the component (e.g. system.caching.CApcCache), while not having to change any code throughout your application.</p>

<h2 id="simple-data-caching">Simple Data Caching</h2>

<hr />

<p>The first and simplest way to use cache is by storing variables. To do that, Yii's cache component gives you two functions: <strong>get()</strong> and <strong>set()</strong>.</p>

<p>So we start by setting a value to be cached. To do so, we will also have to assign it a unique ID. For example:</p>
<pre class="code-pre "><code langs="">// Storing $value in Cache
$value = "This is a variable that I am storing";
$id    = "myValue";
$time  = 30; // in seconds

Yii::app()->cache->set($id, $value, $time);
</code></pre>
<p>The last value, <code>$time</code>, is not required, although useful in order to avoid storing a value forever when it's not necessary.</p>

<p>Getting the stored value is trivial:</p>
<pre class="code-pre "><code langs="">Yii::app()->cache->get($id);
</code></pre>
<p>Should the value not be found (because it does not exist or because it did expire before), this function will return a <strong>false</strong> value. Thus, for example, a nice way of checking if a certain value is cached would be:</p>
<pre class="code-pre "><code langs="">$val = Yii::app()->cache->get($id);
if (!$val):
    // the value is not cached, do something here
else:
    // the value is cached, do something else here
endif;
</code></pre>
<h3 id="delete-a-cached-value">Delete a cached value</h3>

<hr />

<p>To delete a value that is stored in cache, we can call:</p>
<pre class="code-pre "><code langs="">Yii::app()->cache->delete($id);
</code></pre>
<p>If what we need is to clean everything, we will just write:</p>
<pre class="code-pre "><code langs="">Yii::app()->cache->flush();
</code></pre>
<h2 id="query-caching">Query Caching</h2>

<hr />

<p>Built on top of the Data Caching system, this is a very useful feature, especially for heavy apps that rely intensely on a Database.</p>

<p>The concept of this feature is fairly easy but pretty solid.</p>

<p>Firstly, what we have to do is to define a <em>dependency query</em>. In other words, we define a much simpler and lighter Database Query that we will call before the one that we really need. The reason for doing that is to check if anything has changed since the last time that we executed that query.</p>

<p>If, for example, the data we want to retrieve is a list of Book Authors, our dependency query might well be:</p>
<pre class="code-pre "><code langs="">SELECT MAX(id) FROM authors
</code></pre>
<p>By doing so, we will be able to see if any new author has been added since the last time we checked. If no new author has been added, Yii's Cache component will take the Authors list directly from the cache, without executing again our big query, which could be something like:</p>
<pre class="code-pre "><code langs="">SELECT authors.*, book.title 
FROM authors 
JOIN book ON book.id = authors.book_id
</code></pre>
<h3 id="yii-query-builder">Yii Query Builder</h3>

<hr />

<p>To use Query Caching with the Yii <a href="http://www.yiiframework.com/doc/guide/1.1/en/database.query-builder">Query Builder</a>, this is what we have to write [using the Authors' example showed before]:</p>
<pre class="code-pre "><code langs="">// big query
$query = ' SELECT authors.*, book.title 
FROM authors 
JOIN book ON book.id = authors.book_id';
// dependency query 
$dependency = new CDbCacheDependency('SELECT MAX(id) FROM authors'); 
// executing query using Yii Query Builder
$result = Yii::app()->db->cache(1000, $dependency)->createCommand($query)->queryAll();
</code></pre>
<p>The arguments passed to <code>Yii::app()->db->cache()</code> are, respectively, the amount of seconds that the result should be stored for and the dependency query.</p>

<p>As explained before, when running this code, Yii will check for the result of the dependency query before anything else. Should it not find anything, or a different value from the one stored before, it will execute the big query and store the result in cache. Otherwise it will extract the big query result from the cache.</p>

<h3 id="active-record">Active Record</h3>

<hr />

<p>It is also possible to cache the result of a query made using <a href="http://www.yiiframework.com/doc/guide/1.1/en/database.ar">Active Record</a>. The concept remains the same as explained before; but with a different syntax, of course:</p>
<pre class="code-pre "><code langs="">$dependency = new CDbCacheDependency('SELECT MAX(id) FROM authors');
$authors = Author::model()->cache(1000, $dependency)->with('book')->findAll();
</code></pre>
<h3 id="things-to-keep-in-mind">Things to keep in mind</h3>

<hr />

<p>It's pretty obvious that an application that makes intensive use of caching would need to be well designed in advance, since the risk of serving inconsistent data to the user will increase inevitably.</p>

<p>Also, don't forget that each caching component might have limits on the amount of data that can be stored. It's thus a good practice to find out in advance the limit of your caching system.</p>

<div class="author">Submitted by: <a href="https://twitter.com/marcotroisi">Marco Troisi </a></div>

    