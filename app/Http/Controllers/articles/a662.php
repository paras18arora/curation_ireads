<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/Puppet_twitter.png?1458317469/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In the modern world of cloud computing, configuration management is a crucial step. <em>Configuration management tools</em> allow you to reliably deploy configurations to your servers. One of the more mature configuration management tools in this space is <a href="https://indiareads/community/tutorials/getting-started-with-puppet-code-manifests-and-modules">Puppet</a>.</p>

<p>In a typical Puppet environment, a user writes Puppet modules on their workstation, pushes the modules to a version control server (e.g. Git), then pulls those modules down to a Puppet master. A server running the Puppet client periodically connects to the Puppet master to see if anything has changed, and applies the changes if so.</p>

<p>This scenario works just fine until you have to start scaling up how many servers are checking in or the modules become fairly complex. At that point you have two options: cluster your Puppet Master to handle the load (which will likely require you to buy the commercial version of Puppet), or just drop the Puppet master altogether. This article will look into the second option.</p>

<p>A masterless Puppet setup requires a copy of all Puppet modules to be copied to each node via Git and then have Puppet apply the changes locally. The disadvantage with this method is that each server downloads all of the modules, then applies what is relevant, so it's not the best choice for e.g. setups with sensitive information. However, running without a Puppet master gives you a lot of flexibility and works great without having to scale your infrastructure.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>If you are new to Puppet, then you may want to pause here to read <a href="https://indiareads/community/tutorials/getting-started-with-puppet-code-manifests-and-modules">this article on Puppet</a> first, as this tutorial assumes a working knowledge of the tool. If you're new to Git, you can check out <a href="https://indiareads/community/tutorial_series/introduction-to-git-installation-usage-and-branches">this introduction to Git series</a>, too.</p>

<p>In this tutorial, we'll be working with two Droplets: one running as a Git server, and the other that we'll be applying changes to via Puppet. We'll refer to the IP addresses of these Droplets with <code>your_git_server_ip</code> and <code>your_puppet_server_ip</code> respectively.</p>

<p>So, to follow this tutorial, you will need:</p>

<ul>
<li>One Ubuntu 14.04 Droplet with a <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo non-root user</a> and <a href="https://indiareads/community/tutorials/how-to-configure-ssh-key-based-authentication-on-a-linux-server">SSH keys added</a>.</li>
<li>Another Ubuntu 14.04 Dropet with SSH keys added and Git Labs installed.</li>
</ul>

<p>The easiest way to set up Git Labs is to use the one click image: on the Droplet creation page under <strong>Select Image</strong>, click the <strong>Applications</strong> tab, then click <strong>GitLab 7.10.0 CE on 14.04</strong>. You can also follow <a href="https://indiareads/community/tutorials/how-to-set-up-gitlab-as-your-very-own-private-github-clone">this tutorial</a> to set up Git Labs manually.</p>

<h2 id="step-1-—-creating-a-git-repository">Step 1 — Creating a Git Repository</h2>

<p>The first step is to create a repository where all of our Puppet modules and manifests will be stored.</p>

<p>First, open the Git Labs UI by going to <code>http://<span class="highlight">your_git_server_ip</span></code> in your favorite browser. Create an account by filling in the details on the right under <strong>New user? Create an account</strong> and pressing the green <strong>Sign up</strong> button. You'll receive an account activation email, and after activating your account, you'll be able to sign in on the main page.</p>

<p>Click on the green <strong>+ New Project</strong> button on the main page. Enter "puppet" for the <strong>Project path</strong>, and click <strong>Create project</strong>. Enter "puppet" in the <strong>Project path</strong> field, and choose <strong>Public</strong> for the <strong>Visibility Level</strong>, then click the green <strong>Create Project</strong> button.</p>

<p>Make sure you copy the SSH URL, which you'll see toward the top of the project screen, as we'll need it in a later step. It'll look something like <code>git@<span class="highlight">your_git_server_ip</span>:<span class="highlight">username</span>/puppet.git</code>.</p>

<h2 id="step-2-—-adding-an-ssh-key-to-git-labs">Step 2 — Adding an SSH Key to Git Labs</h2>

<p>In this step, we will create an SSH key on the Puppet server, then add that key to the Git Labs server.</p>

