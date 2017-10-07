<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Use of realloc()</h1>
				
			
			<p>Size of dynamically allocated memory can be changed by using realloc(). </p>
<p>As per the C99 standard: <span id="more-9574"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
  void *realloc(void *ptr, size_t size);
</pre>
<p><em>realloc deallocates the old object pointed to by ptr and returns a pointer to a new object that has the size specified by size. The contents of the new object is identical to that of the old object prior to deallocation, up to the lesser of the new and old sizes. Any bytes in the new object beyond the size of the old object have indeterminate values.<br />
</em></p>
<p>The point to note is that <strong>realloc() should only be used for dynamically allocated memory</strong>. If the memory is not dynamically allocated, then behavior is undefined.<br />
For example, program 1 demonstrates incorrect use of realloc() and program 2 demonstrates correct use of realloc().</p>
<p><strong>Program 1:</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>
int main()
{
   int arr[2], i;
   int *ptr = arr;
   int *ptr_new;
   
   arr[0] = 10; 
   arr[1] = 20;      
   
   // incorrect use of new_ptr: undefined behaviour
   ptr_new = (int *)realloc(ptr, sizeof(int)*3);
   *(ptr_new + 2) = 30;
   
   for(i = 0; i < 3; i++)
     printf("%d ", *(ptr_new + i));

   getchar();
   return 0;
}
</pre>
<p>Output:<br />
Undefined Behavior</p>
<p><strong><br />
Program 2:</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>
int main()
{
   int *ptr = (int *)malloc(sizeof(int)*2);
   int i;
   int *ptr_new;
   
   *ptr = 10; 
   *(ptr + 1) = 20;
   
   ptr_new = (int *)realloc(ptr, sizeof(int)*3);
   *(ptr_new + 2) = 30;
   for(i = 0; i < 3; i++)
       printf("%d ", *(ptr_new + i));

   getchar();
   return 0;
}
</pre>
<p>Output:<br />
<em>10 20 30</em></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		