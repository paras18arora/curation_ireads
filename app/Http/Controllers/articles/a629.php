<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="prerequisites">Prerequisites</h3>

<p>The only prerequisite for this tutorial is a VPS with <strong>Ubuntu 13.10 x64</strong> installed.   </p>

<p>You will need to execute commands from the command line which you can do in one of the two ways:</p>

<ol>
<li><p>Use SSH to access the droplet.  </p></li>
<li><p>Use the 'Console Access' from the Digital Ocean Droplet Management Panel  </p></li>
</ol>

<h2 id="what-is-hadoop">What is Hadoop?</h2>

<p><a href="http://hadoop.apache.org/">Hadoop</a> is a framework (consisting of software libraries) which simplifies the processing of data sets distributed across clusters of servers. Two of the main components of Hadoop are <strong>HDFS</strong> and <strong>MapReduce</strong>.</p>

<p>HDFS is the filesystem that is used by Hadoop to store all the data on. This file system spans across all the nodes that are being used by Hadoop. These nodes could be on a single VPS or they can be spread across a large number of virtual servers.</p>

<p>MapReduce is the framework that orchestrates all of Hadoop's activities. It handles the assignment of work to different nodes in the cluster.</p>

<h2 id="benefits-of-using-hadoop">Benefits of using Hadoop</h2>

<p>The architecture of Hadoop allows you to scale your hardware as and when you need to. New nodes can be added incrementally without having to worry about the change in data formats or the handling of applications that sit on the file system.</p>

<p>One of the most important features of Hadoop is that it allows you to save enormous amounts of money by substituting cheap commodity servers for expensive ones. This is possible because Hadoop transfers the responsibility of fault tolerance from the hardware layer to the application layer.</p>

<h2 id="installing-hadoop">Installing Hadoop</h2>

<p>Installing and getting Hadoop up and running is quite straightforward. However, since this process requires editing multiple configuration and setup files, make sure that each step is properly followed.</p>

<h3 id="1-install-java">1. Install Java</h3>

<p>Hadoop requires Java to be installed, so let's begin by installing Java:</p>
<pre class="code-pre "><code langs="">apt-get update
apt-get install default-jdk
</code></pre>
<p>These commands will update the package information on your VPS and then install Java. After executing these commands, execute the following command to verify that Java has been installed:</p>
<pre class="code-pre "><code langs="">java -version
</code></pre>
<p>If Java has been installed, this should display the version details as illustrated in the following image:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/1.png" alt="Java Verification" title="Java Verification" /></p>

<h3 id="2-create-and-setup-ssh-certificates">2. Create and Setup SSH Certificates</h3>

<p>Hadoop uses SSH (to access its nodes) which would normally require the user to enter a password. However, this requirement can be eliminated by creating and setting up SSH certificates using the following commands:</p>
<pre class="code-pre "><code langs="">ssh-keygen -t rsa -P ''
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
</code></pre>
<p>After executing the first of these two commands, you might be asked for a filename. Just leave it blank and press the enter key to continue. The second command adds the newly created key to the list of authorized keys so that Hadoop can use SSH without prompting for a password.</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/2.png" alt="Setup SSH Certificates" title="Setup SSH Certificates" /></p>

<h3 id="3-fetch-and-install-hadoop">3. Fetch and Install Hadoop</h3>

<p>First let's fetch Hadoop from one of the mirrors using the following command:</p>
<pre class="code-pre "><code langs="">wget http://www.motorlogy.com/apache/hadoop/common/current/hadoop-2.3.0.tar.gz
</code></pre>
<p><strong>Note:</strong> <em>This command uses a download a link on one of the mirrors listed on the Hadoop website. The list of mirrors can be found <a href="http://www.apache.org/dyn/closer.cgi/hadoop/common/">on this link</a>. You can choose any other mirror if you want to. To download the latest stable version, choose the *</em>hadoop-X.Y.Z.tar.gz** file from the <strong>current</strong> or the <strong>current2</strong> directory on your chosen mirror.*</p>

