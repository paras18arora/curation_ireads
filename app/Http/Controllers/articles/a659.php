<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p><a href="https://hexo.io/">Hexo</a> is a static blogging framework built on <a href="https://indiareads/community/tutorials/how-to-set-up-a-node-js-application-for-production-on-ubuntu-14-04">Node.js</a>. Using Hexo, you can publish Markdown documents in the form of blog posts. Blog posts and content are processed and converted into HTML/CSS, which is sourced from the default or custom template theme files (much like other static blogging generators, like Jekyll and Ghost). All of the software in Hexo is modular, so you can install and set up exactly what you need.</p>

<p>This tutorial will set up Hexo with deployment supported by GitHub and Nginx.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 14.04 Droplet with a sudo non-root user, which you can set up by following <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">this initial server setup guide</a>.</p></li>
<li><p>Git installed on your server, which you can do by following the "How To Install Git with Apt" and "How To Set Up Git" sections of <a href="https://indiareads/community/tutorials/how-to-install-git-on-ubuntu-14-04">this Git tutorial</a>.</p></li>
<li><p>Node.js installed on your server, which you can set up by following the "How To Install Using NVM" section of <a href="https://indiareads/community/tutorials/how-to-install-node-js-on-an-ubuntu-14-04-server#how-to-install-using-nvm">this Node.js tutorial</a>.</p></li>
<li><p>Nginx installed on your server, which you can set up by following <a href="https://indiareads/community/tutorials/how-to-install-nginx-on-ubuntu-14-04-lts">this Nginx tutorial</a>.</p></li>
<li><p>An account on <a href="https://github.com/">GitHub</a>, which is a <a href="https://indiareads/community/tutorial_series/introduction-to-git-installation-usage-and-branches">Git</a> repository host.</p></li>
</ul>

<h2 id="step-1-—-installing-and-initializing-hexo">Step 1 — Installing and Initializing Hexo</h2>

<p>This initial section contains everything you need to get Hexo up and running on your server. </p>

<p>First, ensure the system packages are up to date.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update && sudo apt-get upgrade
</li></ul></code></pre>
<p>Several software packages and components make up the Hexo blogging framework. Here, we'll pull down two of the most essential ones using <code>npm</code>, the Node.js package manager.</p>

<p>The first, <code>hexo-cli</code>, is the most important and provides the core Hexo commands.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">npm install hexo-cli -g
</li></ul></code></pre>
<p>The second, <code>hexo-server</code>, is the built-in server which can be used to preview and test your blog before deploying.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">npm install hexo-server -g 
</li></ul></code></pre>
<p>There are many more packages available; these are simply the bare essentials you need to get your Hexo blog up and running. You can browse more packages available as part of the Hexo framework on <a href="http://npmsearch.com/?q=hexo">npm search</a>.</p>

<p>Next, we need to set up the base files for your new blog. Fortunately, Hexo does all the groundwork with a single command. All you need to do is provide a path or folder in which you want the blog config files to reside. </p>

<p>A convenient option is your user's home directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hexo init ~/hexo_blog
</li></ul></code></pre>
<p>Within a second or two you'll get some output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>. . .

INFO  Copying data to ~/hexo_blog
INFO  You are almost done! Don't forget to run 'npm install' before you start blogging with Hexo!

. . .
</code></pre>
<p>Next, move to the directory with your config files.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/hexo_blog
</li></ul></code></pre>
<p>Then run the aforementioned installation command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">npm install
</li></ul></code></pre>
<p>You can ignore any optional dependency warnings from <code>npm</code>. After several seconds of processing time, we'll have our base config files.</p>

<h2 id="step-2-—-setting-up-hexo-39-s-main-configuration-file">Step 2 —  Setting Up Hexo's Main Configuration File</h2>

<p>Let's take a look at base config files in our Hexo directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -l
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>-rw-rw-r--   1 sammy sammy 1483 Jan 11 12:30 _config.yml
drwxrwxr-x 191 sammy sammy 4096 Jan 11 12:31 node_modules
-rw-rw-r--   1 sammy sammy  442 Jan 11 12:30 package.json
drwxrwxr-x   2 sammy sammy 4096 Jan 11 12:30 scaffolds
drwxrwxr-x   3 sammy sammy 4096 Jan 11 12:30 source
drwxrwxr-x   3 sammy sammy 4096 Jan 11 12:30 themes
</code></pre>
<p>Out of all the files present, the <code>_config.yml</code> file is arguably the most important. All core settings are stored here and it is central to the blog. If you need to tweak something in the future, it's likely to be in this file.</p>

