<link rel='stylesheet' href='digital_ocean.css'><br> <br> 
      <h3 id="introduction">Introduction</h3>

<p>If you do not have much experience working with Linux systems, you may be overwhelmed by the prospect of controlling an operating system from the command line.  In this guide, we will attempt to get you up to speed with the basics.</p>

<p>This guide will not cover everything you need to know to effectively use a Linux system.  However, it should give you a good jumping-off point for future exploration.  This guide will give you the bare minimum you need to know before moving on to other guides.</p>

<h2 id="prerequisites-and-goals">Prerequisites and Goals</h2>

<p>In order to follow along with this guide, you will need to have access to a Linux server.  If you need information about connecting to your server for the first time, you can follow <a href="https://indiareads/community/tutorials/how-to-connect-to-your-droplet-with-ssh">our guide on connecting to a Linux server using SSH</a>.</p>

<p>You will also want to have a basic understanding of how the terminal works and what Linux commands look like.  <a href="https://indiareads/community/tutorials/an-introduction-to-the-linux-terminal">This guide covers terminal basics</a>, so you should check it out if you are new to using terminals.</p>

<p>All of the material in this guide can be accomplished with a regular, non-root (non-administrative) user account.  You can learn how to configure this type of user account by following your distribution's initial server setup guide (<a href="https://indiareads/community/tutorials/initial-server-setup-with-ubuntu-14-04">Ubuntu 14.04</a>, <a href="https://indiareads/community/tutorials/initial-server-setup-with-centos-7">CentOS 7</a>).</p>

<p>When you are ready to begin, connect to your Linux server using SSH and continue below.</p>

<h2 id="navigation-and-exploration">Navigation and Exploration</h2>

<p>The most fundamental skills you need to master are moving around the filesystem and getting an idea of what is around you.  We will discuss the tools that allow you to do this in this section.</p>

<h3 id="finding-where-you-are-with-the-quot-pwd-quot-command">Finding Where You Are with the "pwd" Command</h3>

<p>When you log into your server, you are typically dropped into your user account's <strong>home directory</strong>.  A home directory is a directory set aside for your user to store files and create directories.  It is the location in the filesystem where you have full dominion.</p>

<p>To find out where your home directory is in relationship to the rest of the filesystem, you can use the <code>pwd</code> command.  This command displays the directory that we are currently in:</p>
<pre class="code-pre "><code langs="">pwd
</code></pre>
<p>You should get back some information that looks like this:</p>
<pre class="code-pre "><code langs="">/home/<span class="highlight">demo</span>
</code></pre>
<p>The home directory is named after the user account, so the above example is what the value would be if you were logged into the server with an account called <code><span class="highlight">demo</span></code>.  This directory is within a directory called <code>/home</code>, which is itself within the top-level directory, which is called "root" but represented by a single slash "/".</p>

<h3 id="looking-at-the-contents-of-directories-with-quot-ls-quot">Looking at the Contents of Directories with "ls"</h3>

<p>Now that you know how to display the directory that you are in, we can show you how to look at the contents of a directory.</p>

