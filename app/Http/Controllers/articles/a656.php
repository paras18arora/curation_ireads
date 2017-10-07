<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/02192014Rubygems_twitter.png?1426699651/> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>The RubyGems is a tool which manages Ruby application packages' lifecycle from creation to distribution. If you have ever worked with a Ruby based application, chances are you have used RubyGems to manage dependencies, libraries, or frameworks (e.g. Ruby-on-Rails).</p>

<p>In this IndiaReads article, we will learn all the important bits and pieces of RubyGems, from the most basic to more advanced features. If you are planning to work with (or use) Ruby in the long run, mastering this tool can translate to a great deal of efficiency for your work.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-package-managers-and-the-rubygems">1. Package Managers And The RubyGems</h3>

<hr />
<pre class="code-pre "><code langs="">1. What Is A Package Manager?
2. Application Packages
3. What Is RubyGems?
4. What Is A Gem?
</code></pre>
<h3 id="2-rubygems-gem-package-format">2. RubyGems Gem Package Format</h3>

<hr />
<pre class="code-pre "><code langs="">1. Gem Information And Format
2. The `*.gemspec` File
</code></pre>
<h3 id="3-getting-started-with-rubygems">3. Getting Started With RubyGems</h3>

<hr />
<pre class="code-pre "><code langs="">1. Installing Ruby And RubyGems
2. Usage
</code></pre>
<h3 id="4-main-rubygem-operations-to-work-with-gems">4. Main RubyGem Operations To Work With Gems</h3>

<hr />
<pre class="code-pre "><code langs="">1. Finding Installed And Available Gems
2. Searching RubyGems.org For Gems
3. Installing New Gems
4. Finding Outdated Gems
5. Updating Gems
6. Removing / Deleting Gems
7. Reading The Gem Documentation
</code></pre>
<h2 id="package-managers-and-the-rubygems">Package Managers And The RubyGems</h2>

<hr />

<h3 id="what-is-a-package-manager">What Is A Package Manager?</h3>

<hr />

<p>In terms of computers, almost everything consists of connections and collections between different programs. The moment you start the machine, a bunch of code gets executed, which in turn loads some others. A pyramid gets built, forming the final platform, allowing you to run the higher-level applications you require (e.g. Ruby!).</p>

<p>As you can see from the above basic portrayal of application execution lifecycle, everything is dependant on others when it comes to programs. And this cycle starts during the development phase.</p>

<p>In order to facilitate the download and installation procedure of libraries that programs depend on, especially nowadays, a set of tools referred to as <em>package managers</em> are heavily used. These tools make it very easy to find, install, and keep track of all other libraries that, as a developer, your programs need.</p>

<p>Application packages which are distributed via these tools are generally simple archives containing programs and metadata. Different package managers exist to manage different programming languages' dependencies – and they each name their packages differently.</p>

<h3 id="application-packages">Application Packages</h3>

<hr />

<p>Simply put, application packages contain already compiled and ready-to-use software or libraries which others use. They can (and usually do) come with additional files to give information about the package, and despite the importance, only sometimes with a decent usage manual. Once the package manager installs a package, all these elements become accessible inside the environment they are set (e.g. an RVM Gemset).</p>

<h3 id="what-is-rubygems">What Is RubyGems?</h3>

<hr />

<p>In the case of Ruby, the default <em>package manager</em> is called the RubyGems. This program has been distributed with the default Ruby interpreter since version <code>1.9</code> and helps you with many things from downloading to packaging and distributing Ruby applications -- and of course, relevant binaries and libraries. RubyGems is very rich, and probably one of the most mature package management applications that exists. It provides the developers a standard structure, along with a standard format to deal with application collections (packages) called Gems.</p>

<h3 id="what-is-a-gem">What Is A Gem?</h3>

<hr />

