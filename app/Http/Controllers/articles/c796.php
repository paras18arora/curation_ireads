<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Interesting Facts about Macros and Preprocessors in C</h1>
				
			
			<p>In a C program, all lines that start with <strong>#</strong> are processed by preprocessor which is a special program invoked by the compiler.<span id="more-126146"></span> In a very basic term, preprocessor takes a C program and produces another C program without any <strong>#</strong>.</p>
<p>Following are some interesting facts about preprocessors in C.</p>
<p><strong>1)</strong> When we use <em><strong>include</strong> </em>directive,  the contents of included header file (after preprocessing) are copied to the current file.<br />
Angular brackets <strong><</strong> and <strong>></strong> instruct the preprocessor to look in the standard folder where all header files are held.  Double quotes <strong>"</strong> and <strong>"</strong> instruct the preprocessor to look into the current folder and if the file is not present in current folder, then in standard folder of all header files.<br />
<strong>2) </strong>When we use<em><strong> define </strong></em>for a constant, the preprocessor produces a C program where the defined constant is searched and matching tokens are replaced with the given expression. For example in the following program <em>max </em>is defined as 100.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
#define max 100
int main()
{
    printf("max is %d", max);
    return 0;
}
// Output: max is 100
// Note that the max inside "" is not replaced
</pre>
<p><strong>3)</strong> The macros can take function like arguments, the arguments are not checked for data type. For example, the following macro INCREMENT(x) can be used for x of any data type.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#define INCREMENT(x) ++x
int main()
{
    char *ptr = "GeeksQuiz";
    int x = 10;
    printf("%s  ", INCREMENT(ptr));
    printf("%d", INCREMENT(x));
    return 0;
}
// Output: eeksQuiz 11</pre>
<p><strong>4)</strong> The macro arguments are not evaluated before macro expansion. For example consider the following program</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#define MULTIPLY(a, b) a*b
int main()
{
    // The macro is expended as 2 + 3 * 3 + 5, not as 5*8
    printf("%d", MULTIPLY(2+3, 3+5));
    return 0;
}
// Output: 16</pre>
<p><strong>5)</strong> The tokens passed to macros can be concatenated using operator <strong>##</strong> called Token-Pasting operator.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#define merge(a, b) a##b
int main()
{
    printf("%d ", merge(12, 34));
}
// Output: 1234</pre>
<p><strong>6)</strong> A token passed to macro can be converted to a sting literal by using # before it.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#define get(a) #a
int main()
{
    // GeeksQuiz is changed to "GeeksQuiz"
    printf("%s", get(GeeksQuiz));
}
// Output: GeeksQuiz</pre>
<p><strong>7)</strong> The macros can be written in multiple lines using ‘\'. The last line doesn't need to have ‘\'.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#define PRINT(i, limit) while (i < limit) \
                        { \
                            printf("GeeksQuiz "); \
                            i++; \
                        }
int main()
{
    int i = 0;
    PRINT(i, 3);
    return 0;
}
// Output: GeeksQuiz  GeeksQuiz  GeeksQuiz
</pre>
<p><strong>8) </strong>The macros with arguments should be avoided as they cause problems sometimes. And Inline functions should be preferred as there is type checking parameter evaluation in inline functions. From <a target="_blank">C99</a> onward, inline functions are supported by C language also.<br />
For example consider the following program. From first look the output seems to be 1, but it produces 36 as output.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#define square(x) x*x
int main()
{
  int x = 36/square(6); // Expended as 36/6*6
  printf("%d", x);
  return 0;
}
// Output: 36
</pre>
<p>If we use inline functions, we get the expected output. Also the program given in point 4 above can be corrected using inline functions.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
inline int square(int x) { return x*x; }
int main()
{
  int x = 36/square(6);
  printf("%d", x);
  return 0;
}
// Output: 1
</pre>
<p><strong>9)</strong> Preprocessors also support if-else directives which are typically used for conditional compilation.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
#if VERBOSE >= 2
  printf("Trace Message");
#endif
}</pre>
<p><strong>10)</strong> A header file may be included more than one time directly or indirectly, this leads to problems of redeclaration of same variables/functions. To avoid this problem, directives like <em><strong>defined</strong></em>, <em><strong>ifdef </strong></em>and <em><strong>ifndef </strong></em> are used.<br />
<strong>11)</strong> There are some standard macros which can be used to print program file (__FILE__), Date of compilation (__DATE__), Time of compilation (__TIME__) and Line Number in C code (__LINE__)</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int main()
{
   printf("Current File :%s\n", __FILE__ );
   printf("Current Date :%s\n", __DATE__ );
   printf("Current Time :%s\n", __TIME__ );
   printf("Line Number :%d\n", __LINE__ );
   return 0;
}

/* Output:
Current File :C:\Users\GfG\Downloads\deleteBST.c
Current Date :Feb 15 2014
Current Time :07:04:25
Line Number :8 */
</pre>
<p>You may like to take a <a target="_blank">Quiz on Macros and Preprocessors in C</a></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		