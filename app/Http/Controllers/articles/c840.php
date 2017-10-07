<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Evaluation order of operands</h1>
				
			
			<p>Consider the below C/C++ program. <span id="more-8389"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int x = 0;

int f1()
{
  x = 5;
  return x;
}

int f2()
{
  x = 10;
  return x;
}

int main()
{
  int p = f1() + f2();
  printf("%d ", x);
  getchar();
  return 0;
}
</pre>
<p>What would the output of the above program – ‘5' or '10'?<br />
The output is undefined as the order of evaluation of f1() + f2() is not mandated by standard. The compiler is free to first call either f1() or f2(). Only when equal level precedence operators appear in an expression, the associativity comes into picture. For example, f1()  +  f2()  +  f3() will be considered as (f1()  +  f2())  +  f3(). But among first pair, which function (the operand) evaluated first is not defined by the standard. </p>
<p>Thanks to <a>Venki </a> for suggesting the solution.</p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		