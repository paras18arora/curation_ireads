<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/centralizedockerlogs-twitter.jpg?1429195503/> <br> 
      <h3 id="an-article-from-fluentd">An Article from <a href="https://www.fluentd.org/">Fluentd</a></h3>

<h2 id="introduction">Introduction</h2>

<p><strong>What’s Fluentd?</strong></p>

<p>Fluentd is an open source data collector designed to unify logging infrastructure. It is designed to bring operations engineers, application engineers, and data engineers together by making it simple and scalable to collect and store logs.</p>

<p><em>Before Fluentd</em></p>

<p><img src="https://assets.digitalocean.com/articles/dockerlogs_fluentd/1.jpg" alt="Messy logging structure showing relationships between many programs" /></p>

<p><em>After Fluentd</em></p>

<p><img src="https://assets.digitalocean.com/articles/dockerlogs_fluentd/2.png" alt="Fluentd collects application and access logs from many sources and funnels them to many analysis, archiving, and metrics outputs" /></p>

<h3 id="key-features">Key Features</h3>

<p>Fluentd has four key features that makes it suitable to build clean, reliable logging pipelines:</p>

<ul>
<li><strong>Unified Logging with JSON:</strong> Fluentd tries to structure data as JSON as much as possible. This allows Fluentd to unify all facets of processing log data: collecting, filtering, buffering, and outputting logs across multiple sources and destinations. The downstream data processing is much easier with JSON, since it has enough structure to be accessible without forcing rigid schemas</li>
<li><strong>Pluggable Architecture:</strong> Fluentd has a flexible plugin system that allows the community to extend its functionality. The 300+ community-contributed plugins connect dozens of data sources to dozens of data outputs, manipulating the data as needed. By using plugins, you can make better use of your logs right away</li>
<li><strong>Minimum Resources Required:</strong> A data collector should be lightweight so that the user can run it comfortably on a busy machine. Fluentd is written in a combination of C and Ruby, and requires minimal system resources. The vanilla instance runs on 30-40MB of memory and can process 13,000 events/second/core</li>
<li><strong>Built-in Reliability:</strong> Data loss should never happen. Fluentd supports memory- and file-based buffering to prevent inter-node data loss. Fluentd also supports robust failover and can be set up for high availability</li>
</ul>

<h3 id="goals-collecting-centralized-docker-container-logs-with-fluentd">Goals: Collecting Centralized Docker Container Logs with Fluentd</h3>

<p>As Docker containers are rolled out in production, there is an increasing need to persist containers’ logs somewhere less ephemeral than containers.</p>

<p>In this tutorial, we'll show you how to install Fluentd and use it to collect logs from  Docker containers, storing them outside so the data can be saved after the containers have been stopped. We'll stream the data to another container running Elasticsearch, on the same Ubuntu 14.04 server.</p>

<p>As outlined in <a href="https://github.com/GoogleCloudPlatform/kubernetes/tree/master/cluster/addons/fluentd-elasticsearch/fluentd-es-image">Kubernetes’s GitHub repo</a>, this architecture uses Fluentd’s ability to tail and parse JSON-per-line log files produced by Docker daemon for each container. For a minimal setup, please see <a href="http://www.fluentd.org/guides/recipes/docker-logging">this recipe</a>.</p>

<p>At the end of this tutorial, we'll discuss two more use cases. After reading this article, you should know the basics of how to use Fluentd.</p>

<h3 id="prerequisites">Prerequisites</h3>

<p>Please make sure you complete these prerequisites for the tutorial.</p>

<ul>
<li>Ubuntu 14.04 Droplet</li>
<li>User with <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo access</a></li>
</ul>

<h2 id="step-1-—-installing-fluentd">Step 1 — Installing Fluentd</h2>

<p>The most common way of deploying Fluentd is via the <code>td-agent</code> package. Treasure Data, the original author of Fluentd, packages Fluentd with its own Ruby runtime so that the user does not need to set up their own Ruby to run Fluentd.</p>

<p>Currently, <code>td-agent</code> supports the following platforms:</p>

<ul>
<li>Ubuntu: Lucid, Precise and Trusty</li>
<li>Debian: Wheezy and Squeeze</li>
<li>RHEL/Centos: 5, 6 and 7</li>
<li>Mac OSX: 10.9 and above</li>
</ul>

<p>In this tutorial, we assume you are on IndiaReads Droplet running Ubuntu 14.04 LTS (Trusty).</p>

