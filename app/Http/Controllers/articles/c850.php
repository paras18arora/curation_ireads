<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">The OFFSETOF() macro</h1>
				
			
			<p>We know that the elements in a structure will be stored in sequential order of their declaration.<span id="more-9324"></span></p>
<p>How to extract the displacement of an element in a structure? We can make use of <a target="_blank">offsetof</a> macro.</p>
<p>Usually we call structure and union types (or <em>classes with trivial constructors</em>) as <em>plain old data</em> (POD) types, which will be used to <em>aggregate other data types</em>. The following non-standard macro can be used to get the displacement of an element in bytes from the base address of the structure variable.</p>
<pre><strong>#define OFFSETOF(TYPE, ELEMENT) ((size_t)&(((TYPE *)0)->ELEMENT))</strong></pre>
<p>Zero is casted to type of structure and required element's address is accessed, which is casted to <em>size_t</em>. As per standard <em>size_t</em> is of type <em>unsigned int</em>. The overall expression results in the number of bytes after which the ELEMENT being placed in the structure.</p>
<p>For example, the following code returns 16 bytes (padding is considered on 32 bit machine) as displacement of the character variable <em>c</em> in the structure Pod.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

#define OFFSETOF(TYPE, ELEMENT) ((size_t)&(((TYPE *)0)->ELEMENT))

typedef struct PodTag
{
   int     i;
   double  d;
   char    c;
} PodType;

int main()
{
   printf("%d", OFFSETOF(PodType, c) );
   
   getchar();
   return 0;
}
</pre>
<p>In the above code, the following expression will return the displacement of element <em>c</em> in the structure <em>PodType</em>.</p>
<pre><strong>OFFSETOF(PodType, c);</strong></pre>
<p>After preprocessing stage the above macro expands to</p>
<pre class="brush: cpp; title: ; notranslate" title="">
((size_t)&(((PodType *)0)->c))
</pre>
<p>Since we are considering 0 as address of the structure variable, c will be placed after 16 bytes of its base address i.e. 0x00 + 0x10. Applying & on the structure element (in this case it is c) returns the address of the element which is 0x10. Casting the address to <em>unsigned int</em> (size_t) results in number of bytes the element is placed in the structure.</p>
<p><strong>Note:</strong> We may consider the address operator & is redundant. Without address operator in macro, the code de-references the element of structure placed at NULL address. It causes an access violation exception (segmentation fault) at runtime.</p>
<p><em>Note that there are other ways to implement offsetof macro according to compiler behaviour. The ultimate goal is to extract displacement of the element. </em><em><strong>We will see practical usage of offsetof macro in liked lists to connect similar objects (for example thread pool) in another article.</strong></em></p>
<p>Article compiled by <strong>Venki</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>
<p>References:</p>
<p>1. <a target="_blank">Linux Kernel code</a>.</p>
<p>2. <a>http://msdn.microsoft.com/en-us/library/dz4y9b9a.aspx</a></p>
<p>3. <a target="_blank">GNU C/C++ Compiler Documentation</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		