<p>We'll set up some basic customization next by going through <code>_config.yml</code> piece by piece. Open <code>_config.yml</code> with <code>nano</code> or your preferred text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano _config.yml 
</li></ul></code></pre>
<p>At the top of the file, you should see a section labeled <strong>Site</strong>:</p>
<div class="code-label " title="Original ~/hexo_blog/_config.yml">Original ~/hexo_blog/_config.yml</div><pre class="code-pre "><code langs="">. . .

# Site
title: Hexo
subtitle:
description:
author: John Doe
language:
timezone:

. . .
</code></pre>
<p>The first four lines are the name of your blog, a suitable subtitle, a description, and the author name. You can choose whatever you like for these options. Note that not all Hexo themes show this data, so it mostly serves as site metadata where relevant.</p>

<p>The next two options are language and time zone. The language option takes <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes?oldformat=true">2-letter ISO-639-1 codes</a> only. The timezone is set to your server's time zone by default, and uses <a href="https://en.wikipedia.org/wiki/List_of_tz_database_time_zones?oldformat=true">"tz database"</a> formatting. If you decide you want to change either of these, make sure they are in these formats. </p>

<p>Here are some example values:</p>
<div class="code-label " title="Example ~/hexo_blog/_config.yml">Example ~/hexo_blog/_config.yml</div><pre class="code-pre "><code langs="">. . .

#Site
title: <span class="highlight">IndiaReads's Hexo Blog</span>  
subtitle: <span class="highlight">Simple Cloud Hosting, Built for Developers.</span>
description: <span class="highlight">Deploy an SSD cloud server in 55 seconds.</span>
author: <span class="highlight">Sammy Shark</span> 
language: <span class="highlight">en</span> 
timezone: <span class="highlight">America/New_York</span>

. . .
</code></pre>
<p>The next section is the <strong>URL</strong> section. Here, we want to change the URL option. Because we don't currently have a domain name for our server, we can enter the IP address instead for the <code>url:</code> setting here.</p>
<div class="code-label " title="~/hexo_blog/_config.yml">~/hexo_blog/_config.yml</div><pre class="code-pre "><code langs="">. . .

# URL
## If your site is put in a subdirectory, set url as 'http://yoursite.com/child' and root as '/child/'
url: http://<span class="highlight">your_server_ip</span>
root: /
permalink: :year/:month/:day/:title/
permalink_defaults:

. . .
</code></pre>
<p>The last option we want to change is <code>default_layout:</code> in the <strong>Writing</strong> section a little further down. This creates new posts as drafts so they must be published before being visible on the blog website. </p>

<p>Set it to <code>draft</code> now like we did below:</p>
<div class="code-label " title="~/hexo_blog/_config.yml">~/hexo_blog/_config.yml</div><pre class="code-pre "><code langs="">. . .

# Writing
new_post_name: :title.md # File name of new posts
default_layout: <span class="highlight">draft</span>
titlecase: false # Transform title into titlecase

. . .
</code></pre>
<p>Save and quit the file for now. We will return to this file briefly for the deployment stages towards the end of the tutorial. </p>

<h2 id="step-3-—-creating-and-publishing-a-new-post">Step 3 — Creating and Publishing a New Post</h2>

<p>The process for creating a post (or draft, like we configured earlier) starts by issuing the following command, where <strong>first-post</strong> is the name of the post you want to make.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hexo new <span class="highlight">first-post</span>
</li></ul></code></pre>
<p>You should see the following output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>INFO  Created: ~/hexo_blog/source/_drafts/first-post.md
</code></pre>
<p>Open the new post for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/hexo_blog/source/_drafts/first-post.md
</li></ul></code></pre>
<p>Every post must have its <a href="https://hexo.io/docs/front-matter.html">front-matter</a> set up. <em>Front-matter</em> is a short block of JSON or YAML that configures settings like the title of your post, the published date, tags, and so on. The end of the front-matter is designated by the first <code>---</code> or <code>;;;</code> marker. After the front-matter, you can write your blog post with Markdown syntax. </p>

<p>Replace the default content in <code>first-post.md</code> with the below example options in the file to start the post. You can customize them if you like.</p>
<div class="code-label " title="Example ~/hexo_blog/source/_drafts/first-post.md">Example ~/hexo_blog/source/_drafts/first-post.md</div><pre class="code-pre "><code langs="">title: IndiaReads's First Post
tags:
  - Test
  - Blog
