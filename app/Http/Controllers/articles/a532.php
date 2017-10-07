<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>If you use a monitoring system (like Zabbix or Nagios) then you know how monitoring works. In a nutshell it can be described as follows: A monitoring system receives various metrics (CPU/memory usage, network utilization, and more). As soon as the value of one of the metrics goes outside the predetermined thresholds, it activates the corresponding trigger, and the monitoring system informs you that one of the metrics is outside normal limits. Thresholds for each metric are usually set manually, which is not always convenient.</p>

<p>In this tutorial you will learn how to install and configure <a href="https://github.com/etsy/skyline"><strong>Skyline</strong></a> — a real-time anomaly detection system. It is able to analyze a set of metrics in real time without setting or adjusting the thresholds for each one. It is designed to be used wherever there is a large number of time series (hundreds of thousands) that need constant monitoring.</p>

<h2 id="threshold-triggers">Threshold Triggers</h2>

<p>Let's look at an example of a monitoring system with manually set thresholds. The figure below shows a graph for CPU load. Dashed lines indicate the thresholds of the trigger.</p>

<p><img src="https://assets.digitalocean.com/articles/skyline_centos7/pic1.png" alt="Figure 1" /><br />
<strong>Figure 1</strong></p>

<p>At point 1 in Figure 1, a process has started, and the CPU load has significantly increased. The trigger has been activated, and the administrator notices it. The administrator decides that it is within normal values and changes the trigger thresholds to the ones shown as the upper dashed lines.</p>

<p>After some time has passed, the trigger is fired again at point 2 in Figure 1. The administrator discovers that a second service is regularly making backups and causing the load increase. Then the question arises: Do you raise the threshold higher or leave it as is but just ignore the alarms?</p>

<p>Let's take a look at point 3. At that moment, the event load falls, but the administrator was not informed because the threshold was not exceeded. The trigger didn't activate.</p>

<p>This simple case shows us that there are some difficulties when trying to set thresholds. It is hard to adjust the threshold values to catch performance issues without triggering false positive errors or false negative errors.</p>

<p>To help solve these problems, <strong>Skyline</strong> has been created. It's using a set of nonparametric algorithms to classify anomalous metrics.</p>

<h2 id="skyline-components">Skyline Components</h2>

<p>Skyline consists of the following components: Horizon Agent, Analyzer Agent, and Webapp.</p>

<h3 id="horizon-agent">Horizon Agent</h3>

<p>The Horizon Agent is responsible for collecting data. It has <strong>Listeners</strong>, which listen for incoming data.</p>

<p>It accept the data in two formats: <a href="https://docs.python.org/2/library/pickle.html">pickle</a> (TCP) and <a href="http://msgpack.org/">MessagePack</a> (UDP). It reads incoming metrics and puts them in a shared queue that the <strong>Workers</strong> read from. Workers encode the data into Messagepack and append it to the Redis database. The Horizon Agent also regularly trims and cleans old metrics using <strong>Roombas</strong>. If this is not done, then all free memory will soon be exhausted.</p>

<h3 id="analyzer-agent">Analyzer Agent</h3>

<p>The Analyzer Agent is responsible for analyzing the data. It receives a list of metrics from Redis, runs several processes, and assigns metrics to each of them. Each process analyzes the data using several algorithms. Each algorithm reports the result — whether data is abnormal or not. If the majority of the algorithms report that the current metric has abnormality, the data is considered <em>anomalous</em>.</p>

<p>All abnormal metrics are written to a file. On the basis of this file, an image is created and shown in the web application.</p>

<p>Analyzer can also send notifications: email, HipChat, or PagerDuty. Email notifications are configured later in this article.</p>

<h3 id="webapp">Webapp</h3>

<p>Skyline provides a small web application to display the abnormal metrics. It's a simple web app written in Python with a Flask framework. The upper part shows two graphs — the past hour and the past day. Below the graphs is a list of all the abnormal metrics.</p>

<h2 id="redis-database">Redis Database</h2>

<p><a href="http://redis.io/">Redis</a> is an open source key-value cache and store database.</p>

<p>Skyline stores all the metrics and encoded time series in a Redis database. When a data point comes in, a Horizon worker packs the datapoint with the schema <code>[timestamp, value]</code> into a MessagePack-encoded binary string and append this string to the appropriate metric key. </p>

