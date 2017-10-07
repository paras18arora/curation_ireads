<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Ruby on Rails, or RoR for short, is a very popular full-stack web application development framework written in Ruby. It allows you to rapidly develop web applications that conform to the MVC (model-view-controller) pattern.</p>

<p>This tutorial will cover how to set up a Ruby on Rails development environment using RVM on your FreeBSD 10.1 server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin, all you need is:</p>

<ul>
<li><p>A FreeBSD 10.1 Droplet.</p></li>
<li><p>A user with <strong>root</strong> privileges. (The default <strong>freebsd</strong> user is fine.)</p></li>
</ul>

<h2 id="step-1-—-setting-bash-as-the-default-shell">Step 1 — Setting bash as the Default Shell</h2>

<p>This tutorial will use the Ruby Version Manager, or RVM for short, to install Ruby. Because RVM works best with bash 3.2.25 or higher, in this step, we will install bash and set it as the default shell.</p>

<p>Before we begin, log into your FreeBSD 10.1 server.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="local$">ssh freebsd@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>Next, install the latest version of bash using <code>pkg</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg install bash
</li></ul></code></pre>
<p>We'll need to add a line to <code>/etc/fstab</code> for bash to work. Open the file using <code>ee</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ee /etc/fstab
</li></ul></code></pre>
<p>Add the line <code>fdesc /dev/fd     fdescfs     rw  0   0</code> to the end of the file as shown below.</p>
<div class="code-label " title="/etc/fstab">/etc/fstab</div><pre class="code-pre "><code langs=""># Custom /etc/fstab for FreeBSD VM images
/dev/gpt/rootfs /       ufs     rw      2       2
/dev/gpt/swapfs none    swap    sw      0       0
<span class="highlight">fdesc    /dev/fd     fdescfs     rw  0   0</span>
</code></pre>
<p>Save and exit the file, then mount the new entry.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mount -a
</li></ul></code></pre>
<p>Now that bash is installed, set it as your default shell using the <code>chsh</code> command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chsh -s bash
</li></ul></code></pre>
<p>To start using bash, log out and log back in to your server. If you don't want to log out, you can start a bash session manually by typing in:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bash
</li></ul></code></pre>
<h2 id="step-2-—-installing-rvm">Step 2 — Installing RVM</h2>

<p>In this step, we will install RVM.</p>

<p>To download the RVM installer, you first need to install <code>curl</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg install curl
</li></ul></code></pre>
<p>Move to the <code>/tmp</code> directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /tmp
</li></ul></code></pre>
<p>Download the RVM installer script from <code>https://get.rvm.io</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">curl -sSL https://get.rvm.io -o installer.sh
</li></ul></code></pre>
<p>Finally, use the script to install the latest stable release of RVM.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bash installer.sh stable
</li></ul></code></pre>
<p>Because RVM makes a few changes in your shell's startup configuration, the recommended way to activate those changes is by logging out of your current session and logging back in. Alternatively, you can apply the changes to your current session manually by running:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">. ~/.rvm/scripts/rvm
</li></ul></code></pre>
<h2 id="step-3-—-installing-ruby">Step 3 — Installing Ruby</h2>

<p>You can now use RVM to install any version of Ruby. Because <strong>2.2.2</strong> is the latest stable version available as of June 2015, we'll install this version.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rvm install <span class="highlight">2.2.2</span>
</li></ul></code></pre>
<p>This will take a moment. Once the installation completes, list the rubies available on your system.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rvm list
</li></ul></code></pre>
<p>If your installation was successful, you will see:</p>
<div class="code-label " title="rvm list output">rvm list output</div><pre class="code-pre "><code langs="">rvm rubies

=* ruby-2.2.2 [ i386 ]

# => - current
# =* - current && default
#  * - default
</code></pre>
<p>To confirm that Ruby 2.2.2 is present in your <code>$PATH</code>, type in:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ruby -v
</li></ul></code></pre>
<p>You should see a message that looks like this:</p>
<div class="code-label " title="ruby -v output">ruby -v output</div><pre class="code-pre "><code langs="">ruby 2.2.2p95 (2015-04-13 revision 50295) [x86_64-freebsd10.1]
</code></pre>
<h2 id="step-4-—-installing-ruby-on-rails">Step 4 — Installing Ruby on Rails</h2>

<p>In this step, we will install Ruby on Rails.</p>

<p>Because Ruby on Rails is a gem, it can be easily installed using RubyGems (Ruby's package management framework) using <code>gem install rails</code>. However, this installation will take a while to complete because it includes lots of other gems (some of which need to be compiled) and their documentation files. You can speed up this command considerably by adding the <code>--no-rdoc --no-ri</code> flags, which will skip the documentation installation.</p>

<p>Install Ruby on Rails, optionally without documentation.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">gem install rails <span class="highlight">--no-rdoc --no-ri</span>
</li></ul></code></pre>
<p>For the Rails Assets Pipeline to work, a Javascript runtime should be present on your server. The easiest way to get one is by installing Node.js using <code>pkg</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pkg install node-devel
</li></ul></code></pre>
<h2 id="step-5-—-creating-a-test-project">Step 5 — Creating a Test Project</h2>

<p>Now that the Rails installation is complete, let's test it by creating an empty project inside the <code>/tmp</code> directory.</p>

<p>If you're not still in the <code>/tmp</code> directory, change to it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /tmp
</li></ul></code></pre>
<p>Use the <code>rails</code> command to create a new project called <strong>test-project</strong> (or whatever you like).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails new <span class="highlight">test-project</span>
</li></ul></code></pre>
<p>Enter the project directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd <span class="highlight">test-project</span>/
</li></ul></code></pre>
<p>And finally, try starting the Rails console.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">rails c
</li></ul></code></pre>
<p>If your Rails installation was successful, you should see the following prompt:</p>
<div class="code-label " title="rails c prompt">rails c prompt</div><pre class="code-pre "><code langs="">Loading development environment (Rails 4.2.1)
2.2.2 :001 >
</code></pre>
<p>You can exit the prompt by entering <code>exit</code>.</p>
<pre class="code-pre custom_prefix"><code langs=""><ul class="prefixed"><li class="line" prefix="2.2.2 :001 >">exit
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>In this tutorial, you learned how to set up Ruby on Rails on your FreeBSD 10.1 server. You can now use your FreeBSD server as a development environment for your Rails projects!</p>

<p>While doing so, you also learned how to install Ruby using RVM. If you want to learn more about RMV, check out this tutorial on <a href="https://indiareads/community/tutorials/how-to-use-rvm-to-manage-ruby-installations-and-environments-on-a-vps">how to use RVM to manage your Ruby environments</a>.</p>

    