<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>LDAP systems can seem difficult to manage if you do not have a good grasp on the tools available and the information and methods that LDAP requires.  In this guide, we will be demonstrating how to use the LDAP tools developed by the OpenLDAP team to interact with an LDAP directory server.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To get started, you should have access to a system with OpenLDAP installed and configured.  You can learn how to set up an OpenLDAP server <a href="https://indiareads/community/tutorials/how-to-install-and-configure-openldap-and-phpldapadmin-on-an-ubuntu-14-04-server">here</a>.  You should be familiar with the basic terminology used when working with an LDAP directory service.  <a href="https://indiareads/community/tutorials/understanding-the-ldap-protocol-data-hierarchy-and-entry-components">This guide</a> can be used to get more familiar with these topics.</p>

<h2 id="installing-the-tools">Installing the Tools</h2>

<p>The prerequisites above assume that you already have access to an LDAP system, but you may not already have the OpenLDAP tools discussed in this guide installed.</p>

<p>On an Ubuntu or Debian system, you can install these tools through the <code>apt</code> repositories.  Update your local package index and install by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo apt-get update
</li><li class="line" prefix="$">sudo apt-get install ldap-utils
</li></ul></code></pre>
<p>On CentOS or Fedora, you can get the appropriate files by using <code>yum</code>.  Install them by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo yum install openldap-clients
</li></ul></code></pre>
<p>Once you have the correct packages installed, continue below.</p>

<h2 id="connecting-to-the-ldap-instance">Connecting to the LDAP Instance</h2>

<p>Most of the OpenLDAP tools are extremely flexible, sacrificing a concise command structure for the ability to interact with systems in several different roles.  Because of this, a user must select a variety of arguments just to express the bare minimum necessary to connect to an LDAP server.</p>

<p>In this section, we'll focus on constructing the arguments needed to contact the server depending on the type of operation you wish to perform.  The arguments discussed here will be used in a variety of tools, but we will use <code>ldapsearch</code> for demonstration purposes.</p>

<h3 id="specifying-the-server">Specifying the Server</h3>

<p>The OpenLDAP tools require that you specify an authentication method and a server location for each operation.  To specify the server, use the <code>-H</code> flag followed by the protocol and network location of the server in question.</p>

<p>For basic, unencrypted communication, the protocol scheme will be <code>ldap://</code> like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap://<span class="highlight">server_domain_or_IP</span> . . .
</li></ul></code></pre>
<p>If you are communicating with a local server, you can leave off the server domain name or IP address (you still need to specify the scheme).</p>

<p>If you are using LDAP over SSL to connect to your LDAP server, you will instead want to use the <code>ldaps://</code> scheme (note that this is a deprecated method.  The OpenLDAP project recommends using a STARTTLS upgrade on the normal LDAP port instead.  Learn how to set this up <a href="">here</a>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldaps://<span class="highlight">server_domain_or_IP</span> . . .
</li></ul></code></pre>
<p>These protocols assume the default port (<code>389</code> for conventional LDAP and <code>636</code> for LDAP over SSL).  If you are using a non-standard port, you'll need to add that onto the end with a colon and the port number.</p>

<p>To connect to an LDAP directory on the server you are querying from over Linux IPC (interprocess communication), you can use the <code>ldapi://</code> protocol.  This is more secure and necessary for some administration tasks:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldapi:// . . .
</li></ul></code></pre>
<p>Since the <code>ldapi</code> scheme requires a local connection, we never will have to specify a server name here.  However, if you changed the socket-file location within the LDAP server configuration, you will need to specify the new socket location as part of the address.</p>

<h3 id="anonymous-bind">Anonymous Bind</h3>

<p>LDAP requires that clients identify themselves so that the server can determine the level of access to grant requests.  This works by using an LDAP mechanism called "binding", which is basically just a term for associating your request with a known security entity.  There are three separate types of authentication that LDAP understands.</p>

<p>The most generic type of authentication that a client can use is an "anonymous" bind.  This is pretty much the absence of authentication.  LDAP servers can categorize certain operations as accessible to anyone (typically, by default, the public-facing DIT is configured as read-only for anonymous users).  If you are using an anonymous bind, these operations will be available to you.</p>

