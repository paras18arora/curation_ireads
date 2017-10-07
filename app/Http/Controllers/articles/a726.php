<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h2 id="introduction">Introduction</h2>

<p>Silex is a PHP micro-framework built on Symfony2 components. It can be used to build small websites and large applications alike. It is concise, extensible, and testable.</p>

<p>In this tutorial, we will begin by downloading and configuring Silex. Then you will learn how to make a basic Silex application.</p>

<p>We will be using Composer to install Silex, which is a popular PHP package manager. More information about Composer can be found in <a href="https://indiareads/community/tutorials/how-to-install-and-use-composer-on-your-vps-running-ubuntu">this tutorial</a>. At the end of this tutorial, you will have a fully functional blog site.</p>

<blockquote>
<p><strong>Note:</strong> This tutorial was tested on Ubuntu, but should work equally well on other Linux distributions. The links refer to Ubuntu tutorials, but please feel free to find the appropriate guides for setting up your server and installing the LAMP stack and Git.</p>
</blockquote>

<h3 id="prerequisites">Prerequisites</h3>

<p>Please complete these prerequisites.</p>

<ul>
<li>Working Ubuntu 14.04 server with SSH access. For more information, visit <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">this tutorial</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo user</a></li>
<li><a href="https://indiareads/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-14-04">LAMP stack</a></li>
<li><a href="https://indiareads/community/articles/how-to-install-git-on-ubuntu-14-04">Git</a></li>
</ul>

<h2 id="step-1-—-installing-silex">Step 1 — Installing Silex</h2>

<p>In this section, we will install Silex using Composer. To begin with, change your working directory to the Apache document root, <code>/var/www/html</code>: </p>
<pre class="code-pre "><code langs="">cd /var/www/html
</code></pre>
<p>Next, delete the default contents of this folder:</p>
<pre class="code-pre "><code langs="">sudo rm /var/www/html/index.html
</code></pre>
<p>Then, move to the <code>/var/www</code> directory so as not to expose all of your files to the public:</p>
<pre class="code-pre "><code langs="">cd /var/www
</code></pre>
<p>Then, download Composer:</p>
<pre class="code-pre "><code langs="">sudo curl -sS https://getcomposer.org/installer | sudo php
</code></pre>
<p>Next, we will create and edit the Composer file <code>composer.json</code>:</p>
<pre class="code-pre "><code langs="">sudo nano composer.json
</code></pre>
<p>In this file, add the following contents:</p>
<pre class="code-pre "><code langs="">{
    "require": {
        "silex/silex": "~1.2"
    }
}
</code></pre>
<p>We have now told Composer to download Silex version 1.2 as a dependency. To start the download, execute the following command:</p>
<pre class="code-pre "><code langs="">sudo php composer.phar update
</code></pre>
<p>Now, Composer will download Silex and its dependencies; this might take a few seconds. </p>

<h2 id="step-2-—-bootstrapping-silex">Step 2 — Bootstrapping Silex</h2>

<p>In this section, we will bootstrap Silex by including the required files and creating the application. To start, edit the file <code>/var/www/html/index.php</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/index.php
</code></pre>
<p>In this file, add the following basic contents:</p>
<pre class="code-pre "><code langs=""><?php
require_once __DIR__.'/../vendor/autoload.php'; // Add the autoloading mechanism of Composer

$app = new Silex\Application(); // Create the Silex application, in which all configuration is going to go

// Section A
// We will later add the configuration, etc. here


// This should be the last line
$app->run(); // Start the application, i.e. handle the request
?>
</code></pre>
<p>Throughout this tutorial, we'll be adding more configuration information and other data to this file. All of the new lines that we add will go in <code>Section A</code>, between the <code>$app = new Silex\Application();</code> and <code>$app->run();</code> lines.</p>

<p>In the same file, <code>/var/www/html/index.php</code> turn on debugging, which is useful when developing your application. Add this line in <strong>Section A</strong>:</p>

