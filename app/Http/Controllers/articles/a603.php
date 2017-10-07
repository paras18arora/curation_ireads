<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="https://getcomposer.org">Composer</a> is a popular dependency management tool for PHP, created mainly to facilitate installation and updates for project dependencies. It will check which other packages a specific project depends on and install them for you, using the appropriate versions according to the project requirements.</p>

<p>This tutorial will show how to install and get started with Composer on an Ubuntu 14.04 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, you will need:</p>

<ul>
<li>A server running Ubuntu 14.04</li>
<li>Access to the server as a regular user with <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">sudo permission</a></li>
</ul>

<h2 id="step-1-—-installing-the-dependencies">Step 1 — Installing the Dependencies</h2>

<p>Before we download and install Composer, we need to make sure our server has all dependencies installed. </p>

<p>First, update the package manager cache by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Now, let's install the dependencies. We'll need <code>curl</code> in order to download Composer and <code>php5-cli</code> for installing and running it. <code>git</code> is used by Composer for downloading project dependencies. Everything can be installed with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install curl php5-cli git
</li></ul></code></pre>
<p>You can now proceed to the next step.</p>

<h2 id="step-2-—-downloading-and-installing-composer">Step 2 — Downloading and Installing Composer</h2>

<p>Composer installation is really simple and can be done with a single command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
</li></ul></code></pre>
<p>This will download and install Composer as a system-wide command named <code>composer</code>, under <code>/usr/local/bin</code>. The output should look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>#!/usr/bin/env php
All settings correct for using Composer
Downloading...

