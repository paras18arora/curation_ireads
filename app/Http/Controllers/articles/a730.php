<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="background">Background</h3>

<hr />

<p><a href="http://www.fatfreeframework.com">Fat Free Framework</a> is a PHP <em>micro-framework</em> that was started in 2009 by Bong Cosca.  Following a minimalistic approach, it tends to avoid adding code and structure that are not strictly necessary, while focusing on what really matters.</p>

<h3 id="why-a-micro-framework">Why a micro-framework?</h3>

<hr />

<p>This type of minimalistic design is common amongst the so-called micro-frameworks, of which PHP offers a wide choice. Other popular micro-frameworks are: <strong>Slim</strong> (PHP), <strong>Sinatra</strong> (Ruby) and <strong>express.js</strong> (node.js). These frameworks usually have a few advantages like, for example:</p>

<ul>
<li><p>Being extremely lightweight (Fat Free only amounts for roughly 55kb)</p></li>
<li><p>Having a gentle learning curve, allowing developers to focus almost exclusively on what matters by not having to change their coding style.</p></li>
<li><p>Offering many of the functionalities that mature, full-fledged frameworks would usually have.</p></li>
</ul>

<p>It goes without saying that choosing a micro-framework like Fat Free is not always the best choice. For big projects where a team of people is expected, a more opinionated and structured framework such as <a href="http://www.yiiframework.com">Yii</a> or <a href="http://framework.zend.com">Zend</a> would likely be a better choice.</p>

<h2 id="set-up-a-new-project-with-fat-free">Set up a new project with Fat Free</h2>

<hr />

<p>The first steps: <a href="https://github.com/bcosca/fatfree/archive/master.zip">downloading</a> the framework unzipping the file within your project's root folder.</p>

<p>Fat Free only runs on PHP 5.3 and higher. If you're not sure of the version that you're currently using, you can check by typing:</p>
<pre class="code-pre "><code langs="">/path/to/php -v
</code></pre>
<p>After having established that the environment where you're developing is the right one, create a file called <code>index.php</code>, which is going to be your project bootstrap file. On the first line, include Fat Free:</p>
<pre class="code-pre "><code langs="">// FatFree framework
$f3 = require ("fatfree/lib/base.php"); 
</code></pre>
<p>Then, you'll have to tell your application if you're in development or in production mode by setting this variable:</p>
<pre class="code-pre "><code langs="">// Set to 1 when in development mode, otherwise set to 0
$f3->set('DEBUG', 1);
</code></pre>
<h2 id="database">Database</h2>

<hr />

<p>And of course, you'll have to set up a database connection. Assuming you're using MySQL:</p>
<pre class="code-pre "><code langs="">// MySql settings
$f3->set('DB', new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=mydatabase',
    'dbuser',
    'dbpassword'
)); 
</code></pre>
<p>Or, if you prefer using SQLite:</p>
<pre class="code-pre "><code langs="">$db=new DB\SQL('sqlite:/var/www/myproject/db/database.sqlite'));
</code></pre>
<h3 id="queries">Queries</h3>

<hr />

<p>A simple query can be called by typing:</p>
<pre class="code-pre "><code langs="">$result = $db->exec('SELECT field FROM table WHERE id = "1"');
</code></pre>
<p>Or, if you like it, you can use Fat Free's built-in <strong>ORM</strong>. The query above would become something like this:</p>
<pre class="code-pre "><code langs="">$table = new DB\SQL\Mapper($db, 'table');
$table->load(array('id=?', '1'));
$result = $table->field;
</code></pre>
<p>With the <code>DB\SQL\Mapper</code> function, you're essentially "mapping" a table that is already in your database. Should you instead need to add a new record in your table, you'll have to type:</p>
<pre class="code-pre "><code langs="">$table = new DB\SQL\Mapper($db, 'table');
$table->field = "Here is a value";
$table->save();
</code></pre>
<p>Notice: You won't be able to alter your table using ORM.</p>

<h2 id="giving-a-structure-to-your-project">Giving a structure to your project</h2>

<hr />

<p>Since Fat Free is a micro-framework, it doesn't come with a ready-to-use structure for your project, thus you'll have to create it by yourself. An example of a structure for your project could be:</p>
<pre class="code-pre "><code langs="">- api
-- models
- css
- js
- template
- views
- index.php
</code></pre>
<p>But of course you'll be entirely free to use the structure that you love. That's the best thing about using a non-opinionated framework.</p>

<h2 id="autoloading">Autoloading</h2>

<hr />

<p>In order to avoid having to include all your classes into your project, Fat Free allows you to use the <a href="http://www.php.net/manual/en/language.oop5.autoload.php">autoloading</a> feature, which is a way to include classes only at the time you really need them. So, to invoke all our classes, we only need to type:</p>
<pre class="code-pre "><code langs="">$f3->set('AUTOLOAD','api/models/');
</code></pre>
<p>In our case, <code>api/models/</code> will clearly be the location where we save all our Model classes. When you invoke a class (e.g. <code>$myClass = new myClass()</code>), Fat Free will automatically look for a file called in the same way (<code>myClass.php</code>) within the autoloaded location.</p>

<h2 id="routing">Routing</h2>

<hr />

<p>The next interesting thing is the way Fat Free manages our application's routing. This is how we define routing to our home page:</p>
<pre class="code-pre "><code langs="">$f3->route('GET /',
    function() {
        echo 'This is my Home Page!';
    }
);
</code></pre>
<p>Notice the <em>GET</em> attribute there. If needed, it can be replaced with <em>POST</em>, or even with <em>GET|POST</em>, should you need both of them.<br />
And then there's obviously a function that defines what that page should do.<br />
You can of course manage different parameters too, using this syntax:</p>
<pre class="code-pre "><code langs="">$f3->route('GET|POST /post/@id',
    function($f3) {
        echo 'Post #'.$f3->get('PARAMS.id');
    }
);
</code></pre>
<p>As you can see, everything preceded by <code>@</code> will be considered a variable parameter.</p>

<h2 id="templating-and-views">Templating and Views</h2>

<hr />

<p>Fat Free gives you the ability to have your template and views. To include your template/view in a route command, just write:</p>
<pre class="code-pre "><code langs="">$f3->route('GET /',
    function($f3) {
        // Instantiates a View object
        $view = new View;
        // Header template
        echo $view->render('template/header.php');
        // This is a variable that we want to pass to the view
        $f3->set('name','value');
        // Page view
        echo $view->render('views/index.php');
        // Footer template
        echo $view->render('template/footer.php');
    }
);  
</code></pre>
<p>In order to set variables to be passed to a view, you can use the <code>$f3->set('nameVariable', 'value')</code> function, and then invoke that same variable into the view (e.g. <code>views/index.php</code>) by typing `<?php echo $nameVariable; ?>. It's really simple. </p>

<p>In conclusion, these are probably the most useful features that you'll need when developing your first application with Fat Free framework. Should you need more of them, you can always refer to the <a href="http://fatfreeframework.com/user-guide">official documentation</a>.</p>

<div class="author">Submitted by: <a href="http://www.marcotroisi.com/">Marco Troisi </a></div>

    