<p>Install <code>td-agent</code> with the following command:</p>
<pre class="code-pre "><code langs="">curl -L http://toolbelt.treasuredata.com/sh/install-ubuntu-trusty-td-agent2.sh | sh
</code></pre>
<p>Start <code>td-agent</code>:</p>
<pre class="code-pre "><code langs="">sudo /etc/init.d/td-agent start
</code></pre>
<p>Check the logs to make sure it was installed successfully:</p>
<pre class="code-pre "><code langs="">tail /var/log/td-agent/td-agent.log
</code></pre>
<p>You should see output similar to the following:</p>
<pre class="code-pre "><code langs="">    port 24230
  </source>
</ROOT>
2015-02-22 18:27:45 -0500 [info]: adding source type="forward"
2015-02-22 18:27:45 -0500 [info]: adding source type="http"
2015-02-22 18:27:45 -0500 [info]: adding source type="debug_agent"
2015-02-22 18:27:45 -0500 [info]: adding match pattern="td.*.*" type="tdlog"
2015-02-22 18:27:45 -0500 [info]: adding match pattern="debug.**" type="stdout"
2015-02-22 18:27:45 -0500 [info]: listening fluent socket on 0.0.0.0:24224
2015-02-22 18:27:45 -0500 [info]: listening dRuby uri="druby://127.0.0.1:24230" object="Engine"
</code></pre>
<blockquote>
<p><strong>Note:</strong> Alternately, Fluentd is available as a Ruby gem and can be installed with <code>gem install fluentd</code>. If you do NOT have sudo privileges, please install Ruby (see <a href="https://indiareads/community/tutorials/how-to-install-ruby-on-rails-with-rbenv-on-debian-7-wheezy">Installing Ruby</a> here, for example) and run:</p>
<pre class="code-pre "><code langs="">gem install fluentd --no-rdoc --no-ri
</code></pre></blockquote>

<h2 id="step-2-—-installing-docker">Step 2 — Installing Docker</h2>

<p>Now we'll install Docker. This tutorial was tested with Docker v1.5.0.</p>

<p>Add the key for the Docker repository so we can get an up-to-date Docker package:</p>
<pre class="code-pre "><code langs="">sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 36A1D7869245C8950F966E92D8576A8BA88D21E9
</code></pre>
<p>Add the repository to your sources:</p>
<pre class="code-pre "><code langs="">sudo sh -c "echo deb https://get.docker.com/ubuntu docker main > /etc/apt/sources.list.d/docker.list"
</code></pre>
<p>Update your system:</p>
<pre class="code-pre "><code langs="">sudo apt-get update
</code></pre>
<p>Install Docker:</p>
<pre class="code-pre "><code langs="">sudo apt-get install lxc-docker
</code></pre>
<p>Verify that Docker was installed by checking the version:</p>
<pre class="code-pre "><code langs="">docker --version
</code></pre>
<p>You should see output like the following:</p>
<pre class="code-pre "><code langs="">Docker version 1.5.0, build a8a31ef
</code></pre>
<h2 id="step-3-—-adding-user-to-docker-group">Step 3 — Adding User to docker Group</h2>

<p>Docker runs as <strong>root</strong>, so in order to issue <code>docker</code> commands, add your sudo user to the <strong>docker</strong> group. Replace <code><span class="highlight">sammy</span></code> with the user of your choice.</p>
<pre class="code-pre "><code langs="">sudo gpasswd -a <span class="highlight">sammy</span> docker
</code></pre>
<p>Then, restart Docker.</p>
<pre class="code-pre "><code langs="">sudo service docker restart
</code></pre>
<p>Finally, if you are currently logged in as your sudo user, you must log out and log back in.</p>

<h2 id="step-4-—-building-the-fluentd-image">Step 4 — Building the Fluentd Image</h2>

<p>In this section we'll create the Docker image for the Fluentd Docker container. If you'd like to learn more about Docker in general, please read <a href="https://indiareads/community/tutorials/the-docker-ecosystem-an-introduction-to-common-components">this introductory tutorial</a>.</p>

