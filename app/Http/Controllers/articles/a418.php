<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>In this guide, we'll talk about how <code>bash</code>, the Linux system, and your terminal come together to offer process and job control.  In a <a href="https://indiareads/community/tutorials/how-to-use-ps-kill-and-nice-to-manage-processes-in-linux">previous guide</a>, we discussed how the <code>ps</code>, <code>kill</code>, and <code>nice</code> commands can be used to control processes on your system.</p>

<p>This article will focus on managing foreground and background processes and will demonstrate how to leverage your shell's job control functions to gain more flexibility in how you run commands.</p>

<h2 id="managing-foreground-processes">Managing Foreground Processes</h2>

<p>Most processes that you start on a Linux machine will run in the foreground.  The command will begin execution, blocking use of the shell for the duration of the process.  The process may allow user interaction or may just run through a procedure and then exit.  Any output will be displayed in the terminal window by default.  We'll discuss the basic way to manage foreground processes below.</p>

<h3 id="starting-a-process">Starting a Process</h3>

<p>By default, processes are started in the foreground.  Until the program exits or changes state, you will not be able to interact with the shell.</p>

<p>Some foreground commands exit very quickly and return you to a shell prompt almost immediately.  For instance, this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo "Hello World"
</li></ul></code></pre>
<p>This would print "Hello World" to the terminal and then return you to your command prompt.</p>

<p>Other foreground commands take longer to execute, blocking shell access for the duration.  This might be because the command is performing a more extensive operation or because it is configured to run until it is explicitly stopped or until it receives other user input.</p>

<p>A command that runs indefinitely is the <code>top</code> utility.  After starting, it will continue to run and update its display until the user terminates the process:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">top
</li></ul></code></pre>
<p>You can quit by typing "q".  Some processes don't have a dedicated quit function.  To stop those, you'll have to use another method.</p>

<h3 id="terminating-a-process">Terminating a Process</h3>

<p>Suppose we start a simple <code>bash</code> loop on the command line.  We can start a loop that will print "Hello World" every ten seconds.  This loop will continue forever, until explicitly terminated:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">while true; do echo "Hello World"; sleep 10; done
</li></ul></code></pre>
<p>Loops have no "quit" key.  We will have to stop the process by sending it a <strong>signal</strong>.  In Linux, the kernel can send processes signals in order to request that they exit or change states.  Linux terminals are usually configured to send the "SIGINT" signal (typically signal number 2) to current foreground process when the <code>CTRL-C</code> key combination is pressed.  The SIGINT signal tells the program that the user has requested termination using the keyboard.</p>

<p>To stop the loop we've started, hold the control key and press the "c" key:</p>
<pre class="code-pre "><code langs="">CTRL-C
</code></pre>
<p>The loop will exit, returning control to the shell.</p>

<p>The SIGINT signal sent by the <code>CTRL-C</code> combination is one of many signals that can be sent to programs.  Most signals do not have keyboard combinations associated with them and must be sent using the <code>kill</code> command instead (we will cover this later).</p>

<h3 id="suspending-processes">Suspending Processes</h3>

<p>We mentioned above that foreground process will block access to the shell for the duration of their execution.  What if we start a process in the foreground, but then realize that we need access to the terminal?</p>

<p>Another signal that we can send is the "SIGTSTP" signal (typically signal number 20).  When we hit <code>CTRL-Z</code>, our terminal registers a "suspend" command, which then sends the SIGTSTP signal to the foreground process.  This will basically pause the execution of the command and return control to the terminal.</p>

