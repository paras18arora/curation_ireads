<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Version control has become a central requirement for modern software development.  It allows projects to safely track changes and enable reversions, integrity checking, and collaboration among other benefits.  The <code>git</code> version control system, in particular, has seen wide adoption in recent years due to its decentralized architecture and the speed at which it can make and transfer changes between parties.</p>

<p>While the <code>git</code> suite of tools offers many well-implemented features, one of the most useful characteristics is its flexibility.  Through the use of a "hooks" system, git allows developers and administrators to extend functionality by specifying scripts that git will call based on different events and actions.</p>

<p>In this guide, we will explore the idea of git hooks and demonstrate how to implement code that can assist you in automating tasks in your own unique environment.  We will be using an Ubuntu 14.04 server in this guide, but any system that can run git should work in a similar way.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you get started, you must have <code>git</code> installed on your server.  If you are following along on Ubuntu 14.04, you can check out our guide on <a href="https://indiareads/community/tutorials/how-to-install-git-on-ubuntu-14-04">how to install git on Ubuntu 14.04</a> here.</p>

<p>You should be familiar with how to use git in a general sense.  If you need an introduction, the series that the installation is a part of, called <a href="https://indiareads/community/tutorial_series/introduction-to-git-installation-usage-and-branches">Introduction to Git: Installation, Usage, and Branches</a>, is a good place to start.</p>

<p>When you are finished with the above requirements, continue on.</p>

<h2 id="basic-idea-with-git-hooks">Basic Idea with Git Hooks</h2>

<p>Git hooks are a rather simple concept that was implemented to address a need.  When developing software on a shared project, maintaining style guide standards, or when deploying software (all are situations that git is often involved with), there are often repetitive tasks that you will want to do each time an action is taken.</p>

<p>Git hooks are event-based.  When you run certain git commands, the software will check the <code>hooks</code> directory within the git repository to see if there is an associated script to run.</p>

<p>Some scripts run prior to an action taking place, which can be used to ensure code compliance to standards, for sanity checking, or to set up an environment.  Other scripts run after an event in order to deploy code, re-establish correct permissions (something git cannot track very well), and so forth.</p>

<p>Using these abilities, it is possible to enforce policies, ensure consistency, and control your environment, and even handle deployment tasks.</p>

<p>The book <a href="http://git-scm.com/book">Pro Git</a> by Scott Chacon attempts to divide the different types of hooks into categories.  He categorizes them as such:</p>

<ul>
<li>Client-Side Hooks: Hooks that are called and executed on the committer's computer.  These in turn are divided into a few separate categories:

<ul>
<li>Committing-Workflow hooks: Committing hooks are used to dictate actions that should be taken around when a commit is being made.  They are used to run sanity checks, pre-populate commit messages, and verify message details.  You can also use this to provide notifications upon committing.</li>
<li>Email Workflow hooks: This category of hooks encompasses actions that are taken when working with emailed patches.  Projects like the Linux kernel submit and review patches using an email method.  These are in a similar vein as the commit hooks, but can be used by maintainers who are responsible for applying submitted code.</li>
<li>Other: Other client-side hooks include hooks that execute when merging, checking out code, rebasing, rewriting, and cleaning repos.</li>
</ul></li>
<li>Server-Side Hooks: These hooks are executed on servers that are used to receive pushes.  Generally, that would be the main git repo for a project.  Again, Chacon divided these into categories:

<ul>
<li>Pre-receive and post-receive:  These are executed on the server receiving a push to do things like check for project conformance and to deploy after a push.</li>
<li>Update: This is like a pre-receive, but operates on a branch-by-branch basis to execute code prior to each branch being accepted.</li>
</ul></li>
</ul>

<p>These categorizations are helpful for getting a general idea of the events that you can optionally set up a hook for.  But to actually understand how these items work, it is best to experiment and to find out what solutions you are trying to implement.</p>

<p>Certain hooks also take parameters.  This means that when git calls the script for the hook, it will pass in some relevant data that the script can then use to complete tasks. In full, the hooks that are available are:</p>

