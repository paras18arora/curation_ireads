<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Storage for Strings in C</h1>
				
			
			<p>In C, a string can be referred either using a character pointer or as a character array. <span id="more-5328"></span></p>
<p><strong>Strings as character arrays</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
char str[4] = "GfG"; /*One extra for string terminator*/
/*    OR    */
char str[4] = {‘G', ‘f', ‘G', '\0'}; /* '\0' is string terminator */
</pre>
<p>When strings are declared as character arrays, they are stored like other types of arrays in C. For example, if str[] is an <a>auto variable</a> then string is stored in stack segment, if it's a global or static variable then stored in <a>data segment</a>, etc.</p>
<p><strong>Strings using character pointers</strong><br />
Using character pointer strings can be stored in two ways:</p>
<p><strong>1) </strong>Read only string in a shared segment.<br />
When string value is directly assigned to a pointer, in most of the compilers, it's stored in a read only block (generally in data segment) that is shared among functions.  </p>
<pre class="brush: cpp; title: ; notranslate" title="">  
  char *str  =  "GfG";  
</pre>
<p>In the above line "GfG" is stored in a shared read only location, but pointer str is stored in a read-write memory. You can change str to point something else but cannot change value at present str. So this kind of string should only be used when we don't want to modify string at a later stage in program.</p>
<p><strong>2)</strong> Dynamically allocated in heap segment.<br />
Strings are stored like other dynamically allocated things in C and can be shared among functions.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
  char *str;
  int size = 4; /*one extra for ‘\0'*/
  str = (char *)malloc(sizeof(char)*size);
  *(str+0) = 'G'; 
  *(str+1) = 'f';  
  *(str+2) = 'G';  
  *(str+3) = '\0';  
</pre>
<p> <br />
Let us see some examples to better understand above ways to store strings.</p>
<p><strong>Example 1 (Try to modify string) </strong><br />
The below program may crash (gives segmentation fault error) because the line *(str+1) = ‘n' tries to write a read only memory.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
 char *str; 
 str = "GfG";     /* Stored in read only part of data segment */
 *(str+1) = 'n'; /* Problem:  trying to modify read only memory */
 getchar();
 return 0;
}
</pre>
<p>Below program works perfectly fine as str[] is stored in writable stack segment.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
 char str[] = "GfG";  /* Stored in stack segment like other auto variables */
 *(str+1) = 'n';   /* No problem: String is now GnG */
 getchar();
 return 0;
}
</pre>
<p>Below program also works perfectly fine as data at str is stored in writable heap segment.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int size = 4;

  /* Stored in heap segment like other dynamically allocated things */
  char *str = (char *)malloc(sizeof(char)*size);
  *(str+0) = 'G'; 
  *(str+1) = 'f';  
  *(str+2) = 'G';    
  *(str+3) = '\0';  
  *(str+1) = 'n';  /* No problem: String is now GnG */
   getchar();
   return 0;
}     
</pre>
<p><strong>Example 2 (Try to return string from a function)</strong><br />
The below program works perfectly fine as the string is stored in a shared segment and data stored remains there even after return of getString()</p>
<pre class="brush: cpp; title: ; notranslate" title="">
char *getString()
{
  char *str = "GfG"; /* Stored in read only part of shared segment */

  /* No problem: remains at address str after getString() returns*/
  return str;  
}     

int main()
{
  printf("%s", getString());  
  getchar();
  return 0;
}
</pre>
<p>The below program alse works perfectly fine as the string is stored in heap segment and data stored in heap segment persists even after return of getString()</p>
<pre class="brush: cpp; title: ; notranslate" title="">
char *getString()
{
  int size = 4;
  char *str = (char *)malloc(sizeof(char)*size); /*Stored in heap segment*/
  *(str+0) = 'G'; 
  *(str+1) = 'f';  
  *(str+2) = 'G';
  *(str+3) = '\0';  
  
  /* No problem: string remains at str after getString() returns */    
  return str;  
}     
int main()
{
  printf("%s", getString());  
  getchar();
  return 0;
}
</pre>
<p>But, the below program may print some garbage data as string is stored in stack frame of function getString() and data may not be there after getString() returns.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
char *getString()
{
  char str[] = "GfG"; /* Stored in stack segment */

  /* Problem: string may not be present after getSting() returns */
  return str; 
}     
int main()
{
  printf("%s", getString());  
  getchar();
  return 0;
}
</pre>
<p>Please write comments if you find anything incorrect in the above article, or you want to share more information about storage of strings</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		