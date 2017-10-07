<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Setting your server's clock and timezone properly is essential in ensuring the healthy operation of distributed systems and maintaining accurate log timestamps. This tutorial will show you how to configure NTP time synchronization and set the timezone on an Ubuntu 14.04 server.</p>

<p>A more detailed version of this tutorial, with better explanations of each step, can be found <a href="https://indiareads/community/tutorials/additional-recommended-steps-for-new-ubuntu-14-04-servers#configure-timezones-and-network-time-protocol-synchronization">here</a>.</p>

<h2 id="step-1-list-available-timezones">Step 1: List available timezones</h2>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">timedatectl list-timezones
</li></ul></code></pre>
<ul>
<li>Press <strong>Space</strong> to scroll to the next page, <strong>b</strong> to scroll back a page.</li>
<li>Once you find the timezone you want to use, press <strong>q</strong> to go back to the command line.</li>
</ul>

<h2 id="step-2-set-the-desired-timezone">Step 2: Set the desired timezone</h2>

<p>Be sure to replace <span class="highlight">desired_timezone</span> with the timezone you selected from the list:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo timedatectl set-timezone <span class="highlight">desired_timezone</span>
</li></ul></code></pre>
<p>For example, to set the timezone to New York use this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo timedatectl set-timezone <span class="highlight">America/New_York</span>
</li></ul></code></pre>
<h2 id="step-3-verify-that-the-timezone-has-been-set-properly">Step 3: Verify that the timezone has been set properly</h2>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">timedatectl
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Example output:">Example output:</div>      Local time: Fri 2016-03-25 12:00:43 EDT
  Universal time: Fri 2016-03-25 16:00:43 UTC
        Timezone: America/New_York (EDT, -0400)
. . .
</code></pre>
<h2 id="step-4-install-ntp">Step 4: Install NTP</h2>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install ntp
</li></ul></code></pre>
<p>Once the NTP package installation is completed, your server will have NTP synchronization enabled!</p>

<h2 id="related-tutorials">Related Tutorials</h2>

<p>Here is a link to a more detailed tutorial that is related to this guide:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/additional-recommended-steps-for-new-ubuntu-14-04-servers">Additional Recommended Steps for New Ubuntu 14.04 Servers</a></li>
</ul>

    