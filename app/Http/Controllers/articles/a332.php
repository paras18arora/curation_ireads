<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p><strong>Docker</strong> containers are created by using [base] images. An image can be basic, with nothing but the operating-system fundamentals, or it can consist of a sophisticated pre-built application stack ready for launch.</p>

<p>When building your images with docker, each action taken (i.e. a command executed such as apt-get install) forms a new layer on top of the previous one. These base images then can be used to create new containers.</p>

<p>In this IndiaReads article, we will see about automating this process as much as possible, as well as demonstrate the best practices and methods to make most of docker and containers via <em>Dockerfiles</em>: scripts to build containers, step-by-step, layer-by-layer, automatically from a source (base) image.</p>

<h2 id="glossary">Glossary</h2>

<hr />

<h3 id="1-docker-in-brief">1. Docker in Brief</h3>

<hr />

<h3 id="2-dockerfiles">2. Dockerfiles</h3>

<hr />

<h3 id="3-dockerfile-syntax">3. Dockerfile Syntax</h3>

<hr />

<ol>
<li>What is Syntax?</li>
<li>Dockerfile Syntax Example</li>
</ol>

<h3 id="4-dockerfile-commands">4. Dockerfile Commands</h3>

<hr />

<ol>
<li>ADD</li>
<li>CMD</li>
<li>ENTRYPOINT</li>
<li>ENV</li>
<li>EXPOSE</li>
<li>FROM</li>
<li>MAINTAINER</li>
<li>RUN</li>
<li>USER</li>
<li>VOLUME</li>
<li>WORKDIR</li>
</ol>

<h3 id="5-how-to-use-dockerfiles">5. How To Use Dockerfiles</h3>

<hr />

<h3 id="6-dockerfile-example-creating-an-image-to-install-mongodb">6. Dockerfile Example: Creating an Image to Install MongoDB</h3>

<hr />

<ol>
<li>Creating the Empty Dockerfile</li>
<li>Defining Our File and Its Purpose</li>
<li>Setting The Base Image to Use</li>
<li>Defining The Maintainer (Author)</li>
<li>Updating The Application Repository List</li>
<li>Setting Arguments and Commands for Downloading MongoDB<br /></li>
<li>Setting The Default Port For MongoDB</li>
<li>Saving The Dockerfile</li>
<li>Building Our First Image</li>
<li>Running A MongoDB Instance</li>
</ol>

<h2 id="docker-in-brief">Docker in Brief</h2>

<hr />

<p>The <strong>docker project</strong> offers higher-level tools which work together, built on top of some Linux kernel features. The goal is to help developers and system administrators port applications. - with all of their dependencies conjointly - and get them running across systems and machines <em>headache free</em>. </p>

<p>Docker achieves this by creating safe, LXC (i.e. Linux Containers) based environments for applications called “docker containers”. These containers are created using <em>docker images</em>, which can be built either by executing commands manually or automatically through <strong>Dockerfiles</strong>.</p>

<p><strong>Note:</strong> To learn more about docker and its parts (e.g. docker daemon, CLI, images etc.), check out our introductory article to the project: <a href="https://indiareads/community/articles/how-to-install-and-use-docker-getting-started/">Docker Explained: Getting Started</a>.</p>

<h2 id="dockerfiles">Dockerfiles</h2>

<hr />

<p>Each Dockerfile is a script, composed of various commands (instructions) and arguments listed successively to automatically perform actions on a base image in order to create (or form) a new one. They are used for organizing things and greatly help with deployments by simplifying the process start-to-finish.</p>

<p>Dockerfiles begin with defining an image FROM which the <em>build process</em> starts. Followed by various other methods, commands and arguments (or conditions), in return, provide a new image which is to be used for creating docker containers.</p>

<p>They can be used by providing a Dockerfile's content - in various ways - to the <strong>docker daemon</strong> to build an image (as explained in the "How To Use" section).</p>

<h2 id="dockerfile-syntax">Dockerfile Syntax</h2>

<hr />

<p>Before we begin talking about Dockerfile, let's quickly go over its syntax and what that actually means.</p>

<h3 id="what-is-syntax">What is Syntax?</h3>

<hr />

<p>Very simply, syntax in programming means a structure to order commands, arguments, and everything else that is required to program an application to perform a procedure (i.e. a function / collection of instructions).</p>

