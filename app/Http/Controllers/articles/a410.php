<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/swap__tw_%284%29.jpg?1461609421/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>One of the easiest way of increasing the responsiveness of your server and guarding against out-of-memory errors in applications is to add some swap space.  In this guide, we will cover how to add a swap file to an Ubuntu 16.04 server.</p>

<div class="code-label notes-and-warnings warning" title="Warning">Warning</div><span class="warning"><p>

Although swap is generally recommended for systems utilizing traditional spinning hard drives, using swap with SSDs can cause issues with hardware degradation over time.  Due to this consideration, we do not recommend enabling swap on IndiaReads or any other provider that utilizes SSD storage.  Doing so can impact the reliability of the underlying hardware for you and your neighbors.  This guide is provided as reference for users who may have spinning disk systems elsewhere.</p>

<p>If you need to improve the performance of your server on IndiaReads, we recommend upgrading your Droplet.  This will lead to better results in general and will decrease the likelihood of contributing to hardware issues that can affect your service.<br /></p></span>

<h2 id="what-is-swap">What is Swap?</h2>

<p><strong>Swap</strong> is an area on a hard drive that has been designated as a place where the operating system can temporarily store data that it can no longer hold in RAM.  Basically, this gives you the ability to increase the amount of information that your server can keep in its working "memory", with some caveats.  The swap space on the hard drive will be used mainly when there is no longer sufficient space in RAM to hold in-use application data.</p>

<p>The information written to disk will be significantly slower than information kept in RAM, but the operating system will prefer to keep running application data in memory and use swap for the older data.  Overall, having swap space as a fall back for when your system's RAM is depleted can be a good safety net against out-of-memory exceptions on systems with non-SSD storage available.</p>

<h2 id="check-the-system-for-swap-information">Check the System for Swap Information</h2>

<p>Before we begin, we can check if the system already has some swap space available.  It is possible to have multiple swap files or swap partitions, but generally one should be enough.</p>

<p>We can see if the system has any configured swap by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo swapon --show
</li></ul></code></pre>
<p>If you don't get back any output, this means your system does not have swap space available currently.</p>

<p>You can verify that there is no active swap using the <code>free</code> utility:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">free -h
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>              total        used        free      shared  buff/cache   available
Mem:           488M         36M        104M        652K        348M        426M
<span class="highlight">Swap:            0B          0B          0B</span>
</code></pre>
<p>As you can see in the "Swap" row of the output, no swap is active on the system.</p>

<h2 id="check-available-space-on-the-hard-drive-partition">Check Available Space on the Hard Drive Partition</h2>

<p>The most common way of allocating space for swap is to use a separate partition devoted to the task.  However, altering the partitioning scheme is not always possible.  We can just as easily create a swap file that resides on an existing partition.</p>

<p>Before we do this, we should check the current disk usage by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">df -h
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Filesystem      Size  Used Avail Use% Mounted on
udev            238M     0  238M   0% /dev
tmpfs            49M  624K   49M   2% /run
<span class="highlight">/dev/vda1        20G  1.1G   18G   6% /</span>
tmpfs           245M     0  245M   0% /dev/shm
tmpfs           5.0M     0  5.0M   0% /run/lock
tmpfs           245M     0  245M   0% /sys/fs/cgroup
tmpfs            49M     0   49M   0% /run/user/1001
</code></pre>
<p>The device under <code>/dev</code> is our disk in this case.  We have plenty of space available in this example (only 1.1G used).  Your usage will probably be different.</p>

<p>Although there are many opinions about the appropriate size of a swap space, it really depends on your personal preferences and your application requirements.  Generally, an amount equal to or double the amount of RAM on your system is a good starting point.  Another good rule of thumb is that anything over 4G of swap is probably unnecessary if you are just using it as a RAM fallback.</p>

<h2 id="create-a-swap-file">Create a Swap File</h2>

<p>Now that we know our available hard drive space, we can go about creating a swap file within our filesystem.  We will create a file of the swap size that we want called <code>swapfile</code> in our root (/) directory.</p>

<p>The best way of creating a swap file is with the <code>fallocate</code> program.  This command creates a file of a preallocated size instantly.</p>

<p>Since the server in our example has 512MB of RAM, we will create a 1 Gigabyte file in this guide.  Adjust this to meet the needs of your own server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo fallocate -l <span class="highlight">1G</span> /swapfile
</li></ul></code></pre>
<p>We can verify that the correct amount of space was reserved by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -lh /swapfile
</li></ul></code></pre><pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">-rw-r--r-- 1 root root 1.0G Apr 25 11:14 /swapfile
</li></ul></code></pre>
<p>Our file has been created with the correct amount of space set aside.</p>

<h2 id="enabling-the-swap-file">Enabling the Swap File</h2>

<p>Now that we have a file of the correct size available, we need to actually turn this into swap space.</p>

<p>First, we need to lock down the permissions of the file so that only the users with <code>root</code> privileges can read the contents.  This prevents normal users from being able to access the file, which would have significant security implications.</p>

