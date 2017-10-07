<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/puppet__module_WP_tw.png?1426699749/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Puppet is a configuration management tool that system administrators use to automate the processes involved in maintaining a company's IT infrastructure. Writing individual Puppet manifest files is sufficient for automating simple tasks. However, when you have an entire workflow to automate, it is ideal to create and use a Puppet module instead. A Puppet module is just a collection of manifests along with files that those manifests require, neatly bundled into a reusable and shareable package.</p>

<p>WordPress is a very popular blogging platform. As an administrator, you might find yourself installing WordPress and its dependencies (Apache, PHP, and MySQL) very often. This installation process is a good candidate for automation, and today we create a Puppet module that does just that.</p>

<h2 id="what-this-tutorial-includes">What This Tutorial Includes</h2>

<p>In this tutorial you will create a Puppet module that can perform the following activities:</p>

<ul>
<li>Install Apache and PHP</li>
<li>Install MySQL</li>
<li>Create a database and a database user on MySQL for WordPress</li>
<li>Install and configure WordPress</li>
</ul>

<p>You will then create a simple manifest that uses the module to set up WordPress on Ubuntu 14.04. At the end of this tutorial, you will have a reusable WordPress module and a working WordPress installation on the server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>You will need the following:</p>

<ul>
<li><strong>Ubuntu 14.04</strong> server</li>
<li>A <a href="https://indiareads/community/tutorials/how-to-add-and-delete-users-on-an-ubuntu-14-04-vps">sudo</a> user</li>
<li>You shoud understand how to manage WordPress once you get to the control panel setup. If you need help with that, check the later parts of <a href="https://indiareads/community/tutorials/how-to-install-wordpress-on-ubuntu-14-04#step-five-%E2%80%94-complete-installation-through-the-web-interface">this tutorial</a></li>
</ul>

<h2 id="step-1-—-install-puppet-in-standalone-mode">Step 1 — Install Puppet in Standalone Mode</h2>

<p>To install Puppet using <span class="highlight">apt-get</span>, the Puppet Labs Package repository has to be added to the list of available repositories. Puppet Labs has a Debian package that does this. The name of this package depends on the version of Ubuntu you are using. As this tutorial uses Ubuntu 14.04, Trusty Tahr, you have to download and install <code>puppetlabs-release-trusty.deb</code>.</p>

<p>Create and move into your <code>Downloads</code> directory:</p>
<pre class="code-pre "><code langs="">mkdir ~/Downloads
cd ~/Downloads
</code></pre>
<p>Get the package:</p>
<pre class="code-pre "><code langs="">wget https://apt.puppetlabs.com/puppetlabs-release-trusty.deb
sudo dpkg -i puppetlabs-release-trusty.deb
</code></pre>
<p>You can now install Puppet using <code>apt-get</code>.</p>
<pre class="code-pre "><code langs="">sudo apt-get update
sudo apt-get install puppet
</code></pre>
<p>Puppet is now installed. You can check by typing in:</p>
<pre class="code-pre "><code langs="">sudo puppet --version
</code></pre>
<p>It should print Puppet's version. At the time of this writing, the latest version is <strong>3.7.1</strong>.</p>

<p><strong>Note</strong>: If you see a warning message about <span class="highlight">templatedir</span>, check the solution in Step 2.</p>

<h2 id="step-2-install-apache-and-mysql-modules">Step 2 - Install Apache and MySQL Modules</h2>

<p>Managing Apache and MySQL are such common activities that PuppetLabs has its own modules for them. We'll use these modules to install and configure Apache and MySQL.</p>

<p>You can list all the Puppet modules installed on your system using the following command:</p>
<pre class="code-pre "><code langs="">sudo puppet module list
</code></pre>
<p>You will find no modules currently installed.</p>

