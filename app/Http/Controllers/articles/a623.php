<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/java_CentOS-Fedora.png?1441228851/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial will show you how to install Java on CentOS 7 (also 5, 6, 6.5), Fedora 20, and RHEL. Java is a popular software platform that allows you to run Java applications and applets. </p>

<p>The installation of the following versions of Java are covered:</p>

<ul>
<li>OpenJDK 7</li>
<li>OpenJDK 6</li>
<li>Oracle Java 8</li>
<li>Oracle Java 7</li>
</ul>

<p>Feel free to skip to your desired section using the <strong>Contents</strong> button on the sidebar!</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you begin this guide, you should have a regular, non-root user with <code>sudo</code> privileges configured on both of your servers--this is the user that you should log in to your servers as. You can learn how to configure a regular user account by following steps 1-4 in our <a href="https://indiareads/community/articles/initial-server-setup-with-ubuntu-14-04">initial server setup guide for Ubuntu 14.04</a>.</p>

<h2 id="variations-of-java">Variations of Java</h2>

<p>There are three different editions of the Java Platform: Standard Edition (SE), Enterprise Edition (EE), and Micro Edition (ME). This tutorial is focused on Java SE (Java Platform, Standard Edition).</p>

<p>There are two different Java SE packages that can be installed: the Java Runtime Environment (JRE) and the Java Development Kit (JDK). JRE is an implementation of the Java Virtual Machine (JVM), which allows you to run compiled Java applications and applets. JDK includes JRE and other software that is required for writing, developing, and compiling Java applications and applets.</p>

<p>There are also two different implementations of Java: OpenJDK and Oracle Java. Both implementations are based largely on the same code but OpenJDK, the reference implementation of Java, is fully open source while Oracle Java contains some proprietary code. Most Java applications will work fine with either but you should use whichever implementation your software calls for.</p>

<p>You may install various versions and releases of Java on a single system, but most people only need one installation. With that in mind, try to only install the version of Java that you need to run or develop your application(s).</p>

<h2 id="install-openjdk-7">Install OpenJDK 7</h2>

<p>This section will show you how to install the prebuilt OpenJDK 7 JRE and JDK packages using the yum package manager, which is similar to apt-get for Ubuntu/Debian. OpenJDK 7 is the latest version of OpenJDK.</p>

<h3 id="install-openjdk-7-jre">Install OpenJDK 7 JRE</h3>

<p>To install OpenJDK 7 <strong>JRE</strong> using yum, run this command:</p>
<pre class="code-pre "><code langs="">sudo yum install java-1.7.0-openjdk
</code></pre>
<p>At the confirmation prompt, enter <code>y</code> then <code>RETURN</code> to continue with the installation.</p>

<p>Congratulations! You have installed OpenJDK 7 JRE.</p>

<h3 id="install-openjdk-7-jdk">Install OpenJDK 7 JDK</h3>

<p>To install OpenJDK 7 <strong>JDK</strong> using yum, run this command:</p>
<pre class="code-pre "><code langs="">sudo yum install java-1.7.0-openjdk-devel
</code></pre>
<p>At the confirmation prompt, enter <code>y</code> then <code>RETURN</code> to continue with the installation.</p>

<p>Congratulations! You have installed OpenJDK 7 JDK.</p>

<h2 id="install-openjdk-6">Install OpenJDK 6</h2>

<p>This section will show you how to install the prebuilt OpenJDK 6 JRE and JDK packages using the yum package manager.</p>

<h3 id="install-openjdk-6">Install OpenJDK 6</h3>

<p>To install OpenJDK 6 <strong>JRE</strong> using yum, run this command:</p>
<pre class="code-pre "><code langs="">sudo yum install java-1.6.0-openjdk
</code></pre>
<p>At the confirmation prompt, enter <code>y</code> then <code>RETURN</code> to continue with the installation.</p>

<p>Congratulations! You have installed OpenJDK 6 JRE.</p>

<h3 id="install-openjdk-6-jdk">Install OpenJDK 6 JDK</h3>

<p>To install OpenJDK 6 <strong>JDK</strong> using yum, run this command:</p>
<pre class="code-pre "><code langs="">sudo yum install java-1.6.0-openjdk-devel
</code></pre>
<p>At the confirmation prompt, enter <code>y</code> then <code>RETURN</code> to continue with the installation.</p>

<p>Congratulations! You have installed OpenJDK 6 JDK.</p>

<h2 id="install-oracle-java-8">Install Oracle Java 8</h2>

<p>This section of the guide will show you how to install Oracle Java 8 update 60 JRE and JDK (64-bit), the latest release of these packages at the time of this writing.</p>

<p><strong>Note:</strong> You must accept the Oracle Binary Code License Agreement for Java SE, which is one of the included steps, before installing Oracle Java.</p>

<h3 id="install-oracle-java-8-jre">Install Oracle Java 8 JRE</h3>

