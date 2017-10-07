<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>This tutorial covers the process of provisioning a basic PHP application using Ansible. The goal at the end of this tutorial is to have your new web server serving a basic PHP application without a single SSH connection or manual command run on the target Droplet.</p>

<p>We will be using the <a href="https://github.com/laravel/laravel">Laravel framework</a> as an example PHP application, but these instructions can be easily modified to support other frameworks and applications if you already have your own.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>For this tutorial, we will be using Ansible to install and configure Nginx, PHP, and other services on a Ubuntu 14.04 Droplet. This tutorial builds on basic Ansible knowledge, so if you are new to Ansible, you can read through <a href="https://indiareads/community/tutorials/how-to-install-and-configure-ansible-on-an-ubuntu-12-04-vps">this basic Ansible tutorial</a> first.</p>

<p>To follow this tutorial, you will need:</p>

<ul>
<li><p>One Ubuntu 14.04 Droplet of any size that we will be using to configure and deploy our PHP applicaton onto. The IP address of this machine will be referred to as <code><span class="highlight">your_server_ip</span></code> throughout the tutorial.</p></li>
<li><p>One Ubuntu 14.04 Droplet which will be used for Ansible. This is the Droplet you will be logged into for the entirety of this tutorial.</p></li>
<li><p><a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Sudo non-root users</a> configured for both Droplets.</p></li>
<li><p>SSH keys for the Ansible Droplet to authorize login on the PHP deployment Droplet, which you can set up by following <a href="https://indiareads/community/tutorials/how-to-set-up-ssh-keys--2">this tutorial</a> on your Ansible Droplet.</p></li>
</ul>

<h2 id="step-1-—-installing-ansible">Step 1 — Installing Ansible</h2>

<p>The first step is to install Ansible. This is easily accomplished by installing the PPA (Personal Package Archive), and installing the Ansible package with <code>apt</code>.</p>

<p>First, add the PPA using the <code>apt-add-repository</code> command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-add-repository ppa:ansible/ansible
</li></ul></code></pre>
<p>Once that has finished, update the <code>apt</code> cache.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li></ul></code></pre>
<p>Finally, install Ansible.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get install ansible
</li></ul></code></pre>
<p>Once Ansible is installed, we'll create a new directory to work in and set up a basic configuration. By default, Ansible uses a hosts file located at <code>/etc/ansible/hosts</code>, which contains all of the servers it is managing. While that file is fine for some use cases, it's global, which isn't what we want here.</p>

<p>For this tutorial, we will create a local hosts file and use that instead. We can do this by creating a new Ansible configuration file within our working directory, which we can use to tell Ansible to look for a hosts file within the same directory.</p>

<p>Create a new directory (which we will use for the rest of this tutorial).</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">mkdir ~/ansible-php
</li></ul></code></pre>
<p>Move into the new directory.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">cd ~/ansible-php/
</li></ul></code></pre>
<p>Create a new file called <code>ansible.cfg</code> and open it for editing using <code>nano</code> or your favorite text editor.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ansible.cfg
</li></ul></code></pre>
<p>Add in the <code>hostfile</code> configuration option with the value of <code>hosts</code> in the <code>[defaults]</code> group by copying the following into the <code>ansible.cfg</code> file.</p>
<div class="code-label " title="ansible.cfg">ansible.cfg</div><pre class="code-pre "><code langs="">[defaults]
hostfile = hosts
</code></pre>
<p>Save and close the <code>ansible.cfg</code> file. Next, we'll create the <code>hosts</code> file, which will contain the IP address of the PHP Droplet where we will deploy our application.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano hosts
</li></ul></code></pre>
<p>Copy the below to add in a section for <code>php</code>, replacing <code><span class="highlight">your_server_ip</span></code> with your server IP address and <code><span class="highlight">sammy</span></code> with the sudo non-root user you created in the prerequisites on your PHP Droplet. </p>
<div class="code-label " title="hosts">hosts</div><pre class="code-pre "><code langs="">[php]
<span class="highlight">your_server_ip</span> ansible_ssh_user=<span class="highlight">sammy</span>
</code></pre>
<p>Save and close the <code>hosts</code> file. Let's run a simple check to make sure Ansible is able to connect to the host as expected by calling the <code>ping</code> module on the new <code>php</code> group.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible php -m ping
</li></ul></code></pre>
<p>You may get an SSH host authentication check, depending on if you've ever logged into that host before. The ping should come back with a successful response, which looks something like this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">111.111.111.111 | success >> {
    "changed": false,
    "ping": "pong"
}
</code></pre>
<p>Ansible is now be installed and configured; we can move on to setting up our web server.</p>