<p>These structures are based on rules, clearly and explicitly defined, and they are to be followed by the programmer to interface with whichever computer application (e.g. interpreters, daemons etc.) uses or expects them. If a script (i.e. a file containing series of tasks to be performed) is not correctly structured (i.e. wrong syntax), the computer program will not be able to parse it. Parsing roughly can be understood as going over an input with the end goal of understanding what is meant.</p>

<p>Dockerfiles use simple, clean, and clear syntax which makes them strikingly easy to create and use. They are designed to be self explanatory, especially because they allow commenting just like a good and properly written application source-code. </p>

<h3 id="dockerfile-syntax-example">Dockerfile Syntax Example</h3>

<hr />

<p>Dockerfile syntax consists of two kind of main line blocks: comments and commands + arguments.</p>
<pre class="code-pre "><code langs=""># Line blocks used for commenting
command argument argument ..
</code></pre>
<blockquote>
<p><strong>A Simple Example:</strong></p>
</blockquote>
<pre class="code-pre "><code langs=""># Print "Hello docker!"
RUN echo "Hello docker!"
</code></pre>
<h2 id="dockerfile-commands-instructions">Dockerfile Commands (Instructions)</h2>

<hr />

<p>Currently there are about a dozen different set of commands which Dockerfiles can contain to have docker build an image. In this section, we will go over all of them, individually, before working on a Dockerfile example.</p>

<p><strong>Note:</strong> As explained in the previous section (Dockerfile Syntax), all these commands are to be listed (i.e. written) successively, inside a single plain text file (i.e. Dockerfile), in the order you would like them performed (i.e. executed) by the docker daemon to build an image. However, some of these commands (e.g. MAINTAINER) can be placed anywhere you seem fit (but always after FROM command), as they do not constitute of any execution but rather <em>value of a definition</em> (i.e. just some additional information).</p>

<h3 id="add">ADD</h3>

<hr />

<p>The ADD command gets two arguments: a source and a destination. It basically copies the files from the source on the host into the container's own filesystem at the set destination. If, however, the source is a URL (e.g. http://github.com/user/file/), then the contents of the URL are downloaded and placed at the destination.</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: ADD [source directory or URL] [destination directory]
ADD /my_app_folder /my_app_folder
</code></pre>
<h3 id="cmd">CMD</h3>

<hr />

<p>The command CMD, similarly to RUN, can be used for executing a specific command. However, unlike RUN it is not executed during build, but when a container is instantiated using the image being built.  Therefore, it should be considered as an initial, default command that gets executed (i.e. run) with the creation of containers based on the image.</p>

<p><strong>To clarify:</strong> an example for CMD would be running an application upon creation of a container which is already installed using RUN (e.g. RUN apt-get install …) inside the image. This default application execution command that is set with CMD becomes the default and replaces any command which is passed during the creation. </p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage 1: CMD application "argument", "argument", ..
CMD "echo" "Hello docker!"
</code></pre>
<h3 id="entrypoint">ENTRYPOINT</h3>

<hr />

<p>ENTRYPOINT argument sets the concrete default application that is used every time a container is created using the image. For example, if you have installed a specific application inside an image and you will use this image to only run that application, you can state it with ENTRYPOINT and whenever a container is created from that image, your application will be the target.</p>

<p>If you couple ENTRYPOINT with CMD, you can remove "application" from CMD and just leave "arguments" which will be passed to the ENTRYPOINT.</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: ENTRYPOINT application "argument", "argument", ..
# Remember: arguments are optional. They can be provided by CMD
#           or during the creation of a container. 
ENTRYPOINT echo

# Usage example with CMD:
# Arguments set with CMD can be overridden during *run*
CMD "Hello docker!"
ENTRYPOINT echo  
</code></pre>
<h3 id="env">ENV</h3>

<hr />

<p>The ENV command is used to set the environment variables (one or more). These variables consist of “key = value” pairs which can be accessed within the container by scripts and applications alike. This functionality of docker offers an enormous amount of flexibility for running programs.</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: ENV key value
ENV SERVER_WORKS 4
</code></pre>
<h3 id="expose">EXPOSE</h3>

<hr />

