<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Django is an excellent Python based platform for building modern web apps. One of its biggest strengths is that it helps developers work faster.</p>

<p>You've built your awesome app and deployed it. Things are great, but now that you're loading it up with larger amounts of data and you're starting to have several people use it at the same time, it's not as fast as you'd like.</p>

<p>It's a common problem. Fortunately, we have some tools to help alleviate the problems.</p>

<p>First, let's check for a few of the more obvious issues:</p>

<h2 id="use-a-real-database">Use a Real Database</h2>

<hr />

<p>During local development, it's hard to beat SQLite3. Unless you're careful, you might be using it on your virtual server as well.</p>

<p>SQLite3 doesn't scale to multiple simultaneous users like MySQL and PostgreSQL do, especially for operations that do many write operations (if you're using sessions then you are writing to the database).</p>

<p>If you're using a low-memory VPS, for example the 512MB droplet, I'd suggest using MySQL. If you have memory to spare (2GB or more), then I'd urge you to consider PostgreSQL, since it is preferred by many of the Django developers.</p>

<h2 id="disable-debug-mode">Disable Debug Mode</h2>

<hr />

<p>Debug mode is absolutely necessary when doing local development, but it will slow down your production server. Look at your virtual server's settings.py and check to ensure that DEBUG is set to False. Also confirm that TEMPLATE_DEBUG is also set to False or that it is set to DEBUG.</p>

<h2 id="use-the-debug-toolbar-to-pin-down-performance-problems">Use the Debug Toolbar to Pin Down Performance Problems</h2>

<hr />

<p>On your local development computer, not your production server, turn on <a href="https://github.com/django-debug-toolbar/django-debug-toolbar">Django Debug Toolbar</a> to locate specific problems.</p>

<p>You do this by installing the <strong>django-debug-toolbar</strong> module and then adding an entry to your MIDDLEWARE_CLASSES dictionary like this:</p>
<pre class="code-pre "><code langs="">MIDDLEWARE_CLASSES = (
    # ...
    'debug_toolbar.middleware.DebugToolbarMiddleware',
    # ...
)
</code></pre>
<p>You'll also need to create an INTERNAL_IPS variable and add your IP address. If you're developing locally, your IP address is probably 127.0.0.1, so you'd add a line like this to your settings.py:</p>
<pre class="code-pre "><code langs="">INTERNAL_IPS = ('127.0.0.1',)
</code></pre>
<p>Finally, add <strong>debug_toolbar</strong> as the last item in your INSTALLED_APPS, like this:</p>
<pre class="code-pre "><code langs="">INSTALLED_APPS = (
    # ...
    'debug_toolbar',
)
</code></pre>
<p>The <a href="http://django-debug-toolbar.readthedocs.org/en/latest/installation.html">installation documentation</a> contains some more detail and optional configuration options you may want to consider.</p>

<p>Remember, don't push these changes to production on accident! (If you do it on purpose, that's fine).</p>

<p>Now you should see a black panel appear down the side of your web pages as you browse around the site. If you like stats and numbers and all kinds of geeky details, you'll love it. You'll also quickly see why you don't want this on your production server!</p>

<p><a href="Signals"><img src="https://assets.digitalocean.com/articles/scale_django/img1.png" /></a></p>

<p>Having built numerous Django apps, I'll suggest you narrow in on the SQL section of the panel, since that is commonly an area of concern.</p>

<p>One benefit/problem that Django has is <a href="https://docs.djangoproject.com/en/1.6/ref/models/querysets/#when-querysets-are-evaluated">lazy loading</a> of related fields when queries are performed. This means that Django will prefer not to do a join. If you end up needing related fields, it will do an additional query each time a related field is needed.</p>

<p>If you do need related fields, this can causes n+1 number of SQL queries. For example, let's say you are going to make a replacement version of Twitter. You create a model for tweets and each model is related to the User model. On your homepage, you list the 30 latest tweets along with the name of the user who created them. This can cause you to do at least 31 SQL queries. One query to get the list of tweets, and one query for each username lookup.</p>

<p>The solution to this problem is <a href="https://docs.djangoproject.com/en/1.6/ref/models/querysets/#select-related">select_related</a>. This is a very simple modification to the query that causes Django to do a join when fetching data. You should use it on any lookup where you know you'll need related fields.</p>

<p>You simply modify a query like this:</p>
<pre class="code-pre "><code langs="">Entry.objects.get(id=5)
</code></pre>
<p>to look like this:</p>
<pre class="code-pre "><code langs="">Entry.objects.select_related().get(id=5)
</code></pre>
<p>Read the documentation for this feature and use it only when necessary.</p>

<h2 id="wrap-up">Wrap Up</h2>

<hr />

<p>In my experience, the above issues solve many sites' initial performance problems and are all quite easy to fix with minimal code and configuration changes.</p>

<p></p><div class="author">Submitted by: <a href="http://www.bearfruit.org/">Matthew Nuzum</a></div>

    