Composer successfully installed to: /usr/local/bin/composer
Use it: php /usr/local/bin/composer
</code></pre>
<p>To test your installation, run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">composer
</li></ul></code></pre>
<p>And you should get output similar to this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>   ______
  / ____/___  ____ ___  ____  ____  ________  _____
 / /   / __ \/ __ `__ \/ __ \/ __ \/ ___/ _ \/ ___/
/ /___/ /_/ / / / / / / /_/ / /_/ (__  )  __/ /
\____/\____/_/ /_/ /_/ .___/\____/____/\___/_/
                    /_/
Composer version 1.0-dev (9859859f1082d94e546aa75746867df127aa0d9e) 2015-08-17 14:57:00

Usage:
 command [options] [arguments]

Options:
 --help (-h)           Display this help message
 --quiet (-q)          Do not output any message
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version
 --ansi                Force ANSI output
 --no-ansi             Disable ANSI output
 --no-interaction (-n) Do not ask any interactive question
 --profile             Display timing and memory usage information
 --working-dir (-d)    If specified, use the given directory as working directory.

. . .
</code></pre>
<p>This means Composer was succesfully installed on your system.</p>

<p><span class="note">If you prefer to have separate Composer executables for each project you might host on this server, you can simply install it locally, on a per-project basis. This method is also useful when your system user doesn't have permission to install software system-wide. In this case, installation can be done with <code>curl -sS https://getcomposer.org/installer | php</code> - this will generate a <code>composer.phar</code> file in your current directory, which can be executed with <code>php composer.phar [command]</code>.<br /></span></p>

<h2 id="step-3-—-generating-the-composer-json-file">Step 3 — Generating the composer.json File</h2>

<p>In order to use Composer in your project, you'll need a <code>composer.json</code> file. The <code>composer.json</code> file basically tells Composer which dependencies it needs to download for your project, and which versions of each package are allowed to be installed. This is extremelly important to keep your project consistent and avoid installing unstable versions that could potentially cause backwards compatibility issues.</p>

<p>You don't need to create this file manually - it's easy to run into syntax errors when you do so. Composer auto-generates the <code>composer.json</code> file when you add a dependency to your project using the <code>require</code> command. Additional dependencies can also be added in the same way, without the need to manually edit this file.</p>

<p>The process of using Composer to install a package as dependency in a project usually involves the following steps:</p>

<ul>
<li>Identify what kind of library the application needs</li>
<li>Research a suitable open source library on <a href="https://packagist.org/">Packagist.org</a>, the official repository for Composer</li>
<li>Choose the package you want to depend on</li>
<li>Run <code>composer require</code> to include the dependency in the <code>composer.json</code> file and install the package</li>
</ul>

<p>We'll see how this works in practice with a simple demo application.</p>

<p>The goal of this application is to transform a given sentence into a URL-friendly string - a <em>slug</em>. This is commonly used to convert page titles to URL paths (like the final portion of the URL for this tutorial).</p>

<p>Let's start by creating a directory for our project. We'll call it <strong>slugify</strong>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~
</li><li class="line" prefix="$">mkdir slugify
</li><li class="line" prefix="$">cd slugify
</li></ul></code></pre>
<h3 id="searching-for-packages-on-packagist">Searching for Packages on Packagist</h3>

<p>Now it's time to search <a href="https://packagist.org/">Packagist.org</a> for a package that can help us generating <em>slugs</em>. If you search for the term "slug" on Packagist, you'll get a result similar to this:</p>

<p><img src="https://assets.digitalocean.com/articles/composer_1404/packagist.png" alt="Packagist Search: easy-slug/easy-slug, muffin/slug, ddd/slug, zelenin/slug, webcastle/slug, anomaly/slug-field_type" /></p>

<p>You'll see two numbers on the right side of each package in the list. The number on the top represents how many times the package was installed, and the number on the bottom shows how many times a package was starred on GitHub. You can reorder the search results based on these numbers (look for the two icons on the right side of the search bar). Generally speaking, packages with more installations and more stars tend to be more stable, since so many people are using them. It's also important to check the package description for relevance - is that really what you are looking for? </p>

<p>What we need is a simple string-to-slug converter. From the search results, the package <code>cocur/slugify</code> seems to be a good match, with a reasonable amount of installations and stars. (The package is a bit further down the page than the screenshot shows.)</p>

<p>You will notice that the packages on Packagist have a <strong>vendor</strong> name and a <strong>package</strong> name. Each package has a unique identifier (a namespace) in the same format Github uses for its repositories: <code>vendor/package</code>. The library we want to install uses the namespace <code>cocur/slugify</code> <strong>The namespace is what we need in order to require the package in our project.</strong></p>

<h3 id="requiring-a-package">Requiring a Package</h3>

<p>Now that we know exactly which package we want to install, we can run <code>composer require</code> to include it as a dependency and also generate the <code>composer.json</code> file for the project:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">composer require cocur/slugify
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Using version ^1.3 for cocur/slugify
./composer.json has been created
Loading composer repositories with package information
Updating dependencies (including require-dev)
  - Installing cocur/slugify (v1.3)
    Downloading: 100%         

Writing lock file
Generating autoload files
</code></pre>
<p>As you can see from the output, Composer automatically decided which version of the package should be used. If you check your project's directory now, it will contain two new files: <code>composer.json</code> and <code>composer.lock</code>, and a <code>vendor</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>total 12
-rw-rw-r-- 1 sammy sammy   59 Sep  9 16:22 composer.json
-rw-rw-r-- 1 sammy sammy 2835 Sep  9 16:22 composer.lock
drwxrwxr-x 4 sammy sammy 4096 Sep  9 16:22 vendor
</code></pre>
<p>The <code>composer.lock</code> file is used to store information about which versions of each package are installed, and make sure the same versions are used if someone else clones your project and installs its dependencies. The <code>vendor</code> directory is where the project dependencies are located. The <code>vendor</code> folder should <strong>not</strong> be committed into version control - you only need to include the <strong>composer.json</strong> and <strong>composer.lock</strong> files. </p>

<p><span class="note">When installing a project that already contains a <code>composer.json</code> file, you need to run <code>composer install</code> in order to download the project's dependencies. <br /></span></p>

<h3 id="understanding-version-constraints">Understanding Version Constraints</h3>

<p>If you check the contents of your <code>composer.json</code> file, you'll see something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat composer.json
</li></ul></code></pre><div class="code-label " title="composer.json">composer.json</div><pre class="code-pre line_numbers"><code class="code-highlight language-json"><ul class="prefixed"><li class="line" prefix="1">{
</li><li class="line" prefix="2">    "require": {
</li><li class="line" prefix="3">        "cocur/slugify": "^1.3"
</li><li class="line" prefix="4">    }
</li><li class="line" prefix="5">}
</li></ul></code></pre>
<p>You might notice the special character <code>^</code> before the version number on <code>composer.json</code>. Composer supports several different constraints and formats for defining the required package version, in order to provide flexibility while also keeping your project stable. The caret (<code>^</code>) operator used by the auto-generated <code>composer.json</code> file is the recommended operator for maximum interoperability, following <a href="http://semver.org/">semantic versioning</a>. In this case, it defines <strong>1.3</strong> as the minimum compatible version, and allows updates to any future version below <strong>2.0</strong>.</p>

<p>Generally speaking, you won't need to tamper with version constraints in your <code>composer.json</code> file. However, some situations might require that you manually edit the constraints - for instance, when a major new version of your required library is released and you want to upgrade, or when the library you want to use doesn't follow semantic versioning.</p>

<p>Here are some examples to give you a better understanding of how Composer version constraints work:</p>

<table class="pure-table"><thead>
<tr>
<th>Constraint</th>
<th>Meaning</th>
<th>Example Versions Allowed</th>
</tr>
</thead><tbody>
<tr>
<td>^1.0</td>
<td>>= 1.0 < 2.0</td>
<td>1.0, 1.2.3, 1.9.9</td>
</tr>
<tr>
<td>^1.1.0</td>
<td>>= 1.1.0 < 2.0</td>
<td>1.1.0, 1.5.6, 1.9.9</td>
</tr>
<tr>
<td>~1.0</td>
<td>>= 1.0 < 2.0.0</td>
<td>1.0, 1.4.1, 1.9.9</td>
</tr>
<tr>
<td>~1.0.0</td>
<td>>= 1.0.0 < 1.1</td>
<td>1.0.0, 1.0.4, 1.0.9</td>
</tr>
<tr>
<td>1.2.1</td>
<td>1.2.1</td>
<td>1.2.1</td>
</tr>
<tr>
<td>1.*</td>
<td>>= 1.0 < 2.0</td>
<td>1.0.0, 1.4.5, 1.9.9</td>
</tr>
<tr>
<td>1.2.*</td>
<td>>= 1.2 < 1.3</td>
<td>1.2.0, 1.2.3, 1.2.9</td>
</tr>
</tbody></table>

<p>For a more in-depth view of Composer version constraints, check their <a href="https://getcomposer.org/doc/articles/versions.md">official documentation</a>.</p>

<h2 id="step-4-—-including-the-autoload-script">Step 4 — Including the Autoload Script</h2>

<p>Composer also provides an autoload script that you can include in your project to get autoloading for free. This makes it much easier to work with your dependencies and define your own namespaces. </p>

<p>The only thing you need to do is include the <code>vendor/autoload.php</code> file in your PHP scripts, before any class instantiation. </p>

<p>Let's come back to the <em>slugify</em> example application. We'll create a <code>test.php</code> script where we'll use the <em>cocur/slugify</em> library: </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">vim test.php
</li></ul></code></pre><div class="code-label " title="test.php">test.php</div><pre class="code-pre line_numbers"><code class="code-highlight language-php"><ul class="prefixed"><li class="line" prefix="1"><?php
</li><li class="line" prefix="2"><span class="highlight">require __DIR__ . '/vendor/autoload.php';</span>
</li><li class="line" prefix="3">
</li><li class="line" prefix="4">use Cocur\Slugify\Slugify;
</li><li class="line" prefix="5">
</li><li class="line" prefix="6">$slugify = new Slugify();
</li><li class="line" prefix="7">
</li><li class="line" prefix="8">echo $slugify->slugify('Hello World, this is a long sentence and I need to make a slug from it!');
</li></ul></code></pre>
<p>You can run the script in the command line with:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">php test.php
</li></ul></code></pre>
<p>This should produce the output <code>hello-world-this-is-a-long-sentence-and-i-need-to-make-a-slug-from-it</code>.</p>

<h2 id="step-5-—-updating-the-project-dependencies">Step 5 — Updating the Project Dependencies</h2>

<p>Whenever you want to update your project dependencies, you just need to run the <code>update</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">composer update
</li></ul></code></pre>
<p>This will check for newer versions of the libraries you required in your project. If a newer version is found and it's compatible with the version constraint defined in the <code>composer.json</code> file, it will replace the previous version installed. The <code>composer.lock</code> file will be updated to reflect these changes.</p>

<p>You can also update one or more specific libraries by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">composer update <span class="highlight">vendor/package vendor2/package2</span>
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Composer is a powerful tool every PHP developer should have in their utility belt.</p>

<p>Beyond providing an easy and reliable way for managing project dependencies, it also establishes a new de facto standard for sharing and discovering PHP packages created by the community.</p>

<p>This tutorial covered the essentials for getting started with Composer on Ubuntu 14.04.</p>

    