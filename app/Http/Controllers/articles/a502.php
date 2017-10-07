<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Managing an OpenLDAP system can be difficult if you do not know how to configure your system or where to find the important information you need.  In this guide, we'll demonstrate how to query your OpenLDAP server for crucial information and how to make changes to your running system.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>To get started, you should have access to a system with OpenLDAP installed and configured.  You can learn how to set up an OpenLDAP server <a href="https://indiareads/community/tutorials/how-to-install-and-configure-openldap-and-phpldapadmin-on-an-ubuntu-14-04-server">here</a>.  You should be familiar with the basic terminology used when working with an LDAP directory service.  <a href="https://indiareads/community/tutorials/understanding-the-ldap-protocol-data-hierarchy-and-entry-components">This guide</a> can be used to get more familiar with these topics.</p>

<h2 id="openldap-online-configuration">OpenLDAP Online Configuration</h2>

<p>LDAP systems organize the data they store into hierarchical structures called <strong>Directory Information Trees</strong> or <strong>DITs</strong> for short.  Starting with version 2.3, the actual configuration for OpenLDAP servers is managed within a special DIT, typically rooted at an entry called <code>cn=config</code>.</p>

<p>This configuration system is known as OpenLDAP online configuration, or <strong>OLC</strong>.  Unlike the deprecated configuration method, which relied on reading configuration files when the service starts, modifications made to the OLC are immediately implemented and often do not require the service to be restarted.</p>

<p>The OLC system uses standard LDAP methods to authenticate and make modifications.  Because of this, management for seasoned LDAP administrators is often seamless, as they can use the same knowledge, skills, and tools that they use to operate the data DITs.  However, for those new to LDAP, it can be difficult to get started since you may need to know how to use LDAP tools in order to configure an environment for learning.</p>

<p>This guide will focus on teaching you basic OpenLDAP administration to get past this chicken-and-egg situation so that you can begin learning LDAP and managing your systems.</p>

<h2 id="accessing-the-root-dse">Accessing the Root DSE</h2>

<p>We will start by talking about a construct called the root DSE, which is the structure that holds all our server's individual DITs.  This is basically an entry used for managing all of the DITs that the server knows about.  By starting at this entry, we can query the server to see how it is organized and to find out where to go next.</p>

<p></p><div class="code-label notes-and-warnings note" title="What Does DSE Stand For?">What Does DSE Stand For?</div><span class="note">
DSE stands for "DSA specific entry", which is a management or control entry in an LDAP server.  DSA stands for "directory system agent", which basically means a directory server that implements the LDAP protocol.<br /></span>

<p>To query the root DSE, we must perform a search with a blank (null) search base and with a search scope of "base".  The base search scope means that only the entry given will be returned.  Typically, this is used to limit the depth of the search, but when operating on the root DSE, this is required (no information will be returned if any other search scope is selected). </p>

<p>The command we need is this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "" -LLL "+"
</li></ul></code></pre>
<p>We assume that you're performing this from the LDAP server itself and that you haven't set up any access restrictions yet.  The results should look similar to this:</p>
<div class="code-label " title="root DSE Output">root DSE Output</div><pre class="code-pre "><code langs="">dn:
structuralObjectClass: OpenLDAProotDSE
configContext: cn=config
namingContexts: dc=example,dc=com
supportedControl: 2.16.840.1.113730.3.4.18

. . .

supportedLDAPVersion: 3
supportedSASLMechanisms: GS2-IAKERB
supportedSASLMechanisms: GS2-KRB5
supportedSASLMechanisms: SCRAM-SHA-1
supportedSASLMechanisms: GSSAPI
supportedSASLMechanisms: DIGEST-MD5
supportedSASLMechanisms: NTLM
supportedSASLMechanisms: CRAM-MD5
entryDN:
subschemaSubentry: cn=Subschema
</code></pre>
<p>We've truncated the output a bit.  You can see the important meta-data about this LDAP server.  We'll cover what some of these items mean in a bit.  For now, we'll take a look at the command that generated this output.</p>