categories:
  - Hexo
comments: true
date: 2015-12-31 00:00:00
---

## Markdown goes here.

**This is our first post!**
</code></pre>
<p>Save and exit the file.</p>

<p>The Markdown file we just created will be held within <code>~/hexo_blog/source/_drafts</code> until we publish it. Any posts inside the <code>_drafts</code> folder will not be visible to visitors on the website.</p>

<p>Next, publish the post so it <em>will</em> be accessible by visitors.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hexo publish first-post 
</li></ul></code></pre>
<p>This results in:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>INFO  Published: ~/hexo_blog/source/_posts/first-post.md
</code></pre>
<p>The post will now be visible once we begin hosting the blog. </p>

<h2 id="step-4-—-running-the-test-server">Step 4 — Running the Test Server</h2>

<p>Now the previous configuration files are complete, and we have an example post ready. Next, we'll start the test server.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hexo server
</li></ul></code></pre>
<p>It's possible to force the test server to render posts in the <code>_drafts</code> folder. To do this include the <code>-d</code> option when issuing the last command.</p>

<p>Now that we have the test server running, you can view your blog by visiting <code>http://<span class="highlight">your_server_ip</span>:4000/</code> in your favorite browser. You'll see Hexo's pre-defined "Hello World" test post, and the test post we just created.</p>

<p><img src="https://assets.digitalocean.com/articles/hexo/07hIfZs.png" alt="IndiaReads's Hexo Blog Image" /></p>

<p>Exit the test server by pressing <code>CTRL+C</code> in the terminal. </p>

<p>The test server is best used for previewing changes and additions to your blog. Once you are happy with how it looks, it's time to deploy it to the web. </p>

<h2 id="step-5-—-setting-up-git-deployment">Step 5 — Setting Up Git Deployment</h2>

<p>There are a number of different ways to deploy what we've done so far with Hexo. The approach in this tutorial is to use Git to store the static files, hooks to forward them, and then Nginx to host them. However, there is provided support for Heroku, Git, Rsync, OpenShift, FTPSync, and more with extra framework packages.</p>

<p>To proceed, you'll need a Git repository to store the static HTML files Hexo generates. To keep this simple, we will use a public Git repository provided by GitHub. </p>

<p>Create a new repository on <a href="https://github.com/">GitHub</a> named <strong>hexo_static</strong> by following their <a href="https://help.github.com/articles/creating-a-new-repository/">repository creation steps</a>. Make sure to select the "Public" option and tick the <strong>Initialize this repository with a README</strong> checkbox.</p>

<p>After you've created the repository, open the main Hexo configuration file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano _config.yml 
</li></ul></code></pre>
<p>At the bottom of the file, there's a section labeled <strong>Deployment</strong>:</p>
<div class="code-label " title="Original ~/hexo_blog/_config.yml">Original ~/hexo_blog/_config.yml</div><pre class="code-pre "><code langs="">. . .

# Deployment
## Docs: https://hexo.io/docs/deployment.html
deploy:
  type:
</code></pre>
<p>Fill out the options for <code>deploy:</code> as shown below. Note that the <code>repo</code> line should contain the URL to the Git repository you just created, so make sure to replace <code><span class="highlight">your_github_username</span></code> with your own GitHub account username.</p>
<div class="code-label " title="~/hexo_blog/_config.yml">~/hexo_blog/_config.yml</div><pre class="code-pre "><code langs="">deploy:
  type: <span class="highlight">git</span>
  <span class="highlight">repo: https://github.com/your_github_username/hexo_static.git</span>
  <span class="highlight">branch: master</span>
</code></pre>
<p>Save and exit the file.</p>

<p>Because we've chosen to use Git for deployment, we need the Hexo package that sends our static markup to the Git repository.</p>

<p>Use <code>npm</code> to install it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">npm install hexo-deployer-git --save 
</li></ul></code></pre>
<p>You can now test deployment to the <code>hexo_static</code> repository and give it its first Hexo automated commit via:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">hexo generate && hexo deploy
</li></ul></code></pre>
<p>Enter your GitHub authentication details when prompted.</p>

<p>Here is what a successful output looks like (or similar) after using these commands. Minus the file generations and Git insertions:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>To <span class="highlight">https://github.com/</span>username<span class="highlight">/hexo_static.git.</span>
 * [new branch]      master -> master