<p><code><br />
$app['debug'] = true;<br />
</code></p>

<h2 id="step-3-creating-a-blog-application">Step 3 - Creating a Blog Application</h2>

<p>In this section, we will create a sample blog application. If you want to focus on your own application instead, please take a look at the <a href="http://silex.sensiolabs.org/doc/usage.html#routing">Silex documentation</a>.</p>

<p>We will create an example blog application. It will not make use of a database, but it can be converted relatively easily by taking a look at the <a href="http://silex.sensiolabs.org/doc/providers/doctrine.html">DoctrineServiceProvider documentation</a>.</p>

<h3 id="adding-the-twig-template-engine">Adding the Twig Template Engine</h3>

<p>First, start by adding another dependency: <a href="http://twig.sensiolabs.org/">Twig</a>. Twig is a template engine also used by the Symfony framework. It will serve the templates of our application. To add it, edit <code>composer.json</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/composer.json
</code></pre>
<p>Then, add the new dependency line for <code>twig</code>, shown in red below. Don't forget the comma on the previous line:</p>
<pre class="code-pre "><code langs="">{
    "require": {
        "silex/silex": "~1.2"<span class="highlight">,</span>
        <span class="highlight">"twig/twig": ">=1.8,<2.0-dev"</span>
    }
}
</code></pre>
<p>Next, update the Composer dependencies:</p>
<pre class="code-pre "><code langs="">sudo php composer.phar update
</code></pre>
<h3 id="enabling-mod_rewrite">Enabling mod_rewrite</h3>

<p>Now, you will need to configure the web server, which is Apache in this case.</p>

<p>First, make sure you have enabled <code>mod_rewrite</code> and that you have permitted changes in the <code>.htaccess</code> file. The process is described in <a href="https://indiareads/community/tutorials/how-to-set-up-mod_rewrite">this tutorial</a>, but remember that Ubuntu 14.04's default virtual host is in <code>/var/www/html</code> rather than <code>/var/www</code>.</p>

<p>After you've enabled the module (as explained in the linked tutorial), add the following lines to your <code>/etc/apache2/sites-available/000-default.conf</code> file:</p>
<pre class="code-pre "><code langs="">sudo vim /etc/apache2/sites-available/000-default.conf
</code></pre><pre class="code-pre "><code langs=""><Directory /var/www/html/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
</Directory>
</code></pre>
<p>Then, create and edit the <code>.htaccess</code> file:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/.htaccess
</code></pre>
<p>In this file, add the following contents:</p>
<pre class="code-pre "><code langs=""><IfModule mod_rewrite.c>
    Options -MultiViews

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]
</IfModule>
</code></pre>
<p>This will make sure that any request for a file which does not exist points to our application, which allows the application to do the routing.</p>

<h3 id="creating-blog-content">Creating Blog Content</h3>

