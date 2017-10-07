<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial is the second in a series about deploying PHP applications using Ansible on Ubuntu 14.04. The <a href="https://indiareads/community/tutorials/how-to-deploy-a-basic-php-application-using-ansible-on-ubuntu-14-04">first tutorial</a> covers the basic steps for deploying an application, and is a starting point for the steps outlined in this tutorial.</p>

<p>In this tutorial we will cover setting up SSH keys to support code deployment/publishing tools, configuring the system firewall, provisioning and configuring the database (including the password!), and setting up task schedulers (crons) and queue daemons. The goal at the end of this tutorial is for you to have a fully working PHP application server with the aforementioned advanced configuration.</p>

<p>Like the last tutorial, we will be using the <a href="https://github.com/laravel/laravel">Laravel framework</a> as our example PHP application. However, these instructions can be easily modified to support other frameworks and applications if you already have your own.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>This tutorial follows on directly from the end of <a href="https://indiareads/community/tutorials/how-to-deploy-a-basic-php-application-using-ansible-on-ubuntu-14-04">the first tutorial in the series</a>, and all of the configuration and files generated for that tutorial are required. If you haven't completed that tutorial yet, please do so first before continuing with this tutorial.</p>

<h2 id="step-1-—-switching-the-application-repository">Step 1 — Switching the Application Repository</h2>

<p>In this step, we will update the Git repository to a slightly customized example repository.</p>

<p>Because the default Laravel installation doesn't require the advanced features that we will be setting up in this tutorial, we will be switching the existing repository from the standard repository to an example repository with some debugging code added, just to show when things are working. The repository we will use is located at <code>https://github.com/do-community/do-ansible-adv-php</code>.</p>

<p>If you haven't done so already, change directories into <code>ansible-php</code> from the previous tutorial.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/ansible-php/
</li></ul></code></pre>
<p>Open up our existing playbook for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Find and update the "Clone git repository" task, so it looks like this.</p>
<div class="code-label " title="Updated Ansible task">Updated Ansible task</div><pre class="code-pre "><code langs="">- name: Clone git repository
  git: >
    dest=/var/www/laravel
    repo=<span class="highlight">https://github.com/do-community/do-ansible-adv-php</span>
    update=<span class="highlight">yes</span>
    <span class="highlight">version=example</span>
  sudo: yes
  sudo_user: www-data
  register: cloned