<table class="pure-table"><thead>
<tr>
<th>Hook Name</th>
<th>Invoked By</th>
<th>Description</th>
<th>Parameters (Number and Description)</th>
</tr>
</thead><tbody>
<tr>
<td>applypatch-msg</td>
<td><code>git am</code></td>
<td>Can edit the commit message file and is often used to verify or actively format a patch's message to a project's standards.  A non-zero exit status aborts the commit.</td>
<td>(1) name of the file containing the proposed commit message</td>
</tr>
<tr>
<td>pre-applypatch</td>
<td><code>git am</code></td>
<td>This is actually called <em>after</em> the patch is applied, but <em>before</em> the changes are committed.  Exiting with a non-zero status will leave the changes in an uncommitted state.  Can be used to check the state of the tree before actually committing the changes.</td>
<td>(none)</td>
</tr>
<tr>
<td>post-applypatch</td>
<td><code>git am</code></td>
<td>This hook is run after the patch is applied and committed.  Because of this, it cannot abort the process, and is mainly used for creating notifications.</td>
<td>(none)</td>
</tr>
<tr>
<td>pre-commit</td>
<td><code>git commit</code></td>
<td>This hook is called before obtaining the proposed commit message.  Exiting with anything other than zero will abort the commit. It is used to check the commit itself (rather than the message).</td>
<td>(none)</td>
</tr>
<tr>
<td>prepare-commit-msg</td>
<td><code>git commit</code></td>
<td>Called after receiving the default commit message, just prior to firing up the commit message editor. A non-zero exit aborts the commit.  This is used to edit the message in a way that cannot be suppressed.</td>
<td>(1 to 3) Name of the file with the commit message, the source of the commit message (<code>message</code>, <code>template</code>, <code>merge</code>, <code>squash</code>, or <code>commit</code>), and the commit SHA-1 (when operating on an existing commit).</td>
</tr>
<tr>
<td>commit-msg</td>
<td><code>git commit</code></td>
<td>Can be used to adjust the message after it has been edited in order to ensure conformity to a standard or to reject based on any criteria.  It can abort the commit if it exits with a non-zero value.</td>
<td>(1) The file that holds the proposed message.</td>
</tr>
<tr>
<td>post-commit</td>
<td><code>git commit</code></td>
<td>Called after the actual commit is made.  Because of this, it cannot disrupt the commit.  It is mainly used to allow notifications.</td>
<td>(none)</td>
</tr>
<tr>
<td>pre-rebase</td>
<td><code>git rebase</code></td>
<td>Called when rebasing a branch.  Mainly used to halt the rebase if it is not desirable.</td>
<td>(1 or 2) The upstream from where it was forked, the branch being rebased (not set when rebasing current)</td>
</tr>
<tr>
<td>post-checkout</td>
<td><code>git checkout</code> and <code>git clone</code></td>
<td>Run when a checkout is called after updating the worktree or after <code>git clone</code>.  It is mainly used to verify conditions, display differences, and configure the environment if necessary.</td>
<td>(3) Ref of the previous HEAD, ref of the new HEAD, flag indicating whether it was a branch checkout (1) or a file checkout (0)</td>
</tr>
<tr>
<td>post-merge</td>
<td><code>git merge</code> or <code>git pull</code></td>
<td>Called after a merge.  Because of this, it cannot abort a merge.  Can be used to save or apply permissions or other kinds of data that git does not handle.</td>
<td>(1) Flag indicating whether the merge was a squash.</td>
</tr>
<tr>
<td>pre-push</td>
<td><code>git push</code></td>
<td>Called prior to a push to a remote.  In addition to the parameters, additional information, separated by a space is passed in through stdin in the form of "<local ref> <local sha1> <remote ref> <remote sha1>".  Parsing the input can get you additional information that you can use to check.  For instance, if the local sha1 is 40 zeros long, the push is a delete and if the remote sha1 is 40 zeros, it is a new branch.  This can be used to do many comparisons of the pushed ref to what is currently there.  A non-zero exit status aborts the push.</td>
<td>(2) Name of the destination remote, location of the destination remote</td>
</tr>
<tr>
<td>pre-receive</td>
<td><code>git-receive-pack</code> on the remote repo</td>
<td>This is called on the remote repo just before updating the pushed refs.  A non-zero status will abort the process. Although it receives no parameters, it is passed a string through stdin in the form of "<old-value> <new-value> <ref-name>" for each ref.</td>
<td>(none)</td>
</tr>
<tr>
<td>update</td>
<td><code>git-receive-pack</code> on the remote repo</td>
<td>This is run on the remote repo once for each ref being pushed instead of once for each push.  A non-zero status will abort the process. This can be used to make sure all commits are only fast-forward, for instance.</td>
<td>(3) The name of the ref being updated, the old object name, the new object name</td>
</tr>
<tr>
<td>post-receive</td>
<td><code>git-receive-pack</code> on the remote repo</td>
<td>This is run on the remote when pushing after the all refs have been updated.  It does not take parameters, but receives info through stdin in the form of "<old-value> <new-value> <ref-name>".  Because it is called after the updates, it cannot abort the process.</td>
<td>(none)</td>
</tr>
<tr>
<td>post-update</td>
<td><code>git-receive-pack</code> on the remote repo</td>
<td>This is run only once after all of the refs have been pushed.  It is similar to the post-receive hook in that regard, but does not receive the old or new values.  It is used mostly to implement notifications for the pushed refs.</td>
<td>(?) A parameter for each of the pushed refs containing its name</td>
</tr>
<tr>
<td>pre-auto-gc</td>
<td><code>git gc --auto</code></td>
<td>Is used to do some checks before automatically cleaning repos.</td>
<td>(none)</td>
</tr>
<tr>
<td>post-rewrite</td>
<td><code>git commit --amend</code>, <code>git-rebase</code></td>
<td>This is called when git commands are rewriting already committed data.  In addition to the parameters, it receives strings in stdin in the form of "<old-sha1> <new-sha1>".</td>
<td>(1) Name of the command that invoked it (<code>amend</code> or <code>rebase</code>)</td>
</tr>
</tbody></table>

