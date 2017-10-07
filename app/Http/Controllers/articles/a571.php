<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>IPython is an interactive command-line interface to Python. Jupyter Notebook offers an interactive web interface to many languages, including IPython.</p>

<p>This article will walk you through setting up a server to run Jupyter Notebook as well as teach you how to connect to and use the notebook.  Jupyter notebooks (or simply notebooks) are documents produced by the Jupyter Notebook app which contain both computer code (e.g. Python) and rich text elements (paragraph, equations, figures, links, etc.) which aid in presenting reproducible research. </p>

<p>By the end of this guide, you will be able to run Python 2.7 code using Ipython and Jupyter Notebook running on a remote server.  For the purposes of this tutorial, Python 2 (2.7.x) is used since many of the data science, scientific computing, and high-performance computing libraries support 2.7 and not 3.0+. </p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To follow this tutorial, you will need the following:</p>

<ul>
<li>Ubuntu 16.04 Droplet</li>
<li>Non-root user with sudo privileges (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Initial Server Setup with Ubuntu 16.04</a> explains how to set this up.)</li>
</ul>

<p>All the commands in this tutorial should be run as a non-root user. If root access is required for the command, it will be preceded by <code>sudo</code>. <a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-16-04">Initial Server Setup with Ubuntu 16.04</a> explains how to add users and give them sudo access.</p>

<h2 id="step-1-—-installing-python-2-7-and-pip">Step 1 — Installing Python 2.7 and Pip</h2>

<p>In this section we will install Python 2.7 and Pip.  </p>

<p>First, update the system's package index. This will ensure that old or outdated packages do not interfere with the installation.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Next, install Python 2.7, Python Pip, and Python Development:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install python2.7 python-pip python-dev
</li></ul></code></pre>
<p>Installing <code>python2.7</code> will update to the latest version of Python 2.7, and <code>python-pip</code> will install Pip which allows us to manage Python packages we would like to use.  Some of Jupyter’s dependencies may require compilation, in which case you would need the ability to compile Python C-extensions, so we are installing <code>python-dev</code> as well.</p>

<p>To verify that you have python installed:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">python --version
</li></ul></code></pre>
<p>This will output:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Python 2.7.11+
</code></pre>
<p>Depending on the latest version of Python 2.7, the output might be different.</p>

<p>You can also check if pip is installed using the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">pip --version
</li></ul></code></pre>
<p>You should something similar to the following:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>pip 8.1.1 from /usr/lib/python2.7/dist-packages (python 2.7)
</code></pre>
<p>Similarly depending on your version of pip, the output might be slightly different. </p>

<h2 id="step-2-—-installing-ipython-and-jupyter-notebook">Step 2 — Installing Ipython and Jupyter Notebook</h2>

<p>In this section we will install Ipython and Jupyter Notebook.</p>

<p>First, install Ipython:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get -y install ipython ipython-notebook
</li></ul></code></pre>
<p>Now we can move on to installing Jupyter Notebook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -H pip install jupyter
</li></ul></code></pre>
<p>Depending on what version of pip is in the Ubuntu apt-get repository, you might get the following error when trying to install Jupyter:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>You are using pip version 8.1.1, however version 8.1.2 is available.
You should consider upgrading via the 'pip install --upgrade pip' command.
</code></pre>
<p>If so, you can use pip to upgrade pip to the latest version:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -H pip install --upgrade pip
</li></ul></code></pre>
<p>Upgrade pip, and then try installing Jupyter again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo -H pip install jupyter
</li></ul></code></pre>
<h2 id="step-3-—-running-jupyter-notebook">Step 3 — Running Jupyter Notebook</h2>

<p>You now have everything you need to run Jupyter Notebook! To run it, execute the following command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">jupyter notebook
</li></ul></code></pre>
<p>If you are running Jupyter on a system with JavaScript installed, it will still run, but it might give you an error stating that the Jupyter Notebook requires JavaScript:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>Jupyter Notebook requires JavaScript.
Please enable it to proceed.
...
</code></pre>
<p>To ignore the error, you can press <code>Q</code> and then press <code>Y</code> to confirm.  </p>

