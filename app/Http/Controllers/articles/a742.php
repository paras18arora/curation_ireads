<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Amongst many PHP based frameworks, Kohana sets itself apart from the rest with its ideology of following common conventions and rules to develop fully object oriented web applications. This BSD licensed framework does not come with commercial strings attached and it has a <em>build-by-the-community, for-the-community</em> type of philosophy.</p>

<p>In this three-part IndiaReads series, we will be covering all the essentials a developer should be familiar with in order to start building web applications using the PHP5 Kohana framework. We will begin with going over some of the basics, continuing through the key concepts and modules after installing the framework.</p>

<p><strong>Note:</strong> This is the first article in our Kohana series - and it mainly consists of the basics and its installation. To continue with learning the framework to create web-applications, check out its sequel <a href="https://indiareads/community/tutorials/how-to-build-web-applications-with-hmvc-php5-framework-kohana">Building Web Applications with HMVC PHP5 Framework Kohana</a>. </p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-web-application-development-frameworks">1. Web Application Development Frameworks</h3>

<hr />
<pre class="code-pre "><code langs="">1. What Are Frameworks?
2. What Makes a Framework "Light"?
</code></pre>
<h3 id="2-kohana-framework">2. Kohana Framework</h3>

<hr />
<pre class="code-pre "><code langs="">1. Kohana's Features
2. Kohana's Standard (Out-of-The-Box) Modules
</code></pre>
<h3 id="3-model-view-controller-pattern">3. Model - View - Controller Pattern</h3>

<hr />
<pre class="code-pre "><code langs="">1. Routing Structure
2. Model
3. View
4. Controller
5. Template Files
</code></pre>
<h3 id="4-programming-with-kohana-and-preparations">4. Programming with Kohana and Preparations</h3>

<hr />

<h3 id="5-downloading-and-installing-the-kohana-framework">5. Downloading and Installing the Kohana Framework</h3>

<hr />

<h3 id="6-getting-started-with-kohana-installation">6. Getting Started with Kohana Installation</h3>

<hr />
<pre class="code-pre "><code langs="">1. Bootstrapping The Setup
2. Setting Application Directory Permissions
3. Finalizing Everything and Testing
</code></pre>
<h2 id="web-application-development-frameworks">Web Application Development Frameworks</h2>

<hr />

<p>Kohana is a web application development framework. Given PHP's nature as a language and the way the code written is executed on computers, there is no strict requirement to exploit frameworks to quickly develop applications when working with it. However, for any serious application that aims to have a long(-ish) lifecycle (making use of code already written once and with more than a single developer working on it), using a framework means making <em>a ton of things</em> much simpler.</p>

<h3 id="what-are-frameworks">What Are Frameworks?</h3>

<hr />

<p>Much like the dictionary definition of the word framework, web application frameworks provide both an essential structure to begin developing [applications] and a glue layer to hold everything together in a sound and logical way that makes sense for those who are familiar with the framework itself.</p>

<p>These bases come with many of the necessary common tools that are almost always needed to develop web applications such as processing incoming requests, generating and populating templates, returning responses, handling security and authentication, managing cookies (and sessions) <em>and more</em>.</p>

<h3 id="what-makes-a-framework-quot-light-quot">What Makes a Framework "Light"?</h3>

<hr />

<p>Depending on the amount of <strong>tools</strong> a framework is shipped with out of the box, it is either referred to as a lightweight or an all-in-one (full stack, batteries included, etc.) solution. Kohana, albeit being extremely powerful and functionally rich, can still be considered light because of the freedom it gives to developers working with it, and the way it has been designed and set to operate.</p>

<h2 id="kohana-framework">Kohana Framework</h2>

<hr />

<p>Kohana HMVC (Hierarchical Model View Controller) framework offers - probably - all the tools necessary to build a modern web application that can be developed rapidly and deployed/maintained easily using the PHP [5] language.</p>

<h3 id="kohana-39-s-features">Kohana's Features</h3>

<hr />

<p>Compared to other similar solutions, Kohana sets itself apart <em>not</em> with its features but with the way it presents these features and how it performs them.</p>

