<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Initialization of a multidimensional arrays in C/C++</h1>
				
			
			<p>In C/C++, initialization of a multidimensional arrays can have left most dimension as optional.  Except the left most dimension, all other dimensions must be specified.<br />
 <span id="more-8733"></span><br />
For example, following program fails in compilation because two dimensions are not specified.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
int main()
{
  int a[][][2] = { {{1, 2}, {3, 4}}, 
                   {{5, 6}, {7, 8}}
                 };  // error
  printf("%d", sizeof(a)); 
  getchar();
  return 0;
}
</pre>
<p>Following 2 programs work without any error.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// Program 1
#include<stdio.h>
int main()
{
  int a[][2] = {{1,2},{3,4}}; // Works
  printf("%d", sizeof(a)); // prints 4*sizeof(int)
  getchar();
  return 0;
}
</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
// Program 2
#include<stdio.h>
int main()
{
  int a[][2][2] = { {{1, 2}, {3, 4}}, 
                     {{5, 6}, {7, 8}}
                   }; // Works
  printf("%d", sizeof(a)); // prints 8*sizeof(int)
  getchar();
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		