<p>A log of the activities of the Jupyter Notebook will be printed to the terminal.  When you run Jupyter Notebook, it runs on a specific port number. The first notebook you are running will usually run on port <code><span class="highlight">8888</span></code>.  To check the specific port number Jupyter Notebook is running on, refer to the output of the command used to start it:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[I NotebookApp] Serving notebooks from local directory: /home/sammy
[I NotebookApp] 0 active kernels 
[I NotebookApp] The Jupyter Notebook is running at: http://localhost<span class="highlight">:8888</span>/
[I NotebookApp] Use Control-C to stop this server and shut down all kernels (twice to skip confirmation).
</code></pre>
<p>If you are running Jupyter Notebook on a local Linux computer (not on a Droplet), you can simply navigate to <code>localhost<span class="highlight">:8888</span></code> to connect to Jupyter Notebook.  If you are running Jupyter Notebook on a Droplet, you will need to connect to the server using SSH tunneling as outlined in the next section.</p>

<p>At this point, you can keep the SSH connection open and keep Jupyter Notebook running or can exit the app and re-run it once you set up SSH tunneling.  Let's keep it simple and stop the Jupyter Notebook process. We will run it again once we have SSH tunneling working.  To stop the Jupyter Notebook process, press <code>CTRL+C</code>, type <code>Y</code>, and hit <code>ENTER</code> to confirm.  The following will be displayed:</p>
<pre class="code-pre "><code langs=""><div class="secondary-code-label " title="Output">Output</div>[C 12:32:23.792 NotebookApp] Shutdown confirmed
[I 12:32:23.794 NotebookApp] Shutting down kernels
</code></pre>
<h2 id="step-4-—-connecting-to-the-server-using-ssh-tunneling">Step 4 — Connecting to the Server Using SSH Tunneling</h2>

<p>In this section we will learn how to connect to the Jupyter Notebook web interface using SSH tunneling.  Since Jupyter Notebook is running on a specific port on the Droplet (such as <code>:8888</code>, <code>:8889</code> etc.), SSH tunneling enables you to connect to the Droplet's port securely.</p>

<p>The next two subsections describe how to create an SSH tunnel from 1) a Mac or Linux and 2) Windows. Please refer to the subsection for your local computer.</p>

<h3 id="ssh-tunneling-with-a-mac-or-linux">SSH Tunneling with a Mac or Linux</h3>

<p>If you are using a Mac or Linux, the steps for creating an SSH tunnel are similar to the <a href="https://indiareads/community/tutorials/how-to-use-ssh-keys-with-digitalocean-droplets">How To Use SSH Keys with IndiaReads Droplets using Linux or Mac</a> guide except there are additional parameters added in the <code>ssh</code> command.  This subsection will outline the additional parameters needed in the <code>ssh</code> command to tunnel successfully. </p>

<p>SSH tunneling can be done by running the following SSH command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh -L <span class="highlight">8000</span>:localhost:<span class="highlight">8888</span> <span class="highlight">your_server_username</span>@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>The <code>ssh</code> command opens an SSH connection, but <code>-L</code> specifies that the given port on the local (client) host is to be forwarded to the given host and port on the remote side (Droplet).  This means that whatever is running on the second port number (i.e. <code><span class="highlight">8888</span></code>) on the Droplet will appear on the first port number (i.e. <code><span class="highlight">8000</span></code>) on your local computer.  You should change <code><span class="highlight">8888</span></code> to the port which Jupyter Notebook is running on. Optionally change port <code><span class="highlight">8000</span></code> to one of your choosing (for example, if <code>8000</code> is used by another process). Use a port greater or equal to <code>8000</code> (ie <code><span class="highlight">8001</span></code>, <code><span class="highlight">8002</span></code>, etc.) to avoid using a port already in use by another process. <code><span class="highlight">server_username</span></code> is your username (i.e. sammy) on the Droplet which you created and <code><span class="highlight">your_server_ip</span></code> is the  IP address of your Droplet.  For example, for the username <code><span class="highlight">sammy</span></code> and the server address <code><span class="highlight">111.111.111.111</span></code>, the command would be:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh -L <span class="highlight">8000</span>:localhost:<span class="highlight">8888</span> <span class="highlight">sammy</span>@<span class="highlight">111.111.111.111</span>
</li></ul></code></pre>
<p>If no error shows up after running the <code>ssh -L</code> command, you can run Jupyter Notebook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">jupyter notebook
</li></ul></code></pre>
<p>Now, from a web browser on your local machine, open the Jupyter Notebook web interface with <code>http://localhost:<span class="highlight">8000</span></code> (or whatever port number you chose).</p>