<p>Now that you have all of this general information, we can demonstrate how to implement these in a few scenarios.</p>

<h2 id="setting-up-a-repository">Setting Up a Repository</h2>

<p>To get started, we'll create a new, empty repository in our home directory.  We will call this <code>proj</code>.</p>
<pre class="code-pre "><code langs="">mkdir ~/proj
cd ~/proj
git init
</code></pre><pre class="code-pre "><code langs="">Initialized empty Git repository in /home/demo/proj/.git/
</code></pre>
<p>Now, we are in the empty working directory of a git-controlled directory.  Before we do anything else, let's jump into the repository that is stored in the hidden file called <code>.git</code> within this directory:</p>
<pre class="code-pre "><code langs="">cd .git
ls -F
</code></pre><pre class="code-pre "><code langs="">branches/  config  description  HEAD  hooks/  info/  objects/  refs/
</code></pre>
<p>We can see a number of files and directories.  The one we're interested in is the <code>hooks</code> directory:</p>
<pre class="code-pre "><code langs="">cd hooks
ls -l
</code></pre><pre class="code-pre "><code langs="">total 40
-rwxrwxr-x 1 demo demo  452 Aug  8 16:50 applypatch-msg.sample
-rwxrwxr-x 1 demo demo  896 Aug  8 16:50 commit-msg.sample
-rwxrwxr-x 1 demo demo  189 Aug  8 16:50 post-update.sample
-rwxrwxr-x 1 demo demo  398 Aug  8 16:50 pre-applypatch.sample
-rwxrwxr-x 1 demo demo 1642 Aug  8 16:50 pre-commit.sample
-rwxrwxr-x 1 demo demo 1239 Aug  8 16:50 prepare-commit-msg.sample
-rwxrwxr-x 1 demo demo 1352 Aug  8 16:50 pre-push.sample
-rwxrwxr-x 1 demo demo 4898 Aug  8 16:50 pre-rebase.sample
-rwxrwxr-x 1 demo demo 3611 Aug  8 16:50 update.sample
</code></pre>
<p>We can see a few things here.  First, we can see that each of these files are marked executable.  Since these scripts are just called by name, they must be executable and their first line must be a <a href="http://en.wikipedia.org/wiki/Shebang_(Unix)#Magic_number">shebang magic number</a> reference to call the correct script interpreter.  Most commonly, these are scripting languages like bash, perl, python, etc.</p>