<p>To demonstrate, let's use <code>ping</code> to connect to google.com every 5 seconds.  We will precede the <code>ping</code> command with <code>command</code>, which will allow us to bypass any shell aliases that artificially set a maximum count on the command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">command ping -i 5 google.com
</li></ul></code></pre>
<p>Instead of terminating the command with <code>CTRL-C</code>, type <code>CTRL-Z</code> instead:</p>
<pre class="code-pre "><code langs="">CTRL-Z
</code></pre>
<p>You will see output that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[1]+  Stopped                 ping -i 5 google.com
</code></pre>
<p>The <code>ping</code> command has been temporarily stopped, giving you access to a shell prompt again.  We can use the <code>ps</code> process tool to show this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ps T
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>  PID TTY      STAT   TIME COMMAND
26904 pts/3    Ss     0:00 /bin/bash
29633 pts/3    T      0:00 ping -i 5 google.com
29643 pts/3    R+     0:00 ps t
</code></pre>
<p>We can see that the <code>ping</code> process is still listed, but that the "STAT" column has a "T" in it.  The <code>ps</code> man page tells us that this represents a job that has been "stopped by (a) job control signal".</p>

<p>We will discuss in more depth how to change process states, but for now, we can resume execution of the command in the foreground again by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fg
</li></ul></code></pre>
<p>Once the process has resumed, terminate it with <code>CTRL-C</code>:</p>
<pre class="code-pre "><code langs="">CTRL-C
</code></pre>
<h2 id="managing-background-processes">Managing Background Processes</h2>

<p>The main alternative to running a process in the foreground is to allow it to execute in the background.  A background process is associated with the specific terminal that started it, but does not block access to the shell.  Instead, it executes in the background, leaving the user able to interact with the system while the command runs.</p>

<p>Because of the way that a foreground processes interacts with its terminal, there can be only a single foreground process for every terminal window.  Because background processes return control to the shell immediately without waiting for the process to complete, many background processes can run at the same time.</p>

<h3 id="starting-processes">Starting Processes</h3>

<p>You can start a background process by appending an ampersand character ("&") to the end of your commands.  This tells the shell not to wait for the process to complete, but instead to begin execution and to immediately return the user to a prompt.  The output of the command will still display in the terminal (unless <a href="https://indiareads/community/tutorials/an-introduction-to-linux-i-o-redirection">redirected</a>), but you can type additional commands as the background process continues.</p>

<p>For instance, we can start the same ping process from the last section in the background by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">command ping -i 5 google.com &
</li></ul></code></pre>
<p>You will see output from the <code>bash</code> job control system that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[1] 4287
</code></pre>
<p>You will also see the normal output from the <code>ping</code> command:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>PING google.com (74.125.226.71) 56(84) bytes of data.
64 bytes from lga15s44-in-f7.1e100.net (74.125.226.71): icmp_seq=1 ttl=55 time=12.3 ms
64 bytes from lga15s44-in-f7.1e100.net (74.125.226.71): icmp_seq=2 ttl=55 time=11.1 ms
64 bytes from lga15s44-in-f7.1e100.net (74.125.226.71): icmp_seq=3 ttl=55 time=9.98 ms
</code></pre>
<p>However, you can also type commands at the same time.  The background process's output will be mixed among the input and output of your foreground processes, but it will not interfere with the execution of the foreground processes.</p>

<h3 id="listing-background-processes">Listing Background Processes</h3>

<p>To see all stopped or backgrounded processes, you can use the <code>jobs</code> command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">jobs
</li></ul></code></pre>
<p>If you have the <code>ping</code> command running in the background, you will see something that looks like this:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[1]+  Running                 command ping -i 5 google.com &
</code></pre>
<p>This shows that we currently have a single background process running.  The <code>[1]</code> represents the command's "job spec" or job number.  We can reference this with other job and process control commands, like <code>kill</code>, <code>fg</code>, and <code>bg</code> by preceding the job number with a percentage sign.  In this case, we'd reference this job as <code>%1</code>.</p>

<h3 id="stopping-background-processes">Stopping Background Processes</h3>

<p>We can stop the current background process in a few ways.  The most straight forward way is to use the <code>kill</code> command with the associate job number.  For instance, we can kill our running background process by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">kill %1
</li></ul></code></pre>
<p>Depending on how your terminal is configured, either immediately or the next time you hit ENTER, you will see the job termination status:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[1]+  Terminated              command ping -i 5 google.com
</code></pre>
<p>If we check the <code>jobs</code> command again, we'll see no current jobs.</p>

