<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Sharing forms a bridge between everyone involved and it makes things grow. That's the basis for the open-source movement, which gave way and allowed so many great things to happen -- especially in the recent years. </p>

<p>This principal applies for Ruby and Ruby based applications. That's why - and how - a developer can get started working on making their idea a reality so rapidly, thanks to all the available tools, libraries, and frameworks that they can take advantage of.</p>

<p>In this IndiaReads article, we aim to help those trying to find ways to give back to the community by sharing their own Ruby-based or Ruby-related creations. We are going to shed some light behind the mystery of packaging code for others to be able to easy download it as a Gem using the RubyGems package manager, which makes the whole process a breeze.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-packaging-applications">1. Packaging Applications</h3>

<hr />

<h3 id="2-rubygems-package-manager-and-ruby-gem-packages">2. RubyGems Package Manager And Ruby Gem Packages</h3>

<hr />

<ol>
<li>RubyGems  Package Manager</li>
<li>Ruby Gem Packages</li>
<li>Gem Package Structure</li>
</ol>

<h3 id="3-getting-ruby-and-necessary-tools">3. Getting Ruby And Necessary Tools</h3>

<hr />

<ol>
<li>Installing Ruby</li>
<li>Installing Bundler</li>
</ol>

<h3 id="3-packaging-a-ruby-application">3. Packaging A Ruby Application</h3>

<hr />

<ol>
<li>Preparing The Distribution Directory</li>
<li>Creating A .gemspec File</li>
<li>Placing The Application Code</li>
<li>Modifying The Main Application Script</li>
<li>Making Sure Everything Works</li>
<li>Listing Your Gem's Dependencies</li>
<li>Committing The Gem Package</li>
</ol>

<h3 id="4-releasing-a-gem-package">4. Releasing A Gem Package</h3>

<hr />

<ol>
<li>Creating The Package</li>
<li>Publishing The Gem</li>
</ol>

<h2 id="packaging-applications">Packaging Applications</h2>

<hr />

<p>One of the ways of distributing applications, libraries, or other programming related code bundles is to put them in archives called <em>packages</em>. Application packages contain already compiled and ready-to-use software in an easy-to-keep-track-of and easy-to-use way. They usually come with additional files that contain information about the package and sometimes documentation as well.</p>

<p>Packaging applications, therefore, consists of following a set format defined by package management tools (i.e. the RubyGems) and using these tools to share them with others in an easily accessible way.</p>

<p>In this tutorial, we will begin with understanding RubyGems, the Gem package format, and then learn how to package a Ruby application from start to finish, beginning with creating the package structure to contain the code (and other related material).</p>

<h2 id="rubygems-package-manager-and-ruby-gem-packages">RubyGems Package Manager And Ruby Gem Packages</h2>

<hr />

<p><strong>Note:</strong> The subject matter of this article is to package applications. This section contains a summary of related tools and materials. To learn more about them, you can read the <a href="http://link_to_9_4_rubygems_introduction">introductory first part</a> of our RubyGems series.</p>

<h3 id="rubygems-package-manager">RubyGems Package Manager</h3>

<hr />

<p>RubyGems is the default <em>package manager</em> for Ruby. It helps with all application package lifecycle from downloading to distributing Ruby applications and relevant binaries or libraries. RubyGems is a powerful package management tool which provides the developers a standardised structure for packing application in archives called <em>Ruby Gems</em>.</p>

<h3 id="ruby-gem-packages">Ruby Gem Packages</h3>

<hr />

<p>A Gem is a Ruby application package which can contain anything from a collection of code to libraries, and/or list of dependencies that the packaged code actually needs to run.</p>

<h3 id="gem-package-structure">Gem Package Structure</h3>

<hr />

<p>Gem packages contain different sets of components. Each component gets placed inside a dedicated location within the gem bundle.</p>

<p>All the below elements (and more) can go inside Gems:</p>