<ul>
<li><p>Kohana comes with many of the commonly required additional tools (modules)such as <strong>encryption</strong>, <strong>validation</strong>, <strong>database access</strong> etc.</p></li>
<li><p>It offers the possibility to simply expand the defaults.</p></li>
<li><p>Allows building commercial applications with its BSD licensing.</p></li>
<li><p>Getting started and setting up is extremely fast and easy compared to heavy and complicated frameworks.</p></li>
<li><p>All the modules and the way things function are designed and built using classes and object. The framework sustains the "Don't Repeat Yourself" principle.</p></li>
<li><p>Offers profiling and debugging tools.</p></li>
<li><p>Its code is very well documented and it comes with a relatively good documentation with examples and good explanations.</p></li>
<li><p>Prefers following <em>conventions</em> over [endless and frustrating] <em>configurations</em>.</p></li>
</ul>

<h3 id="kohana-39-s-standard-out-of-the-box-modules">Kohana's Standard (Out-of-The-Box) Modules</h3>

<hr />

<p>Below are some of the out-of-the-box modules of Kohana.</p>

<ul>
<li><p><strong>Auth:</strong> User authentication and authorization.</p></li>
<li><p><strong>Cache:</strong> Common interface for caching engines.</p></li>
<li><p><strong>Codebench:</strong> Code benchmarking tool.</p></li>
<li><p><strong>Database:</strong> Database agnostic querying and result management.</p></li>
<li><p><strong>Image:</strong> Image manipulation module.</p></li>
<li><p><strong>ORM (<em>Object Relational Mapper</em>):</strong> A modeling library for object relational mapping. </p></li>
<li><p><strong>Unittest:</strong> Unit testing module.</p></li>
</ul>

<h2 id="model-view-controller-pattern">Model - View - Controller Pattern</h2>

<hr />

<p>The MVC (Model - View - Controller) application pattern is used to divide code and logical structures into groups depending on their role <em>and</em> what they are set out to perform. Each of these parts process information within themselves and then share the necessary output between each other to complete jobs collectively, forming the final presentation (i.e. results) to the end user (i.e. the result of a URL visited).</p>

<h3 id="routing-structure">Routing Structure</h3>

<hr />

<p>Following the MVC pattern, a request goes through a process - similar to the example below - before a result gets returned.</p>
<pre class="code-pre "><code langs="">  (1)                       (2)                    (3)
Request       --->       Parsing       --->     Matching
[Data] .. [] >> .. [] > [] [] [] .. .. .>. .. . ........

  (4)                       (5)                    (6)
Routing       --->      Controller     --->     Response
 ----- .. >> .. >> ..  ../\ .. /\  []  >> [] >>  [Data] 
                         ||  . ||
                         \/  . \/
                       Model   View
</code></pre>
<h3 id="model">Model</h3>

<hr />

<p>In model, definition of object classes and handling the data operations exist. In this layer, there is no direct interaction with other parts of the application (e.g. views). When a new event takes place, <em>model</em> let's its parent (i.e. the controller) know.</p>

<h3 id="view">View</h3>

<hr />

<p>View layer consists of files where the <em>views</em> (e.g. data representations) are generated. The controller object, using the view, presents the final result to the user.</p>

<h3 id="controller">Controller</h3>

<hr />

<p>In controller, the parsed data from the <em>request</em> gets processed using the model and the view, generating the file <em>response</em> through <em>actions</em>. Controllers act like a glue, connecting all pieces to work together. </p>

<h3 id="template-files">Template Files</h3>

<hr />

<p>Template files form a base which are generally used to facilitate maintenance of the representation of certain data presented by the application to the end user. In terms of PHP applications, PHP language equally acts like a templating language hence providing the <em>templating syntax</em>.</p>

<h2 id="programming-with-kohana-and-preparations">Programming with Kohana and Preparations</h2>

<hr />

<p>Kohana, as a light framework, consists of a bunch of files scattered across carefully structured directories which, in the end, is transferred to the production server and used to run the web application. Therefore, each Kohana package can be considered a [new] web application.</p>

<p><strong>Note:</strong> In our examples, we will be working on a droplet, running the latest version of Ubuntu. To build web applications with Kohana, you can work on your home computer until the production step and later push your code for publication.</p>

<p><strong>Note:</strong> We are going to use a default LAMP (Linux - Apache - MySQL - PHP) set up in our droplet to work with Kohana. To quickly set up a LAMP stack on a Ubuntu droplet, you can use:</p>
<pre class="code-pre "><code langs="">sudo apt-get install tasksel
sudo tasksel install lamp-server
</code></pre>
<h2 id="downloading-and-installing-the-kohana-framework">Downloading and Installing the Kohana Framework</h2>

<hr />

