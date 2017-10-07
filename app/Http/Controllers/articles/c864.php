<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What’s difference between char s[] and char *s in C?</h1>
				
			
			<p>Consider below two statements in C.  What is difference between two?</p>
<pre>
   char s[] = "geeksquiz";
   char *s  = "geeksquiz";
</pre>
<p>The statements ‘<strong>char s[] = "geeksquiz"</strong>‘ creates a character array which is like any other array and we can do all array operations.  The only special thing about this array is, although we have initialized it with 9 elements, its size is 10 (Compiler automatically adds ‘\0′)</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
    char s[] = "geeksquiz";
    printf("%lu", sizeof(s));
    s[0] = 'j';
    printf("\n%s", s);
    return 0;
}
</pre>
<p>Output:
</p><pre>10
jeeksquiz</pre>
<p>The statement ‘<strong>char *s  = "geeksquiz"</strong>‘ creates a string literal. The string literal is stored in  read only part of memory by most of the compilers. The C and C++ standards say that string literals have static storage duration, any attempt at modifying them gives undefined behavior.<br />
<strong>s</strong> is just a pointer and like any other pointer stores address of string literal.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
    char *s = "geeksquiz";
    printf("%lu", sizeof(s));

    // Uncommenting below line would cause undefined behaviour
    // (Caused segmentation fault on gcc)
    //  s[0] = 'j';  
    return 0;
}
</pre>
<p>Output:
</p><pre>8</pre>
<p>Running above program may generates a warning also "warning: deprecated conversion from string constant to ‘char*'".  This warning occurs because s is not a const pointer, but stores address of read only location.  The warning can be avoided by pointer to const.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
    const char *s = "geeksquiz";
    printf("%lu", sizeof(s));
    return 0;
}
</pre>
<p>This article is contributed by Abhay Rathi. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		