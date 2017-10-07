<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How to pass a 2D array as a parameter in C?</h1>
				
			
			<p>This post is an extension of <a target="_blank">How to dynamically allocate a 2D array in C?</a> <span id="more-128405"></span></p>
<p>A one dimensional array can be easily passed as a pointer, but syntax for passing a 2D array to a function can be difficult to remember.    One important thing for passing multidimensional arrays is, first array dimension does not have to be  specified. The second (and any subsequent) dimensions must be given</p>
<p><strong>1) When second dimension is available globally (either as a macro or as a global constant).</strong></p>
<pre class="brush: cpp; highlight: [2,4,15]; title: ; notranslate" title="">
#include <stdio.h>
const int n = 3;

void print(int arr[][n], int m)
{
    int i, j;
    for (i = 0; i < m; i++)
      for (j = 0; j < n; j++)
        printf("%d ", arr[i][j]);
}

int main()
{
    int arr[][3] = {{1, 2, 3}, {4, 5, 6}, {7, 8, 9}};
    print(arr, 3);
    return 0;
}
</pre>
<p>Output:
</p><pre>1 2 3 4 5 6 7 8 9</pre>
<p>The above method is fine if second dimension is fixed and is not user specified.  The following methods handle cases when second dimension can also change.</p>
<p><strong>1) If compiler is C99 compatible</strong><br />
From C99, C language supports variable sized arrays to be passed simply by specifying the variable dimensions (See <a>this </a>for an example run)</p>
<pre class="brush: cpp; highlight: [4,5,17]; title: ; notranslate" title="">
// The following program works only if your compiler is C99 compatible.
#include <stdio.h>

// n must be passed before the 2D array
void print(int m, int n, int arr[][n])
{
    int i, j;
    for (i = 0; i < m; i++)
      for (j = 0; j < n; j++)
        printf("%d ", arr[i][j]);
}

int main()
{
    int arr[][3] = {{1, 2, 3}, {4, 5, 6}, {7, 8, 9}};
    int m = 3, n = 3;
    print(m, n, arr);
    return 0;
}</pre>
<p>Output on a C99 compatible compiler:
</p><pre>1 2 3 4 5 6 7 8 9</pre>
<p>If compiler is not C99 compatible, then we can use one of the following methods to pass a variable sized 2D array.</p>
<p><strong>2) Using a single pointer</strong><br />
In this method, we must typecast the 2D array when passing to function.</p>
<pre class="brush: cpp; highlight: [2,7,14]; title: ; notranslate" title="">
#include <stdio.h>
void print(int *arr, int m, int n)
{
    int i, j;
    for (i = 0; i < m; i++)
      for (j = 0; j < n; j++)
        printf("%d ", *((arr+i*n) + j));
}

int main()
{
    int arr[][3] = {{1, 2, 3}, {4, 5, 6}, {7, 8, 9}};
    int m = 3, n = 3;
    print((int *)arr, m, n);
    return 0;
}
</pre>
<p>Output:
</p><pre>1 2 3 4 5 6 7 8 9</pre>
<p><strong>3) Using an array of pointers or double pointer</strong><br />
In this method also, we must typecast the 2D array when passing to function. </p>
<pre class="brush: cpp; highlight: [2,3,8,16]; title: ; notranslate" title="">
#include <stdio.h>
// Same as "void print(int **arr, int m, int n)"
void print(int *arr[], int m, int n)
{
    int i, j;
    for (i = 0; i < m; i++)
      for (j = 0; j < n; j++)
        printf("%d ", *((arr+i*n) + j));
}

int main()
{
    int arr[][3] = {{1, 2, 3}, {4, 5, 6}, {7, 8, 9}};
    int m = 3;
    int n = 3;
    print((int **)arr, m, n);
    return 0;
}</pre>
<p>Output:
</p><pre>1 2 3 4 5 6 7 8 9</pre>
<p>Remember that <a target="_blank">in C, array parameters are treated as pointers</a>, so an array of pointers or a double pointer are same when they are parameters. For dynamic allocation, we had 2 different methods for array of pointers and double pointer. In <a target="_blank">dynamic allocation post,</a> we had 2 different methods for array of pointers and double pointer.</p>
<p>Methods 2 and 3 can also be useful on a C99 compatible compiler, in a situation when 2D array is dynamically allocated using malloc.</p>
<p><strong>References:</strong><br />
<a target="_blank">http://www.eskimo.com/~scs/cclass/int/sx9a.html</a></p>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		