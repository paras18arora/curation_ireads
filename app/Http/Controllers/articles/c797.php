<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Variables and Keywords in C</h1>
				
			
			<p>A <strong>variable </strong>in simple terms is a storage place which has some memory allocated to it. So basically a variable used to store some form of data. Different types of variables require different amounts of memory and have some specific set of operations which can be applied on them.<span id="more-17751"></span></p>
<p><strong>Variable Declaration:</strong><br />
A typical variable declaration is of the form:
</p><pre>
  type variable_name;
    or for multiple variables:
  type variable1_name, variable2_name, variable3_name;</pre>
<p>A variable name can consist of alphabets (both upper and lower case), numbers and the underscore ‘_' character. However, the name must not start with a number.</p>
<p><strong>Difference b/w variable declaration and definition</strong><br />
Variable declaration refers to the part where a variable is first declared or introduced before its first use. Variable definition is the part where the variable is assigned a memory location and a value.   Most of the times, variable declaration and definition are done together</p>
<p>See the following C program for better clarification:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
    // declaration and definition of variable 'a123'
    char a123 = 'a'; 

    // This is also both declaration and definition as 'b' is allocated
    // memory and assigned some garbage value.   
    float b;  

    // multiple declarations and definitions
    int _c, _d45, e; 

    // Let us print a variable
    printf("%c \n", a123);

    return 0;
}</pre>
<p>Output:
</p><pre>a</pre>
<p>Is it possible to have separate declaration and definition?<br />
It is possible in case of <a>extern variables</a> and <a>functions</a>.  See question 1 of <a>this </a>for more details.</p>
<p> <br />
 <br />
<strong>Keywords</strong> are specific reserved words in C each of which has a specific feature associated with it. Almost all of the words which help us use the functionality of the C language are included in the list of keywords. So you can imagine that the list of keywords is not going to be a small one!</p>
<p>There are a total of 32 keywords in C:
</p><pre>
   auto       break    case     char     const     continue
   default    do       double   else     enum      extern
   float      for      goto     if       int       long
   register   return   short    signed   sizeof    static
   struct     switch   typedef  union    unsigned  void
   volatile   while </pre>
<p>Most of these keywords have already been discussed in the various sub-sections of the <a>C language</a>, like Data Types, Storage Classes, Control Statements, Functions etc.</p>
<p>Let us discuss some of the other keywords which allow us to use the basic functionality of C:</p>
<p><strong><a>const</a></strong>:  const can be used to declare constant variables. Constant variables are variables which when initialized, can't change their value. Or in other words, the value assigned to them is unmodifiable.<br />
Syntax:
</p><pre>const data_type var_name = var_value; </pre>
<p>Note: Constant variables need to be compulsorily be initialized during their declaration itself. const keyword is also used with pointers. Please refer the <a>const qualifier in C</a> for understanding the same.</p>
<p><strong><a>extern</a></strong>: extern simply tells us that the variable is defined elsewhere and not within the same block where it is used. Basically, the value is assigned to it in a different block and this can be overwritten/changed in a different block as well. So an extern variable is nothing but a global variable initialized with a legal value where it is declared in order to be used elsewhere. It can be accessed within any function/block. Also, a normal global variable can me made extern as well by placing the ‘extern' keyword before its declaration/definition in any function/block. This basically signifies that we are not initializing a new variable but instead we are using/accessing the global variable only. The main purpose of using extern variables is that they can be accessed between two different files which are part of a large program.<br />
Syntax:
</p><pre>extern data_type var_name = var_value;</pre>
<p><strong>static</strong>: static keyword is used to declare static variables which are popularly used while writing programs in C language. Static variables have a property of preserving their value even after they are out of their scope! Hence, static variables preserve the value of their last use in their scope. So we can say that they are initialized only once and exist till the termination of the program. Thus, no new memory is allocated because they are not re-declared. Their scope is local to the function to which they were defined. Global static variables can be accessed anywhere in the program. By default, they are assigned the value 0 by the compiler.<br />
Syntax:
</p><pre>static data_type var_name = var_value;</pre>
<p><strong>void</strong>: void is a special data type only. But what makes it so special? void, as it literally means an empty data type. It means it has nothing or it holds no value. For example, when it is used as the return data type for a function, it simply represents that the function returns no value. Similarly, when it is added to a function heading, it represents that the function takes no arguments.<br />
Note: void also has a significant use with pointers. Please refer the <a>void pointer in C</a> for understanding the same.</p>
<p><strong>typedef</strong>: typedef is used to give a new name to an already existing or even a custom data type (like a structure). It comes in very handy at times, for example in a case when the name of the structure defined by you is very long or you just need a short-hand notation of a per-existing data type.</p>
<p>Let's implement the keywords which we have discussed above. See the following code which is a working example to demonstrate these keywords:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

// declaring and initializing an extern variable
extern int x = 9; 

// declaring and initializing a global variable
// simply int z; would have initialized z with
// the default value of a global variable which is 0
int z=10;

// using typedef to give a short name to long long int 
// very convenient to use now due to the short name
typedef long long int LL; 

// function which prints square of a no. and which has void as its
// return data type
void calSquare(int arg) 
{
    printf("The square of %d is %d\n",arg,arg*arg);
}

// Here void means function main takes no parameters
int main(void) 
{
    // declaring a constant variable, its value cannot be modified
    const int a = 32; 

    // declaring a  char variable
    char b = 'G';

    // telling the compiler that the variable z is an extern variable 
    // and has been defined elsewhere (above the main function)
    extern int z;

    LL c = 1000000;

    printf("Hello World!\n");

    // printing the above variables
    printf("This is the value of the constant variable 'a': %d\n",a);
    printf("'b' is a char variable. Its value is %c\n",b);
    printf("'c' is a long long int variable. Its value is %lld\n",c);
    printf("These are the values of the extern variables 'x' and 'z'"
    " respectively: %d and %d\n",x,z);

    // value of extern variable x modified
    x=2; 

    // value of extern variable z modified
    z=5; 

    // printing the modified values of extern variables 'x' and 'z'
    printf("These are the modified values of the extern variables"
    " 'x' and 'z' respectively: %d and %d\n",x,z);

    // using a static variable
    printf("The value of static variable 'y' is NOT initialized to 5 after the "
            "first iteration! See for yourself :)\n");

    while (x > 0)
    {
        static int y = 5;
        y++;
        // printing value at each iteration
        printf("The value of y is %d\n",y);
        x--;
    }

    // print square of 5
    calSquare(5); 

    printf("Bye! See you soon. :)\n");

    return 0;
}
</pre>
<p>Output:
</p><pre>Hello World
This is the value of the constant variable 'a': 32
'b' is a char variable. Its value is G
'c' is a long long int variable. Its value is 1000000
These are the values of the extern variables 'x' and 'z' respectively: 9 and 10
These are the modified values of the extern variables 'x' and 'z' respectively: 2 and 5
The value of static variable 'y' is NOT initialized to 5 after the first iteration! See for yourself :)
The value of y is 6
The value of y is 7
The square of 5 is 25
Bye! See you soon. :)</pre>
<p>This article is contributed by Ayush Jaggi. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		