<p>Create a new directory for your Fluentd Docker resources, and move into it:</p>
<pre class="code-pre "><code langs="">mkdir ~/fluentd-docker && cd ~/fluentd-docker
</code></pre>
<p>Create the following <code>Dockerfile</code>:</p>
<pre class="code-pre "><code langs="">sudo nano Dockerfile
</code></pre>
<p>Add the following contents to your file exactly. This file tells Docker to update the Docker container and install Ruby, Fluentd, and Elasticsearch:</p>
<pre class="code-pre "><code langs="">FROM ruby:2.2.0
MAINTAINER kiyoto@treausuredata.com
RUN apt-get update
RUN gem install fluentd -v "~>0.12.3"
RUN mkdir /etc/fluent
RUN apt-get install -y libcurl4-gnutls-dev make
RUN /usr/local/bin/gem install fluent-plugin-elasticsearch
ADD fluent.conf /etc/fluent/
ENTRYPOINT ["/usr/local/bundle/bin/fluentd", "-c", "/etc/fluent/fluent.conf"]
</code></pre>
<p>You also need to create a <code>fluent.conf</code> file in the same directory.</p>
<pre class="code-pre "><code langs="">sudo nano fluent.conf
</code></pre>
<p>The <code>fluent.conf</code> file should look like this. You can copy this file exactly:</p>
<pre class="code-pre "><code langs=""><source>
  type tail
  read_from_head true
  path /var/lib/docker/containers/*/*-json.log
  pos_file /var/log/fluentd-docker.pos
  time_format %Y-%m-%dT%H:%M:%S
  tag docker.*
  format json
</source>
# Using filter to add container IDs to each event
<filter docker.var.lib.docker.containers.*.*.log>
  type record_transformer
  <record>
    container_id ${tag_parts[5]}
  </record>
</filter>

<match docker.var.lib.docker.containers.*.*.log>
  type elasticsearch
  logstash_format true
  host "#{ENV['ES_PORT_9200_TCP_ADDR']}" # dynamically configured to use Docker's link feature
  port 9200
  flush_interval 5s
</match>
</code></pre>
<p>The purpose of this file is to tell Fluentd where to find the logs for other Docker containers.</p>

<p>Then, build your Docker image, called <code>fluentd-es</code>:</p>
<pre class="code-pre "><code langs="">docker build -t fluentd-es .
</code></pre>
<p>This will take a few minutes to complete. Check that you have successfully built the images:</p>
<pre class="code-pre "><code langs="">docker images
</code></pre>
<p>You should see output like this:</p>
<pre class="code-pre "><code langs="">REPOSITORY          TAG                 IMAGE ID            CREATED             VIRTUAL SIZE
fluentd-es          latest              89ba1fb47b23        2 minutes ago       814.1 MB
ruby                2.2.0               51473a2975de        6 weeks ago         774.9 MB
</code></pre>
<h2 id="step-5-—-starting-the-elasticsearch-container">Step 5 — Starting the Elasticsearch Container</h2>

<p>Now move back to your home directory or preferred directory for your Elasticsearch container:</p>
<pre class="code-pre "><code langs="">cd ~
</code></pre>
<p>Download and start the Elasticsearch container. There is already an automated build for this:</p>
<pre class="code-pre "><code langs="">docker run -d -p 9200:9200 -p 9300:9300 --name es dockerfile/elasticsearch
</code></pre>
<p>Wait for the container image to download and start.</p>

<p>Next, make sure that the Elasticsearch container is running properly by checking the Docker processes:</p>
<pre class="code-pre "><code langs="">docker ps
</code></pre>
<p>You should see output like this:</p>
<pre class="code-pre "><code langs="">CONTAINER ID        IMAGE                           COMMAND             CREATED             STATUS              PORTS                                           NAMES
c474fd99ce43        dockerfile/elasticsearch:latest   "/elasticsearch/bin/   4 minutes ago      Up 4 minutes        0.0.0.0:9200->9200/tcp, 0.0.0.0:9300->9300/tcp   es
</code></pre>
<h2 id="step-6-—-starting-the-fluentd-to-elasticsearch-container">Step 6 — Starting the Fluentd-to-Elasticsearch Container</h2>

<p>Now we'll start the container that runs Fluentd, collects the logs, and sends them to Elastcisearch.</p>
<pre class="code-pre "><code langs="">docker run -d --link es:es -v /var/lib/docker/containers:/var/lib/docker/containers fluentd-es
</code></pre>
<p>In the above command, the <code>--link es:es</code> portion links the Elasticsearch container to the Fluentd container. The <code>-v /var/lib/docker/containers:/var/lib/docker/containers</code> portion is needed to mount the host container's log directory into the Fluentd container, so that Fluentd can tail the log files as containers are created.</p>

<p>Finally, check that the container is running by checking our active Docker processes:</p>
<pre class="code-pre "><code langs="">docker ps
</code></pre>
<p>This time, you should see both the Elasticsearch container and the new <code>fluentd-es</code> container:</p>
<pre class="code-pre "><code langs="">CONTAINER ID        IMAGE                           COMMAND             CREATED             STATUS              PORTS                                           NAMES
f0d2cac81ac8        fluentd-es:latest               "/usr/local/bundle/b   2 seconds ago    Up 2 seconds                                                        stupefied_brattain
c474fd99ce43        dockerfile/elasticsearch:latest   "/elasticsearch/bin/   6 minutes ago      Up 6 minutes        0.0.0.0:9200->9200/tcp, 0.0.0.0:9300->9300/tcp   es
</code></pre>
<h2 id="step-7-—-confirming-that-elasticsearch-is-receiving-events">Step 7 — Confirming that Elasticsearch is Receiving Events</h2>

<p>Finally, let’s confirm that Elasticsearch is receiving the events:</p>
<pre class="code-pre "><code langs="">curl -XGET 'http://localhost:9200/_all/_search?q=*'
</code></pre>
<p>The output should contain events that look like this:</p>
<pre class="code-pre "><code langs="">{"took":66,"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":0,"max_score":null,"hits":[]}}
{"took":59,"timed_out":false,"_shards":{"tod","_id":"AUwLaKjcnpi39wqZnTXQ","_score":1.0,"_source":{"log":"2015-03-12 00:35:44 +0000 [info]: following tail of /var/lib/docker/containers/6abeb6ec0019b2198ed708315f4770fc7ec6cc44a10705ea59f05fae23b81ee9/6abeb6ec0019b2198ed708315f4770fc7ec6cc44a10705ea59f05fae23b81ee9-json.log\n","stream":"stdout","container_id":"6abeb6ec0019b2198ed708315f4770fc7ec6cc44a10705ea59f05fae23b81ee9","@timestamp":"2015-03-12T00:35:44+00:00"}}]}}
</code></pre>
<p>You may have quite a few events logged depending on your setup. A single event should start with <code>{"took":</code> and end with a timestamp.</p>

<p>As this output shows, Elasticsearch is receiving data. (Your container ID will be different than the one shown above!)</p>

<h2 id="step-8-—-taking-event-logs-to-the-next-level">Step 8 — Taking Event Logs to the Next Level</h2>

<p>Now that your container events are being saved by Elasticsearch, what should you do next? There are plenty of useful things to do with Elasticsearch. If you're looking for ideas, you may want to check out:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/how-to-interact-with-data-in-elasticsearch-using-crud-operation">Basic Elasticsearch operations</a></li>
<li><a href="https://indiareads/community/tutorials/elasticsearch-fluentd-and-kibana-open-source-log-search-and-visualization">Adding a dashboard</a> so you can visualize your logs</li>
</ul>

<h2 id="conclusion">Conclusion</h2>

<p>Collecting logs from Docker containers is just one way to use Fluentd. In this section we'll present two other common use cases for Fluentd.</p>

<h3 id="use-case-1-real-time-log-search-and-log-archiving">Use Case 1: Real-time Log Search and Log Archiving</h3>

<p>Many users come to Fluentd to build a logging pipeline that does both real-time log search and long-term storage. The architecture looks like this:</p>

<p><img src="https://assets.digitalocean.com/articles/dockerlogs_fluentd/3.png" alt="Funnelling logs from multiple backends to Elasticsearch, MongoDB, and Hadoop" /></p>

<p>This architecture takes advantage of Fluentd’s ability to copy data streams and output them to multiple storage systems. In the above setting, Elasticsearch is used for real-time search, while MongoDB and/or Hadoop are used for batch analytics and long-term storage.</p>

<h3 id="use-case-2-centralized-application-logging">Use Case 2: Centralized Application Logging</h3>

<p>Web applications produce a lot of logs, and they are often formatted arbitrarily and stored on the local filesystem. This is bad for two reasons:</p>

<ul>
<li>The logs are difficult to parse programmatically (requiring lots of regular expressions) and hence are not very accessible to those who wish to understand user behavior through statistical analysis (A/B testing, fraud detection, etc.)</li>
<li>The logs are not accessible in real-time because the text logs are bulk-loaded into storage systems. Also, if the server’s disk gets corrupted between bulk-loads, the logs become lost or corrupted</li>
</ul>

<p>Fluentd solves these problems by:</p>

<ul>
<li>Providing logger libraries for various programming languages with a consistent API: each logger sends a triple of (timestamp, tag, JSON-formatted event) to Fluentd. Currently, there are logger libraries for Ruby, Node.js, Go, Python, Perl, PHP, Java and C++</li>
<li>Allowing the application to “fire and forget”: the logger can log asynchronously to Fluentd, which in turn buffers the logs before uploading to backend systems</li>
</ul>

<p>Resources:</p>

<ul>
<li>Read about the <a href="http://www.fluentd.org/blog/unified-logging-layer">Unified Logging Layer</a></li>
<li><a href="http://blog.raintown.org/2014/11/logging-kubernetes-pods-using-fluentd.html">Fluentd + Elasticsearch for Kubernetes</a> by Satnam Singh (Kubernetes committer)</li>
</ul>

    