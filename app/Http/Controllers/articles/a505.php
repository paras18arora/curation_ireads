<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>LDAP is a protocol for managing and interacting with directory services.  The OpenLDAP project provides an LDAP-compliant directory service that can be used to store and provide an interface to directory data.</p>

<p>In this guide, we will discuss the LDIF file format that is used to communicate with LDAP directories.  We will discuss the tools that you can use to process these files and modify the LDAP Directory Information Tree based on the commands specified.</p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Before starting this guide, you should have access to an OpenLDAP server.  You can learn how to set up an OpenLDAP server <a href="https://indiareads/community/tutorials/how-to-install-and-configure-openldap-and-phpldapadmin-on-an-ubuntu-14-04-server">here</a>.  You should be familiar with the basic terminology used when working with an LDAP directory service.  <a href="https://indiareads/community/tutorials/understanding-the-ldap-protocol-data-hierarchy-and-entry-components">This guide</a> can be used to get more familiar with these topics.</p>

<h2 id="ldif-format">LDIF Format</h2>

<p>LDIF, or the LDAP Data Interchange Format, is a text format for representing LDAP data and commands.  When using an LDAP system, you will likely use the LDIF format to specify your data and the changes you wish to make to the LDAP DIT.</p>

<p>LDIF is meant to be able to describe any entry within an LDAP system, as well as any modifications that must take place.  Because of this, the syntax is very precise and can initially seem somewhat complex.  Using LDIF, LDAP changes are simple written within files with an arbitrary name and then fed into the LDAP system using one of the available management commands.</p>

<p>LDIF works using a basic key-value system, with one statement per-line. The key is on the left-hand side of a line followed by a colon (:) and a space.  The space is important for the line to be read correctly.  The value is then assigned on the right side.  This format works well for LDAP's attribute-heavy syntax, but can also be used to issue commands and provide instructions on how the content should be interpreted.</p>

<p>Multiple lines can be used to provide long values for attribute by beginning the extra lines with a single space.  LDAP will join these when processing the entry.</p>

<h2 id="adding-entries-to-the-dit">Adding Entries to the DIT</h2>

<p>There are two main ways of specifying a new entry within an LDIF file.  The best method for your needs depends on the types of other changes you need to coordinate with.  The method you choose will dictate the tools and arguments you must use to apply the changes to the LDAP DIT (directory information tree).</p>

<h3 id="listing-entries-to-add-to-the-dit">Listing Entries to Add to the DIT</h3>

<p>The most basic method of defining new entries to add to LDAP is to simply list the entries in their entirety, exactly as they would typically displayed using LDAP tools.  This starts with the DN (distinguished name) where the entry will be created, after the <code>dn:</code> indicator:</p>
<pre class="code-pre "><code langs="">dn: ou=newgroup,dc=example,dc=com
</code></pre>
<p>In the line above, we reference a few key-value pairs in order to construct the DN for our new entry.  When <em>setting</em> attribute values, you must use the colon and space.  When <em>referencing</em> attributes/values, an equal sign should be used instead.</p>

<p>In the simplest LDIF format for adding entries to a DIT, the rest of the entry is simply written out using this format beneath the DN definition.  The necessary objectClass declarations and attributes must be set to construct a valid entry.  For example, to create an organizational unit to contain the entries for the employees of our organization, we could use this:</p>
<pre class="code-pre "><code langs="">dn: ou=People,dc=example,dc=com
objectClass: organizationalUnit
ou: People
</code></pre>
<p>You can add multiple entries in a single file.  Each entry must be separated by at least one completely blank line:</p>
<pre class="code-pre "><code langs="">dn: ou=People,dc=example,dc=com
objectClass: organizationalUnit
ou: People

dn: ou=othergroup,dc=example,dc=com
objectClass: organizationalUnit
ou: othergroup
</code></pre>
<p>As you can see, this LDIF format mirrors almost exactly the format you would see when querying an LDAP tree for entries with this information.  You can pretty much just write what you'd like the entry to contain verbatim.</p>

<h3 id="using-quot-changetype-add-quot-to-create-new-entries">Using "Changetype: Add" to Create New Entries</h3>

<p>The second format that we will be looking at works well if you are making other modifications within the same LDIF file.  OpenLDAP provides tools that can handle both additions and modifications, so if we are modifying other entries within the same file, we can flag our new entries as additions so that they are processed correctly.</p>

