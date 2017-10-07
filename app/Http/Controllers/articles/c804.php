<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Data Types in C</h1>
				
			
			<p>Each variable in C has an associated data type. Each data type requires different amounts of memory and has some specific operations which can be performed over it. Let us briefly describe them one by one:</p>
<p>Following are the examples of some very common data types used in C:</p>
<p><strong>char:</strong> The most basic data type in C. It stores a single character and requires a single byte of memory in almost all compilers.<br />
<strong>int: </strong>As the name suggests, an int variable is used to store an integer.<br />
<strong>float:</strong> It is used to store decimal numbers (numbers with floating point value) with single precision.<br />
<strong>double:</strong> It is used to store decimal numbers (numbers with floating point value) with double precision.</p>
<p>Different data types also have different ranges upto which they can store numbers. These ranges may vary from compiler to compiler. Below is list of ranges along with the memory requirement and format specifiers on 32 bit gcc compiler.</p>
<pre>
Data Type             Memory (bytes)          Range                      Format Specifier
short int                   2          -32,768 to 32,767                       %hd
unsigned short int          2           0 to 65,535                            %hu
unsigned int                4           0 to 4,294,967,295                     %u
int                         4          -2,147,483,648 to 2,147,483,647         %d
long int                    4          -2,147,483,648 to 2,147,483,647         %ld
unsigned long int           4           0 to 4,294,967,295                     %lu
long long int               8          -(2^63) to (2^63)-1                     %lld
unsigned long long int      8           0 to 18,446,744,073,709,551,615        %llu
signed char                 1          -128 to 127                             %c 
unsigned char               1           0 to 255                               %c
float                       4                                                  %f
double                      8                                                  %lf
long double                 12                                                 %Lf
</pre>
<p>We can use the<a> sizeof() operator </a>to check the size of a variable. See the following C program for the usage of the various data types:</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main()
{
    int a = 1;
    char b ='G';
    double c = 3.14;
    printf("Hello World!\n");

    //printing the variables defined above along with their sizes
    printf("Hello! I am a character. My value is %c and "
           "my size is %lu byte.\n", b,sizeof(char));
    //can use sizeof(b) above as well

    printf("Hello! I am an integer. My value is %d and "
           "my size is %lu  bytes.\n", a,sizeof(int));
    //can use sizeof(a) above as well

    printf("Hello! I am a double floating point variable."
           " My value is %lf and my size is %lu bytes.\n",c,sizeof(double));
    //can use sizeof(c) above as well

    printf("Bye! See you soon. :)\n");

    return 0;
}
</pre>
<p>Output:</p>
<pre>Hello World!
Hello! I am a character. My value is G and my size is 1 byte.
Hello! I am an integer. My value is 1 and my size is 4  bytes.
Hello! I am a double floating point variable. My value is 3.140000 and my size i
s 8 bytes.
Bye! See you soon. :)</pre>
<p><a>Quiz on Data Types in C</a></p>
<p>This article is contributed by Ayush Jaggi. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		