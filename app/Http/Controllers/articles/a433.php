<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>Linux is a multi-user OS that is based on the Unix concepts of <em>file ownership</em> and <em>permissions</em> to provide security, at the file system level. If you are planning improving your Linux skills, it is essential that have a decent understanding of how ownership and permissions work. There are many intricacies when dealing with file ownership and permissions, but we will try our best to distill the concepts down to the details that are necessary for a foundational understanding of how they work.</p>

<p>In this tutorial, we will cover how to view and understand Linux ownership and permissions. If you are looking for a tutorial on how to modify permissions, check out this guide: <a href="https://indiareads/community/tutorials/linux-permissions-basics-and-how-to-use-umask-on-a-vps#types-of-permissions">Linux Permissions Basics and How to Use Umask on a VPS</a></p>

<h2 id="prerequisites">Prerequisites</h2>

<p>Make sure you understand the concepts covered in the prior tutorials in this series:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-the-linux-terminal">An Introduction to the Linux Terminal</a></li>
<li><a href="https://indiareads/community/tutorials/basic-linux-navigation-and-file-management">Basic Linux Navigation and File Management</a></li>
</ul>

<p>Access to a Linux server is not strictly necessary to follow this tutorial, but having one to use will let you get some first-hand experience. If you want to set one up, <a href="https://indiareads/community/tutorials/how-to-create-your-first-digitalocean-droplet-virtual-server">check out this link</a> for help.</p>

<h2 id="about-users">About Users</h2>

<p>As mentioned in the introduction, Linux is a multi-user system. We must understand the basics of Linux <em>users</em> and <em>groups</em> before we can talk about ownership and permissions, because they are the entities that the ownership and permissions apply to. Let's get started with the basics of what users are.</p>

<p>In Linux, there are two types of users: <em>system users</em> and <em>regular users</em>. Traditionally, system users are used to run non-interactive or background processes on a system, while regular users used for logging in and running processes interactively. When you first log in to a Linux system, you may notice that it starts out with many system users that run the services that the OS depends on--this is completely normal.</p>

<p>An easy way to view all of the users on a system is to look at the contents of the <code>/etc/passwd</code> file. Each line in this file contains information about a single user, starting with its <em>user name</em> (the name before the first <code>:</code>). Print the <code>passwd</code> file with this command:</p>
<pre class="code-pre "><code langs="">cat /etc/passwd
</code></pre>
<h3 id="superuser">Superuser</h3>

<p>In addition to the two user types, there is the <em>superuser</em>, or <em>root</em> user, that has the ability to override any file ownership and permission restrictions. In practice, this means that the superuser has the rights to access anything on its own server. This user is used to make system-wide changes, and must be kept secure.</p>

<p>It is also possible to configure other user accounts with the ability to assume "superuser rights". In fact, creating a normal user that has <code>sudo</code> privileges for system administration tasks is considered to be best practice.</p>

<h2 id="about-groups">About Groups</h2>

<p>Groups are collections of zero or more users. A user belongs to a default group, and can also be a member of any of the other groups on a server.</p>

<p>An easy way to view all the groups and their members is to look in the <code>/etc/group</code> file on a server. We won't cover group management in this article, but you can run this command if you are curious about your groups:</p>
<pre class="code-pre "><code langs="">cat /etc/group
</code></pre>
<p>Now that you know what users and groups are, let's talk about file ownership and permissions!</p>

<h2 id="viewing-ownership-and-permissions">Viewing Ownership and Permissions</h2>

<p>In Linux, each and every file is owned by a single user and a single group, and has its own access permissions. Let's look at how to view the ownership and permissions of a file.</p>

<p>The most common way to view the permissions of a file is to use <code>ls</code> with the long listing option, e.g. <code>ls -l myfile</code>. If you want to view the permissions of all of the files in your current directory, run the command without an argument, like this:</p>
<pre class="code-pre "><code langs="">ls -l
</code></pre>
<p><strong>Hint:</strong> If you are in an empty home directory, and you haven't created any files to view yet, you can follow along by listing the contents of the <code>/etc</code> directory by running this command: <code>ls -l /etc</code></p>

<p>Here is an example screenshot of what the output might look like, with labels of each column of output:</p>

<p><img src="https://assets.digitalocean.com/articles/linux_basics/ls-l.png" alt="ls -l" /></p>

<p>Note that each file's mode (which contains permissions), owner, group, and name are listed. Aside from the <em>Mode</em> column, this listing is fairly easy to understand. To help explain what all of those letters and hyphens mean, let's break down the <em>Mode</em> column into its components.</p>

<h2 id="understanding-mode">Understanding Mode</h2>

<p>To help explain what all the groupings and letters mean, take a look at this closeup of the <em>mode</em> of the first file in the example above:</p>

<p><img src="https://assets.digitalocean.com/articles/linux_basics/mode.png" alt="Mode and permissions breakdown" /></p>

<h3 id="file-type">File Type</h3>

<p>In Linux, there are two basic types of files: <em>normal</em> and <em>special</em>. The file type is indicated by the first character of the <em>mode</em> of a file--in this guide, we refer to this as the <em>file type field</em>.</p>

<p>Normal files can be identified by files with a hyphen (<code>-</code>) in their file type fields. Normal files are just plain files that can contain data. They are called normal, or regular, files to distinguish them from special files. </p>

