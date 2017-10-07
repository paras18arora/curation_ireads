<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Initialization of variables sized arrays in C</h1>
				
			
			<p>The C99 standard allows variable sized arrays (see <a>this</a>). But, unlike the normal arrays, variable sized arrays cannot be initialized.  <span id="more-18912"></span></p>
<p>For example, the following program compiles and runs fine on a C99 compatible compiler.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>

int main()
{
  int M = 2;
  int arr[M][M];
  int i, j;
  for (i = 0; i < M; i++)
  {
    for (j = 0; j < M; j++)
    {
       arr[i][j] = 0;
       printf ("%d ", arr[i][j]);
    }
    printf("\n");
  }
  return 0;
}
</pre>
<p>Output:</p>
<pre>
0 0
0 0
</pre>
<p>But the following fails with compilation error.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>

int main()
{
  int M = 2;
  int arr[M][M] = {0}; // Trying to initialize all values as 0
  int i, j;
  for (i = 0; i < M; i++)
  {
    for (j = 0; j < M; j++)
       printf ("%d ", arr[i][j]);
    printf("\n");
  }
  return 0;
}
</pre>
<p>Output:</p>
<pre>
Compiler Error: variable-sized object may not be initialized
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		