<p>The <code>-H ldap://</code> command is used to specify an unencrypted LDAP query on the localhost.  The <code>-x</code> without any authentication information lets the server know you want an anonymous connection.  We tell it the search scope and set the search base to null with <code>-s base -b ""</code>.  We suppress some extraneous output with <code>-LLL</code>.  Finally, the <code>"+"</code> specifies that we want to see the operational attributes that would normally be hidden (this is where we'll find the information we need).</p>

<h3 id="find-the-dits-this-server-manages">Find the DITs this Server Manages</h3>

<p>For our purposes now, we are trying to find out what DITs this particular LDAP server is configured to serve.  We can find that as the value of the <code>namingContexts</code> operational attribute that we can see in the output above.</p>

<p>If this was the only piece of information we wanted, we could construct a better query that would look like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "" -LLL "namingContexts"
</li></ul></code></pre>
<p>Here, we've called out the exact attribute that we want to know the value of.  The base entry of each DIT on the server is available through the <code>namingContexts</code> attribute.  This is an operational attribute that would normally be hidden, but calling it out explicitly allows it to be returned.</p>

<p>This will suppress the other information, giving us clean output that looks like this:</p>
<div class="code-label " title="namingContexts search">namingContexts search</div><pre class="code-pre "><code langs="">dn:
namingContexts: <span class="highlight">dc=example,dc=com</span>
</code></pre>
<p>We can see that this LDAP server has only one (non-management) DIT which is rooted at an entry with a distinguished name (DN) of <code>dc=example,dc=com</code>.  It's possible that this would return multiple values if the server is responsible for additional DITs.</p>

<h3 id="find-the-configuration-dit">Find the Configuration DIT</h3>

<p>The DIT that can be used to configure the OpenLDAP server is not returned by a search for <code>namingContexts</code>.  The root entry of the config DIT is instead stored in a dedicated attribute called <code>configContext</code>.</p>

<p>To learn the base DN for the configuration DIT, you query this specific attribute, just as we did before:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "" -LLL "configContext"
</li></ul></code></pre>
<p>The result will likely be this:</p>
<div class="code-label " title="configContext search">configContext search</div><pre class="code-pre "><code langs="">dn:
configContext: <span class="highlight">cn=config</span>
</code></pre>
<p>The configuration DIT is based at a DN called <code>cn=config</code>.  Since it is likely that this matches your configuration DIT exactly, we'll use this throughout the guide.  Modify the given commands if your configuration DIT is different.</p>

<h2 id="accessing-the-configuration-dit">Accessing the Configuration DIT</h2>

<p>Now that we know the location of the configuration DIT, we can query it to see the current settings.  To do this, we actually need to diverge a bit from the format we've been using up to this point.</p>

<p>Since this DIT can be used to change the settings of our LDAP system, it has some access controls in place.  It is configured, by default, to allow administration for root or <code>sudo</code> users of the OS.</p>

<p>The command we need looks like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q
</li></ul></code></pre>
<p>To make this work, you need to use <code>sudo</code> before the command and replace the <code>-x</code> in our previous <code>ldapsearch</code> commands with <code>-Y EXTERNAL</code> to indicate that we want to use a SASL authentication method.  You also need to change the protocol from <code>ldap://</code> to <code>ldapi://</code> to make the request over a Unix socket.  This allows OpenLDAP to verify the operating system user, which it needs to evaluate the access control properties.  We then use the <code>cn=config</code> entry as the basis of our search.</p>

<p>The result will be a long list of settings.  It may be helpful to pipe it into a pager so that you can easily scroll up and down:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q | less
</li></ul></code></pre>
<p>You can see that there is quite a lot of information, which can be a lot to process.  This command printed off the entire configuration tree.  To get a better idea of the hierarchy in which the information is organized and stored, let's just print out the various entry DNs instead:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q dn
</li></ul></code></pre>
<p>This will be a much more manageable list, showing the entry titles (DNs) themselves instead of their entire content:</p>
<div class="code-label " title="cn=config entry DNs">cn=config entry DNs</div><pre class="code-pre "><code langs="">dn: cn=config

dn: cn=module{0},cn=config

dn: cn=schema,cn=config

dn: cn={0}core,cn=schema,cn=config

