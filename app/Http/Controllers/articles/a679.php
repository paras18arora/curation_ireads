<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <p><strong>An article from <a href="https://github.com/progrium/dokku">Dokku</a></strong><br />
<strong>Submitted by <a href="https://github.com/progrium">Jeff Lindsay</a></strong></p>

<p><strong><span class="highlight">Note: The Dokku project has changed significantly since this guide was written.  The instructions below may not reflect the current state of the Dokku project.</span></strong></p>

<h3 id="introduction">Introduction</h3>

<hr />

<p><a href="https://github.com/progrium/dokku">Dokku</a> makes it easy to deploy and manage web applications on your own server in a way that’s very similar to Heroku. Except on IndiaReads, it’s faster, cheaper, and you have more control. Now IndiaReads has a one-click application for creating a Dokku Droplet, making it even easier to have your own private application platform in a matter of minutes.</p>

<p>We’re going to show you how simple it is to get started with step-by-step instructions. By the end, you’ll have a Heroku-style application running on a IndiaReads Droplet, deployed via Git, and a Dokku instance ready for more. </p>

<p>Before we get started, be sure you have a IndiaReads account. If not, go ahead and <a href="https://cloud.digitalocean.com/registrations/new">sign up</a>.</p>

<h2 id="step-1-create-a-dokku-droplet">Step 1: Create a Dokku Droplet</h2>

<hr />

<p>In your IndiaReads control panel, press the <a href="https://cloud.digitalocean.com/droplets/new">Create Droplet</a> button, to be taken to the creation screen.</p>

<p><strong>Droplet Hostname</strong>: The hostname identifies your Droplet. Feel free to call your hostname anything you like. I’m just going to call mine “dokku”.</p>

<p><strong>Select Size</strong>: Depending on how many applications you’ll be deploying, you may want a larger size Droplet. However, for most purposes the 512MB size is enough to get started. You can always change it later.</p>

<p><strong>Select Region</strong>: Choose the region closest to you, or the people that will be accessing your applications.</p>

<p><strong>Select Image</strong>: This is the important part! Under 'Select Image' choose 'Applications' and then select 'Dokku on Ubuntu 13.04'.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku/dokku_image.png" alt="IndiaReads Dokku select image" /></p>

<p>Lastly, you will need to have an SSH key installed for Dokku to accept deployments via Git push. You can set up an SSH key for Dokku in a later step, but it’s easier to just pick one now for your Droplet. If you don’t have any <a href="https://indiareads/community/articles/how-to-set-up-ssh-keys--2">SSH keys set up with IndiaReads</a>, here is a great tutorial.</p>

<p>Now click the 'Create Droplet' button, and we’ll wait while the Droplet boots up.</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_intro/create_droplet.png" alt="IndiaReads Dokku creating droplet" /></p>

<p>Once your droplet is ready, you'll see a screen like this one:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_intro/droplet_ready.png" alt="IndiaReads Dokku droplet ready" /></p>

<p>See the IP address on this screen?  For the next step, you'll want to copy and paste that IP into your browser's address bar to load the Dokku setup page.</p>

<h2 id="step-2-setting-up-dokku">Step 2: Setting up Dokku</h2>

<hr />

<p>Browsing to the IP of your droplet, you should see a screen like this:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_intro/dokku_setup.png" alt="IndiaReads Dokku setup page" /></p>

<h3 id="admin-access">Admin Access</h3>

<hr />

<p>If you used a IndiaReads SSH key with your Droplet, it should already be filled in for your admin SSH public key. Anybody set up with this key can deploy and manage apps on your Dokku. You can add more later, but this one is for you. If there’s no key in that first box, you’ll have to create a keypair and paste in the public key. Remember, here’s <a href="https://indiareads/community/articles/how-to-set-up-ssh-keys--2">a great article</a> if you forgot how.</p>

<h3 id="hostname-configuration">Hostname Configuration</h3>

<hr />

<p>Unfortunately, Dokku doesn’t know what domain you want to use for your apps. Without a domain, you have to access each app with a different port number like the example on the screen above. However, most people want their apps to run as subdomains of a custom domain. For example, <code>myapp.progriumapps.com</code>. </p>

<p>In order to have this setup, you need to do three things:</p>

<ol>
<li><p>Set up 2 DNS records for your domain that point to this IP</p>

<p>a. First, a regular A record (<code>progriumapps.com</code>)<br />
b. Second, a wildcard A record (<code>*.progriumapps.com</code>)</p></li>
<li><p>Change the <strong>Hostname</strong> field on the Dokku Setup screen to your domain</p></li>
<li><p>Check the box that says <strong>Use virtualhost naming</strong></p></li>
</ol>

<p>For me, my Hostname Configuration now looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/dokku_intro/hostname_config.png" alt="IndiaReads Dokku hostname config" /></p>

<p>If you’re unsure how to achieve the DNS step, read this guide on <a href="https://indiareads/community/articles/how-to-set-up-a-host-name-with-digitalocean">how to set up a hostname with IndiaReads</a>.</p>

<p>That’s it! Click Finish Setup. If everything worked, it’ll take you to the Dokku README file on the next step which is to deploy an app. </p>

<p>Once you’re set up, the Dokku Setup page will no longer be available, so don’t worry if you don’t get anything when you browse to your IP again.</p>

<h2 id="step-3-deploy-an-app">Step 3: Deploy an App!</h2>

<hr />

<p>Now for the fun part. Dokku supports many different languages and follows many standard application patterns. If you’ve never built an app that can run on Dokku, you can read <a href="https://indiareads/community/community_tags/dokku">documentation in our library</a> about how to get your application to work with Dokku.</p>

<p>I’m going to deploy a sample application so that you can see how this works. Let’s go with a Node.js sample application. This is <a href="https://github.com/heroku/node-js-sample">available on Github</a> if you want to follow along with it.</p>

<p>After getting a fresh clone of this application using git, we need to add a git remote that points to the Dokku Droplet using the hostname we set up. If you were deploying your own app, you’d also add this remote. From the directory of your application on your computer, you’d run something like this:</p>
<pre class="code-pre "><code langs="">git remote add dokku dokku@progriumapps.com:node-js-sample
</code></pre>
<p>If you weren’t able to set up a domain instead of an IP for the hostname, you’d use the IP instead of the domain. The name after the colon can be anything, but it will be the name used in the subdomain set it up with virtualhost naming.</p>

<p>Now we can deploy our app by pushing to the dokku remote. Be sure to include the branch:</p>
<pre class="code-pre "><code langs="">git push dokku master
</code></pre>
<p>This should result in logs showing your app being built and deployed, finally resulting in a URL you can browse to and see that your application deployed successfully. Yay!</p>

<h2 id="next-steps">Next Steps</h2>

<hr />

<p>Despite being used by many people, Dokku is still quite an early project. You can read more about using Dokku on the <a href="https://github.com/progrium/dokku#dokku">README</a> and soon there will be more in-depth documentation. Until then, there is a great community willing to help out if you run into problems. You can submit an issue on Github or join us in IRC on Freenode in the #dokku channel. </p>

<p>IndiaReads is a perfect host for running Dokku, so hopefully you’ll enjoy playing around with Dokku and using it to manage and deploy your web applications. </p>

    