<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What are the default values of static variables in C?</h1>
				
			
			<p>In C, if an object that has static storage duration is not initialized explicitly, then:<span id="more-9061"></span><br />
— if it has pointer type, it is initialized to a  NULL pointer;<br />
— if it has arithmetic type, it is initialized to (positive or unsigned) zero;<br />
— if it is an aggregate, every member is initialized (recursively) according to these rules;<br />
— if it is a union, the first named member is initialized (recursively) according to these rules. </p>
<p>For example, following program prints:<br />
<em>Value of g = 0<br />
Value of sg = 0<br />
Value of s = 0<br />
</em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int g;  //g = 0, global objects have static storage duration
static int gs; //gs = 0, global static objects have static storage duration
int main()
{
  static int s; //s = 0, static objects have static storage duration
  printf("Value of g = %d", g);
  printf("\nValue of gs = %d", gs);
  printf("\nValue of s = %d", s);

  getchar();
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>
<p>References:<br />
The C99 standard</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		