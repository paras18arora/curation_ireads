<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>LDAP, or Lightweight Directory Access Protocol, is an open protocol used to store and retrieve data from a hierarchical directory structure.  Commonly used to store information about an organization and its assets and users, LDAP is a flexible solution for defining any type of entity and its qualities.</p>

<p>For many users, LDAP can seem difficult to understand because it relies on special terminology, makes use of some uncommon abbreviations, and is often implemented as a component of a larger system of interacting parts.  In this guide, we will introduce you to some of the LDAP basics so that you have a good foundation for working with the technology.</p>

<h2 id="what-is-a-directory-service">What is a Directory Service?</h2>

<p>A directory service is used to store, organize and present data in a key-value type format.  Typically, directories are optimized for lookups, searches, and read operations over write operations, so they function extremely well for data that is referenced often but changes infrequently.</p>

<p>The data stored in a directory service is often descriptive in nature and used to define the qualities of an entity.  An example of a physical object that would be well represented in a directory service is an address book.  Each person could be represented by an entry in the directory, with key-value pairs describing their contact information, place of business, etc.  Directory services are useful in many scenarios where you want to make qualitative, descriptive information accessible.</p>

<h2 id="what-is-ldap">What is LDAP?</h2>

<p>LDAP, or lightweight directory access protocol, is a communications protocol that defines the methods in which a directory service can be accessed.  More broadly speaking, LDAP shapes the way that the data within a directory service should be represented to users, defines requirements for the components used to create data entries within a directory service, and outlines the way that different primitive elements are used to compose entries.</p>

<p>Since LDAP is an open protocol, there are many different implementations available.  The OpenLDAP project is one of the most well supported open source variants.</p>

<h2 id="basic-ldap-data-components">Basic LDAP Data Components</h2>

<p>We discussed above how LDAP is a protocol used to communicate with a directory database to query, add or modify information.  However, this simple definition misrepresents the complexity of the systems that support this protocol.  The way that LDAP displays data to users is very dependent upon the interaction of and relationship between some defined structural components.</p>

<h3 id="attributes">Attributes</h3>