Branch master set up to track remote branch master from <span class="highlight">https://github.com/</span>username<span class="highlight">/hexo_static.git.</span>
INFO  Deploy done: git
</code></pre>
<h2 id="step-6-—-setting-up-nginx">Step 6 — Setting up Nginx</h2>

<p>We'll use a basic Nginx web server setup to serve the Hexo blog because Nginx serves static content very well, and our blog will only ever contain static files. There are other viable options that work fine too, like GitHub pages or web servers such as Apache, but this choice in particular ensures some efficiency and personal control over the hosting.</p>

<p>First, create the system directories, which we'll tell Nginx to use for hosting.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p /var/www/hexo
</li></ul></code></pre>
<p>Then give your current Ubuntu user ownership of these web server system directories.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chown -R $USER:$USER /var/www/hexo
</li></ul></code></pre>
<p>Update the <a href="https://indiareads/community/tutorials/an-introduction-to-linux-permissions">permissions</a> in accordance with the ownership.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod -R 755 /var/www/hexo
</li></ul></code></pre>
<p>Open up the <code>default</code> Nginx server block for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/nginx/sites-available/default
</li></ul></code></pre>
<p>Ignoring the areas and lines of the file that are commented out, make changes to the active part of the configuration code so the <code>root</code> directive points to the <code>/var/www/hexo</code> directory. </p>
<div class="code-label " title="/etc/nginx/sites-available/default">/etc/nginx/sites-available/default</div><pre class="code-pre "><code langs="">. . .

server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root <span class="highlight">/var/www/hexo</span>;
    index index.html index.htm;

. . .
</code></pre>
<p>Save and exit the file. If in the future you set up a domain name for this server, then come back to this file and replace the <code>server_name</code> entry in the same block with your new domain name. </p>

<p>Lastly, restart the Nginx service for the changes to take effect.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo service nginx restart
</li></ul></code></pre>
<h2 id="step-7-—-creating-git-hooks">Step 7 — Creating Git Hooks</h2>

<p>In this step, we'll link the <strong>hexo_static</strong> repository to another Git repository, so we can send the static HTML files through to the web server directory (when triggered). </p>

<p>First, initialize a new empty Git repository (not on GitHub). This repository's only purpose will be to forward the contents of our <code>hexo_static</code> repository onto the web server directory. </p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git init --bare ~/hexo_bare
</li></ul></code></pre>
<p>Make a new hook file inside the Git generated <code>hooks</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/hexo_bare/hooks/post-receive 
</li></ul></code></pre>
<p>Add the two lines of code below into file. This specifies the Git work tree (which has the source code) and Git directory (which has configuration settings, history, and more).</p>
<div class="code-label " title="~/hexo_bare/hooks/post-receive">~/hexo_bare/hooks/post-receive</div><pre class="code-pre "><code langs="">#!/bin/bash

git --work-tree=/var/www/hexo --git-dir=/home/$USER/hexo_bare checkout -f
</code></pre>
<p>Save and quit the file once completed. </p>

<p>Make this <code>post-receive</code> file executable.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod +x ~/hexo_bare/hooks/post-receive
</li></ul></code></pre>
<p>We must now clone the <code>hexo_static</code> deployment repository, which we created in step 5, to our server. Make sure you replace <code><span class="highlight">username</span></code> in this next command with your own GitHub account username.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone https://github.com/<span class="highlight">username</span>/hexo_static.git ~/hexo_static
</li></ul></code></pre>
<p>Move into the cloned repository.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/hexo_static
</li></ul></code></pre>
<p>Finally, add our bare repository from earlier as a Git remote named <strong>live</strong>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git remote add live ~/hexo_bare
</li></ul></code></pre>
<h2 id="step-8-—-creating-the-deploy-script">Step 8 — Creating the Deploy Script</h2>

<p>A short shell script can be used to start and trigger the entire deploy process we've set up here. This means we won't have to run several Hexo commands individually or trigger the Git hook with multiple commands.</p>

<p>Move back into our original Hexo blog directory, and create a file for the deploy script.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/hexo_blog 
</li><li class="line" prefix="$">nano hexo_git_deploy.sh
</li></ul></code></pre>
<p>Paste the following code into the file:</p>
<div class="code-label " title="hexo_blog/hexo_git_deploy.sh">hexo_blog/hexo_git_deploy.sh</div><pre class="code-pre "><code class="code-highlight language-bash">#!/bin/bash

hexo clean
hexo generate 
hexo deploy

( cd ~/hexo_static ; git pull ; git push live master )
</code></pre>
<p>Save and quit the file.</p>