<p>The latest available version of Kohana is <code>3.3.1</code>. In order to download it to our VPS, we will use <code>wget</code> (i.e. the GNU Wget command line tool).</p>
<pre class="code-pre "><code langs="">wget https://github.com/kohana/kohana/releases/download/v3.3.1/kohana-v3.3.1.zip
</code></pre>
<p>After the download, we need to expand the zipped package. For this we will be using the <strong>unzip</strong> command and set ""my_app as the extraction folder.</p>
<pre class="code-pre "><code langs=""># You might need to install *unzip* before extracting the files    
aptitude install -y unzip 

# Unzip and extract the files
unzip kohana-v3.3.1.zip -d my_app

# Remove the zip package
rm -v kohana-v3.3.1.zip
</code></pre>
<p>Once we are ready with the framework package, we can move it to a more permanent location to get it to work with Apache. The default location for our LAMP installation is <strong>/var/www/</strong></p>
<pre class="code-pre "><code langs=""># Remove the *index.html* inside /var/www
rm -v /var/www/index.html

# Move the application directory inside
mv my_app /var/www/

# Enter the directory
cd /var/www/my_app    
</code></pre>
<p>From now on, your installation will be accessible from the WWW.</p>
<pre class="code-pre "><code langs=""># Visit: http://[your droplet's IP adde.]/my_app/ 
http://95.85.44.185/my_app/
</code></pre>
<p><strong>Note:</strong> Kohana is not yet ready to work. Its configuration needs to be set first (i.e. bootstrapped).</p>

<h2 id="getting-started-with-kohana-installation">Getting Started with Kohana Installation</h2>

<hr />

<h3 id="bootstrapping-the-set-up">Bootstrapping The Set Up</h3>

<hr />

<p>Before we start going over the steps to learn about developing an application, let's bootstrap and finish off its installation procedure.</p>

<p>Run the following to edit the bootstrapping file using the nano text editor:</p>
<pre class="code-pre "><code langs="">nano application/bootstrap.php
</code></pre>
<p>Edit your timezone:</p>
<pre class="code-pre "><code langs=""># Find date_default_timezone_set and set your timezone
date_default_timezone_set('Europe/London');
</code></pre>
<p>Set your locale:</p>
<pre class="code-pre "><code langs=""># Find setlocale and set your locale
setlocale(LC_ALL, 'en_UK.utf-8');
</code></pre>
<p>Set the base application directory location:</p>
<pre class="code-pre "><code langs=""># Find base_url and set the base application directory
# Relative to the base Apache directory (i.e. /var/www/)

Kohana::init(array(
    'base_url' => '/my_app/',
));
</code></pre>
<p>Enable modules:</p>
<pre class="code-pre "><code langs=""># Find Kohana::modules and uncomment them

Kohana::modules(array(
    'auth'       => MODPATH.'auth',       // Basic authentication
    'cache'      => MODPATH.'cache',      // Caching with multiple backends
    'codebench'  => MODPATH.'codebench',  // Benchmarking tool
    'database'   => MODPATH.'database',   // Database access
    'image'      => MODPATH.'image',      // Image manipulation
    'orm'        => MODPATH.'orm',        // Object Relationship Mapping
    'oauth'      => MODPATH.'oauth',      // OAuth authentication
    'pagination' => MODPATH.'pagination', // Paging of results
    'unittest'   => MODPATH.'unittest',   // Unit testing
    'userguide'  => MODPATH.'userguide',  // User guide and API documentation
));
</code></pre>
<p>Save and exit by pressing CTRL+X and confirm with Y.</p>

<h3 id="setting-application-directory-permissions">Setting Application Directory Permissions</h3>

<hr />

<p>In order to <em>run</em> Kohana, we need to mark two of its folders <em>writable</em>.</p>
<pre class="code-pre "><code langs="">sudo chmod -R a+rwx application/cache
sudo chmod -R a+rwx application/logs
</code></pre>
<h3 id="finalizing-everything-and-testing">Finalizing Everything and Testing</h3>

<hr />

<p>Once we are done with bootstrapping the set up and configuring folder permissions, we can test it all again by visiting the application using a web browser</p>
<pre class="code-pre "><code langs=""># Visit: http://[your droplet's IP adde.]/my_app/ 
http://95.85.44.185/my_app/
</code></pre>
<p>When you confirm that everything is set correctly and working fine, you can remove the <code>install.php</code>.</p>

<p>Run the following to remove the install file:</p>
<pre class="code-pre "><code langs="">rm -v install.php
</code></pre>
<p>If you re-visit the URL from the previous step, you will be welcomed with a <strong>hello, world!</strong> message. This means that our requests are now routed through the HMVC process following the pattern correctly. </p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    