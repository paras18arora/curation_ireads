<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="about-lithium">About Lithium</h3>

<hr />

<p><a href="http://li3.me/">Lithium</a> is a full stack PHP framework for developing web applications. Based on the Model-View-Controller (MVC) architecture, it is built for PHP 5.3+ and integrates with the latest storage technologies like MongoDB or CouchDB.</p>

<p>It is designed to offer both a great project organisation as well as the possibility to code out of the framework as you develop your own unique web application. Additionally, it features a robust plugin system that allows you to use your favorite components from outside the framework (such as Twig for templating or Doctrine2 for ORM).</p>

<p>In this tutorial, we will continue where we left off previously when we <a href="https://indiareads/community/articles/how-to-install-and-start-using-lithium-on-ubuntu-12-04-vps">installed Lithium onto our Ubuntu 12.04 VPS</a> and configured all the necessary requirements for building our web application. If you remember, we also connected a MySQL database and had a glimpse of how to print out "Hello World" onto the page. What will follow now is illustrating how to use the three MVC components (Models, Views and Controllers) together with the Lithium framework.</p>

<h2 id="controller">Controller</h2>

<hr />

<p>We've seen already in action a simple controller class (<em>HelloController</em>) and its index (that is, its default) method. We'll continue building on it to illustrate 3 more MVC related aspects:</p>

<ul>
<li><p>Using other controller class methods and how they related to the URL that we call in the browser</p></li>
<li><p>Passing information from the controller to be displayed in a View (which is a best practice as opposed to just <em>echo-ing</em> strings directly from the controller methods).</p></li>
<li><p>Creating a model to represent our data in the database</p></li>
</ul>

<h2 id="routing">Routing</h2>

<hr />

<p>To illustrate how the controller maps to the URL we need to access in the browser, let's create another method in our <em>HelloController</em>:</p>
<pre class="code-pre "><code langs="">public function goodbye() {
    echo "Goodbye!";
}
</code></pre>
<p>Now when we point our browser to <code>your-ip/sites/hello/goodbye</code>, we'll see the word "Goodbye" printed on the screen. This is the defaul routing behaviour of Lithium by which the first url parameter that we pass is the name of the controller (<strong>Hello</strong> <- notice the lack of the word "Controller") and the second one is the name of the method (<strong>goodbye</strong>). </p>

<p>We can take if further and even pass a parameter to the method like so:</p>
<pre class="code-pre "><code langs="">public function goodbye($name) {
    echo "Goodbye " . $name . '!';
}
</code></pre>
<p>Then we can point our browser to <code>your-ip/sites/hello/goodbye/danny</code> and you'll see printed "Goodbye danny!" on the screen. So it's quite handy and logical, working similar to <a href="https://indiareads/community/articles/how-to-install-codeigniter-on-an-ubuntu-12-04-cloud-server">CodeIgniter</a>.</p>

<p>As straightforward as this may be, it does not satisfy the need of every project. In that case, you have the possibility to define custom routing rules and map urls to controller methods as you want. Read <a href="http://li3.me/docs/manual/handling-http-requests/routing.md">more information</a> on the Lithium documentation site. </p>

<h2 id="views">Views</h2>

<hr />

<p>As I mentioned, the MVC architecture promotes a separation of logic from presentation so let's see how we can use Lithium Views to display information built in our <code>HelloController</code> class. Going back to the <code>goodbye()</code> method we created earlier, let's assume that we need the parameter we pass to it (the <code>$name</code>) printed out in a View. </p>

<p>The first thing we need to do is have this method pass the variable to the View. One of the ways this can be achieved is by returning an associative array of keys and values. Therefore, alter the <code>goodbye()</code> method to look like this:</p>
<pre class="code-pre "><code langs="">public function goodbye($name) {
    return array(
      'name' => $name,  
    );
}
</code></pre>
<p>As you can see, all the method does is returns an array that contains the variable (passed from the URL). The key that correlates with the <code>$name</code> variable will be available in the View for outputting as a variable. </p>