<p>The second thing you may notice is that all of the files end in <code>.sample</code>.  That is because git simply looks at the filename when trying to find the hook files to execute.  Deviating from the name of the script git is looking for basically disables the script.  In order to enable any of the scripts in this directory, we would have to remove the <code>.sample</code> suffix.</p>

<p>Let's get back out into our working directory:</p>
<pre class="code-pre "><code langs="">cd ../..
</code></pre>
<h3 id="first-example-deploying-to-a-local-web-server-with-a-post-commit-hook">First Example: Deploying to a Local Web Server with a Post-Commit Hook</h3>

<p>Our first example will use the <code>post-commit</code> hook to show you how to deploy to a local web server whenever a commit is made.  This is not the hook you would use for a production environment, but it lets us demonstrate some important, barely-documented items that you should know about when using hooks.</p>

<p>First, we will install the Apache web server to demonstrate:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install apache2
</code></pre>
<p>In order for our script to modify the web root at <code>/var/www/html</code> (this is the document root on Ubuntu 14.04.  Modify as needed), we need to have write permission.  Let's give our normal user ownership of this directory.  You can do this by typing:</p>
<pre class="code-pre "><code langs="">sudo chown -R `whoami`:`id -gn` /var/www/html
</code></pre>
<p>Now, in our project directory, let's create an <code>index.html</code> file:</p>
<pre class="code-pre "><code langs="">cd ~/proj
nano index.html
</code></pre>
<p>Inside, we can add a little bit of HTML just to demonstrate the idea.  It doesn't have to be complicated:</p>
<pre class="code-pre "><code langs=""><h1>Here is a title!</h1>

<p>Please deploy me!</p>
</code></pre>
<p>Add the new file to tell git to track the file:</p>
<pre class="code-pre "><code langs="">git add .
</code></pre>
<p>Now, <em>before</em> you commit, we are going to set up our <code>post-commit</code> hook for the repository.  Create this file within the <code>.git/hooks</code> directory for the project:</p>
<pre class="code-pre "><code langs="">vim .git/hooks/post-commit
</code></pre>
<p>Before we go over what to put in this file, we need to learn a bit about how git sets up the environment when running hooks.</p>

<h3 id="an-aside-about-environmental-variables-with-git-hooks">An Aside about Environmental Variables with Git Hooks</h3>

<p>Before we can begin our script, we need to learn a bit about what environmental variables git sets when calling hooks.  To get our script to function, we will eventually need to unset an environmental variable that git sets when calling the <code>post-commit</code> hook.</p>

<p>This is a very important point to internalize if you hope to write git hooks that function in a reliable way.  Git sets different environmental variables depending on which hook is being called.  This means that the environment that git is pulling information from will be different depending on the hook.</p>

<p>The first issue with this is that it can make your scripting environment very unpredictable if you are not aware of what variables are being set automatically.  The second issue is that the variables that are set are almost completely absent in git's own documentation.</p>

<p>Fortunately, Mark Longair developed <a href="http://longair.net/blog/2011/04/09/missing-git-hooks-documentation/">a method for testing each of the variables that git sets</a> when running these hooks.  It involves putting the following contents in various git hook scripts:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
echo Running $BASH_SOURCE
set | egrep GIT
echo PWD is $PWD
</code></pre>
<p>The information on his site is from 2011 working with git version 1.7.1, so there have been a few changes.  At the time of this writing in August of 2014, the current version of git in Ubuntu 14.04 is 1.9.1.</p>

<p>The results of the tests on this version of git are below (including the working directory as seen by git when running each hook).  The local working directory for the test was <code>/home/demo/test_hooks</code> and the bare remote (where necessary) was <code>/home/demo/origin/test_hooks.git</code>:</p>

