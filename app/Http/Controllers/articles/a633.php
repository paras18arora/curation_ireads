<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/java_tw.jpg?1434576218/> <br> 
      <h3 id="introduction">Introduction</h3>

<hr />

<p>As a lot of articles and programs require to have Java installed, this article will guide you through the process of installing and managing different versions of Java.</p>

<h2 id="installing-default-jre-jdk">Installing default JRE/JDK</h2>

<hr />

<p>This is the recommended and easiest option. This will install OpenJDK 6 on Ubuntu 12.04 and earlier and on 12.10+ it will install OpenJDK 7.</p>

<p>Installing Java with <code>apt-get</code> is easy. First, update the package index:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Then, check if Java is not already installed:</p>
<pre class="code-pre "><code langs="">java -version
</code></pre>
<p>If it returns "The program java can be found in the following packages", Java hasn't been installed yet, so execute the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install default-jre
</code></pre>
<p>This will install the Java Runtime Environment (JRE). If you instead need the Java Development Kit (JDK), which is usually needed to compile Java applications (for example  <a href="http://ant.apache.org/">Apache Ant</a>, <a href="http://maven.apache.org/">Apache Maven</a>, <a href="https://www.eclipse.org/">Eclipse</a> and <a href="http://www.jetbrains.com/idea/,%20etc.">IntelliJ IDEA</a> execute the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install default-jdk
</code></pre>
<p>That is everything that is needed to install Java. </p>

<p>All other steps are optional and must only be executed when needed.</p>

<h2 id="installing-openjdk-7-optional">Installing OpenJDK 7 (optional)</h2>

<hr />

<p>To install OpenJDK 7, execute the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install openjdk-7-jre 
</code></pre>
<p>This will install the Java Runtime Environment (JRE). If you instead need the Java Development Kit (JDK), execute the following command:</p>
<pre class="code-pre "><code langs="">sudo apt-get install openjdk-7-jdk
</code></pre>
<h2 id="installing-oracle-jdk-optional">Installing Oracle JDK (optional)</h2>

<hr />

<p>The Oracle JDK is the official JDK; however, it is no longer provided by Oracle as a default installation for Ubuntu. </p>

<p>You can still install it using apt-get. To install any version, first execute the following commands:</p>
<pre class="code-pre "><code langs="">sudo apt-get install python-software-properties
sudo add-apt-repository ppa:webupd8team/java
sudo apt-get update
</code></pre>
<p>Then, depending on the version you want to install, execute one of the following commands:</p>

<h3 id="oracle-jdk-6">Oracle JDK 6</h3>

<hr />

<p>This is an old version but still in use.</p>
<pre class="code-pre "><code langs="">sudo apt-get install oracle-java6-installer
</code></pre>
<h3 id="oracle-jdk-7">Oracle JDK 7</h3>

<hr />

<p>This is the latest stable version.</p>
<pre class="code-pre "><code langs="">sudo apt-get install oracle-java7-installer
</code></pre>
<h3 id="oracle-jdk-8">Oracle JDK 8</h3>

<hr />

<p>This is a developer preview, the general release is scheduled for March 2014. This <a href="http://www.techempower.com/blog/2013/03/26/everything-about-java-8/">external article about Java 8</a> may help you to understand what it's all about.</p>
<pre class="code-pre "><code langs="">sudo apt-get install oracle-java8-installer
</code></pre>
<h2 id="managing-java-optional">Managing Java (optional)</h2>

<hr />

<p>When there are multiple Java installations on your Droplet, the Java version to use as default can be chosen. To do this, execute the following command:</p>
<pre class="code-pre "><code langs="">sudo update-alternatives --config java
</code></pre>
<p>It will usually return something like this if you have 2 installations (if you have more, it will of course return more):</p>
<pre class="code-pre "><code langs="">There are 2 choices for the alternative java (providing /usr/bin/java).

Selection    Path                                            Priority   Status
------------------------------------------------------------
* 0            /usr/lib/jvm/java-7-oracle/jre/bin/java          1062      auto mode
  1            /usr/lib/jvm/java-6-openjdk-amd64/jre/bin/java   1061      manual mode
  2            /usr/lib/jvm/java-7-oracle/jre/bin/java          1062      manual mode

Press enter to keep the current choice[*], or type selection number:
</code></pre>
<p>You can now choose the number to use as default. This can also be done for the Java compiler (<code>javac</code>):</p>
<pre class="code-pre "><code langs="">sudo update-alternatives --config javac
</code></pre>
<p>It is the same selection screen as the previous command and should be used in the same way. This command can be executed for all other commands which have different installations. In Java, this includes but is not limited to: <code>keytool</code>, <code>javadoc</code> and <code>jarsigner</code>.</p>

<h2 id="setting-the-quot-java_home-quot-environment-variable">Setting the "JAVA_HOME" environment variable</h2>

<hr />

<p>To set the <code>JAVA_HOME</code> environment variable, which is needed for some programs, first find out the path of your Java installation:</p>
<pre class="code-pre "><code langs="">sudo update-alternatives --config java
</code></pre>
<p>It returns something like:</p>
<pre class="code-pre "><code langs="">There are 2 choices for the alternative java (providing /usr/bin/java).

Selection    Path                                            Priority   Status
------------------------------------------------------------
* 0            /usr/lib/jvm/java-7-oracle/jre/bin/java          1062      auto mode
  1            /usr/lib/jvm/java-6-openjdk-amd64/jre/bin/java   1061      manual mode
  2            /usr/lib/jvm/java-7-oracle/jre/bin/java          1062      manual mode

Press enter to keep the current choice[*], or type selection number:
</code></pre>
<p>The path of the installation is for each:</p>

<ol>
<li><p><code>/usr/lib/jvm/java-7-oracle</code></p></li>
<li><p><code>/usr/lib/jvm/java-6-openjdk-amd64</code></p></li>
<li><p><code>/usr/lib/jvm/java-7-oracle</code></p></li>
</ol>

<p>Copy the path from your preferred installation and then edit the file <code>/etc/environment</code>:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/environment
</code></pre>
<p>In this file, add the following line (replacing YOUR_PATH by the just copied path):</p>
<pre class="code-pre "><code langs="">JAVA_HOME="YOUR_PATH"
</code></pre>
<p>That should be enough to set the environment variable. Now reload this file:</p>
<pre class="code-pre "><code langs="">source /etc/environment
</code></pre>
<p>Test it by executing:</p>
<pre class="code-pre "><code langs="">echo $JAVA_HOME
</code></pre>
<p>If it returns the just set path, the environment variable has been set successfully. If it doesn't, please make sure you followed all steps correctly.</p>

<div class="author">Submitted by: <a href="http://koenv.com">Koen Vlaswinkel</a></div>

    