<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How to deallocate memory without using free() in C?</h1>
				
			
			<p><strong>Question:</strong> How to deallocate dynamically allocate memory without using "free()" function. <span id="more-16600"></span></p>
<p><strong>Solution:</strong> Standard library function <a>realloc()</a> can be used to deallocate previously allocated memory. Below is function declaration of "realloc()" from "stdlib.h"</p>
<pre class="brush: cpp; title: ; notranslate" title="">
void *realloc(void *ptr, size_t size);
</pre>
<p>If "size" is zero, then call to realloc is equivalent to "free(ptr)". And if "ptr" is NULL and size is non-zero then call to realloc is equivalent to "malloc(size)".</p>
<p>Let us check with simple example.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* code with memory leak */
#include <stdio.h>
#include <stdlib.h>

int main(void)
{
    int *ptr = (int*)malloc(10);

    return 0;
}
</pre>
<p>Check the leak summary with valgrind tool. It shows memory leak of 10 bytes, which is highlighed in red colour.</p>
<pre>
  [narendra@ubuntu]$ valgrind –leak-check=full ./free
  ==1238== LEAK SUMMARY:
  <font color="red">==1238==    definitely lost: 10 bytes in 1 blocks.</font>
  ==1238==      possibly lost: 0 bytes in 0 blocks.
  ==1238==    still reachable: 0 bytes in 0 blocks.
  ==1238==         suppressed: 0 bytes in 0 blocks.
[narendra@ubuntu]$
</pre>
<p>Let us modify the above code.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>

int main(void)
{
    int *ptr = (int*) malloc(10);

    /* we are calling realloc with size = 0 */
    realloc(ptr, 0);
   

    return 0;
}
</pre>
<p>Check the valgrind's output. It shows no memory leaks are possible, highlighted in red color.</p>
<pre>
  [narendra@ubuntu]$ valgrind –leak-check=full ./a.out
  ==1435== ERROR SUMMARY: 0 errors from 0 contexts (suppressed: 11 from 1)
  ==1435== malloc/free: in use at exit: 0 bytes in 0 blocks.
  ==1435== malloc/free: 1 allocs, 1 frees, 10 bytes allocated.
  ==1435== For counts of detected errors, rerun with: -v
  <font color="red">==1435== All heap blocks were freed — no leaks are possible.</font>
  [narendra@ubuntu]$
</pre>
<p>This article is compiled by "Narendra Kangralkar" and reviewed by GeeksforGeeks team. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		