<ul>
<li><strong>Hooks</strong>: <code>applypatch-msg</code>, <code>pre-applypatch</code>, <code>post-applypatch</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GIT_AUTHOR_DATE='Mon, 11 Aug 2014 11:25:16 -0400'</code></li>
<li><code>GIT_AUTHOR_EMAIL=demo@example.com</code></li>
<li><code>GIT_AUTHOR_NAME='Demo User'</code></li>
<li><code>GIT_INTERNAL_GETTEXT_SH_SCHEME=gnu</code></li>
<li><code>GIT_REFLOG_ACTION=am</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/test_hooks</code></li>
</ul></li>
<li><strong>Hooks</strong>: <code>pre-commit</code>, <code>prepare-commit-msg</code>, <code>commit-msg</code>, <code>post-commit</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GIT_AUTHOR_DATE='@1407774159 -0400'</code></li>
<li><code>GIT_AUTHOR_EMAIL=demo@example.com</code></li>
<li><code>GIT_AUTHOR_NAME='Demo User'</code></li>
<li><code>GIT_DIR=.git</code></li>
<li><code>GIT_EDITOR=:</code></li>
<li><code>GIT_INDEX_FILE=.git/index</code></li>
<li><code>GIT_PREFIX=</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/test_hooks</code></li>
</ul></li>
<li><strong>Hooks</strong>: <code>pre-rebase</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GIT_INTERNAL_GETTEXT_SH_SCHEME=gnu</code></li>
<li><code>GIT_REFLOG_ACTION=rebase</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/test_hooks</code></li>
</ul></li>
<li><strong>Hooks</strong>: <code>post-checkout</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GIT_DIR=.git</code></li>
<li><code>GIT_PREFIX=</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/test_hooks</code></li>
</ul></li>
<li><strong>Hooks</strong>: <code>post-merge</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GITHEAD_4b407c...</code></li>
<li><code>GIT_DIR=.git</code></li>
<li><code>GIT_INTERNAL_GETTEXT_SH_SCHEME=gnu</code></li>
<li><code>GIT_PREFIX=</code></li>
<li><code>GIT_REFLOG_ACTION='pull other master'</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/test_hooks</code></li>
</ul></li>
<li><strong>Hooks</strong>: <code>pre-push</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GIT_PREFIX=</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/test_hooks</code></li>
</ul></li>
<li><strong>Hooks</strong>: <code>pre-receive</code>, <code>update</code>, <code>post-receive</code>, <code>post-update</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GIT_DIR=.</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/origin/test_hooks.git</code></li>
</ul></li>
<li><strong>Hooks</strong>: <code>pre-auto-gc</code>

<ul>
<li>(unknown because this is difficult to trigger reliably)</li>
</ul></li>
<li><strong>Hooks</strong>: <code>post-rewrite</code>

<ul>
<li><strong>Environmental Variables</strong>:</li>
<li><code>GIT_AUTHOR_DATE='@1407773551 -0400'</code></li>
<li><code>GIT_AUTHOR_EMAIL=demo@example.com</code></li>
<li><code>GIT_AUTHOR_NAME='Demo User'</code></li>
<li><code>GIT_DIR=.git</code></li>
<li><code>GIT_PREFIX=</code></li>
<li><strong>Working Directory</strong>: <code>/home/demo/test_hooks</code></li>
</ul></li>
</ul>

<p>These variables have implication on how git sees its environment.  We will use the above information about variables to ensure that our script takes its environment into account correctly.</p>

<h3 id="back-to-the-script">Back to the Script</h3>

<p>Now that you have an idea about the type of environment that will be in place (look at the variables set for the <code>post-commit</code> hook), we can begin our script.</p>

<p>Since git hooks are standard scripts, we need to tell git what interpreter to use:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
</code></pre>
<p>After that, we are just going to use git itself to unpack the newest version of the repository after the commit, into our web directory.  To do this, we should set our working directory to Apache's document root.  We should also set our git directory to the repo.</p>

<p>We will want to force this transaction to make sure this is successful each time, even if there are conflicts between what is currently in the working directory.  It should look like this:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
git --work-tree=/var/www/html --git-dir=/home/<span class="highlight">demo</span>/proj/.git checkout -f
</code></pre>
<p>At this point, we are almost done.  However, we need to look extra close at the environmental variables that are set each time the <code>post-commit</code> hook is called.  In particular, the <code>GIT_INDEX_FILE</code> is set to <code>.git/index</code>.</p>

