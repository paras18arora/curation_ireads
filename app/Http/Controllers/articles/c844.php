<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">To find sum of two numbers without using any operator</h1>
				
			
			<p>Write a C program to find sum of positive integers without using any operator. Only use of printf() is allowed.  No other library function can be used.<span id="more-22080"></span></p>
<p><strong>Solution</strong><br />
It's a trick question. We can use printf() to find sum of two numbers as printf() returns the number of characters printed. The <a>width field in printf()</a> can be used to find the sum of two numbers. We can use ‘*' which indicates the minimum width of output.  For example, in the statement "printf("%*d", width, num);", the specified ‘width' is substituted in place of *, and ‘num' is printed within the minimum width specified.  If number of digits in ‘num' is smaller than the specified ‘wodth', the output is padded with blank spaces. If number of digits are more, the output is printed as it is (not truncated). In the following program, add() returns sum of x and y. It prints 2 spaces within the width specified using x and y. So total characters printed is equal to sum of x and y. That is why add() returns x+y.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int add(int x, int y)
{
    return printf("%*c%*c",  x, ' ',  y, ' ');
}

int main()
{
    printf("Sum = %d", add(3, 4));
    return 0;
}
</pre>
<p>Output:</p>
<pre>
       Sum = 7
</pre>
<p>The output is seven spaces followed by "Sum = 7″.  We can avoid the leading spaces by using carriage return. Thanks to <a>krazyCoder</a> and <a>Sandeep </a>for suggesting this.  The following program prints output without any leading spaces.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int add(int x, int y)
{
    return printf("%*c%*c",  x, '\r',  y, '\r');
}

int main()
{
    printf("Sum = %d", add(3, 4));
    return 0;
}
</pre>
<p>Output:</p>
<pre>
Sum = 7
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		