<ul>
<li><p>Application code;</p></li>
<li><p>Tests;</p></li>
<li><p>Description of dependencies;</p></li>
<li><p>Binaries;</p></li>
<li><p>Relevant Documentation; </p></li>
<li><p>Information regarding the package (gemspec).</p></li>
</ul>

<p>Gems are formed of a structure similar to the following:</p>
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

<h2 id="getting-ruby-and-necessary-tools">Getting Ruby And Necessary Tools</h2>

<hr />

<h3 id="installing-ruby">Installing Ruby</h3>

<hr />

<p>In case you don't have Ruby installed already, you can follow one of the two links below to get it properly set up on your platform of choice.</p>

<ul>
<li><p><strong>CentOS / Rhel:</strong><br /><br />
<a href="https://indiareads/community/articles/how-to-install-ruby-2-1-0-on-centos-6-5-using-rvm">How To Install Ruby 2.1.0 On CentOS 6.5 Using RVM</a></p></li>
<li><p><strong>Ubuntu / Debian:</strong><br /><br />
<a href="https://link_to_8_4_ruby_sinatra_on_ubuntu">How To Install Ruby 2.1.0 On Ubuntu 13 With RVM</a></p></li>
</ul>

<h3 id="installing-bundler">Installing Bundler</h3>

<hr />