<p><em>Figure 2</em> shows the diagram of interactions of the Skyline's components.</p>

<p><img src="https://assets.digitalocean.com/articles/skyline_centos7/pic2.png" alt="Figure 2" /><br />
<strong>Figure 2</strong></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before you can install Skyline, you need the complete the following prerequisites:</p>

<ul>
<li>Deploy a CentOS 7 Droplet.</li>
<li>Add a sudo user by following the <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">Initial Server Setup</a> tutorial. All the commands in this tutorial should be run as this non-root user.</li>
<li><a href="https://indiareads/community/tutorials/how-to-add-swap-on-centos-7">Add swap space</a> to your server. 4 GB is fine.<br /></li>
<li>Install Graphite and collectd by following the instructions in the <a href="https://indiareads/community/tutorials/how-to-keep-effective-historical-logs-with-graphite-carbon-and-collectd-on-centos-7">How To Keep Effective Historical Logs with Graphite, Carbon, and collectd on CentOS 7</a> tutorial.</li>
</ul>

<h2 id="step-1-—-installing-skyline-and-redis">Step 1 — Installing Skyline and Redis</h2>

<p>To install Skyline, first install the required applications including some Python-related tools and the Apache web server:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install httpd gcc gcc-c++ git pycairo mod_wsgi python-pip python-devel blas-devel lapack-devel libffi-devel
</li></ul></code></pre>
<p>Get the latest source files for Skyline from GitHub:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt
</li><li class="line" prefix="$">sudo git clone https://github.com/etsy/skyline.git
</li></ul></code></pre>
<p>Install some required Python packages:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/skyline
</li><li class="line" prefix="$">sudo pip install -U six
</li><li class="line" prefix="$">sudo pip install -r requirements.txt
</li></ul></code></pre>
<p>Install the following Python packages in this specified order:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo pip install numpy
</li><li class="line" prefix="$">sudo pip install scipy
</li><li class="line" prefix="$">sudo pip install pandas
</li><li class="line" prefix="$">sudo pip install patsy
</li><li class="line" prefix="$">sudo pip install statsmodels
</li><li class="line" prefix="$">sudo pip install msgpack-python
</li></ul></code></pre>
<p><span class="note">Installation of some packages may take a long time, so please be patient.<br /></span></p>

<p>Most of them are open source Python libraries used for scientific and technical computing. The <code>msgpack-python</code> package is necessary for reading and writing <a href="http://msgpack.org/">MessagePack</a> data.</p>

<p>Copy the example Skyline settings file to the correct file location:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /opt/skyline/src/settings.py.example /opt/skyline/src/settings.py
</li></ul></code></pre>
<p>Create the following directories:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo mkdir /var/log/skyline
</li><li class="line" prefix="$">sudo mkdir /var/run/skyline
</li><li class="line" prefix="$">sudo mkdir /var/log/redis
</li><li class="line" prefix="$">sudo mkdir /var/dump/
</li></ul></code></pre>
<p>As we mentioned above, Skyline stores all the metrics in a Redis database, so you need to install it as well:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install redis
</li></ul></code></pre>
<p>You can find more information about Redis from the tutorial <a href="https://indiareads/community/tutorials/how-to-install-and-use-redis">How To Install and Use Redis</a>.</p>

<p>Start the Skyline and Redis services:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd /opt/skyline/bin
</li><li class="line" prefix="$">sudo redis-server redis.conf
</li><li class="line" prefix="$">sudo ./horizon.d start
</li><li class="line" prefix="$">sudo ./analyzer.d start
</li><li class="line" prefix="$">sudo ./webapp.d start
</li></ul></code></pre>
<p>To test the installation, run the included test script:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">python /opt/skyline/utils/seed_data.py
</li></ul></code></pre>
<p>You should see the following output:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">Loading data over UDP via Horizon...                                           
</li><li class="line" prefix="$">Connecting to Redis...                                                         
</li><li class="line" prefix="$">Congratulations! The data made it in. The Horizon pipeline seems to be working.
</li></ul></code></pre>
<p>The installation and basic configuration of Skyline is finished. Now you need to send data into it.</p>

<h2 id="step-2-—-getting-data-into-skyline">Step 2 — Getting Data into Skyline</h2>