<p>The data itself in an LDAP system is mainly stored in elements called <strong>attributes</strong>.  Attributes are basically key-value pairs.  Unlike in some other systems, the keys have predefined names which are dictated by the objectClasses selected for entry (we'll discuss this in a bit).  Furthermore, the data in an attribute must match the type defined in the attribute's initial definition. </p>

<p>Setting the value for an attribute is done with the attribute name and the attribute value separated by a colon and a space.  An example of an attribute called <code>mail</code>, which defines an email address would look like this:</p>
<pre class="code-pre "><code langs="">mail: admin@example.com
</code></pre>
<p>When referring to an attribute and its data (when not setting it), the two sides are instead joined by an equals sign:</p>
<pre class="code-pre "><code langs="">mail=example.com
</code></pre>
<p>The attribute values contain most of the actual data you want to store and access in an LDAP system.  The other elements within LDAP are used for structure, organization, etc.</p>

<h3 id="entries">Entries</h3>

<p>Attributes by themselves are not very useful.  To have meaning, they must be <em>associated</em> with something.  Within LDAP, you use attributes within an <strong>entry</strong>.  An entry is basically a collection of attributes under a name used to describe something.</p>

<p>For instance, you can have an entry for a user in your system or for each item in an inventory.  This is roughly analogous to a row in a relational database system or to a single page within an address book (the attributes here would represent the various fields in each of these models).  While an attribute defines a quality or characteristic of something, an entry describes the item itself by simply collecting these attributes under a name.</p>

<p>An example entry as displayed in the LDIF (LDAP Data Interchange Format) would look something like this:</p>
<pre class="code-pre "><code langs="">dn: sn=Ellingwood,ou=people,dc=digitalocean,dc=com
objectclass: person
sn: Ellingwood
cn: Justin Ellingwood
</code></pre>
<p>The above example could be a valid entry within an LDAP system.</p>

<h3 id="dit">DIT</h3>

<p>As you begin to become familiar with LDAP, it is easy to recognize that the data defined by attributes only represents part of the available information about an object.  The rest is found the entry's placement within the LDAP system and the relationships that this implies.</p>

<p>For instance, if it is possible to have entries for both a user and an inventory item, how would someone be able to tell them apart?  One way to distinguish between entries of different types is by establishing relationships and groups.  This is largely a function of where the entry is placed when it is created.  Entries are all added to an LDAP system as branches on trees called <strong>Data Information Trees</strong>, or <strong>DITs</strong>. </p>

<p>A DIT represents an organizational structure similar to a file system where each entry (other than the top-level entry) has exactly one parent entry and may have any number of child entries beneath it.  Since entries in an LDAP tree can represent just about anything, some entries will be used mainly for organizational purposes, similar to directories within a filesystem. </p>

<p>In this way, you may have an entry for "people" and an entry for "inventoryItems".  Your actual data entries could be created as children of these to better distinguish their type.  Your organizational entries can be arbitrarily defined to best represent your data.</p>

<p>In the example entry in the last section, we see one indication of the DIT in the <code>dn</code> line:</p>
<pre class="code-pre "><code langs="">dn: sn=Ellingwood,ou=people,dc=digitalocean,dc=com
</code></pre>
<p>This line is called the entry's distinguished name (more on this later) and is used to identify the entry.  It functions like a full path back to the root of the DIT.  In this instance, we have an entry called <code>sn=Ellingwood</code>, which we are creating. The direct parent is an entry called <code>ou=people</code> which is probably being used as a container for entries describing people.  The parents of this entry derived from the <code>digitalocean.com</code> domain name, which functions as the root of our DIT.</p>

<h2 id="defining-ldap-data-components">Defining LDAP Data Components</h2>

<p>In the last section, we discussed how data is represented within an LDAP system.  However, we must also talk about how the components that store data are defined.  For instance, we mentioned that data must match the type defined for each attribute.  Where do these definitions come from?  Let's start from the bottom in terms of complexity again and work our way up.</p>

<h3 id="attribute-definitions">Attribute Definitions</h3>

<p>Attributes are defined using fairly involved syntax. They must indicate the name for an attribute, any other names that can be used to refer to the attribute, the type of the data that may be entered, as well as a variety of other metadata.  This metadata can describe the attribute, tell LDAP how to sort or compare the attribute's value, and tell how it relates to other attributes. </p>

<p>For example, this is the definition for the <code>name</code> attribute:</p>
<pre class="code-pre "><code langs="">attributetype ( 2.5.4.41 NAME 'name' DESC 'RFC4519: common supertype of name attributes'
        EQUALITY caseIgnoreMatch
        SUBSTR caseIgnoreSubstringsMatch
        SYNTAX 1.3.6.1.4.1.1466.115.121.1.15{32768} )
</code></pre>
<p>The <code>'name'</code> is the name of the attribute.  The number in the first line is a globally unique OID (object ID) assigned to the attribute to differentiate it from every other attribute.  The rest of the entry defines how the entry can be compared during searches and has a pointer telling where to find information for the data type requirements of attribute.</p>

<p>One important part of an attribute definition is whether the attribute may be defined more than once in an entry.  For instance, the definition may define that a surname may only be defined once per entry, but an attribute for "niece" may allow that attribute to be defined multiple times in a single entry.  Attributes are multi-value by default, and must contain the <code>SINGLE-VALUE</code> flag if they may only be set once per entry.</p>

<p>Attribute definitions are much more complicated than using and setting attributes.  Fortunately, for the most part you will not have to define your own attributes because the most common ones are included with most LDAP implementations and others are available to import easily.</p>

<h3 id="objectclass-definitions">ObjectClass Definitions</h3>

<p>Attributes are collected within entities called <strong>objectClasses</strong>.  ObjectClasses are simply groupings of associated attributes that would be useful in describing a specific thing.  For instance, "person" is an objectClass. </p>

<p>Entries gain the ability to use an objectClass's attributes by setting a special attribute called <code>objectClass</code>, naming the objectClass you wish to use.  In fact, <code>objectClass</code> is the only attribute you can set in an entry without specifying a further objectClass.  </p>

<p>So if you are creating an entry to describe a person, including <code>objectClass person</code> (or any of the more specific person objectClasses derived from person — we'll cover this later) allows you to use all of the attributes within that objectClass:</p>
<pre class="code-pre "><code langs="">dn: . . .
objectClass: person
</code></pre>
<p>You would then have the ability to set these attributes within the entry:</p>

<ul>
<li><strong>cn</strong>: Common name</li>
<li><strong>description</strong>: Human-readable description of the entry</li>
<li><strong>seeAlso</strong>: Reference to related entries</li>
<li><strong>sn</strong>: Surname</li>
<li><strong>telephoneNumber</strong>: A telephone number</li>
<li><strong>userPassword</strong>: A password for the user</li>
</ul>

<p>The <code>objectClass</code> attribute can be used multiple times if you need attributes from different objectClasses, but there are rules that dictate what is acceptable.  ObjectClasses are defined as being one of several "types".</p>

<p>The two main types of ObjectClasses are <strong>structural</strong> or <strong>auxiliary</strong>.  An entry <strong>must</strong> have exactly one structural class, but may have zero or more auxiliary classes used to augment the attributes available to the class.  A structural objectClass is used to create and define the entry, while the auxiliary objectClasses add additional functionality through extra attributes.</p>

<p>ObjectClass definitions determine whether the attributes that they provide are required (indicated by a <code>MUST</code> specification) or optional (indicated by a <code>MAY</code> specification).  Multiple objectClasses can provide the same attributes and an attribute's <code>MAY</code> or <code>MUST</code> categorization may vary from objectClass to objectClass.</p>

<p>As an example, the <code>person</code> objectClass is defined like this:</p>
<pre class="code-pre "><code langs="">objectclass ( 2.5.6.6 NAME 'person' DESC 'RFC2256: a person' SUP top STRUCTURAL
  MUST ( sn $ cn )
  MAY ( userPassword $ telephoneNumber $ seeAlso $ description ) )
</code></pre>
<p>This is defined as a structural objectClass, meaning that it can be used to create an entry.  The entry created <em>must</em> set the <code>surname</code> and <code>commonname</code> attributes, and may choose to set the <code>userPassword</code>, <code>telephoneNumber</code>, <code>seeAlso</code>, or <code>description</code> attributes.</p>

<h3 id="schemas">Schemas</h3>

<p>ObjectClass definitions and attribute definitions are, in turn, grouped together in a construct known as a <strong>schema</strong>.  Unlike traditional relational databases, schemas in LDAP are simply collections of related objectClasses and attributes.  A single DIT can have many different schemas so that it can create the entries and attributes it needs.</p>

<p>Schemas will often include additional attribute definitions and may require the attributes defined in other schemas.  For example, the <code>person</code> objectClass that we discussed above requires that the <code>surname</code> or <code>sn</code> attribute be set for any entries using the <code>person</code> objectClass.  If these are not defined within the LDAP server itself, a schema containing these definitions could be used to add these definitions to the server's vocabulary. </p>

<p>The format of a schema is basically just a combination of the above entries, like this:</p>
<pre class="code-pre "><code langs="">. . .

objectclass ( 2.5.6.6 NAME 'person' DESC 'RFC2256: a person' SUP top STRUCTURAL
  MUST ( sn $ cn )
  MAY ( userPassword $ telephoneNumber $ seeAlso $ description ) )

attributetype ( 2.5.4.4 NAME ( 'sn' 'surname' )
  DESC 'RFC2256: last (family) name(s) for which the entity is known by' SUP name )

attributetype ( 2.5.4.4 NAME ( 'cn' 'commonName' )
  DESC 'RFC4519: common name(s) for which the entity is known by' SUP name )

. . .
</code></pre>
<h2 id="data-organization">Data Organization</h2>

<p>We've covered the common elements that are used to construct entries within an LDAP system and talked about how these building blocks are defined within the system.  However, we haven't talked much about how the information itself is organized and structured within an LDAP DIT yet.</p>

<h3 id="placing-entries-within-the-dit">Placing Entries within the DIT</h3>

<p>A DIT is simply the hierarchy describing the relationship of existing entries.  Upon creation, each new entry must "hook into" the existing DIT by placing itself as a child of an existing entry.  This creates a tree-like structure that is used to define relationships and assign meaning.</p>

<p>The top of the DIT is the broadest categorization under which each subsequent node is somehow descendent.  Typically, the top-most entry is simply used as a label indicating the organization that the DIT is used for.  These entries can be of whatever objectClasses desired, but usually they are constructed using domain components (<code>dc=example,dc=com</code> for an LDAP managing info associated with <code>example.com</code>), locations (<code>l=new_york,c=us</code> for an organization or segment in NY), or organizational segments (<code>ou=marketing,o=Example_Co</code>).</p>

<p>Entries used for organization (used like folders) often use the organizationalUnit objectClass, which allows the use of a simple descriptive attribute label called <code>ou=</code>.  These are often used for the general categories under the top-level DIT entry (things like <code>ou=people</code>, <code>ou=groups</code>, and <code>ou=inventory</code> are common).  LDAP is optimized for finding information laterally along the tree rather than up and down within the tree, so it is often best to keep the DIT hierarchy rather shallow, with general organizational branches and further subdivision indicated through the assignment of specific attributes.</p>

<h3 id="naming-and-referencing-entries-within-the-dit">Naming and Referencing Entries within the DIT</h3>

<p>We refer to entries by their attributes.  This means that each entry must have an attribute or group of attributes that is unambiguous at its level in the DIT hierarchy.  This attribute or group of attributes is called the entry's <strong>relative distinguished name</strong> or <strong>RDN</strong> and it functions like a file name.</p>

<p>To refer to an entry unambiguously, you use the entry's RDN combined with all of its parent entries' RDNs.  This chain of RDNs leads back up to the top of the DIT hierarchy and provides an unambiguous path to the entry in question.  We call this chain of RDNs the entry's <strong>distinguished name</strong> or <strong>DN</strong>.  You must specify the DN for an entry during creation so that the LDAP system knows where to place the new entry and can ensure that the entry's RDN is not being used by another entry already.</p>

<p>As an analogy, you can think of an RDN as a relative file or directory name, as you would see in a file system.  The DN, on the other hand is more analogous to the absolute path.  An important distinction is that LDAP DNs contain the most specific value on the <em>left-hand</em> side, while file paths contain the most specific information on the <em>right-hand</em> side.  DNs separate the RDN values with a comma.</p>

<p>For instance, an entry for a person named John Smith might be placed beneath a "People" entry for an organization under <code>example.com</code>.  Since there might be multiple John Smiths in the organization, a user ID might be a better choice for the entry's RDN.  The entry might be specified like this:</p>
<pre class="code-pre "><code langs="">dn: uid=jsmith1,ou=People,dc=example,dc=com
objectClass: inetOrgPerson
cn: John Smith
sn: Smith
uid: jsmith1
</code></pre>
<p>We had to use the <code>inetOrgPerson</code> objectClass to get access to the <code>uid</code> attribute in this instance (we still have access to all of attributes defined in the <code>person</code> objectClass, as we will see in the next section).</p>

<h2 id="ldap-inheritance">LDAP Inheritance</h2>

<p>When it comes down to it, much of the way that data in an LDAP system relates to one another is a matter of hierarchies, inheritance, and nesting.  LDAP initially seems unusual to many people because it implements some object-oriented concepts in its design.  This mainly comes from its use of classes, as we have previously discussed, and the availability of inheritance, which we will talk about now.</p>

<h3 id="objectclass-inheritance">ObjectClass Inheritance</h3>

<p>Each objectClass is a class that describes the characteristics of objects of that type.</p>

<p>However, unlike simple inheritance, objects in LDAP can be and often are instances of multiple classes (some programming languages provide similar functionality through multiple inheritance).  This is possible because LDAP's conception of a class is simply a collection of attributes that it MUST or MAY have.  This allows multiple classes to be specified for an entry (although only one <code>STRUCTURAL</code> objectClass can and must be present), resulting in the object simply having access to the merged collection of attributes with the strictest MUST or MAY declaration taking precedence.</p>

<p>In its definition, an objectClass can identify a parent objectClass from which to inherit its attributes.  This is done using <code>SUP</code> followed by the objectClass to inherit from.  For instance, the <code>organizationalPerson</code> objectClass begins like this:</p>
<pre class="code-pre "><code langs="">objectclass ( 2.5.6.7 NAME 'organizationalPerson' SUP <span class="highlight">person</span> STRUCTURAL
 . . .
</code></pre>
<p>The objectClass following the <code>SUP</code> identifier is the parent objectClass.  The parent must share the objectClass type of the objectClass being defined (for instance <code>STRUCTURAL</code> or <code>AUXILIARY</code>).  The child objectClass automatically inherits the attributes and attribute requirements of the parent. </p>

<p>When assigning an objectClass in an entry, you only need to specify the most specific descendent of an inheritance chain to have access to the attributes all the way up.  In the last section, we used this to specify <code>inetOrgPerson</code> as the sole objectClass for our John Smith entry while still having access to the attributes defined in the <code>person</code> and <code>organizationalPerson</code> objectClasses.  The <code>inetOrgPerson</code> inheritance hierarchy looks like this:</p>
<pre class="code-pre "><code langs="">inetOrgPerson -> organizationalPerson -> person -> top
</code></pre>
<p>Almost all objectClass inheritance trees end with a special objectClass called "top".  This is an abstract objectClass whose only purpose is to require that objectClass itself be set.  It is used to indicate the top of the inheritance chain.</p>

<h3 id="attribute-inheritance">Attribute Inheritance</h3>

<p>In a similar way, attributes themselves can list a parent attribute during their definition.  The attribute will then inherit the properties that were set in the parent attribute.</p>

<p>This is often used for making more specific versions of a general attribute.  For instance, a surname is a type of name and can use all of the same methods to compare and check for equality.  It can inherit these qualities to get the general form of a "name" attribute.  In fact, the actual surname definition may contain little more than a pointer back to the parent attribute.</p>

<p>This is useful because it allows for the creation of a specific attribute that is useful for people interpreting the element, even when its general form remains unchanged.  The inheritance of the <code>surname</code> attribute we discussed here helps people distinguish between a surname and a more general name, but other than the value's meaning, there is little difference between a surname and name to the LDAP system.</p>

<h2 id="ldap-protocol-variations">LDAP Protocol Variations</h2>

<p>We mentioned at the beginning that LDAP is actually just the protocol that defines the communication interface for working with directory services.  This is generally just known as the LDAP or ldap protocol.</p>

<p>It is worth mentioning that you might see some variants on the regular format:</p>

<ul>
<li><strong>ldap://</strong>: This is the basic LDAP protocol that allows for structured access to a directory service.</li>
<li><strong>ldaps://</strong>: This variant is used to indicate LDAP over SSL/TLS.  Normal LDAP traffic is not encrypted, although most LDAP implementations support this.  This method of encrypting LDAP connections is actually deprecated and the use of STARTTLS encryption is recommended instead.  If you are operating LDAP over an insecure network, encryption is strongly recommended.</li>
<li><strong>ldapi://</strong>: This is used to indicate LDAP over an IPC.  This is often used to connect securely with a local LDAP system for administrative purposes.  It communicates over internal sockets instead of using an exposed network port.</li>
</ul>

<p>All three formats utilize the LDAP protocol, but the last two indicate additional information about how it is being used.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should have a fairly good understanding of the LDAP protocol and the way that implementations of LDAP represent data to users.  Understanding how elements of the system are related to each other and where they get their properties makes managing and using LDAP systems simpler and more predictable.</p>

    