<p><strong>Note:</strong> If you would like to install a different release of Oracle Java 8 JRE, go to the <a href="http://www.oracle.com/technetwork/java/javase/downloads/jre8-downloads-2133155.html">Oracle Java 8 JRE Downloads Page</a>, accept the license agreement, and copy the download link of the appropriate Linux <code>.rpm</code> package. Substitute the copied download link in place of the highlighted part of the <code>wget</code> command.</p>

<p>Change to your home directory and download the Oracle Java 8 JRE RPM with these commands:</p>
<pre class="code-pre "><code langs="">cd ~
wget --no-cookies --no-check-certificate --header "Cookie: gpw_e24=http%3A%2F%2Fwww.oracle.com%2F; oraclelicense=accept-securebackup-cookie" \
"<span class="highlight">http://download.oracle.com/otn-pub/java/jdk/8u60-b27/jre-8u60-linux-x64.rpm</span>"
</code></pre>
<p>Then install the RPM with this yum command (if you downloaded a different release, substitute the filename here):</p>
<pre class="code-pre "><code langs="">sudo yum localinstall <span class="highlight">jre-8u60-linux-x64</span>.rpm
</code></pre>
<p>Now Java should be installed at <code>/usr/java/<span class="highlight">jdk1.8.0_60</span>/jre/bin/java</code>, and linked from <code>/usr/bin/java</code>.</p>

<p>You may delete the archive file that you downloaded earlier:</p>
<pre class="code-pre "><code langs="">rm ~/<span class="highlight">jre-8u60-linux-x64.rpm</span>
</code></pre>
<p>Congratulations! You have installed Oracle Java 8 JRE.</p>

<h3 id="install-oracle-java-8-jdk">Install Oracle Java 8 JDK</h3>

<p><strong>Note:</strong> If you would like to install a different release of Oracle Java 8 JDK, go to the <a href="http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html">Oracle Java 8 JDK Downloads Page</a>, accept the license agreement, and copy the download link of the appropriate Linux <code>.rpm</code> package. Substitute the copied download link in place of the highlighted part of the <code>wget</code> command.</p>

<p>Change to your home directory and download the Oracle Java 8 JDK RPM with these commands:</p>
<pre class="code-pre "><code langs="">cd ~
wget --no-cookies --no-check-certificate --header "Cookie: gpw_e24=http%3A%2F%2Fwww.oracle.com%2F; oraclelicense=accept-securebackup-cookie" "<span class="highlight">http://download.oracle.com/otn-pub/java/jdk/8u60-b27/jdk-8u60-linux-x64.rpm</span>"
</code></pre>
<p>Then install the RPM with this yum command (if you downloaded a different release, substitute the filename here):</p>
<pre class="code-pre "><code langs="">sudo yum localinstall <span class="highlight">jdk-8u60-linux-x64</span>.rpm
</code></pre>
<p>Now Java should be installed at <code>/usr/java/<span class="highlight">jdk1.8.0_60</span>/jre/bin/java</code>, and linked from <code>/usr/bin/java</code>.</p>

<p>You may delete the archive file that you downloaded earlier:</p>
<pre class="code-pre "><code langs="">rm ~/<span class="highlight">jdk-8u60-linux-x64.rpm</span>
</code></pre>
<p>Congratulations! You have installed Oracle Java 8 JDK.</p>

<h2 id="install-oracle-java-7">Install Oracle Java 7</h2>

<p>This section of the guide will show you how to install Oracle Java 7 update 79 JRE and JDK (64-bit).</p>

<p><strong>Note:</strong> You must accept the Oracle Binary Code License Agreement for Java SE, which is one of the included steps, before installing Oracle Java.</p>

<h3 id="install-oracle-java-7-jre">Install Oracle Java 7 JRE</h3>

<p><strong>Note:</strong> If you would like to install a different release of Oracle Java 7 JRE, go to the <a href="http://www.oracle.com/technetwork/java/javase/downloads/jre7-downloads-1880261.html">Oracle Java 7 JRE Downloads Page</a>, accept the license agreement, and copy the download link of the appropriate Linux <code>.rpm</code> package. Substitute the copied download link in place of the highlighted part of the <code>wget</code> command.</p>

<p>Change to your home directory and download the Oracle Java 7 JRE RPM with these commands:</p>
<pre class="code-pre "><code langs="">cd ~
wget --no-cookies --no-check-certificate --header "Cookie: gpw_e24=http%3A%2F%2Fwww.oracle.com%2F; oraclelicense=accept-securebackup-cookie" "<span class="highlight">http://download.oracle.com/otn-pub/java/jdk/7u79-b15/jre-7u79-linux-x64.rpm</span>"
</code></pre>
<p>Then install the RPM with this yum command (if you downloaded a different release, substitute the filename here):</p>
<pre class="code-pre "><code langs="">sudo yum localinstall <span class="highlight">jre-7u79-linux-x64</span>.rpm
</code></pre>
<p>Now Java should be installed at <code>/usr/java/<span class="highlight">jdk1.7.0_79</span>/jre/bin/java</code>, and linked from <code>/usr/bin/java</code>.</p>

