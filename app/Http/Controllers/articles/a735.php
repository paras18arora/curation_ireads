<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <div><h3>About PhalconPHP</h3><hr />

<a href="http://phalconphp.com/en/" target="_blank">Phalcon</a> is a PHP framework that promotes the Model-View-Controller architecture and has many framework-like features you'd expect in a piece of software such as this: ORM, templating engine, routing, caching, etc. 
<br /><br />
In this tutorial, we will continue where we left off the <a href="https://indiareads/community/articles/how-to-install-and-get-started-with-phalcon-on-an-ubuntu-12-04-vps" target="_blank">last time</a> when we got Phalcon installed on our Ubuntu 12.04 VPS and managed to print our first string to the screen using a Phalcon controller. In this tutorial, we will touch upon using the other two core MVC components: the views and the models. 
<br /><br />
To follow along this article, I assume you have gone through the steps outlined in the previous tutorial and have your Phalcon application printing out <em>Hello World</em> if you point your browser to your <em>ip-address/project-name</em>. So let’s dig in. 

<h2>Views</h2><hr />

In the previous tutorial we created the default Index controller with one method (IndexAction) that does this:

<pre>echo "<h1>Hello World!</h1>";</pre>
<br />
As you know, this is not the best way to print things onto the screen; and like other PHP frameworks out there, Phalcon comes with a templating system that we should use instead. In other words, we can use a View and pass the <em>Hello World</em> string to it through a variable. 
<br /><br />
The way this works with Phalcon is that in the <em>app/views</em> folder we need to create another folder named as our controller and inside this folder a file named after the controller’s action. This way, Phalcon autoloads this view when the controller action is called. So let’s do this for our first controller/action <em>Index</em>. Create the folder first:

<pre>mkdir /var/www/project_name/app/<WBR />views/index</pre>
<br />
Inside this folder, create a the file with a phtml extension (named after the controller action):

<pre>nano /var/www/project_name/app/<WBR />views/index/index.phtml</pre>
<br />
Inside this file, cut the contents of the IndexAction from the controller and paste it in there, between tags:

<pre><?php echo "<h1>Hello World!</h1>"; ?></pre>
<br />
Now the controller action is empty, yet the string gets printed from the view that corresponds to that controller/action. So in this short example we have not passed anything to the view yet from the controller, we just demonstrated how the view is loaded automatically. Let’s now declare that variable in our controller, and then pass it to the view to be displayed. 
<br /><br />
Go back to the <em>IndexController</em> and inside the <em>indexAction</em> method, paste in the following (making sure this is all there is inside this function):

<pre>$string = "Hello World!";
$this->view->setVar("string", $string);</pre>
<br />
In the first line, we set our text value to the <em>$string</em> variable and in the second one we use the <em>setVar</em> method of the <em>view()</em> method of our parent controller class to send this variable to another one that can be accessed inside the view: also called <em>$string</em>.
<br /><br />
Now edit the view and replace this:

<pre><?php echo "<h1>Hello World!</h1>"; ?></pre>
<br />
With this:

<pre><h1><?php echo $string; ?></h1></pre>
<br />
Now you should get the same thing in the browser. Like this, we separated logic (our string value that for all intents and purposes could have been dynamically generated or retrieved) from presentation (the html header tag we wrapped around the string in the View). This is one of the tenets of the MVC architecture and good modern programming practice. 

<h2>Models and database</h2><hr />

Now that we’ve seen controllers and views, let’s connect a database and see how we can interact with it using models. Continuing from here assumes you have already a database you can use, you know its access credentials, and are familiar with basic MySQL commands. If not, check out <a href="https://indiareads/community/articles/a-basic-mysql-tutorial" target="_blank">this tutorial</a>. 
<br /><br />
To connect our database, we need to edit the bootstrap file we created in the previous article, the <em>index.php</em> file located in the public/ folder:

<pre>nano /var/www/project_name/public/<WBR />index.php</pre>
<br />
Under this block:

<pre>  //Setup a base URI so that all generated URIs include the "tutorial" folder
    $di->set('url', function(){
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri('/project/');
        return $url;
    });</pre>
<br />
Add the following block:

<pre>  //Setup the database service
    $di->set('db', function(){
        return new \Phalcon\Db\Adapter\Pdo\Mysql(<WBR />array(
            "host" => "localhost",
            "username" => "root",
            "password" => "password",
            "dbname" => "db-name"
        ));
    });</pre>
<br />
And of course replace where appropriate with your database information. Save the file and exit. Next, create a table that will host your first model. Let’s call it <em>articles</em> and give it an ID column, a title column, and a body column. The following MySQL command will create it for you. 

<pre>CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`id`)
);</pre>
<br />
Let’s now insert a row through our command line to have something to play with. You can use this MySQL command to do it if you want:

<pre>INSERT INTO `articles` (title, body)  VALUES ('This is the first article', 'some article body');</pre>
<br />
Now that we have some content, let's define our <em>Article</em> model. Create a php file in the <em>app/models</em> folder of your application:

<pre>nano /var/www/project_name/app/<WBR />models/Articles.php</pre>
<br />
And paste this inside (omit the closing php tag):

<pre><?php

class Articles extends \Phalcon\Mvc\Model {

}</pre>
<br />
We are now extending the Phalcon model class, which provides a lot of useful functionality for us to interact with our model data. Another cool thing is that since we named the class as we did the database table, the two are already linked. This model will refer to that database table. 
<br /><br />
Now what we need to do is declare our model properties (that map to the table columns). So inside the class, add the following protected properties:

<pre>public $id;

public $title;

public $body;</pre>
 <br />
Next, we need some setters/getters to retrieve from or to assign values to these protected properties. And here we can have a lot to control over how the data can be accessed. But since this tutorial will not look into adding information to the database, we will only add one getter function to retrieve existing information from the property. So below, but still inside the model class, add the following methods:

<pre>public function getId()    {
    return $this->id;
}

public function getTitle()    {
    return $this->title;
}

public function getBody()    {
    return $this->body;
}</pre>
<br />
Normally however, you’ll also need setter functions. Let's save this file and turn back to our <em>IndexController</em>. Inside the <em>IndexAction</em>, replace all the contents with the following:

<pre>$article = Articles::findFirst(1);
$this->view->setVar("string", $article->getTitle());</pre>
<br />
On the first line we use the <em>findFirst</em> method of the Phalcon model class from which we extended our <em>Articles</em> model to retrieve the article with the ID of 1. Then in the second one, we pass to the View the value of the title column that we are retrieving with our getter function we declared in the model earlier. Save the file and reload the browser. You should see printed out the title of the first article.
<br /><br />
Since we cannot go into all what you can do with the Phalcon model class, I encourage you to read more about it <a href="http://docs.phalconphp.com/en/latest/reference/models.html" target="_blank">here</a>. You will find a bunch of ready functionality and database abstraction to get you going fast. 

<h3>Conclusion</h3><hr />

In this tutorial, we've seen how views are automatically loaded by Phalcon given their placement in the project folder structure and also how to pass information from the controller action to its respective view. Additionally, we've set up a small Phalcon model to see how we can interact with a database table and retrieve information. 


<div class="author">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div>
</div>
    