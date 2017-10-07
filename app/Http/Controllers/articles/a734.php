<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/021314sculpin_twitter.png?1426699644/> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>Sculpin is a PHP based static site generator. It converts Twig or HTML templates together with content written in markdown into an easily deployable static site. </p>

<p>In this tutorial, we will install Sculpin on our VPS running Ubuntu 12.04 and get started using it. We will see how to start a project from scratch, how to generate the static files, and how to use its internal web server to deliver the files to the browser. Additionally, I'll show you how you can get started with a pre-made blogging site built in Sculpin and add new blog posts to it.</p>

<h2 id="installation">Installation</h2>

<hr />

<p>The first thing we need to do is take care of some of the requirements we will need later. So run the following commands to install them. If you already have any of them, you can just remove them from the command.</p>
<pre class="code-pre "><code langs="">apt-get update
apt-get install php5-common php5-cli git-core php5-curl
</code></pre>
<p>Then we download the Sculpin PHP executable (the .phar file):</p>
<pre class="code-pre "><code langs="">curl -O https://download.sculpin.io/sculpin.phar
</code></pre>
<p>Next, we need to make it executable:</p>
<pre class="code-pre "><code langs="">chmod +x sculpin.phar
</code></pre>
<p>Now in order to run Sculpin from any folder on our virtual server, let's move it into the /bin folder:</p>
<pre class="code-pre "><code langs="">mv sculpin.phar ~/bin/sculpin
</code></pre>
<p>If you don't yet have the /bin folder in your user's root folder, go ahead and create it before. And finally, let's add it to the bash. Open the .bashrc file:</p>
<pre class="code-pre "><code langs="">nano ~/.bashrc
</code></pre>
<p>And paste in the following line:</p>
<pre class="code-pre "><code langs="">PATH=$PATH:$HOME/bin
</code></pre>
<p>Then run the following command to source the <code>.bashrc</code> file and make sure your changes stick:</p>
<pre class="code-pre "><code langs="">source ~/.bashrc
</code></pre>
<p>And that should do it. To test if the command works, run just the sculpin command from any folder (<code>sculpin</code>) and you should get the Sculpin help in the command terminal.</p>

<h2 id="your-first-sculpin-site">Your first Sculpin site</h2>

<hr />

<p>One thing about Sculpin is that it comes with a built-in webserver capable of serving its pages to the web. To see this in action, let's create our simple site in our user's root folder and not the Apache root directory. Let's start with our first project folder:</p>
<pre class="code-pre "><code langs="">mkdir mysite
cd mysite
mkdir source
cd source
</code></pre>
<p>Now we have the project folder (mysite/) and another folder inside (source/). The latter is where you will put your site's content. So let's create a simple <strong>Hello World</strong> thing in there. Create an index.md file in the /source folder and paste the following:</p>
<pre class="code-pre "><code langs="">---
---

# Hello World
</code></pre>
<p>The format with which we are writing the <strong>Hello World</strong> is called markdown and the lines above are for YAML formatting. For more about markdown syntax, check <a href="http://daringfireball.net/projects/markdown/syntax">this page</a>. Save the file, exit, go back to the mysite/ folder and run the following command:</p>
<pre class="code-pre "><code langs="">sculpin generate --watch --server
</code></pre>
<p>This will generate your development site. Watch for current changes in the content in case you make some updates to the files and create a folder to serve the files through a server port. So now you can visit your site at ip-address:8000. You should see <strong>Hello World</strong> printed on the page in between header tags. To stop the server (as mentioned in the terminal), just run CTRL + C. </p>

<p>You'll notice that inside the mysite/ folder, you now have another folder called output_dev/ in which the equivalent html files are stored. To generate the production files, add the <code>--env=prod</code> tag to the sculpin generate command:</p>
<pre class="code-pre "><code langs="">sculpin generate --watch --server --env=prod
</code></pre>
<p>This will generate the output_prod/ folder and the necessary files inside. You can then sync this folder with your Apache so you can use a proper server to deliver the site pages to the browser. Let's quickly see how to do that. </p>

