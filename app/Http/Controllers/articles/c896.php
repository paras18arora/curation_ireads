<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">void pointer in C</h1>
				
			
			<p>A void pointer is a pointer that has no associated data type with it. A void pointer can hold address of any type and can be typcasted to any type.<span id="more-12867"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int a = 10;
char b = 'x';

void *p = &a;  // void pointer holds address of int 'a'
p = &b; // void pointer holds address of char 'b'
</pre>
<p><strong>Advantages of void pointers:</strong><br />
<strong>1) </strong>malloc() and calloc() return void * type and this allows these functions to be used to allocate memory of any data type (just because of void *) </p>
<pre class="brush: plain; title: ; notranslate" title="">
int main(void)
{
    // Note that malloc() returns void * which can be 
    // typecasted to any type like int *, char *, ..
    int *x = malloc(sizeof(int) * n);
}
</pre>
<p>Note that the above program compiles in C, but doesn't compile in C++.  In C++, we must explicitly typecast return value of malloc to (int *).</p>
<p><strong>2)</strong> void pointers in C are used to implement generic functions in C.  For example <a target="_blank">compare function which is used in qsort()</a>.  </p>
<p><strong>Some Interesting Facts:</strong><br />
<strong>1)</strong>  void pointers cannot be dereferenced.  For example the following program doesn't compile.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    int a = 10;
    void *ptr = &a;
    printf("%d", *ptr);
    return 0;
}
</pre>
<p>Output: </p>
<pre>Compiler Error: 'void*' is not a pointer-to-object type </pre>
<p>The following program compiles and runs fine.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    int a = 10;
    void *ptr = &a;
    printf("%d", *(int *)ptr);
    return 0;
}
</pre>
<p>Output:
</p><pre>10</pre>
<p><strong>2) </strong> The <a>C standard</a> doesn't allow pointer arithmetic with void pointers. However,  in GNU C it is allowed  by considering the size of void is 1.  For example the following program compiles and runs fine in gcc.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    int a[2] = {1, 2};
    void *ptr = &a;
    ptr = ptr + sizeof(int);
    printf("%d", *(int *)ptr);
    return 0;
}
</pre>
<p>Output:
</p><pre>2</pre>
<p>Note that the above program may not work in other compilers.</p>
<p><strong>References:</strong><br />
<a target="_blank">http://stackoverflow.com/questions/20967868/should-the-compiler-warn-on-pointer-arithmetic-with-a-void-pointer</a><br />
<a target="_blank">http://stackoverflow.com/questions/692564/concept-of-void-pointer-in-c-programming</a></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		