<p>To add some articles, we will create an array containing the title, contents, author and date of publication. We can store this in our application object by means of the container object that it extends. A container object is able to hold multiple objects, which can be reused by all other objects in the application. To do this, add the following in <code>Section A</code> in <code>/var/www/html/index.php</code></p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/index.php
</code></pre>
<p>Add the following contents:</p>
<pre class="code-pre "><code langs="">$app['articles'] = array(
    array(
        'title'    => 'Lorem ipsum dolor sit amet',
        'contents' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean mollis vestibulum ultricies. Sed sit amet sagittis nisl. Nulla leo metus, efficitur non risus ut, tempus convallis sem. Mauris pharetra sagittis ligula pharetra accumsan. Cras auctor porta enim, a eleifend enim volutpat vel. Nam volutpat maximus luctus. Phasellus interdum elementum nulla, nec mollis justo imperdiet ac. Duis arcu dolor, ultrices eu libero a, luctus sollicitudin diam. Phasellus finibus dictum turpis, nec tincidunt lacus ullamcorper et. Praesent laoreet odio lacus, nec lobortis est ultrices in. Etiam facilisis elementum lorem ut blandit. Nunc faucibus rutrum nulla quis convallis. Fusce molestie odio eu mauris molestie, a tempus lorem volutpat. Sed eu lacus eu velit tincidunt sodales nec et felis. Nullam velit ex, pharetra non lorem in, fringilla tristique dolor. Mauris vel erat nibh.',
        'author'   => 'Sammy',
        'date'     => '2014-12-18',
    ),
    array(
        'title'    => 'Duis ornare',
        'contents' => 'Duis ornare, odio sit amet euismod vulputate, purus dui fringilla neque, quis eleifend purus felis ut odio. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque bibendum pretium ante, eu aliquet dolor feugiat et. Pellentesque laoreet est lectus, vitae vulputate libero sollicitudin consequat. Vivamus finibus interdum egestas. Nam sagittis vulputate lacus, non condimentum sapien lobortis a. Sed ligula ante, ultrices ut ullamcorper nec, facilisis ac mi. Nam in vehicula justo. In hac habitasse platea dictumst. Duis accumsan pellentesque turpis, nec eleifend ex suscipit commodo.',
        'author'   => 'Sammy',
        'date'     => '2014-11-08',
    ),
);
</code></pre>
<p>These articles can be reused everywhere in our application now, and you can even add more yourself. For real-world websites, it would probably be a better idea to use a database.</p>

<h3 id="routing">Routing</h3>

<p>Basically, routing maps a URL like <code>http://www.example.com/</code> to <span class="highlight">/</span> and executes the function associated with it. To add a basic route, add the following in <code>Section A</code> of <code>/var/www/html/index.php</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/index.php
</code></pre>
<p>Contents:</p>
<pre class="code-pre "><code langs="">$app->get('/', function (Silex\Application $app)  { // Match the root route (/) and supply the application as argument
    $output = '';
    foreach ($app['articles'] as $article) { // Create a basic list of article titles
        $output .= $article['title'];
        $output .= '<br />';
    }

    return $output; // Return it to so it gets displayed by the browser
});
</code></pre>
<p>Now, when you visit <code>http://<span class="highlight">your_server_ip</span></code>, it should display a list of article titles:</p>

<p><img src="https://assets.digitalocean.com/articles/silex_ubuntu/1.png" alt="The browser view of the page created above, showing the two article titles" /></p>

<h3 id="templates">Templates</h3>

<p>Even though our website now displays the correct output, it doesn't look very nice. To fix this, we will make use of Twig.</p>

<p>First, Silex requires us to register Twig as a <em>service provider</em>, which is basically a way to reuse certain parts of an application in another application. To register Twig, add this in <code>section A</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/index.php
</code></pre>
<p>Contents:</p>
<pre class="code-pre "><code langs="">$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates', // The path to the templates, which is in our case points to /var/www/templates
));
</code></pre>
<p>We can now make use of the Twig template engine. To do this, edit the <code>$app->get('/', function (Silex\Application $app)  { });</code> block, which defines the route of <code>/</code>, to match what's shown here. New lines are shown in <span class="highlight">red</span>:</p>
<pre class="code-pre "><code langs="">$app->get('/', function (Silex\Application $app)  { // Match the root route (/) and supply the application as argument
    <span class="highlight">return $app['twig']->render( // Render the page index.html.twig</span>
        <span class="highlight">'index.html.twig',</span>
        <span class="highlight">array(</span>
            <span class="highlight">'articles' => $app['articles'], // Supply arguments to be used in the template</span>
        <span class="highlight">)</span>
    <span class="highlight">);</span>
});
</code></pre>
<p>Save your changes and close the file.</p>