<p>Currently, your home directory that we saw above does not have much to see, so we will go to another, more populated directory to explore.  Type the following in your terminal to move to this directory (we will explain the details of moving directories in the next section).  Afterward, we'll use <code>pwd</code> to confirm that we successfully moved:</p>
<pre class="code-pre "><code langs="">cd /usr/share
pwd
</code></pre><pre class="code-pre "><code langs="">/usr/share
</code></pre>
<p>Now that we are in a new directory, let's look at what's inside.  To do this, we can use the <code>ls</code> command:</p>
<pre class="code-pre "><code langs="">ls
</code></pre><pre class="code-pre "><code langs="">adduser            groff                          pam-configs
applications       grub                           perl
apport             grub-gfxpayload-lists          perl5
apps               hal                            pixmaps
apt                i18n                           pkgconfig
aptitude           icons                          polkit-1
apt-xapian-index   info                           popularity-contest
. . .
</code></pre>
<p>As you can see, there are <em>many</em> items in this directory.  We can add some optional flags to the command to modify the default behavior.  For instance, to list all of the contents in an extended form, we can use the <code>-l</code> flag (for "long" output):</p>
<pre class="code-pre "><code langs="">ls -l
</code></pre><pre class="code-pre "><code langs="">total 440
drwxr-xr-x   2 root root  4096 Apr 17  2014 adduser
drwxr-xr-x   2 root root  4096 Sep 24 19:11 applications
drwxr-xr-x   6 root root  4096 Oct  9 18:16 apport
drwxr-xr-x   3 root root  4096 Apr 17  2014 apps
drwxr-xr-x   2 root root  4096 Oct  9 18:15 apt
drwxr-xr-x   2 root root  4096 Apr 17  2014 aptitude
drwxr-xr-x   4 root root  4096 Apr 17  2014 apt-xapian-index
drwxr-xr-x   2 root root  4096 Apr 17  2014 awk
. . .
</code></pre>
<p>This view gives us plenty of information, most of which looks rather unusual.  The first block describes the file type (if the first column is a "d" the item is a directory, if it is a "-", it is a normal file) and permissions.  Each subsequent column, separated by white space, describes the number of hard links, the owner, group owner, item size, last modification time, and the name of the item.  We will describe some of these at another time, but for now, just know that you can view this information with the <code>-l</code> flag of <code>ls</code>.</p>

<p>To get a listing of all files, including <em>hidden</em> files and directories, you can add the <code>-a</code> flag.  Since there are no real hidden files in the <code>/usr/share</code> directory, let's go back to our home directory and try that command.  You can get back to the home directory by typing <code>cd</code> with no arguments:</p>
<pre class="code-pre "><code langs="">cd
ls -a
</code></pre><pre class="code-pre "><code langs="">.  ..  .bash_logout  .bashrc  .profile
</code></pre>
<p>As you can see, there are three hidden files in this demonstration, along with <code>.</code> and <code>..</code>, which are special indicators.  You will find that often, configuration files are stored as hidden files, as is the case here.</p>

<p>For the dot and double dot entries, these aren't exactly directories as much as built-in methods of referring to related directories.  The single dot indicates the current directory, and the double dot indicates this directory's parent directory.  This will come in handy in the next section.</p>

<h3 id="moving-around-the-filesystem-with-quot-cd-quot">Moving Around the Filesystem with "cd"</h3>

<p>We have already made two directory moves in order to demonstrate some properties of <code>ls</code> in the last section.  Let's take a better look at the command here.</p>

<p>Begin by going back to the <code>/usr/share</code> directory by typing this:</p>
<pre class="code-pre "><code langs="">cd /usr/share
</code></pre>
<p>This is an example of changing a directory by giving an <strong>absolute path</strong>.  In Linux, every file and directory is under the top-most directory, which is called the "root" directory, but referred to by a single leading slash "/".  An absolute path indicates the location of a directory in relation to this top-level directory.  This lets us refer to directories in an unambiguous way from any place in the filesystem.  Every absolute path <strong>must</strong> begin with a slash.</p>

<p>The alternative is to use <strong>relative paths</strong>.  Relative paths refer to directories in relation to the <em>current</em> directory.  For directories close to the current directory in the hierarchy, this is usually easier and shorter.  Any directory within the current directory can be referenced by name without a leading slash.  We can change to the <code>locale</code> directory within <code>/usr/share</code> from our current location by typing:</p>
<pre class="code-pre "><code langs="">cd locale
</code></pre>
<p>We can likewise move multiple directory levels with relative paths by providing the portion of the path that comes after the current directory's path.  From here, we can get to the <code>LC_MESSAGES</code> directory within the <code>en</code> directory by typing:</p>
<pre class="code-pre "><code langs="">cd en/LC_MESSAGES
</code></pre>
<p>To go back up, travelling to the parent of the current directory, we use the special double dot indicator we talked about earlier.  For instance, we are now in the <code>/usr/share/locale/en/LC_MESSAGES</code> directory.  To move up one level, we can type:</p>
<pre class="code-pre "><code langs="">cd ..
</code></pre>
<p>This takes us to the <code>/usr/share/locale/en</code> directory.</p>