<p>Now, let's go ahead and create a View file in the app/views/ folder that has the same name as the controller method, and resides in a folder named after the controller. So in our case it would be (while being in the project root folder):</p>
<pre class="code-pre "><code langs="">nano app/views/hello/goodbye.html.php
</code></pre>
<p>Inside this file now paste the following:</p>
<pre class="code-pre "><code langs=""><h1>Goodbye <?=$name;?>!</h1>
</code></pre>
<p>Now when you navigate to the previous url:</p>
<pre class="code-pre "><code langs="">your-ip/sites/hello/goodbye/danny
</code></pre>
<p>You should see in between the header tags how the <code>$name</code> variable we got from passing to the controller, in turn was passed to and printed by the View. Another cool thing is that the value gets escaped automatically by Lithium. </p>

<p>You'll also notice that our view is injected inside an existing layout that comes by default with Lithium (with a small menu at the top etc). But for more information about using Views and layouts, check the <a href="http://li3.me/docs/manual/handling-http-requests/views.md">Lithium docs</a>. </p>

<h2 id="models">Models</h2>

<hr />

<p>Having seen how we route requests and display information, let's see how we can get information from our database by creating a model to represent it. And since convention goes a long way with Lithium, we won't have to do a lot before we can see some awesome results. </p>

<p>First off, make sure you have the database and a table in it. I will call mine news and it will have 3 columns: id, title, and body. Additonally, populate this table with some dummy content so you have something to play around with. For more information about working with MySQL, you can read this great <a href="https://indiareads/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu">tutorial</a> on IndiaReads. But don't forget that with Lithium you have the option of using other storage engines.</p>

<p>To speed things along you can quickly create this table from the command line after logging in to your MySQL server by running this command:</p>
<pre class="code-pre "><code langs="">CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`id`)
);
</code></pre>
<p>And you can also insert two dummy rows with the following two commands:</p>
<pre class="code-pre "><code langs="">INSERT INTO `news` (title, body)  VALUES ('This is the first news article', 'some article body');
INSERT INTO `news` (title, body)  VALUES ('This is other news', 'some more article body');
</code></pre>
<p>Now that we have some content, let's create a News.php file to hold our model class in the app/models/ folder:</p>
<pre class="code-pre "><code langs="">nano app/models/News.php
</code></pre>
<p>Inside, paste the following:</p>
<pre class="code-pre "><code langs=""><?php

namespace app\models;

class News extends \lithium\data\Model {
}

?>
</code></pre>
<p>For now, that's all we need in this file. The base class we are extending provides plenty of methods we can use to interact with our data. </p>

<p>Back in our <code>HelloController</code> class, add the following line above the class declaration:</p>
<pre class="code-pre "><code langs="">use app\models\News;
</code></pre>
<p>Now let's add another method inside the class:</p>
<pre class="code-pre "><code langs="">public function news($id) {
    $news = News::first(array(
      'conditions' => array('id' => $id)
    ));
    return array(
      'news' => $news->data()  
    );
  }
</code></pre>
<p>This method takes the parameter from the URL (the news id), retrieves the news with that id, and passes the result to the View. It uses the Lithium Model base class to do the query using the <code>find()</code> method, and the data is then accessed in the resulting object using the <code>data()</code> method. Now let's create the View to show the news article:</p>
<pre class="code-pre "><code langs="">nano app/views/hello/news.html.php
</code></pre>
<p>And paste the following:</p>
<pre class="code-pre "><code langs=""><h1><?=$news['title'];?></h1>
<p><?=$news['body'];?></p>
</code></pre>
<p>When making these changes, be sure that you have the correct MySQL credentials in the configuration file:</p>
<pre class="code-pre "><code langs="">nano /var/www/site/app/config/bootstrap/connections.php
</code></pre>
<p>As you can see, the <code>$news</code> variable is an array with keys named after the table columns. Neat. Now point your browser to the following url:</p>
<pre class="code-pre "><code langs="">your-ip/site/hello/news/1
</code></pre>
<p>And you should see the first news article. Pass <code>2</code> as the last argument and you should see the second news article, etc. For more detailed information about using Lithium models, read the respective docs <a href="http://li3.me/docs/manual/working-with-data/using-models.md">here</a>.</p>

<h3 id="conclusion">Conclusion</h3>

<hr />

<p>In this tutorial,we've played around a bit with Lithium, learning how the basic routing system works and how the URL gets translated into controller requests. Additionally, we've seen how to work with Views to show information passed from controllers and how to integrate our database information using models. </p>

<div class="author">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div>

    