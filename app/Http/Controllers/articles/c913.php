<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What is Memory Leak?  How can we avoid?</h1>
				
			
			<p>Memory leak occurs when programmers create a memory in heap and forget to delete it.<br />
Memory leaks are particularly serious issues for programs like daemons and servers which by definition never terminate.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Function with memory leak */
#include <stdlib.h>

void f()
{
   int *ptr = (int *) malloc(sizeof(int));

   /* Do some work */

   return; /* Return without freeing ptr*/
}
</pre>
<p>To avoid memory leaks, memory allocated on heap should always be freed when no longer needed.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Function without memory leak */
#include <stdlib.h>;

void f()
{
   int *ptr = (int *) malloc(sizeof(int));

   /* Do some work */

   free(ptr);
   return;
}
</pre>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		