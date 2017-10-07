<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Cheat is a command line based Python program that allows system administrators to view and store helpful cheat sheets. It retrieves plain-text examples of a chosen command in order to remind the user of options, arguments, or common uses. Cheat is ideal for "commands that you use frequently, but not frequently enough to remember."</p>

<p>Sheets are small portable text files that can be copied across multiple Linux/Unix systems; they are called and viewed like any other command line program. Base sheets for common programs are provided but you can add custom new sheets, too.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 14.04 Droplet</p></li>
<li><p>A sudo non-root user, which you can set up by following the <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">How To Add and Delete Users on an Ubuntu 14.04 VPS</a> tutorial</p></li>
</ul>

<h2 id="step-1-—-installing-cheat">Step 1 — Installing Cheat</h2>

<p>Before installing Cheat, we need make sure everything's up to date on the system.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update && sudo apt-get upgrade
</li></ul></code></pre>
<p>Confirm by entering <code>y</code> for any prompts in this step. </p>

<p>Installing Cheat is best done with the Python package manager Pip, so install Pip next.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install python-pip
</li></ul></code></pre>
<p>Cheat itself only depends upon two Python packages, both of which are conveniently included with Pip's Cheat package. Finally, install Cheat.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip install cheat
</li></ul></code></pre>
<p>A successful install of Cheat will output these lines:</p>
<div class="code-label " title="sudo pip install cheat output">sudo pip install cheat output</div><pre class="code-pre "><code langs="">Successfully installed cheat docopt pygments
Cleaning up...
</code></pre>
<p>We can confirm that Cheat is installed and working by running it with its <code>-v</code> option.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat -v
</li></ul></code></pre>
<p>This outputs the version of Cheat that we have installed.</p>
<div class="code-label " title="cheat -v output">cheat -v output</div><pre class="code-pre "><code langs="">cheat 2.1.10
</code></pre>
<h2 id="step-2-—-setting-the-text-editor">Step 2 — Setting the Text Editor</h2>

<p>Before we can go on to create our own cheat sheets, Cheat needs to know which text editor we would like to use to edit sheets by default. To do this, we must create and set an environment variable called <code>EDITOR</code>. For more information on shell and environment variables, you can read the <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">How To Read and Set Environmental and Shell Variables</a> tutorial.</p>

<p>Because nano is already installed on Ubuntu and is generally easy to learn, we'll set it as our preferred text editor with the command below. However, you can use vim, emacs, or your favorite text editor instead.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">export EDITOR="/usr/bin/nano"
</li></ul></code></pre>
<p>We can confirm this was successful by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">printenv EDITOR
</li></ul></code></pre>
<p>This will output the new <code>$EDITOR</code> environment variable's contents:</p>
<div class="code-label " title="printenv EDITOR output">printenv EDITOR output</div><pre class="code-pre "><code langs="">/usr/bin/nano
</code></pre>
<p>To make this change persistent and permanent across all future shell sessions, you must add the environment variable declaration to your <code>.bashrc</code> file. This is one of several files that are run at the start of a bash shell session.</p>

<p>Open this file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/.bashrc
</li></ul></code></pre>
<p>Then add the same export command:</p>
<div class="code-label " title="~/.bashrc">~/.bashrc</div><pre class="code-pre "><code langs="">. . .
# If not running interactively, don't do anything
case $- in
    *i*) ;;
      *) return;;
esac

<span class="highlight">export EDITOR="/usr/bin/nano"</span>

# don't put duplicate lines or lines starting with space in the history.
# See bash(1) for more options
HISTCONTROL=ignoreboth
. . .
</code></pre>
<p>Save and exit the file by pressing <code>CTRL+X</code> and then <code>Y</code> followed by <code>ENTER</code>.</p>

<h2 id="step-3-—-customizing-cheat-optional">Step 3 — Customizing Cheat (Optional)</h2>

<p>In this step, we'll customize Cheat by enabling syntax highlighting and command line auto-completion.</p>