<p>You may delete the archive file that you downloaded earlier:</p>
<pre class="code-pre "><code langs="">rm ~/<span class="highlight">jre-7u79-linux-x64.rpm</span>
</code></pre>
<p>Congratulations! You have installed Oracle Java 7 JRE.</p>

<h3 id="install-oracle-java-7-jdk">Install Oracle Java 7 JDK</h3>

<p><strong>Note:</strong> If you would like to install a different release of Oracle Java 7 JDK, go to the <a href="http://www.oracle.com/technetwork/java/javase/downloads/jdk7-downloads-1880260.html">Oracle Java 7 JDK Downloads Page</a>, accept the license agreement, and copy the download link of the appropriate Linux <code>.rpm</code> package. Substitute the copied download link in place of the highlighted part of the <code>wget</code> command.</p>

<p>Change to your home directory and download the Oracle Java 7 JDK RPM with these commands:</p>
<pre class="code-pre "><code langs="">cd ~
wget --no-cookies --no-check-certificate --header "Cookie: gpw_e24=http%3A%2F%2Fwww.oracle.com%2F; oraclelicense=accept-securebackup-cookie" "<span class="highlight">http://download.oracle.com/otn-pub/java/jdk/7u79-b15/jdk-7u79-linux-x64.rpm</span>"
</code></pre>
<p>Then install the RPM with this yum command (if you downloaded a different release, substitute the filename here):</p>
<pre class="code-pre "><code langs="">sudo yum localinstall <span class="highlight">jdk-7u79-linux-x64</span>.rpm
</code></pre>
<p>Now Java should be installed at <code>/usr/java/<span class="highlight">jdk1.7.0_79</span>/jre/bin/java</code>, and linked from <code>/usr/bin/java</code>.</p>

<p>You may delete the archive file that you downloaded earlier:</p>
<pre class="code-pre "><code langs="">rm ~/<span class="highlight">jdk-7u79-linux-x64.rpm</span>
</code></pre>
<p>Congratulations! You have installed Oracle Java 7 JDK.</p>

<h2 id="set-default-java">Set Default Java</h2>

<p>If you installed multiple versions of Java, you may want to set one as your default (i.e. the one that will run when a user runs the <code>java</code> command). Additionally, some applications require certain environment variables to be set to locate which installation of Java to use. This section will show you how to do this.</p>

<p>By the way, to check the version of your default Java, run this command:</p>
<pre class="code-pre "><code langs="">java -version
</code></pre>
<h3 id="using-alternatives">Using Alternatives</h3>

<p>The <code>alternatives</code> command, which manages default commands through symbolic links, can be used to select the default Java command.</p>

<p>To print the programs that provide the <code>java</code> command that are managed by <code>alternatives</code>, use this command:</p>
<pre class="code-pre "><code langs="">sudo alternatives --config java
</code></pre>
<p>Here is an example of the output:</p>
<pre class="code-pre "><code langs="">There are 5 programs which provide 'java'.

  Selection    Command
-----------------------------------------------
*+ 1           /usr/java/jdk1.8.0_60/jre/bin/java
   2           /usr/java/jdk1.7.0_79/jre/bin/java


Enter to keep the current selection[+], or type selection number: 
</code></pre>
<p>Simply enter the a selection number to choose which <code>java</code> executable should be used by default.</p>

<h3 id="using-environment-variables">Using Environment Variables</h3>

<p>Many Java applications use the <code>JAVA_HOME</code> or <code>JRE_HOME</code> environment variables to determine which <code>java</code> executable to use.</p>

<p>For example, if you installed Java to <code><span class="highlight">/usr/java/jdk1.8.0_60/jre/bin</span></code> (i.e. <code>java</code> executable is located at <code><span class="highlight">/usr/java/jdk1.8.0_60/jre</span>/bin/java</code>), you could set your <code>JAVA_HOME</code> environment variable in a bash shell or script like so:</p>
<pre class="code-pre "><code langs="">export JAVA_HOME=<span class="highlight">/usr/java/jdk1.8.0_60/jre</span>
</code></pre>
<p>If you want <code>JAVA_HOME</code> to be set for every user on the system by default, add the previous line to the <code>/etc/environment</code> file. An easy way to append it to the file is to run this command:</p>
<pre class="code-pre "><code langs="">sudo sh -c "echo export JAVA_HOME=<span class="highlight">/usr/java/jdk1.8.0_60/jre</span> >> /etc/environment"
</code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>Congratulations, you are now set to run and/or develop your Java applications!</p>

    