<p>This looks much like the method above, but we add <code>changetype: add</code> directly below the DN specification.  For instance, we could add a John Smith entry to a DIT that already contains the <code>ou=People,dc=example,dc=com</code> structure using an LDIF like this:</p>
<pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
<span class="highlight">changetype: add</span>
objectClass: inetOrgPerson
description: John Smith from Accounting.  John is the project
  manager of the building project, so contact him with any que
 stions.
cn: John Smith
sn: Smith
uid: jsmith1
</code></pre>
<p>This is basically the format we've been using to describe entries thus far, with the exception of an additional line after the DN specification.  Here, we tell LDAP that the change we are making is an entry creation.  Since we are using the <code>changetype</code> option, this entry can be processed by the <code>ldapmodify</code> tool without a problem, allowing us to place modifications of other types in the same LDIF file.  The <code>changetype</code> option must come immediately after the DN specification.</p>

<p>Another thing to note above is the use of a multi-line value for the <code>description</code> attribute.  Since the lines that follow begin with a space, they will be joined with the space removed.  Our first continuation line in our example contains an additional space, but that is part of the sentence itself, separating the words "project" and "manager".</p>

<p>As with the last section, each additional entry within the same file is separated by a blank line.  Comments can be used by starting the line with a <code>#</code> character.  Comments must exist on their own line.  For instance, if we wanted to add Sally in this same LDIF file, we could separate the two entries like this:</p>
<pre class="code-pre "><code langs=""># Add John Smith to the organization
dn: uid=jsmith1,ou=People,dc=example,dc=com
changetype: add
objectClass: inetOrgPerson
description: John Smith from Accounting.  John is the project
  manager of the building project, so contact him with any qu
 estions.
cn: John Smith
sn: Smith
uid: jsmith1

# Add Sally Brown to the organization
dn: uid=sbrown20,ou=People,dc=example,dc=com
changetype: add
objectClass: inetOrgPerson
description: Sally Brown from engineering.  Sally is responsibl
 e for designing the blue prints and testing the structural int
 egrity of the design.
cn: Sally Brown
sn: Brown
uid: sbrown20
</code></pre>
<h3 id="processing-entry-additions">Processing Entry Additions</h3>

<p>Now that we know how to construct LDIF files to add new entries, we need to actually process these with LDAP tools to add them to the DIT.  The tool and/or arguments you use will depend on the form you chose above.  </p>

<p>If you are using the simple entry format (without the <code>changetype</code> setting), you can use the <code>ldapadd</code> command or the <code>ldapmodify</code> command with the <code>-a</code> flag, which specifies an entry addition.  You will either need to use a SASL method to authenticate with the LDAP instance (this is outside of the scope of this guide), or bind to an administrative account in your DIT and provide the required password.</p>

<p>For instance, if we stored our entries from the simple entry section in a file called <code>newgroups.ldif</code>, the command we would need to process the file and add the new entries would look something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapadd -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f newgroups.ldif
</li></ul></code></pre>
<p>You could also use the <code>ldapmodify -a</code> combination for the same result:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -a -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f newgroups.ldif
</li></ul></code></pre>
<p>If you are using the <em>second</em> format, with the <code>changetype</code> declaration, you will want to use the <code>ldapmodify</code> command without the <code>-a</code> flag.  Since this command and format works for most other modifications, it is probably easier to use for most changes.  If we stored the two new user additions within a file called <code>newusers.ldif</code>, we could add it to our existing DIT by typing something like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f newusers.ldif
</li></ul></code></pre>
<p>This will allow you to add entries to your DIT at will.  You can easily store many entries in a single LDIF file and populate your DIT in a single command.</p>

<h2 id="deleting-entries-from-the-dit">Deleting Entries from the DIT</h2>

<p>We had our first glimpse of the <code>changetype</code> option in the last section.  This option provides the method for specifying the high-level type of modification we wish to make.  For an entry deletion, the value of this option is "delete".</p>

<p>Entry deletion is actually the most straight-forward change that you can perform because the only piece of information needed is the DN.</p>

<p>For instance, if we wanted to remove the <code>ou=othergroup</code> entry from our DIT, our LDIF file would only need to contain this:</p>
<pre class="code-pre "><code langs="">dn: ou=othergroup,dc=example,dc=com
changetype: delete
</code></pre>
<p>To process the change, you can use the exact format used with <code>ldapmodify</code> above.  If we call the file with the deletion request <code>rmothergroup.ldif</code>, we would apply it like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f rmothergroup.ldif
</li></ul></code></pre>
<p>This will remove the <code>ou=othergroup</code> entry from the system immediately.</p>

