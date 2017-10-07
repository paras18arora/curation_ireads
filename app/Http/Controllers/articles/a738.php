<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>We started out our series with talking about <a href="http://kohanaframework.org">Kohana</a> and its installation process. Since the framework does not require any additional configurations or modifications, we are ready to begin with learning web-application development basics using the Kohana framework.</p>

<p>In this IndiaReads article, we will be jumping in on understanding how Kohana is designed to work along with its most important parts. We will go over the HMVC (Hierarchical Model View Controller) pattern and learn how to create one of each - and get them to work together!</p>

<p><strong>Note:</strong> This is the second article in our Kohana series, focused on working with the framework. To see the first concerning installing it, check out <a href="https://digitalocean.com/community/articles/how-to-install-and-setup-kohana-a-php-web-application-development-framework">Getting Started with Kohana</a>. To see about deploying a Kohana based web application, check out <a href="https://indiareads/community/articles/how-to-deploy-kohana-php-applications-on-a-debian-7-ubuntu-13-vps-with-nginx-and-php-fpm">Deploying Kohana Based PHP Web-Applications</a>.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-convention-over-configuration">1. Convention over Configuration</h3>

<hr />

<h3 id="2-kohana-39-s-choices-and-its-conventions">2. Kohana's Choices and Its Conventions</h3>

<hr />
<pre class="code-pre "><code langs="">1. File Naming
2. Coding Style
</code></pre>
<h3 id="3-kohana-and-the-mvc-pattern">3. Kohana and The MVC Pattern</h3>

<hr />

<h3 id="4-controllers-quot-c-quot-of-the-mvc-pattern">4. Controllers - "C" of the MVC Pattern</h3>

<hr />
<pre class="code-pre "><code langs="">1. Conventions
2. How does it work?
</code></pre>
<h3 id="5-actions">5. Actions</h3>

<hr />
<pre class="code-pre "><code langs="">1. Conventions
2. How does it work?
</code></pre>
<h3 id="6-model-quot-m-quot-of-the-mvc-pattern">6. Model - "M" of the MVC Pattern</h3>

<hr />
<pre class="code-pre "><code langs="">1. Conventions
2. How does it work?
</code></pre>
<h3 id="7-view-quot-v-quot-of-the-mvc-pattern">7. View - "V" of the MVC Pattern</h3>

<hr />
<pre class="code-pre "><code langs="">1. Conventions
2. How does it work?
</code></pre>
<h3 id="8-routing">8. Routing</h3>

<hr />
<pre class="code-pre "><code langs="">1. Conventions
2. How does it work?
</code></pre>
<h3 id="9-handling-errors-with-kohana">9. Handling Errors with Kohana</h3>

<hr />
<pre class="code-pre "><code langs="">1. Registering HTTP Error Page Controllers
2. Throwing HTTP Errors
</code></pre>
<h3 id="10-sessions-and-cookies">10. Sessions and Cookies</h3>

<hr />
<pre class="code-pre "><code langs="">1. Sessions
2. Cookies
</code></pre>
<h2 id="convention-over-configuration">Convention over Configuration</h2>

<hr />

<p>In application programming, <strong>Convention-over-Configuration</strong> (or coding-by-convention) is a term used to describe a certain type of design (i.e. application structuring / modelling) whereby applications trust that code being built respects the rules and core instructions (i.e. connecting Models with Controllers automatically - identifying using names).</p>

<p>This application development paradigm is used to reduce all sorts of confusing, overly complicated and unnecessary options (and needs) for classic, file based configurations (e.g. config.xml). It is based on components [forming the application] following the already established conventions to have things work smoothly - hence eliminating the need for additional configuration.</p>

<p>Kohana's strict reliance on this concept makes it one of the easiest and simplest to work with frameworks. If you follow Kohana's conventions (including - and very importantly - the coding style), everything will be easier to create and to maintain.</p>

<h2 id="kohana-39-s-choices-and-its-conventions">Kohana's Choices and Its Conventions</h2>

<hr />

<h3 id="file-naming">File Naming</h3>

<hr />

<p>In order to facilitate PHP'S autoloading of required files (i.e. those created later), Kohana uses a strict style: first letter of class names are capitalised and underscores are used to separate each word forming it - as per <a href="https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md">PSR-0 Autoloading Standard</a>.</p>

<p>Example:</p>
<pre class="code-pre "><code langs="">#  Class Name             -     Class File Location
1. MyClass                      classes/MyClass.php
2. Controller_ClassOne          classes/MyWork/ClassOne.php
2. Controller_ClassTwo          classes/MyWork/ClassTwo.php
</code></pre>
<p><strong>Note:</strong> Remember that by the convention, all class defining files must exist under the <code>classes</code> directory.</p>

