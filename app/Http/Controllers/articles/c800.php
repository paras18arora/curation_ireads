<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How Linkers Resolve Global Symbols Defined at  Multiple Places?</h1>
				
			
			<p>At compile time, the compiler exports each global symbol to the assembler as either strong or weak, and the assembler encodes this information implicitly in the symbol table of the relocatable object file. Functions and initialized global variables get strong symbols. <span id="more-7238"></span>Uninitialized global variables get weak symbols.<br />
For the following example programs, <em>buf, bufp0, main,</em> and <em>swap </em>are strong symbols; <em>bufp1 </em>is a weak symbol.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
 /* main.c */
 void swap();
 int buf[2] = {1, 2};
 int main()
 {
   swap();
   return 0;
 }

 /* swap.c */
 extern int buf[];

 int *bufp0 = &buf[0];
 int *bufp1;

 void swap()
 {
   int temp;

   bufp1 = &buf[1];
   temp = *bufp0;
   *bufp0 = *bufp1;
   *bufp1 = temp;
}
</pre>
<p>Given this notion of strong and weak symbols, Unix linkers use the following rules for dealing with multiply defined symbols:</p>
<p><strong>Rule 1:</strong> Multiple strong symbols are not allowed.<br />
<strong>Rule 2: </strong>Given a strong symbol and multiple weak symbols, choose the strong symbol.<br />
<strong>Rule 3:</strong> Given multiple weak symbols, choose any of the weak symbols.</p>
<p>For example, suppose we attempt to compile and link the following two C modules:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* foo1.c */        
int main()          
{                   
  return 0;       
}                  

/* bar1.c */
int main()
{
  return 0;
}
</pre>
<p>In this case, the linker will generate an error message because the strong symbol <em>main </em>is defined multiple times (rule 1):</p>
<p><em>unix> gcc foo1.c bar1.c<br />
/tmp/cca015022.o: In function ‘main':<br />
/tmp/cca015022.o(.text+0x0): multiple definition of ‘main'<br />
/tmp/cca015021.o(.text+0x0): first defined here</em></p>
<p>Similarly, the linker will generate an error message for the following modules because the strong symbol <em>x </em>is defined twice (rule 1):</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* foo2.c */
int x = 15213;
int main()
{
  return 0;
}

/* bar2.c */
int x = 15213;
void f()
{
}
</pre>
<p>However, if <em>x </em>is uninitialized in one module, then the linker will quietly choose the strong symbol defined in the other (rule 2) as is the case in following program:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* foo3.c */
#include <stdio.h>
void f(void);
int x = 15213;
int main()
{
  f();
  printf("x = %d\n", x);
  return 0;
}

/* bar3.c */
int x;
void f()
{
  x = 15212;
}
</pre>
<p>At run time, function f() changes the value of x from 15213 to 15212, which might come as a unwelcome surprise to the author of function main! Notice that the linker normally gives no indication that it has detected multiple definitions of x.</p>
<p><em>unix> gcc -o foobar3 foo3.c bar3.c<br />
unix> ./foobar3<br />
x = 15212</em></p>
<p>The same thing can happen if there are two weak definitions of x (rule 3).</p>
<p>See below source link for more detailed explanation and more examples.</p>
<p>Source:<br />
<a>http://csapp.cs.cmu.edu/public/ch7-preview.pdf</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		