<h2 id="modifying-an-entry-39-s-attributes">Modifying an Entry's Attributes</h2>

<p>Modifying an entry's attributes is a very common change to make and is made possible by specifying <code>changetype: modify</code> after the DN of the entry.  The types of modifications you can make to attributes mostly mirror the modifications you can make to an entry itself.  Because of this, the details of the type of requested attribute change are specified afterwards using additional directives.</p>

<h3 id="adding-an-attribute-to-an-entry">Adding an Attribute to an Entry</h3>

<p>For instance, you can add an attribute by using the <code>add:</code> command after <code>changetype: modify</code>.  This should specify the attribute you wish to add.  You would then set the value of the attribute like normal.  So the basic format would be:</p>
<pre class="code-pre "><code langs="">dn: <span class="highlight">entry_to_add_attribute</span>
changetype: modify
add: <span class="highlight">attribute_type</span>
<span class="highlight">attribute_type</span>: <span class="highlight">value_to_set</span>
</code></pre>
<p>For instance, to add some email addresses to our accounts, we could have an LDIF file that looks like this:</p>
<pre class="code-pre "><code langs="">dn: uid=sbrown20,ou=People,dc=example,dc=com
changetype: modify
add: mail
mail: sbrown@example.com

dn: uid=jsmith1,ou=People,dc=example,dc=com
changetype: modify
add: mail
mail: jsmith1@example.com
mail: johnsmith@example.com
</code></pre>
<p>As you can see from the second entry, you can specify multiple additions at the same time.  The <code>mail</code> attribute allows for multiple values, so this is permissible.</p>

<p>You can process this with <code>ldapmodify</code> as normal.  If the change is in the file <code>sbrownaddmail.ldif</code>, you could type:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f sbrownaddmail.ldif
</li></ul></code></pre>
<h3 id="replacing-the-value-of-an-attribute-in-an-entry">Replacing the Value of an Attribute in an Entry</h3>

<p>Another common change is to modify the existing value for an attribute.  We can do this using the <code>replace:</code> option below <code>changetype: modify</code>.</p>

<p>This operates in almost the same way as the <code>add:</code> command, but by default, removes every existing occurrence of the attribute from the entry and replaces it with the values defined afterwards.  For instance, if we notice that our last <code>add:</code> command had an incorrect email, we could modify it with the <code>replace</code> command like this:</p>
<pre class="code-pre "><code langs="">dn: uid=sbrown20,ou=People,dc=example,dc=com
changetype: modify
replace: mail
mail: sbrown2@example.com
</code></pre>
<p>Keep in mind that this will replace <em>every</em> instance of <code>mail</code> in the entry.  This is important for multi-value attributes that can be defined more than once per-entry (like <code>mail</code>).  If you wish to replace only a single occurrence of an attribute, you should use the attribute <code>delete:</code> option (described below) in combination with the attribute <code>add:</code> option (described above).</p>

<p>If this change was stored in a file called <code>sbrownchangemail.ldif</code>, we can replace Sally's email by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f sbrownchangemail.ldif
</li></ul></code></pre>
<h3 id="delete-attributes-from-an-entry">Delete Attributes from an Entry</h3>

<p>If you wish to remove an attribute from an entry, you can use the <code>delete:</code> command.  You will specify the attribute you wish to delete as the value of the option.  If you want to delete a specific instance of the attribute, you can specify the specific key-value attribute occurrence on the following line.  Otherwise, every occurrence of that attribute in the entry will be removed.</p>

<p>For instance, this would delete every description attribute in John Smith's entry:</p>
<pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
changetype: modify
delete: description
</code></pre>
<p>However, this would delete only the email specified:</p>
<pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
changetype: modify
delete: mail
mail: jsmith1@example.com
</code></pre>
<p>Since we gave John two email addresses earlier, the other email address should be left unchanged by this request.</p>

<p>If these changes were in files called <code>jsmithrmdesc.ldif</code> and <code>jsmithrmextramail.ldif</code>, we could apply them by typing:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f jsmithrmdesc.ldif
</li><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f jsmithrmextramail.ldif
</li></ul></code></pre>
<h3 id="specifying-multiple-attribute-changes">Specifying Multiple Attribute Changes</h3>