<p>This path is in relation to the working directory, which in this case is <code>/var/www/html</code>.  Since the git index does not exist at this location, the script will fail if we leave it as-is.  To avoid this situation, we can manually <em>unset</em> the variable, which will cause git to search in relation to the repo directory, as it usually does.  We need to add this <strong>above</strong> the checkout line:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
<span class="highlight">unset GIT_INDEX_FILE</span>
git --work-tree=/var/www/html --git-dir=/home/<span class="highlight">demo</span>/proj/.git checkout -f
</code></pre>
<p>These types of conflicts are why git hook issues are sometimes difficult to diagnose.  You must be aware of how git has constructed the environment it is working in.</p>

<p>When you are finished with these changes, save and close the file.</p>

<p>Because this is a regular script file, we need to make it executable:</p>
<pre class="code-pre "><code langs="">chmod +x .git/hooks/post-commit
</code></pre>
<p>Now, we are finally ready to commit the changes we made in our git repo.  Ensure that you are back in the correct directory and then commit the changes:</p>
<pre class="code-pre "><code langs="">cd ~/proj
git commit -m "here we go..."
</code></pre>
<p>Now, if you visit your server's domain name or IP address in your browser, you should see the <code>index.html</code> file you created:</p>
<pre class="code-pre "><code langs="">http://<span class="highlight">server_domain_or_IP</span>
</code></pre>
<p><img src="https://assets.digitalocean.com/articles/git_hooks/first_deploy.png" alt="Test index.html" /></p>

<p>As you can see, our most recent changes have been automatically pushed to the document root of our web server upon commit.  We can make some additional changes to show that it works on each commit:</p>
<pre class="code-pre "><code langs="">echo "<p>Here is a change.</p>" >> index.html
git add .
git commit -m "First change"
</code></pre>
<p>When you refresh your browser, you should immediately see the new changes that you applied:</p>

<p><img src="https://assets.digitalocean.com/articles/git_hooks/deploy_changes.png" alt="deploy changes" /></p>

<p>As you can see, this type of set up can make things easier for testing changes locally.  However, you'd almost never want to publish on commit in a production environment.  It is much safer to push after you've tested your code and are sure it is ready.</p>

<h2 id="using-git-hooks-to-deploy-to-a-separate-production-server">Using Git Hooks to Deploy to a Separate Production Server</h2>

<p>In this next example, we'll demonstrate a better way to update a production server.  We can do this by using the push-to-deploy model in order to update our web server whenever we push to a bare git repository.</p>

<p>We can use the same server we've set up as our development machine.  This is where we will do our work.  We will be able to see our changes after every single commit.</p>

<p>On our production machine, we will be setting up another web server, a bare git repository that we will push changes to, and a git hook that will execute whenever a push is received.  Complete the steps below as a normal user with sudo privileges.</p>

<h3 id="set-up-the-production-server-post-receive-hook">Set Up the Production Server Post-Receive Hook</h3>

<p>On the production server, start off by installing the web server:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install apache2
</code></pre>
<p>Again, we should give ownership of the document root to the user we are operating as:</p>
<pre class="code-pre "><code langs="">sudo chown -R `whoami`:`id -gn` /var/www/html
</code></pre>
<p>We need to remember to install git on this machine as well:</p>
<pre class="code-pre "><code langs="">sudo apt-get install git
</code></pre>
<p>Now, we can create a directory within our user's home directory to hold the repository.  We can then move into that directory and initialize a bare repository.  A bare repository does not have a working directory and is better for servers that you will not be working with much directly:</p>
<pre class="code-pre "><code langs="">mkdir ~/proj
cd ~/proj
git init --bare
</code></pre>
<p>Since this is a bare repository, there is no working directory and all of the files that are located in <code>.git</code> in a conventional setup are in the main directory itself.</p>

