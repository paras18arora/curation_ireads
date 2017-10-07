<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Write one line functions for strcat() and strcmp()</h1>
				
			
			<p>Recursion can be used to do both tasks in one line. Below are one line implementations for stracat() and strcmp(). <span id="more-7705"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">

/* my_strcat(dest, src) copies data of src to dest.  To do so, it first reaches end of the string dest using recursive calls my_strcat(++dest, src).  Once end of dest is reached, data is copied using 
(*dest++ = *src++)?  my_strcat(dest, src). */
void my_strcat(char *dest, char *src)
{
  (*dest)? my_strcat(++dest, src): (*dest++ = *src++)? my_strcat(dest, src): 0 ;
}

/* driver function to test above function */
int main()
{
  char dest[100] = "geeksfor";
  char *src = "geeks";
  my_strcat(dest, src);
  printf(" %s ", dest);
  getchar();
}    
</pre>
<p>The function my_strcmp() is simple compared to my_strcmp().</p>
<pre class="brush: cpp; title: ; notranslate" title="">
/* my_strcmp(a, b) returns 0 if strings a and b are same, otherwise 1.   It recursively increases a and b pointers. At any point if *a is not equal to *b then 1 is returned.  If we reach end of both strings at the same time then 0 is returned. */
int my_strcmp(char *a, char *b)
{
  return (*a == *b && *b == '\0')? 0 : (*a == *b)? my_strcmp(++a, ++b): 1;
} 

/* driver function to test above function */
int main()
{
  char *a = "geeksforgeeks";
  char *b = "geeksforgeeks";
  if(my_strcmp(a, b) == 0)
     printf(" String are same ");
  else  
     printf(" String are not same ");  

  getchar();
  return 0;
}    
</pre>
<p>The above functions do very basic string concatenation and string comparison.  These functions do not provide same functionality as standard library functions. </p>
<p>Asked by <a>geek4u</a></p>
<p>Please write comments if you find the above code incorrect, or find better ways to solve the same problem.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		