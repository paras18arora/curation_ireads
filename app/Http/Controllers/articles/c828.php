<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Operators in C | Set 2 (Relational and Logical Operators)</h1>
				
			
			<p>We have discussed <a>introduction to operators in C and Arithmetic Operators</a>.  In this article, Relational and Logical Operators are discussed.</p>
<p><strong>Relational Operators:</strong><br />
Relational operators are used for comparison of two values. Let's see them one by one:</p>
<ul>
<li><strong>‘=='</strong> operator checks whether the two given operands are equal or not. If so, it returns true. Otherwise it returns false. For example, <strong>5==5</strong> will return true.</li>
<li><strong>‘!='</strong> operator checks whether the two given operands are equal or not. If not, it returns true. Otherwise it returns false. It is the exact boolean complement of the <strong>‘=='</strong> operator. For example, <strong>5!=5</strong> will return false.</li>
<li><strong>‘>'</strong> operator checks whether the first operand is greater than the second operand. If so, it returns true. Otherwise it returns false. For example, <strong>6>5</strong> will return true.</li>
<li><strong>‘<‘</strong> operator checks whether the first operand is lesser than the second operand. If so, it returns true. Otherwise it returns false. For example, <strong>6<5</strong> will return false.</li>
<li><strong>‘>='</strong> operator checks whether the first operand is greater than or equal to the second operand. If so, it returns true. Otherwise it returns false. For example, <strong>5>=5</strong> will return true.</li>
<li><strong>‘<='</strong> operator checks whether the first operand is lesser than or equal to the second operand. If so, it returns true. Otherwise it returns false. For example, <strong>5<=5</strong> will also return true.</li>
</ul>
<pre class="brush: cpp; title: ; notranslate" title="">
// C program to demonstrate working of relational operators
#include <stdio.h>

int main()
{
    int a=10, b=4;

    // relational operators
    // greater than example
    if (a > b)
        printf("a is greater than b\n");
    else printf("a is less than or equal to b\n");

    // greater than equal to
    if (a >= b)
        printf("a is greater than or equal to b\n");
    else printf("a is lesser than b\n");

    // less than example
    if (a < b)
        printf("a is less than b\n");
    else printf("a is greater than or equal to b\n");

    // lesser than equal to
    if (a <= b)
        printf("a is lesser than or equal to b\n");
    else printf("a is greater than b\n");

    // equal to
    if (a == b)
        printf("a is equal to b\n");
    else printf("a and b are not equal\n");

    // not equal to
    if (a != b)
        printf("a is not equal to b\n");
    else printf("a is equal b\n");

    return 0;
}
</pre>
<p>Output:
</p><pre>
a is greater than b
a is greater than or equal to b
a is greater than or equal to b
a is greater than b
a and b are not equal
a is not equal to b</pre>
<p><br />
<strong>Logical Operators:</strong><br />
They are used to combine two or more conditions/constraints or to complement the evaluation of the original condition in consideration. They are described below:</p>
<ul>
<li><strong>Logical AND:</strong> The <strong>‘&&'</strong> operator returns true when both the conditions in consideration are satisfied. Otherwise it returns false. For example, <strong>a && b</strong> returns true when both a and b are true (i.e. non-zero).</li>
<li><strong>Logical OR:</strong> The <strong>‘||'</strong> operator returns true when one (or both) of the conditions in consideration is satisfied. Otherwise it returns false. For example, <strong>a || b</strong> returns true if one of a or b is true (i.e. non-zero). Of course, it returns true when both a and b are true.</li>
<li><strong>Logical NOT:</strong> The <strong>‘!'</strong> operator returns true the condition in consideration is not satisfied. Otherwise it returns false. For example, <strong>!a</strong> returns true if a is false, i.e. when a=0.</li>
</ul>
<pre class="brush: cpp; title: ; notranslate" title="">
// C program to demonstrate working of logical operators
#include <stdio.h>

int main()
{
    int a=10, b=4, c = 10, d = 20;

    // logical operators

    // logical AND example
    if (a>b && c==d)
        printf("a is greater than b AND c is equal to d\n");
    else printf("AND condition not satisfied\n");

    // logical AND example
    if (a>b || c==d)
        printf("a is greater than b OR c is equal to d\n");
    else printf("Neither a is greater than b nor c is equal "
                " to d\n");

    // logical NOT example
    if (!a)
        printf("a is zero\n");
    else printf("a is not zero");

    return 0;
}
</pre>
<p>Output:
</p><pre>
AND condition not satisfied
a is greater than b OR c is equal to d
a is not zero</pre>
<p><strong>Short-Circuiting in Logical Operators:</strong><br />
In case of <strong>logical AND</strong>, the second operand is not evaluated if first operand is false.  For example, program 1 below doesn't print "GeeksQuiz" as the first operand of logical AND itself is false.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdbool.h>
int main()
{
    int a=10, b=4;
    bool res = ((a == b) && printf("GeeksQuiz"));
    return 0;
}
</pre>
<p>But below program prints "GeeksQuiz" as first operand of logical AND is true.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdbool.h>
int main()
{
    int a=10, b=4;
    bool res = ((a != b) && printf("GeeksQuiz"));
    return 0;
}
</pre>
<p> <br />
In case of <strong>logical OR</strong>, the second operand is not evaluated if first operand is true.  For example, program 1 below doesn't print "GeeksQuiz" as the first operand of logical OR itself is true.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdbool.h>
int main()
{
    int a=10, b=4;
    bool res = ((a != b) || printf("GeeksQuiz"));
    return 0;
}
</pre>
<p>But below program prints "GeeksQuiz" as first operand of logical OR is false.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
#include <stdbool.h>
int main()
{
    int a=10, b=4;
    bool res = ((a == b) || printf("GeeksQuiz"));
    return 0;
}
</pre>
<p><a>Quiz on Operators in C</a></p>
<p>This article is contributed by Ayush Jaggi. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		