<p>When using a terminal emulator that has color support, you can enable syntax highlighting for your sheets by exporting a shell environment variable named <code>CHEATCOLORS</code> defined as true:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">export CHEATCOLORS=true
</li></ul></code></pre>
<p>Now whenever you retrieve cheat sheets, they will be formatted with colored syntax highlighting. If you like this feature, you can make it persistent and permanent across shell sessions by adding the export command to your <code>.bashrc</code> file.</p>

<p>Open the <code>.bashrc</code> file again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/.bashrc
</li></ul></code></pre>
<p>Then add the new <code>CHEATCOLORS</code> variable below the <code>EDITOR</code> variable:</p>
<div class="code-label " title="~/.bashrc">~/.bashrc</div><pre class="code-pre "><code langs="">. . .
# If not running interactively, don't do anything
case $- in
    *i*) ;;
      *) return;;
esac

export EDITOR="/usr/bin/nano"
<span class="highlight">export CHEATCOLORS=true</span>

# don't put duplicate lines or lines starting with space in the history
. . .
</code></pre>
<p>Save and close the file.</p>

<p>Next, to enable command line auto-completion, we need to put a script in the <code>/etc/bash_completion.d/</code> directory. Change to this directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /etc/bash_completion.d/
</li></ul></code></pre>
<p>Then download the script we need from Cheat's GitHub project page.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo wget https://raw.githubusercontent.com/chrisallenlane/cheat/master/cheat/autocompletion/cheat.bash
</li></ul></code></pre>
<p>Now enter <code>bash</code> into the current shell to pick up the changes.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bash
</li></ul></code></pre>
<p>Tab auto-completion for Cheat is now enabled. If you type <code>cheat</code> followed by a space, pressing the <code>TAB</code> key twice will give you a list of commands.</p>
<div class="code-label " title="cheat tab auto-completion output">cheat tab auto-completion output</div><pre class="code-pre "><code langs="">cheat 
7z           asciiart     chown        df           du          
grep         indent       jrnl         mkdir        netstat
. . .
</code></pre>
<h2 id="step-4-—-running-cheat">Step 4 — Running Cheat</h2>

<p>To run Cheat in its most basic form, you call it like any other command, followed by an existing cheat sheet name. </p>

<p>Here is an example of how to do this with one of the default sheets that comes included with Cheat, for the <code>tail</code> command (which outputs the last few lines of a file).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat tail
</li></ul></code></pre>
<p>You'll then see this output:</p>
<div class="code-label " title="cheat tail output">cheat tail output</div><pre class="code-pre "><code langs=""># To show the last 10 lines of file
tail file

# To show the last N lines of file
tail -n N file

# To show the last lines of file starting with the Nth
tail -n +N file

# To show the last N bytes of file
tail -c N file

# To show the last 10 lines of file and to wait for file to grow
tail -f file
</code></pre>
<p>To see what other other existing cheat sheets are available to us, run Cheat with its  <code>-l</code> option.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat -l
</li></ul></code></pre>
<p>This lists all available sheets and their location on the server.</p>

<h2 id="step-5-—-creating-and-editing-cheat-sheets">Step 5 — Creating and Editing Cheat Sheets</h2>

<p>Although the base provisional sheets included with Cheat are useful and varied, they are not all inclusive of every shell command or program available to us. The real benefit we can get from Cheat comes with adding our own custom sheets. </p>

<p>For example, there is no sheet for the networking program <code>ping</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat ping
</li></ul></code></pre><div class="code-label " title="cheat ping output">cheat ping output</div><pre class="code-pre "><code langs="">No cheatsheet found for ping
</code></pre>
<p>Let's make one to serve as an example of how to create and add a new sheet. First, invoke Cheat on the command line again, this time followed by <code>-e</code> and the name of the sheet we are making it for.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat -e ping
</li></ul></code></pre>
<p>Cheat will create and open the relevant file for editing using the <code>$EDITOR</code> variable we set earlier.</p>