<p>The EXPOSE command is used to associate a specified port to enable networking between the running process inside the container and the outside world (i.e. the host).</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: EXPOSE [port]
EXPOSE 8080
</code></pre>
<blockquote>
<p>To learn about ports, check out this document on <a href="http://docs.docker.io/en/latest/use/port_redirection">port redirection</a>.</p>
</blockquote>

<h3 id="from">FROM</h3>

<hr />

<p>FROM directive is probably the most crucial amongst all others for Dockerfiles. It defines the base image to use to start the build process. It can be any image, including the ones you have created previously. If a FROM image is not found on the host, docker will try to find it (and download) from the <strong>docker image index</strong>. It needs to be the first command declared inside a Dockerfile.</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: FROM [image name]
FROM ubuntu
</code></pre>
<h3 id="maintainer">MAINTAINER</h3>

<hr />

<p>One of the commands that can be set anywhere in the file - although it would be better if it was declared on top - is MAINTAINER. This non-executing command declares the author, hence setting the author field of the images. It should come nonetheless after FROM. </p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: MAINTAINER [name]
MAINTAINER authors_name
</code></pre>
<h3 id="run">RUN</h3>

<hr />

<p>The RUN command is the central executing directive for Dockerfiles. It takes a command as its argument and runs it to form the image. Unlike CMD, it actually <strong>is</strong> used to build the image (forming another layer on top of the previous one which is committed).</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: RUN [command]
RUN aptitude install -y riak
</code></pre>
<h3 id="user">USER</h3>

<hr />

<p>The USER directive is used to set the UID (or username) which is to run the container based on the image being built.</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: USER [UID]
USER 751
</code></pre>
<h3 id="volume">VOLUME</h3>

<hr />

<p>The VOLUME command is used to enable access from your container to a directory on the host machine (i.e. mounting it). </p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: VOLUME ["/dir_1", "/dir_2" ..]
VOLUME ["/my_files"]
</code></pre>
<h3 id="workdir">WORKDIR</h3>

<hr />

<p>The WORKDIR directive is used to set where the command defined with CMD is to be executed.</p>

<p>Example:</p>
<pre class="code-pre "><code langs=""># Usage: WORKDIR /path
WORKDIR ~/
</code></pre>
<h2 id="how-to-use-dockerfiles">How to Use Dockerfiles</h2>

<hr />

<p>Using the Dockerfiles is as simple as having the docker daemon run one. The output after executing the script will be the ID of the new docker image.</p>

<p>Usage:</p>
<pre class="code-pre "><code langs=""># Build an image using the Dockerfile at current location
# Example: sudo docker build -t [name] .
sudo docker build -t my_mongodb .    
</code></pre>
<h2 id="dockerfile-example-creating-an-image-to-install-mongodb">Dockerfile Example: Creating an Image to Install MongoDB</h2>

<hr />

<p>In this final section for Dockerfiles, we will create a Dockerfile document and populate it step-by-step with the end result of having a Dockerfile, which can be used to create a docker image to run MongoDB containers.</p>

<p><strong>Note:</strong> After starting to edit the Dockerfile, all the content and arguments from the sections below are to be written (appended) inside of it successively, following our example and explanations from the <strong>Docker Syntax</strong> section. You can see what the end result will look like at the latest section of this walkthrough.</p>

<h3 id="creating-the-empty-dockerfile">Creating the Empty Dockerfile</h3>

<hr />

<p>Using the nano text editor, let's start editing our Dockerfile.</p>
<pre class="code-pre "><code langs="">sudo nano Dockerfile
</code></pre>
<h3 id="defining-our-file-and-its-purpose">Defining Our File and Its Purpose</h3>

<hr />

