<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/DeployingDeis-twitter.png?1449155476/> <br> 
      <h3 id="an-article-from-deis">An Article from <a href="http://deis.io">Deis</a></h3>

<h3 id="introduction">Introduction</h3>

<p>Deis is an open source private Platform as a Service (PaaS) that simplifies deploying and managing your applications on your own servers. By leveraging technologies such as Docker and CoreOS, Deis provides a workflow and scaling features that are similar to that of Heroku, on the hosting provider of your choice. Deis supports applications that can run in a Docker container, and Deis can run on any platform that supports CoreOS.</p>

<p>This guide steps you through the new and improved Deis provisioning process using the Deis project's new tool called Rigger.</p>

<h2 id="preview">Preview</h2>

<p>If you don't have much time, this accelerated <a href="https://asciinema.org/">terminal recording</a> (only around a minute long!) shows what we'll be up to in the rest of this article:</p>

<p><a href="https://asciinema.org/a/29033"><img src="https://asciinema.org/a/29033.png" alt="asciicast" /></a></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Rigger is designed to handle its own dependency management, but you will need to setup a few things before provisioning a Deis cluster with it. To follow along with this guide at home you'll need:</p>

<ul>
<li>IndiaReads Personal Access Token to access the IndiaReads API (follow <a href="https://indiareads/community/tutorials/how-to-use-the-digitalocean-api-v2">How To Generate a Personal Access Token</a>) (<strong>the token must be read-write</strong>)</li>
<li>An SSH key pair (follow <a href="https://indiareads/community/tutorials/how-to-use-ssh-keys-with-digitalocean-droplets">How To Use SSH Keys with IndiaReads Droplets</a>)</li>
</ul>

<p>All the command in this tutorial can be run on a local Mac or Linux workstation (OS X >= 10.10 and Debian/Ubuntu were tested). They can also be run on a Droplet, but that is not necessary.</p>

<p>The <code>zip</code>, <code>make</code>, and <code>git</code> utilities need to be installed on whatever workstation you use to provision a Deis cluster with Rigger. </p>

<p>For example, if you are using an Ubuntu system, install them with the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install zip make git
</li></ul></code></pre>
<p>The <code>git</code> utility is used through the article to download Rigger and an example application. The <code>zip</code> and <code>make</code> utilities are used by the Rigger provisioning script.</p>

<p>If you are running Mac OS X, you also need to agree to the Xcode license agreement to use git:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo xcodebuild -license
</li></ul></code></pre>
<p><span class="note"><strong>Note:</strong> This article was written for Deis version 1.12.0.<br /></span></p>

<h2 id="step-1-—-installing-rigger">Step 1 — Installing Rigger</h2>

<p>To install Rigger, first use <code>git</code> to download it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone https://github.com/deis/rigger.git
</li></ul></code></pre>
<p>Change to the directory created:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd rigger
</li></ul></code></pre>
<p>Then, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./rigger
</li></ul></code></pre>
<p>When it first runs, you will see the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Downloading rerun from GitHub...
</code></pre>
<p>At the end of the output, you will see a list of available commands:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Available commands in module, "rigger":
checkout: "checkout the Deis repo with version: $VERSION into directory: $DEIS_ROOT"
configure: "initialize a rigger varsfile to use with future commands"
   [ --advanced]: "configure all the nitty gritty details of the infrastructure and Deis deployment"
   [ --provider <>]: "which cloud provider to use to provision a Deis cluster"
   [ --version <>]: "choose what version of Deis to deploy"
create-registry: "Create a local dev registry"
deploy: "Install and Deploy Deis"
destroy: "destroy all infrastructure created by the provision step"
provision: "provision new infrastructure and deploy Deis to it"
   [ --cleanup]: "destroy cluster after action"
setup-clients: "download and stage deisctl and deis clients for your own use"
shellinit: "show the current sourceable environment variables (useful for eval-ing)"
   [ --file <>]: "use a specific file"
shell-reset: "an eval-able output that unsets variables that have been injected by rigger"
   [ --file <>]: "use a specific file"
test: "run a test suite on the provisioned Deis cluster"
   [ --type <smoke>]: "provide a type of test to run"
upgrade: "Tests upgrade path for Deis"
   [ --to <master>]: "Define version of Deis to upgrade to"
   [ --cleanup]: "destroy cluster after action"
    --upgrade-style <graceful>: "choose the style of upgrade you'd like to perform"
</code></pre>
<h2 id="step-2-—-configuring-the-deis-deployment">Step 2 — Configuring the Deis Deployment</h2>

<p>To configure the Deis deployment to use IndiaReads as the provider and a specific version of Deis, all we need to do is call:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./rigger configure --provider "digitalocean" --version "1.12.0"
</li></ul></code></pre>
<p>Rigger will then ask you a few questions. It will all look like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>-> What IndiaReads token should I use? DO_TOKEN (no default)
<span class="highlight">[enter or paste your IndiaReads token here]</span>
You chose: ******

-> Which private SSH key should be used? SSH_PRIVATE_KEY_FILE [ /Users/sgoings/.ssh/id_dsa ]
1) /Users/sgoings/.ssh/id_dsa
2) ...
#? <span class="highlight">[enter a number]</span>
You chose: 1) /Users/sgoings/.ssh/id_dsa

... output snipped ...

Enter passphrase for /Users/sgoings/.ssh/id_dsa: <span class="highlight">[enter your ssh passphrase]</span>

Rigger has been configured on this system using ${HOME}/.rigger/<span class="highlight"><id></span>/vars
To use the configuration outside of rigger, you can run:

  source "${HOME}/.rigger/<span class="highlight"><id></span>/vars"