<p>This is a good time to talk about specifying multiple attribute changes at the same time.  For a single entry within an LDIF file, you can specify multiple attribute changes by separating them with a line populated only with the <code>-</code> character.  Following the separator, the attribute change type must be specified and the required attributes must be given.</p>

<p>For example, we could delete John's remaining email attribute, change his name to "Johnny Smith" and add his location by creating a file with the following contents:</p>
<pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
changetype: modify
delete: mail
-
replace: cn
cn: Johnny Smith
-
add: l
l: New York
</code></pre>
<p>To apply all of these changes in one command, we'd use the same <code>ldapmodify</code> format we've been using all along:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f multichange.ldif
</li></ul></code></pre>
<h2 id="renaming-and-moving-entries">Renaming and Moving Entries</h2>

<p>The <code>changetype: modrdn</code> option makes it possible to rename or move existing entries.  After specifying the <code>dn:</code> you wish to target, set the <code>changetype: modrdn</code> option.</p>

<h3 id="renaming-an-entry">Renaming an Entry</h3>

<p>Let's say that we mistyped Sally's username when we initially entered it into the system.  Since that is used in the entry's DN, it can't simply be replaced with the <code>changetype: modify</code> and <code>replace:</code> options because the entry's RDN would be invalid.  If her real username is <code>sbrown200</code>, we could change the entry's DN, creating any necessary attributes along the way, with an LDIF file like this:</p>
<pre class="code-pre "><code langs="">dn: uid=sbrown20,ou=People,dc=example,dc=com
changetype: modrdn
newrdn: uid=sbrown200
deleteoldrdn: 0
</code></pre>
<p>We could apply this change with this command:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f fixsallydn.ldif
</li></ul></code></pre>
<p>This would make the complete entry look something like this:</p>
<pre class="code-pre "><code langs="">dn: uid=sbrown200,ou=People,dc=example,dc=com
objectClass: inetOrgPerson
description: Sally Brown from engineering.  Sally is responsibl
 e for designing the blue prints and testing the structural int
 egrity of the design.
cn: Sally Brown
sn: Brown
uid: sbrown20
uid: sbrown200
mail: sbrown2@example.com
</code></pre>
<p>As you can see, our DN has been adjusted to use the new attribute/value pair.  The attribute has been added to the entry to make this possible.</p>

<p>You may have noticed two things in the example above.  First, we set an option called <code>deleteoldrdn</code> to "0".  Secondly, the resulting entry has both <code>uid: sbrown20</code> and <code>uid: sbrown200</code>.</p>

<p>The <code>deleteoldrdn</code> option must be set when changing the DN of an entry.  Setting <code>deleteoldrdn</code> to "0" causes LDAP to keep the old attribute used in the DN alongside the new attribute in the entry.  Sometimes this is what you want, but often you will want to remove the old attribute from the entry completely after the DN has changed.  You can do that by setting <code>deleteoldrdn</code> to "1" instead.</p>

<p>Let's pretend we made a mistake again and that Sally's actual username is <code>sbrown2</code>.  We can set <code>deleteoldrdn</code> to "1" to remove the <code>sbrown200</code> instance that is currently used in the DN from the entry after the rename.  We'll go ahead and include an additional <code>changetype: modify</code> and <code>delete:</code> pair to get rid of the other stray username, <code>sbrown20</code>, since we kept that around during the first rename:</p>
<pre class="code-pre "><code langs="">dn: uid=sbrown200,ou=People,dc=example,dc=com
changetype: modrdn
newrdn: uid=sbrown2
deleteoldrdn: 1

dn: uid=sbrown2,ou=People,dc=example,dc=com
changetype: modify
delete: uid
uid: sbrown20
</code></pre>
<p>Apply the file like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f fix2sallydn.ldif
</li></ul></code></pre>
<p>This combination will not add a new username with the change (<code>sbrown200</code> will be removed), and the second entry modification will remove the original value of the username (<code>sbrown20</code>).</p>

<h3 id="moving-an-entry">Moving an Entry</h3>

<p>If you need to move the entry to a new location, an additional setting for <code>changetype: modrdn</code> is the <code>newsuperior:</code> option.  When using this option, you can specify a new location on the DIT to move the entry to.  This will place the entry under the specified parent DN during the change.</p>