<p>Make the file only accessible to <code>root</code> by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo chmod 600 /swapfile
</li></ul></code></pre>
<p>Verify the permissions change by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ls -lh /swapfile
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div><span class="highlight">-rw-------</span> 1 root root 1.0G Apr 25 11:14 /swapfile
</code></pre>
<p>As you can see, only the root user has the read and write flags enabled.</p>

<p>We can now mark the file as swap space by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkswap /swapfile
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Setting up swapspace version 1, size = 1024 MiB (1073737728 bytes)
no label, UUID=6e965805-2ab9-450f-aed6-577e74089dbf
</code></pre>
<p>After marking the file, we can enable the swap file, allowing our system to start utilizing it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo swapon /swapfile
</li></ul></code></pre>
<p>We can verify that the swap is available by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo swapon --show
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>NAME      TYPE  SIZE USED PRIO
/swapfile file 1024M   0B   -1
</code></pre>
<p>We can check the output of the <code>free</code> utility again to corroborate our findings:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">free -h
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>              total        used        free      shared  buff/cache   available
Mem:           488M         37M         96M        652K        354M        425M
<span class="highlight">Swap:          1.0G          0B        1.0G</span>
</code></pre>
<p>Our swap has been set up successfully and our operating system will begin to use it as necessary.</p>

<h2 id="make-the-swap-file-permanent">Make the Swap File Permanent</h2>

<p>Our recent changes have enabled the swap file for the current session.  However, if we reboot, the server will not retain the swap settings automatically.  We can change this by adding the swap file to our <code>/etc/fstab</code> file.</p>

<p>Back up the <code>/etc/fstab</code> file in case anything goes wrong:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /etc/fstab /etc/fstab.bak
</li></ul></code></pre>
<p>You can add the swap file information to the end of your <code>/etc/fstab</code> file by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
</li></ul></code></pre>
<h2 id="tweak-your-swap-settings">Tweak your Swap Settings</h2>

<p>There are a few options that you can configure that will have an impact on your system's performance when dealing with swap.</p>

<h3 id="adjusting-the-swappiness-property">Adjusting the Swappiness Property</h3>

<p>The <code>swappiness</code> parameter configures how often your system swaps data out of RAM to the swap space.  This is a value between 0 and 100 that represents a percentage.</p>

<p>With values close to zero, the kernel will not swap data to the disk unless absolutely necessary.  Remember, interactions with the swap file are "expensive" in that they take a lot longer than interactions with RAM and they can cause a significant reduction in performance.  Telling the system not to rely on the swap much will generally make your system faster.</p>

<p>Values that are closer to 100 will try to put more data into swap in an effort to keep more RAM space free.  Depending on your applications' memory profile or what you are using your server for, this might be better in some cases.</p>

<p>We can see the current swappiness value by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /proc/sys/vm/swappiness
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>60
</code></pre>
<p>For a Desktop, a swappiness setting of 60 is not a bad value.  For a server, you might want to move it closer to 0.</p>

<p>We can set the swappiness to a different value by using the <code>sysctl</code> command.</p>

<p>For instance, to set the swappiness to 10, we could type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sysctl vm.swappiness=10
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>vm.swappiness = 10
</code></pre>
<p>This setting will persist until the next reboot.  We can set this value automatically at restart by adding the line to our <code>/etc/sysctl.conf</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/sysctl.conf
</li></ul></code></pre>
<p>At the bottom, you can add:</p>
<div class="code-label " title="/etc/sysctl.conf">/etc/sysctl.conf</div><pre class="code-pre "><code langs="">vm.swappiness=10
</code></pre>
<p>Save and close the file when you are finished.</p>

<h3 id="adjusting-the-cache-pressure-setting">Adjusting the Cache Pressure Setting</h3>

<p>Another related value that you might want to modify is the <code>vfs_cache_pressure</code>.  This setting configures how much the system will choose to cache inode and dentry information over other data. </p>

<p>Basically, this is access data about the filesystem.  This is generally very costly to look up and very frequently requested, so it's an excellent thing for your system to cache.  You can see the current value by querying the <code>proc</code> filesystem again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cat /proc/sys/vm/vfs_cache_pressure
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>100
</code></pre>
<p>As it is currently configured, our system removes inode information from the cache too quickly.  We can set this to a more conservative setting like 50 by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo sysctl vm.vfs_cache_pressure=50
</li></ul></code></pre><pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>vm.vfs_cache_pressure = 50
</code></pre>
<p>Again, this is only valid for our current session.  We can change that by adding it to our configuration file like we did with our swappiness setting:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo nano /etc/sysctl.conf
</li></ul></code></pre>
<p>At the bottom, add the line that specifies your new value:</p>
<div class="code-label " title="/etc/sysctl.conf">/etc/sysctl.conf</div><pre class="code-pre "><code langs="">vm.vfs_cache_pressure=50
</code></pre>
<p>Save and close the file when you are finished.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Following the steps in this guide will give you some breathing room in cases that would otherwise lead to out-of-memory exceptions.  Swap space can be incredibly useful in avoiding some of these common problems.</p>

<p>If you are running into OOM (out of memory) errors, or if you find that your system is unable to use the applications you need, the best solution is to optimize your application configurations or upgrade your server.</p>

    