</code></pre>
<p>You're all done with the hard part!</p>

<h2 id="step-3-—-profit">Step 3 — Profit!</h2>

<p>Or more accurately: run <code>rigger</code> to provision infrastructure on IndiaReads and then deploy Deis! </p>

<p>All we need to do is execute:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./rigger provision
</li></ul></code></pre>
<span class="warning"><p>
<strong>Warning:</strong> If you are running Rigger on Mac OS X, you might see the following error message:</p>
<pre class="code-pre "><code langs="">Agreeing to the Xcode/iOS license requires admin privileges, please re-run as root via sudo.
</code></pre>
<p>If so, you need to agree to the Xcode license with the <code>sudo xcodebuild -license</code> command as mentioned in the Prerequisite section.<br /></p></span>

<p>The Deis provisioning process with Rigger on IndiaReads takes about 15 minutes and goes like this:</p>

<ol>
<li><a href="https://terraform.io">Terraform</a> is automatically downloaded and installed into <code>${HOME}/.rigger</code> for use by Rigger</li>
<li>Deis clients (<code>deis</code> and <code>deisctl</code>) are downloaded into <code>${HOME}/.rigger/<span class="highlight"><id></span>/bins</code></li>
<li><a href="https://terraform.io">Terraform</a> is used to provision 3 CoreOS Droplets in IndiaReads</li>
<li><code>DEISCTL_TUNNEL</code> is determined by investigating one of the newly provisioned IndiaReads Droplets</li>
<li><a href="http://xip.io">xip.io</a> is used to set up a simple DNS entry point to the cluster</li>
<li><code>deisctl install platform</code> is executed</li>
<li><code>deisctl start platform</code> is executed</li>
</ol>

<span class="warning"><p>
<strong>Warning:</strong> If you see the following errors when provisioning the infrastructure on IndiaReads, make sure your DO Access Token is read-write:</p>
<pre class="code-pre "><code langs="">3 error(s) occurred:

* digitalocean_droplet.deis.1: Error creating droplet: Error creating droplet: API Error: 403 Forbidden
* digitalocean_droplet.deis.0: Error creating droplet: Error creating droplet: API Error: 403 Forbidden
* digitalocean_droplet.deis.2: Error creating droplet: Error creating droplet: API Error: 403 Forbidden
</code></pre>
<p>You can then run the <code>./rigger provision</code> again.<br /></p></span>

<h2 id="step-4-—-playtime">Step 4 — Playtime!</h2>

<p>After you've created your Deis cluster using Rigger, you should deploy an app to it!</p>

<p>First, get back to some free directory space:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ../
</li></ul></code></pre>
<p>Next, grab an example app from the Deis project:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git clone https://github.com/deis/example-nodejs-express.git
</li></ul></code></pre>
<p>Change into the newly created directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd example-nodejs-express
</li></ul></code></pre>
<p>Load all the <code>rigger</code> environment variables into this shell:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">source "${HOME}/.rigger/<span class="highlight"><id></span>/vars"
</li></ul></code></pre>
<p>Then register an administrative account to this Deis cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deis auth:register http://deis.${DEIS_TEST_DOMAIN}
</li></ul></code></pre>
<p>You will be prompted for some information to create the account:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>username: <span class="highlight">[ enter a username ]</span>
password: <span class="highlight">[ enter a password ]</span>
password (confirm): <span class="highlight">[ enter the same password ]</span>
email: <span class="highlight">[ enter an email for this user ]</span>
Registered <span class="highlight"><username></span>
Logged in as <span class="highlight"><username></span>
</code></pre>
<p>Add your public key to the Deis cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deis keys:add
</li></ul></code></pre>
<p>You will see the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Found the following SSH public keys:
1) deiskey.pub deiskey
2) id_dsa.pub sgoings
0) Enter path to pubfile (or use keys:add <key_path>)
Which would you like to use with Deis? <span class="highlight">[ enter number ]</span> 
</code></pre>
<p>You should pick the public key that goes along with the private key you chose during the <code>rigger configure</code> step.</p>

<p>Add a git remote to point at the Deis cluster:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">deis apps:create
</li></ul></code></pre>
<p>You will see the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Creating Application... done, created <span class="highlight">hearty-kingfish</span>
Git remote deis added
remote available at ssh://git@deis.<span class="highlight">${DEIS_TEST_DOMAIN}</span>:2222/<span class="highlight">hearty-kingfish</span>.git
</code></pre>
<p>Now, push it!</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git push deis master
</li></ul></code></pre>
<p>This might take a while. You should eventually see the following at the end of the output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>-----> Launching...
       done, <span class="highlight">hearty-kingfish</span>:v2 deployed to Deis

       http://<span class="highlight">hearty-kingfish.${DEIS_TEST_DOMAIN}</span>

       To learn more, use `deis help` or visit http://deis.io
</code></pre>
<p>Go ahead and load that URL in your browser! (the app is pretty simple, it just prints out: "Powered by Deis")</p>

<h2 id="step-5-—-knocking-it-all-down">Step 5 — Knocking it all down!</h2>

<p>Once you've played around with your fancy new Deis cluster a bit... it'd probably be a good idea to tear it all down, eh? That's simple.</p>

<p>Go back to the <code>rigger</code> directory:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ../rigger
</li></ul></code></pre>
<p>Then, destroy it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">./rigger destroy
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>In this guide, you were able to see where the Deis team is headed to make the lives of developers, operators, and open source contributors easier. Provisioning a <a href="http://deis.io">Deis</a> cluster is now a breeze with Rigger, thanks to the winning combination of <a href="https://terraform.io">Terraform</a> under the hood and lightning fast IndiaReads as the infrastructure provider.</p>

    