<p>A shortcut that you saw earlier that will always take you back to your home directory is to use <code>cd</code> without providing a directory:</p>
<pre class="code-pre "><code langs="">cd
pwd
</code></pre><pre class="code-pre "><code langs="">/home/<span class="highlight">demo</span>
</code></pre>
<p>To learn more about how to use these three commands, you can check out <a href="https://indiareads/community/tutorials/how-to-use-cd-pwd-and-ls-to-explore-the-file-system-on-a-linux-server">our guide on exploring the Linux filesystem</a>.</p>

<h2 id="viewing-files">Viewing Files</h2>

<p>In the last section, we learned a bit about how to navigate the filesystem.  You probably saw some files when using the <code>ls</code> command in various directories.  In this section, we'll discuss different ways that you can use to view files.  In contrast to some operating systems, Linux and other Unix-like operating systems rely on plain text files for vast portions of the system.</p>

<p>The main way that we will view files is with the <code>less</code> command.  This is what we call a "pager", because it allows us to scroll through pages of a file.  While the previous commands immediately executed and returned you to the command line, <code>less</code> is an application that will continue to run and occupy the screen until you exit.</p>

<p>We will open the <code>/etc/services</code> file, which is a configuration file that contains service information that the system knows about:</p>
<pre class="code-pre "><code langs="">less /etc/services
</code></pre>
<p>The file will be opened in <code>less</code>, allowing you to see the portion of the document that fits in the area of the terminal window:</p>
<pre class="code-pre "><code langs=""># Network services, Internet style
#
# Note that it is presently the policy of IANA to assign a single well-known
# port number for both TCP and UDP; hence, officially ports have two entries
# even if the protocol doesn't support UDP operations.
#
# Updated from http://www.iana.org/assignments/port-numbers and other
# sources like http://www.freebsd.org/cgi/cvsweb.cgi/src/etc/services .
# New ports will be added on request if they have been officially assigned
# by IANA and used in the real-world or are needed by a debian package.
# If you need a huge list of used numbers please install the nmap package.

tcpmux          1/tcp                           # TCP port service multiplexer
echo            7/tcp
. . .
</code></pre>
<p>To scroll, you can use the up and down arrow keys on your keyboard.  To page down one whole screens-worth of information, you can use either the space bar, the "Page Down" button on your keyboard, or the <code>CTRL-f</code> shortcut.</p>

<p>To scroll back up, you can use either the "Page Up" button, or the <code>CTRL-b</code> keyboard shortcut.</p>

<p>To search for some text in the document, you can type a forward slash "/" followed by the search term.  For instance, to search for "mail", we would type:</p>
<pre class="code-pre "><code langs="">/mail
</code></pre>
<p>This will search forward through the document and stop at the first result.  To get to another result, you can type the lower-case <code>n</code> key:</p>
<pre class="code-pre "><code langs="">n
</code></pre>
<p>To move backwards to the previous result, use a capital <code>N</code> instead:</p>
<pre class="code-pre "><code langs="">N
</code></pre>
<p>When you wish to exit the <code>less</code> program, you can type <code>q</code> to quit:</p>
<pre class="code-pre "><code langs="">q
</code></pre>
<p>While we focused on the <code>less</code> tool in this section, there are many other ways of viewing a file that come in handy in certain circumstances.  The <code>cat</code> command displays a file's contents and returns you to the prompt immediately.  The <code>head</code> command, by default, shows the first 10 lines of a file.  Likewise, the <code>tail</code> command shows the last 10 lines by default.  These commands display file contents in a way that is useful for "piping" to other programs.  We will discuss this concept in a future guide.</p>