<p>Add a useful ping command example to the beginning of this new sheet, complete with a comment (indicated by <code>#</code>) that explains what the command does when entered. Here is one such command you could enter in the file:</p>
<div class="code-label " title="~/.cheat/ping">~/.cheat/ping</div><pre class="code-pre "><code langs=""># ping a host with a total count of 15 packets overall.    
ping -c 15 www.example.com
</code></pre>
<p>Save and exit the file as before. Next let's test the new sheet by running <code>cheat ping</code> again.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat ping
</li></ul></code></pre>
<p>This time, we'll see the cheat sheet we just added.</p>
<div class="code-label " title="cheat ping output">cheat ping output</div><pre class="code-pre "><code langs=""># ping a host with a total count of 15 packets overall.    
ping -c 15 www.example.com
</code></pre>
<p>To modify an existing sheet, we can use the <code>-e</code> option again.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat -e ping 
</li></ul></code></pre>
<p>The ping sheet is now open and we can add more examples or content. For example, we can add the following:</p>
<div class="code-label " title="~/.cheat/ping">~/.cheat/ping</div><pre class="code-pre "><code langs=""># ping a host with a total count of 15 packets overall.    
ping -c 15 www.example.com

# ping a host with a total count of 15 packets overall, one every .5 seconds (faster ping). 
ping -c 15 -i .5 www.example.com
</code></pre>
<h2 id="step-6-—-searching-cheat-sheets">Step 6 — Searching Cheat Sheets</h2>

<p>Cheat has a built-in search function triggered with the <code>-s</code> option. This will pick up any and all occurrences of the text you provide it with. For example:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cheat -s packets
</li></ul></code></pre>
<p>This command will output all the lines featuring the term "packets" and the sheet they originate from.</p>
<div class="code-label " title="cheat -s packets utput">cheat -s packets utput</div><pre class="code-pre "><code langs="">nmap:
  # --min-rate=X => min X packets / sec

ping:
  # ping a host with a total count of 15 packets overall.    
  # ping a host with a total count of 15 packets overall, one every .5 seconds (faster ping). 

route:
  # To add a default  route (which will be used if no other route matches).  All packets using this route will be gatewayed through "mango-gw". The device which will actually be used for that route depends on how we can reach "mango-gw" - the static route to "mango-gw" will have to be set up before.

tcpdump:
  # and other packets being transmitted or received over a network. (cf Wikipedia).

. . .
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Because everything Cheat displays is plain-text and directed through the shell's standard output, we can use any text processing commands (like <code>grep</code>) with it. You can read the <a href="https://indiareads/community/tutorials/using-grep-regular-expressions-to-search-for-text-patterns-in-linux">Using Grep & Regular Expressions to Search for Text Patterns in Linux</a> tutorial for more information on <code>grep</code>.</p>

<p>Additionally, version control system such as Git with GitHub are ideal for storing your custom cheat sheets centrally, so you can get hold of them on multiple platforms via cloning a repository. A sheet is classed as <em>custom</em> if have you added to it, amended it, or created it yourself through Cheat.</p>

<p>All custom cheat sheets are stored in your Linux user's home directory, inside a hidden folder named <code>.cheat</code>. You can find this location by running <code>cheat -d</code>, which will output two directories: the first is the location of your custom sheets, and the second is the location of the default sheets you get with Cheat upon install.  </p>

<p>To access your library of custom sheets on other systems, you need only to copy this <code>.cheat</code> folder onto them. The cheat sheets are small plain-text files so this makes them perfect for tracking with version control. For a complete solution to making your cheat sheets and configuration files accessible at all times, you can read the <a href="https://indiareads/community/tutorials/how-to-use-git-to-manage-your-user-configuration-files-on-a-linux-vps">How To Use Git to Manage your User Configuration Files on a Linux VPS</a> tutorial.</p>

    