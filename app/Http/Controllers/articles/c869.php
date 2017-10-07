<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How to dynamically allocate a 2D array in C?</h1>
				
			
			<p>Following are different ways to create a 2D array on heap (or dynamically allocate a 2D array).<span id="more-128407"></span></p>
<p>In the following examples, we have considered ‘<strong>r</strong>‘ as number of rows, ‘<strong>c</strong>‘ as number of columns and we created a 2D array with r = 3, c = 4 and following values
</p><pre>
  1  2  3  4
  5  6  7  8
  9  10 11 12 </pre>
<p><strong>1) Using a single pointer:</strong><br />
A simple way is to allocate memory block of size r*c and access elements using simple pointer arithmetic. </p>
<pre class="brush: cpp; highlight: [7,12]; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>

int main()
{
    int r = 3, c = 4;
    int *arr = (int *)malloc(r * c * sizeof(int));

    int i, j, count = 0;
    for (i = 0; i <  r; i++)
      for (j = 0; j < c; j++)
         *(arr + i*c + j) = ++count;

    for (i = 0; i <  r; i++)
      for (j = 0; j < c; j++)
         printf("%d ", *(arr + i*c + j));

   /* Code for further processing and free the 
      dynamically allocated memory */
  
   return 0;
}
</pre>
<p>Output:
</p><pre>1 2 3 4 5 6 7 8 9 10 11 12</pre>
<p><strong>2) Using an array of pointers</strong><br />
We can create an array of pointers of size r. Note that from C99, C language allows variable sized arrays.  After creating an array of pointers, we can dynamically allocate memory for every row.</p>
<pre class="brush: cpp; highlight: [8,9,10,16]; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>

int main()
{
    int r = 3, c = 4, i, j, count;

    int *arr[r];
    for (i=0; i<r; i++)
         arr[i] = (int *)malloc(c * sizeof(int));

    // Note that arr[i][j] is same as *(*(arr+i)+j)
    count = 0;
    for (i = 0; i <  r; i++)
      for (j = 0; j < c; j++)
         arr[i][j] = ++count; // Or *(*(arr+i)+j) = ++count

    for (i = 0; i <  r; i++)
      for (j = 0; j < c; j++)
         printf("%d ", arr[i][j]);

    /* Code for further processing and free the 
      dynamically allocated memory */

   return 0;
}
</pre>
<p>Output:
</p><pre>1 2 3 4 5 6 7 8 9 10 11 12</pre>
<p><strong>3) Using pointer to a pointer</strong><br />
We can create an array of pointers also dynamically using a double pointer.  Once we have an array pointers allocated dynamically, we can dynamically allocate memory and for every row like method 2.</p>
<pre class="brush: cpp; highlight: [8,9,10,16]; title: ; notranslate" title="">
#include <stdio.h>
#include <stdlib.h>

int main()
{
    int r = 3, c = 4, i, j, count;

    int **arr = (int **)malloc(r * sizeof(int *));
    for (i=0; i<r; i++)
         arr[i] = (int *)malloc(c * sizeof(int));

    // Note that arr[i][j] is same as *(*(arr+i)+j)
    count = 0;
    for (i = 0; i <  r; i++)
      for (j = 0; j < c; j++)
         arr[i][j] = ++count;  // OR *(*(arr+i)+j) = ++count

    for (i = 0; i <  r; i++)
      for (j = 0; j < c; j++)
         printf("%d ", arr[i][j]);

   /* Code for further processing and free the 
      dynamically allocated memory */

   return 0;
}
</pre>
<p>Output:
</p><pre>1 2 3 4 5 6 7 8 9 10 11 12</pre>
<p><strong>4) Using double pointer and one malloc call for all rows</strong></p>
<pre class="brush: cpp; highlight: [10,11,12,13,14]; title: ; notranslate" title="">
#include<stdio.h>
#include<stdlib.h>
 
int main()
{
    int r=3, c=4;
    int **arr;
    int count = 0,i,j;
 
    arr  = (int **)malloc(sizeof(int *) * r);
    arr[0] = (int *)malloc(sizeof(int) * c * r);

    for(i = 0; i < r; i++)
        arr[i] = (*arr + c * i);
 
    for (i = 0; i < r; i++)
        for (j = 0; j < c; j++)
            arr[i][j] = ++count;  // OR *(*(arr+i)+j) = ++count
 
    for (i = 0; i <  r; i++)
        for (j = 0; j < c; j++)
            printf("%d ", arr[i][j]);
 
    return 0;
}
</pre>
<p>Output:
</p><pre>1 2 3 4 5 6 7 8 9 10 11 12</pre>
<p>Thanks to <a>Trishansh Bhardwaj</a> for suggesting this 4th method.</p>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		