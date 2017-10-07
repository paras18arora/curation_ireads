<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Static Variables in C</h1>
				
			
			<p>Static variables have a property of preserving their value even after they are out of their scope!<span id="more-17943"></span>Hence, static variables preserve their previous value in their previous scope and are not initialized again in the new scope.<br />
Syntax:
</p><pre>
static data_type var_name = var_value; </pre>
<p><br />
Following are some interesting facts about static variables in C.</p>
<p><strong>1)</strong> A static int variable remains in memory while the program is running.  A normal or auto variable is destroyed when a function call where the variable was declared is over.  </p>
<p>For example, we can use static int to count number of times a function is called, but an auto variable can't be sued for this purpose.</p>
<p>For example below program prints "1 2″</p>
<pre class="brush: cpp; title: ; notranslate" title="">	
#include<stdio.h>
int fun()
{
  static int count = 0;
  count++;
  return count;
}
 
int main()
{
  printf("%d ", fun());
  printf("%d ", fun());
  return 0;
}</pre>
<p>Output:
</p><pre>1 2</pre>
<p>But below program prints 1 1</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int fun()
{
  int count = 0;
  count++;
  return count;
}
 
int main()
{
  printf("%d ", fun());
  printf("%d ", fun());
  return 0;
}</pre>
<p>Output:
</p><pre>1 1</pre>
<p><br />
<strong>2)</strong> Static variables are allocated memory in data segment, not stack segment.  See <a>memory layout of C programs</a> for details.</p>
<p><br />
<strong>3) </strong>Static variables (like global variables) are initialized as 0 if not initialized explicitly.  For example in the below program, value of x is printed as 0, while value of y is something garbage.  See <a>this </a>for more details.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
    static int x;
    int y;
    printf("%d \n %d", x, y);
}</pre>
<p>Output:
</p><pre>0 
[some_garbage_value] </pre>
<p><br />
<strong>4)</strong> In C, static variables can only be initialized using constant literals. For example, following program fails in compilation.  See <a>this </a>for more details.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int initializer(void)
{
    return 50;
}
 
int main()
{
    static int i = initializer();
    printf(" value of i = %d", i);
    getchar();
    return 0;
}
</pre>
<p>Output
</p><pre> In function 'main':
9:5: error: initializer element is not constant
     static int i = initializer();
     ^</pre>
<p>Please note that this condition doesn't hold in C++.  So if you save the program as a C++ program, it would compile \and run fine.</p>
<p></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			

<!-- GQBottom -->


		