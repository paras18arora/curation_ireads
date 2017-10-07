<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Java is a programming technology originally developed by Sun Microsystems and later acquired by Oracle. Oracle Java is a proprietary implementation for Java that is free to download and use for commercial use, but not to redistribute, therefore it is not included in a officially maintained repository.</p>

<p>There are many reasons why you would want to install Oracle Java over OpenJDK. In this tutorial, we will not discuss the differences between the above mentioned implementations.</p>

<h2 id="assumptions">Assumptions</h2>

<p>This tutorial assumes that you have an account with IndiaReads, as well as a Droplet running Debian 7 or Ubuntu 12.04 or above. You will need root privileges (via sudo) to complete the tutorial.</p>

<p>You will need to know whether you are running a 32 bit or a 64 bit OS:</p>
<pre class="code-pre "><code langs="">uname -m
</code></pre>
<ul>
<li><p><strong>x86_64</strong>: 64 bit kernel</p></li>
<li><p><strong>i686</strong>: 32 bit kernel</p></li>
</ul>

<h2 id="downloading-oracle-java-jdk">Downloading Oracle Java JDK</h2>

<p>Using your web browser, go to the <a href="http://www.oracle.com/technetwork/java/javase/downloads/index.html">Oracle Java SE (Standard Edition) website</a> and decide which version you want to install:</p>

<ul>
<li><p><strong>JDK:</strong> Java Development Kit. Includes a complete JRE plus tools for developing, debugging, and monitoring Java applications.</p></li>
<li><p><strong>Server JRE:</strong> Java Runtime Environment. For deploying Java applications on servers. Includes tools for JVM monitoring and tools commonly required for server applications.</p></li>
</ul>

<p>In this tutorial we will be installing the JDK Java SE Development Kit 8 x64 bits. Accept the license and <strong>copy the download link</strong> into your clipboard. Remember to <strong>choose the right tar.gz</strong> (64 or 32 bits). Use wget to download the archive into your server:</p>
<pre class="code-pre "><code langs="">    wget --header "Cookie: oraclelicense=accept-securebackup-cookie" http://download.oracle.com/otn-pub/java/jdk/8u5-b13/jdk-8u5-linux-x64.tar.gz
</code></pre>
<p>Oracle does not allow downloads without accepting their license, therefore we needed to modify the header of our request. Alternatively, you can just download the compressed file using your browser and manually upload it using a SFTP/FTP client.</p>

<p><strong>Always get the latest version from Oracle's website</strong> and modify the commands from this tutorial accordingly to your downloaded file.</p>

<h2 id="installing-oracle-jdk">Installing Oracle JDK</h2>

<p>In this section, you will need sudo privileges:</p>
<pre class="code-pre "><code langs="">    sudo su
</code></pre>
<p>The <strong>/opt</strong> directory is reserved for all the software and add-on packages that are not part of the default installation. Create a directory for your JDK installation:</p>
<pre class="code-pre "><code langs="">    mkdir /opt/jdk
</code></pre>
<p>and extract java into the <strong>/opt/jdk</strong> directory:</p>
<pre class="code-pre "><code langs="">    tar -zxf jdk-8u5-linux-x64.tar.gz -C /opt/jdk
</code></pre>
<p>Verify that the file has been extracted into the <strong>/opt/jdk</strong> directory.</p>
<pre class="code-pre "><code langs="">    ls /opt/jdk
</code></pre>
<h2 id="setting-oracle-jdk-as-the-default-jvm">Setting Oracle JDK as the default JVM</h2>

<p>In our case, the java executable is located under <strong>/opt/jdk/jdk1.8.0_05/bin/java</strong> . To set it as the default JVM in your machine run:</p>
<pre class="code-pre "><code langs="">    update-alternatives --install /usr/bin/java java /opt/jdk/jdk1.8.0_05/bin/java 100
</code></pre>
<p>and</p>
<pre class="code-pre "><code langs="">    update-alternatives --install /usr/bin/javac javac /opt/jdk/jdk1.8.0_05/bin/javac 100
</code></pre>
<h2 id="verify-your-installation">Verify your installation</h2>

<p>Verify that java has been successfully configured by running:</p>
<pre class="code-pre "><code langs="">    update-alternatives --display java
</code></pre>
<p>and</p>
<pre class="code-pre "><code langs="">    update-alternatives --display javac
</code></pre>
<p>The output should look like this:</p>
<pre class="code-pre "><code langs="">    java - auto mode
    link currently points to /opt/jdk/jdk1.8.0_05/bin/java
    /opt/jdk/jdk1.8.0_05/bin/java - priority 100
    Current 'best' version is '/opt/jdk/jdk1.8.0_05/bin/java'.

    javac - auto mode
    link currently points to /opt/jdk/jdk1.8.0_05/bin/javac
    /opt/jdk/jdk1.8.0_05/bin/javac - priority 100
    Current 'best' version is '/opt/jdk/jdk1.8.0_05/bin/javac'.
</code></pre>
<p>Another easy way to check your installation is:</p>
<pre class="code-pre "><code langs="">    java -version
</code></pre>
<p>The output should look like this:</p>
<pre class="code-pre "><code langs="">    java version "1.8.0_05"
    Java(TM) SE Runtime Environment (build 1.8.0_05-b13)
    Java HotSpot(TM) 64-Bit Server VM (build 25.5-b02, mixed mode)
</code></pre>
<h2 id="optional-updating-java">(Optional) Updating Java</h2>

<p>To update Java, simply download an updated version from Oracle's website and extract it under the <strong>/opt/jdk</strong> directory, then set it up as the default JVM with a higher priority number (in this case 110):</p>
<pre class="code-pre "><code langs="">    update-alternatives --install /usr/bin/java java /opt/jdk/jdk.new.version/bin/java 110
    update-alternatives --install /usr/bin/javac javac /opt/jdk/jdk.new.version/bin/javac 110
</code></pre>
<p>You can keep the old version or delete it:</p>
<pre class="code-pre "><code langs="">    update-alternatives --remove java /opt/jdk/jdk.old.version/bin/java
    update-alternatives --remove javac /opt/jdk/jdk.old.version/bin/javac

    rm -rf /opt/jdk/jdk.old.version
</code></pre>
<p>The installation procedure documented above is confirmed to work on a Debian server, but can also be applied to an Ubuntu server. If you encounter any problem after following all the steps, please post a comment below.</p>

<div class="author">Submitted by: <a rel="author" href="http://www.santiagoti.com">Santiago Ti</a></div>                                     

    