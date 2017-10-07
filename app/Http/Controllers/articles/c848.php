<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Multiline macros in C</h1>
				
			
			<p>In this article, we will discuss how to write a multi-line macro. We can write multi-line macro same like function, but each statement ends with "\". Let us see with example.<span id="more-21808"></span> Below is simple macro, which accepts input number from user, and prints whether entered number is even or odd.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

#define MACRO(num, str) {\
			printf("%d", num);\
			printf(" is");\
			printf(" %s number", str);\
			printf("\n");\
		   }

int main(void)
{
    int num;

    printf("Enter a number: ");
    scanf("%d", &num);

    if (num & 1)
        MACRO(num, "Odd");
    else
        MACRO(num, "Even");

    return 0;
}
</pre>
<p>At first look, the code looks OK, but when we try to compile this code, it gives compilation error.</p>
<pre>
[narendra@/media/partition/GFG]$ make macro
cc     macro.c   -o macro
macro.c: In function ‘main':
macro.c:19:2: error: ‘else' without a previous ‘if'
make: *** [macro] Error 1
[narendra@/media/partition/GFG]$ 
</pre>
<p>Let us see what mistake we did while writing macro. We have enclosed macro in curly braces. According to C-language rule, each C-statement should end with semicolon. That's why we have ended MACRO with semicolon. Here is a mistake. Let us see how compile expands this macro.</p>
<pre>
if (num & 1)
{
    -------------------------
    ---- Macro expansion ----
    -------------------------
};    /* Semicolon at the end of MACRO, and here is ERROR */

else 
{
   -------------------------
   ---- Macro expansion ----
   -------------------------

};
</pre>
<p>We have ended macro with semicolon. When compiler expands macro, it puts semicolon after "if" statement.  Because of semicolon between "if and else statement" compiler gives compilation error. Above program will work fine, if we ignore "else" part.</p>
<p>To overcome this limitation, we can enclose our macro in "do-while(0)" statement. Our modified macro will look like this.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

#define MACRO(num, str) do {\
			printf("%d", num);\
			printf(" is");\
			printf(" %s number", str);\
			printf("\n");\
		   } while(0)

int main(void)
{
    int num;

    printf("Enter a number: ");
    scanf("%d", &num);

    if (num & 1)
        MACRO(num, "Odd");
    else
        MACRO(num, "Even");

    return 0;
}
</pre>
<p>Compile and run above code, now this code will work fine.</p>
<pre>
[narendra@/media/partition/GFG]$ make macro
cc     macro.c   -o macro
[narendra@/media/partition/GFG]$ ./macro 
Enter a number: 9
9 is Odd number
[narendra@/media/partition/GFG]$ ./macro 
Enter a number: 10
10 is Even number
[narendra@/media/partition/GFG]$ 
</pre>
<p>We have enclosed macro in "do – while(0)" loop and at the end of while, we have put condition as "while(0)", that's why this loop will execute only one time.</p>
<p>Similarly, instead of "do – while(0)" loop we can enclose multi-line macro in parenthesis. We can achieve the same result by using this trick. Let us see example.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

#define MACRO(num, str) ({\
			printf("%d", num);\
			printf(" is");\
			printf(" %s number", str);\
			printf("\n");\
		   })

int main(void)
{
    int num;

    printf("Enter a number: ");
    scanf("%d", &num);

    if (num & 1)
        MACRO(num, "Odd");
    else
        MACRO(num, "Even");

    return 0;
}
</pre>
<pre>
[narendra@/media/partition/GFG]$ make macro
cc     macro.c   -o macro
[narendra@/media/partition/GFG]$ ./macro 
Enter a number: 10
10 is Even number
[narendra@/media/partition/GFG]$ ./macro 
Enter a number: 15
15 is Odd number
[narendra@/media/partition/GFG]$ 
</pre>
<p>This article is compiled by <strong>Narendra Kangralkar</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		