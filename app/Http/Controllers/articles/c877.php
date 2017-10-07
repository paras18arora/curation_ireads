<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Interesting facts about switch statement in C</h1>
				
			
			<p>Switch  is a control statement that allows a value to change control of execution.<span id="more-125859"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Following is a simple program to demonstrate syntax of switch.
#include <stdio.h>
int main()
{
   int x = 2;
   switch (x)
   {
       case 1: printf("Choice is 1");
               break;
       case 2: printf("Choice is 2");
                break;
       case 3: printf("Choice is 3");
               break;
       default: printf("Choice other than 1, 2 and 3");
                break;  
   }
   return 0;
} 
</pre>
<p>Output:
</p><pre>Choice is 2</pre>
<p>Following are some interesting facts about switch statement.</p>
<p><em><strong>1) The expression used in switch must be  integral type ( int, char and enum).</strong></em> Any other type of expression is not allowed.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// float is not allowed in switch
#include <stdio.h>
int main()
{
   float x = 1.1;
   switch (x)
   {
       case 1.1: printf("Choice is 1");
                 break;
       default: printf("Choice other than 1, 2 and 3");
                break;  
   }
   return 0;
} 
</pre>
<p>Output:
</p><pre> Compiler Error: switch quantity not an integer</pre>
<p>In Java, String is also allowed in switch (See <a target="_blank">this</a>)</p>
<p><em><strong>2) All the statements following a matching case execute until a break statement is reached.</strong></em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
// There is no break in all cases
#include <stdio.h>
int main()
{
   int x = 2;
   switch (x)
   {
       case 1: printf("Choice is 1\n");
       case 2: printf("Choice is 2\n");
       case 3: printf("Choice is 3\n");
       default: printf("Choice other than 1, 2 and 3\n");
   }
   return 0;
} 
</pre>
<p>Output:
</p><pre>
Choice is 2
Choice is 3
Choice other than 1, 2 and 3</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
// There is no break in some cases
#include <stdio.h>
int main()
{
   int x = 2;
   switch (x)
   {
       case 1: printf("Choice is 1\n");
       case 2: printf("Choice is 2\n");
       case 3: printf("Choice is 3\n");
       case 4: printf("Choice is 4\n");
               break;
       default: printf("Choice other than 1, 2, 3 and 4\n");
                break;
   }
   printf("After Switch");
   return 0;
} </pre>
<p>Output:
</p><pre>Choice is 2
Choice is 3
Choice is 4
After Switch</pre>
<p><em><strong>3) The default block can be placed anywhere.</strong></em> The position of default doesn't matter, it is still executed if no match found.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// The default block is placed above other cases.
#include <stdio.h>
int main()
{
   int x = 4;
   switch (x)
   {
       default: printf("Choice other than 1 and 2");
                break;    	
       case 1: printf("Choice is 1");
               break;
       case 2: printf("Choice is 2");
                break;
   }
   return 0;
}  </pre>
<p>Output:
</p><pre>Choice other than 1 and 2</pre>
<p><em><strong>4) The integral expressions used in labels must be a constant expressions</strong></em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
// A program with variable expressions in labels
#include <stdio.h>
int main()
{
    int x = 2;
    int arr[] = {1, 2, 3};
    switch (x)
    {
        case arr[0]: printf("Choice 1\n"); 
        case arr[1]: printf("Choice 2\n");
        case arr[2]: printf("Choice 3\n");
    }
    return 0;
}
</pre>
<p>Output:
</p><pre>Compiler Error: case label does not reduce to an integer constant</pre>
<p><em><strong>5) The statements written above cases are never executed</strong></em> After the switch statement, the control transfers to the matching case, the statements executed before case are not executed. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Statements before all cases are never executed
#include <stdio.h>
int main()
{
   int x = 1;
   switch (x)
   {
       x = x + 1;  // This statement is not executed
       case 1: printf("Choice is 1");
               break;
       case 2: printf("Choice is 2");
                break;
       default: printf("Choice other than 1 and 2");
                break;                   
   }
   return 0;
} 
</pre>
<p>Output:
</p><pre>Choice is 1</pre>
<p><em><strong>6) Two case labels cannot have same value</strong></em></p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Program where two case labels have same value
#include <stdio.h>
int main()
{
   int x = 1;
   switch (x)
   {
       case 2: printf("Choice is 1");
               break;
       case 1+1: printf("Choice is 2");
                break;
   }
   return 0;
} 
</pre>
<p>Output:
</p><pre>Compiler Error: duplicate case value</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		