<p>We need to create another git hook.  This time, we are interested in the <code>post-receive</code> hook, which is run on the server receiving a <code>git push</code>.  Open this file in your editor:</p>
<pre class="code-pre "><code langs="">nano hooks/post-receive
</code></pre>
<p>Again, we need to start off by identifying the type of script we are writing.  After that, we can type out the same checkout command that we used in our <code>post-commit</code> file, modified to use the paths on this machine:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
git --work-tree=/var/www/html --git-dir=/home/<span class="highlight">demo</span>/proj checkout -f
</code></pre>
<p>Since this is a bare repository, the <code>--git-dir</code> should point to the top-level directory of that repo.  The rest is fairly similar.</p>

<p>However, we need to add some additional logic to this script.  If we accidentally push a <code>test-feature</code> branch to this server, we do not want that to be deployed.  We want to make sure that we are only going to be deploying the <code>master</code> branch.</p>

<p>For the <code>post-receive</code> hook, you may have noticed in the table earlier that git passes the old revision's commit hash, the new revision's commit hash, and the reference that is being pushed as standard input to the script.  We can use this to check whether the ref is the master branch or not.</p>

<p>First, we need to read the standard input.  For each ref being pushed, the three pieces of info (old rev, new rev, ref) will be fed to the script, separated by white space, as standard input.  We can read this with a <code>while</code> loop to surround the <code>git</code> command:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
while read oldrev newrev ref
do
    git --work-tree=/var/www/html --git-dir=/home/<span class="highlight">demo</span>/proj checkout -f
done
</code></pre>
<p>So now, we will have three variables set based on what is being pushed.  For a master branch push, the <code>ref</code> object will contain something that looks like <code>refs/heads/master</code>.  We can check to see if the ref the server is receiving has this format by using an <code>if</code> construct:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
while read oldrev newrev ref
do
    if [[ $ref =~ .*/master$ ]];
    then
        git --work-tree=/var/www/html --git-dir=/home/<span class="highlight">demo</span>/proj checkout -f
    fi
done
</code></pre>
<p>For server-side hooks, git can actually pass messages back to the client.  Anything sent to standard out will be redirected to the client.  This gives us an opportunity to explicitly notify the user about what decision has been made.</p>

<p>We should add some text describing what situation was detected, and what action was taken.  We should add an <code>else</code> block to notify the user when a non-master branch was successfully received, even though the action won't trigger a deploy:</p>
<pre class="code-pre "><code langs="">#!/bin/bash
while read oldrev newrev ref
do
    if [[ $ref =~ .*/master$ ]];
    then
        echo "Master ref received.  Deploying master branch to production..."
        git --work-tree=/var/www/html --git-dir=/home/<span class="highlight">demo</span>/proj checkout -f
    else
        echo "Ref $ref successfully received.  Doing nothing: only the master branch may be deployed on this server."
    fi
done
</code></pre>
<p>When you are finished, save and close the file.</p>

<p>Remember, we must make the script executable for the hook to work:</p>
<pre class="code-pre "><code langs="">chmod +x hooks/post-receive
</code></pre>
<p>Now, we can set up access to this remote server on our client.</p>

<h3 id="configure-the-remote-server-on-your-client-machine">Configure the Remote Server on your Client Machine</h3>

<p>Back on your client (development) machine, go back into the working directory of your project:</p>
<pre class="code-pre "><code langs="">cd ~/proj
</code></pre>
<p>Inside, add the remote server as a remote called <code>production</code>.  You will need to know the username that you used on your production server, as well as its IP address or domain name. You will also need to know the location of the bare repository you set up in relation to the user's home directory.</p>

<p>The command you type should look something like this:</p>
<pre class="code-pre "><code langs="">git remote add production <span class="highlight">demo</span>@<span class="highlight">server_domain_or_IP</span>:proj
</code></pre>
<p>Let's push our current master branch to our production server:</p>
<pre class="code-pre "><code langs="">git push production master
</code></pre>
<p>If you do not have SSH keys configured, you may have to enter the password of your production server user.  You should see something that looks like this:</p>
<pre class="code-pre "><code langs="">Counting objects: 8, done.
Delta compression using up to 2 threads.
Compressing objects: 100% (3/3), done.
Writing objects: 100% (4/4), 473 bytes | 0 bytes/s, done.
Total 4 (delta 0), reused 0 (delta 0)
<span class="highlight">remote: Master ref received.  Deploying master branch...</span>
To demo@107.170.14.32:proj
   009183f..f1b9027  master -> master
