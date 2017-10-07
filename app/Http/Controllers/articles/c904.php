<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Struct Hack</h1>
				
			
			<p>What will be the size of following structure?<span id="more-22677"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct employee
{
    int 	emp_id;
    int 	name_len;
    char	name[0];
};
</pre>
<p>4 + 4 + 0 = 8 bytes.</p>
<p>And what about size of "name[0]".  In gcc, when we create an array of zero length, it is considered as array of incomplete type that's why gcc reports its size as "0" bytes. This technique is known as "Stuct Hack". When we create array of zero length inside structure, it must be (and only) last member of structure. Shortly we will see how to use it.<br />
"Struct Hack" technique is used to create variable length member in a structure. In the above structure, string length of "name" is not fixed, so we can use "name" as variable length array.</p>
<p>Let us see below memory allocation.
</p><pre>struct employee *e = malloc(sizeof(*e) + sizeof(char) * 128); </pre>
<p>is equivalent to</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct employee
{
	int 	emp_id;
	int		name_len;
	char	name[128]; /* character array of size 128 */
};
</pre>
<p>And below memory allocation
</p><pre>struct employee *e = malloc(sizeof(*e) + sizeof(char) * 1024); </pre>
<p>is equivalent to</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct employee
{
	int		emp_id;
	int		name_len;
	char	name[1024]; /* character array of size 1024 */
};
</pre>
<p>Note: since name is character array, in malloc instead of "sizeof(char) * 128″, we can use "128" directly.  sizeof is used to avoid confusion.</p>
<p>Now we can use "name" same as pointer. e.g.
</p><pre>e->emp_id 	= 100;
e->name_len	= strlen("Geeks For Geeks");
strncpy(e->name, "Geeks For Geeks", e->name_len);</pre>
<p>When we allocate memory as given above, compiler will allocate memory to store "emp_id" and "name_len" plus contiguous memory to store "name". When we use this technique, gcc guaranties that, "name" will get contiguous memory.<br />
Obviously there are other ways to solve problem, one is we can use character pointer. But there is no guarantee that character pointer will get contiguous memory, and we can take advantage of this contiguous memory. For example, by using this technique, we can allocate and deallocate memory by using single malloc and free call (because memory is contagious). Other advantage of this is, suppose if we want to write data, we can write whole data by using single "write()" call. e.g.
</p><pre>
write(fd, e, sizeof(*e) + name_len); /* write emp_id + name_len + name */ </pre>
<p>If we use character pointer, then we need 2 write calls to write data. e.g.
</p><pre>write(fd, e, sizeof(*e)); 		/* write emp_id + name_len */
write(fd, e->name, e->name_len);	/* write name */</pre>
<p>Note: In C99, there is feature called "flexible array members", which works same as "Struct Hack"</p>
<p>This article is compiled by <strong>Narendra Kangralkar</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		