<p>A <em>Gem</em> is an application package very similar in structure to the generic description we have just given. It can be a collection of code, libraries, list of dependencies and some additional meta-data, defining the package that gets distributed with the RubyGems tool. </p>

<p>The simplest way to start working with programs is to use these bundles when developing Ruby based (or Ruby related) applications. In this tutorial, we are going to learn how to use the RubyGems to work with and handle Gem based packages.</p>

<h2 id="rubygems-gem-package-format">RubyGems Gem Package Format</h2>

<hr />

<h3 id="gem-information-and-format">Gem Information And Format</h3>

<hr />

<p>As we have mentioned previously, a Gem is a package that contains different sets of components. Each Gem has a version and a basic definition of the platform it was built for.</p>

<p>The Gem directories can contain the following:</p>

<ul>
<li><p>Application code;</p></li>
<li><p>Tests;</p></li>
<li><p>Description of dependencies;</p></li>
<li><p>Binaries;</p></li>
<li><p>Relevant Documentation; </p></li>
<li><p>Information regarding the package (e.g. gemspec).</p></li>
</ul>

<p>And they have a basic structure similar to the following:</p>
<pre class="code-pre "><code langs="">/[package_name]               # 1
        |__ /bin              # 2
        |__ /lib              # 3
        |__ /test             # 4
        |__ README            # 5
        |__ Rakefile          # 6
        |__ [name].gemspec    # 7
</code></pre>
<ol>
<li><p><strong>[package_name]:</strong><br /><br />
The main root directory of the Gem package.</p></li>
<li><p><strong>/bin:</strong><br /><br />
Location of the executable binaries if the package has any.</p></li>
<li><p><strong>/lib:</strong><br /><br />
Directory containing the main Ruby application code (inc. modules).</p></li>
<li><p><strong>/test:</strong><br /><br />
Location of test files.</p></li>
<li><p><strong>Rakefile:</strong><br /><br />
The Rake-file for libraries which use Rake for builds.</p></li>
<li><p><strong>[packagename].gemspec:</strong><br /><br />
*.gemspec file, which has the name of the main directory, contains all package meta-data, e.g. name, version, directories etc.</p></li>
</ol>

<h3 id="the-gemspec-file">The gemspec File</h3>

<hr />

<p>Pretty much like any application collection that is distributed or shared, Gems also come with a file describing the package, which also tends to contain some very useful additional information.</p>

<p>These <em>gemspec</em> files contain certain required data, such as the package version, maintainer name and platform, with the possibility of some optional attributes such as keys, contact information or additional description.</p>

<p>A <em>gemspec</em> file looks similar to the following example:</p>
<pre class="code-pre "><code langs="">Gem::Specification.new do |s|
  s.name        = 'myapp'
  s.version     = '1.5.7'
  s.licenses    = ['Apache']
  s.summary     = "My application package"
  s.description = "Optional, longer description."
  s.authors     = ["Maintaner Name"]
  s.files       = ["path/to/files", "additional/files", ..]
end
</code></pre>
<h2 id="getting-started-with-rubygems">Getting Started With RubyGems</h2>

<hr />

<h3 id="installing-ruby-and-rubygems">Installing Ruby And RubyGems</h3>

<hr />

<p>If you have not installed Ruby, and thus the RubyGems, you can follow one of the two links below to get it on your platform of choice.</p>

<ul>
<li><p><strong>CentOS / Rhel:</strong><br /><br />
<a href="https://indiareads/community/articles/how-to-install-ruby-2-1-0-on-centos-6-5-using-rvm">How To Install Ruby 2.1.0 On CentOS 6.5 Using RVM</a></p></li>
<li><p><strong>Ubuntu / Debian:</strong><br /><br />
<a href="https://link_to_8_4_ruby_sinatra_on_ubuntu">How To Install Ruby 2.1.0 On Ubuntu 13 With RVM</a></p></li>
</ul>

<h3 id="usage">Usage</h3>

<hr />