<p>Now let's create the <code>index.html.twig</code> template. Create the directory and then create and open the file <code>base.html.twig</code>:</p>
<pre class="code-pre "><code langs="">sudo mkdir /var/www/templates
sudo nano /var/www/templates/base.html.twig
</code></pre>
<p>This file will be our base template, which means all other templates will inherit from it, so we don't have to add the basics in every template. In this file, add the following contents:</p>
<pre class="code-pre "><code langs=""><!doctype html>
<html>
<head>
    <title>{% block title %}Blog{% endblock %}</title>
</head>
<body>
{% block body %}

{% endblock body %}
</body>
</html>
</code></pre>
<p>This file contains two <em>blocks</em>. Blocks can be overridden in subtemplates to provide content. The block called <code>title</code> will be used to provide a title for the single article page. The block <code>body</code> will be used to display all contents.</p>

<p>Save your changes.</p>

<p>Now we'll create and edit the file <code>/var/www/templates/index.html.twig</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/templates/index.html.twig
</code></pre>
<p>Add the following contents:</p>
<pre class="code-pre "><code langs="">{% extends 'base.html.twig' %}
{% block body %}
    <h1>
        Blog index
    </h1>

    {% for article in articles %}
        <article>
            <h1>{{ article.title }}</h1>
            <p>{{ article.contents }}</p>
            <p><small>On {{ article.date }} by {{ article.author }}</small></p>
        </article>
    {% endfor %}
{% endblock %}
</code></pre>
<p>First, we specify that we want to extend the template <code>base.html.twig</code>. After that, we can begin overriding blocks defined in the parent template. In this template, we only override the block <code>body</code>, where we create a loop which displays all articles.</p>

<p>Now visit <code>http://<span class="highlight">your_server_ip</span></code>; it should show an index of all your posts:</p>

<p><img src="https://assets.digitalocean.com/articles/silex_ubuntu/2.png" alt="Browser view of the new index page, listing all posts" /></p>

<h3 id="another-controller-for-a-single-post">Another Controller for a Single Post</h3>

<p>Next, we will add another controller which displays a single post. The posts will be matched by their array index. Open <code>/var/www/html/index.php</code> again:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/index.php
</code></pre>
<p>Add this to <code>Section A</code>, which will allow us to display pages for individual articles:</p>
<pre class="code-pre "><code langs="">$app->get('/{id}', function (Silex\Application $app, $id)  { // Add a parameter for an ID in the route, and it will be supplied as argument in the function
    if (!array_key_exists($id, $app['articles'])) {
        $app->abort(404, 'The article could not be found');
    }
    $article = $app['articles'][$id];
    return $app['twig']->render(
        'single.html.twig',
        array(
            'article' => $article,
        )
    );
})
    ->assert('id', '\d+') // specify that the ID should be an integer
    ->bind('single'); // name the route so it can be referred to later in the section 'Generating routes'
</code></pre>
<p>Save your changes. Then, create and edit the file <code>/var/www/templates/single.html.twig</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/templates/single.html.twig
</code></pre>
<p>Add the following contents:</p>
<pre class="code-pre "><code langs="">{% extends 'base.html.twig' %}
{% block title %}{{ article.title }}{% endblock %}
{% block body %}
    <h1>
        {{ article.title }}
    </h1>
    <p>{{ article.contents }}</p>
    <p><small>On {{ article.date }} by {{ article.author }}</small></p>
{% endblock %}
</code></pre>
<p>In this template, we also make use of the <code>title</code> block to display the article title. The <code>body</code> block looks almost identical to the former <code>body</code> block, so it should be pretty self-explanatory.</p>

<p>If you now visit <code>http://<span class="highlight">your_server_ip</span>/0</code> or  <code>http://<span class="highlight">your_server_ip</span>/1</code>, it should show an article:</p>

<p><img src="https://assets.digitalocean.com/articles/silex_ubuntu/3.png" alt="The browser view of a single article" /></p>

<p>If, however, you visit a non-existing ID, such as <code>http://<span class="highlight">your_server_ip</span>/2</code> in this example, it will show an error page:</p>

