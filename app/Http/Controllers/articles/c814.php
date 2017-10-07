<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Const Qualifier in C</h1>
				
			
			<p>The qualifier const can be applied to the declaration of any variable to specify that its value will not be changed ( Which depends upon where const variables are stored, we may change value of const variable by using pointer ).<span id="more-14516"></span> The result is implementation-defined if an attempt is made to change a const (See <a>this </a>forum topic).</p>
<p><strong>1) Pointer to variable.</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int *ptr;
</pre>
<p>We can change the value of ptr and we can also change the value of object ptr pointing to. Pointer and value pointed by pointer both are stored in read-write area. See the following code fragment.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
int main(void)
{
    int i = 10;
    int j = 20;
    int *ptr = &i;        /* pointer to integer */
    printf("*ptr: %d\n", *ptr);
 
    /* pointer is pointing to another variable */
    ptr = &j;
    printf("*ptr: %d\n", *ptr);
 
    /* we can change value stored by pointer */
    *ptr = 100;
    printf("*ptr: %d\n", *ptr);
 
    return 0;
}
</pre>
<p>Output:</p>
<pre>
    *ptr: 10
    *ptr: 20
    *ptr: 100
</pre>
<p><br />
<strong><br />
2) Pointer to constant.</strong><br />
Pointer to constant can be declared in following two ways.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
const int *ptr;
</pre>
<p>or </p>
<pre class="brush: cpp; title: ; notranslate" title="">
int const *ptr;
</pre>
<p>We can change pointer to point to any other integer variable, but cannot change value of object (entity) pointed using pointer ptr. Pointer is stored in read-write area (stack in present case). Object pointed may be in read only or read write area. Let us see following examples.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h> 
int main(void)
{
    int i = 10;   
    int j = 20;
    const int *ptr = &i;    /* ptr is pointer to constant */
 
    printf("ptr: %d\n", *ptr); 
    *ptr = 100;        /* error: object pointed cannot be modified
                     using the pointer ptr */
 
    ptr = &j;          /* valid */ 
    printf("ptr: %d\n", *ptr);
 
    return 0;
}
</pre>
<p>Output:</p>
<pre>
 error: assignment of read-only location ‘*ptr'
</pre>
<p>Following is another example where variable i itself is constant.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h> 

int main(void)
{  
    int const i = 10;    /* i is stored in read only area*/
    int j = 20;

    int const *ptr = &i;        /* pointer to integer constant. Here i 
                                 is of type "const int", and &i is of 
                                 type "const int *".  And p is of type                            
                                "const int", types are matching no issue */ 

    printf("ptr: %d\n", *ptr); 

    *ptr = 100;        /* error */ 

    ptr = &j;          /* valid. We call it as up qualification. In 
                         C/C++, the type of "int *" is allowed to up 
                         qualify to the type "const int *". The type of 
                         &j is "int *" and is implicitly up qualified by 
                         the compiler to "cons tint *" */ 

    printf("ptr: %d\n", *ptr);

    return 0;
}
</pre>
<p>Output:</p>
<pre>
 error: assignment of read-only location ‘*ptr'
</pre>
<p>Down qualification is not allowed in C++ and may cause warnings in C.  Following is another example with down qualification.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

int main(void)
{
    int i = 10;
    int const j = 20;

    /* ptr is pointing an integer object */
    int *ptr = &i; 

    printf("*ptr: %d\n", *ptr); 

    /* The below assignment is invalid in C++, results in error 
       In C, the compiler *may* throw a warning, but casting is 
       implicitly allowed */
    ptr = &j;

    /* In C++, it is called 'down qualification'. The type of expression 
       &j is "const int *" and the type of ptr is "int *". The 
       assignment "ptr = &j" causes to implicitly remove const-ness 
       from the expression &j. C++ being more type restrictive, will not 
       allow implicit down qualification. However, C++ allows implicit 
       up qualification. The reason being, const qualified identifiers 
       are bound to be placed in read-only memory (but not always). If 
       C++ allows above kind of assignment (ptr = &j), we can use 'ptr' 
       to modify value of j which is in read-only memory. The 
       consequences are implementation dependent, the program may fail 
       at runtime. So strict type checking helps clean code. */

    printf("*ptr: %d\n", *ptr);

    return 0;
} 

// Reference http://www.dansaks.com/articles/1999-02%20const%20T%20vs%20T%20const.pdf

// More interesting stuff on C/C++ @ http://www.dansaks.com/articles.htm
</pre>
<p><br />
<strong>3) Constant pointer to variable.</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int *const ptr;
</pre>
<p>Above declaration is constant pointer to integer variable, means we can change value of object pointed by pointer, but cannot change the pointer to point another variable. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
 
int main(void)
{
   int i = 10;
   int j = 20;
   int *const ptr = &i;    /* constant pointer to integer */
 
   printf("ptr: %d\n", *ptr);
 
   *ptr = 100;    /* valid */
   printf("ptr: %d\n", *ptr);
 
   ptr = &j;        /* error */
   return 0;
}
</pre>
<p>Output:</p>
<pre>
 error: assignment of read-only variable ‘ptr'
</pre>
<p><br />
<strong>4) constant pointer to constant</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
const int *const ptr;
</pre>
<p>Above declaration is constant pointer to constant variable which means we cannot change value pointed by pointer as well as we cannot point the pointer to other variable. Let us see with example. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>
 
int main(void)
{
    int i = 10;
    int j = 20;
    const int *const ptr = &i;        /* constant pointer to constant integer */
 
    printf("ptr: %d\n", *ptr);
 
    ptr = &j;            /* error */
    *ptr = 100;        /* error */
 
    return 0;
}
</pre>
<p>Output:</p>
<pre>
     error: assignment of read-only variable ‘ptr'
     error: assignment of read-only location ‘*ptr'
</pre>
<p>This article is compiled by "<strong>Narendra Kangralkar</strong>". Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		