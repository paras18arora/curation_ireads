<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Interesting facts about Operator Precedence and Associativity in C</h1>
				
			
			<p>Operator precedence determines which operator is performed first in an expression with more than one operators with different precedence.<span id="more-126091"></span> For example 10 + 20 * 30 is calculated as 10 + (20 * 30) and not as (10 + 20) * 30.</p>
<p>Associativity is used when two operators of same precedence appear in an expression. Associativity can be either <strong>L</strong>eft<strong> t</strong>o <strong>R</strong>ight or<strong> R</strong>ight<strong> t</strong>o <strong>L</strong>eft. For example ‘*' and ‘/' have same precedence and their associativity is <strong>L</strong>eft<strong> t</strong>o <strong>R</strong>ight, so the expression "100 / 10 * 10″ is treated as "(100 / 10) * 10″.</p>
<p><em> Precedence and Associativity are two characteristics of operators that determine the evaluation order of subexpressions in absence of brackets</em>.</p>
<p><strong>1) Associativity is only used when there are two or more operators of same precedence.</strong><br />
The point to note is associativity doesn't define the order in which operands of a single operator are evaluated. For example consider the following program, associativity of the + operator is left to right, but it doesn't mean f1() is always called before f2(). The output of following program is in-fact compiler dependent.  See <a target="_blank">this</a> for details.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Associativity is not used in the below program. Output 
// is compiler dependent.
int x = 0; 
int f1() {
  x = 5;
  return x;
} 
int f2() {
  x = 10;
  return x;
}
int main() {
  int p = f1() + f2();
  printf("%d ", x);
  return 0;
}</pre>
<p><strong>2) All operators with same precedence have same associativity</strong><br />
This is necessary, otherwise there won't be any way for compiler to decide evaluation order of expressions which have two operators of same precedence and different associativity. For example + and – have same associativity.</p>
<p><strong>3) Precedence and associativity of postfix ++ and prefix ++ are different</strong><br />
Precedence of postfix ++ is more than prefix ++, their associativity is also different. Associativity of postfix ++ is left to right and associativity of prefix ++. See <a target="_blank">this</a> for examples.</p>
<p><strong>4) Comma has the least precedence among all operators and should be used carefully</strong> For example consider the following program, the output is 1. See <a target="_blank">this</a> and <a target="_blank">this</a> for more details.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h> 
int main()
{
    int a;
    a = 1, 2, 3; // Evaluated as (a = 1), 2, 3
    printf("%d", a);
    return 0;
}</pre>
<p><strong>5) There is no chaining of comparison operators in C</strong><br />
In Python, expression like "c > b > a" is treated as "a > b and b > c", but this type of chaining doesn't happen in C. For example consider the following program. The output of following program is "FALSE". </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
  int a = 10, b = 20, c = 30;

  // (c > b > a) is treated as ((c > b) > a), associativity of '>' 
  // is left to right. Therefore the value becomes ((30 > 20) > 10) 
  // which becomes (1 > 20)
  if (c > b > a)  
   printf("TRUE");
  else
    printf("FALSE");
  return 0;
}</pre>
<p>Please see the following precedence and associativity table for reference.</p>
<table width="90%" border="1" cellspacing="0" cellpadding="2">
<tbody>
<tr>
<th>
<p align="center"><b>Operator</b></p>
</th>
<th><b>Description</b></th>
<th>
<p align="center"><b>Associativity</b></p>
</th>
</tr>
<tr>
<td align="center"><span style="font-size: small;">( )<br />
[ ]<br />
.<br />
-><br />
++ —</span></td>
<td><span style="font-size: small;">Parentheses (function call) (see Note 1)<br />
Brackets (array subscript)<br />
Member selection via object name<br />
Member selection via pointer<br />
Postfix increment/decrement (see Note 2)</span></td>
<td valign="top">
<p align="center"><span style="font-size: small;">left-to-right</span></p>
</td>
</tr>
<tr>
<td align="center"><span><span style="font-size: small;"><span>++ —<br />
+ –<br />
! ~<br />
(<i>type</i>)<br />
*<br />
&<br />
sizeof</span> </span></span></td>
<td><span style="font-size: small;">Prefix increment/decrement<br />
Unary plus/minus<br />
Logical negation/bitwise complement<br />
Cast (convert value to temporary value of <i>type</i>)<br />
Dereference<br />
Address (of operand)<br />
Determine size in bytes on this implementation</span></td>
<td align="center" valign="top"><span style="font-size: small;">right-to-left</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">*  /  %</span></td>
<td><span style="font-size: small;">Multiplication/division/modulus</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">+  –</span></td>
<td><span style="font-size: small;">Addition/subtraction</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;"><<  >></span></td>
<td><span style="font-size: small;">Bitwise shift left, Bitwise shift right</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;"><  <=<br />
>  >=</span></td>
<td><span style="font-size: small;">Relational less than/less than or equal to<br />
Relational greater than/greater than or equal to</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">==  !=</span></td>
<td><span style="font-size: small;">Relational is equal to/is not equal to</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">&</span></td>
<td><span style="font-size: small;">Bitwise AND</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">^</span></td>
<td><span style="font-size: small;">Bitwise exclusive OR</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">|</span></td>
<td><span style="font-size: small;">Bitwise inclusive OR</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">&&</span></td>
<td><span style="font-size: small;">Logical AND</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">| |</span></td>
<td><span style="font-size: small;">Logical OR</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">? :</span></td>
<td><span style="font-size: small;">Ternary conditional</span></td>
<td align="center" valign="top"><span style="font-size: small;">right-to-left</span></td>
</tr>
<tr>
<td align="center"><span style="font-size: small;">=<br />
+=  -=<br />
*=  /=<br />
%=  &=<br />
^=  |=<br />
<<=  >>=</span></td>
<td><span style="font-size: small;">Assignment<br />
Addition/subtraction assignment<br />
Multiplication/division assignment<br />
Modulus/bitwise AND assignment<br />
Bitwise exclusive/inclusive OR assignment<br />
Bitwise shift left/right assignment</span></td>
<td align="center" valign="top"><span style="font-size: small;">right-to-left</span></td>
</tr>
<tr>
<td>
<p align="center"><span style="font-size: small;">,</span></p>
</td>
<td><span style="font-size: small;">Comma (separate expressions)</span></td>
<td align="center" valign="top"><span style="font-size: small;">left-to-right</span></td>
</tr>
</tbody>
</table>
<p>It is good to know precedence and associativity rules, but the best thing is to use brackets, especially for less commonly used operators (operators other than +, -, *.. etc). Brackets increase readability of the code as the reader doesn't have to see the table to find out the order.</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		