<h3 id="ssh-tunneling-with-windows-and-putty">SSH Tunneling with Windows and Putty</h3>

<p>If you are using Windows, you can also easily create an SSH tunnel using Putty as outlined in <a href="https://indiareads/community/tutorials/how-to-use-ssh-keys-with-putty-on-digitalocean-droplets-windows-users">How To Use SSH Keys with PuTTY on IndiaReads Droplets (Windows users)</a>.  </p>

<p>First, enter the server URL or IP address as the hostname as shown:</p>

<p><img src="https://assets.digitalocean.com/articles/jupyter_notebook/set_hostname_putty.png" alt="Set Hostname for SSH Tunnel" /></p>

<p>Next, click <strong>SSH</strong> on the bottom of the left pane to expand the menu, and then click <strong>Tunnels</strong>.  Enter the local port number to use to access Jupyter on your local machine.  Choose  <code><span class="highlight">8000</span></code> or greater (ie <code><span class="highlight">8001</span></code>, <code><span class="highlight">8002</span></code>, etc.) to avoid ports used by other services, and set the destination as <code>localhost:<span class="highlight">8888</span></code> where <code><span class="highlight">:8888</span></code> is the number of the port that Jupyter Notebook is running on.  Now click the <strong>Add</strong> button, and the ports should appear in the <strong>Forwarded ports</strong> list:</p>

<p><img src="https://assets.digitalocean.com/articles/jupyter_notebook/forwarded_ports_putty.png" alt="Forwarded ports list" /></p>

<p>Finally, click the <strong>Open</strong> button to connect to the server via SSH and tunnel the desired ports.  Navigate to <code>http://localhost:<span class="highlight">8000</span></code> (or whatever port you chose) in a web browser to connect to Jupyter Notebook running on the server.</p>

<h2 id="step-5-—-using-jupyter-notebook">Step 5 — Using Jupyter Notebook</h2>

<p>This section goes over the basics of using Jupyter Notebook.  By this point you should have Jupyter Notebook running, and you should be connected to it using a web browser.  Jupyter Notebook is very powerful and has many features. This section will outline a few of the basic features to get you started using the notebook.  Automatically, Jupyter Notebook will show all of the files and folders in the directory it is run from.  </p>

<p>To create a new notebook file, select <strong>New</strong> > <strong>Python 2</strong> from the top right pull-down menu:</p>

<p><img src="https://assets.digitalocean.com/articles/jupyter_notebook/create_python2_notebook.png" alt="Create a new Python 2 notebook" /></p>

<p>This will open a notebook.  We can now run Python code in the cell or change the cell to markdown.  For example, change the first cell to accept Markdown by clicking <strong>Cell</strong> > <strong>Cell Type</strong> > <strong>Markdown</strong> from the top navigation bar.  We can now write notes using Markdown and even include equations written in LaTeX by putting them between the <code>$$</code> symbols.  For example, type the following into the cell after changing it to markdown:</p>
<pre class="code-pre "><code langs=""># Simple Equation

Let us now implement the following equation:
$$ y = x^2$$

where $x = 2$
</code></pre>
<p>To turn the markdown into rich text, press <code>CTRL+ENTER</code>, and the following should be the results:</p>

<p><img src="https://assets.digitalocean.com/articles/jupyter_notebook/markdown_results.png" alt="results of markdown" /></p>

<p>You can use the markdown cells to make notes and document your code.  Let's implement that simple equation and print the result.   Select <strong>Insert</strong> > <strong>Insert Cell Below</strong> to insert and cell and enter the following code:</p>
<pre class="code-pre "><code langs="">x = 2
y = x*x
print y
</code></pre>
<p>To run the code, press <code>CTRL+ENTER</code>. The following should be the results:</p>

<p><img src="https://assets.digitalocean.com/articles/jupyter_notebook/equations_results.png" alt="simple equation results" /></p>

<p>You now have the ability to include libraries and use the notebook as you would with any other Python development environment!</p>

<h2 id="conclusion">Conclusion</h2>

<p>Congratulations! You should be now able to write reproducible Python code and notes using markdown using Jupyter notebook running on a Droplet.  To get a quick tour of Jupyter notebook, select <strong>Help</strong> > <strong>User Interface Tour</strong> from the top navigation menu.</p>

    