<p><img src="https://assets.digitalocean.com/articles/silex_ubuntu/4.png" alt="The browser view of a non-existing article error, HttpException" /></p>

<h3 id="generating-routes">Generating Routes</h3>

<p>Next, we will add links from the home page to the single article view, and back from the articles to the home page. Silex has the ability to generate routes using a Symfony component. It is provided as a <em>service provider</em>, so you should add that first to <code>Section A</code>. Open <code>/var/www/html/index.php</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/html/index.php
</code></pre>
<p>Add the following to <code>Section A</code>:</p>
<pre class="code-pre "><code langs="">$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
</code></pre>
<p>This will allow us to make use of the URL generator service. When we created the single view controller, we already added a named route. It was done using the following line:</p>
<pre class="code-pre "><code langs="">->bind('single'); // name the route so it can be referred to later in the section 'Generating routes'
</code></pre>
<p>Now, we will also need to bind a route to the home page. To do that, add the <code>->bind('index')</code> route at the end of this block, just before the final semicolon. Changes are marked in <span class="highlight">red</span>:</p>
<pre class="code-pre "><code langs="">$app->get('/', function (Silex\Application $app)  { // Match the root route (/) and supply the application as argument
    return $app['twig']->render(
        'index.html.twig',
        array(
            'articles' => $app['articles'],
        )
    );
})<span class="highlight">->bind('index')</span>;
</code></pre>
<p>Next, we will need to actually generate the URLs. Open <code>/var/www/templates/index.html.twig</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/templates/index.html.twig
</code></pre>
<p>Then, change the following <code><h1></code> line, as shown below:</p>
<pre class="code-pre "><code langs="">{% extends 'base.html.twig' %}
{% block body %}
    <h1>
        Blog index
    </h1>

    {% for article in articles %}
        <article>
            <h1><span class="highlight"><a href="{{ app.url_generator.generate('single', { id: loop.index0 }) }}">{{ article.title }}</a></span></h1>
            <p>{{ article.contents }}</p>
            <p><small>On {{ article.date }} by {{ article.author }}</small></p>
        </article>
    {% endfor %}
{% endblock %}
</code></pre>
<p>This creates a link from the title of the article to the individual article page. The <code>app.url_generator</code> refers to the service that we have registered. The <code>generate</code> function takes two parameters: the route name, <code>single</code> in this case, and parameters for the route, which is just the ID in this case. <code>loop.index0</code> refers to a 0-indexed index in the loop. Thus, when the first item is looped, it is a <code>0</code>; when the second item is looped, it is a <code>1</code>, etc.</p>

<p>The same can be done to refer back to the index page in the single page template:</p>
<pre class="code-pre "><code langs="">sudo nano /var/www/templates/single.html.twig
</code></pre>
<p>Add the following <code><p></code> line to create the link:</p>
<pre class="code-pre "><code langs="">{% extends 'base.html.twig' %}
{% block title %}{{ article.title }}{% endblock %}
{% block body %}
    <h1>
        {{ article.title }}
    </h1>
    <p>{{ article.contents }}</p>
    <p><small>On {{ article.date }} by {{ article.author }}</small></p>

    <span class="highlight"><p><a href="{{ app.url_generator.generate('index') }}">Back to homepage</a></p></span>
{% endblock %}
</code></pre>
<p>This should be pretty self-explanatory.</p>

<p>That's it! Feel free to visit the website again at <code>http://<span class="highlight">your_server_ip</span>/</code>. You should be able to click an article title to visit that article's page, and then use the link at the bottom of the article to return to the home page.</p>

<h3 id="complete-index-php-file">Complete index.php File</h3>

<p>For your reference, here's what the final <code>/var/www/html/index.php</code> file should look like.</p>
<pre class="code-pre "><code langs=""><?php
require_once __DIR__.'/../vendor/autoload.php'; // Add the autoloading mechanism of Composer

