<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Difference between pointer and array in C?</h1>
				
			
			<p>Pointers are used for storing address of dynamically allocated arrays and for arrays which are passed as arguments to functions. <span id="more-126595"></span>In other contexts, arrays and pointer are two different things, see the following programs to justify this statement. </p>
<p><em>Behavior of sizeof operator</em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
// 1st program to show that array and pointers are different
#include <stdio.h>
int main()
{
   int arr[] = {10, 20, 30, 40, 50, 60};
   int *ptr = arr;
   
   // sizof(int) * (number of element in arr[]) is printed
   printf("Size of arr[] %d\n", sizeof(arr));

   // sizeof a pointer is printed which is same for all type 
   // of pointers (char *, void *, etc)
   printf("Size of ptr %d", sizeof(ptr));
   return 0;
}
</pre>
<p>Output:
</p><pre>Size of arr[] 24
Size of ptr 4</pre>
<p><em>Assigning any address to an array variable is not allowed.  </em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
// IInd program to show that array and pointers are different
#include <stdio.h>
int main()
{
   int arr[] = {10, 20}, x = 10;
   int *ptr = &x; // This is fine
   arr = &x;  // Compiler Error
   return 0;
}</pre>
<p>Output:
</p><pre> Compiler Error: incompatible types when assigning to 
              type 'int[2]' from type 'int *' </pre>
<p>See the <a target="_blank">previous post</a> on this topic for more differences. </p>
<p><em><strong>Although array and pointer are different things, following properties of array make them look similar.</strong></em></p>
<p><strong>1)</strong> <em>Array name gives address of first element of array.</em><br />
Consider the following program for example.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
   int arr[] = {10, 20, 30, 40, 50, 60};
   int *ptr = arr;  // Assigns address of array to ptr
   printf("Value of first element is %d", *ptr)
   return 0;
}
</pre>
<p>Output:
</p><pre>Value of first element is 10</pre>
<p> <br />
<strong>2)</strong> <em>Array members are accessed using pointer arithmetic.</em><br />
Compiler uses pointer arithmetic to access array element.  For example, an expression like "arr[i]" is treated as *(arr + i) by the compiler.  That is why the expressions like *(arr + i) work for array arr, and expressions like ptr[i] also work for pointer ptr.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
   int arr[] = {10, 20, 30, 40, 50, 60};
   int *ptr = arr;
   printf("arr[2] = %d\n", arr[2]);
   printf("*(ptr + 2) = %d\n", *(arr + 2));
   printf("ptr[2] = %d\n", ptr[2]);
   printf("*(ptr + 2) = %d\n", *(ptr + 2));
   return 0;
}
</pre>
<p>Output:
</p><pre>arr[2] = 30
*(ptr + 2) = 30
ptr[2] = 30
*(ptr + 2) = 30 </pre>
<p> <br />
<strong>3)</strong> <em>Array parameters are always passed as pointers, even when we use square brackets.</em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int fun(int ptr[])
{
   int x = 10;

   // size of a pointer is printed
   printf("sizeof(ptr) = %d\n", sizeof(ptr));

   // This allowed because ptr is a pointer, not array
   ptr = &x;

   printf("*ptr = %d ", *ptr);

   return 0;
}
int main()
{
   int arr[] = {10, 20, 30, 40, 50, 60};
   fun(arr);
   return 0;
}
</pre>
<p>Output:
</p><pre>sizeof(ptr) = 4
*ptr = 10</pre>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		