<p>Log in to the Puppet server as <strong>root</strong>. (Because Puppet's files will be owned by root, we need to have rights to setup the initial Git repo in the Puppet folder.)</p>

<p>Create an SSH key for the root user. Make sure not to enter a passphrase because this key will be used by scripts, not a user.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">ssh-keygen -t rsa
</li></ul></code></pre>
<p>Next, display your public key with the following command.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">cat ~/.ssh/id_rsa.pub
</li></ul></code></pre>
<p>Copy this key. It will look something like <code>ssh-rsa long_alphanumeric_string root@<span class="highlight">hostname</span></code>.</p>

<p>Now, on your Git Labs Dashboard page, click on the <strong>Profile settings</strong> icon on the top bar, second from the right. In the left menu, click <strong>SSH Keys</strong>, then click the green <strong>Add an SSH Key</strong> button. In the <strong>Title</strong>, field add a description of the key (like "Root Puppet Key"), and paste your public key into the <strong>Key</strong> field. Finally, click <strong>Add key</strong>.</p>

<h2 id="step-3-—-installing-puppet-and-git">Step 3 — Installing Puppet and Git</h2>

<p>In this step, we will install Puppet and Git.</p>

<p>On the Puppet server, first download the Puppet package for Ubuntu 14.04.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">wget http://apt.puppetlabs.com/puppetlabs-release-trusty.deb
</li></ul></code></pre>
<p>Install the package.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">dpkg -i /tmp/puppetlabs-release-trusty.deb
</li></ul></code></pre>
<p>Update your system's package list.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">apt-get update
</li></ul></code></pre>
<p>Finally, install Puppet and git.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">apt-get install puppet git-core
</li></ul></code></pre>
<p>At this point, you should configure your Git environment by following the instructions in <a href="https://indiareads/community/tutorials/how-to-install-git-on-ubuntu-14-04#how-to-set-up-git">this tutorial</a>.</p>

<h2 id="step-4-—-pushing-the-initial-puppet-configuration">Step 4 — Pushing the Initial Puppet Configuration</h2>

<p>With Puppet and Git installed, we are ready to do our initial push to our Puppet repository.</p>

<p>First, move to the <code>/etc/puppet</code> directory, where the configuration files live.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">cd /etc/puppet
</li></ul></code></pre>
<p>Initialize a git repository here.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">git init
</li></ul></code></pre>
<p>Add everything in the current directory.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">git add .
</li></ul></code></pre>
<p>Commit these changes with a descriptive comment.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">git commit -m "<span class="highlight">Initial commit of Puppet files</span>"
</li></ul></code></pre>
<p>Add the Git project we created earlier as origin using the SSH URL you copied in Step 1.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">git remote add origin git@<span class="highlight">your_server_ip</span>:<span class="highlight">username</span>/puppet.git
</li></ul></code></pre>
<p>And finally, push the changes.</p>
<pre class="code-pre super_user"><code langs=""><ul class="prefixed"><li class="line" prefix="#">git push -u origin master
</li></ul></code></pre>
<h2 id="step-5-—-cleaning-up-puppet-39-s-configuration">Step 5 — Cleaning Up Puppet's Configuration</h2>

<p>Now that Puppet is installed, we can put everything together. At this point, you can log out as root and instead log in as the sudo non-root user you created during the prerequisites. It isn't good practice to operate as the root user unless absolutely necessary.</p>

<p>To get the foundation in place, we need to make a couple of changes. First, we are going to clean up the <code>/etc/puppet/puppet.conf</code> file. Using your favorite editor (vim, nano, etc.) edit <code>/etc/puppet/puppet.conf</code> with the following changes.</p>

<p>Let's start by making a few changes to the <code>/etc/puppet/puppet.conf</code> file for our specific setup. Open the file using nano or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/puppet/puppet.conf
</li></ul></code></pre>
<p>The file will look like this:</p>
<div class="code-label " title="Original /etc/puppet/puppet.conf">Original /etc/puppet/puppet.conf</div><pre class="code-pre "><code langs="">
[main]
logdir=/var/log/puppet
vardir=/var/lib/puppet
ssldir=/var/lib/puppet/ssl
rundir=/var/run/puppet
factpath=$vardir/lib/facter
templatedir=$confdir/templates

[master]
# These are needed when the puppetmaster is run by passenger
# and can safely be removed if webrick is used.
ssl_client_header = SSL_CLIENT_S_DN 
ssl_client_verify_header = SSL_CLIENT_VERIFY
</code></pre>
<p>First, remove everything from the <code>[master]</code> line down, as we aren't running a Puppet master. Also delete the last line in the <code>[main]</code> section which begins with <code>templatedir</code>, as this is deprecated. Finally, change the line which reads <code>factpath=$vardir/lib/facter</code> to <code>factpath=$confdir/facter</code> instead. <code>$confdir</code> is equivalent to <code>/etc/puppet/</code>, i.e. our Puppet repository.</p>

<p>Here is what your <code>puppet.conf</code> should look like once you're finished with the above changes.</p>
<div class="code-label " title="Modified /etc/puppet/puppet.conf">Modified /etc/puppet/puppet.conf</div><pre class="code-pre "><code langs="">
[main]
logdir=/var/log/puppet
vardir=/var/lib/puppet
ssldir=/var/lib/puppet/ssl
rundir=/var/run/puppet
factpath=$confdir/facter
</code></pre>
<h2 id="step-6-—-adding-a-puppet-module">Step 6 — Adding a Puppet Module</h2>

<p>Now Puppet is set up, but it's not doing any work. The way Puppet works is by looking at files called manifests that define what it should do, so in this step, we'll create a useful module for Puppet to run.</p>

<p>Our first module, which we will call cron-puppet, will deploy Puppet via Git. It'll install a Git hook that will run Puppet after a successful merge (e.g. git pull), and it'll install a cron job to perform a <code>git pull</code> every 30 minutes.</p>

<p>First, move into the Puppet modules directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/puppet/modules
</li></ul></code></pre>
<p>Next, make a <code>cron-puppet</code> directory containing <code>manifests</code> and <code>files</code> directories.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir -p cron-puppet/manifests cron-puppet/files
</li></ul></code></pre>
<p>Create and open a file called <code>init.pp</code> in the <code>manifests</code> directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano cron-puppet/manifests/init.pp
</li></ul></code></pre>
<p>Copy the following code into <code>init.pp</code>. This is what tells Puppet to pull from Git every half hour.</p>
<div class="code-label " title="init.pp">init.pp</div><pre class="code-pre "><code langs="">
class cron-puppet {
    file { 'post-hook':
        ensure  => file,
        path    => '/etc/puppet/.git/hooks/post-merge',
        source  => 'puppet:///modules/cron-puppet/post-merge',
        mode    => 0755,
        owner   => root,
        group   => root,
    }
    cron { 'puppet-apply':
        ensure  => present,
        command => "cd /etc/puppet ; /usr/bin/git pull",
        user    => root,
        minute  => '*/30',
        require => File['post-hook'],
    }
}
</code></pre>
<p>Save and close the file, then open another file called <code>post-merge</code> in the <code>files</code> directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano cron-puppet/files/post-merge
</li></ul></code></pre>
<p>Copy the following bash script into <code>post-merge</code>. This bash script will run after a successful Git merge, and logs the result of the run.</p>
<div class="code-label " title="post-merge">post-merge</div><pre class="code-pre "><code class="code-highlight language-bash">
#!/bin/bash -e
## Run Puppet locally using puppet apply
/usr/bin/puppet apply /etc/puppet/manifests/site.pp

## Log status of the Puppet run
if [ $? -eq 0 ]
then
    /usr/bin/logger -i "Puppet has run successfully" -t "puppet-run"
    exit 0
else
    /usr/bin/logger -i "Puppet has ran into an error, please run Puppet manually" -t "puppet-run"
    exit 1
fi
</code></pre>
<p>Save and close this file</p>

<p>Finally, we have to tell Puppet to run this module by creating a global manifest, which is canonically found at <code>/etc/puppet/manifests/site.pp</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/puppet/manifests/site.pp
</li></ul></code></pre>
<p>Paste the following into <code>site.pp</code>. This creates a node classification called 'default'. Whatever is included in the 'default' node will be run on every server. Here, we tell it to run our <code>cron-puppet</code> module.</p>
<div class="code-label " title="site.pp">site.pp</div><pre class="code-pre "><code langs="">
node default {
    include cron-puppet
}
</code></pre>
<p>Save and close the file. Now, let's make sure our module works by running it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo puppet apply /etc/puppet/manifests/site.pp
</li></ul></code></pre>
<p>After a successful run you should see some output ending with a line like this.</p>
<pre class="code-pre "><code langs="">...

Notice: Finished catalog run in 0.18 seconds
</code></pre>
<p>Finally, let's commit our changes to the Git repository. First, log in as the root user, because that is the user with SSH key access to the repository.</p>

<p>Next, change to the <code>/etc/puppet</code> directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/puppet
</li></ul></code></pre>
<p>Add everything in that directory to the commit.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git add .
</li></ul></code></pre>
<p>Commit the changes with a descriptive message.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git commit -m "<span class="highlight">Added the cron-puppet module</span>"
</li></ul></code></pre>
<p>Finally, push the changes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git push -u origin master
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>To add more servers, simply follow step 3 above to install Puppet and Git on the new server, then clone the Git repository to <code>/etc/puppet</code> and apply the <code>site.pp</code> manifest.</p>

<p>You can even automate this installation by using <a href="https://indiareads/community/tutorials/an-introduction-to-droplet-metadata">user data</a> when you create a Droplet. Make sure you use an SSH key when you create the Droplet, and have that SSH key added to your GitLab server. Then just tick the <strong>Enable User Data</strong> checkbox on the Droplet creation screen and enter the following bash script, replacing the variables highlighted in red with your own.</p>
<pre class="code-pre "><code class="code-highlight language-bash">#!/bin/bash -e

## Install Git and Puppet
wget -O /tmp/puppetlabs.deb http://apt.puppetlabs.com/puppetlabs-release-`lsb_release -cs`.deb
dpkg -i /tmp/puppetlabs.deb
apt-get update
apt-get -y install git-core puppet

# Clone the 'puppet' repo
cd /etc
mv puppet/ puppet-bak
git clone http://<span class="highlight">your_git_server_ip</span>/<span class="highlight">username</span>/puppet.git /etc/puppet

# Run Puppet initially to set up the auto-deploy mechanism
puppet apply /etc/puppet/manifests/site.pp
</code></pre>
<p>That's all! You now have a masterless Puppet system, and can spin up any number of additional servers without even having to log in to them.</p>

    