</code></pre>
<p>Save and run the playbook.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>When it has finished running, visit your server in your web browser (i.e. <code>http://<span class="highlight">your_server_ip</span>/</code>). You should see a message that says <strong>"could not find driver"</strong>.</p>

<p>This means we have successfully swapped out the default repository for our example repository, but the application cannot connect to the database. This is what we expect to see here, and we will install and set up the database later in the tutorial.</p>

<h2 id="step-2-—-setting-up-ssh-keys-for-deployment">Step 2 — Setting up SSH Keys for Deployment</h2>

<p>In this step, we will set up SSH keys that can be used for application code deployment scripts.</p>

<p>While Ansible is great for maintaining configuration and setting up servers and applications, tools like <a href="http://laravel.com/docs/5.0/envoy">Envoy</a> and <a href="https://github.com/rocketeers/rocketeer">Rocketeer</a> are often used to push code changes onto your server and run application commands remotely. Most of these tools require an SSH connection that can access the application installation directly. In our case, this means we need to configure SSH keys for the <code>www-data</code> user.</p>

<p>We will need the public key file for the user you wish to push your code from. This file is typically found at <code>~/.ssh/id_rsa.pub</code>. Copy that file into the <code>ansible-php</code> directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cp ~/.ssh/id_rsa.pub ~/ansible-php/deploykey.pub
</li></ul></code></pre>
<p>We can use the Ansible <code>authorized_key</code> module to install our public key within <code>/var/www/.ssh/authorized_keys</code>, which will allow the deployment tools to connect and access our application. The configuration only needs to know where the key is, using a lookup, and the user the key needs to be installed for (<code>www-data</code> in our case).</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Copy public key into /var/www
  authorized_key: user=www-data key="{{ lookup('file', 'deploykey.pub') }}"
</code></pre>
<p>We also need to set the <code>www-data</code> user's shell, so we can actually log in. Otherwise, SSH will allow the connection, but there will be no shell presented to the user. This can be done using the <code>user</code> module, and setting the shell to <code>/bin/bash</code> (or your preferred shell).</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Set www-data user shell
  user: name=www-data shell=/bin/bash
</code></pre>
<p>Now, open up the playbook for editing to add in the new tasks.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above tasks to your <code>php.yml</code> playbook; the end of the file should match the following. The additions are highlighted in red.</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

  - name: Configure nginx
    template: src=nginx.conf dest=/etc/nginx/sites-available/default
    notify:
      - restart php5-fpm
      - restart nginx

  <span class="highlight">- name: Copy public key into /var/www</span>
    <span class="highlight">authorized_key: user=www-data key="{{ lookup('file', 'deploykey.pub') }}"</span>

  <span class="highlight">- name: Set www-data user shell</span>
    <span class="highlight">user: name=www-data shell=/bin/bash</span>

  handlers:

. . .
</code></pre>
<p>Save and run the playbook.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>When Ansible finishes, you should be able to SSH in using the <code>www-data</code> user.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ssh www-data@<span class="highlight">your_server_ip</span>
</li></ul></code></pre>
<p>If you successfully log in, it's working! You can now log back out by entering <code>logout</code> or pressing <strong>CTRL+D</strong>.</p>

<p>We won't need to use that connection for any other steps in this tutorial, but it will be useful if you are setting up other tools, as mentioned above, or for general debugging and application maintenance as required.</p>

<h2 id="step-3-—-configuring-the-firewall">Step 3 — Configuring the Firewall</h2>

<p>In this step we will configure the firewall on the server to allow only connections for HTTP and SSH.</p>

<p>Ubuntu 14.04 comes with UFW (<em>Uncomplicated Firewall</em>) installed by default, and Ansible supports it with the <code>ufw</code> module. It has a number of powerful features and has been designed to be as simple as possible. It's perfectly suited for self-contained web servers that only need a couple of ports open. In our case, we want port 80 (HTTP) and port 22 (SSH) open. You may also want port 443 for HTTPS.</p>

<p>The <code>ufw</code> module has a number of different options which perform different tasks. The different tasks we need to perform are:</p>

<ol>
<li><p>Enable UFW and deny all incoming traffic by default.</p></li>
<li><p>Open the SSH port but rate limit it to prevent brute force attacks.</p></li>
<li><p>Open the HTTP port.</p></li>
</ol>

<p>This can be done with the following tasks, respectively.</p>
<div class="code-label " title="New Ansible tasks">New Ansible tasks</div><pre class="code-pre "><code langs="">- name: Enable UFW
  ufw: direction=incoming policy=deny state=enabled

- name: UFW limit SSH
  ufw: rule=limit port=ssh

- name: UFW open HTTP
  ufw: rule=allow port=http
</code></pre>
<p>As before, open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above tasks to the the playbook; the end of the file should match the following.</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

  - name: Copy public key into /var/www
    authorized_key: user=www-data key="{{ lookup('file', 'deploykey.pub') }}"

  - name: Set www-data user shell
    user: name=www-data shell=/bin/bash

  <span class="highlight">- name: Enable UFW</span>
    <span class="highlight">ufw: direction=incoming policy=deny state=enabled</span>

  <span class="highlight">- name: UFW limit SSH</span>
    <span class="highlight">ufw: rule=limit port=ssh</span>

  <span class="highlight">- name: UFW open HTTP</span>
    <span class="highlight">ufw: rule=allow port=http</span>

  handlers:

. . .
</code></pre>
<p>Save and run the playbook.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>When that has successfully completed, you should still be able to connect via SSH (using Ansible) or HTTP to your server; other ports will now be blocked.</p>

<p>You can verify the status of UFW at any time by running this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible php --sudo --ask-sudo-pass -m shell -a "ufw status verbose"
</li></ul></code></pre>
<p>Breaking down the Ansible command above:</p>

<ul>
<li><code>ansible</code>: Run a raw Ansible task, without a playbook.</li>
<li><code>php</code>: Run the task against the hosts in this group.</li>
<li><code>--sudo</code>: Run the command as <code>sudo</code>.</li>
<li><code>--ask-sudo-pass</code>: Prompt for the <code>sudo</code> password.</li>
<li><code>-m shell</code>: Run the <code>shell</code> module.</li>
<li><code>-a "ufw status verbose"</code>: The options to be passed into the module. Because it is a <code>shell</code> command, we pass the raw command (i.e. <code>ufw status verbose</code>) straight in without any <code>key=value</code> options.</li>
</ul>

<p>It should return something like this.</p>
<div class="code-label " title="UFW status output">UFW status output</div><pre class="code-pre "><code langs=""><span class="highlight">your_server_ip</span> | success | rc=0 >>
Status: active
Logging: on (low)
Default: deny (incoming), allow (outgoing), disabled (routed)
New profiles: skip

To                         Action      From
--                         ------      ----
22                         LIMIT IN    Anywhere
80                         ALLOW IN    Anywhere
22 (v6)                    LIMIT IN    Anywhere (v6)
80 (v6)                    ALLOW IN    Anywhere (v6)
</code></pre>
<h2 id="step-4-—-installing-the-mysql-packages">Step 4 — Installing the MySQL Packages</h2>

<p>In this step we will set up a MySQL database for our application to use.</p>

<p>The first step is to ensure that MySQL is installed on our server by simply adding the required packages to the install packages task at the top of our playbook. The packages we need are <code>mysql-server</code>, <code>mysql-client</code>, and <code>php5-mysql</code>. We will also need <code>python-mysqldb</code> so Ansible can communicate with MySQL.</p>

<p>As we are adding packages, we need to restart <code>nginx</code> and <code>php5-fpm</code> to ensure the new packages are usable by the application. In this case, we need MySQL to be available to PHP, so it can connect to the database.</p>

<p>One of the fantastic things about Ansible is that you can modify any of the tasks and re-run your playbook and the changes will be applied. This includes lists of options, like we have with the <code>apt</code> task.</p>

<p>As before, open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Find the <code>install packages</code> task, and update it to include the packages above:</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

- name: install packages
  apt: name={{ item }} update_cache=yes state=latest
  with_items:
    - git
    - mcrypt
    - nginx
    - php5-cli
    - php5-curl
    - php5-fpm
    - php5-intl
    - php5-json
    - php5-mcrypt
    - php5-sqlite
    - sqlite3
    <span class="highlight">- mysql-server</span>
    <span class="highlight">- mysql-client</span>
    <span class="highlight">- php5-mysql</span>
    <span class="highlight">- python-mysqldb</span>
  <span class="highlight">notify:</span>
    <span class="highlight">- restart php5-fpm</span>
    <span class="highlight">- restart nginx</span>

. . .
</code></pre>
<p>Save and run the playbook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<h2 id="step-5-—-setting-up-the-mysql-database">Step 5 — Setting up the MySQL Database</h2>

<p>In this step we will create a MySQL database for our application.</p>

<p>Ansible can talk directly to MySQL using the <code>mysql_</code>-prefaced modules (e.g. <code>mysql_db</code>, <code>mysql_user</code>). The <code>mysql_db</code> module provides a way to ensure a database with a specific name exists, so we can use a task like this to create the database.</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Create MySQL DB
  mysql_db: name=laravel state=present
</code></pre>
<p>We also need a valid user account with a known password to allow our application to connect to the database. One approach to this is to generate a password locally and save it in our Ansible playbook, but that is insecure and there is a better way.</p>

<p>We will generate the password using Ansible on the server itself and use it directly where it is needed. To generate a password, we will use the <code>makepasswd</code> command line tool, and ask for a 32-character password. Because <code>makepasswd</code> isn't default on Ubuntu, we will need to add that to the packages list too.</p>

<p>We will also tell Ansible to remember the output of the command (i.e. the password), so we can use it later in our playbook. However, because Ansible doesn't know if it has already run a <code>shell</code> command, we'll also create a file when we run that command. Ansible will check if the file exists, and if so, it will assume the command has already been run and won't run it again.</p>

<p>The task looks like this:</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Generate DB password
  shell: makepasswd --chars=32
  args:
    creates: /var/www/laravel/.dbpw
  register: dbpwd
</code></pre>
<p>Next, we need to create the actual MySQL database user with the password we specified. This is done using the <code>mysql_user</code> module, and we can use the <code>stdout</code> option on the variable we defined during the password generation task to get the raw output of the shell command, like this: <code>dbpwd.stdout</code>.</p>

<p>The <code>mysql_user</code> command accepts the name of the user and the privileges required. In our case, we want to create a user called <code>laravel</code> and give them full privileges on the <code>laravel</code> table. We also need to tell the task to only run when the <code>dbpwd</code> variable has <em>changed</em>, which will only be when the password generation task is run.</p>

<p>The task should look like this:</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Create MySQL User
  mysql_user: name=laravel password={{ dbpwd.stdout }} priv=laravel.*:ALL state=present
  when: dbpwd.changed
</code></pre>
<p>Putting this together, open the <code>php.yml</code> file for editing, so we can add in the above tasks.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Firstly, find the <code>install packages</code> task, and update it to include the <code>makepasswd</code> package.</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

- name: install packages
  apt: name={{ item }} update_cache=yes state=latest
  with_items:
    - git
    - mcrypt
    - nginx
    - php5-cli
    - php5-curl
    - php5-fpm
    - php5-intl
    - php5-json
    - php5-mcrypt
    - php5-sqlite
    - sqlite3
    - mysql-server
    - mysql-client
    - php5-mysql
    - python-mysqldb
    <span class="highlight">- makepasswd</span>
  notify:
    - restart php5-fpm
    - restart nginx

. . .
</code></pre>
<p>Then, add the password generation, MySQL database creation, and user creation tasks at the bottom.</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

  - name: UFW limit SSH
    ufw: rule=limit port=ssh

  - name: UFW open HTTP
    ufw: rule=allow port=http

  <span class="highlight">- name: Create MySQL DB</span>
    <span class="highlight">mysql_db: name=laravel state=present</span>

  <span class="highlight">- name: Generate DB password</span>
    <span class="highlight">shell: makepasswd --chars=32</span>
    <span class="highlight">args:</span>
      <span class="highlight">creates: /var/www/laravel/.dbpw</span>
    <span class="highlight">register: dbpwd</span>

  <span class="highlight">- name: Create MySQL User</span>
    <span class="highlight">mysql_user: name=laravel password={{ dbpwd.stdout }} priv=laravel.*:ALL state=present</span>
    <span class="highlight">when: dbpwd.changed</span>

  handlers:

. . .
</code></pre>
<p><strong>Do not run the playbook yet!</strong> You may have noticed that although we have created the MySQL user and database, we haven't done anything with the password. We will cover that in the next step. When using <code>shell</code> tasks within Ansible, it is always important to remember to complete the entire workflow that deals with the output/results of the task before running it to avoid having to manually log in and reset the state.</p>

<h2 id="step-6-—-configuring-the-php-application-for-the-database">Step 6 — Configuring the PHP Application for the Database</h2>

<p>In this step, we will save the MySQL database password into the <code>.env</code> file for the application.</p>

<p>Like we did in the last tutorial, we will update the <code>.env</code> file to include our newly created database credentials. By default Laravel's <code>.env</code> file contains these lines:</p>
<div class="code-label " title="Laravel .env file">Laravel .env file</div><pre class="code-pre "><code langs="">DB_HOST=localhost
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
</code></pre>
<p>We can leave the <code>DB_HOST</code> line as-is, but will update the other three using the following tasks, which are very similar to the tasks we used in the previous tutorial to set <code>APP_ENV</code> and <code>APP_DEBUG</code>.</p>
<div class="code-label " title="New Ansible tasks">New Ansible tasks</div><pre class="code-pre "><code langs="">- name: set DB_DATABASE
  lineinfile: dest=/var/www/laravel/.env regexp='^DB_DATABASE=' line=DB_DATABASE=laravel

- name: set DB_USERNAME
  lineinfile: dest=/var/www/laravel/.env regexp='^DB_USERNAME=' line=DB_USERNAME=laravel

- name: set DB_PASSWORD
  lineinfile: dest=/var/www/laravel/.env regexp='^DB_PASSWORD=' line=DB_PASSWORD={{ dbpwd.stdout }}
  when: dbpwd.changed
</code></pre>
<p>As we did with the MySQL user creation task, we have used the generated password variable (<code>dbpwd.stdout</code>) to populate the file with the password, and have added the <code>when</code> option to ensure it is only run when <code>dbpwd</code> has changed.</p>

<p>Now, because the <code>.env</code> file already existed before we added our password generation task, we will need to save the password to another file. The generation task can look for that file's existence (which we already set up within the task). We will also use the <code>sudo</code> and <code>sudo_user</code> options to tell Ansible to create the file as the <code>www-data</code> user.</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Save dbpw file
  lineinfile: dest=/var/www/laravel/.dbpw line="{{ dbpwd.stdout }}" create=yes state=present
  sudo: yes
  sudo_user: www-data
  when: dbpwd.changed
</code></pre>
<p>Open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above tasks to the the playbook; the end of the file should match the following.</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">
. . .

  - name: Create MySQL User
    mysql_user: name=laravel password={{ dbpwd.stdout }} priv=laravel.*:ALL state=present
    when: dbpwd.changed

  <span class="highlight">- name: set DB_DATABASE</span>
    <span class="highlight">lineinfile: dest=/var/www/laravel/.env regexp='^DB_DATABASE=' line=DB_DATABASE=laravel</span>

  <span class="highlight">- name: set DB_USERNAME</span>
    <span class="highlight">lineinfile: dest=/var/www/laravel/.env regexp='^DB_USERNAME=' line=DB_USERNAME=laravel</span>

  <span class="highlight">- name: set DB_PASSWORD</span>
    <span class="highlight">lineinfile: dest=/var/www/laravel/.env regexp='^DB_PASSWORD=' line=DB_PASSWORD={{ dbpwd.stdout }}</span>
    <span class="highlight">when: dbpwd.changed</span>

  <span class="highlight">- name: Save dbpw file</span>
    <span class="highlight">lineinfile: dest=/var/www/laravel/.dbpw line="{{ dbpwd.stdout }}" create=yes state=present</span>
    <span class="highlight">sudo: yes</span>
    <span class="highlight">sudo_user: www-data</span>
    <span class="highlight">when: dbpwd.changed</span>

  handlers:

. . .
</code></pre>
<p><strong>Again, do not run the playbook yet!</strong> We have one more step to complete before we can run the playbook.</p>

<h2 id="step-7-—-migrating-the-database">Step 7 — Migrating the Database</h2>

<p>In this step, we will run the database migrations to set up the database tables.</p>

<p>In Laravel, this is done by running the <code>migrate</code> command (i.e. <code>php artisan migrate --force</code>) within the Laravel directory. Note that we have added the <code>--force</code> flag because the <code>production</code> environment requires it.</p>

<p>The Ansible task to perform this looks like this.</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">  - name: Run artisan migrate
    shell: php /var/www/laravel/artisan migrate --force
    sudo: yes
    sudo_user: www-data
    when: dbpwd.changed
</code></pre>
<p>Now it is time to update our playbook. Open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above tasks to the the playbook; the end of the file should match the following.</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

  - name: Save dbpw file
    lineinfile: dest=/var/www/laravel/.dbpw line="{{ dbpwd.stdout }}" create=yes   state=present
    sudo: yes
    sudo_user: www-data
    when: dbpwd.changed

  <span class="highlight">- name: Run artisan migrate</span>
    <span class="highlight">shell: php /var/www/laravel/artisan migrate --force</span>
    <span class="highlight">sudo: yes</span>
    <span class="highlight">sudo_user: www-data</span>
    <span class="highlight">when: dbpwd.changed</span>

  handlers:

. . .
</code></pre>
<p>Finally, we can save and run the playbook.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>When that finishes executing, refresh the page in your browser and you should see a message that says:</p>
<div class="code-label " title="http://<span class=" highlight>your_server_ip/'>http://<span class="highlight">your_server_ip</span>/</div><pre class="code-pre "><code langs="">Queue: NO
Cron: NO
</code></pre>
<p>This means the database is set up correctly and working as expected, but we haven't yet set up cron tasks or the queue daemon.</p>

<h2 id="step-8-—-configuring-cron-tasks">Step 8 — Configuring cron Tasks</h2>

<p>In this step, we will set up any cron tasks that need to be configured.</p>

<p>Cron tasks are commands that run on a set schedule and can be used to perform any number of tasks for your application, like performing maintenance tasks or sending out email activity updates — essentially anything that needs to be done periodically without manual user intervention. Cron tasks can run as frequently as every minute, or as infrequently as you require.</p>

<p>Laravel comes with an Artisan command called <code>schedule:run</code> by default, which is designed to be run every minute and executes the defined scheduled tasks within the application. This means we only need to add a single cron task, if our application takes advantage of this feature.</p>

<p>Ansible has a <code>cron</code> module with a number of different options that translate directly into the different options you can configure via cron:</p>

<ul>
<li><code>job</code>: The command to execute. Required if state=present.</li>
<li><code>minute</code>, <code>hour</code>, <code>day</code>, <code>month</code>, and <code>weekday</code>: The minute, hour, day, month, or day of the week when the job should run, respectively.</li>
<li><code>special_time</code> (<code>reboot</code>, <code>yearly</code>, <code>annually</code>, <code>monthly</code>, <code>weekly</code>, <code>daily</code>, <code>hourly</code>): Special time specification nickname.</li>
</ul>

<p>By default, it will create a task that runs every minute, which is what we want. This means the task we want looks like this:</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Laravel Scheduler
  cron: >
    job="run-one php /var/www/laravel/artisan schedule:run 1>> /dev/null 2>&1"
    state=present
    user=www-data
    name="php artisan schedule:run"
</code></pre>
<p>The <code>run-one</code> command is a small helper in Ubuntu that ensures the command is only being run once. This means that if a previous <code>schedule:run</code> command is still running, it won't be run again. This is helpful to avoid a situation where a cron task becomes locked in a loop, and over time, more and more instances of the same task are started until the server runs out of resources.</p>

<p>As before, open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above task to the the playbook; the end of the file should match the following.</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

  - name: Run artisan migrate
    shell: php /var/www/laravel/artisan migrate --force
    sudo: yes
    sudo_user: www-data
    when: dbpwd.changed

  <span class="highlight">- name: Laravel Scheduler</span>
    <span class="highlight">cron: ></span>
      <span class="highlight">job="run-one php /var/www/laravel/artisan schedule:run 1>> /dev/null 2>&1"</span>
      <span class="highlight">state=present</span>
      <span class="highlight">user=www-data</span>
      <span class="highlight">name="php artisan schedule:run"</span>

  handlers:

. . .
</code></pre>
<p>Save and run the playbook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>Now, refresh the page in your browser. In a minute, it will update to look like this.</p>
<div class="code-label " title="http://<span class=" highlight>your_server_ip/'>http://<span class="highlight">your_server_ip</span>/</div><pre class="code-pre "><code langs="">Queue: NO
Cron: <span class="highlight">YES</span>
</code></pre>
<p>This means that the cron is working in the background correctly. As part of the example application, there is a cron job that is running every minute updating a status entry in the database so the application knows it is running.</p>

<h2 id="step-9-—-configuring-the-queue-daemon">Step 9 — Configuring the Queue Daemon</h2>

<p>Like the <code>schedule:run</code> Artisan command from step 8, Laravel also comes with a queue worker that can be started with the <code>queue:work --daemon</code> Artisan command. In this step we will configure the queue daemon worker for Laravel.</p>

<p>Queue workers are similar to cron jobs in that they run tasks in the background. The difference is that the application pushes jobs into the queue, either via actions performed by the user or from tasks scheduled through a cron job. Queue tasks are executed by the worker one at a time, and will be processed on-demand when they are found in the queue. Queue tasks are commonly used for work that takes time to execute, such as sending emails or making API calls to external services.</p>

<p>Unlike the <code>schedule:run</code> command, this isn't a command that needs to be run every minute. Instead, it needs to run as a daemon in the background constantly. A common way to do this is by using a third party package like <em>supervisord</em>, but that method requires understanding how to configure and manage said system. There is a much simpler way to implement it using cron and the <code>run-one</code> command.</p>

<p>We will create a cron entry to start the queue worker daemon, and use <code>run-one</code> to run it. This means that cron will start the process the first time it runs, and any subsequent cron runs will be ignored by <code>run-one</code> while the worker is running. As soon as the worker stops, <code>run-one</code> will allow the command to run again, and the queue worker will start again. It is an incredibly simple and easy to use method that saves you from needing to learn how to configure and use another tool.</p>

<p>With all of that in mind, we will create another cron task to run our queue worker.</p>
<div class="code-label " title="New Ansible task">New Ansible task</div><pre class="code-pre "><code langs="">- name: Laravel Queue Worker
  cron: >
    job="run-one php /var/www/laravel/artisan queue:work --daemon --sleep=30 --delay=60 --tries=3 1>> /dev/null 2>&1"
    state=present
    user=www-data
    name="Laravel Queue Worker"
</code></pre>
<p>As before, open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above task to the the playbook; the end of the file should match the following:</p>
<div class="code-label " title="Updated php.yml">Updated php.yml</div><pre class="code-pre "><code langs="">. . .

  - name: Laravel Scheduler
    cron: >
      job="run-one php /var/www/laravel/artisan schedule:run 1>> /dev/null 2>&1"
      state=present
      user=www-data
      name="php artisan schedule:run"

  <span class="highlight">- name: Laravel Queue Worker</span>
    <span class="highlight">cron: ></span>
      <span class="highlight">job="run-one php /var/www/laravel/artisan queue:work --daemon --sleep=30 --delay=60 --tries=3 1>> /dev/null 2>&1"</span>
      <span class="highlight">state=present</span>
      <span class="highlight">user=www-data</span>
      <span class="highlight">name="Laravel Queue Worker"</span>

  handlers:
. . .
</code></pre>
<p>Save and run the playbook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>Like before, refresh the page in your browser. After a minute, it will update to look like this:</p>
<div class="code-label " title="http://<span class=" highlight>your_server_ip/'>http://<span class="highlight">your_server_ip</span>/</div><pre class="code-pre "><code langs="">Queue: <span class="highlight">YES</span>
Cron: YES
</code></pre>
<p>This means that the queue worker is working in the background correctly. The cron job that we started in the last step pushes a job onto the queue. This job updates the database when it is run to show that it is working.</p>

<p>We now have a working example Laravel application which includes functioning cron jobs and queue workers.</p>

<h2 id="conclusion">Conclusion</h2>

<p>This tutorial covered the some of the more advanced topics when using Ansible for deploying PHP applications. All of the tasks used can be easily modified to suit most PHP applications (depending on their specific requirements), and it should give you a good starting point to set up your own playbooks for your applications.</p>

<p>We have not used a single SSH command as part of this tutorial (apart from checking the <code>www-data</code> user login), and everything — including the MySQL user password — has been set up automatically. After following this tutorial, your application is ready to go and supports tools to push code updates.</p>

    