<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">C Programming Language Standard</h1>
				
			
			<p>The idea of this article is to introduce C standard. </p>
<p><strong>What to do when a C program produces different results in two different compilers?</strong><br />
For example, consider the following simple C program.<span id="more-125385"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
   void main() {  }
</pre>
<p>The above program fails in gcc as the return type of main is void, but it compiles in Turbo C.  How do we decide whether it is a legitimate C program or not? </p>
<p>Consider the following program as another example.  It produces different results in different compilers.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
    int i = 1;
    printf("%d %d %d\n", i++, i++, i);
    return 0;
}
</pre>
<pre>
2 1 3 - using g++ 4.2.1 on Linux.i686
1 2 3 - using SunStudio C++ 5.9 on Linux.i686
2 1 3 - using g++ 4.2.1 on SunOS.x86pc
1 2 3 - using SunStudio C++ 5.9 on SunOS.x86pc
1 2 3 - using g++ 4.2.1 on SunOS.sun4u
1 2 3 - using SunStudio C++ 5.9 on SunOS.sun4u</pre>
<p>Source: <a target="_blank">Stackoverflow</a></p>
<p>Which compiler is right?</p>
<p>The answer to all such questions is C standard.  In all such cases we need to see what C standard says about such programs.</p>
<p><strong>What is C standard?</strong><br />
The latest C standard is <a>ISO/IEC 9899:2011</a>, also known as <a target="_blank">C11 </a>as the final draft was published in 2011.   Before C11, there was <a target="_blank">C99</a>. The C11 final draft is available <a target="_blank">here</a>.  See <a target="_blank">this </a>for complete history of C standards.</p>
<p><strong>Can we know behavior of all programs from C standard?</strong><br />
C standard leaves some behavior of many C constructs as <a target="_blank">undefined </a>and some as <a target="_blank">unspecified </a>to simplify the specification and allow some flexibility in implementation.  For example, in C the use of any automatic variable before it has been initialized yields undefined behavior  and order of evaluations of subexpressions is unspecified. This specifically frees the compiler to do whatever is easiest or most efficient, should such a program be submitted.</p>
<p><strong>So what is the conclusion about above two examples?</strong><br />
Let us consider the first example which is "void main() {}", the standard says following about prototype of main().</p>
<pre>
The function called at program startup is named main. The implementation 
declares no prototype for this function. It shall be defined with a return 
type of int and with no parameters:
       int main(void) { /* ... */ }
or with two parameters (referred to here as argc and argv, though any names 
may be used, as they are local to the function in which they are declared):
       int main(int argc, char *argv[]) { /* ... */ }
or equivalent;10) or in some other implementation-defined manner.
</pre>
<p>So the return type void doesn't follow the standard and it's something allowed by certain compilers.</p>
<p>Let us talk about second example.  Note the following statement in C standard is listed under unspecified behavior.</p>
<pre>The order in which the function designator, arguments, and 
subexpressions within the arguments are evaluated in a function 
call (6.5.2.2). </pre>
<p><strong>What to do with programs whose behavior is undefined or unspecified in standard?</strong><br />
As a programmer, it is never a good idea to use programming constructs whose behaviour is undefined or unspecified, such programs should always be discouraged. The output of such programs may change with compiler and/or machine.</p>
<p>This article is contributed by <strong>Abhay Rathi</strong>.  Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		