<p>The script contains three <code>hexo</code> commands:</p>

<ul>
<li><code>clean</code> removes any previously generated static files in the public folder.</li>
<li><code>generate</code> creates the static HTML files from our markdown, inside the public folder. </li>
<li><code>deploy</code> sends the newly generated static files as a commit to the "live" Git repository we defined in <code>_config.yml</code> earlier on. </li>
</ul>

<p>The last line, <code>( cd ~/hexo_static ; git pull ; git push live master )</code>, triggers the Git hook and updates the web server hosting directory with our HTML static files.</p>

<p>Be sure to save and quit the file once it's filled out.</p>

<p>Make the new deploy script executable to complete this step.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">chmod +x hexo_git_deploy.sh
</li></ul></code></pre>
<h2 id="step-9-—-running-the-deploy-script">Step 9 — Running the Deploy Script</h2>

<p>Run the deployment script we created in the previous step to test the overall deploy process.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./hexo_git_deploy.sh
</li></ul></code></pre>
<p>Wait for the commands and processing to complete, entering in any GitHub authentication details in the process. Then, take a look at the files in the <code>/var/www/hexo</code> directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls /var/www/hexo
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>2015  2016  archives  categories  css  fancybox  index.html  js  tags
</code></pre>
<p>Your web server directory should now be populated with the blog's website files, which means accessing the web server through your browser will take you to the blog.</p>

<p>Visit <code>http://<span class="highlight">your_server_ip</span>/</code> in your favorite browser to see your blog live (without using the test server).</p>

<p>To deploy new blog changes in the future, all you need do is re-run the <code>hexo_git_deploy.sh</code> script. Remember to test new posts for errors with the <code>hexo server</code> or <code>hexo server -d</code> commands before deploying.</p>

<h2 id="step-10-—-examining-hexo-39-s-filesystem-optional">Step 10 — Examining Hexo's Filesystem (Optional)</h2>

<p>This section is optional and provides some background on the rest of Hexo's filesystem. None of these files need changing or altering for this tutorial, but it's good to know the general purpose of each one for if you want to make use of them in the future. </p>

<p>The layout of the files and directories looks like this: </p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Hexo files and directories">Hexo files and directories</div>├── _config.yml
├── node_modules
├── package.json
├── scaffolds
├── source
|   └── _posts
└── themes
</code></pre>
<h3 id="node_modules">node_modules</h3>

<p>In this directory, Hexo stores the modules you download via <code>npm</code> for use with your blog. At the end of this tutorial, there will only be the packages we downloaded in step 1.</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>hexo  hexo-generator-archive  hexo-generator-category  hexo-generator-index  hexo-generator-tag  hexo-renderer-ejs  hexo-renderer-marked  hexo-renderer-stylus  hexo-server
</code></pre>
<p>Some of these unfamiliar modules come bundled as part of the core packages. There is usually no real need to change or remove the files in here. </p>

<h3 id="package-json">package.json</h3>

<p>This JSON file contains our Hexo package configurations and versions which Hexo will use for your blog. </p>

<p>If you are ever required to update, downgrade, or remove a package <strong>manually</strong>, it can be done by altering the values in here. Usually, you will only need to do this if a conflict arises within Hexo, which is relatively uncommon.</p>

<h3 id="scaffolds">scaffolds</h3>

<p>When creating new posts, Hexo can base them upon template files in the <code>scaffolds</code> folder.  </p>

<p>You must first create template files and place them here to use them. This feature is optional and only necessary if you'd like repeated layouts for your future Hexo posts.  </p>

<h3 id="source">source</h3>

<p>The posts you publish and want visible to the public are kept in <code>_posts</code>, and once they're generated, the <code>_drafts</code> folder plus any other user created pages live here too.</p>

<p>The vast majority of your blog's Markdown content is placed inside of here by Hexo in one of these subfolders.</p>

<h3 id="themes">themes</h3>

<p>Custom themes, once downloaded, should be kept in here. Most themes have their own <code>_config.yml</code> file to hold their equivalent configuration settings. We stuck with the default theme for the purposes of this guide. </p>

<h2 id="conclusion">Conclusion</h2>

<p>There's a lot more to Hexo than what was covered in this guide, but this is a good head-start for building your new blogging site. The <a href="https://hexo.io/docs/">documentation for Hexo</a> is very concise if you want to learn more. Looking into installing one of the <a href="https://hexo.io/themes/">custom themes</a> available for Hexo is the next step towards developing your blog.</p>

    