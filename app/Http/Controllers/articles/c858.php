<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Do not use sizeof for array parameters</h1>
				
			
			<p>Consider the below program. <span id="more-6594"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
void fun(int arr[])  
{
  int i;   

  /* sizeof should not be used here to get number 
    of elements in array*/
  int arr_size = sizeof(arr)/sizeof(arr[0]); /* incorrect use of siseof*/
  for (i = 0; i < arr_size; i++) 
  {  
    arr[i] = i;  /*executed only once */
  }
}

int main()
{
  int i;  
  int arr[4] = {0, 0 ,0, 0};
  fun(arr);
  
  /* use of sizeof is fine here*/
  for(i = 0; i < sizeof(arr)/sizeof(arr[0]); i++)
    printf(" %d " ,arr[i]);

  getchar();  
  return 0;
}    
</pre>
<p>Output: 0 0 0 0 on a <a>IA-32 machine</a>.</p>
<p>The function fun() receives an array parameter arr[] and tries to find out number of elements in arr[] using sizeof operator.<br />
In C, array parameters are treated as pointers (See <a>http://geeksforgeeks.org/?p=4088</a> for details).  So the expression sizeof(arr)/sizeof(arr[0]) becomes sizeof(int *)/sizeof(int) which results in 1 for IA 32 bit machine (size of int and int * is 4) and the for loop inside fun() is executed only once irrespective of the size of the array. </p>
<p>Therefore, sizeof should not be used to get number of elements in such cases. A separate parameter for array size (or length) should be passed to fun().  So the <strong>corrected program is:</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
void fun(int arr[], size_t arr_size)  
{
  int i;   
  for (i = 0; i < arr_size; i++) 
  {  
    arr[i] = i;  
  }
}

int main()
{
  int i;  
  int arr[4] = {0, 0 ,0, 0};
  fun(arr, 4);
  
  for(i = 0; i < sizeof(arr)/sizeof(arr[0]); i++)
    printf(" %d ", arr[i]);

  getchar();  
  return 0;
}    
</pre>
<p>Please write comments if you find anything incorrect in the above article or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		