<p>Feel free to see how these commands display the <code>/etc/services</code> file differently.</p>

<h2 id="file-and-directory-manipulation">File and Directory Manipulation</h2>

<p>We learned in the last section how to view a file.  In this section, we'll demonstrate how to create and manipulate files and directories.</p>

<h3 id="create-a-file-with-quot-touch-quot">Create a File with "touch"</h3>

<p>Many commands and programs can create files.  The most basic method of creating a file is with the <code>touch</code> command.  This will create an empty file using the name and location specified.</p>

<p>First, we should make sure we are in our home directory, since this is a location where we have permission to save files.  Then, we can create a file called <code>file1</code> by typing:</p>
<pre class="code-pre "><code langs="">cd
touch file1
</code></pre>
<p>Now, if we view the files in our directory, we can see our newly created file:</p>
<pre class="code-pre "><code langs="">ls
</code></pre><pre class="code-pre "><code langs="">file1
</code></pre>
<p>If we use this command on an existing file, the command simply updates the data our filesystem stores on the time when the file was last accessed and modified.  This won't have much use for us at the moment.</p>

<p>We can also create multiple files at the same time.  We can use absolute paths as well.  For instance, if our user account is called <code>demo</code>, we could type:</p>
<pre class="code-pre "><code langs="">touch /home/<span class="highlight">demo</span>/file2 /home/<span class="highlight">demo</span>/file3
ls
</code></pre><pre class="code-pre "><code langs="">file1  file2  file3
</code></pre>
<h3 id="create-a-directory-with-quot-mkdir-quot">Create a Directory with "mkdir"</h3>

<p>Similar to the <code>touch</code> command, the <code>mkdir</code> command allows us to create empty directories.</p>

<p>For instance, to create a directory within our home directory called <code>test</code>, we could type:</p>
<pre class="code-pre "><code langs="">cd
mkdir test
</code></pre>
<p>We can make a directory <em>within</em> the <code>test</code> directory called <code>example</code> by typing:</p>
<pre class="code-pre "><code langs="">mkdir test/example
</code></pre>
<p>For the above command to work, the <code>test</code> directory must already exist.  To tell <code>mkdir</code> that it should create any directories necessary to construct a given directory path, you can use the <code>-p</code> option.  This allows you to create nested directories in one step.  We can create a directory structure that looks like <code>some/other/directories</code> by typing:</p>
<pre class="code-pre "><code langs="">mkdir -p some/other/directories
</code></pre>
<p>The command will make the <code>some</code> directory first, then it will create the <code>other</code> directory inside of that.  Finally it will create the <code>directories</code> directory within those two directories.</p>

<h3 id="moving-and-renaming-files-and-directories-with-quot-mv-quot">Moving and Renaming Files and Directories with "mv"</h3>

<p>We can move a file to a new location using the <code>mv</code> command.  For instance, we can move <code>file1</code> into the <code>test</code> directory by typing:</p>
<pre class="code-pre "><code langs="">mv file1 test
</code></pre>
<p>For this command, we give all of the items that we wish to move, with the location to move them at the end.  We can move that file <em>back</em> to our home directory by using the special dot reference to refer to our current directory.  We should make sure we're in our home directory, and then execute the command:</p>
<pre class="code-pre "><code langs="">cd
mv test/file1 .
</code></pre>
<p>This may seem unintuitive at first, but the <code>mv</code> command is also used to <em>rename</em> files and directories.  In essence, moving and renaming are both just adjusting the location and name for an existing item.</p>

<p>So to rename the <code>test</code> directory to <code>testing</code>, we could type:</p>
<pre class="code-pre "><code langs="">mv test testing
</code></pre>
<p><strong>Note</strong>: It is important to realize that your Linux system will not prevent you from certain destructive actions.  If you are renaming a file and choose a name that <em>already</em> exists, the previous file will be <strong>overwritten</strong> by the file you are moving.  There is no way to recover the previous file if you accidentally overwrite it.</p>