<p>After downloading the Hadoop package, execute the following command to extract it:</p>
<pre class="code-pre "><code langs="">tar xfz hadoop-2.3.0.tar.gz
</code></pre>
<p>This command will extract all the files in this package in a directory named <code>hadoop-2.3.0</code>. For this tutorial, the Hadoop installation will be moved to the <code>/usr/local/hadoop</code> directory using the following command:</p>
<pre class="code-pre "><code langs="">mv hadoop-2.3.0 /usr/local/hadoop 
</code></pre>
<p><strong>Note:</strong> <em>The name of the extracted folder depends on the Hadoop version your have downloaded and extracted. If your version differs from the one used in this tutorial, change the above command accordingly.</em></p>

<h3 id="4-edit-and-setup-configuration-files">4. Edit and Setup Configuration Files</h3>

<p>To complete the setup of Hadoop, the following files will have to be modified:</p>

<ul>
<li>~/.bashrc</li>
<li>/usr/local/hadoop/etc/hadoop/hadoop-env.sh</li>
<li>/usr/local/hadoop/etc/hadoop/core-site.xml</li>
<li>/usr/local/hadoop/etc/hadoop/yarn-site.xml</li>
<li>/usr/local/hadoop/etc/hadoop/mapred-site.xml.template</li>
<li>/usr/local/hadoop/etc/hadoop/hdfs-site.xml</li>
</ul>

<h4 id="i-editing-bashrc">i. Editing ~/.bashrc</h4>

<p>Before editing the <code>.bashrc</code> file in your home directory, we need to find the path where Java has been installed to set the <code>JAVA_HOME</code> environment variable. Let's use the following command to do that:</p>
<pre class="code-pre "><code langs="">update-alternatives --config java
</code></pre>
<p>This will display something like the following:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/3.png" alt="Get Java Path" title="Get Java Path" /></p>

<p>The complete path displayed by this command is:</p>
<pre class="code-pre "><code langs="">/usr/lib/jvm/java-7-openjdk-amd64/jre/bin/java
</code></pre>
<p>The value for <code>JAVA_HOME</code> is everything before <code>/jre/bin/java</code> in the above path - in this case, <code>/usr/lib/jvm/java-7-openjdk-amd64</code>. Make a note of this as we'll be using this value in this step and in one other step.</p>

<p>Now use <code>nano</code> (or your favored editor) to edit ~/.bashrc using the following command:</p>
<pre class="code-pre "><code langs="">nano ~/.bashrc
</code></pre>
<p>This will open the <code>.bashrc</code> file in a text editor. Go to the end of the file and paste/type the following content in it:</p>
<pre class="code-pre "><code langs="">#HADOOP VARIABLES START
export JAVA_HOME=/usr/lib/jvm/java-7-openjdk-amd64
export HADOOP_INSTALL=/usr/local/hadoop
export PATH=$PATH:$HADOOP_INSTALL/bin
export PATH=$PATH:$HADOOP_INSTALL/sbin
export HADOOP_MAPRED_HOME=$HADOOP_INSTALL
export HADOOP_COMMON_HOME=$HADOOP_INSTALL
export HADOOP_HDFS_HOME=$HADOOP_INSTALL
export YARN_HOME=$HADOOP_INSTALL
export HADOOP_COMMON_LIB_NATIVE_DIR=$HADOOP_INSTALL/lib/native
export HADOOP_OPTS="-Djava.library.path=$HADOOP_INSTALL/lib"
#HADOOP VARIABLES END
</code></pre>
<p><strong>Note 1:</strong> <em>If the value of <code>JAVA_HOME</code> is different on your VPS, make sure to alter the first <code>export</code> statement in the above content accordingly.</em></p>

<p><strong>Note 2:</strong> <em>Files opened and edited using nano can be saved using <code>Ctrl + X</code>. Upon the prompt to save changes, type <code>Y</code>. If you are asked for a filename, just press the enter key.</em></p>

<p>The end of the <code>.bashrc</code> file should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/4.png" alt=".bashrc contents" title=".bashrc contens" /></p>

<p>After saving and closing the <code>.bashrc</code> file, execute the following command so that your system recognizes the newly created environment variables:</p>
<pre class="code-pre "><code langs="">source ~/.bashrc
</code></pre>
<p>Putting the above content in the <code>.bashrc</code> file ensures that these variables are always available when your VPS starts up.</p>

<h3 id="ii-editing-usr-local-hadoop-etc-hadoop-hadoop-env-sh">ii. Editing /usr/local/hadoop/etc/hadoop/hadoop-env.sh</h3>