<p>Working with RubyGems is very easy. Once you install Ruby, the application should be set up in your <code>PATH</code> and you can start using the tool by typing <code>gem</code> inside the terminal emulator (e.g. Terminal for OS X, Putty for Windows etc).</p>

<p>Run <code>gem</code> to see some usage instructions and examples:</p>
<pre class="code-pre "><code langs="">gem

# RubyGems is a sophisticated package manager for Ruby.  This is a
# basic help message containing pointers to more information.

# Usage:
#     gem -h/--help
#     gem -v/--version
#     gem command [arguments...] [options...]
</code></pre>
<p>As you can see, working with <code>gem</code> consists of chaining a command with arguments and options, e.g.:</p>
<pre class="code-pre "><code langs="">gem help commands

# GEM commands are:

# build             Build a gem from a gemspec
# cert              Manage RubyGems certificates and signing settings
# check             Check a gem repository for added or missing files
# ..
</code></pre>
<blockquote>
<p><strong>Tip:</strong> When you run a command like ruby or rake, your operating system searches through a list of directories to find an executable file with that name. This list of directories lives in an environment variable called PATH, with each directory in the list separated by a colon:</p>

<p>/usr/local/bin:/usr/bin:/bin</p>

<p>Directories in PATH are searched from left to right, so a matching executable in a directory at the beginning of the list takes precedence over another one at the end. In this example, the /usr/local/bin directory will be searched first, then /local/bin, then /bin.</p>

<p>When you install Ruby, both the <code>ruby</code> interpreter and the RubyGems' <code>gem</code> get added to your PATH.</p>
</blockquote>

<h2 id="main-rubygem-operations-to-work-with-gems">Main RubyGem Operations To Work With Gems</h2>

<hr />

<p>Beginning to learn a new programming language also means learning to work with the basic and common related tools, such as the RubyGems for Ruby. Without over-complicating things, let's see the basic operations that you need to know when getting started with this package management tool.</p>

<p>Usually, the main operations with any package manager can be considered as:</p>

<ul>
<li><p>Finding out what is installed on the system;</p></li>
<li><p>Searching for and discovering new packages;</p></li>
<li><p>Finding out which packages need updating;</p></li>
<li><p>Installing new ones;</p></li>
<li><p>Updating old ones;</p></li>
<li><p>Removing (or deleting) packages;</p></li>
<li><p>Cleaning up what is no longer needed;</p></li>
<li><p>Checking out the documentation.</p></li>
</ul>

<p>Let's see how to perform these operations with RubyGems.</p>

<h3 id="finding-installed-and-available-gems">Finding Installed And Available Gems</h3>

<hr />

<p>You can think of finding all the currently installed gems as getting a <em>list</em> of their names. </p>

<p>Hence remembering that the <code>list</code> command is what you need for this operation.</p>

<p>Run the following to get a <em>list</em> of installed gems with their versions:</p>
<pre class="code-pre "><code langs="">gem list

# ** LOCAL GEMS ***

# actionmailer (4.0.2)
# actionpack (4.0.2)
# activesupport (4.0.2)
# bundler (1.5.3, 1.5.2)
# capistrano (3.1.0)
# coffee-rails (4.0.1)
# coffee-script (2.2.0)
# coffee-script-source (1.7.0, 1.6.3)
# execjs (2.0.2)
# i18n (0.6.9)
# ..
</code></pre>
<h3 id="searching-rubygems-org-for-gems">Searching RubyGems.org For Gems</h3>

<hr />

<p>If you already know the name of a gem, you can use the <code>search</code> command to look for it. </p>

<p>In return, you will again have a list of gems and their versions. </p>

<p>Run the following to search for a gem by name:</p>
<pre class="code-pre "><code langs=""># Usage: gem search [name]
# Example:
gem search rails

# *** REMOTE GEMS ***