<p>You might see a warning message that says:</p>
<pre class="code-pre "><code langs="">Warning: Setting templatedir is deprecated. See http://links.puppetlabs.com/env-settings-deprecations
(at /usr/lib/ruby/vendor_ruby/puppet/settings.rb:1071:in `each')
</code></pre>
<p>To remove this warning, use <span class="highlight">nano</span> to edit the <span class="highlight">puppet.conf</span> file, and comment out the <span class="highlight">templatedir</span> line:</p>
<pre class="code-pre "><code langs="">sudo nano /etc/puppet/puppet.conf
</code></pre>
<p>After the edits, the file should have the following contents. You are just commenting out the <span class="highlight">templatedir</span> line:</p>
<pre class="code-pre "><code langs="">[main]
logdir=/var/log/puppet
vardir=/var/lib/puppet
ssldir=/var/lib/puppet/ssl
rundir=/var/run/puppet
factpath=$vardir/lib/facter
<span class="highlight">#templatedir=$confdir/templates</span>

[master]
# These are needed when the puppetmaster is run by passenger
# and can safely be removed if webrick is used.
ssl_client_header = SSL_CLIENT_S_DN
ssl_client_verify_header = SSL_CLIENT_VERIFY
</code></pre>
<p>That should remove the warning message.</p>

<p>Install the PuppetLabs Apache and MySQL modules:</p>
<pre class="code-pre "><code langs="">sudo puppet module install puppetlabs-apache
sudo puppet module install puppetlabs-mysql
</code></pre>
<p>Verify the installation by listing the modules again:</p>
<pre class="code-pre "><code langs="">sudo puppet module list
</code></pre>
<p>You should be able to see the Apache and MySQL modules in the list.</p>
<pre class="code-pre "><code langs="">/etc/puppet/modules
├── puppetlabs-apache (v1.1.1)
├── puppetlabs-concat (v1.1.1)
├── puppetlabs-mysql (v2.3.1)
└── puppetlabs-stdlib (v4.3.2)
</code></pre>
<h2 id="step-3-create-a-new-module-for-wordpress">Step 3 - Create a New Module for WordPress</h2>

<p>Create a new directory to keep all your custom modules.</p>
<pre class="code-pre "><code langs="">mkdir ~/MyModules
cd ~/MyModules
</code></pre>
<p>Let us call our module <span class="highlight">do-wordpress</span>. Generate the generic new module:</p>
<pre class="code-pre "><code langs="">puppet module generate do-wordpress --skip-interview
</code></pre>
<p>If you don't include the <span class="highlight">--skip-interview</span> flag, the command will be interactive, and will prompt you with various questions about the module to populate the <span class="highlight">metadata.json</span> file.</p>

<p>At this point a new directory named <span class="highlight">do-wordpress</span> has been created. It contains boilerplate code and a directory structure that is necessary to build the module.</p>

<p>Edit the <span class="highlight">metadata.json</span> file to replace <span class="highlight">puppetlabs-stdlib</span> with <span class="highlight">puppetlabs/stdlib</span>.</p>
<pre class="code-pre "><code langs="">nano ~/MyModules/do-wordpress/metadata.json
</code></pre>
<p>This edit is required due a currently open <a href="https://tickets.puppetlabs.com/browse/PUP-3121">bug</a> in Puppet. After the change, your <span class="highlight">metadata.json</span> file should look like this:</p>
<pre class="code-pre "><code langs="">{
  "name": "do-wordpress",
  "version": "0.1.0",
  "author": "do",
  "summary": null,
  "license": "Apache 2.0",
  "source": "",
  "project_page": null,
  "issues_url": null,
  "dependencies": [
    {"name":<span class="highlight">"puppetlabs/stdlib"</span>,"version_requirement":">= 1.0.0"}
  ]
}
</code></pre>
<h2 id="step-4-create-a-manifest-to-install-apache-and-php">Step 4 - Create a Manifest to Install Apache and PHP</h2>

<p>Use <span class="highlight">nano</span> to create and edit a file named <span class="highlight">web.pp</span> in the <span class="highlight">manifests</span> directory, which will install Apache and PHP:</p>
<pre class="code-pre "><code langs="">nano ~/MyModules/do-wordpress/manifests/web.pp
</code></pre>
<p>Install Apache and PHP with default parameters. We use <span class="highlight">prefork</span> as the MPM (Multi-Processing Module) to maximize compatibility with other libraries.</p>

<p>Add the following code to the file exactly:</p>
<pre class="code-pre "><code langs="">class wordpress::web {

    # Install Apache
    class {'apache': 
        mpm_module => 'prefork'
    }

    # Add support for PHP 
    class {'::apache::mod::php': }
}
</code></pre>
<h2 id="step-5-create-a-file-to-store-configuration-variables">Step 5 - Create a File to Store Configuration Variables</h2>

<p>Use <span class="highlight">nano</span> to create and edit a file named <span class="highlight">conf.pp</span> in the <span class="highlight">manifests</span> directory.</p>
<pre class="code-pre "><code langs="">nano ~/MyModules/do-wordpress/manifests/conf.pp
</code></pre>
<p>This file is the one place where you should set custom configuration values such as passwords and names. Every other configuration file on the system will pull its values from this file.</p>

<p>In the future, if you need to change the Wordpress/MySQL configuration, you will have to change only this file.</p>

<p>Add the following code to the file. Make sure you replace the database values with the custom information you want to use with WordPress. You will most likely want to leave <span class="highlight">db<em>host</em></span> set to <span class="highlight">localhost</span>. You <strong>should</strong> change the <span class="highlight">rootpassword</span> and <span class="highlight">db<em>user</em>password</span>.</p>

<p>Variables that you can or should edit are marked in <span class="highlight">red</span>:</p>
<pre class="code-pre "><code langs="">class wordpress::conf {
    # You can change the values of these variables
    # according to your preferences

    $root_password = '<span class="highlight">password</span>'
    $db_name = '<span class="highlight">wordpress</span>'
    $db_user = '<span class="highlight">wp</span>'
    $db_user_password = '<span class="highlight">password</span>'
    $db_host = '<span class="highlight">localhost</span>'

    # Don't change the following variables

    # This will evaluate to wp@localhost
    $db_user_host = "${db_user}@${db_host}"

    # This will evaluate to wp@localhost/wordpress.*
    $db_user_host_db = "${db_user}@${db_host}/${db_name}.*"
}
</code></pre>
<h2 id="step-6-create-a-manifest-for-mysql">Step 6 - Create a Manifest for MySQL</h2>

<p>Use <span class="highlight">nano</span> to create and edit a file named <span class="highlight">db.pp</span> in the <span class="highlight">manifests</span> directory:</p>
<pre class="code-pre "><code langs="">nano ~/MyModules/do-wordpress/manifests/db.pp
</code></pre>
<p>This manifest does the following:</p>

<ul>
<li>Installs MySQL server</li>
<li>Sets the root password for MySQL server</li>
<li>Creates a database for Wordpress</li>
<li>Creates a user for Wordpress</li>
<li>Grants privileges to the user to access the database</li>
<li>Installs MySQL client and bindings for various languages</li>
</ul>

<p>All of the above actions are performed by the classes <code>::mysql::server</code> and <code>::mysql::client</code>.</p>

<p>Add the following code to the file exactly as shown. Inline comments are included to provide a better understanding:</p>
<pre class="code-pre "><code langs="">class wordpress::db {

    class { '::mysql::server':

        # Set the root password
        root_password => $wordpress::conf::root_password,

        # Create the database
        databases => {
            "${wordpress::conf::db_name}" => {
                ensure => 'present',
                charset => 'utf8'
            }
        },

        # Create the user
        users => {
            "${wordpress::conf::db_user_host}" => {
                ensure => present,
                password_hash => mysql_password("${wordpress::conf::db_user_password}")
            }
        },

        # Grant privileges to the user
        grants => {
            "${wordpress::conf::db_user_host_db}" => {
                ensure     => 'present',
                options    => ['GRANT'],
                privileges => ['ALL'],
                table      => "${wordpress::conf::db_name}.*",
                user       => "${wordpress::conf::db_user_host}",
            }
        },
    }

    # Install MySQL client and all bindings
    class { '::mysql::client':
        require => Class['::mysql::server'],
        bindings_enable => true
    }
}
</code></pre>
<h2 id="step-7-download-the-latest-wordpress">Step 7 - Download the Latest WordPress</h2>

<p>Download the latest WordPress installation bundle from the <a href="http://wordpress.org">official website</a> using <span class="highlight">wget</span> and store it in the <span class="highlight">files</span> directory.</p>

<p>Create and move to a new directory:</p>
<pre class="code-pre "><code langs="">mkdir ~/MyModules/do-wordpress/files
cd ~/MyModules/do-wordpress/files
</code></pre>
<p>Download the files:</p>
<pre class="code-pre "><code langs="">wget http://wordpress.org/latest.tar.gz
</code></pre>
<h2 id="step-8-create-a-template-for-wp-config-php">Step 8 - Create a Template for wp-config.php</h2>

<p>You might already know that Wordpress needs a <span class="highlight">wp-config.php</span> file that contains information about the MySQL database that it is allowed to use. A template is used so that Puppet can generate this file with the right values.</p>

<p>Create a new directory named <span class="highlight">templates</span>.</p>
<pre class="code-pre "><code langs="">mkdir ~/MyModules/do-wordpress/templates
</code></pre>
<p>Move into the <span class="highlight">/tmp</span> directory:</p>
<pre class="code-pre "><code langs="">cd /tmp
</code></pre>
<p>Extract the WordPress files:</p>
<pre class="code-pre "><code langs="">tar -xvzf ~/MyModules/do-wordpress/files/latest.tar.gz  # Extract the tar
</code></pre>
<p>The <span class="highlight">latest.tar.gz</span> file that you downloaded contains a <span class="highlight">wp-config-sample.php</span> file. Copy the file to the <span class="highlight">templates</span> directory as <span class="highlight">wp-config.php.erb</span>.</p>
<pre class="code-pre "><code langs="">cp /tmp/wordpress/wp-config-sample.php ~/MyModules/do-wordpress/templates/wp-config.php.erb
</code></pre>
<p>Clean up the <span class="highlight">/tmp</span> directory:</p>
<pre class="code-pre "><code langs="">rm -rf /tmp/wordpress  # Clean up
</code></pre>
<p>Edit the <code>wp-config.php.erb</code> file using <span class="highlight">nano</span>.</p>
<pre class="code-pre "><code langs="">nano ~/MyModules/do-wordpress/templates/wp-config.php.erb
</code></pre>
<p>Use the variables defined in <span class="highlight">conf.pp</span> to set the values for <span class="highlight">DB<em>NAME</em></span>, <span class="highlight">DBUSER</span>, <span class="highlight">DB<em>PASSWORD</em></span> and <span class="highlight">DBHOST</span>. You can use the exact settings shown below, which will pull in your actual variables from the <span class="highlight">conf.pp</span> file we created earlier. The items marked in <span class="highlight">red</span> are the exact changes that you need to make on the four database-related lines.</p>

<p>Ignoring the comments, your file should look like this:</p>
<pre class="code-pre "><code langs=""><?php
define('DB_NAME', '<span class="highlight"><%= scope.lookupvar('wordpress::conf::db_name') %></span>');
define('DB_USER', '<span class="highlight"><%= scope.lookupvar('wordpress::conf::db_user') %></span>');
define('DB_PASSWORD', '<span class="highlight"><%= scope.lookupvar('wordpress::conf::db_user_password') %></span>');
define('DB_HOST', '<span class="highlight"><%= scope.lookupvar('wordpress::conf::db_host') %></span>');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

$table_prefix  = 'wp_';

define('WP_DEBUG', false);

if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

require_once(ABSPATH . 'wp-settings.php');
</code></pre>
<h2 id="step-9-create-a-manifest-for-wordpress">Step 9 - Create a Manifest for Wordpress</h2>

<p>Use <span class="highlight">nano</span> to create and edit a file named <span class="highlight">wp.pp</span> in the <code>manifests</code> directory:</p>
<pre class="code-pre "><code langs="">nano ~/MyModules/do-wordpress/manifests/wp.pp
</code></pre>
<p>This manifest performs the following actions:</p>

<ul>
<li>Copies the contents of the Wordpress installation bundle to <span class="highlight">/var/www/</span>. This has to be done because the default configuration of Apache serves files from <span class="highlight">/var/www/</span></li>
<li>Generates a <span class="highlight">wp-config.php</span> file using the template</li>
</ul>

<p>Add the following code to the file exactly as shown:</p>
<pre class="code-pre "><code langs="">class wordpress::wp {

    # Copy the Wordpress bundle to /tmp
    file { '/tmp/latest.tar.gz':
        ensure => present,
        source => "puppet:///modules/wordpress/latest.tar.gz"
    }

    # Extract the Wordpress bundle
    exec { 'extract':
        cwd => "/tmp",
        command => "tar -xvzf latest.tar.gz",
        require => File['/tmp/latest.tar.gz'],
        path => ['/bin'],
    }

    # Copy to /var/www/
    exec { 'copy':
        command => "cp -r /tmp/wordpress/* /var/www/",
        require => Exec['extract'],
        path => ['/bin'],
    }

    # Generate the wp-config.php file using the template
    file { '/var/www/wp-config.php':
        ensure => present,
        require => Exec['copy'],
        content => template("wordpress/wp-config.php.erb")
    }
}
</code></pre>
<h2 id="step-10-create-init-pp-a-manifest-that-integrates-the-other-manifests">Step 10 - Create init.pp, a Manifest that Integrates the Other Manifests</h2>

<p>Every Puppet module needs to have a file named <span class="highlight">init.pp</span>. When an external manifest includes your module, the contents of this file will be executed. The <code>puppet module generate</code> command created a generic version of this file for you already.</p>

<p>Edit <span class="highlight">init.pp</span> using <strong>nano</strong>:</p>
<pre class="code-pre "><code langs="">nano ~/MyModules/do-wordpress/manifests/init.pp
</code></pre>
<p>Let the file have the following contents.</p>

<p>You can leave the commented explanations and examples at the top. There should be an empty block for the <code>wordpress</code> class. Add the contents shown here so the <code>wordpress</code> block looks like the one shown below. Make sure you get the brackets nested correctly.</p>

<p>Inline comments are included to explain the settings:</p>
<pre class="code-pre "><code langs="">class wordpress {
    # Load all variables
    class { 'wordpress::conf': }

    # Install Apache and PHP
    class { 'wordpress::web': }

    # Install MySQL
    class { 'wordpress::db': }

    # Run Wordpress installation only after Apache is installed
    class { 'wordpress::wp': 
        require => Notify['Apache Installation Complete']
    }

    # Display this message after MySQL installation is complete
    notify { 'MySQL Installation Complete':
        require => Class['wordpress::db']
    }

    # Display this message after Apache installation is complete
    notify { 'Apache Installation Complete':
        require => Class['wordpress::web']
    }

    # Display this message after Wordpress installation is complete
    notify { 'Wordpress Installation Complete':
        require => Class['wordpress::wp']
    }
}
</code></pre>
<h2 id="step-11-build-the-wordpress-module">Step 11 - Build the WordPress Module</h2>

<p>The module is now ready to be built. Move into the <em>MyModules</em> directory:</p>
<pre class="code-pre "><code langs="">cd ~/MyModules
</code></pre>
<p>Use the <code>puppet module build</code> command to build the module:</p>
<pre class="code-pre "><code langs="">sudo puppet module build do-wordpress
</code></pre>
<p>You should see the following output from a successful build:</p>
<pre class="code-pre "><code langs="">Notice: Building /home/user/MyModules/do-wordpress for release
Module built: /home/user/MyModules/do-wordpress/pkg/do-wordpress-0.1.0.tar.gz
</code></pre>
<p>The module is now ready to be used and shared. You will find the installable bundle in the module's <span class="highlight">pkg</span> directory.</p>

<h2 id="step-12-install-the-wordpress-module">Step 12 - Install the WordPress Module</h2>

<p>To use the module, it has to be installed first. Use the <code>puppet module install</code> command.</p>
<pre class="code-pre "><code langs="">sudo puppet module install ~/MyModules/do-wordpress/pkg/do-wordpress-0.1.0.tar.gz
</code></pre>
<p>After installation, when you run the <code>sudo puppet module list</code> command, you should see an output similar to this:</p>
<pre class="code-pre "><code langs="">/etc/puppet/modules
├── do-wordpress (v0.1.0)
├── puppetlabs-apache (v1.1.1)
├── puppetlabs-concat (v1.1.1)
├── puppetlabs-mysql (v2.3.1)
└── puppetlabs-stdlib (v4.3.2)
</code></pre>
<p>Now that it's installed, you should reference this module as <code>do-wordpress</code> for any Puppet commands.</p>

<h3 id="updating-or-uninstalling-the-module">Updating or Uninstalling the Module</h3>

<p>If you receive installation errors, or if you notice configuration problems with WordPress, you will likely need to make changes in one or more of the manifest and related files we created earlier in the tutorial.</p>

<p>Or, you may simply want to uninstall the module at some point.</p>

<p>To update or uninstall the module, use this command:</p>
<pre class="code-pre "><code langs="">sudo puppet module uninstall do-wordpress
</code></pre>
<p>If you just wanted to uninstall, you're done.</p>

<p>Otherwise, make the changes you needed, then rebuild and reinstall the module according to Steps 11-12.</p>

<h2 id="step-13-use-the-module-in-a-standalone-manifest-file-to-install-wordpress">Step 13 - Use the Module in a Standalone Manifest File to Install WordPress</h2>

<p>To use the module to install Wordpress, you have to create a new manifest, and apply it. </p>

<p>Use <span class="highlight">nano</span> to create and edit a file named <code>install-wp.pp</code> in the <span class="highlight">/tmp</span> directory (or any other directory of your choice).</p>
<pre class="code-pre "><code langs="">nano /tmp/install-wp.pp
</code></pre>
<p>Add the following contents to the file exactly as shown:</p>
<pre class="code-pre "><code langs="">class { 'wordpress':
}
</code></pre>
<p>Apply the manifest using <code>puppet apply</code>. This is the step that gets WordPress up and running on your server:</p>
<pre class="code-pre "><code langs="">sudo puppet apply /tmp/install-wp.pp
</code></pre>
<p>It's fine to see a warning or two.</p>

<p>This will take a while to run, but when it completes, you will have Wordpress and all its dependencies installed and running.</p>

<p>The final few successful installation lines should look like this:</p>
<pre class="code-pre "><code langs="">Notice: /Stage[main]/Apache/File[/etc/apache2/mods-enabled/authn_core.load]/ensure: removed
Notice: /Stage[main]/Apache/File[/etc/apache2/mods-enabled/status.load]/ensure: removed
Notice: /Stage[main]/Apache/File[/etc/apache2/mods-enabled/mpm_prefork.load]/ensure: removed
Notice: /Stage[main]/Apache/File[/etc/apache2/mods-enabled/status.conf]/ensure: removed
Notice: /Stage[main]/Apache/File[/etc/apache2/mods-enabled/mpm_prefork.conf]/ensure: removed
Notice: /Stage[main]/Apache::Service/Service[httpd]: Triggered 'refresh' from 55 events
Notice: Finished catalog run in 55.91 seconds
</code></pre>
<p>You can open a browser and visit http://<span class="highlight">server-IP</span>/. You should see the WordPress welcome screen.</p>

<p><img src="https://assets.digitalocean.com/articles/wordpress_puppet/1.png" alt="WordPress Welcome" /></p>

<p>From here, you can configure your WordPress control panel normally.</p>

<h2 id="deploying-to-multiple-servers">Deploying to Multiple Servers</h2>

<p>If you are running Puppet in an Agent-Master configuration and want to install WordPress on one or more remote machines, all you have to do is add the line <code>class {'wordpress':}</code> to the <strong>node</strong> definitions of those machines. To learn more about Agent-Master configuration and node definitions, you can refer to this tutorial:</p>

<p><a href="https://indiareads/community/tutorials/how-to-install-puppet-to-manage-your-server-infrastructure#using-a-module">How To Install Puppet To Manage Your Server Infrastructure</a></p>

<h2 id="conclusion">Conclusion</h2>

<p>With this tutorial, you have learned to create your own Puppet module that sets up WordPress for you. You could further build on this to add support for automatically installing certain themes and plugins. Finally, when you feel your module could be useful for others as well, you can publish it on Puppet Forge.</p>

    