<p>As previously mentioned, the Skyline accepts data in two formats: <a href="https://docs.python.org/2/library/pickle.html">pickle</a> (TCP) and <a href="http://msgpack.org/">MessagePack</a> (UDP). </p>

<p>You can write your own script or module to your favorite monitoring agent and have it encode the data with MessagePack to be sent to Skyline for analysis. Skyline accepts metrics in the form of MessagePack encoded strings over UDP. MessagePack is an object serialization specification like JSON. The format is <code>[<metric name>, [<timestamp>, <value>]]</code>. MessagePack has an API for most programming languages. More information and API examples can be find on the <a href="http://msgpack.org/">MessagePack official site</a>.</p>

<p>This tutorial will show you how to send the data from Graphite and collectd to Skyline.</p>

<h3 id="getting-data-from-graphite">Getting Data from Graphite</h3>

<p>Graphite consists of several components, one of which is the <strong>carbon-relay</strong> service. Carbon-relay forwards incoming metrics to another Graphite instance for redundancy. So you can point the carbon-relay service to the host where Skyline is running.</p>

<p><img src="https://assets.digitalocean.com/articles/skyline_centos7/graphite_skyline.png" alt="Figure 3" /><br />
<strong>Figure 3</strong></p>

<p>Figure 3 shows a schematic diagram of the data flow. Data from external monitoring agents (<a href="http://collectd.org/">collectd</a>, <a href="https://github.com/BrightcoveOS/Diamonds">diamond</a>, <a href="https://github.com/etsy/statsd">statsd</a> etc.) or systems (<a href="http://www.nagios.org/">Nagios</a>, <a href="https://www.icinga.org/">Icinga</a>, <a href="http://sensuapp.org/">Sensu</a> etc.) are transferred into Graphite. Next, carbon-relay forwards the data into Skyline. Carbon-relay, carbon-cache, and Skyline may run either on a single host or on separate hosts.</p>

<p>You need to configure Graphite, collectd, and Skyline to get this data flow working. </p>

<p>If you did not copy the example <code>relay-rules.conf</code> to the proper location for the carbon-relay configuration file earlier, you have to do it now:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo cp /opt/graphite/conf/relay-rules.conf.example /opt/graphite/conf/relay-rules.conf
</li></ul></code></pre>
<p>Let's open the <code>relay-rules.conf</code> configuration file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /opt/graphite/conf/relay-rules.conf
</li></ul></code></pre>
<p>Add your Skyline host to the list of destinations, where <span class="highlight">YOUR<em>SKYLINE</em>HOST</span> is the IP address of your Skyline host:</p>
<div class="code-label " title="/opt/graphite/conf/relay-rules.conf">/opt/graphite/conf/relay-rules.conf</div><pre class="code-pre "><code langs="">[default]
default = true
destinations = 127.0.0.1:2004, <span class="highlight">YOUR_SKYLINE_HOST</span>:2024
</code></pre>
<p>All destinations used in <code>relay-rules.conf</code> must also be defined in <code>carbon.conf</code> configuration file.</p>

<p>Open the <code>carbon.conf</code> configuration file to make this change:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /opt/graphite/conf/carbon.conf
</li></ul></code></pre>
<p>Then locate the <code>[relay]</code> section, and edit the <code>DESTINATIONS</code> line:</p>
<div class="code-label " title="/opt/graphite/conf/carbon.conf">/opt/graphite/conf/carbon.conf</div><pre class="code-pre "><code langs="">[relay]
...
DESTINATIONS = 127.0.0.1:2004, <span class="highlight">YOUR_SKYLINE_HOST</span>:2024
...
</code></pre>
<p>Once you have made these changes, start the carbon-relay service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl start carbon-relay
</li></ul></code></pre>
<h3 id="allowing-skyline-access-to-graphite-web">Allowing Skyline Access to Graphite-Web</h3>

<p>In  <a href="https://indiareads/community/tutorials/how-to-keep-effective-historical-logs-with-graphite-carbon-and-collectd-on-centos-7">How To Keep Effective Historical Logs with Graphite, Carbon, and collectd on CentOS 7</a>, if you elected to password-protect the Graphite web interface, you <strong>must allow</strong> access from localhost without password for Skyline to work.</p>