# rails (4.0.2)
# rails-3-settings (0.1.1)
# rails-action-args (0.1.1)
# rails-admin (0.0.0)
# .. 
</code></pre>
<p><strong>Note:</strong> You can use regular expressions with the gem name queried. You can also pass the <code>-d</code> flag to get additional information, e.g.:</p>
<pre class="code-pre "><code langs="">gem search rails_utils -d

# *** REMOTE GEMS ***

# rails_utils (2.1.4)
#     Author: Winston Teo
#     Homepage: https://github.com/winston/rails_utils
# 
#     Rails helpers based on opinionated project practices.
</code></pre>
<h3 id="installing-new-gems">Installing New Gems</h3>

<hr />

<p>Once you have found a gem you would like to install, you can simply use the <code>install</code> command.</p>

<p>Run the following to install a new gem:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] gem install [name]
# Example:
gem install rails_utils

# Fetching: rails_utils-2.1.4.gem (100%)
# Successfully installed rails_utils-2.1.4
# Parsing documentation for rails_utils-2.1.4
# Installing ri documentation for rails_utils-2.1.4
# Done installing documentation for rails_utils after 0 seconds
# 1 gem installed
</code></pre>
<p><strong>Note:</strong> When you install a new gem, all the dependencies specified within the gem are also installed so that the gem can actually work.</p>

<p>In order to download a specific version of a gem, use the following:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] gem install [name] -v [version]
# Example:
gem install rails -v 4.0
</code></pre>
<h3 id="finding-outdated-gems">Finding Outdated Gems</h3>

<hr />

<p>In order to find out which gems are outdated (i.e. a newer version exists), you can use the <code>outdated</code> command. This, again, will provide you a list of gems with their currently installed versions (i.e. <em>local gems</em>).</p>

<p>Run the following to find out which gems are outdated:</p>
<pre class="code-pre "><code langs="">gem outdated

# builder (3.1.4 < 3.2.2)
# bundler (1.5.2 < 1.5.3)
# coffee-script-source (1.6.3 < 1.7.0)
# jquery-rails (3.0.4 < 3.1.0)
</code></pre>
<h3 id="updating-gems">Updating Gems</h3>

<hr />

<p>After you see which gems need updating, you can simply do so using the <code>update</code> command.</p>

<p>Run the following to update a gem:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] gem update [name]
# Example:
gem update bundler

# Updating installed gems
# Updating bundler
# Fetching: bundler-1.5.3.gem (100%)
# Successfully installed bundler-1.5.3
# Parsing documentation for bundler-1.5.3
# Installing ri documentation for bundler-1.5.3
# Installing darkfish documentation for bundler-1.5.3
# Done installing documentation for bundler after 6 seconds
# Gems updated: bundler
</code></pre>
<h3 id="removing-deleting-gems">Removing / Deleting Gems</h3>

<hr />

<p>Removing a gem from your local machine is done with the <code>uninstall</code> command, similarly to the <code>install</code>.</p>

<p>Run the following to remove / delete a gem:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] gem uninstall [name]
# Example:
gem uninstall bundler
</code></pre>
<p>Alternatively, you can specify a version to remove that one only.</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] gem uninstall [name] -v [version]
# Example:
gem uninstall bundler -v 1.0.0

# Successfully uninstalled bundler-1.0.0
</code></pre>
<h3 id="reading-the-gem-documentation">Reading The Gem Documentation</h3>

<hr />

<p>One of the most handy and important things about gems is that they [should] come with good documentation to allow you to start working with them fast. The simplest way to go with documentation is to run a local server where you will have access to all installed gems' usage instructions.</p>

<p>Run the following to run a documentation server:</p>
<pre class="code-pre "><code langs="">gem server

# Server started at http://0.0.0.0:8808
# Server started at http://[::]:8808
</code></pre>
<p>You can now access <code>http://0.0.0.0:8808</code> using your favourite browser, find the gem you would like to learn more about and read its documentation.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    