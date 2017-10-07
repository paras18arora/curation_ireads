<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Functions in C</h1>
				
			
			<p>A function is a set of statements that take inputs, do some specific computation and produces output. </p>
<p>The idea is to put some commonly or repeatedly done task together and make a function, so that instead of writing the same code again and again for different inputs, we can call the function.<span id="more-17290"></span></p>
<p> <br />
<strong>Example: </strong><br />
Below is a simple C program to demonstrate functions in C. </p>
<pre class="brush: cpp; highlight: [3,4,5,6,7,8,9,10,11]; title: ; notranslate" title="">
#include <stdio.h>

// An example function that takes two parameters 'x' and 'y'
// as input and returns max of two input numbers
int max(int x, int y)
{
    if (x > y)
      return x;
    else
      return y;
}

// main function that doesn't receive any parameter and
// returns integer.
int main(void)
{
    int a = 10, b = 20;

    // Calling above function to find max of 'a' and 'b'
    int m = max(a, b);

    printf("m is %d", m);
    return 0;
}
</pre>
<p>Output:
</p><pre>m is 20</pre>
<p> <br />
<strong>Function Declaration</strong><br />
Function declaration tells compiler about number of parameters function takes, data-types of parameters and return type of function. Putting parameter names in function declaration is optional in function declaration, but it is necessary to put them in definition. Below are example of function declarations. (parameter names are not there in below declarations)</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// A function that takes two integers as parameters
// and returns an integer
int max(int, int);

// A function that takes a char and an int as parameters
// and returns an integer
int fun(char, int);
</pre>
<p>It is always recommended to declare a function before it is used (See <a>this</a>, <a>this </a>and <a>this </a>for details) </p>
<p>In C, we can do both declaration and definition at same place, like done in above example program. </p>
<p>C also allows to declare and define functions separately, this is specially needed in case of library functions. The library functions are declared in header files and defined in library files.  Below is an example declaration. </p>
<p> <br />
<strong>Parameter Passing to functions</strong><br />
The parameters passed to function are called <em><strong>actual parameters</strong></em>. For example, in the above program 10 and 20 are actual parameters.<br />
The parameters received by function are called <em><strong>formal parameters</strong></em>. For example, in the above program x and y are formal parameters.<br />
There are two most popular ways to pass parameters.</p>
<p><em><strong>Pass by Value:</strong></em> In this parameter passing method, values of actual parameters are copied to function's formal parameters and the two types of parameters are stored in different memory locations.  So any changes made inside functions are not reflected in actual parameters of caller.</p>
<p><em><strong>Pass by Reference</strong></em> Both actual and formal parameters refer to same locations, so any changes made inside the function are actually reflected in actual parameters of caller.</p>
<p>In C, parameters are always passed by value. Parameters are always passed by value in C. For example. in the below code, value of y is not modified using the function fun(). </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
void fun(int x)
{
   x = 30;
}

int main(void)
{
    int x = 20;
    fun(x);
    printf("x = %d", x);
    return 0;
}
</pre>
<p>Output:
</p><pre>x = 20</pre>
<p>However, in C, we can use pointers to get the effect of pass by reference.  For example, consider the below program. The function fun() expects a pointer ptr to an integer (or an address of an integer). It modifies the value at the address ptr. The dereference operator * is used to access the value at an address. In the statement ‘*ptr = 30', value at address ptr is changed to 30. The address operator & is used to get the address of a variable of any data type. In the function call statement ‘fun(&y)', address of y is passed so that y can be modified using its address.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
# include <stdio.h>
void fun(int *ptr)
{
    *ptr = 30;
}
 
int main()
{
  int x = 20;
  fun(&x);
  printf("x = %d", x);
 
  return 0;
}
</pre>
<p>Output:
</p><pre>x = 30</pre>
<p> <br />
<strong>Following are some important points about functions in C.</strong><br />
<strong>1) </strong>Every C program has a function called main() that is called by operating system when a user runs the program.</p>
<p><strong>2)</strong> Every function has a return type.  If a function doesn't return any value, then void is used as return type.</p>
<p><strong>3)</strong> In C, functions can return any type except arrays and functions. We can get around this limitation by returning pointer to array or pointer to function.</p>
<p><strong>4)</strong> Empty parameter list in C mean that the parameter list is not specified and function can be called with any parameters. In C, it is not a good idea to declare a function like fun(). To declare a function that can only be called without any parameter, we should use "void fun(void)".<br />
As a side note, in C++, empty list means function can only be called without any parameter. In C++, both void fun() and void fun(void) are same.</p>
<p> <br />
<strong>More on Functions in C:</strong></p>
<ul>
<li> Quiz on function in C</li>
<li><a>Importance of function prototype in C</a></li>
<li><a>Functions that are executed before and after main() in C</a></li>
<li><a>return statement vs exit() in main()</a></li>
<li><a>How to Count Variable Numbers of Arguments in C?, </a></li>
<li><a>What is evaluation order of function parameters in C?</a></li>
<li><a>Does C support function overloading?</a></li>
<li><a>How can we return multiple values from a function?</a></li>
<li><a>What is the purpose of a function prototype?</a></li>
<li><a>Static functions in C</a></li>
<li><a>exit(), abort() and assert()</a></li>
<li><a>Implicit return type int in C</a></li>
<li><a>What happens when a function is called before its declaration in C?</a></li>
</ul>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		