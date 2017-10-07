<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Operators in C | Set 1 (Arithmetic Operators)</h1>
				
			
			<p>Operators are the foundation of any programming language. Thus the functionality of C language is incomplete without the use of operators. Operators allow us to perform different kinds of operations on operands. In C, operators in Can be categorized in following categories: <span id="more-18443"></span>
</p><ul>
<li><strong>Arithmetic Operator</strong>s (+, -, *, /, %, post-increment, pre-increment, post-decrement, pre-decrement) </li>
<li><strong>Relational Operators</strong> (==, != , >, <, >= & <=) Logical Operators (&&, || and !)  </li>
<li><strong>Bitwise Operators </strong>(&, |, ^, ~, >> and <<)</li>
<li><strong>Assignment Operator</strong>s (=, +=, -=, *=, etc)</li>
<li><strong>Other Operators</strong> (conditional, comma, sizeof, address, redirecton)</li>
</ul>
<p><strong>Arithmetic Operators:</strong> These are used to perform arithmetic/mathematical operations on operands. The binary operators falling in this category are:</p>
<ul>
<ul>
<li><strong>Addition:</strong> The <strong>‘+'</strong> operator adds two operands. For example, <strong>x+y</strong>.</li>
<li><strong>Subtraction:</strong> The <strong>‘-‘</strong> operator subtracts two operands. For example, <strong>x-y</strong>.</li>
<li><strong>Multiplication:</strong> The <strong>‘*'</strong> operator multiplies two operands. For example, <strong>x*y</strong>.</li>
<li><strong>Division:</strong> The <strong>‘/'</strong> operator divides the first operand by the second. For example, <strong>x/y</strong>.</li>
<li><strong>Modulus:</strong> The <strong>‘%'</strong> operator returns the remainder when first operand is divided by the second. For example, <strong>x%y</strong>.</li>
</ul>
</ul>
<pre class="brush: cpp; title: ; notranslate" title="">
// C program to demonstrate working of binary arithmetic operators
#include<stdio.h>

int main()
{
    int a = 10, b = 4, res;

    //printing a and b
    printf("a is %d and b is %d\n", a, b);

    res = a+b; //addition
    printf("a+b is %d\n", res);

    res = a-b; //subtraction
    printf("a-b is %d\n", res);

    res = a*b; //multiplication
    printf("a*b is %d\n", res);

    res = a/b; //division
    printf("a/b is %d\n", res);

    res = a%b; //modulus
    printf("a%%b is %d\n", res);

    return 0;
}
</pre>
<p>Output:</p>
<pre>a is 10 and b is 4
a+b is 14
a-b is 6
a*b is 40
a/b is 2
a%b is 2</pre>
<p>The ones falling into the category of unary arithmetic operators are:</p>
<ul>
<li><strong>Increment:</strong> The <strong>‘++'</strong> operator is used to increment the value of an integer. When placed before the variable name (also called pre-increment operator), its value is incremented instantly. For example, <strong>++x</strong>.<br />
And when it is placed after the variable name (also called post-increment operator), its value is preserved temporarily until the execution of this statement and it gets updated before the execution of the next statement. For example, <strong>x++</strong>.</li>
<li><strong>Decrement:</strong> The <strong>‘–‘</strong> operator is used to decrement the value of an integer. When placed before the variable name (also called pre-decrement operator), its value is decremented instantly. For example, <strong>–x</strong>.<br />
And when it is placed after the variable name (also called post-decrement operator), its value is preserved temporarily until the execution of this statement and it gets updated before the execution of the next statement. For example, <strong>x–</strong>.</li>
</ul>
<pre class="brush: cpp; title: ; notranslate" title="">
// C program to demonstrate working of Unary arithmetic operators
#include<stdio.h>

int main()
{
    int a = 10, b = 4, res;

    // post-increment example:
    // res is assigned 10 only, a is not updated yet
    res = a++;
    printf("a is %d and res is %d\n", a, res); //a becomes 11 now


    // post-decrement example:
    // res is assigned 11 only, a is not updated yet
    res = a--;
    printf("a is %d and res is %d\n", a, res);  //a becomes 10 now


    // pre-increment example:
    // res is assigned 11 now since a is updated here itself
    res = ++a;
    // a and res have same values = 11
    printf("a is %d and res is %d\n", a, res);


    // pre-decrement example:
    // res is assigned 10 only since a is updated here itself
    res = --a;
    // a and res have same values = 10
    printf("a is %d and res is %d\n",a,res); 

    return 0;
}
</pre>
<p>Output:</p>
<pre>a is 11 and res is 10
a is 10 and res is 11
a is 11 and res is 11
a is 10 and res is 10</pre>
<p>We will soon be discussing other categories of operators in different posts.</p>
<p>To know about <strong>Operator Precedence and Associativity</strong>, refer <a>this </a>link:</p>
<p><a>Quiz on Operators in C</a></p>
<p>This article is contributed by Ayush Jaggi. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		