$app = new Silex\Application(); // Create the Silex application, in which all configuration is going to go



// Section A
// We will later add the configuration, etc. here

$app['debug'] = true;
$app['articles'] = array(
    array(
        'title'    => 'Lorem ipsum dolor sit amet',
        'contents' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean mollis vestibulum ultricies. Sed sit amet sagittis nisl. Nulla leo metus, efficitur non risus ut, tempus convallis sem. Mauris pharetra sagittis ligula pharetra accumsan. Cras auctor porta enim, a eleifend enim volutpat vel. Nam volutpat maximus luctus. Phasellus interdum elementum nulla, nec mollis justo imperdiet ac. Duis arcu dolor, ultrices eu libero a, luctus sollicitudin diam. Phasellus finibus dictum turpis, nec tincidunt lacus ullamcorper et. Praesent laoreet odio lacus, nec lobortis est ultrices in. Etiam facilisis elementum lorem ut blandit. Nunc faucibus rutrum nulla quis convallis. Fusce molestie odio eu mauris molestie, a tempus lorem volutpat. Sed eu lacus eu velit tincidunt sodales nec et felis. Nullam velit ex, pharetra non lorem in, fringilla tristique dolor. Mauris vel erat nibh.',
        'author'   => 'Sammy',
        'date'     => '2014-12-18',
    ),
    array(
        'title'    => 'Duis ornare',
        'contents' => 'Duis ornare, odio sit amet euismod vulputate, purus dui fringilla neque, quis eleifend purus felis ut odio. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque bibendum pretium ante, eu aliquet dolor feugiat et. Pellentesque laoreet est lectus, vitae vulputate libero sollicitudin consequat. Vivamus finibus interdum egestas. Nam sagittis vulputate lacus, non condimentum sapien lobortis a. Sed ligula ante, ultrices ut ullamcorper nec, facilisis ac mi. Nam in vehicula justo. In hac habitasse platea dictumst. Duis accumsan pellentesque turpis, nec eleifend ex suscipit commodo.',
        'author'   => 'Sammy',
        'date'     => '2014-11-08',
    ),
);

$app->get('/', function (Silex\Application $app)  { // Match the root route (/) and supply the application as argument
    return $app['twig']->render( // Render the page index.html.twig
        'index.html.twig',
        array(
            'articles' => $app['articles'], // Supply arguments to be used in the template
        )
    );
})->bind('index');

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates', // The path to the templates, which is in our case points to /var/www/templates
));

$app->get('/{id}', function (Silex\Application $app, $id)  { // Add a parameter for an ID in the route, and it will be supplied as argument in the function
    if (!array_key_exists($id, $app['articles'])) {
        $app->abort(404, 'The article could not be found');
    }
    $article = $app['articles'][$id];
    return $app['twig']->render(
        'single.html.twig',
        array(
            'article' => $article,
        )
    );
})
    ->assert('id', '\d+') // specify that the ID should be an integer
    ->bind('single'); // name the route so it can be referred to later in the section 'Generating routes'

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());




// This should be the last line
$app->run(); // Start the application, i.e. handle the request
?>
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>You have created a simple blog application using Silex. It can be expanded a lot further, starting with coupling it with a database. That is out of the scope of this tutorial, though. <a href="http://silex.sensiolabs.org/documentation">The official documentation</a> can be very helpful and is definitely a must-read if you want to continue using Silex.</p>

<p>If Silex is too micro for you, you should definitely consider using the Symfony framework, for which a tutorial can be found <a href="https://indiareads/community/tutorials/how-to-install-and-get-started-with-symfony-2-on-an-ubuntu-vps">here</a>.</p>

<h3 id="links">Links</h3>

<ul>
<li><a href="https://github.com/koesie10/IndiaReads_Silex">Final Blog Application on GitHub</a></li>
<li><a href="http://silex.sensiolabs.org">Silex Homepage</a></li>
</ul>

    