<h2 id="step-2-—-installing-required-packages">Step 2 — Installing Required Packages</h2>

<p>In this step we will install some required system packages using Ansible and <code>apt</code>. In particular, we will install <code>git</code>, <code>nginx</code>, <code>sqlite3</code>, <code>mcrypt</code>, and a couple of <code>php5-*</code> packages.</p>

<p>Before we add in the <code>apt</code> module to install the packages we want, we need to create a basic playbook. We'll build on this playbook as we go through the tutorial. Create a new playbook called <code>php.yml</code>.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Paste in the following configuration. The first two lines specifies the hosts group we wish to use (<code>php</code>) and makes sure it runs commands with <code>sudo</code> by default. The rest adds in a module with the packages that we need. You can customize this for your own application, or use the configuration below if you're following along with the example Laravel application.</p>
<pre class="code-pre "><code langs="">---
- hosts: php
  sudo: yes

  tasks:

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
</code></pre>
<p>Save the <code>php.yml</code> file. Finally, run <code>ansible-playbook</code> to install the packages on the Droplet. Don't forget to use the <code>--ask-sudo-pass</code> option if your sudo user on your PHP Droplet requires a password.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<h2 id="step-3-—-modifying-system-configuration-files">Step 3 — Modifying System Configuration Files</h2>

