<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Git is a <strong>version control</strong> system distributed under the terms of the GNU General Public License v.2 since its release in 2005.</p>

<p>Git is software used primarily for version control which allows for <em>non-linear</em> development of projects, even ones with large amounts of data. Every working directory in Git is a full-fledged repository with <strong>complete history and tracking</strong> independent of network access or a central server.</p>

<p>The advantages of using Git stem from the way the program stores data. Unlike other version control systems, it is best to think of Git's storage process as a set of snapshots of a mini filesystem, primarily on your local disk. Git maximizes efficiency and allows for powerful tools to be built on top of it.</p>

<p>In this tutorial we'll install and configure Git on your Debian 8 Linux server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You'll need the following items for this tutorial:</p>

<ul>
<li>A Droplet running Debian 8</li>
<li>A <a href="https://indiareads/community/tutorials/initial-server-setup-with-debian-8">sudo user</a></li>
</ul>

<h3 id="what-the-lt-gt-red-lt-gt-means">What the <span class="highlight">Red</span> Means</h3>

<p>The majority of code in this tutorial can be copy and pasted as-is! The lines that you will need to customize will be <span class="highlight">red</span> in this tutorial.</p>

<h2 id="step-1-—-installing-git-with-apt">Step 1 — Installing Git with APT</h2>

<p>Before you install Git, make sure that your package lists are updated by executing the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Install Git with <code>apt-get</code> in one command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install git-core
</li></ul></code></pre>
<p>This is the only command you'll need to install Git. The next part is configuring Git.</p>

<p>Using <code>apt-get</code> is the easiest and probably one of the most reliable ways to install Git, because APT takes care of all the software dependencies your system might have.</p>

<p>Now, let us take a look at how to configure Git.</p>

<h2 id="step-2-—-configuring-git">Step 2 — Configuring Git</h2>

<p>Git implements version control using two primary settings:</p>

<ul>
<li>A user name</li>
<li>The user's email</li>
</ul>

<p>This information will be embedded in every commit you make with Git so it can track who is making which commits.</p>

<p>We need to add these two settings in our Git configuration file. This can be done with the help of the <code>git config</code> utility. Here's how:</p>

<p><strong>Set your Git user name:</strong></p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git config --global user.name "<span class="highlight">Sammy Shark</span>"
</li></ul></code></pre>
<p><strong>Set your Git email:</strong></p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git config --global user.email <span class="highlight">sammy@example.com</span>
</li></ul></code></pre>
<p><strong>View all Git settings:</strong></p>

<p>You can view these newly-configured settings (and all the previously existing ones, if any) using the <code>--list</code> parameter in the <code>git config</code> utility.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">git config --list
</li></ul></code></pre>
<p>You should see your user settings:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>user.name=<span class="highlight">Sammy Shark</span>
user.email=<span class="highlight">sammy@example.com</span>
</code></pre>
<p><strong>.gitconfig</strong></p>

<p>If you want to get your hands dirty with the Git configuration file, simply fire up <code>nano</code> (or your favorite text editor) and edit to your heart's content:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/.gitconfig
</li></ul></code></pre>
<p>Here you can manually update your Git settings:</p>
<div class="code-label " title="~/.gitconfig">~/.gitconfig</div><pre class="code-pre "><code langs="">[user]
        name = <span class="highlight">Sammy Shark</span>
        email = <span class="highlight">sammy@example.com</span>
</code></pre>
<p>This is the basic configuration you need to get up and running with Git. </p>

<p>Adding your username and email is not <em>mandatory</em>, but it is recommended. Otherwise, you'll get a message like this when you use Git:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output when Git user name and email are not set">Output when Git user name and email are not set</div>[master 0d9d21d] initial project version
 Committer: root 
Your name and email address were configured automatically based
on your username and hostname. Please check that they are accurate.
You can suppress this message by setting them explicitly:

    git config --global user.name "Your Name"
    git config --global user.email you@example.com

After doing this, you may fix the identity used for this commit with:

    git commit --amend --reset-author
</code></pre>
<p>Congratulations on your very own Git installation.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Here are a few tutorials which you can use to help you take full advantage of Git:</p>

<ul>
<li><a href="https://indiareads/community/articles/how-to-use-git-effectively">How To Use Git Effectively</a></li>
<li><a href="https://indiareads/community/articles/how-to-use-git-branches">How To Use Git Branches</a></li>
</ul>

<p>Happy branching!</p>

    