<p>To do so, edit the Graphite configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/httpd/conf.d/graphite.conf
</li></ul></code></pre>
<p>Add the following lines in red to the <code><Location></code> block:</p>
<div class="code-label " title="/etc/httpd/conf.d/graphite.conf">/etc/httpd/conf.d/graphite.conf</div><pre class="code-pre "><code langs=""><Location "/"> 
  AuthType Basic
  AuthName "Private Area" 
  AuthUserFile /opt/graphite/secure/.passwd
  Require user sammy
    <span class="highlight">Order Deny,Allow</span>
    <span class="highlight">Deny from all</span>
    <span class="highlight">Allow from localhost</span>
    <span class="highlight">Satisfy Any</span>
</Location>
</code></pre>
<p>Then restart the Apache service:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart httpd
</li></ul></code></pre>
<h3 id="getting-data-from-collectd">Getting Data from Collectd</h3>

<p>You can also configure collectd to send data to Skyline. Open its configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /etc/collectd.conf
</li></ul></code></pre>
<p>Change the port number in the <code><Plugin write_graphite></code> block to <code>2013</code>:</p>
<div class="code-label " title="/etc/collectd.conf">/etc/collectd.conf</div><pre class="code-pre "><code langs=""><Plugin write_graphite>
    . . .       
    Port "<span class="highlight">2013</span>"
    . . .
</code></pre>
<p>Then restart collectd:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo systemctl restart collectd.service
</li></ul></code></pre>
<p>To avoid confusion, Figure 4 shows a simplified scheme with the correct port numbers.</p>

<p><img src="https://assets.digitalocean.com/articles/skyline_centos7/graphite_skyline_ports.png" alt="Figure 4" /><br />
<strong>Figure 4</strong></p>

<p>The correct port numbers are as follows: </p>

<ol>
<li>Carbon-relay listens for incoming data in <em>plaintext</em> format on port <em>2013</em></li>
<li>Carbon-relay sends the data in <em>pickle</em> format</li>
<li>Carbon-cache listens for incoming data in <em>pickle</em> format on port <em>2004</em></li>
<li>Horizon agent listens for incoming data in <em>pickle</em> format on port <em>2024</em></li>
</ol>

<p><span class="note">Attention! If you start the Horizon agent and the optional carbon-aggregator on the same host, you must change their ports. By default they are both set to the same port <em>2024</em>.<br /></span></p>

<h2 id="step-3-—-setting-up-skyline">Step 3 — Setting Up Skyline</h2>

<p>The Skyline configuration file contains many settings. Open the file for editing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /opt/skyline/src/settings.py
</li></ul></code></pre>
<p>Each setting within this file is documented via informative comments in the file itself. At a minimum, you need to set the following parameters, replacing the text in red with your values:</p>

<ul>
<li><code>GRAPHITE_HOST = '<span class="highlight">YOUR_GRAPHITE_HOST</span>'</code></li>
<li><code>HORIZON_IP = '<span class="highlight">0.0.0.0</span>'</code></li>
<li><code>WEBAPP_IP = '<span class="highlight">YOUR_SKYLINE_HOST_IP</span>'</code></li>
</ul>

<p>The other options can be left to their default values. They are as follows:</p>

<ul>
<li><code>FULL_DURATION</code> — This option specifies the maximum length of time for which the data will be stored in Redis and analyzed. Longer durations take longer to analyze, but they can help reduce the noise and provide more accurate anomaly detection. Default value is <code>86400</code> seconds.</li>
<li><code>CARBON_PORT</code> — This option specifies the carbon port. Default value is <code>2003</code>.</li>
<li><code>ANALYZER_PROCESSES</code> — This option specifies the number of processes that the Skyline analyzer will spawn. It is recommended to set this parameter to several less than the total number of CPUs on your host. Default value is <code>5</code>.</li>
<li><code>WORKER_PROCESSES</code> — This option specifies the number of worker processes that will consume from the Horizon queue. Default value is <code>2</code>.</li>
<li><code>PICKLE_PORT</code> — This option specifies the TCP port that listens for Graphite's pickles. Default value is <code>2024</code>. </li>
<li><code>UDP_PORT</code> — This option specifies the UDP port that listens for MessagePack-encoded packets. Default value is <code>2025</code>.</li>
<li><code>WEBAPP_PORT</code> — This option specifies the port for the Skyline webapp. Default value is <code>1500</code>.</li>
</ul>