dn: cn={1}cosine,cn=schema,cn=config

dn: cn={2}nis,cn=schema,cn=config

dn: cn={3}inetorgperson,cn=schema,cn=config

dn: olcBackend={0}hdb,cn=config

dn: olcDatabase={-1}frontend,cn=config

dn: olcDatabase={0}config,cn=config

dn: olcDatabase={1}hdb,cn=config
</code></pre>
<p>These entries represent the configuration hierarchy where different areas of the LDAP system are configured.  Let's take a look at what settings are handled by each of these entries:</p>

<p>The top-level entry contains some global settings that will apply to the entire system (unless overridden in a more specific context).  You can see what is stored in this entry by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q -s base
</li></ul></code></pre>
<p>Common items in this section are global authorization settings, log level verbosity settings, a pointer to the process's PID file location, and information about SASL authentication.</p>

<p>The entries beneath this configure more specific areas of the system.  Let's take a look at the different types of entries you are likely to see.</p>

<h2 id="find-admin-entry">Find Admin Entry</h2>

<p>Now that you have access to the <code>cn=config</code> DIT, we can find the rootDNs of all of the DITs on the system.  A rootDN is basically the administrative entry.  We can also find the password (usually hashed) that can be used to log into that account.</p>

<p>To find the rootDN for each of your DITs, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" "(olcRootDN=*)" olcSuffix olcRootDN olcRootPW -LLL -Q
</li></ul></code></pre>
<p>You will get a printout that looks something like this:</p>
<div class="code-label " title="rootDN Information">rootDN Information</div><pre class="code-pre "><code langs="">dn: olcDatabase={1}hdb,cn=config
olcSuffix: dc=example,dc=com
olcRootDN: cn=admin,dc=example,dc=com
olcRootPW: {SSHA}AOADkATWBqb0SJVbGhcIAYF+ePzQJmW+
</code></pre>
<p>If your system serves multiple DITs, you should see one block for each of them.  Here, we can see that our admin entry is <code>cn=admin,dc=example,dc=com</code> for the DIT based at <code>dc=example,dc=com</code>.  We can also see hashed password.</p>

<h2 id="viewing-schema-information">Viewing Schema Information</h2>

<p>LDAP schemas define the objectClasses and attributes available to the system.  Schemas can be added to the system during runtime to make different object types and attributes available.  However, certain properties are built-in to the system itself.</p>

<h3 id="view-the-built-in-schema">View the Built-In Schema</h3>

<p>The built-in schema can be found in the <code>cn=schema,cn=config</code> entry.  You can see the schema that is built-in to the LDAP system by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=schema,cn=config" -s base -LLL -Q | less
</li></ul></code></pre>
<p>This will show you the schema that is included in the OpenLDAP system itself.  Unlike every other schema, this does not need to be added to the system to be used.</p>

<h3 id="view-additional-schema">View Additional Schema</h3>

<p>The built-in schema provides a nice jumping off point but it likely won't have everything you want to use in your entries.  You can add additional schema to your system through conventional LDIF methods.  These will be available as sub-entries beneath the <code>cn=schema</code> entry that represents the built-in schema.</p>

<p>Usually, these will be named with a bracketed number followed by the schema name like <code>cn={0}core,cn=schema,cn=config</code>.  The bracketed number represents an index used to determine the order that the schema are read into the system.  This is typically done automatically by the system when they are added.</p>

<p>To see just the names of the additional schema loaded onto the system, you can type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=schema,cn=config" -s one -Q -LLL dn
</li></ul></code></pre>
<p>The output will show the names of the sub-entries.  It may look something like this, depending on what's been loaded onto the system:</p>
<div class="code-label " title="additional schemas">additional schemas</div><pre class="code-pre "><code langs="">dn: cn={0}core,cn=schema,cn=config

dn: cn={1}cosine,cn=schema,cn=config

dn: cn={2}nis,cn=schema,cn=config