<p>The OpenLDAP tools assume SASL authentication (we'll discuss this momentarily) by default, so to allow an anonymous bind, we must give the <code>-x</code> argument.  Combined with the server specification, this will look something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap://<span class="highlight">server_domain_or_IP</span> -x
</li></ul></code></pre>
<p>If you type that in without providing additional arguments, you should get something like this:</p>
<div class="code-label " title="Output for ldapsearch with an anonymous bind">Output for ldapsearch with an anonymous bind</div><pre class="code-pre "><code langs=""># extended LDIF
#
# LDAPv3
# base <> (default) with scope subtree
# filter: (objectclass=*)
# requesting: ALL
#

# search result
search: 2
result: 32 No such object

# numResponses: 1
</code></pre>
<p>This says that the tool didn't find what we searched for.  Since we didn't provide query parameters, this is expected, but it does show us that our anonymous bind was accepted by the server.</p>

<h3 id="simple-authentication">Simple Authentication</h3>

<p>The second method of authenticating to an LDAP server is with a simple bind.  A simple bind uses an entry within the LDAP server to authenticate the request.  The DN (distinguished name) of the entry functions as a username for the authentication.  Inside of the entry, an attribute defines a password which must be provided during the request.</p>

<h4 id="finding-the-dit-root-entry-and-the-rootdn-bind">Finding the DIT Root Entry and the RootDN Bind</h4>

<p>To authenticate using simple authentication, you need to know the parent element at the top of the DIT hierarchy, called the root, base, or suffix entry, under which all other entries are placed.  You also need to know of a DN to bind to.</p>

<p>Typically, during installation of the LDAP server, an initial DIT is set up and configured with an administrative entry, called the rootDN, and a password.  When starting out, this will be the only DN that is configured for binds.</p>

<p>If you do not know the root entry of the LDAP server you are connecting to, you can query a special "meta" entry outside of the normal LDAP DIT for information about what DIT root entries it knows about (this is called the root DSE).  You can query this entry for the DIT names by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap://<span class="highlight">server_domain_or_IP</span> -x -LLL -s base -b "" namingContexts
</li></ul></code></pre>
<p>The LDAP server should return the root entries that it knows about, which will look something like this:</p>
<div class="code-label " title="LDAP root entry results">LDAP root entry results</div><pre class="code-pre "><code langs="">dn:
namingContexts: <span class="highlight">dc=example,dc=com</span>
</code></pre>
<p>The highlighted area is the root of the DIT.  We can use this to search for the entry to bind to.  The admin entry typically uses the <code>simpleSecurityObject</code> objectClass in order to gain the ability to set a password in the entry.  We can use this to search for entry's with this class:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap://<span class="highlight">server_domain_or_IP</span> -x -LLL -b "<span class="highlight">dc=example,dc=com</span>" "(objectClass=simpleSecurityObject)" dn
</li></ul></code></pre>
<p>This will give you a list of the entries that use this class.  Usually there is only one:</p>
<div class="code-label " title="simpleSecurityObject search results">simpleSecurityObject search results</div><pre class="code-pre "><code langs="">dn: <span class="highlight">cn=admin,dc=example,dc=com</span>
</code></pre>
<p>This is the rootDN account that we can bind to.  You should have configured a password for this account during the server's installation.  If you do not know the password, you can follow <a href="https://indiareads/community/tutorials/how-to-change-account-passwords-in-an-ldap-server">this guide</a> to reset the password.</p>

<h4 id="performing-the-bind">Performing the Bind</h4>

<p>Once you have an entry and password, you can perform a simple bind during your request to authenticate yourself to the LDAP server.</p>

<p>Again, we will have to specify the LDAP server location and provide the <code>-x</code> flag to indicate that we don't wish to use SASL authentication.  To perform the actual bind, we will need to use the <code>-D</code> flag to specify the DN to bind to, and provide a password using the <code>-w</code> or <code>-W</code> command.  The <code>-w</code> option allows you to supply a password as part of the command, while the <code>-W</code> option will prompt you for the password.</p>

<p>An example request binding to the rootDN would look like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap://<span class="highlight">server_domain_or_IP</span> -x -D "cn=admin,dc=example,dc=com" -W
</li></ul></code></pre>
<p>We should get the same result as our anonymous bind, indicating that our credentials were accepted.  Binding to an entry often gives you additional privileges that are not available through an anonymous bind.  Binding to the rootDN gives you read/write access to the entire DIT, regardless of access controls.</p>

<h3 id="sasl-authentication">SASL Authentication</h3>

<p>SASL stands for simple authentication and security layer.  It is a framework for hooking up authentication methods with protocols in order to provide a flexible authentication system that is not tied to a specific implementation.  You can check out the <a href="http://en.wikipedia.org/wiki/Simple_Authentication_and_Security_Layer#SASL_mechanisms">wikipedia page</a> to learn about the various methods available. </p>

<p>Your LDAP server will probably only support a subset of the possible SASL mechanisms.  To find out which mechanisms it allows, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -LLL -s base -b "" supportedSASLMechanisms
</li></ul></code></pre>
<p>The results that you see will differ depending on the scheme that you used to connect.  For the unencrypted <code>ldap://</code> scheme, most systems will default to allowing:</p>
<div class="code-label " title="ldap:// supportedSASLMechanisms">ldap:// supportedSASLMechanisms</div><pre class="code-pre "><code langs="">dn:
supportedSASLMechanisms: DIGEST-MD5
supportedSASLMechanisms: NTLM
supportedSASLMechanisms: CRAM-MD5
</code></pre>
<p>If you are using the <code>ldapi://</code> scheme, which uses secure interprocess communication, you will likely have an expanded list of choices:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldapi:// -x -LLL -s base -b "" supportedSASLMechanisms
</li></ul></code></pre><div class="code-label " title="ldapi:// supportedSASLMechanisms">ldapi:// supportedSASLMechanisms</div><pre class="code-pre "><code langs="">dn:
supportedSASLMechanisms: DIGEST-MD5
supportedSASLMechanisms: EXTERNAL
supportedSASLMechanisms: NTLM
supportedSASLMechanisms: CRAM-MD5
supportedSASLMechanisms: LOGIN
supportedSASLMechanisms: PLAIN
</code></pre>
<p>Configuring most SASL methods of authentication can take some time, so we will not cover much of the details here.  While SASL authentication is generally outside of the scope of this article, we should talk about the <code>EXTERNAL</code> method that we see available for use with the <code>ldapi://</code> scheme.</p>

<p>The <code>EXTERNAL</code> mechanism indicates that authentication and security is handled by some other means associated with the connection.  For instance, it can be used with SSL to provide encryption and authentication.</p>

<p>Most commonly, you will see it used with with the <code>ldapi://</code> interface with the root or <code>sudo</code> users.  Since <code>ldapi://</code> uses Unix sockets, the user initiating the request can be obtained, and used to authenticate for certain operations.  The DIT that LDAP uses for configuration uses this mechanism to authenticate the root user to read and make changes to LDAP.  These requests look something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL . . .
</li></ul></code></pre>
<p>This is used to modify the LDAP configuration that is typically kept in a DIT starting with a <code>cn=config</code> root entry.</p>

<h3 id="setting-up-an-ldaprc-file">Setting Up an .ldaprc File</h3>

<p>We have been specifying the connection information mainly on the command line so far.  However, you can save yourself some typing by putting some of the common connection values in a configuration file.</p>

<p>The global client configuration file is located at <code>/etc/ldap/ldap.conf</code>, but you'll mainly want to add changes to your user's configuration file located in your home directory at <code>~/.ldaprc</code>.  Create and open a file with this name in your text editor:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">nano ~/.ldaprc
</li></ul></code></pre>
<p>Inside, the basic settings you probably want to configure are <code>BASE</code>, <code>URI</code>, and <code>BINDDN</code>:</p>

<ul>
<li><strong><code>BASE</code></strong>: The default base DN used to specify the entry where searches should start.  This will be overridden if another search base is provided on the command line (we'll see more of this in the next section).</li>
<li><strong><code>URI</code></strong>: The address where the LDAP server can be reached.  This should include a scheme (<code>ldap</code> for regular LDAP, <code>ldaps</code> for LDAP over SSL, and <code>ldapi</code> for LDAP over an IPC socket) followed by the name and port of the server.  The name can be left off if the server is located on the same machine and the port can be left off if the server is running on the default port for the scheme selected.</li>
<li><strong><code>BINDDN</code></strong>: This specifies the default LDAP entry to bind to.  This is used to provide the "account" information for the access you wish to use.  You will still need to specify any password on the command line.</li>
</ul>

<p>This will take care of the simple authentication information.  If you are using SASL authentication, check out <code>man ldap.conf</code> to see the options for configuring SASL credentials.</p>

<p>If our LDAP's base entry is <code>dc=example,dc=com</code>, the server is located on the local computer, and we are using the <code>cn=admin,dc=example,dc=com</code> to bind to, we might have an <code>~/.ldaprc</code> file that looks like this:</p>
<div class="code-label " title="~/.ldaprc">~/.ldaprc</div><pre class="code-pre "><code langs="">BASE    dc=example,dc=com
URI     ldap://
BINDDN  cn=admin,dc=example,dc=com
</code></pre>
<p>Using this, we could perform a basic search by just specifying non-SASL authentication and providing the password associated with the admin entry.  This would provide a full subtree search of the default base DN we specified:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -x -w <span class="highlight">password</span>
</li></ul></code></pre>
<p>This can help shorten your the "boilerplate" connection options as you use the LDAP utilities.  Throughout this guide, we'll include the connection info in the commands in order to be explicit, but when running the commands, you can remove any portion that you've specified in your configuration file.</p>

<h2 id="using-ldapsearch-to-query-the-dit-and-lookup-entries">Using ldapsearch to Query the DIT and Lookup Entries</h2>

<p>Now that we have a good handle on how to authenticate to and specify an LDAP server, we can begin talking a bit more about the actual tools that are at your disposal.  For most of our examples, we'll assume we are performing these operations on the same server that hosts the LDAP server.  This means that our host specification will be blank after the scheme.  We'll also assume that the base entry of the DIT that the server manages is for <code>dc=example,dc=com</code>.  The rootDN will be <code>cn=admin,dc=example,dc=com</code>.  Let's get started.</p>

<p>We'll start with <code>ldapsearch</code>, since we have been using it in our examples thus far.  LDAP systems are optimized for search, read, and lookup operations.  If you are utilizing an LDAP directory, the majority of your operations will probably be searches or lookups.  The <code>ldapsearch</code> tool is used to query and display information in an LDAP DIT.</p>

<p>We've covered part of the syntax that is responsible for naming and connecting to the server, which looks something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -W
</li></ul></code></pre>
<p>This gets us the bare minimum needed to connect and authenticate to the LDAP instance running on the server, however, we're not really searching for anything.  To learn more, we'll have to discuss the concepts of search base and search scope.</p>

<h3 id="search-base-and-scope">Search Base and Scope</h3>

<p>In LDAP, the place where a search begins is called the <strong>search base</strong>.  This is an entry within a DIT from which the operation will commence and acts as an anchor.  We specify the search base by passing the entry name with the <code>-b</code> flag.</p>

<p>For instance, to start at the root of our <code>dc=example,dc=com</code> DIT, we can use that as the search base, like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "dc=example,dc=com"
</li></ul></code></pre>
<p>This command should produce every entry beneath the <code>dc=example,dc=com</code> entry that the user you have bound to has access to.  If we use a different entry, would get another section of the tree.  For instance, if we start at the admin entry, you may only get the admin entry itself:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "cn=admin,dc=example,dc=com"
</li></ul></code></pre><div class="code-label " title="search base at cn=admin,dc=example,dc=com">search base at cn=admin,dc=example,dc=com</div><pre class="code-pre "><code langs=""># extended LDIF
#
# LDAPv3
# base <cn=admin,dc=example,dc=com> with scope subtree
# filter: (objectclass=*)
# requesting: ALL
#

# admin, example.com
dn: cn=admin,dc=example,dc=com
objectClass: simpleSecurityObject
objectClass: organizationalRole
cn: admin
description: LDAP administrator
userPassword:: e1NTSEF9ejN2UmHoRjdha09tQY96TC9IN0kxYUVCSjhLeXBsc3A=

# search result
search: 2
result: 0 Success

# numResponses: 2
# numEntries: 1
</code></pre>
<p>We have specified the base in these examples, but we can further shape the way that the tool looks for results by specifying the search scope.  This option is set by the <code>-s</code> option and can be any of the following:</p>

<ul>
<li><strong><code>sub</code></strong>: The default search scope if no other is specified.  This searches the base entry itself and any descendants all of the way down the tree.  This is the largest scope.</li>
<li><strong><code>base</code></strong>: This only searches the search base itself.  It is used to return the entry specified in the search base and better defined as a lookup than a search.</li>
<li><strong><code>one</code></strong>: This searches only the immediate descendants/children of the search base (the single hierarchy level below the search base).  This does not include the search base itself and does not include the subtree below any of these entries.</li>
<li><strong><code>children</code></strong>: This functions the same as the <code>sub</code> scope, but it does not include the search base itself in the results (searches every entry beneath, but not including the search base).</li>
</ul>

<p>Using the <code>-s</code> flag and the <code>-b</code> flag, we can begin to shape the areas of the DIT that we want the tool to look in.  For instance, we can see all of the first-level children of our base entry by using the <code>one</code> scope, like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "dc=example,dc=com" -s one -LLL dn
</li></ul></code></pre>
<p>We added <code>-LLL dn</code> to the end to filter the output a bit.  We'll discuss this further later in the article.  If we had added a few more entries to the tree, this might have returned results like this:</p>
<div class="code-label " title="output">output</div><pre class="code-pre "><code langs="">dn: cn=admin,dc=example,dc=com

dn: ou=groups,dc=example,dc=com

dn: ou=people,dc=example,dc=com
</code></pre>
<p>If we wanted to see everything under the <code>ou=people</code> entry, we could set that as the search base and use the <code>children</code> scope:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "ou=people,dc=example,dc=com" -s children -LLL dn
</li></ul></code></pre>
<p>By tweaking the search base and search scope, you can operate on just the portions of the DIT that you are interested in.  This will make your query perform better by only searching a section of the tree and it will only return the entries you are interested in.</p>

<h3 id="removing-extraneous-output">Removing Extraneous Output</h3>

<p>Before moving on, let's talk about how to remove some of the extra output that <code>ldapsearch</code> produces.</p>

<p>The majority of the extra output is controlled with <code>-L</code> flags.  You can use zero to three <code>-L</code> flags depending on the level of output that you'd like to see.  The more <code>-L</code> flags you add, the more information is suppressed.  It might be a good idea to refrain from suppressing any output when learning or troubleshooting, but during normal operation, using all three levels will probably lead to a better experience.</p>

<p>If you are using SASL authentication, when modifying the <code>cn=config</code> DIT for instance, you can additionally use the <code>-Q</code> flag.  This will enable SASL quiet mode, which will remove any SASL-related output.  This is fine when using the <code>-Y EXTERNAL</code> method, but be careful if you are using a mechanism that prompts for credentials because this will be suppressed as well (leading to an authentication failure).</p>

<h3 id="search-filters-and-output-attribute-filters">Search Filters and Output Attribute Filters</h3>

<p>To actually perform a search instead of simply outputting the entirety of the search scope, you need to specify the search filter.</p>

<p>These can be placed towards the end of the line and take the form of an attribute type, a comparison operator, and a value.  Often, they are specified within quotation marks to prevent interpretation by the shell.  Parentheses are used to indicate the bounds of one filter from another.  These are optional in simple, single-attribute searches, but required in more complex, compound filters.  We'll use them here to better indicate where the search filter is.</p>

<p>As an example, we could see if there is an entry within the <code>dc=example,dc=com</code> DIT with a username (<code>uid</code>) attribute set to "jsmith".  This searches each entry within the search scope for an attribute set to that value:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "dc=example,dc=com" -LLL "(uid=jsmith)"
</li></ul></code></pre>
<p>We used the equality operator in the above example, which tests for an exact match of an attribute's value.  There are various other operator as well, which function as you would expect.  For example, to search for entries that <em>contain</em> an attribute, without caring about the value set, you can use the "presence" operator, which is simply an equals sign with a wildcard on the right side of the comparison.  We could search for entries that contain a password by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "dc=example,dc=com" -LLL "(userPassword=*)"
</li></ul></code></pre>
<p>Some search filters that are useful are:</p>

<ul>
<li><strong>Equality</strong>: Uses the <code>=</code> operator to match an exact attribute value.</li>
<li><strong>Presence</strong>: Uses <code>=*</code> to check for the attribute's existence without regard to its value.</li>
<li><strong>Greater than or equal</strong>: Uses the <code>>=</code> operator to check for values greater than or equal to the given value.</li>
<li><strong>Less than or equal</strong>: Uses the <code><=</code> operator to check for values less than or equal to the given value.</li>
<li><strong>Substring</strong>: Uses <code>=</code> with a string and the <code>*</code> wildcard character as part of a string.  Used to specify part of the value you are looking for.</li>
<li><strong>Proximity</strong>: Uses the <code>~=</code> operator to approximately match what is on the right.  This is not always supported by the LDAP server (in which case an equality or substring search will be performed instead).</li>
</ul>

<p>You can also negate most of the searches by wrapping the search filter in an additional set of parentheses prefixed with the "!" negation symbol.  For example, to search for all organizational unit entries, we could use this filter:</p>
<pre class="code-pre "><code langs="">"(ou=*)"
</code></pre>
<p>To search for all entries that are <em>not</em> organizational unit entries, we could use this filter:</p>
<pre class="code-pre "><code langs="">"(!(ou=*)"
</code></pre>
<p>The negation modifier reverses the meaning of the search filter that follows.</p>

<p>Following the filter specification, we can also add attribute output filters.  This is just a list of attributes that you wish to display from each matched entry.  By default, every attribute that your credentials have read access to are displayed for each matched entry.  Setting an attribute output filter allows you to specify exactly what type of output you'd like to see.</p>

<p>For instance, we can search for all entries that have user IDs, but only display the associated <em>common name</em> of each entry by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "dc=example,dc=com" -LLL "(uid=*)" cn
</li></ul></code></pre>
<p>This might produce a list that looks like this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">dn: uid=bwright,ou=People,dc=example,dc=com
cn: Brian Wright

dn: uid=jsmith1,ou=People,dc=example,dc=com
cn: Johnny Smith

dn: uid=sbrown2,ou=People,dc=example,dc=com
cn: Sally Brown
</code></pre>
<p>If we want to see their entry description as well, we can just add that to the list of attributes to display:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -b "dc=example,dc=com" -LLL "(uid=*)" cn description
</li></ul></code></pre>
<p>It would instead show something like this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">dn: uid=bwright,ou=People,dc=example,dc=com
cn: Brian Wright
description: Brian Wright from Marketing.  Brian takes care of marketing, pres
 s, and community.  Ask him for help if you need any help with outreach.

dn: uid=jsmith1,ou=People,dc=example,dc=com
cn: Johnny Smith
description: Johnny Smith from Accounting.  Johnny is in charge of the company
  books and hiring within the Accounting department.

dn: uid=sbrown2,ou=People,dc=example,dc=com
cn: Sally Brown
description: Sally Brown from engineering.  Sally is responsible for designing
  the blue prints and testing the structural integrity of the design.
</code></pre>
<p>If no attribute filter is given, all attributes are returned.  This can be made explicit with the "*" character.  To return operational attributes (special metadata attributes managed in the background for each entry), you can use the special "+" symbol.  For instance, to see the operational attributes for our rootDN, we could type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -D "cn=admin,dc=example,dc=com" -b "dc=example,dc=com" -LLL "(cn=admin)" "+"
</li></ul></code></pre>
<p>The results would look something like this:</p>
<div class="code-label " title="Output">Output</div><pre class="code-pre "><code langs="">dn: cn=admin,dc=example,dc=com
structuralObjectClass: organizationalRole
entryUUID: cdc718a0-8c3c-1034-8646-e30b83a2e38d
creatorsName: cn=admin,dc=example,dc=com
createTimestamp: 20150511151904Z
entryCSN: 20150514191233.782384Z#000000#000#000000
modifiersName: cn=admin,dc=example,dc=com
modifyTimestamp: 20150514191233Z
entryDN: cn=admin,dc=example,dc=com
subschemaSubentry: cn=Subschema
hasSubordinates: FALSE
</code></pre>
<h3 id="compound-searching">Compound Searching</h3>

<p>Compound searching involves combining two or more individual search filters to get more precise results.  Search filters are combined by wrapping them in another set of parentheses with a relational operator as the first item.  This is easier demonstrated than explained.</p>

<p>The relational operators are the "&" character which works as a logical AND, and the "|" character, which signifies a logical OR.  These precede the filters whose relationships they define within an outer set of parentheses.</p>

<p>So to search for an entry that has both a description and an email address in our domain, we could construct a filter like this:</p>
<pre class="code-pre "><code langs="">"(&(description=*)(mail=*@example.com))"
</code></pre>
<p>For an entry to be returned, it must have both of those attributes defined.</p>

<p>The OR symbol will return the results if either of the sub-filters are true.  If we want to output entries for which we have contact info, we might try a filter like this:</p>
<pre class="code-pre "><code langs="">"(|(telephoneNumber=*)(mail=*)(street=*))"
</code></pre>
<p>Here, we see that the operator can apply to more than two sub-filters.  We can also nest these logical constructions as needed to create quite complex patterns.</p>

<h2 id="using-ldapmodify-and-variations-to-change-or-create-ldap-entries">Using ldapmodify and Variations to Change or Create LDAP Entries</h2>

<p>So far, we have focused exclusively on the <code>ldapsearch</code> command, which is useful for looking up, searching, and displaying entries and entry segments within an LDAP DIT.  This will satisfy the majority of users' read-only requirements, but we need a different tool if we want to change the objects in the DIT.</p>

<p>The <code>ldapmodify</code> command manipulates a DIT through the use of LDIF files.  You can learn more about LDIF files and the specifics of how to use these to modify or add entries by looking at <a href="https://indiareads/community/tutorials/how-to-use-ldif-files-to-make-changes-to-an-openldap-system">this guide</a>.</p>

<p>The basic format of <code>ldapmodify</code> closely matches the <code>ldapsearch</code> syntax that we've been using throughout this guide.  For instance, you will still need to specify the server with the <code>-H</code> flag, authenticate using the <code>-Y</code> flag for SASL authentication or the <code>-x</code>, <code>-D</code>, and <code>-[W|w]</code> flags for simple authentication.</p>

<h3 id="applying-changes-from-an-ldif-file">Applying Changes from an LDIF File</h3>

<p>After providing these boilerplate options, the most common action is to read in an LDIF file and apply it to the DIT.  This can be accomplished with the <code>-f</code> option (if you do not use the <code>-f</code> option, you will have to type in a change using the LDIF format on the command line).  You will need to create the LDIF file yourself, using the syntax described in the guide linked to above:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -f <span class="highlight">/path/to/file.ldif</span>
</li></ul></code></pre>
<p>This will read the LDIF file and apply the changes specified within.  For the <code>ldapmodify</code> command, each LDIF change should have a <code>changetype</code> specified.  The <code>ldapmodify</code> command is the most general form of the DIT manipulation commands.</p>

<p>If your LDIF file is adding new entries and <em>does not</em> include <code>changetype: add</code> for each entry, you can use the <code>-a</code> flag with <code>ldapmodify</code>, or simply use the <code>ldapadd</code> command, which basically aliases this behavior.  For example, an LDIF file which <em>includes</em> the <code>changetype</code> would look like this:</p>
<div class="code-label " title="LDIF with changetype">LDIF with changetype</div><pre class="code-pre "><code langs="">dn: ou=newgroup,dc=example,dc=com
<span class="highlight">changetype: add</span>
objectClass: organizationalUnit
ou: newgroup
</code></pre>
<p>To process this file, you could simply use <code>ldapmodify</code>:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -f <span class="highlight">/path/to/file.ldif</span>
</li></ul></code></pre>
<p>However, the file could also be constructed <em>without</em> the <code>changetype</code>, like this:</p>
<div class="code-label " title="LDIF without changetype">LDIF without changetype</div><pre class="code-pre "><code langs="">dn: ou=newgroup,dc=example,dc=com
objectClass: organizationalUnit
ou: newgroup
</code></pre>
<p>In this case, to add this entry to the DIT, you would either need to use the <code>-a</code> flag with <code>ldapmodify</code>, or use the <code>ldapadd</code> command.  Either:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> <span class="highlight">-a</span> -f <span class="highlight">/path/to/file.ldif</span>
</li></ul></code></pre>
<p>Or this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$"><span class="highlight">ldapadd</span> -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -f <span class="highlight">/path/to/file.ldif</span>
</li></ul></code></pre>
<p>Similar commands are available for entry deletion (<code>ldapdelete</code>) and moving LDAP entries (<code>ldapmodrdn</code>).  Using these commands eliminates the need for you to specify <code>changetype: delete</code> and <code>changetype: modrdn</code> explicitly in the files, respectively.  For each of these, it is up to you which format to use (whether to specify the change in the LDIF file or on the command line).</p>

<h3 id="testing-changes-and-handling-errors">Testing Changes and Handling Errors</h3>

<p>If you want to do a dry run of any LDIF file, you can use the <code>-n</code> and <code>-v</code> flags.  This will tell you what change would be performed without modifying the actual DIT:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> <span class="highlight">-n -v</span> -f <span class="highlight">/path/to/file.ldif</span>
</li></ul></code></pre>
<p>Typically, if an error occurs while processing an LDIF file, the operation halts immediately.  This is generally the safest thing to do because often, change requests later in the file will modify the DIT under the assumption that the earlier changes were applied correctly.</p>

<p>However, if you want the command to continue through the file, skipping the error-causing changes, you can use the <code>-c</code> flag.  You'll probably also want to use the <code>-S</code> flag to point to a file where the errors can be written to so that you can fix the offending requests and re-run them:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> <span class="highlight">-c -S /path/to/error_file</span> -f <span class="highlight">/path/to/file.ldif</span>
</li></ul></code></pre>
<p>This way, you will have a log (complete with comments indicating the offending entries) to evaluate after the operation.</p>

<h2 id="various-other-ldap-commands">Various Other LDAP Commands</h2>

<p>The commands that we've already covered perform the most common LDAP operations you will use on a day-to-day basis.  There are a few more commands though that are useful to know about.</p>

<h3 id="ldappasswd">ldappasswd</h3>

<p>If some of your LDAP entries have passwords, the <code>ldappasswd</code> command can be used to modify the entry.  This works by authenticating using the account in question or an administrative account and then providing the new password (and optionally the old password).</p>

<p>The old password should be specified using either the <code>-a</code> flag (the old password is given in-line as the next item), the <code>-A</code> flag (the old password is prompted for), or the <code>-t</code> flag (the old password is read from the file given as the next item).  This is optional for some LDAP implementations but required by others, so it is best to include.</p>

<p>The new password should be specified using either the <code>-s</code> flag (the new password is given in-line as the next item), the <code>-S</code> flag (the new password is prompted for), or the <code>-T</code> flag (the new password is read from the file given as the next item).</p>

<p>So a typical change may look like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldappasswd -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">oldpassword</span> -a <span class="highlight">oldpassword</span> -s <span class="highlight">newpassword</span>
</li></ul></code></pre>
<p>If no entry is given, the entry that is being used for binding will be changed.  If you are binding to an administrative entry, you can change other entries that you have write access to by providing them after the command.</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldappasswd -H ldap:// -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">adminpassword</span> -a <span class="highlight">oldpassword</span> -s <span class="highlight">newpassword</span> "<span class="highlight">uid=user,dc=example,dc=com</span>"
</li></ul></code></pre>
<p>To learn more about changing and resetting passwords, check out <a href="https://indiareads/community/tutorials/how-to-change-account-passwords-in-an-ldap-server">this guide</a>.</p>

<h3 id="ldapwhoami">ldapwhoami</h3>

<p>The <code>ldapwhoami</code> command can tell you how the LDAP server sees you after authenticating. </p>

<p>If you are using anonymous or simple authentication, the results will probably not be too useful ("anonymous" or exactly the entry you are binding to, respectively).  However, for SASL authentication, this can provide insight into how your authentication mechanism is being seen.</p>

<p>For instance, if we use the <code>-Y EXTERNAL</code> SASL mechanism with <code>sudo</code> to perform operations on the <code>cn=config</code> DIT, we could check with <code>ldapwhoami</code> to see the authentication DN:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapwhoami -H ldapi:// -Y EXTERNAL -Q
</li></ul></code></pre><div class="code-label " title="ldapwhoami output">ldapwhoami output</div><pre class="code-pre "><code langs="">dn:gidNumber=0+uidNumber=0,cn=peercred,cn=external,cn=auth
</code></pre>
<p>This is not an actual entry in our DIT, it is just how SASL authentication gets translated into a format that LDAP can understand.  Seeing the authentication DN can be used to create mappings and access restrictions though, so it is good to know how to get this information.</p>

<h3 id="ldapurl">ldapurl</h3>

<p>The <code>ldapurl</code> tool allows you to construct LDAP URLs by specifying the various components involved in your query.  LDAP URLs are a way that you can request resources from an LDAP server through a standardized URL.  These are unauthenticated connections and are read-only.  Many LDAP solutions no longer support LDAP URLs for requesting resources, so their use may be limited depending on the software you are using.</p>

<p>The standard LDAP URL is formatted using the following syntax:</p>
<pre class="code-pre "><code langs="">ldap://<span class="highlight">host</span>:<span class="highlight">port</span>/<span class="highlight">base_dn</span>?<span class="highlight">attr_to_return</span>?<span class="highlight">search_scope</span>?<span class="highlight">filter</span>?<span class="highlight">extension</span>
</code></pre>
<p>The components are as follows:</p>

<ul>
<li><code>base_dn</code>: The base DN to begin the search from.</li>
<li><code>attr_to_return</code>: The attributes from the matching entities that you're interested in.  These should be comma-separated.</li>
<li><code>search_scope</code>: The search scope.  Either base, sub, one, or children.</li>
<li><code>filter</code>: The search filter used to select the entries that should be returned.</li>
<li><code>extension:</code> The LDAP extensions that you wish to specify.  We won't cover these here.</li>
</ul>

<p>Each of the items are separated in the URL with a question mark.  You do not have to provide the items that you aren't using, but since the item type is identified by its position in the string, you must leave the "slot" empty for that item, which will leave you with multiple question marks in a row.  You can stop the URL as soon as you have added your information (you don't need question marks at the end to represent unused "slots").</p>

<p>For example, a URL might look like this:</p>
<pre class="code-pre "><code langs="">ldap://localhost:389/dc=example,dc=com?dn,ou?sub?(ou=*)
</code></pre>
<p>If you were to feed this into the <code>ldapurl</code> tool, you'd use the <code>-H</code> flag and put the URL in quotes:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapurl -H "ldap://localhost:389/dc=example,dc=com?dn,ou?sub?(ou=*)"
</li></ul></code></pre>
<p>The command would break it apart like this:</p>
<div class="code-label " title="ldapurl output">ldapurl output</div><pre class="code-pre "><code langs="">scheme: ldap
host: localhost
port: 389
dn: dc=chilidonuts,dc=tk
selector: dn
selector: ou
scope: sub
filter: (ou=*)
</code></pre>
<p>You can also use these flags to reverse the process and cobble together an LDAP URL.  These mirror the various components of the LDAP URL:</p>

<ul>
<li><code>-S</code>: The URL scheme (<code>ldap</code>, <code>ldaps</code>, or <code>ldapi</code>).  The <code>ldap</code> scheme is default.</li>
<li><code>-h</code>: The LDAP server name or address</li>
<li><code>-p</code>: The LDAP server port.  The default value will depend on the scheme.</li>
<li><code>-b</code>: The base DN to start the query</li>
<li><code>-a</code>: A comma-separated list of attributes to return</li>
<li><code>-s</code>: The search scope to use (base, sub, children, or one)</li>
<li><code>-f</code>: The LDAP filter to select the entries to return</li>
<li><code>-e</code>: The LDAP extensions to specify</li>
</ul>

<p>Using these, you could type something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapurl -h localhost -b "dc=example,dc=com" -a dn,ou -s sub -f "(ou=*)"
</li></ul></code></pre>
<p>The command would return the constructed URL, which would look like this:</p>
<div class="code-label " title="ldapurl output">ldapurl output</div><pre class="code-pre "><code langs="">ldap://localhost:389/dc=example,dc=com?dn,ou?sub?(ou=*)
</code></pre>
<p>You can use this to construct URLs that can be used with an LDAP client capable of communicating using this format.</p>

<h3 id="ldapcompare">ldapcompare</h3>

<p>The <code>ldapcompare</code> tool can be used to compare an entry's attribute to a value.  This is used to perform simple assertion checks to validate data.</p>

<p>The process involves binding as you normally would depending on the data being queried, providing the entry DN and the assertion to check.  The assertion is given by specifying an attribute and then a value, separated by one or two colons.  For simple string values, a single colon should be used.  A double colon indicates a base64 encoded value has been given.</p>

<p>So you can assert that John is a member of the "powerusers" group with something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapcompare -H ldap:// -x "ou=powerusers,ou=groups,dc=example,dc=com" "member:uid=john,ou=people,dc=example,dc=com"
</li></ul></code></pre>
<p>If he is in the group, it will return <code>TRUE</code>.  If not, the command will return <code>FALSE</code>.  If the DN being used to bind doesn't have sufficient privileges to read the attribute in question, it will return <code>UNDEFINED</code>.</p>

<p>This could be used as the basis for an authorization system by checking group membership prior to performing requested actions.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a good idea of how to use some of the LDAP utilities to connect to, manage, and use your LDAP server.  Other clients may provide a more usable interface to your LDAP system for day-to-day management, but these tools can help you learn the ropes and provide good low-level access to the data and structures of your DIT.</p>

    