<h2 id="changing-process-states">Changing Process States</h2>

<p>Now that we know how to start and stop processes in the background, we can talk about how to change their state.</p>

<p>We demonstrated one state change earlier when we described how to stop or suspend a process with <code>CTRL-Z</code>.  When processes are in this stopped state, we can move a foreground process to the background or vice versa.</p>

<h3 id="moving-foreground-processes-to-the-background">Moving Foreground Processes to the Background</h3>

<p>If we forget to end a command with <code>&</code> when we start it, we can still move the process to the background.</p>

<p>The first step is to stop the process with <code>CTRL-Z</code> again:</p>
<pre class="code-pre "><code langs="">CTRL-Z
</code></pre>
<p>Once the process is stopped, we can use the <code>bg</code> command to start it again in the background:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">bg
</li></ul></code></pre>
<p>You will see the job status line again, this time with the ampersand appended:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[1]+ ping -i 5 google.com &
</code></pre>
<p>By default, the <code>bg</code> command operates on the most recently stopped process.  If you've stopped multiple processes in a row without starting them again, you can reference the process by job number to background the correct process.</p>

<p>Note that not all commands can be backgrounded.  Some processes will automatically terminate if they detect that they have been started with their standard input and output directly connected to an active terminal.</p>

<h3 id="moving-background-processes-to-the-foreground">Moving Background Processes to the Foreground</h3>

<p>We can also move background processes to the foreground by typing <code>fg</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fg
</li></ul></code></pre>
<p>This operates on your most recently backgrounded process (indicated by the "+" in the <code>jobs</code> output).  It immediately suspends the process and puts it into the foreground.  To specify a different job, use its job number:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">fg %2
</li></ul></code></pre>
<p>Once a job is in the foreground, you can kill it with <code>CTRL-C</code>, let it complete, or suspend and background it again.</p>

<h2 id="dealing-with-sighups">Dealing with SIGHUPs</h2>

<p>Whether a process is in the background or in the foreground, it is rather tightly tied with the terminal instance that started it.  When a terminal closes, it typically sends a SIGHUP signal to all of the processes (foreground, background, or stopped) that are tied to the terminal.  This signals for the processes to terminate because their controlling terminal will shortly be unavailable.  What if you want to close a terminal but keep the background processes running?</p>

<p>There are a number of ways of accomplishing this.  The most flexible ways are typically to use a terminal multiplexer like <a href="https://indiareads/community/tutorials/how-to-install-and-use-screen-on-an-ubuntu-cloud-server"><code>screen</code></a> or <a href="https://indiareads/community/tutorials/how-to-install-and-use-tmux-on-ubuntu-12-10--2"><code>tmux</code></a>, or use a utility that provides at least the detach functionality of those, like <a href="https://indiareads/community/tutorials/how-to-use-dvtm-and-dtach-as-a-terminal-window-manager-on-an-ubuntu-vps"><code>dtach</code></a>.</p>

<p>However, this isn't always an option.  Sometimes these programs aren't available or you've already started the process you need to continue running.  Sometimes these are overkill for what you need to accomplish.</p>

<h3 id="using-nohup">Using nohup</h3>

<p>If you know when starting the process that you will want to close the terminal before the process completes, you can start it using the <code>nohup</code> command.  This makes the started process immune to the SIGHUP signal.  It will continue running when the terminal closes.  It will be reassigned as a child of the init system:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nohup ping -i 5 google.com &
</li></ul></code></pre>
<p>You will see a line that looks like this, indicating that the output of the command will be written to a file called <code>nohup.out</code> (in the current directory if writeable, otherwise to your home directory):</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>nohup: ignoring input and appending output to ‘nohup.out’
</code></pre>
<p>This is to ensure that output is not lost if the terminal window is closed.</p>

<p>If you close the terminal window and open another one, the process will still be running.  You will not see it in the output of the <code>jobs</code> command because each terminal instance maintains its own independent job queue.  The terminal closing caused the <code>ping</code> <em>job</em> to be destroyed even though the <code>ping</code> <em>process</em> is still running.</p>