<h3 id="copying-files-and-directories-with-quot-cp-quot">Copying Files and Directories with "cp"</h3>

<p>With the <code>mv</code> command, we could move or rename a file or directory, but we could not duplicate it.  The <code>cp</code> command can make a new copy of an existing item.</p>

<p>For instance, we can copy <code>file3</code> to a new file called <code>file4</code>:</p>
<pre class="code-pre "><code langs="">cp file3 file4
</code></pre>
<p>Unlike a <code>mv</code> operation, after which <code>file3</code> would no longer exist, we now have both <code>file3</code> and <code>file4</code>.</p>

<p><strong>Note</strong>: As with the <code>mv</code> command, it is possible to <strong>overwrite</strong> a file if you are not careful about the filename you are using as the target of the operation.  For instance, if <code>file4</code> already existed in the above example, its content would be completely replaced by the content of <code>file3</code>.</p>

<p>In order to copy directories, you must include the <code>-r</code> option to the command.  This stands for "recursive", as it copies the directory, plus all of the directory's contents.  This option is necessary with directories, regardless of whether the directory is empty.</p>

<p>For instance, to copy the <code>some</code> directory structure to a new structure called <code>again</code>, we could type:</p>
<pre class="code-pre "><code langs="">cp -r some again
</code></pre>
<p>Unlike with files, with which an existing destination would lead to an overwrite, if the target is an <em>existing directory</em>, the file or directory is copied <em>into</em> the target:</p>
<pre class="code-pre "><code langs="">cp file1 again
</code></pre>
<p>This will create a new copy of <code>file1</code> and place it inside of the <code>again</code> directory.</p>

<h3 id="removing-files-and-directories-with-quot-rm-quot-and-quot-rmdir-quot">Removing Files and Directories with "rm" and "rmdir"</h3>

<p>To delete a file, you can use the <code>rm</code> command.</p>

<p><strong>Note</strong>: Be extremely careful when using any destructive command like <code>rm</code>.  There is no "undo" command for these actions so it is possible to accidentally destroy important files permanently.</p>

<p>To remove a regular file, just pass it to the <code>rm</code> command:</p>
<pre class="code-pre "><code langs="">cd
rm file4
</code></pre>
<p>Likewise, to remove <em>empty</em> directories, we can use the <code>rmdir</code> command.  This will only succeed if there is nothing in the directory in question.  For instance, to remove the <code>example</code> directory within the <code>testing</code> directory, we can type:</p>
<pre class="code-pre "><code langs="">rmdir testing/example
</code></pre>
<p>If you wish to remove a <em>non-empty</em> directory, you will have to use the <code>rm</code> command again.  This time, you will have to pass the <code>-r</code> option, which removes all of the directory's contents recursively, plus the directory itself.</p>

<p>For instance, to remove the <code>again</code> directory and everything within it, we can type:</p>
<pre class="code-pre "><code langs="">rm -r again
</code></pre>
<p>Once again, it is worth reiterating that these are permanent actions.  Be entirely sure that the command you typed is the one that you wish to execute.</p>

<h2 id="editing-files">Editing Files</h2>

<p>Currently, we know how to manipulate files as objects, but we have not learned how to actually edit them and add content to them.</p>

<p>The <code>nano</code> command is one of the simplest command-line Linux text editors, and is a great starting point for beginners.  It operates somewhat similarly to the <code>less</code> program discussed above, in that it occupies the entire terminal for the duration of its use.</p>

<p>The <code>nano</code> editor can open existing files, or create a file.  If you decide to create a new file, you can give it a name when you call the <code>nano</code> editor, or later on, when you wish to save your content.</p>

<p>We can open the <code>file1</code> file for editing by typing:</p>
<pre class="code-pre "><code langs="">cd
nano file1
</code></pre>
<p>The <code>nano</code> application will open the file (which is currently blank).  The interface looks something like this:</p>
<pre class="code-pre "><code langs="">  GNU nano 2.2.6                 File: file1                                         








                                  [ Read 0 lines ]
