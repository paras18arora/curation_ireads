<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">C Language Introduction</h1>
				
			
			<p>C is a procedural programming language. It was initially developed by Dennis Ritchie between 1969 and 1973. <span id="more-9087"></span>It was mainly developed as a system programming language to write operating system. The main features of C language include low-level access to memory, simple set of keywords, and clean style, these features make C language suitable for system programming like operating system or compiler development.<br />
Many later languages have borrowed syntax/features directly or indirectly from C language. Like syntax of Java, PHP, JavaScript and many other languages is mainly based on C language. C++ is nearly a superset of C language (There are few programs that may compile in C, but not in C++). </p>
<p><strong>Beginning with C programming:</strong></p>
<p><strong>1) Finding a Compiler:</strong><br />
Before we start C programming, we need to have a compiler to compile and run our programs. There are certain online compilers like <a>http://code.geeksfogeeks.org/</a>, <a target="_blank">http://ideone.com/</a> or <a target="_blank">http://codepad.org/</a> that can be used to start C without installing a compiler.<br />
<em><strong></strong></em></p>
<p><em><strong>Windows:</strong></em> There are many compilers available freely for compilation of C programs like <a target="_blank">Code Blocks </a> and <a target="_blank">Dev-CPP</a>.   We strongly recommend Code Blocks.</p>
<p><em><strong>Linux:</strong></em> For Linux, <a target="_blank">gcc </a>comes bundled with the linux,  Code Blocks can also be used with Linux.</p>
<p><strong>2) Writing first program:</strong><br />
Following is first program in C</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main(void)
{
    printf("GeeksQuiz");
    return 0;
}
</pre>
<p>Output:</p>
<pre>GeeksQuiz</pre>
<p>Let us analyze the program line by line.<br />
<em><strong>Line 1: [ #include <stdio.h> ]</strong></em> In a C program, all lines that start with <strong># </strong>are processed by <a target="_blank">preporcessor </a>which is a program invoked by the compiler. In a very basic term, <a target="_blank">preprocessor </a>takes a C program and produces another C program. The produced program has no lines starting with #, all such lines are processed by the preprocessor. In the above example, preprocessor copies the preprocessed code of stdio.h to our file. The .h files are called header files in C. These header files generally contain declaration of functions.  We need stdio.h for the function printf() used in the program.  </p>
<p><em><strong>Line 2 [ int main(void) ]</strong></em> There must to be starting point from where execution of compiled C program begins. In C, the execution typically begins with first line of main(). The void written in brackets indicates that the main doesn't take any parameter (See <a target="_blank">this </a>for more details). main() can be written to take parameters also. We will be covering that in future posts.<br />
The int written before main indicates return type of main(). The value returned by main indicates status of program termination. See <a target="_blank">this </a>post for more details on return type.</p>
<p><em><strong>Line 3 and 6: [ { and } ]</strong></em> In C language, a pair of curly brackets define a scope and mainly used in functions and control statements like if, else, loops.  All functions must start and end with curly brackets.  </p>
<p><em><strong>Line 4 [ printf("GeeksQuiz"); ]</strong></em> <a target="_blank">printf()</a> is a standard library function to print something on standard output.  The semiolon at the end of printf indicates line termination. In C, semicolon is always used to indicate end of statement. </p>
<p><em><strong>Line 5 [ return 0; ]</strong></em> The return statement returns the value from main(). The returned value may be used by operating system to know termination status of your program.  The value 0 typically means successful termination. </p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		