<p>In this section we will modify some of the system configuration files on the PHP Droplet. The most important configuration option to change (aside from Nginx's files, which will be covered in a later step) is the <code>cgi.fix_pathinfo</code> option in <code>php5-fpm</code>, because the default value is a security risk.</p>

<p>We'll first explain all the sections we're going to add to this file, then include the entire <code>php.yml</code> file for you to copy and paste in.</p>

<p>The lineinfile module can be used to ensure the configuration value within the file is exactly as we expect it. This can be done using a generic <a href="https://indiareads/community/tutorials/an-introduction-to-regular-expressions">regular expression</a> so Ansible can understand most forms the parameter is likely to be in. We'll also need to restart <code>php5-fpm</code> and <code>nginx</code> to ensure the change takes effect, so we need to add in two handlers as well, in a new <code>handlers</code> section. Handlers are perfect for this, as they are only fired when the task changes. They also run at the end of the playbook, so multiple tasks can call the same handler and it will only run once.</p>

<p>The section to do the above will look like this:</p>
<pre class="code-pre "><code langs="">  - name: ensure php5-fpm cgi.fix_pathinfo=0
    lineinfile: dest=/etc/php5/fpm/php.ini regexp='^(.*)cgi.fix_pathinfo=' line=cgi.fix_pathinfo=0
    notify:
      - restart php5-fpm
      - restart nginx

  handlers:
    - name: restart php5-fpm
      service: name=php5-fpm state=restarted

    - name: restart nginx
      service: name=nginx state=restarted
</code></pre>
<div class="code-label notes-and-warnings note" title="Note: Ansible version 1.9.1 bug">Note: Ansible version 1.9.1 bug</div><span class="note"><p>

There is a bug with Ansible version 1.9.1 that prevents <code>php5-fpm</code> from being restarted with the <code>service</code> module, as we have used in our handlers.</p>

<p>Until a fix is released, you can work around this issue by changing the <code>restart php5-fpm</code> handler from using the <code>service</code> command to using the <code>shell</code> command, like this:</p>
<pre class="code-pre "><code langs="">    - name: restart php5-fpm
      <span class="highlight">shell: service php5-fpm restart</span>
</code></pre>
<p>This will bypass the issue and correctly restart <code>php5-fpm</code>.<br /></p></span>

<p>Next, we also need to ensure the <code>php5-mcrypt</code> module is enabled. This is done by running the <code>php5enmod</code> script with the shell task, and checking the <code>20-mcrypt.ini</code> file is in the right place when it's enabled. Note that we are telling Ansible that the task creates a specific file. If that file exists, the task won't be run.</p>
<pre class="code-pre "><code langs="">  - name: enable php5 mcrypt module
    shell: php5enmod mcrypt
    args:
      creates: /etc/php5/cli/conf.d/20-mcrypt.ini
</code></pre>
<p>Now, open <code>php.yml</code> for editing again.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above tasks and handlers, so the file matches the below:</p>
<pre class="code-pre "><code langs="">---
- hosts: php
  sudo: yes

  tasks:

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

  - name: ensure php5-fpm cgi.fix_pathinfo=0
    lineinfile: dest=/etc/php5/fpm/php.ini regexp='^(.*)cgi.fix_pathinfo=' line=cgi.fix_pathinfo=0
    notify:
      - restart php5-fpm
      - restart nginx

  - name: enable php5 mcrypt module
    shell: php5enmod mcrypt
    args:
      creates: /etc/php5/cli/conf.d/20-mcrypt.ini

  handlers:
    - name: restart php5-fpm
      service: name=php5-fpm state=restarted

    - name: restart nginx
      service: name=nginx state=restarted
</code></pre>
<p>Finally, run the playbook.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>The Droplet now has all the required packages installed and the basic configuration set up and ready to go.</p>

<h2 id="step-4-—-cloning-the-git-repository">Step 4 — Cloning the Git Repository</h2>

<p>In this section we will clone the Laravel framework repository onto our Droplet using Git. Like in Step 3, we'll explain all the sections we're going to add to the playbook, then include the entire <code>php.yml</code> file for you to copy and paste in.</p>

<p>Before we clone our Git repository, we need to make sure <code>/var/www</code> exists. We can do this by creating a task with the file module.</p>
<pre class="code-pre "><code langs="">- name: create /var/www/ directory
  file: dest=/var/www/ state=directory owner=www-data group=www-data mode=0700
</code></pre>
<p>As mentioned above, we need to use the Git module to clone the repository onto our Droplet. The process is simple because all we normally require for a <code>git clone</code> command is the source repository. In this case, we will also define the destination, and tell Ansible to not update the repository if it already exists by setting <code>update=no</code>. Because we are using Laravel, the git repository URL we will use is <code>https://github.com/laravel/laravel.git</code>.</p>

<p>However, we need to run the task as the <code>www-data</code> user to ensure that the permissions are correct. To do this, we can tell Ansible to run the command as a specific user using <code>sudo</code>. The final task will look like this:</p>
<pre class="code-pre "><code langs="">- name: Clone git repository
  git: >
    dest=/var/www/laravel
    repo=https://github.com/laravel/laravel.git
    update=no
  sudo: yes
  sudo_user: www-data
</code></pre>
<p><strong>Note</strong>: For SSH-based repositories you can add <code>accept_hostkey=yes</code> to prevent SSH host verification from hanging the task.</p>

<p>As before, open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the above tasks to the the playbook; the end of the file should match the following:</p>
<pre class="code-pre "><code langs="">...

  - name: enable php5 mcrypt module
    shell: php5enmod mcrypt
    args:
      creates: /etc/php5/cli/conf.d/20-mcrypt.ini

  - name: create /var/www/ directory
    file: dest=/var/www/ state=directory owner=www-data group=www-data mode=0700

  - name: Clone git repository
    git: >
      dest=/var/www/laravel
      repo=https://github.com/laravel/laravel.git
      update=no
    sudo: yes
    sudo_user: www-data

  handlers:
    - name: restart php5-fpm
      service: name=php5-fpm state=restarted

    - name: restart nginx
      service: name=nginx state=restarted
</code></pre>
<p>Save and close the playbook, then run it.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<h2 id="step-5-—-creating-an-application-with-composer">Step 5 — Creating an Application with Composer</h2>

<p>In this step, we will use Composer to install the PHP application and its dependencies.</p>

<p>Composer has a <code>create-project</code> command that installs all of the required dependencies and then runs the project creation steps defined in the <code>post-create-project-cmd</code> section of the <code>composer.json</code> file. This is the best way to ensure the application is set up correctly for its first use.</p>

<p>We can use the following Ansible task to download and install Composer globally as <code>/usr/local/bin/composer</code>. It will then be accessible by anyone using the Droplet, including Ansible.</p>
<pre class="code-pre "><code langs="">- name: install composer
  shell: curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  args:
    creates: /usr/local/bin/composer
</code></pre>
<p>With Composer installed, there is a Composer module that we can use. In our case, we want to tell Composer where our project is (using the <code>working_dir</code> paramter), and to run the <code>create-project</code> command. We also need to add <code>optimize_autoloader=no</code> parameter, as this flag isn't supported by the <code>create-project</code> command. Like the <code>git</code> command, we also want to run this as the <code>www-data</code> user to ensure permissions are valid. Putting it all together, we get this task:</p>
<pre class="code-pre "><code langs="">- name: composer create-project
  composer: command=create-project working_dir=/var/www/laravel optimize_autoloader=no
  sudo: yes
  sudo_user: www-data
</code></pre>
<p><strong>Note</strong>: <code>create-project</code> task may take a significant amount of time on a fresh Droplet, as Composer will have an empty cache and will need download everything fresh.</p>

<p>Now, open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add the tasks above at the end of the <code>tasks</code> section, above <code>handlers</code>, so that the end of the playbook matches the following:</p>
<pre class="code-pre "><code langs="">...

  - name: Clone git repository
    git: >
      dest=/var/www/laravel
      repo=https://github.com/laravel/laravel.git
      update=no
    sudo: yes
    sudo_user: www-data

  - name: install composer
    shell: curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    args:
      creates: /usr/local/bin/composer

  - name: composer create-project
    composer: command=create-project working_dir=/var/www/laravel optimize_autoloader=no
    sudo: yes
    sudo_user: www-data

  handlers:
    - name: restart php5-fpm
      service: name=php5-fpm state=restarted

    - name: restart nginx
      service: name=nginx state=restarted
</code></pre>
<p>Finally, run the playbook.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>What would happen if we ran Ansible again now? The <code>composer create-project</code> would run again, and in the case of Laravel, this means a new <code>APP_KEY</code>. So what we want instead is to set that task to only run after a fresh clone. We can ensure that it is only run once by registering a variable with the results of the <code>git clone</code> task, and then checking those results within the <code>composer create-project</code> task. If the <code>git clone</code> task was <em>Changed</em>, then we run <code>composer create-project</code>, if not, it is skipped.</p>

<p><strong>Note:</strong> There appears to be a bug in some versions of the Ansible <code>composer</code> module, and it may output <em>OK</em> instead of <em>Changed</em>, as it ignores that scripts were executed even though no dependencies were installed.</p>

<p>Open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Find the <code>git clone</code> task. Add the <code>register</code> option to save the results of the task into the the <code>cloned</code> variable, like this:</p>
<pre class="code-pre "><code langs="">- name: Clone git repository
  git: >
    dest=/var/www/laravel
    repo=https://github.com/laravel/laravel.git
    update=no
  sudo: yes
  sudo_user: www-data
  <span class="highlight">register: cloned</span>
</code></pre>
<p>Next, find the <code>composer create-project</code> task. Add the <code>when</code> option to check the <code>cloned</code> variable to see if it has changed or not.</p>
<pre class="code-pre "><code langs="">- name: composer create-project
  composer: command=create-project working_dir=/var/www/laravel optimize_autoloader=no
  sudo: yes
  sudo_user: www-data
  <span class="highlight">when: cloned|changed</span>
</code></pre>
<p>Save the playbook, and run it:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>Now Composer will stop changing the <code>APP_KEY</code> each time it is run.</p>

<h2 id="step-6-—-updating-environment-variables">Step 6 — Updating Environment Variables</h2>

<p>In this step, we will update the environment variables for our application.</p>

<p>Laravel comes with a default <code>.env</code> file which sets the <code>APP_ENV</code> to <code>local</code> and <code>APP_DEBUG</code> to <code>true</code>. We want to swap them for <code>production</code> and <code>false</code>, respectively. This can be done simply using the <code>lineinfile</code> module with the following tasks.</p>
<pre class="code-pre "><code langs="">- name: set APP_DEBUG=false
  lineinfile: dest=/var/www/laravel/.env regexp='^APP_DEBUG=' line=APP_DEBUG=false

- name: set APP_ENV=production
  lineinfile: dest=/var/www/laravel/.env regexp='^APP_ENV=' line=APP_ENV=production
</code></pre>
<p>Open the <code>php.yml</code> file for editing.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add this task to the the playbook; the end of the file should match the following:</p>
<pre class="code-pre "><code langs="">...

  - name: composer create-project
    composer: command=create-project working_dir=/var/www/laravel optimize_autoloader=no
    sudo: yes
    sudo_user: www-data
    when: cloned|changed

  - name: set APP_DEBUG=false
    lineinfile: dest=/var/www/laravel/.env regexp='^APP_DEBUG=' line=APP_DEBUG=false

  - name: set APP_ENV=production
    lineinfile: dest=/var/www/laravel/.env regexp='^APP_ENV=' line=APP_ENV=production

  handlers:
    - name: restart php5-fpm
      service: name=php5-fpm state=restarted

    - name: restart nginx
      service: name=nginx state=restarted
</code></pre>
<p>Save and run the playbook:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>The <code>lineinfile</code> module is very useful for quick tweaks of any text file, and it's great for ensuring environment variables like this are set correctly.</p>

<h2 id="step-7-—-configuring-nginx">Step 7 — Configuring Nginx</h2>

<p>In this section we will configure a Nginx to serve the PHP application.</p>

<p>If you visit your Droplet in your web browser now (i.e. <code>http://<span class="highlight">your_server_ip</span>/</code>), you will see the Nginx default page instead of the Laravel new project page. This is because we still need to configure our Nginx web server to serve the application from the <code>/var/www/laravel/public</code> directory. To do this we need to update our Nginx default configuration with that directory, and add in support for <code>php-fpm</code>, so it can handle PHP scripts.</p>

<p>Create a new file called <code>nginx.conf</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano nginx.conf
</li></ul></code></pre>
<p>Save this server block within that file. You can check out Step 4 of <a href="https://indiareads/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04#step-four-%E2%80%94-configure-nginx-to-use-our-php-processor">this tutorial</a> for more details about this Nginx configuration; the modifications below are specifying where the Laravel public directory is and making sure Nginx uses the hostname we've defined in the <code>hosts</code> file as the <code>server_name</code> with the <code>inventory_hostname</code> variable.</p>
<div class="code-label " title="nginx.conf">nginx.conf</div><pre class="code-pre "><code langs="">server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /var/www/laravel/public;
    index index.php index.html index.htm;

    server_name {{ inventory_hostname }};

    location / {
        try_files $uri $uri/ =404;
    }

    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
    location = /50x.html {
        root /var/www/laravel/public;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
</code></pre>
<p>Save and close the <code>nginx.conf</code> file.</p>

<p>Now, we can use the template module to push our new configuration file across. The <code>template</code> module may look and sound very similar to the <code>copy</code> module, but there is a big difference. <code>copy</code> will copy one or more files across <strong>without making any changes</strong>, while <code>template</code> copies a single files and will resolve all variables within the the file. Because we have used <code>{{ inventory_hostname }}</code> within our config file, we use the <code>template</code> module so it is resolved into the IP address that we used in the <code>hosts</code> file. This way, we don't need to hard code the configuration files that Ansible uses.</p>

<p>However, as is usual when writing tasks, we need to consider the what will happen on the Droplet. Because we are changing the Nginx configuration, we need to restart Nginx and <code>php-fpm</code>. This is done using the <code>notify</code> options.</p>
<pre class="code-pre "><code langs="">- name: Configure nginx
  template: src=nginx.conf dest=/etc/nginx/sites-available/default
  notify:
    - restart php5-fpm
    - restart nginx
</code></pre>
<p>Open your <code>php.yml</code> file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano php.yml
</li></ul></code></pre>
<p>Add in this nginx task at the end of the tasks section. The entire <code>php.yml</code> file should now look like this:</p>
<div class="code-label " title="php.yml">php.yml</div><pre class="code-pre "><code langs="">---
- hosts: php
  sudo: yes

  tasks:

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

  - name: ensure php5-fpm cgi.fix_pathinfo=0
    lineinfile: dest=/etc/php5/fpm/php.ini regexp='^(.*)cgi.fix_pathinfo=' line=cgi.fix_pathinfo=0
    notify:
      - restart php5-fpm
      - restart nginx

  - name: enable php5 mcrypt module
    shell: php5enmod mcrypt
    args:
      creates: /etc/php5/cli/conf.d/20-mcrypt.ini

  - name: create /var/www/ directory
    file: dest=/var/www/ state=directory owner=www-data group=www-data mode=0700

  - name: Clone git repository
    git: >
      dest=/var/www/laravel
      repo=https://github.com/laravel/laravel.git
      update=no
    sudo: yes
    sudo_user: www-data
    register: cloned

  - name: install composer
    shell: curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    args:
      creates: /usr/local/bin/composer

  - name: composer create-project
    composer: command=create-project working_dir=/var/www/laravel optimize_autoloader=no
    sudo: yes
    sudo_user: www-data
    when: cloned|changed

  - name: set APP_DEBUG=false
    lineinfile: dest=/var/www/laravel/.env regexp='^APP_DEBUG=' line=APP_DEBUG=false

  - name: set APP_ENV=production
    lineinfile: dest=/var/www/laravel/.env regexp='^APP_ENV=' line=APP_ENV=production

  - name: Configure nginx
    template: src=nginx.conf dest=/etc/nginx/sites-available/default
    notify:
      - restart php5-fpm
      - restart nginx

  handlers:
    - name: restart php5-fpm
      service: name=php5-fpm state=restarted

    - name: restart nginx
      service: name=nginx state=restarted
</code></pre>
<p>Save and run the playbook again:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ansible-playbook php.yml --ask-sudo-pass
</li></ul></code></pre>
<p>Once it completes, go back to your browser and refresh. You should now see the Laravel new project page!</p>

<h3 id="conclusion">Conclusion</h3>

<p>This tutorial covers deploying a PHP application with a public repository. While it is perfect for learning how Ansible works, you won't always be working on fully open source projects with open repositories. This means that you will need to authenticate the <code>git clone</code> in Step 3 with your private repository. This can be very easily done using SSH keys.</p>

<p>For example, once you have your SSH deploy keys created and set on your repository, you can use Ansible to copy and configure them on your server before the <code>git clone</code> task:</p>
<pre class="code-pre "><code langs="">- name: create /var/www/.ssh/ directory
  file: dest=/var/www/.ssh/ state=directory owner=www-data group=www-data mode=0700

- name: copy private ssh key
  copy: src=deploykey_rsa dest=/var/www/.ssh/id_rsa owner=www-data group=www-data mode=0600
</code></pre>
<p>That should allow the server to correctly authenticate and deploy your application.</p>

<hr />

<p>You have just deployed a basic PHP application on a Ubuntu-based Nginx web server using Composer to manage dependencies! All of it has been completed without a needing to log directly into your PHP Droplet and run a single manual command.</p>

    