^G Get Help   ^O WriteOut   ^R Read File  ^Y Prev Page  ^K Cut Text   ^C Cur Pos
^X Exit       ^J Justify    ^W Where Is   ^V Next Page  ^U UnCut Text ^T To Spell
</code></pre>
<p>Along the top, we have the name of the application and the name of the file we are editing.  In the middle, the content of the file, currently blank, is displayed.  Along the bottom, we have a number of key combinations that indicate some basic controls for the editor.  For each of these, the <code>^</code> character means the <code>CTRL</code> key.</p>

<p>To get help from within the editor, type:</p>
<pre class="code-pre "><code langs="">CTRL-G
</code></pre>
<p>When you are finished browsing the help, type <code>CTRL-X</code> to get back to your document.</p>

<p>Type in or modify any text you would like.  For this example, we'll just type these two sentences:</p>
<pre class="code-pre "><code langs="">Hello there.

Here is some text.
</code></pre>
<p>To save our work, we can type:</p>
<pre class="code-pre "><code langs="">CTRL-O
</code></pre>
<p>This is the letter "o", not a zero.  It will ask you to confirm the name of the file you wish to save to:</p>
<pre class="code-pre "><code langs="">File Name to Write: file1                                                            
^G Get Help          M-D DOS Format       M-A Append           M-B Backup File
^C Cancel            M-M Mac Format       M-P Prepend
</code></pre>
<p>As you can see, the options at the bottom have also changed.  These are contextual, meaning they will change depending on what you are trying to do.  If <code>file1</code> is still the file you wish to write to, hit "ENTER".</p>

<p>If we make some additional changes and wish to save the file and exit the program, we will see a similar prompt.  Add a new line, and then try to exit the program by typing:</p>
<pre class="code-pre "><code langs="">CTRL-X
</code></pre>
<p>If you have not saved after making your modification, you will be asked whether you wish to save the modifications you made:</p>
<pre class="code-pre "><code langs="">Save modified buffer (ANSWERING "No" WILL DESTROY CHANGES) ?                         
 Y Yes
 N No           ^C Cancel
</code></pre>
<p>You can type "Y" to save your changes, "N" to discard your changes and exit, or "CTRL-C" to cancel the exit operation.  If you choose to save, you will be given the same file prompt that you received before, confirming that you want to save the changes to the same file.  Press ENTER to save the file and exit the editor.</p>

<p>You can see the contents of the file you created using either the <code>cat</code> program to display the contents, or the <code>less</code> program to open the file for viewing.  After viewing with <code>less</code>, remember that you should hit <code>q</code> to get back to the terminal.</p>
<pre class="code-pre "><code langs="">less file1
</code></pre><pre class="code-pre "><code langs="">Hello there.

Here is some text.

Another line.
</code></pre>
<p>Another editor that you may see referenced in certain guides is <code>vim</code> or <code>vi</code>.  This is a more advanced editor that is very powerful, but comes with a very steep learning curve.  If you are ever told to use <code>vim</code> or <code>vi</code>, feel free to use <code>nano</code> instead.  If you wish to learn how to use <code>vim</code>, read our <a href="https://indiareads/community/tutorials/installing-and-using-the-vim-text-editor-on-a-cloud-server">guide to getting started with vim</a>.</p>

<h2 id="conclusion">Conclusion</h2>

<p>By now, you should have a basic understanding of how to get around your Linux server and how to see the files and directories available.  You should also know some basic file manipulation commands that will allow you to view, copy, move, or delete files.  Finally, you should be comfortable with some basic editing using the <code>nano</code> text editor.</p>

<p>With these few skills, you should be able to continue on with other guides and learn how to get the most out of your server.  In our next guide, we will discuss <a href="https://indiareads/community/tutorials/an-introduction-to-linux-permissions">how to view and understand Linux permissions</a>.</p>

    