<p>Assuming that you want the site to be accessible from out of your Apache's default /var/www/ folder (the web server root directory), you can do the following. Navigate to the output_prod/ folder:</p>
<pre class="code-pre "><code langs="">cd output_prod
</code></pre>
<p>And run the following <code>rsync</code> command to synchronize the files from here with the /var/www folder:</p>
<pre class="code-pre "><code langs="">rsync -azv * /var/www
</code></pre>
<p>Now you can access the site straight from going to your VPS IP address (if you haven't changed any virtual host configuration). And just run this command from the same folder whenever you make any changes to your site and generate new html files.</p>

<h2 id="twig-and-layouts">Twig and layouts</h2>

<hr />

<p>Sculpin uses Twig for its layouts, which is a powerful templating system for PHP. There is an introductory article about Twig on <a href="https://indiareads/community/articles/how-to-install-and-get-started-with-twig-on-a-vps">IndiaReads</a>. As we saw, what gets printed out on the page resides in the source/ folder - this is your content written in markdown. Let's now create a layout into which that index.md file content will be injected. </p>

<p>Inside the source/ folder, create a folder called <strong>_views</strong> (the naming is a best practice kind of thing):</p>
<pre class="code-pre "><code langs="">mkdir _views
</code></pre>
<p>Inside this folder, create a file called <strong>main.html</strong>. In here we can declare all the main page HTML we will want + the Twig content block which will render our site content. So for instance, paste inside the following:</p>
<pre class="code-pre "><code langs=""><html>
<head><title>My first Sculpin site</title></head>
<body><div class="content">{% block content %}{% endblock %}</div></body>
</html>
</code></pre>
<p>Now edit the index.md file we created earlier, and instead of this:</p>
<pre class="code-pre "><code langs="">---
---

# Hello World
</code></pre>
<p>Paste this:</p>
<pre class="code-pre "><code langs="">---
layout: main
---

# Hello World
</code></pre>
<p>Now if you run again the sculpin command (you can leave out the watch for now):</p>
<pre class="code-pre "><code langs="">sculpin generate  --server
</code></pre>
<p>You should see that the markdown file you wrote (index.md) is automagically injected in the Twig content block we defined in the main.html template, due to the YAML declaration we made at the top of the file. Neat.</p>

<h2 id="blog-for-testing">Blog for testing</h2>

<hr />

<p>If you want to see more about what you can do with Sculpin, you should get the Sculpin blog skeleton that can help you better understand how it works. You can use git for this:</p>
<pre class="code-pre "><code langs="">cd ~
git clone https://github.com/sculpin/sculpin-blog-skeleton.git blog
cd blog
</code></pre>
<p>Now we need to have Sculpin install the project dependencies using Composer. So just run the following command:</p>
<pre class="code-pre "><code langs="">sculpin install
</code></pre>
<p>Next, you can run the <code>sculpin generate</code> function with the server option and go back to the browser to see the blog site you just created. You can then explore the files that make up the blog and see how they work together. Additionally, you can add a new post to the blog. Go to the _posts/ folder:</p>
<pre class="code-pre "><code langs="">cd source/_posts
</code></pre>
<p>And create a new file:</p>
<pre class="code-pre "><code langs="">nano 2020-02-07-my-post.md
</code></pre>
<p>Paste the following inside and save:</p>
<pre class="code-pre "><code langs="">---
title: My post
---

# Hello world.
</code></pre>
<p>Then go ahead and generate again and check out the blog. You'll see your new post there.</p>

<h3 id="conclusion">Conclusion</h3>

<hr />

<p>Sculpin is an interesting static site generator that uses markdown for quick content formatting and Twig for awesome templating to get you deploying a static site easily. We've seen what a project looks like and how to get started, as well as what an already made blog site built with Sculpin looks like.</p>

<div class="author">Article Submitted by: <a href="http://www.webomelette.com/">Danny</a></div>

    