<p>Open the <code>/usr/local/hadoop/etc/hadoop/hadoop-env.sh</code> file with nano using the following command:</p>
<pre class="code-pre "><code langs="">nano /usr/local/hadoop/etc/hadoop/hadoop-env.sh
</code></pre>
<p>In this file, locate the line that exports the <code>JAVA_HOME</code> variable. Change this line to the following:</p>
<pre class="code-pre "><code langs="">export JAVA_HOME=/usr/lib/jvm/java-7-openjdk-amd64
</code></pre>
<p><strong>Note:</strong> <em>If the value of <code>JAVA_HOME</code> is different on your VPS, make sure to alter this line accordingly.</em></p>

<p>The <code>hadoop-env.sh</code> file should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/5.png" alt="hadoop-env.sh contents" title="hadoop-env.sh contents" /></p>

<p>Save and close this file. Adding the above statement in the <code>hadoop-env.sh</code> file ensures that the value of <code>JAVA_HOME</code> variable will be available to Hadoop whenever it is started up.</p>

<h3 id="iii-editing-usr-local-hadoop-etc-hadoop-core-site-xml">iii. Editing /usr/local/hadoop/etc/hadoop/core-site.xml</h3>

<p>The <code>/usr/local/hadoop/etc/hadoop/core-site.xml</code> file contains configuration properties that Hadoop uses when starting up. This file can be used to override the default settings that Hadoop starts with. </p>

<p>Open this file with nano using the following command:</p>
<pre class="code-pre "><code langs="">nano /usr/local/hadoop/etc/hadoop/core-site.xml
</code></pre>
<p>In this file, enter the following content in between the <code><configuration></configuration></code> tag:</p>
<pre class="code-pre "><code langs=""><property>
   <name>fs.default.name</name>
   <value>hdfs://localhost:9000</value>
</property>
</code></pre>
<p>The <code>core-site.xml</code> file should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/6.png" alt="core-site.xml contents" title="core-site.xml contents" /></p>

<p>Save and close this file.</p>

<h3 id="iv-editing-usr-local-hadoop-etc-hadoop-yarn-site-xml">iv. Editing /usr/local/hadoop/etc/hadoop/yarn-site.xml</h3>

<p>The <code>/usr/local/hadoop/etc/hadoop/yarn-site.xml</code> file contains configuration properties that MapReduce uses when starting up. This file can be used to override the default settings that MapReduce starts with. </p>

<p>Open this file with nano using the following command:</p>
<pre class="code-pre "><code langs="">nano /usr/local/hadoop/etc/hadoop/yarn-site.xml
</code></pre>
<p>In this file, enter the following content in between the <code><configuration></configuration></code> tag:</p>
<pre class="code-pre "><code langs=""><property>
   <name>yarn.nodemanager.aux-services</name>
   <value>mapreduce_shuffle</value>
</property>
<property>
   <name>yarn.nodemanager.aux-services.mapreduce.shuffle.class</name>
   <value>org.apache.hadoop.mapred.ShuffleHandler</value>
</property>
</code></pre>
<p>The <code>yarn-site.xml</code> file should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/7.png" alt="yarn-site.xml contents" title="yarn-site.xml contents" /></p>

<p>Save and close this file.</p>

<h3 id="v-creating-and-editing-usr-local-hadoop-etc-hadoop-mapred-site-xml">v. Creating and Editing /usr/local/hadoop/etc/hadoop/mapred-site.xml</h3>

<p>By default, the <code>/usr/local/hadoop/etc/hadoop/</code> folder contains the  <code>/usr/local/hadoop/etc/hadoop/mapred-site.xml.template</code> file which has to be renamed/copied with the name <code>mapred-site.xml</code>. This file is used to specify which framework is being used for MapReduce.</p>

<p>This can be done using the following command:</p>
<pre class="code-pre "><code langs="">cp /usr/local/hadoop/etc/hadoop/mapred-site.xml.template /usr/local/hadoop/etc/hadoop/mapred-site.xml
</code></pre>
<p>Once this is done, open the newly created file with nano using the following command:</p>
<pre class="code-pre "><code langs="">nano /usr/local/hadoop/etc/hadoop/mapred-site.xml
</code></pre>
<p>In this file, enter the following content in between the <code><configuration></configuration></code> tag:</p>
<pre class="code-pre "><code langs=""><property>
   <name>mapreduce.framework.name</name>
   <value>yarn</value>