<p>One of the tools we will be using for creating Gems is <em>Bundler</em>. Once Ruby and thus RubyGems are installed on your system, you can use the `<code>gem</code> command to get <strong>bundler</strong>.</p>

<p>Run the following to install <code>bundler</code> using <code>gem</code>:</p>
<pre class="code-pre "><code langs="">gem install bundler

# Successfully installed bundler-1.5.3
# Parsing documentation for bundler-1.5.3
# Done installing documentation for bundler after 3 seconds
# 1 gem installed
</code></pre>
<h2 id="packaging-a-ruby-application">Packaging A Ruby Application</h2>

<hr />

<p>There are several ways to start creating a Gem package. One the methods is to use the popular Bundler, a Ruby environment and dependency manager that helps with an application's requirements and maintenance of the code. This tool can also be used to scaffold a Gem distribution directory to kick-start the packaging process.</p>

<h3 id="preparing-the-distribution-directory">Preparing The Distribution Directory</h3>

<hr />

<p>Gem packages are kept in package directories which should be named after your package as we have discussed in the previous structuring section. Since these are simple locations found on the file system, you can use the Unix <code>mkdir</code> command to create them one by one... or get Bundler to do the job.</p>

<p>Run the following command to scaffold all the necessary directories inside a folder, named after your Gem's desired name:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] bundle gem [your chosen gem name]
# Example:
bundle gem my_gem
</code></pre>
<p><strong>Note:</strong> Gem names need to be unique. Therefore, you should search and make sure that the name you would like to use for your Gem is not already chosen by someone else's project. In order to verify, you can visit and search on <a href="http://rubygems.org/">RubyGems.org</a>.</p>

<p>The above command will run a series of commands to create our package structure, e.g.:</p>
<pre class="code-pre "><code langs=""># bundle gem my_gem
#       create  my_gem/Gemfile
#       create  my_gem/Rakefile
#       create  my_gem/LICENSE.txt
#       create  my_gem/README.md
#       create  my_gem/.gitignore
#       create  my_gem/my_gem.gemspec
#       create  my_gem/lib/my_gem.rb
#       create  my_gem/lib/my_gem/version.rb
# Initializing git repo in .../my_gem
</code></pre>
<p>As you will see, Bundler has also created a brand-new Git repository which comes in handy with various versioning operations. </p>

<p><strong>Note:</strong> If you would like to learn more about how to work with Git, check out the <a href="https://indiareads/community/community_tags/git">IndiaReads community articles</a> on the subject.</p>

<h3 id="creating-a-gemspec-file">Creating A .gemspec File</h3>

<hr />

<p>The <code>.gemspec</code> file contains some absolutely vital information regarding Gem packages. Ever Gem must be shipped with one where meta-data from Gem name to version and description to folders to be included by Gem are found.</p>

<p><strong>Tip:</strong> <code>.gemspec</code> files are regular Ruby scripts -- which means that they are programmable.</p>

<p>To see the contents of the generic <em>gemspec</em> created by Bundler, use the following command:</p>
<pre class="code-pre "><code langs="">cat my_gem/my_gem.gemspec

# # coding: utf-8
# lib = File.expand_path('../lib', __FILE__)
# $LOAD_PATH.unshift(lib) unless $LOAD_PATH.include?(lib)
# require 'my_gem/version'

# Gem::Specification.new do |spec|
#   spec.name          = "my_gem"
#   spec.version       = MyGem::VERSION
#   spec.authors       = ["Maintainer Name"]
#   spec.email         = ["maintainer@email.address"]
#   spec.summary       = %q{TODO: Write a short summary. Required.}"
#   spec.description   = %q{TODO: Write a longer description. Optional.}
#   spec.homepage      = ""
#   spec.license       = "MIT"

#   spec.files         = `git ls-files -z`.split("\x0")
#   spec.executables   = spec.files.grep(%r{^bin/}) { |f| File.basename(f) }
#   spec.test_files    = spec.files.grep(%r{^(test|spec|features)/})
#   spec.require_paths = ["lib"]

#   spec.add_development_dependency "bundler", "~> 1.5"
#   spec.add_development_dependency "rake"
# end
# mba:Git
</code></pre>
<p>You can either edit this file now, or before each time you package and publish.</p>

<p>In order to modify this file, you can use the following command to edit it using <strong>nano</strong>:</p>
<pre class="code-pre "><code langs="">nano my_gem/my_gem.gemspec
</code></pre>
<p>This will open up the nano text editor.</p>

<p>One of the recommended additional information you might want to declare here is the minimum Ruby interpreter version required to run your code.</p>

<p>You can do this with the <code>required_ruby_version</code> declaration, which can be added towards the bottom end of the file for consistency.</p>

<p>For example:</p>
<pre class="code-pre "><code langs=""># ..

  # Declare that the Gem is compatible with
  # version 2.0 or greater
  spec.required_ruby_version = ">= 2.0"

  spec.add_development_dependency "bundler", "~> 1.5"
  spec.add_development_dependency "rake"
end
</code></pre>
<p>Once you are done editing your file, press CTRL+X and confirm with Y to save and exit.</p>

<p><strong>Note:</strong> Do not forget to modify the declarations that contain a "to-do" placeholder (i.e. <code>%q{TODO:</code>) such as the <code>spec.description</code>.</p>

<h3 id="placing-the-application-code">Placing The Application Code</h3>

<hr />

<p>Your library (or application, framework, etc.) shall always go inside the <code>/lib</code> directory. Inside this directory, there shall be a Ruby script named exactly the same way as your Gem. This file is the main one that gets imported when another application depends on your Gem.</p>

<p>The recommended and tidiest way to place your application code is to divide it into bits and place them in a directory, inside <code>/lib</code>, where they are used and made available by <code>my_gem.rb</code> to the public.</p>

<p>When you look at the contents of the <code>/lib</code> directory, you will see that the main Ruby script and a directory to contain your code is ready:</p>
<pre class="code-pre "><code langs="">ls -l my_gem/lib

# drwxr-xr-x  3 user  staff  102 dd Mmm hh:mm my_gem
# -rw-r--r--  1 user  staff   70 dd Mmm hh:mm my_gem.rb
</code></pre>
<p>And the <code>my_gem</code> directory inside <code>/lib</code> comes with a version file:</p>
<pre class="code-pre "><code langs="">cat my_gem/lib/my_gem/version.rb

module MyGem
  VERSION = "0.0.1"
end
</code></pre>
<p>This <code>VERSION</code> number is set to be automatically imported and used inside the <code>*.gemspec</code> file by Bundler. You can modify it here and change it to match your Gem's current version.</p>

<p>You should move all your code here to be used by your application.</p>

<p>As an example, let's create a <code>Hello [name]!</code> module.</p>
<pre class="code-pre "><code langs="">nano my_gem/lib/my_gem/hail.rb
</code></pre>
<p>Place the below example inside:</p>
<pre class="code-pre "><code langs="">class Hail
  def self.name(n = "Dalek")
    n
  end
end
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<h3 id="modifying-the-main-application-script">Modifying The Main Application Script</h3>

<hr />

<p>In the previous step, we have learned that for the purposes of keeping everything in order, applications with especially a lot of classes should be separated into pieces with all elements placed inside the <code>/lib/[gem-name]</code> directory.</p>

<p>Let's see how we can modify the main Ruby script that gets imported when somebody uses your Gem.</p>

<p>Run the following command to edit the imported Ruby file inside <code>/lib</code> using nano:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] nano my_gem/lib/[gem name].rb
nano my_gem/lib/my_gem.rb
</code></pre>
<p>You will see a very short script similar to the one below:</p>
<pre class="code-pre "><code langs="">require "my_gem/version"

module MyGem
  # Your code goes here...
end
</code></pre>
<p>Here, you should import all your classes and code from the <code>/lib/[gem name]</code> directory and use them.</p>

<p>In our case, let's see how to use <code>Hail</code> class which we created in the previous step.</p>

<p>Modify your code similarly to below example:</p>
<pre class="code-pre "><code langs="">require "my_gem/version"
require "my_gem/hail"

module MyGem
  def self.hi(n = "Default Name")
    hail = Hail
    Hail.name(n)
  end
end
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p><strong>Note:</strong> Although there is no need to make <code>Hail</code> an <em>instantiable</em> class, for the purposes of demonstration, we have made it so and left MyGem as a module to use its methods directly.</p>

<h3 id="making-sure-everything-works">Making Sure Everything Works</h3>

<hr />

<p>Once you move your code inside and modify your main imported script, you will want to make sure everything works, naturally. The simplest way to go about doing this is to use <em>Bundler</em> again -- and not by installing the Gem.</p>

<p>First, let's enter the Gem directory and then use Bundler's console feature:</p>
<pre class="code-pre "><code langs="">cd my_gem
bundler console
</code></pre>
<p>This will load your Gem using the information from the <code>*.gemspec</code> and let you get to work, e.g.:</p>
<pre class="code-pre "><code langs="">bundler console

# irb(main):001:0> MyGem.hi("Hello world!")
# => "Hello world!"
</code></pre>
<h3 id="listing-your-gem-39-s-dependencies">Listing Your Gem's Dependencies</h3>

<hr />

<p>In a real world scenario, it is highly likely that your Gem itself will be dependent on others.</p>

<p>These dependencies are also listed in the <code>*.gemspec</code> file. </p>

<p>Run the following command to edit the file using nano:</p>
<pre class="code-pre "><code langs="">nano my_gem/my_gem.gemspec
</code></pre>
<p>Add the following instructions at an appropriate block to list dependencies:</p>
<pre class="code-pre "><code langs=""># Usage: spec.add_runtime_dependency "[gem name]", [[version]]
spec.add_runtime_dependency "activesupport", [">= 4.0"]
</code></pre>
<p>Press CTRL+X and confirm with Y to save and exit.</p>

<p><strong>Note:</strong> You can list all necessary dependencies by repeating the instructions successively on the <code>*.gemspec</code> file.</p>

<h3 id="committing-the-gem-package">Committing The Gem Package</h3>

<hr />

<p>Once your Gem is ready to be shipped, you should commit the Git repository for versioning. </p>

<p>Use the following command to commit with Git:</p>
<pre class="code-pre "><code langs="">git commit -m "Version 0.1.0" 
</code></pre>
<p>Git, then, is going to commit all your code and give you the results:</p>
<pre class="code-pre "><code langs=""># [master (root-commit) d4640b8] Version 0.1.0
#  8 files changed, 104 insertions(+)
#  create mode 100644 .gitignore
#  create mode 100644 Gemfile
#  create mode 100644 LICENSE.txt
#  create mode 100644 README.md
#  create mode 100644 Rakefile
#  create mode 100644 lib/my_gem.rb
#  create mode 100644 lib/my_gem/version.rb
#  create mode 100644 my_gem.gemspec
</code></pre>
<h2 id="releasing-a-gem-package">Releasing A Gem Package</h2>

<hr />

<p>Once you are happy with your Gem, you can release it to the world on RubyGems.org.</p>

<p><strong>Note:</strong> In order to release your code, you will need to have an account at <a href="https://rubygems.org/sign_up"><code>https://rubygems.org/sign_up</code></a>.</p>

<h3 id="creating-the-package">Creating The Package</h3>

<hr />

<p>Once all is set, you can create the package using the <code>gem</code> tool.</p>

<p>Run the following to create your package:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] gem build [gem name].gemspec
# Example:
gem build mygem.gemspec

# Successfully built RubyGem
# Name: my_gem
# Version: 0.1.0
# File: my_gem-0.1.0.gem
</code></pre>
<h3 id="publishing-the-gem">Publishing The Gem</h3>

<hr />

<p>There are a couple of ways to publish newly minted Gem. Either way, you will need to log-in with a RubyGems.org account so let's start with doing that first.</p>

<p>Run the following command to log-in using <code>gem</code>:</p>
<pre class="code-pre "><code langs="">gem push
</code></pre>
<p>Enter your email address, and then your password to sign in.</p>

<p><strong>Note:</strong> We specifically refrain from giving a Gem-name to push so that we only log-in without performing further action.</p>

<p>In order to simply push your Gem, run the following:</p>
<pre class="code-pre "><code langs=""># Usage: [sudo] gem push [gem file]
# Example:
gem push my_gem-0.1.0.gem
</code></pre>
<p>Alternatively, you can benefit from Bundler's Rake tasks. You can see a full list with the following:</p>
<pre class="code-pre "><code langs="">rake -T
</code></pre>
<ul>
<li><p><strong>rake build:</strong><br /><br />
Build my_gem-0.0.1.gem into the pkg directory</p></li>
<li><p><strong>rake install:</strong><br /><br />
Build and install my_gem-0.0.1.gem into system gems</p></li>
<li><p><strong>rake release:</strong><br /><br />
Create tag v0.0.1 and build and push my_gem-0.0.1.gem to Rubygems</p></li>
</ul>

<p>If you call <code>rake release</code>, your package will be pushed to your set Git account and then to RubyGems.org, e.g.:</p>
<pre class="code-pre "><code langs="">rake build
# my_gem 0.1.0 built to pkg/my_gem-0.1.0.gem.

rake release

# rake aborted!
# Couldn't git push. `git push  2>&1' failed with the following output:

# fatal: No configured push destination.
# Either specify the URL from the command-line or configure a remote repository using

#     git remote add <name> <url>
</code></pre>
<p>To continue, add a remote Git account:</p>
<pre class="code-pre "><code langs=""># Usage: git remote add origin git@github.com:[user name]/[repository].git
# Example:
git remote add origin git@github.com:maintainer1/my_gem.git
</code></pre>
<p>Then simultaniously release your code using <code>rake</code>:</p>
<pre class="code-pre "><code langs="">rake release

# ..
# Pushed MyGem
</code></pre>
<p>And that's it! You can now go to RubyGems.org and check out your Gem:</p>
<pre class="code-pre "><code langs="">http://www.rubygems.org/gems/[your gem name]
</code></pre>
<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    