<h3 id="coding-style">Coding Style</h3>

<hr />

<p>Albeit not strictly necessary, for the above mentioned reasons, Kohana recommends sticking with the BSD/Allman style when it comes to writing your code.</p>

<p>This process consists of having curly-brackets on their own line.</p>

<p>Example:</p>
<pre class="code-pre "><code langs="">// for (int i=0; i<x; i++)
if (a == b)
{
    proc1();
}

finally();
</code></pre>
<p><strong>Note:</strong> Curly brackets following the class name are to be at the same level. </p>

<p>Example:</p>
<pre class="code-pre "><code langs="">class MyClass {
// ...
// ..
/  .
</code></pre>
<blockquote>
<p><strong>Remember:</strong> To learn more about Kohana's conventions, you may choose to refer to its documentation located <a href="http://kohanaframework.org/3.3/guide/kohana/conventions">here</a>.</p>
</blockquote>

<h2 id="kohana-and-the-mvc-pattern">Kohana and The MVC Pattern</h2>

<hr />

<p>This section and the following related ones (Controllers, Actions, Models, Views) form the first major part of application development fundamentals with Kohana - creating procedures (functions) to process requests. The sections after this cover other key areas (e.g. defining routes, handling errors etc). You are advised to try these examples on your droplet as much as you like to get comfortable before moving on with building a production-ready application.</p>

<p>As we have discussed at length, Kohana uses the (H)MVC pattern to process requests. Applications developed using Kohana are better to follow this style as thoroughly as possible to create smoothly operating programs.</p>

<h2 id="controller-quot-c-quot-of-the-mvc-pattern">Controller - "C" of the MVC Pattern</h2>

<hr />

<p>Controllers are plain text files which constitute one of the major parts of processing an incoming request. It glues the rest of the pieces that form the MVC pattern and makes them all work jointly to create and return a response. Each incoming request, after being routed, gets passed to a matching controller and gets processed by calling an action (e.g. print_names).</p>

<h3 id="conventions">Conventions</h3>

<hr />

<p>Kohana's conventions also apply to its controllers and therefore, each controller must:</p>

<ul>
<li><p>Exist under <code>classes/Controller/*</code>.</p></li>
<li><p>Have its name match the file name (i.e. <code>Controller_Name</code> inside <code>classes/Controller/</code> as <code>Name.php</code>).</p></li>
<li><p>Follow the rest of the naming and styling conventions.</p></li>
<li><p>Extend the parent <strong>Controller</strong> class.</p></li>
</ul>

<p><strong>Examples:</strong></p>
<pre class="code-pre "><code langs=""># Each one of the below examples represent the top -
# section (definition) of a class on a single [class] file. 

# Example [1]
// classes/Controller/ClassOne.php
class Controller_ClassOne extends Controller {

# Example [2]
// classes/Controller/Namegroup/ClassOne.php
class Controller_Namegroup_ClassOne extends Controller {

# Example [3]
// classes/Controller/ClassTwo.php
class Controller_ClassTwo extends Controller_ClassOne {
</code></pre>
<h3 id="how-does-it-work">How does it work?</h3>

<hr />

<p>Controllers work similarly to below:</p>

<ul>
<li><p><strong>Request</strong> - Controllers receive the request data wrapped as an object, attached to the [object] variable <code>$this->request</code>.</p></li>
<li><p><strong>With Models</strong> - Controllers pass information to models to modify the database and data objects and they request/receive data from models to process the data (possible passing through views).</p></li>
<li><p><strong>With Views</strong> - Controllers, after processing the information received with the request and data from models, pass this information by the view layer to return a final response along with its presentation (i.e. the view).</p></li>
<li><p><strong>Response</strong> - Controllers return a final response wrapped as an object [variable] defined by <code>$this->response</code> (e.g. final view body can be set via <code>$this->response->body($my_resp)</code>.)</p></li>
</ul>

<h2 id="actions">Actions</h2>

<hr />

<p>Actions are [public] procedures (i.e. functions) defined under classes. They consist of callables for requests to be processed.</p>

<h3 id="conventions">Conventions</h3>

<hr />

<p>By Kohana's conventions, <em>actions</em> must have:</p>

<ul>
<li><p><code>action_</code> prefix prepended to their names. (i.e. <code>action_print_names</code>).</p></li>
<li><p><em>public</em> classification (i.e. <code>public function action_print_names()</code>).</p></li>
<li><p><code>$this->response->body($view)</code> set at the end of their execution cycle to return a view to the user.</p></li>
</ul>

<p><strong>Note:</strong> There are two major exception to actions. These are:</p>

<ul>
<li><p><strong>before (<code>public function before()</code>)</strong> - Used to have code executed <em>before</em> everything.</p></li>
<li><p><strong>after (<code>public function after()</code>)</strong> - Used to have code executed <em>after</em> everything. </p></li>
</ul>

<h2 id="model-quot-m-quot-of-the-mvc-pattern">Model - "M" of the MVC Pattern</h2>

<hr />

<p>Models in Kohana are plain text files containing classes or other data forming / containing objects which represent the layer right above the database (or any datasource). The distinct nature of these kind of objects (i.e. those used for representing actual data) make <em>models</em> perfect pieces of the MVC paradigm by allowing the separation of all procedures related to directly creating, modifying, updating or deleting data.</p>

<h3 id="conventions">Conventions</h3>

<hr />

<p>By convention, classes defined under models - similarly to Controllers -  must:</p>

<ul>
<li><p>Exist <strong>under</strong> <code>classes/Models/*</code>.</p></li>
<li><p>Have its name match the file name (i.e. <code>Model_Name</code> inside <code>classes/Model/</code> as <code>Name.php</code>).</p></li>
<li><p>Follow the rest of the naming and styling conventions.</p></li>
<li><p>Extend the parent <strong>Model</strong> class.</p></li>
</ul>

<h3 id="how-does-it-work">How does it work?</h3>

<hr />

<p>Usually models use an Object Relational Mapper (ORM) solution to expose data and ways to interact with it to controller classes. Kohana comes with its own ORM module that allows very well structured objects to be designed and created.</p>

<p>Upon receiving a command (possibly with further variables), model perform desired actions to either send back a response to request of data, or, update the database with what is given. </p>

<h2 id="view-quot-v-quot-of-the-mvc-pattern">View - "V" of the MVC Pattern</h2>

<hr />

<p>View files form everything that is related to representation of final response. Of course, these files do not directly contain third party resources (e.g. images or other run-time dependencies); however, they form the base for what is to be provided to the end user. If you are designing a web-based API, views can be used to return desired data in a structured way that is easy to maintain. (e.g. json response to an ajax request).</p>

<p>When working with views, it is best to keep away all logical operations that would otherwise modify data-to-be-represented from view files. Views should be used (as much as possible) for forming the way the data is shown. </p>

<h3 id="conventions">Conventions</h3>

<hr />

<ul>
<li><p>View files must exist under <code>views/</code> directory (e.g. <code>views/login.php</code>)</p></li>
<li><p>They should be as "dumb" as possible.</p></li>
<li><p>They should <strong>not</strong> be used for anything other than using the data provided to form a representation.</p></li>
</ul>

<h3 id="how-does-it-work">How does it work?</h3>

<hr />

<p>In order to form a final view, the controller passes a certain payload (data) to the view file(s) which process their way through them (e.g. iterate over a list to form a table's columns). After compiling everything (template files with the payload / data processed), the view's representation gets transferred by the controller as the final response to the request.</p>

<h2 id="routing">Routing</h2>

<hr />

<p>As we have diagramed and discussed on our first article in Kohana series, each request is parsed (processed) and routed. The way this works is set out mainly by how routes are defined in your application. </p>

<p>These elements form <em>patterns</em> which are matched against (in order they are written) requests to decide which <em>action</em> to call with the request payload.</p>

<h3 id="conventions">Conventions</h3>

<hr />

<p>Unless you are strictly developing a module of its own, routes are usually defined under the <code>bootstrap.php</code> file - at the very end.</p>

<p>These routing mechanism definitions are extremely flexible and generous in the way they function - probably the most flexible in existence. You can use them to achieve great things very simply - just by following the correct schemas.</p>

<ul>
<li><p>All route names must be unique.</p></li>
<li><p>They must be defined before the default route.</p></li>
<li><p>Must not contain special token parameters (<code>(), <></code>).</p></li>
</ul>

<p><strong>Example:</strong></p>
<pre class="code-pre "><code langs=""># Route Syntax: set([name], [pattern])
# Pattern Syntax: (.. item ..)  <- optional elements,
                  <controller>  <- match a  controller,
                  <action>      <- match an action,
                  <id>          <- request variable

# Example [1]
Route::set('default', '(<controller>(/<action>(/<id>)))')
->defaults(array(
    'controller' => 'Welcome',
    'action'     => 'index',
)); 

# Route explained:
# By default, match all requests - optionally with:
#                                  /controller, and,
#                                - optionally with:
#                       /controller/action,     and,
#                /controller/action/id
# Use controller "Welcome" by default if none other matched,
# Use action "index" by default if one is not matched.

# Example [2]
Route::set('logops', '<action>',
array(
    'action' => '(login|logout)'
))
->defaults(array(
    'controller' => 'logops',
)); 

# Route explained:
# Match all incoming requests to /login and /logout with
# "logops" controller and call the related action (i.e. login or logout)
</code></pre>
<p><strong>Note:</strong> As soon as a route is matched, the procedure stops. Therefore, all the additional routes (i.e. all bar the default route) must be created before the default one.</p>

<h3 id="how-does-it-work">How does it work?</h3>

<hr />

<p>Request objects, upon being matched to a route, are transferred along with their data. This matching and request routing process consists of:</p>

<ul>
<li><p>Match a request to a route</p></li>
<li><p>Find the relevant classes under <code>classes/Controller</code> directory.</p></li>
<li><p>Find the controller.</p></li>
<li><p>Find and call controller's <em>action</em> callable.</p></li>
<li><p>Return the response (i.e. <em>the view</em>).</p></li>
</ul>

<h2 id="handling-errors-with-kohana">Handling Errors with Kohana</h2>

<hr />

<p>Together with unit testing, handling errors (in a sane way) is one of the most critical parts of almost any application. Kohana, using PHP's <strong>ErrorException</strong>, turns errors into exceptions and allows their handling with its helpers.</p>

<h3 id="registering-http-error-page-controllers">Registering HTTP Error Page Controllers</h3>

<hr />

<p>A robust web application will handle errors well and offer results (i.e. a response) back to its user correctly. For this purpose, Kohana offers an exceptional error handling system (no pun intended - see above).</p>

<p>To register an HTTP error page to throw exceptions:</p>
<pre class="code-pre "><code langs="">class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {

    public function get_response()
    {
        $response = Response::factory();
        $view     = View::factory('errors/404');

        $view->message = $this->getMessage();
        $response->body($view->render());

        return $response;
    }

}
</code></pre>
<h3 id="throwing-http-errors">Throwing HTTP Errors</h3>

<hr />

<p>Kohana has a very good and easy to operate error handling mechanism for throwing HTTP errors / exceptions. </p>

<p><strong>Example:</strong> </p>
<pre class="code-pre "><code langs=""># To create an error (exception) for 404 - Not Found Error
throw HTTP_Exception::factory(404, 'Not Found');
</code></pre>
<h2 id="sessions-and-cookies">Sessions and Cookies</h2>

<hr />

<p>To facilitate working with sessions and cookies, Kohana provides helper classes which allow you to work with each securely.</p>

<h3 id="sessions">Sessions</h3>

<hr />

<p>When working with sessions, you need to have a variable accessing the <em>session instance</em>.</p>
<pre class="code-pre "><code langs=""># Accesing the session instance
$session = Session::instance();
</code></pre>
<p>Through your access to the session instance, you can obtain all of them in an array with the following:</p>
<pre class="code-pre "><code langs=""># Accessing the session variables
$svars = $session->as_array();
</code></pre>
<p>To append a new value to the session variables:</p>
<pre class="code-pre "><code langs=""># Usage: $session_instance->set($session_key, $value);
$session->set('uid', 1234567890);
</code></pre>
<p>To get the value of one:</p>
<pre class="code-pre "><code langs=""># Usage: $session_instance->get($session_key, $optional_default_value);
$uid = $session->get('uid', 0);
</code></pre>
<p>And finally, to remove:</p>
<pre class="code-pre "><code langs=""># Usage: $session_instance->delete($session_key);
$session->delete('uid');
</code></pre>
<h3 id="cookies">Cookies</h3>

<hr />

<p>Kohana deals only with secure cookies. For this, a string, set-as and referred-as <code>Cookie::$salt</code> must be defined in the <code>bootstrap.php</code> file as the following:</p>
<pre class="code-pre "><code langs=""># Usage: Cookie::$salt = 'secure_cookie_key'; 
Cookie::$salt = '1234567890';
</code></pre>
<p>To set a cookie value, you can use the same way as you did with Sessions:</p>
<pre class="code-pre "><code langs=""># Usage: Cookie::set($cookie_key, $cookie_value);
Cookie::set('uid', 1234..);
</code></pre>
<p>To get a cookie's value:</p>
<pre class="code-pre "><code langs=""># Usage: $variable = Cookie::get($cookie_key, $optional_default_value);
$var = Cookie::get('uid', 0);
</code></pre>
<p>And finally, to delete:</p>
<pre class="code-pre "><code langs=""># Usage: Cookie::delete($cookie_key);
Cookie::delete('uid');
</code></pre>
<p><strong>Note:</strong> To learn about how to work with cookies using Kohana and more, check out <a href="http://kohanaframework.org/3.3/guide/kohana/">its official documentation</a> (latest edition).</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    