<p>Special files can be identified by files that have a non-hyphen character, such as a letter, in their file type fields, and are handled by the OS differently than normal files. The character that appears in the file type field indicates the kind of special file a particular file is. For example, a directory, which is the most common kind of special file, is identified by the <code>d</code> character that appears in its file type field (like in the previous screenshot). There are several other kinds of special files but they are not essential what we are learning here.</p>

<h3 id="permissions-classes">Permissions Classes</h3>

<p>From the diagram, we know that <em>Mode</em> column indicates the file type, followed by three triads, or classes, of permissions: user (owner), group, and other. The order of the classes is consistent across all Linux distributions.</p>

<p>Let's look at which users belong to each permissions class:</p>

<ul>
<li><strong>User</strong>: The <em>owner</em> of a file belongs to this class</li>
<li><strong>Group</strong>: The members of the file's group belong to this class</li>
<li><strong>Other</strong>: Any users that are not part of the <em>user</em> or <em>group</em> classes belong to this class.</li>
</ul>

<h3 id="reading-symbolic-permissions">Reading Symbolic Permissions</h3>

<p>The next thing to pay attention to are the sets of three characters, or triads, as they denote the permissions, in symbolic form, that each class has for a given file. </p>

<p>In each triad, read, write, and execute permissions are represented in the following way:</p>

<ul>
<li><strong>Read</strong>: Indicated by an <code>r</code> in the first position</li>
<li><strong>Write</strong>: Indicated by a <code>w</code> in the second position</li>
<li><strong>Execute</strong>: Indicated by an <code>x</code> in the third position. In some special cases, there may be a different character here</li>
</ul>

<p>A hyphen (<code>-</code>) in the place of one of these characters indicates that the respective permission is not available for the respective class. For example, if the <em>group</em> triad for a file is <code>r--</code>, the file is "read-only" to the group that is associated with the file.</p>

<h2 id="understanding-read-write-execute">Understanding Read, Write, Execute</h2>

<p>Now that you know how to read which permissions of a file, you probably want to know what each of the permissions actually allow users to do. We will explain each permission individually, but keep in mind that they are often used in combination with each other to allow for meaningful access to files and directories.</p>

<p>Here is a quick breakdown of the access that the three basic permission types grant a user.</p>

<h3 id="read">Read</h3>

<p>For a normal file, read permission allows a user to view the contents of the file.</p>

<p>For a directory, read permission allows a user to view the names of the file in the directory.</p>

<h3 id="write">Write</h3>

<p>For a normal file, write permission allows a user to modify and delete the file.</p>

<p>For a directory, write permission allows a user to delete the directory, modify its contents (create, delete, and rename files in it), and modify the contents of files that the user can read.</p>

<h3 id="execute">Execute</h3>

<p>For a normal file, execute permission allows a user to execute a file (the user must also have read permission). As such, execute permissions must be set for executable programs and shell scripts before a user can run them.</p>

<p>For a directory, execute permission allows a user to access, or traverse, into (i.e. <code>cd</code>) and access metadata about files in the directory (the information that is listed in an <code>ls -l</code>).</p>

<h2 id="examples-of-modes-and-permissions">Examples of Modes (and Permissions)</h2>

<p>Now that know  how to read the mode of a file, and understand the meaning of each permission, we will present a few examples of common modes, with brief explanations, to bring the concepts together.</p>

<ul>
<li><code>-rw-------</code>: A file that is only accessible by its owner</li>
<li><code>-rwxr-xr-x</code>: A file that is executable by every user on the system. A "world-executable" file</li>
<li><code>-rw-rw-rw-</code>: A file that is open to modification by every user on the system. A "world-writable" file</li>
<li><code>drwxr-xr-x</code>: A directory that every user on the system can read and access</li>
<li><code>drwxrwx---</code>: A directory that is modifiable (including its contents) by its owner and group</li>
<li><code>drwxr-x---</code>: A directory that is accessible by its group</li>
</ul>

<p>As you may have noticed, the owner of a file usually enjoys the most permissions, when compared to the other two classes. Typically, you will see that the <em>group</em> and <em>other</em> classes only have a subset of the owner's permissions (equivalent or less). This makes sense because files should only be accessible to users who need access to them for a particular reason.</p>

<p>Another thing to note is that even though many permissions combinations are possible, only certain ones make sense in most situations. For example, <em>write</em> or <em>execute</em> access is almost always accompanied by <em>read</em> access, since it's hard to modify, and impossible to execute, something you can't read.</p>

<h2 id="modifying-ownership-and-permissions">Modifying Ownership and Permissions</h2>

<p>To keep this tutorial simple, we will not cover how to modify file ownership and permissions here. To learn how to use <code>chown</code>, <code>chgrp</code>, and <code>chmod</code> to accomplish these tasks, refer to this guide: <a href="https://indiareads/community/tutorials/linux-permissions-basics-and-how-to-use-umask-on-a-vps#types-of-permissions">Linux Permissions Basics and How to Use Umask on a VPS</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>You should now have a good understanding of how ownership and permissions work in Linux. If you would like to learn more about Linux basics, it is highly recommended that you read the next tutorial in this series:</p>

<ul>
<li><a href="https://indiareads/community/tutorials/an-introduction-to-linux-i-o-redirection">An Introduction to Linux I/O Redirection</a></li>
</ul>

    