<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Comma in C and C++</h1>
				
			
			<p>In C and C++, comma (,) can be used in two contexts: <span id="more-8482"></span></p>
<p>1) Comma as an operator:<br />
The comma operator (represented by the token ,) is a binary operator that evaluates its first operand and discards the result, it then evaluates the second operand and returns this value (and type). The comma operator has the lowest precedence of any C operator, and acts as a <a>sequence point</a>. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* comma as an operator */
int i = (5, 10);  /* 10 is assigned to i*/
int j = (f1(), f2());  /* f1() is called (evaluated) first followed by f2(). 
                      The returned value of f2() is assigned to j */
</pre>
<p>2) Comma as a separator:<br />
Comma acts as a separator when used with function calls and definitions, function like macros, variable declarations, enum declarations, and similar constructs.</p>
<pre class="brush: cpp; title: ; notranslate" title=""> 
  /* comma as a separator */
  int a = 1, b = 2;
  void fun(x, y);
</pre>
<p>The use of comma as a separator should not be confused with the use as an operator.  For example, in below statement, f1() and f2() can be called in any order. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* Comma acts as a separator here and doesn't enforce any sequence. 
    Therefore, either f1() or f2() can be called first */
void fun(f1(), f2());
</pre>
<p>See <a>this </a> for C vs C++ differences of using comma operator.</p>
<p>You can try below programs to check your understanding of comma in C.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// PROGRAM 1
#include<stdio.h>
int main()
{
   int x = 10;
   int y = 15; 
 
   printf("%d", (x, y));
   getchar();
   return 0;
}
</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
// PROGRAM 2:  Thanks to Shekhu for suggesting this program
#include<stdio.h>
int main()
{
   int x = 10;
   int y = (x++, ++x);
   printf("%d", y);
   getchar();
   return 0;
}
</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
// PROGRAM 3:  Thanks to Venki for suggesting this program
int main()
{
    int x = 10, y;
 
    // The following is equavalent to y = x++
    y = (x++, printf("x = %d\n", x), ++x, printf("x = %d\n", x), x++);
 
    // Note that last expression is evaluated
    // but side effect is not updated to y
    printf("y = %d\n", y);
    printf("x = %d\n", x);
 
    return 0;
}
</pre>
<p>References:<br />
<a>http://en.wikipedia.org/wiki/Comma_operator</a><br />
<a>http://publib.boulder.ibm.com/infocenter/comphelp/v101v121/index.jsp?topic=/com.ibm.xlcpp101.aix.doc/language_ref/co.html</a><br />
<a>http://msdn.microsoft.com/en-us/library/zs06xbxh.aspx</a></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		