<p>To kill the <code>ping</code> process, you'll have to look up its process ID (or "PID").  You can do that with the <code>pgrep</code> command (there is also a <code>pkill</code> command, but this two-part method ensures that we are only killing the intended process).  Use <code>pgrep</code> and the <code>-a</code> flag to search for the executable:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pgrep -a ping
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>7360 ping -i 5 google.com
</code></pre>
<p>You can then kill the process by referencing the returned PID, which is the number in the first column:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">kill 7360
</li></ul></code></pre>
<p>You may wish to remove the <code>nohup.out</code> file if you don't need it anymore.</p>

<h3 id="using-disown">Using disown</h3>

<p>The <code>nohup</code> command is helpful, but only if you know you will need it at the time you start the process.  The <code>bash</code> job control system provides other methods of achieving similar results with the <code>disown</code> built in command.</p>

<p>The <code>disown</code> command, in its default configuration, removes a job from the jobs queue of a terminal.  This means that it can no longer be managed using the job control mechanisms discussed in this guide (like <code>fg</code>, <code>bg</code>, <code>CTRL-Z</code>, <code>CTRL-C</code>).  It will immediately be removed from the list in the <code>jobs</code> output and no longer associated with the terminal.</p>

<p>The command is called by specifying a job number.  For instance, to immediately disown job 2, we could type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">disown %2
</li></ul></code></pre>
<p>This leaves the process in a state not unlike that of a <code>nohup</code> process after the controlling terminal has been closed.  The exception is that any output will be lost when the controlling terminal closes if it is not being redirected to a file.</p>

<p>Usually, you don't want to remove the process completely from job control if you aren't immediately closing your terminal window.  You can pass the <code>-h</code> flag to the <code>disown</code> process instead in order to mark the process to ignore SIGHUP signals, but to otherwise continue on as a regular job:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">disown -h %1
</li></ul></code></pre>
<p>In this state, you could use normal job control mechanisms to continue controlling the process until closing the terminal.  Upon closing the terminal, you will, once again, be stuck with a process with nowhere to output if you didn't redirect to a file when starting it.</p>

<p>To work around that, you can try to redirect the output of your process after it is already running.  This is outside the scope of this guide, but you can take a look at <a href="http://etbe.coker.com.au/2008/02/27/redirecting-output-from-a-running-process/">this post</a> to get an idea of how you would do that.</p>

<h3 id="using-the-huponexit-shell-option">Using the huponexit Shell Option</h3>

<p>Bash also has another way of avoiding the SIGHUP problem for child processes.  The <code>huponexit</code> shell option controls whether bash will send its child processes the SIGHUP signal when it exits.</p>

<div class="code-label notes-and-warnings note" title="Note">Note</div><span class="note"><p>

The <code>huponexit</code> option only affect the SIGHUP behavior when a shell session termination is initiated <strong>from within the shell itself</strong>.  Some examples of when this applies is when the <code>exit</code> command or <code>CTRL-D</code> is hit within the session.</p>

<p>When a shell session is ended through the terminal program itself (through closing the window, etc.), the command <code>huponexit</code> will have <strong>no</strong> affect.  Instead of <code>bash</code> deciding on whether to send the SIGHUP signal, the terminal itself will send the SIGHUP signal to <code>bash</code>, which will then (correctly) propagate the signal to its child processes.<br /></p></span>

<p>Despite the above caveats, the <code>huponexit</code> option is perhaps one of the easiest.  You can see whether this feature is on or off by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">shopt huponexit
</li></ul></code></pre>
<p>To turn it on, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">shopt -s huponexit
</li></ul></code></pre>
<p>Now, if you exit your session by typing <code>exit</code>, your processes will all continue to run:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">exit
</li></ul></code></pre>
<p>This has the same caveats about program output as the last option, so make sure you have redirected your processes' output if it is important prior to closing your terminal.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Learning job control and how to manage foreground and background processes will give you greater flexibility when running programs on the command line.  Instead of having to open up many terminal windows or SSH sessions, you can often get by with a few stop and background commands.</p>

    