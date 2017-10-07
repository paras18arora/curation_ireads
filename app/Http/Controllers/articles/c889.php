<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Implicit return type int in C</h1>
				
			
			<p>Predict the output of following C program.<span id="more-125702"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
fun(int x)
{
    return x*x;
}
int main(void)
{
    printf("%d", fun(10));
    return 0;
}</pre>
<p>Output: 100</p>
<p>The important thing to note is, there is no return type for fun(), the program still compiles and runs fine in most of the C compilers. In C, if we do not specify a return type, compiler assumes an implicit return type as int. However, C99 standard doesn't allow return type to be omitted even if return type is int. This was allowed in older C standard C89.</p>
<p>In C++, the above program is not valid except few old C++ compilers like Turbo C++. Every function should specify the return type in C++.</p>
<p>This article is contributed by <strong>Pravasi Meet</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		