<p>After making these changes you have to restart the corresponding app:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/skyline/bin/horizon.d restart
</li><li class="line" prefix="$">sudo /opt/skyline/bin/analyzer.d restart
</li><li class="line" prefix="$">sudo /opt/skyline/bin/webapp.d restart
</li></ul></code></pre>
<p>Then you can open the link <code>http://<span class="highlight">your_server_ip</span>:1500</code> and see the Skyline webpage (Figure 5). It will display anomalous metric as they are found.</p>

<p><img src="https://assets.digitalocean.com/articles/skyline_centos7/skyline_interface.png" alt="Figure 5" /><br />
<strong>Figure 5</strong></p>

<p>For Skyline to operate in its full capacity, you need to wait until the <code>FULL_DURATION</code> seconds has passed. By default, <code>FULL_DURATION</code> is set to 1 day (<code>86400</code> seconds).</p>

<p>You should wait at least one hour to start to track anomalies. This will give Skyline time to  accumulate information about the normal load levels. Try not to create extra load on the system while Skyline is establishing a baseline.</p>

<h2 id="step-4-—-enabling-email-alerts">Step 4 — Enabling Email Alerts</h2>

<p>By default, Skyline displays detected anomalies in its web interface (<code>http://<span class="highlight">your_server_ip</span>:1500</code>) as they are found and while they are still occurring. As soon as an anomaly disappears, its corresponding metric disappears from this interface. Therefore, you must monitor the webpage to see these anomalies, which is not always convenient. </p>

<p>You can configure email alerts so you don't miss them.</p>

<p>To do so, open the Skyline configuration file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo vi /opt/skyline/src/settings.py
</li></ul></code></pre>
<p>Make sure alerts are enabled:</p>
<div class="code-label " title="/opt/syline/src/settings.py">/opt/syline/src/settings.py</div><pre class="code-pre "><code langs="">ENABLE_ALERTS = True
</code></pre>
<p>Then find the following ALERTS section and add the following schema in red:</p>
<div class="code-label " title="/opt/syline/src/settings.py">/opt/syline/src/settings.py</div><pre class="code-pre "><code langs="">ALERTS = (
    (^)("collectd", "smtp", 1800)(^),
)
</code></pre>
<p>The first value in the schema is the process to monitor. In this case, it is <code>collectd.</code> The second value of the schema is <code>smtp</code>, which stands for email alerts. The last value of <code>1800</code> is in seconds. It means that alerts will not fire more than once within 30 minutes (1800 seconds) even if a trigger is detected. Modify this value to best suite your needs.</p>

<p>Also find the following section and modify it for the email addresses you want to use. Email alerts will be sent to the (^)administrator@example.com(^) account from (^)skyline-alerts@example.com(^). </p>
<div class="code-label " title="/opt/syline/src/settings.py">/opt/syline/src/settings.py</div><pre class="code-pre "><code langs="">SMTP_OPTS = {
    "sender": "(^)skyline-alerts@example.com(^)",
    "recipients": {
        "collectd": ["(^)administrator@example.com(^)"],
    },
}
</code></pre>
<p>After making all these changes, you have to restart analyzer daemon:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo /opt/skyline/bin/analyzer.d restart
</li></ul></code></pre>
<h2 id="step-5-—-testing-skyline">Step 5 — Testing Skyline</h2>

<p>To test Skyline, we can create a CPU spike with a bash command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">dd if=/dev/zero of=/dev/null
</li></ul></code></pre>
<p>You can stop the command at any time by pressing CTRL-C. Several minutes should be enough to create an anomaly.</p>

<p>If you look at the Skyline web interface while running this command you will see the detected anomalies. An example is shown in Figure 6.</p>

<p><img src="https://assets.digitalocean.com/articles/skyline_centos7/skyline_anomalies_list.png" alt="Figure 6" /><br />
<strong>Figure 6</strong></p>

<p>You can see that as a result of high CPU load, the speed of Skyline's components decreased. All detected abnormal metrics are displayed as a list at the bottom of the webpage. When you hover over the name of one of the metrics, in the upper graphs you can see the corresponding time series for the last hour and the day. Clicking on the name of the metric to open a more detailed graph, generated by Graphite (see Figure 7 for an example).</p>

<p><img src="https://assets.digitalocean.com/articles/skyline_centos7/detail_graph.png" alt="Figure 7" /><br />
<strong>Figure 7</strong></p>

<p>CPU load did not reach extremely high value in this example, and the threshold was not exceeded. In this case a classical monitoring system was not able to find a deviation. Such case was mentioned earlier (Figure 1, point 3). </p>

<p>Unlike classical monitoring systems, Skyline can quickly find deviations and notify you about them.</p>

<h2 id="step-6-—-adjusting-algorithms-optional">Step 6 — Adjusting Algorithms (Optional)</h2>

<p>As was mentioned earlier, Skyline is using a set of algorithms to detect anomalies. The following algorithms are currently implemented:</p>

<ul>
<li>Mean absolute deviation</li>
<li>Grubbs' test</li>
<li>First hour average</li>
<li>Standard deviation from average</li>
<li>Standard deviation from moving average</li>
<li>Least squares</li>
<li>Histogram bins</li>
<li>Kolmogorov–Smirnov test</li>
</ul>

<p>Most of them are based on the <strong>сontrol charts</strong> (also known as Shewhart charts) and the <strong>three-sigma rule</strong>. They use Python libraries SciPy and NumPy in their calculations. </p>

<p>You can customize any of the used algorithms. You can also modify, delete, or add new ones. To do this, you must edit the configuration file:</p>
<pre class="code-pre "><code langs="">sudo vi /opt/skyline/src/analyzer/algorithms.py
</code></pre>
<p>Each of the algorithms in this file is provided with a small description. For example, let's examine the following algorithm:</p>
<div class="code-label " title="/opt/skyline/src/analyzer/algorithms.py">/opt/skyline/src/analyzer/algorithms.py</div><pre class="code-pre "><code langs="">def median_absolute_deviation(timeseries):
    """
    A timeseries is anomalous if the deviation of its latest datapoint with
    respect to the median is X times larger than the median of deviations.
    """

    series = pandas.Series([x[1] for x in timeseries])
    median = series.median()
    demedianed = np.abs(series - median)
    median_deviation = demedianed.median()

    # The test statistic is infinite when the median is zero,
    # so it becomes super sensitive. We play it safe and skip when this happens.
    if median_deviation == 0:
        return False

    test_statistic = demedianed.iget(-1) / median_deviation

    # Completely arbitary...triggers if the median deviation is
    # 6 times bigger than the median
    if test_statistic > <span class="highlight">6</span>:
        return True                                                       
</code></pre>
<p>Based on the nature of your data, you may need to change the threshold value from <code>6</code> to something else - <code>4</code>, <code>5</code>, <code>7</code> etc.</p>

<p>You can also tune some the settings in the <code>settings.py</code> file:</p>
<div class="code-label " title="/opt/skyline/src/settings.py">/opt/skyline/src/settings.py</div><pre class="code-pre "><code langs="">ALGORITHMS = [
    'first_hour_average',
    'mean_subtraction_cumulation',
     'stddev_from_average',
     'stddev_from_moving_average',
     'least_squares',
     'grubbs',
     'histogram_bins', 
     'median_absolute_deviation',
     'ks_test',
]

CONSENSUS = 6
</code></pre>
<p>The <code>ALGORITHMS</code> option specifies the algorithms that the Analyzer will run. You can comment any of them out to disable them or add new algorithms. The <code>CONSENSUS</code> option specifies the number of algorithms that must return <code>True</code> before a metric is classified as anomalous. To increase the sensitivity, you can reduce this option, and vice versa.</p>

<h2 id="conclusion">Conclusion</h2>

<p><a href="https://github.com/etsy/skyline">Skyline</a> is well proven in complex dynamically changing IT-systems. It may be useful to programmers who regularly make changes to the operating system and want to quickly detect anomalies in system metrics after a new software release.</p>

<p>Its main advantages include:</p>

<ul>
<li>High speed analysis of large amounts of data</li>
<li>No need to set individual parameters for each metric</li>
<li>Ability to add your own algorithms for anomaly detection</li>
</ul>

<p>It also has some disadvantages:</p>

<ul>
<li>Data of each of the metrics is analyzed by several algorithms that require significant computing system resources.</li>
<li>All data is stored in RAM, which allows the system to operate very quickly. With a large number of metrics and a long period of analysis you will need a large amount of the RAM.</li>
</ul>

    