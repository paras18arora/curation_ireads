<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Sequence Points in C | Set 1</h1>
				
			
			<p>In this post, we will try to cover many ambiguous questions like following.<span id="more-8730"></span></p>
<p>Guess the output of following programs.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// PROGRAM 1
#include <stdio.h>
int f1() { printf ("Geeks"); return 1;}
int f2() { printf ("forGeeks"); return 1;}
int main() 
{ 
  int p = f1() + f2();  
  return 0; 
}

// PROGRAM 2
#include <stdio.h>
int x = 20;
int f1() { x = x+10; return x;}
int f2() { x = x-5;  return x;}
int main()
{
  int p = f1() + f2();
  printf ("p = %d", p);
  return 0;
}


// PROGRAM 3
#include <stdio.h>
int main()
{
   int i = 8;
   int p = i++*i++;
   printf("%d\n", p);
}
</pre>
<p>The output of all of the above programs is <a>undefined</a> or <a target="_blank">unspecified</a>. The output may be different with different compilers and different machines. It is like asking the value of undefined automatic variable. </p>
<p>The reason for undefined behavior in PROGRAM 1 is, the operator ‘+' doesn't have standard defined order of evaluation for its operands.  Either f1() or f2() may be executed first. So output may be either "GeeksforGeeks"  or "forGeeksGeeks".<br />
Similar to operator ‘+', most of the other similar operators like ‘-‘, ‘/', ‘*', Bitwise AND &, Bitwise OR |, .. etc don't have a standard defined order for evaluation for its operands.</p>
<p>Evaluation of an expression may also produce side effects. For example, in the above program 2, the final values of p is ambiguous. Depending on the order of expression evaluation, if f1() executes first, the value of p will be 55, otherwise 40.</p>
<p>The output of program 3 is also undefined. It may be 64, 72, or may be something else. The subexpression i++ causes a side effect, it modifies i's value, which leads to undefined behavior since i is also referenced elsewhere in the same expression.</p>
<p>Unlike above cases, <em>at certain specified points in the execution sequence called <a>sequence points</a>, all side effects of previous evaluations are guaranteed to be complete</em>.  A <a>sequence point</a> defines any point in a computer program's execution at which it is guaranteed that all side effects of previous evaluations will have been performed, and no side effects from subsequent evaluations have yet been performed. Following are the sequence points listed in the C standard:</p>
<p><strong>— The end of the first operand of the following operators: </strong><br />
  a) logical AND &&<br />
  b) logical OR ||<br />
  c) conditional ?<br />
  d) comma ,</p>
<p>For example, the output of following programs is guaranteed to be "GeeksforGeeks" on all compilers/machines.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Following 3 lines are common in all of the below programs
#include <stdio.h>
int f1() { printf ("Geeks"); return 1;}
int f2() { printf ("forGeeks"); return 1;}

// PROGRAM 4
int main() 
{ 
   // Since && defines a sequence point after first operand, it is 
   // guaranteed that f1() is completed first.
   int p = f1() && f2();  
   return 0; 
}

// PROGRAM 5
int main()
{
   // Since comma operator defines a sequence point after first operand, it is
   // guaranteed that f1() is completed first.
  int p = (f1(), f2());
  return 0;
}


// PROGRAM 6
int main() 
{ 
   // Since ? operator defines a sequence point after first operand, it is 
   // guaranteed that f1() is completed first.
  int p = f1()? f2(): 3;  
  return 0; 
}
</pre>
<p><br />
<strong>— The end of a full expression. This category includes following expression statements </strong><br />
a) Any full statement ended with semicolon like "a = b;"<br />
b) return statements<br />
c) The controlling expressions of if, switch, while, or do-while statements.<br />
d) All three expressions in a for statement.</p>
<p>The above list of sequence points is partial.  We will be covering all remaining sequence points in the next post on Sequence Point. We will also be covering many C questions asked in forum (See <a>this</a>, <a>this</a>, <a>this </a>, <a>this </a> and many others). </p>
<p>References:<br />
<a>http://en.wikipedia.org/wiki/Sequence_point</a><br />
<a>http://c-faq.com/expr/seqpoints.html</a><br />
<a>http://msdn.microsoft.com/en-us/library/d45c7a5d(v=vs.110).aspx</a><br />
<a>http://www.open-std.org/jtc1/sc22/wg14/www/docs/n925.htm</a></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		