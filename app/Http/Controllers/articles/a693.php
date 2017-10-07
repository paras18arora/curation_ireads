<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>There are numerous choices available for application level messaging implementations, each with its own special benefits over the others. One thing is surely certain, however, and that is for many situations a full featured solution and protocol implementation (such as Advanced Message Queuing Protocol) can be an over-kill.</p>

<p>For these situations, a lean and truly high-performant messaging library which allows you to craft the exact system you need has tons of benefits. One of these libraries - and perhaps <em>the go-to solution</em> of its kind - is <strong>ZeroMQ</strong>.</p>

<p>ZeroMQ is an asynchronous messaging system toolkit (i.e. a library). It doesn't follow conventions, nor does it set itself defining a new protocol. Despite the heavyweight champions of the world, this superbly efficient messaging component focuses on handling tasks as efficiently and as powerfully as they can be handled without being an extra abundant layer when it’s not needed.</p>

<p>In this IndiaReads article, we are going to learn about setting up the latest version of ZeroMQ from source, which should allow you to start implementing efficient lightweight messaging into your application stack.</p>

<h2 id="zeromq">ZeroMQ</h2>

<hr />

<p>If you have past experience with other messaging systems such as RabbitMQ, it might be a little bit challenging to understand the position of ZeroMQ due to some wide range of irrelevant comparisons all over the internet. These two are completely different tools aimed to solve different kinds of problems.</p>

<p>ZeroMQ, as we have mentioned at the beginning, is a library (i.e. toolkit). Although it might appear as a lower level solution compared to others, it brings everything necessary to quickly implement custom messaging solutions with its ease of use and large range of different programming language bindings.</p>

<p>What this translates to is the need of downloading and setting up ZeroMQ library, followed by the additional files for your programming language of choice to get started building a ZeroMQ application. In our tutorial, to get the latest versions and have stable installation, we are going to install ZeroMQ from source in a few simple steps. </p>

<h2 id="installing-from-source">Installing From Source</h2>

<hr />

<p>Building applications on Unix systems can appear scary to some, but it is generally easier than you think. Although it should be noted that there are other tools to achieve the same task, we will be using <strong>GNU make</strong> to build ZeroMQ. GNU make is one of the most widespread utilities as it’s been built into Unix systems since its introduction in the late 70s.</p>

<h3 id="why-build-from-source">Why Build From Source?</h3>

<hr />

<p>Many system administrators choose to build software from source as it can help to solve problems caused by deb/rpm (pre-made) packages. It also allows you to customize the installation process, to have multiple versions of the same application on a single system, and to use the desired one without worrying about pre-built binaries (compiled files).</p>

<h2 id="taking-care-of-zeromq-dependencies-and-preparing-the-system">Taking Care of ZeroMQ Dependencies and Preparing the System</h2>

<hr />

<p>With the more recent builds of both operating systems and ZeroMQ application itself, installation process became more and more simpler. Nonetheless, we need to make a few preparations before starting the build process.</p>

<h3 id="update-the-default-operating-system-tools">Update the Default Operating System Tools</h3>

<hr />

<p>To ensure that we have the latest version of default system tools, let's begin with running a base update on our system:</p>
<pre class="code-pre "><code langs="">yum -y update
</code></pre>
<h3 id="enable-additional-application-repository-epel">Enable Additional Application Repository (EPEL)</h3>

<hr />

<p>To be able to download certain tools necessary for building and using ZeroMQ  and many others, we need to enable <strong>EPEL</strong>: Extra Packages for Enterprise Linux. This will allow us to download and install many packages that are not available by default with ease.</p>

<p>Run the following commands to enable EPEL:</p>
<pre class="code-pre "><code langs=""># If you are on a 64-bit CentOS / RHEL based system: 
wget http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
rpm -ivh epel-release-6-8.noarch.rpm

# If you are on a 32-bit CentOS / RHEL based system:
wget http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
rpm -ivh epel-release-6-8.noarch.rpm
</code></pre>
<h3 id="download-additional-tools-for-building-from-source">Download Additional Tools for Building From Source</h3>

<hr />

<p>ZeroMQ's build process - as previously mentioned - requires a few additional tools. Upon enabling EPEL, we can easily download them using the default package manager YUM.</p>

<p>Run the following to get the tools:</p>
<pre class="code-pre "><code langs="">yum install -y uuid-devel
yum install -y pkgconfig
yum install -y libtool
yum install -y gcc-c++
</code></pre>
<h2 id="downloading-and-installing-zeromq-from-source">Downloading and Installing ZeroMQ From Source</h2>

<hr />

<p>After covering all necessary applications, we can begin the installation process for ZeroMQ.</p>

<p>The latest available version for ZeroMQ is <strong>4.0.3</strong> released on 2013-11-24.</p>

<p>Let's begin with downloading the application source:</p>
<pre class="code-pre "><code langs="">wget http://download.zeromq.org/zeromq-4.0.3.tar.gz
</code></pre>
<p>Extract the contents of the tar archive and enter the directory:</p>
<pre class="code-pre "><code langs="">tar xzvf zeromq-4.0.3.tar.gz
cd zeromq-4.0.3
</code></pre>
<p>Configure the application build procedure:</p>
<pre class="code-pre "><code langs="">./configure
</code></pre>
<p>Build the program using the Makefile:</p>
<pre class="code-pre "><code langs="">make
</code></pre>
<p>Install the application:</p>
<pre class="code-pre "><code langs="">make install
</code></pre>
<p>Update the system library cache:</p>
<pre class="code-pre "><code langs="">echo /usr/local/lib > /etc/ld.so.conf.d/local.conf
ldconfig    
</code></pre>
<p>And that's it! You now have ZeroMQ messaging library set up on your system, which can be used to to create messaging applications.</p>

<h2 id="getting-zeromq-language-bindings">Getting ZeroMQ Language Bindings</h2>

<hr />

<h3 id="python-bindings-pyzmq">Python Bindings: PyZMQ</h3>

<hr />

<p>It is possible to download and build Python bindings for ZeroMQ (PyZMQ) using the Python package manager <strong>pip</strong>.</p>

<p>Download and install PyZQM with pip:</p>
<pre class="code-pre "><code langs="">pip install pyzmq
</code></pre>
<p>If you would like to learn about setting up Python 2.7.x and 3.x on CentOS along with common Python tools including pip, check out our article <a href="https://indiareads/community/articles/how-to-set-up-python-2-7-6-and-3-3-3-on-centos-6-4">How To Set Up Python on CentOS</a>.</p>

<h3 id="ruby-bindings-zmq-gem">Ruby Bindings: zmq Gem</h3>

<hr />

<p>ZeroMQ Ruby bindings are available as a Ruby Gem called <strong>zmq</strong>.</p>

<p>For default ZeroMQ installations, run the following to get zmq:</p>
<pre class="code-pre "><code langs="">gem install zmq
</code></pre>
<p>For non-default ZeroMQ installations, use the following:</p>
<pre class="code-pre "><code langs="">gem install zmq -- --with-zmq-dir=/path/to/zeromq
</code></pre>
<h3 id="other-programming-language-bindings-for-zeromq">Other Programming Language Bindings for ZeroMQ</h3>

<hr />

<p>For all other ZeroMQ bindings - including but not limited to PHP, C#, Erlang, Haskell, Java, Lua and more - visit <a href="http://zeromq.org/community">ZeroMQ Community Wiki</a>.</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    