dn: cn={3}inetorgperson,cn=schema,cn=config
</code></pre>
<p>The schema themselves and the index number assigned may vary.  You can see the contents of a specific schema by doing a base search and listing the specific schema you are interested in.  For instance, if we wanted to see the <code>cn={3}inetorgperson</code> schema listed above, we could type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "<span class="highlight">cn={3}inetorgperson,cn=schema,cn=config</span>" -s base -LLL -Q | less
</li></ul></code></pre>
<p>If you want to print all of the additional schema, instead type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=schema,cn=config" -s one -LLL -Q | less
</li></ul></code></pre>
<p>If you want to print out all of the schema, including the built-in schema, use this instead:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=schema,cn=config" -LLL -Q | less
</li></ul></code></pre>
<h2 id="modules-backends-and-database-settings">Modules, Backends, and Database Settings</h2>

<p>Some other areas of interest in the configuration DIT are modules and the various storage technology settings.</p>

<h3 id="modules">Modules</h3>

<p>Modules are used to extend the functionality of the OpenLDAP system.  These entries are used to point to and load modules in order to use their functionality.  The actual configuration is done through other entries.</p>

<p>Entries used to load modules will start with <code>cn=module{#}</code> where the bracket contains a number in order to order the loading of modules and to differentiate between the various entries.</p>

<p>You can see the modules that are dynamically loaded on the system by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q "objectClass=olcModuleList"
</li></ul></code></pre>
<p>You will see the modules that are currently loaded into the system:</p>
<div class="code-label " title="loaded modules">loaded modules</div><pre class="code-pre "><code langs="">dn: cn=module{0},cn=config
objectClass: olcModuleList
cn: module{0}
olcModulePath: /usr/lib/ldap
olcModuleLoad: {0}back_hdb
</code></pre>
<p>This particular example only has a single module which allows us to use the <code>hdb</code> backend module.</p>

<h3 id="backends">Backends</h3>

<p>Backend entries are used to specify the storage technology that will actually handle the data storage.  </p>

<p>To see which backends are active for your system, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q "objectClass=olcBackendConfig"
</li></ul></code></pre>
<p>The result will give you an idea of the storage technology in use.  It may look something like this:</p>
<div class="code-label " title="OpenLDAP active backends">OpenLDAP active backends</div><pre class="code-pre "><code langs="">dn: olcBackend={0}hdb,cn=config
objectClass: olcBackendConfig
olcBackend: {0}hdb
</code></pre>
<h3 id="databases">Databases</h3>

<p>The actual configuration of these storage systems is done in separate database entries.  There should be a database entry for each of the DITs that an OpenLDAP system serves.  The attributes available will depend on the backend used for each of the databases.</p>

<p>To see all of the names of database entries on the system, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "cn=config" -LLL -Q "olcDatabase=*" dn
</li></ul></code></pre>
<p>You should see the DNs of the database entries:</p>
<div class="code-label " title="database entries">database entries</div><pre class="code-pre "><code langs="">dn: olcDatabase={-1}frontend,cn=config

dn: olcDatabase={0}config,cn=config

dn: olcDatabase={1}hdb,cn=config
</code></pre>
<p>Let's discuss a bit about what each of these is used for:</p>

<ul>
<li><strong><code>olcDatabase={-1}frontend,cn=config</code></strong>: This entry is used to define the features of the special "frontend" database.  This is a pseudo-database used to define global settings that should apply to all other databases (unless overridden).</li>
<li><strong><code>olcDatabase={0}config,cn=config</code></strong>: This entry is used to define the settings for the <code>cn=config</code> database that we are now using.  Most of the time, this will be mainly access control settings, replication configuration, etc.</li>
<li><strong><code>olcDatabase={1}hdb,cn=config</code></strong>: This entry defines the settings for a database of the type specified (<code>hdb</code> in this case).  These will typically define access controls, details of how the data will be stored, cached, and buffered, and the root entry and administrative details of the DIT.</li>
</ul>

<p>The numbers in brackets represent an index value.  They are mainly created automatically by the system.  You will have to substitute the value given to the entry in order to reference it successfully.</p>

<p>You can see the contents of any of these entries by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">sudo ldapsearch -H ldapi:// -Y EXTERNAL -b "<span class="highlight">entry_to_view</span>" -LLL -Q -s base | less
</li></ul></code></pre>
<p>Use the entry DNs returned from the previous command to populate the <code><span class="highlight">entry_to_view</span></code> field.</p>

<h2 id="print-an-entry-39-s-operational-attributes-metadata">Print an Entry's Operational Attributes (Metadata)</h2>

<p>So far, we've been working mainly with the <code>cn=config</code> DIT.  The rest of this guide will be applicable to regular DITs as well.</p>

<p>Each entry has operational attributes that act as administrative metadata.  These can be accessed in any DIT in order to find out important information about the entry.</p>

<p>To print out all of the operational attributes for an entry, you can specify the special "+" attribute after the entry.  For instance, to print out the operational attributes of an entry at <code>dc=example,dc=com</code>, we could type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "dc=example,dc=com" -LLL "+"
</li></ul></code></pre>
<p>This will print off all of the operational attributes.  It will likely look something like this:</p>
<pre class="code-pre "><code langs="">[list operational attributes]
dn: dc=example,dc=com
structuralObjectClass: organization
entryUUID: cdc658a2-8c3c-1034-8645-e30b83a2e38d
creatorsName: cn=admin,dc=example,dc=com
createTimestamp: 20150511151904Z
entryCSN: 20150511151904.220840Z#000000#000#000000
modifiersName: cn=admin,dc=example,dc=com
modifyTimestamp: 20150511151904Z
entryDN: dc=example,dc=com
subschemaSubentry: cn=Subschema
hasSubordinates: TRUE
</code></pre>
<p>This can be useful for seeing who modified or created an entry at what time, among other things.</p>

<h2 id="working-with-the-subschema">Working with the subschema</h2>

<p>The subschema is a representation of the available classes and attributes.  It shows similar information to the schema entries in the <code>cn=config</code> DIT, with some additional information.  This is available through regular, non-configuration DITs, so root access is not required.</p>

<h3 id="finding-the-subschema">Finding the subschema</h3>

<p>To find the subschema for an entry, you can query all of the operational attributes of an entry, as we did above, or you can ask for the specific attribute that defines the subschema for the entry (<code>subschemaSubentry</code>):</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "dc=example,dc=com" -LLL subschemaSubentry
</li></ul></code></pre>
<p>This will print out the subschema entry that is associated with the current entry:</p>
<pre class="code-pre "><code langs="">[list subchema entry]
dn: dc=chilidonuts,dc=tk
subschemaSubentry: <span class="highlight">cn=Subschema</span>
</code></pre>
<p>It is common for every entry within a tree to share the same subschema, so you usually will not have to query this for each entry.</p>

<h3 id="displaying-the-subschema">Displaying the subschema</h3>

<p>To view the contents of the subschema entry, we need to query the subschema entry we found above with a scope of "base".  All of the important information is stored in operational attributes, so we will have to use the special "+" selector again.</p>

<p>The command we need is:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "<^>cn=subschema" -LLL "+" | less
</li></ul></code></pre>
<p>This will print out the entirety of the subschema entry.  We can filter based on the type of information we are looking for.</p>

<p>If you want to see the LDAP syntax definitions, you can filter by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "cn=subschema" -LLL ldapSyntaxes | less
</li></ul></code></pre>
<p>If you want to view the definitions that control how searches are processed to match entries, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "cn=subschema" -LLL matchingRules | less
</li></ul></code></pre>
<p>To see which items the matching rules can be used to match, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "cn=subschema" -LLL matchingRuleUse | less
</li></ul></code></pre>
<p>To view the definitions for the available attribute types, use:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "cn=subschema" -LLL attributeTypes | less
</li></ul></code></pre>
<p>To view the objectClass definitions, type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -H ldap:// -x -s base -b "cn=subschema" -LLL objectClasses | less
</li></ul></code></pre>
<h2 id="conclusion">Conclusion</h2>

<p>While operating an OpenLDAP server can seem tricky at first, getting to know the configuration DIT and how to find metadata within the system can help you hit the ground running.  Modifying the <code>cn=config</code> DIT with LDIF files can immediately affect the running system.  Also, configuring the system via a DIT allows you to potentially set up remote administration using only LDAP tools.  This means that you can separate LDAP administration from server administration.</p>

    