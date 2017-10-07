<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Interesting Facts about Bitwise Operators in C</h1>
				
			
			<p>In C, following 6 operators are bitwise operators (work at bit-level)<span id="more-126943"></span> </p>
<p><strong>& (bitwise AND)</strong> Takes two numbers as operand and does AND on every bit of two numbers.   The result of AND is 1 only if both bits are 1. </p>
<p><strong>| (bitwise OR)</strong> Takes two numbers as operand and does OR on every bit of two numbers.   The result of OR is 1 any of the two bits is 1.</p>
<p><strong>^ (bitwise XOR)</strong> Takes two numbers as operand and does XOR on every bit of two numbers.   The result of XOR is 1 if the two bits are different.</p>
<p><strong><< (left shift)</strong> Takes two numbers, left shifts the bits of first operand, the second operand decides the number of places to shift.</p>
<p><strong>>> (right shift)</strong> Takes two numbers, right  shifts the bits of first operand, the second operand decides the number of places to shift.</p>
<p><strong>~ (bitwise NOT)</strong> Takes one number and inverts all bits of it</p>
<p>Following is example C program.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* C Program to demonstrate use of bitwise operators */
#include<stdio.h>
int main()
{
    unsigned char a = 5, b = 9; // a = 4(00000101), b = 8(00001001)
    printf("a = %d, b = %d\n", a, b);
    printf("a&b = %d\n", a&b); // The result is 00000001
    printf("a|b = %d\n", a|b);  // The result is 00001101
    printf("a^b = %d\n", a^b); // The result is 00001100
    printf("~a = %d\n", a = ~a);   // The result is 11111010
    printf("b<<1 = %d\n", b<<1);  // The result is 00010010 
    printf("b>>1 = %d\n", b>>1);  // The result is 00000100 
    return 0;
}
</pre>
<p>Output:
</p><pre>a = 5, b = 9
a&b = 1
a|b = 13
a^b = 12
~a = 250
b<<1 = 18
b>>1 = 4
</pre>
<p>Following are interesting facts about bitwise operators.</p>
<p><strong>1) The left shift and right shift operators should not be used for negative numbers</strong>  The result of << and >> is undefined behabiour if any of the operands is a negative number.  For example results of both -1 << 1 and 1 << -1 is undefined.  Also, if the number is shifted more than the size of integer, the behaviour is undefined. For example, 1 << 33 is undefined if integers are stored using 32 bits. See <a target="_blank">this</a> for more details.</p>
<p><strong>2) The bitwise XOR operator is the most useful operator from technical interview perspective.</strong>  It is used in many problems.  A simple example could be "Given a set of numbers where all elements occur even number of times except one number, find the odd occuring number" This problem can be efficiently solved by just doing XOR of all numbers.  </p>
<pre class="brush: cpp; highlight: [1,2,3,4,5,6,7]; title: ; notranslate" title="">
// Function to return the only odd occurring element
int findOdd(int arr[], int n) {
   int res = 0, i;
   for (i = 0; i < n; i++)
     res ^= arr[i];
   return res;
}

int main(void) {
   int arr[] = {12, 12, 14, 90, 14, 14, 14};
   int n = sizeof(arr)/sizeof(arr[0]);
   printf ("The odd occurring element is %d ", findOdd(arr, n));
   return 0;
}
// Output: The odd occurring element is 90
</pre>
<p>The following are many other interesting problems which can be used using XOR operator.<br />
<a target="_blank">Find the Missing Number</a>, <a target="_blank">swap two numbers without using a temporary variable</a>, <a target="_blank">A Memory Efficient Doubly Linked List</a>, and <a target="_blank">Find the two non-repeating elements</a>. There are many more (See <a target="_blank">this</a>, <a target="_blank">this</a>, <a target="_blank">this</a>, <a target="_blank">this</a>, <a target="_blank">this</a> and <a target="_blank">this</a>)</p>
<p><strong>3) The bitwise operators should not be used in-place of logical operators.</strong><br />
The result of logical operators (&&, || and !) is either 0 or 1, but bitwise operators return an integer value. Also, the logical operators consider any non-zero operand as 1.  For example consider the following program, the results of & and && are different for same operands.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
   int x = 2, y = 5;
   (x & y)? printf("True ") : printf("False ");
   (x && y)? printf("True ") : printf("False ");
   return 0;
}
// Output: False True
</pre>
<p><strong>4) The left-shift and right-shift operators are equivalent to multiplication and division by 2 respectively.</strong><br />
As mentioned in point 1, it works only if numbers are positive. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
   int x = 19;
   printf ("x << 1 = %d\n", x << 1);
   printf ("x >> 1 = %d\n", x >> 1);
   return 0;
}
// Output: 38 9
</pre>
<p><strong>5) The & operator can be used to quickly check if a number is odd or even</strong><br />
The value of expression (x & 1) would be non-zero only if x is odd, otherwise the value would be zero.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
   int x = 19;
   (x & 1)? printf("Odd"): printf("Even");
   return 0;
}
// Output: Odd
</pre>
<p><strong>6) The ~ operator should be used carefully</strong><br />
The result of ~ operator on a small number can be a big number if result is stored in a unsigned variable.  And result may be negative number if result is stored in signed variable (assuming that the negative numbers are stored in 2's complement form where leftmost bit is the sign bit)</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Note that the output of following program is compiler dependent
int main()
{
   unsigned int x = 1;
   printf("Signed Result %d \n", ~x);
   printf("Unsigned Result %ud \n", ~x);
   return 0;
}
/* Output: 
Signed Result -2 
Unsigned Result 4294967294d */
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		