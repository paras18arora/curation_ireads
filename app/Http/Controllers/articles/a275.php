<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="what-is-mesosphere">What is Mesosphere</h3>

<p>Mesosphere is a software solution that expands upon the cluster management capabilities of Apache Mesos with additional components to provide a new and novel way to manage server infrastructures. By combining several components with Mesos, such as Marathon and Chronos, Mesosphere enables a way to easily scale applications by abstracting away many of the challenges associated with scaling.</p>

<p>Mesosphere provides features such as application scheduling, scaling, fault-tolerance, and self-healing. It also provides application service discovery, port unification, and end-point elasticity.</p>

<p>To give a better idea of how Mesosphere provides the aforementioned features, we will briefly explain what each key component of Mesosphere does, starting with Apache Mesos, and show how each is used in the context of Mesosphere.</p>

<h2 id="a-basic-overview-of-apache-mesos">A Basic Overview of Apache Mesos</h2>

<p>Apache Mesos is an open source cluster manager that simplifies running applications on a scalable cluster of servers, and is the heart of the Mesosphere system.</p>

<p>Mesos offers many of the features that you would expect from a cluster manager, such as:</p>

<ul>
<li>Scalability to over 10,000 nodes</li>
<li>Resource isolation for tasks through Linux Containers</li>
<li>Efficient CPU and memory-aware resource scheduling</li>
<li>Highly-available master through Apache ZooKeeper</li>
<li>Web UI for monitoring cluster state</li>
</ul>

<h3 id="mesos-architecture">Mesos Architecture</h3>

<p>Mesos has an architecture that is composed of master and slave daemons, and frameworks. Here is a quick breakdown of these components, and some relevant terms:</p>

<ul>
<li><strong>Master daemon</strong>: runs on a master node and manages slave daemons</li>
<li><strong>Slave daemon</strong>: runs on a master node and runs tasks that belong to frameworks</li>
<li><strong>Framework</strong>: also known as a Mesos application, is composed of a <em>scheduler</em>, which registers with the master to receive resource <em>offers</em>, and one or more <em>executors</em>, which launches <em>tasks</em> on slaves. Examples of Mesos frameworks include Marathon, Chronos, and Hadoop</li>
<li><strong>Offer</strong>: a list of a slave node's available CPU and memory resources. All slave nodes send offers to the master, and the master provides offers to registered frameworks</li>
<li><strong>Task</strong>: a unit of work that is scheduled by a framework, and is executed on a slave node. A task can be anything from a bash command or script, to an SQL query, to a Hadoop job</li>
<li><strong>Apache ZooKeeper</strong>: software that is used to coordinate the master nodes</li>
</ul>

<p><img src="https://assets.digitalocean.com/articles/mesosphere/mesos_architecture.png" alt="Mesos Architecture" /></p>

<p><strong>Note:</strong> "ZK" represents ZooKeeper in this diagram.</p>

<p>This architecture allows Mesos to share the cluster's resources amongst applications with a high level of granularity. The amount of resources offered to a particular framework is based on the policy set on the master, and the framework scheduler decides which of the offers to use. Once the framework scheduler decides which offers it wants to use, it tells Mesos which tasks should be executed, and Mesos launches the tasks on the appropriate slaves. After tasks are completed, and the consumed resources are freed, the resource offer cycle repeats so more tasks can be scheduled.</p>

<h3 id="high-availability">High Availability</h3>

<p>High availability of Mesos masters in a cluster is enabled through the use of Apache ZooKeeper to replicate the masters to form a <em>quorum</em>. ZooKeeper also coordinates master leader election and handles leader detection amongst Mesos components, including slaves and frameworks.</p>

<p>At least three master nodes are required for a highly-available configuration--a three master setup allows quorum to be maintained in the event that a single master fails--but five master nodes are recommended for a resilient production environment, allowing quorum to be maintained with two master nodes offline.</p>

<p>For more about Apache Mesos, visit <a href="http://mesos.apache.org/documentation/latest/">its official documentation page</a>.</p>

<h2 id="a-basic-overview-of-marathon">A Basic Overview of Marathon</h2>

<p>Marathon is a framework for Mesos that is designed to launch long-running applications, and, in Mesosphere, serves as a replacement for a traditional <code>init</code> system. It has many features that simplify running applications in a clustered environment, such as high-availability, node constraints, application health checks, an API for scriptability and service discovery, and an easy to use web user interface. It adds its scaling and self-healing capabilities to the Mesosphere feature set.</p>

<p>Marathon can be used to start other Mesos frameworks, and it can also launch any process that can be started in the regular shell. As it is designed for long-running applications, it will ensure that applications it has launched will continue running, even if the slave node(s) they are running on fails.</p>

<p>For more about Marathon, visit <a href="https://github.com/mesosphere/marathon">its GitHub page</a>.</p>

<h2 id="a-basic-overview-of-chronos">A Basic Overview of Chronos</h2>

<p>Chronos is a framework for Mesos that was originally developed by Airbnb as a replacement for <code>cron</code>. As such, it is a fully-featured, distributed, and fault-tolerant scheduler for Mesos, which eases the orchestration of jobs, which are collections of tasks. It includes an API that allows for scripting of scheduling jobs, and a web UI for ease of use.</p>

<p>In Mesosphere, Chronos compliments Marathon as it provides another way to run applications, according to a schedule or other conditions, such as the completion of another job. It is also capable of scheduling jobs on multiple Mesos slave nodes, and provides statistics about job failures and successes.</p>

<p>For more about Chronos, visit <a href="https://github.com/mesosphere/chronos">its GitHub page</a>.</p>

<h2 id="a-basic-overview-of-haproxy">A Basic Overview of HAProxy</h2>

<p>HAProxy is a popular open source load balancer and reverse proxying solution. It can be used in Mesosphere to route network traffic from known hosts, typically Mesos masters, to the actual services that are running on Mesos slave nodes. The service discovery capabilities of Mesos can be used to dynamically configure HAProxy to route incoming traffic to the proper backend slave nodes.</p>

<p>For more about the general capabilities of HAProxy, check out our <a href="https://indiareads/community/tutorials/an-introduction-to-haproxy-and-load-balancing-concepts">Introduction to HAProxy</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>Mesosphere employs server infrastructure paradigms that may seem unfamiliar, as it was designed with a strong focus on clustering and scalability, but hopefully you now have a good understanding of how it works. Each of the components it is based on provides solutions to issues that are commonly faced when dealing with clustering and scaling a server infrastructure, and Mesosphere aims to provide a complete solution to these needs.</p>

<p>Now that you know the basics of Mesosphere, check out the next tutorial in this series. It will teach you <a href="https://indiareads/community/tutorials/how-to-configure-a-production-ready-mesosphere-cluster-on-ubuntu-14-04">how to set up a production-ready Mesosphere cluster on Ubuntu 14.04</a>!</p>

    