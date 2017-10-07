<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">C function to Swap strings</h1>
				
			
			<p>Let us consider the below program.  <span id="more-5276"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
void swap(char *str1, char *str2)
{
  char *temp = str1;
  str1 = str2;
  str2 = temp;
}  
 
int main()
{
  char *str1 = "geeks";
  char *str2 = "forgeeks";
  swap(str1, str2);
  printf("str1 is %s, str2 is %s", str1, str2);
  getchar();
  return 0;
}
</pre>
<p>Output of the program is<em> str1 is geeks, str2 is forgeeks</em>. So the above swap() function doesn't swap strings. The function just changes local pointer variables and the changes are not reflected outside the function.</p>
<p>Let us see the correct ways for swapping strings:</p>
<p><strong>Method 1(Swap Pointers)</strong><br />
If you are <a>using character pointer for strings </a>(not arrays) then change str1 and str2 to point each other's data. i.e., swap pointers.  In a function, if we want to change a pointer (and obviously we want changes to be reflected outside the function) then we need to pass a pointer to the pointer.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>

/* Swaps strings by swapping pointers */ 
void swap1(char **str1_ptr, char **str2_ptr)
{
  char *temp = *str1_ptr;
  *str1_ptr = *str2_ptr;
  *str2_ptr = temp;
}  
 
int main()
{
  char *str1 = "geeks";
  char *str2 = "forgeeks";
  swap1(&str1, &str2);
  printf("str1 is %s, str2 is %s", str1, str2);
  getchar();
  return 0;
}
</pre>
<p>This method cannot be applied if strings are stored using character arrays.</p>
<p><strong>Method 2(Swap Data)</strong><br />
If you are <a>using character arrays to store strings</a> then preferred way is to swap the data of both arrays. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include<stdio.h>
#include<string.h>
#include<stdlib.h>
 
/* Swaps strings by swapping data*/
void swap2(char *str1, char *str2)
{
  char *temp = (char *)malloc((strlen(str1) + 1) * sizeof(char));
  strcpy(temp, str1);
  strcpy(str1, str2);
  strcpy(str2, temp);
  free(temp);
}  
 
int main()
{
  char str1[10] = "geeks";
  char str2[10] = "forgeeks";
  swap2(str1, str2);
  printf("str1 is %s, str2 is %s", str1, str2);
  getchar();
  return 0;
}
</pre>
<p>This method cannot be applied for strings stored in read only block of memory. </p>
<p><br />
Please write comments if you find anything incorrect in the above article, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		