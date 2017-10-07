<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p>MEAN is a quick start boilerplate for creating applications based on MongoDB, Node.js, <a href="http://expressjs.com/">Express</a>, and <a href="https://angularjs.org/">AngularJS</a>. IndiaReads's MEAN One-Click Application pre-installs the <a href="http://meanjs.org/">Mean.js</a> implementation of the stack. It also comes with the front-end package manager <a href="http://bower.io/">bower</a> and <a href="http://gruntjs.com/">grunt</a>, a tool for automating JavaScript tasks. Together, these tools  provide a solid base to build your web application.</p>

<h2 id="creating-the-mean-droplet">Creating the MEAN Droplet</h2>

<p>You can launch a new MEAN instance by selecting <strong>MEAN on Ubuntu 14.04</strong> from the Applications menu during droplet creation:</p>

<p><img src="https://assets.digitalocean.com/articles/MEAN-1-click/control-panel.png" alt="" /></p>

<p>Once you have created the droplet, connect to it via the web-based console in the IndiaReads control panel or SSH:</p>
<pre class="code-pre "><code langs="">ssh root@<span class="highlight">your.ip.address</span>
</code></pre>
<h2 id="launch-the-project">Launch the project</h2>

<p>A sample MEAN project is installed at <code>/opt/mean</code> To launch it, simply change to that directory and run <code>grunt</code>:</p>
<pre class="code-pre "><code langs="">cd /opt/mean
grunt
</code></pre>
<p>It will now be available at <code>http://<span class="highlight">your.ip.address</span>:3000</code></p>

<p><img src="https://assets.digitalocean.com/articles/MEAN-1-click/mean.png" alt="" /></p>

<h2 id="developing-your-application">Developing your application</h2>

<p>The MEAN sample project provides you with a template on which to build your application. Let's look at the folder structure:</p>
<pre class="code-pre "><code langs="">root@meanjs:/opt/mean# ls
app         config      fig.yml       karma.conf.js  node_modules  Procfile  README.md
bower.json  Dockerfile  gruntfile.js  LICENSE.md     package.json  public    server.js
</code></pre>
<p>The <code>app</code> folder contains the server-side Model View Controller (MVC) files. <a href="http://mongoosejs.com/">Mongoose</a> is used to communicate with the MongoDB instance which is available at <code>127.0.0.1:27017</code></p>

<p>Th <code>config</code> folder contains the configuration files for your application. This includes developer credentials for the social login feature provided by <a href="http://passportjs.org/">Passport</a>. The template supports five social platforms out of the box: Facebook, Twitter, Google, Linkedin, and Github. If your application needs to send mail, you can also configure your <a href="http://www.nodemailer.com/">Nodemailer</a> settings here as well.</p>

<p>The <code>public</code> folder contains all of the static files for your application. You can use <code>bower</code> to install additional front-end libraries for use in your app. For instance if you need JQuery, it can be installed with:</p>
<pre class="code-pre "><code langs="">bower --allow-root install jquery
</code></pre>
<p>It will now be installed to <code>public/lib/jquery/</code></p>

<h2 id="deploying-your-own">Deploying your own</h2>

<p>If you already have an application built on the MEAN stack, there's no need to use the sample project. This image provides you with a pre-installed Node environment with MongoDB running on <code>127.0.0.1:27017</code></p>

<p>Simply remove the existing project and deploy yours as you normally would:</p>
<pre class="code-pre "><code langs="">rm -r /opt/mean
cd /opt/
git clone git@github.com:<span class="highlight">you/your_project.git</span>
cd your_project
npm install
NODE_ENV=production PORT=80 grunt
</code></pre>
<h2 id="further-information">Further information</h2>

<p>For more detailed information on the MEAN stack check out the <a href="http://meanjs.org/docs.html">the docs at Mean.js</a>. To learn more about running MongoDB and Node, check out their respective tags here at IndiaReads:</p>

<ul>
<li><a href="https://indiareads/community/tags/explore/mongodb">MongoDB</a></li>
<li><a href="https://indiareads/community/tags/explore/node-js">Node.js</a></li>
</ul>

    