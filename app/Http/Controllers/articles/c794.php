<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Is it fine to write “void main()” or “main()” in C/C++?</h1>
				
			
			<p>The definition</p>
<pre class="brush: cpp; title: ; notranslate" title="">
	void main() { /* ... */ }</pre>
<p>is not and never has been C++, nor has it even been C. <span id="more-125231"></span>See the ISO C++ standard 3.6.1[2] or the ISO C standard 5.1.2.2.1. A conforming implementation accepts</p>
<pre class="brush: cpp; title: ; notranslate" title="">
	int main() { /* ... */ }</pre>
<p>and</p>
<pre class="brush: cpp; title: ; notranslate" title="">
	int main(int argc, char* argv[]) { /* ... */ }</pre>
<p>A conforming implementation may provide more versions of main(), but they must all have return type int. The int returned by main() is a way for a program to return a value to "the system" that invokes it. On systems that doesn't provide such a facility the return value is ignored, but that doesn't make "void main()" legal C++ or legal C. <strong><em>Even if your compiler accepts "void main()" avoid it, or risk being considered ignorant by C and C++ programmers.<br />
In C++, main() need not contain an explicit return statement. In that case, the value returned is 0, meaning successful execution.</em></strong> For example:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <iostream>
int main()
{
    std::cout << "This program returns the integer value 0\n";
}</pre>
<p>Note also that neither ISO C++ nor C99 allows you to leave the type out of a declaration. That is, in contrast to C89 and ARM C++ ,"int" is not assumed where a type is missing in a declaration. Consequently:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <iostream>

main() { /* ... */ } </pre>
<p>is an error because the return type of main() is missing.</p>
<p>Source: <a target="_blank">http://www.stroustrup.com/bs_faq2.html#void-main</a></p>
<p>To summarize above, it is never a good idea to use "void main()" or just "main()" as it doesn't confirm standards.  It may be allowed by some compilers though.</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		