</property>
</code></pre>
<p>The <code>mapred-site.xml</code> file should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/8.png" alt="mapred-site.xml contents" title="mapred-site.xml contents" /></p>

<p>Save and close this file.</p>

<h3 id="vi-editing-usr-local-hadoop-etc-hadoop-hdfs-site-xml">vi. Editing /usr/local/hadoop/etc/hadoop/hdfs-site.xml</h3>

<p>The <code>/usr/local/hadoop/etc/hadoop/hdfs-site.xml</code> has to be configured for each host in the cluster that is being used. It is used to specify the directories which will be used as the <strong>namenode</strong> and the <strong>datanode</strong> on that host. </p>

<p>Before editing this file, we need to create two directories which will contain the <strong>namenode</strong> and the <strong>datanode</strong> for this Hadoop installation. This can be done using the following commands:</p>
<pre class="code-pre "><code langs="">mkdir -p /usr/local/hadoop_store/hdfs/namenode
mkdir -p /usr/local/hadoop_store/hdfs/datanode
</code></pre>
<p><strong>Note:</strong> <em>You can create these directories in different locations, but make sure to modify the contents of <code>hdfs-site.xml</code> accordingly.</em></p>

<p>Once this is done, open the <code>/usr/local/hadoop/etc/hadoop/hdfs-site.xml</code> file with nano using the following command:</p>
<pre class="code-pre "><code langs="">nano /usr/local/hadoop/etc/hadoop/hdfs-site.xml
</code></pre>
<p>In this file, enter the following content in between the <code><configuration></configuration></code> tag:</p>
<pre class="code-pre "><code langs=""><property>
   <name>dfs.replication</name>
   <value>1</value>
 </property>
 <property>
   <name>dfs.namenode.name.dir</name>
   <value>file:/usr/local/hadoop_store/hdfs/namenode</value>
 </property>
 <property>
   <name>dfs.datanode.data.dir</name>
   <value>file:/usr/local/hadoop_store/hdfs/datanode</value>
 </property>
</code></pre>
<p>The <code>hdfs-site.xml</code> file should look something like this:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/9.png" alt="hdfs-site.xml contents" title="hdfs-site.xml contents" /></p>

<p>Save and close this file.</p>

<h2 id="format-the-new-hadoop-filesystem">Format the New Hadoop Filesystem</h2>

<p>After completing all the configuration outlined in the above steps, the Hadoop filesystem needs to be formatted so that it can start being used. This is done by executing the following command:</p>
<pre class="code-pre "><code langs="">hdfs namenode -format
</code></pre>
<p><strong>Note:</strong> <em>This only needs to be done once before you start using Hadoop. If this command is executed again after Hadoop has been used, it'll destroy all the data on the Hadoop file system.</em></p>

<h2 id="start-hadoop">Start Hadoop</h2>

<p>All that remains to be done is starting the newly installed single node cluster:</p>
<pre class="code-pre "><code langs="">start-dfs.sh
</code></pre>
<p>While executing this command, you'll be prompted twice with a message similar to the following:</p>

<blockquote>
<p>Are you sure you want to continue connecting (yes/no)?</p>
</blockquote>

<p>Type in <code>yes</code> for both these prompts and press the enter key. Once this is done, execute the following command:</p>
<pre class="code-pre "><code langs="">start-yarn.sh
</code></pre>
<p>Executing the above two commands will get Hadoop up and running. You can verify this by typing in the following command:</p>
<pre class="code-pre "><code langs="">jps
</code></pre>
<p>Executing this command should show you something similar to the following:</p>

<p><img src="https://assets.digitalocean.com/articles/Ubuntu_Hadoop/10.png" alt="jps command" title="jps command" /></p>

<p>If you can see a result similar to the depicted in the screenshot above, it means that you now have a functional instance of Hadoop running on your VPS.</p>

<h3 id="next-steps">Next Steps</h3>

<p>If you have an application that is set up to use Hadoop, you can fire that up and start using it with the new installation. On the other hand, if you're just playing around and exploring Hadoop, you can start by adding/manipulating data or files on the new filesystem to get a feel for it.</p>

<div class="author">Submitted by: <a href="http://javascript.asia">Jay</a></div>

    