<p>Albeit optional, it is always a good practice to let yourself and everybody figure out (when necessary) what this file is and what it is intended to do. For this, we will begin our Dockerfile with fancy comments (i#) to describe it - <em>and have it like cool kids</em>.</p>
<pre class="code-pre "><code langs="">############################################################
# Dockerfile to build MongoDB container images
# Based on Ubuntu
############################################################
</code></pre>
<h3 id="setting-the-base-image-to-use">Setting The Base Image to Use</h3>

<hr />
<pre class="code-pre "><code langs=""># Set the base image to Ubuntu
FROM ubuntu
</code></pre>
<h3 id="defining-the-maintainer-author">Defining The Maintainer (Author)</h3>

<hr />
<pre class="code-pre "><code langs=""># File Author / Maintainer
MAINTAINER Example McAuthor
</code></pre>
<h3 id="updating-the-application-repository-list">Updating The Application Repository List</h3>

<hr />

<p><strong>Note:</strong> This step is not necessary, given that we are not using the repository right afterwards. However, it can be considered good practice.</p>
<pre class="code-pre "><code langs=""># Update the repository sources list
RUN apt-get update
</code></pre>
<h3 id="setting-arguments-and-commands-for-downloading-mongodb">Setting Arguments and Commands for Downloading MongoDB</h3>

<hr />
<pre class="code-pre "><code langs="">################## BEGIN INSTALLATION ######################
# Install MongoDB Following the Instructions at MongoDB Docs
# Ref: http://docs.mongodb.org/manual/tutorial/install-mongodb-on-ubuntu/

# Add the package verification key
RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10

# Add MongoDB to the repository sources list
RUN echo 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' | tee /etc/apt/sources.list.d/mongodb.list

# Update the repository sources list once more
RUN apt-get update

# Install MongoDB package (.deb)
RUN apt-get install -y mongodb-10gen

# Create the default data directory
RUN mkdir -p /data/db

##################### INSTALLATION END #####################
</code></pre>
<h3 id="setting-the-default-port-for-mongodb">Setting The Default Port For MongoDB</h3>

<hr />
<pre class="code-pre "><code langs=""># Expose the default port
EXPOSE 27017

# Default port to execute the entrypoint (MongoDB)
CMD ["--port 27017"]

# Set default container command
ENTRYPOINT usr/bin/mongod
</code></pre>
<h3 id="saving-the-dockerfile">Saving The Dockerfile</h3>

<hr />

<p>After you have appended everything to the file, it is time to save and exit. Press CTRL+X and then Y to confirm and save the Dockerfile.</p>

<blockquote>
<p>This is what the final file should look like:</p>
</blockquote>
<pre class="code-pre "><code langs="">############################################################
# Dockerfile to build MongoDB container images
# Based on Ubuntu
############################################################

# Set the base image to Ubuntu
FROM ubuntu

# File Author / Maintainer
MAINTAINER Example McAuthor

# Update the repository sources list
RUN apt-get update

################## BEGIN INSTALLATION ######################
# Install MongoDB Following the Instructions at MongoDB Docs
# Ref: http://docs.mongodb.org/manual/tutorial/install-mongodb-on-ubuntu/

# Add the package verification key
RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10

# Add MongoDB to the repository sources list
RUN echo 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' | tee /etc/apt/sources.list.d/mongodb.list

# Update the repository sources list once more
RUN apt-get update

# Install MongoDB package (.deb)
RUN apt-get install -y mongodb-10gen

# Create the default data directory
RUN mkdir -p /data/db

##################### INSTALLATION END #####################

# Expose the default port
EXPOSE 27017

# Default port to execute the entrypoint (MongoDB)
CMD ["--port 27017"]

# Set default container command
ENTRYPOINT usr/bin/mongod
</code></pre>
<h3 id="building-our-first-image">Building Our First Image</h3>

<hr />

<p>Using the explanations from before, we are ready to create our first MongoDB image with docker!</p>
<pre class="code-pre "><code langs="">sudo docker build -t my_mongodb .
</code></pre>
<p><strong>Note:</strong> The <strong>-t [name]</strong> flag here is used to tag the image. To learn more about what else you can do during build, run <code>sudo docker build --help</code>.</p>

<h3 id="running-a-mongodb-instance">Running A MongoDB Instance</h3>

<hr />

<p>Using the image we have build, we can now proceed to the final step: creating a container running a MongoDB instance inside, using a name of our choice (if desired with <strong>-name [name]</strong>).</p>
<pre class="code-pre "><code langs="">sudo docker run -name my_first_mdb_instance -i -t my_mongodb
</code></pre>
<p><strong>Note:</strong> If a name is not set, we will need to deal with complex, alphanumeric IDs which can be obtained by listing all the containers using <code>sudo docker ps -l</code>.</p>

<p><strong>Note:</strong> To detach yourself from the container, use the escape sequence CTRL+P followed by CTRL+Q.</p>

<p>Enjoy!</p>

<div class="author">Submitted by: <a href="https://twitter.com/ostezer">O.S. Tezer</a></div>

    