</code></pre>
<p>As you can see, the text from our <code>post-receive</code> hook is in the output of the command.  If we visit our production server's domain name or IP address in our web browser, we should see the current version of our project:</p>

<p><img src="https://assets.digitalocean.com/articles/git_hooks/pushed_prod.png" alt="pushed production" /></p>

<p>It looks like the hook has successfully pushed our code to production once it received the information.</p>

<p>Now, let's test out some new code.  Back on the development machine, we will create a new branch to hold our changes.  This way, we can make sure everything is ready to go before we deploy into production.</p>

<p>Make a new branch called <code>test_feature</code> and check the new branch out by typing:</p>
<pre class="code-pre "><code langs="">git checkout -b test_feature
</code></pre>
<p>We are now working in the <code>test_feature</code> branch.  Let's make a change that we <em>might</em> want to move to production.  We will commit it to this branch:</p>
<pre class="code-pre "><code langs="">echo "<h2>New Feature Here</h2>" >> index.html
git add .
git commit -m "Trying out new feature"
</code></pre>
<p>At this point, if you go to your development machine's IP address or domain name, you should see your changes displayed:</p>

<p><img src="https://assets.digitalocean.com/articles/git_hooks/devel_commit.png" alt="commit changes" /></p>

<p>This is because our development machine is still being re-deployed at each commit.  This work-flow is great for testing out changes prior to moving them to production.</p>

<p>We can push our <code>test_feature</code> branch to our remote production server:</p>
<pre class="code-pre "><code langs="">git push production test_feature
</code></pre>
<p>You should see the other message from our <code>post-receive</code> hook in the output:</p>
<pre class="code-pre "><code langs="">Counting objects: 5, done.
Delta compression using up to 2 threads.
Compressing objects: 100% (2/2), done.
Writing objects: 100% (3/3), 301 bytes | 0 bytes/s, done.
Total 3 (delta 1), reused 0 (delta 0)
<span class="highlight">remote: Ref refs/heads/test_feature successfully received.  Doing nothing: only the master branch may be deployed on this server</span>
To demo@107.170.14.32:proj
   83e9dc4..5617b50  test_feature -> test_feature
</code></pre>
<p>If you check out the production server in your browser again, you should see that nothing has changed.  This is what we expect, since the change that we pushed was not in the master branch.</p>

<p>Now that we have tested our changes on our development machine, we are sure that we want to incorporate this feature into our master branch.  We can checkout our <code>master</code> branch and merge in our <code>test_feature</code> branch on our development machine:</p>
<pre class="code-pre "><code langs="">git checkout master
git merge test_feature
</code></pre>
<p>Now, you have merged the new feature into the master branch.  Pushing to the production server will deploy our changes:</p>
<pre class="code-pre "><code langs="">git push production master
</code></pre>
<p>If we check out our production server's domain name or IP address, we will see our changes:</p>

<p><img src="https://assets.digitalocean.com/articles/git_hooks/new_prod.png" alt="Pushed to production" /></p>

<p>Using this workflow, we can have a development machine that will immediately show any committed changes.  The production machine will be updated whenever we push the master branch.</p>

<h2 id="conclusion">Conclusion</h2>

<p>If you've followed along this far, you should be able to see the different ways that git hooks can help automate some of your tasks.  They can help you deploy your code, or help you maintain quality standards by rejecting non-conformant changes or commit messages.</p>

<p>While the utility of git hooks is hard to argue, the actual implementation can be rather difficult to grasp and frustrating to troubleshoot.  Practicing implementing various configurations, experimenting with parsing arguments and standard input, and keeping track of how git constructs the hooks' environment will go a long way in teaching you how to write effective hooks.  In the long run, the time investment is usually worth it, as it can easily save you and your team loads of manual work over the course of your project's life.</p>

    