<p>For instance, if we wanted to move Sally under the <code>ou=superusers</code> entry, we could add this entry and then move her to it by typing:</p>
<pre class="code-pre "><code langs="">dn: ou=superusers,dc=example,dc=com
changetype: add
objectClass: organizationalUnit
ou: superusers

dn: uid=sbrown2,ou=People,dc=example,dc=com
changetype: modrdn
newrdn: uid=sbrown2
deleteoldrdn: 0
newsuperior: ou=superusers,dc=example,dc=com
</code></pre>
<p>Assuming that this is stored in a file called <code>mksuperuser.ldif</code>, we could apply the changes like this:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapmodify -x -D "cn=admin,dc=example,dc=com" -w <span class="highlight">password</span> -H ldap:// -f mksuperuser.ldif
</li></ul></code></pre>
<p>This results in a move and never a copy.</p>

<p>In this case, we did not wish to actually change the RDN of the entry, so we set the <code>newrdn:</code> value to the same value that it currently has.  We could easily rename during the move too though if we so desired.  In this case, the <code>newsuperior:</code> setting is the only line of the second change that actually impacts the state of the entry.</p>

<h2 id="an-aside-adding-binary-data-to-an-entry">An Aside: Adding Binary Data to an Entry</h2>

<p>This section is separate from the information above because it could fit within the sections on creating an entry or with defining additional attributes. </p>

<p>LDAP has the ability to store binary data for certain attributes.  For instance, the <code>inetOrgPerson</code> class allows an attribute called <code>jpegPhoto</code>, which can be used to store a person's photograph or user icon.  Another attribute of this objectClass that can use binary data is the <code>audio</code> attribute.</p>

<p>To add this type of data to an LDAP entry, you must use a special format.  When specifying the attribute, immediately following the colon, use a less-than character (<) and a space.  Afterwards, include the path to the file in question.</p>

<p>For instance, if you have a file called <code>john.jpg</code> in the <code>/tmp</code> directory, you can add the file to John's entry with an LDIF file that looks like this:</p>
<pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
changetype: modify
add: jpegPhoto
jpegPhoto:< file:///tmp/john.jpg
</code></pre>
<p>Pay close attention to the placement of the colon, less than character, and space.  If your file is located on disk, the <code>file://</code> prefix can be used.  The path will add an additional slash to indicate the root directory if you are using an absolute path.</p>

<p>This would work the same way with an audio file:</p>
<pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
changetype: modify
add: audio
audio:< file:///tmp/hellojohn.mp3
</code></pre>
<p>Once you have processed the LDIF file, the actual file will be encoded within your LDAP directory service.  This is important to keep in mind, because adding significant number of files like this will have an impact on the size and performance of your service.</p>

<p>When you need to retrieve the encoded data using the <code>ldapsearch</code> tool, you will need to add the <code>-t</code> flag, which will allow the file to be written to the <code>/tmp</code> directory.  The generated filename will be indicated in the results.</p>

<p>For instance, we could use this command to write out the binary data to a temporary file:</p>
<pre class="code-pre command"><code langs=""><ul class="prefixed"><li class="line" prefix="$">ldapsearch -LLL -x -H ldap:// <span class="highlight">-t</span> -b "dc=example,dc=com" "uid=jsmith1"
</li></ul></code></pre>
<p>The search result will look like this:</p>
<div class="code-label " title="ldapsearch output">ldapsearch output</div><pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
objectClass: inetOrgPerson
sn: Smith
uid: jsmith1
cn: Johnny Smith
l: New York
audio:< file:///tmp/<span class="highlight">ldapsearch-audio-n5GRF6</span>
</code></pre>
<p>If we go to the <code>/tmp</code> directory, we can find the file.  It can be renamed as needed and should be in the exact state that it was in before entering it into the directory.</p>

<p>Be careful when doing this operation repeatedly, as a new file is written out each time the search is performed.  You could easily fill a disk without realizing if you do not pay attention.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now you should have a fairly good handle on how to manipulate the entries within an LDAP directory information tree using LDIF formatted files and a few tools.  While certain LDAP clients may make LDIF files unnecessary for day-to-day operations, LDIF files can be the best way of performing batch operations on your DIT entries. It is also important to know how to modify your entries using these methods for administration purposes, when setting up the initial directory service, and when fixing issues that might prevent clients from correctly accessing your data.</p>

    