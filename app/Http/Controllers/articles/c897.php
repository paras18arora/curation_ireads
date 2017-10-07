<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Few bytes on NULL pointer in C !</h1>
				
			
			<p style="text-align: justify;">At the very high level, we can think of NULL as null pointer which is used in C for various purposes. Some of the most common use cases for NULL are<br />
a) To initialize a pointer variable when that pointer variable isn't assigned any valid memory address yet.<br />
b) To check for null pointer before accessing any pointer variable. By doing so, we can perform error handling in pointer related code e.g. dereference pointer variable only if it's not NULL.<br />
c) To pass a null pointer to a function argument when we don't want to pass any valid memory address.</p>
<p style="text-align: justify;">The example of a) is</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int * pInt = NULL;
</pre>
<p style="text-align: justify;">The example of b) is</p>
<pre class="brush: cpp; title: ; notranslate" title="">
if(pInt != NULL) /*We could use if(pInt) as well*/
{ /*Some code*/}
else
{ /*Some code*/}
</pre>
<p style="text-align: justify;">The example of c) is</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int fun(int *ptr)
{
 /*Fun specific stuff is done with ptr here*/
 return 10;
}
fun(NULL);
</pre>
<p style="text-align: justify;">It should be noted that NULL pointer is different from uninitialized and dangling pointer. In a specific program context, all uninitialized or dangling or NULL pointers are invalid but NULL is a specific invalid pointer which is mentioned in C standard and has specific purposes. What we mean is that uninitialized and dangling pointers are invalid but they can point to some memory address that may be accessible though the memory access is unintended.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
 int *i, *j;
 int *ii = NULL, *jj = NULL;
 if(i == j)
 {
  printf("This might get printed if both i and j are same by chance.");
 }
 if(ii == jj)
 {
  printf("This is always printed coz ii and jj are same.");
 }
 return 0;
}
</pre>
<p style="text-align: justify;">By specifically mentioning NULL pointer, C standard gives mechanism using which a C programmer can use and check whether a given pointer is legitimate or not. But what exactly is NULL and how it's defined? Strictly speaking, NULL expands to an implementation-defined null pointer constant which is defined in many header files such as "<em>stdio.h</em>", "<em>stddef.h</em>", "<em>stdlib.h</em>" etc. Let us see what C standards say about null pointer. From C11 standard clause 6.3.2.3,</p>
<p style="text-align: justify;">"<em>An integer constant expression with the value 0, or such an expression cast to type void *, is called a null pointer constant. If a null pointer constant is converted to a pointer type, the resulting pointer, called a null pointer, is guaranteed to compare unequal to a pointer to any object or function.</em>"</p>
<p style="text-align: justify;">Before we proceed further on this NULL discussion :), let's mention few lines about C standard just in case you wants to refer it for further study. Please note that ISO/IEC 9899:2011 is the C language's latest standard which was published in Dec 2011. This is also called C11 standard. For completeness, let us mention that previous standards for C were C99, C90 (also known as ISO C) and C89 (also known as ANSI C). Though the actual C11 standard can be purchased from ISO, there's a draft document which is available in public domain for free.</p>
<p style="text-align: justify;">Coming to our discussion, NULL macro is defined as <em>((void *)0)</em> in header files of most of the C compiler implementations. But C standard is saying that 0 is also a null pointer constant. It means that the following is also perfectly legal as per standard.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int * ptr = 0;
</pre>
<p style="text-align: justify;">Please note that 0 in the above C statement is used in pointer-context and it's different from 0 as integer. This is one of the reason why usage of NULL is preferred because it makes it explicit in code that programmer is using null pointer not integer 0. Another important concept about NULL is that "<em>NULL expands to an implementation-defined null pointer constant</em>". This statement is also from C11 clause 7.19. It means that internal representation of the null pointer could be non-zero bit pattern to convey NULL pointer. That's why NULL always needn't be internally represented as all zeros bit pattern. A compiler implementation can choose to represent "null pointer constant" as a bit pattern for all 1s or anything else. But again, as a C programmer, we needn't to worry much on the internal value of the null pointer unless we are involved in Compiler coding or even below level of coding. Having said so, typically NULL is represented as all bits set to 0 only. To know this on a specific platform, one can use the following</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
 printf("%d",NULL);
 return 0;
}
</pre>
<p style="text-align: justify;">Most likely, it's printing 0 which is the typical internal null pointer value but again it can vary depending on the C compiler/platform. You can try few other things in above program such as <em>printf("‘%c",NULL)</em> or <em>printf("%s",NULL)</em> and even <em>printf("%f",NULL)</em>. The outputs of these are going to be different depending on the platform used but it'd be interesting especially usage of <em>%f</em> with NULL!</p>
<p style="text-align: justify;">Can we use <em>sizeof()</em> operator on NULL in C? Well, usage of <em>sizeof(NULL)</em> is allowed but the exact size would depend on platform.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
 printf("%d",sizeof(NULL));
 return 0;
}
</pre>
<p style="text-align: justify;">Since NULL is defined as <em>((void*)0)</em>, we can think of NULL as a special pointer and its size would be equal to any pointer. If pointer size of a platform is 4 bytes, the output of above program would be 4. But if pointer size on a platform is 8 bytes, the output of above program would be 8.</p>
<p style="text-align: justify;">What about dereferencing of NULL? What's going to happen if we use the following C code</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
 int * ptr = NULL;
 printf("%d",*ptr);
 return 0;
}
</pre>
<p style="text-align: justify;">On some machines, the above would compile successfully but crashes when the program is run though it needn't to show the same behavior across all the machines. Again it depends on lot of factors. But the idea of mentioning the above snippet is that we should always check for NULL before accessing it.</p>
<p style="text-align: justify;">Since NULL is typically defined as <em>((void*)0)</em>, let us discuss a little bit about <em>void</em> type as well. As per C11 standard clause 6.2.5, "<em>The void type comprises an empty set of values; it is an incomplete object type that cannot be completed</em>". Even C11 clause 6.5.3.4 mentions that "<em>The sizeof operator shall not be applied to an expression that has function type or an incomplete type, to the parenthesized name of such a type, or to an expression that designates a bit-field member.</em>" Basically, it means that <em>void</em> is an incomplete type whose size doesn't make any sense in C programs but implementations (such as gcc) can choose <em>sizeof(void)</em> as 1 so that the flat memory pointed by void pointer can be viewed as untyped memory i.e. a sequence of bytes. But the output of the following needn't to same on all platforms.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
 printf("%d",sizeof(void));
 return 0;
}
</pre>
<p style="text-align: justify;">On gcc, the above would output 1. What about <em>sizeof(void *)</em>? Here C11 has mentioned guidelines. From clause 6.2.5, "<em>A pointer to void shall have the same representation and alignment requirements as a pointer to a character type</em>". That's why the output of the following would be same as any pointer size on a machine.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
 printf("%d",sizeof(void *));
 return 0;
}
</pre>
<p style="text-align: justify;">Inspite of mentioning machine dependent stuff as above, we as C programmers should always strive to make our code as portable as possible. So we can conclude on NULL as follows:</p>
<p style="text-align: justify;">1. Always initialize pointer variables as NULL.<br />
2. Always perform NULL check before accessing any pointer.</p>
<p style="text-align: justify;">Please do Like/Tweet/G+1 if you find the above useful. Also, please do leave us comment for further clarification or info. We would love to help and learn <img src="http://d30wr2otswzun8.cloudfront.net/wp-includes/images/smilies/simple-smile.png" alt=":)" class="wp-smiley" style="height: 1em; max-height: 1em;" /></p>

			

<!-- GQBottom -->


		