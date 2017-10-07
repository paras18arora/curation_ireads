<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Why C treats array parameters as pointers?</h1>
				
			
			<p>In C, array parameters are treated as pointers. The following two definitions of foo() look different, but to the compiler they mean exactly the same thing. <span id="more-4088"></span> It's preferable to use whichever syntax is more accurate for readability. If the pointer coming in really is the base address of a whole array, then we should use [ ].</p>
<pre class="brush: cpp; title: ; notranslate" title="">
void foo(int arr_param[]) 
{

  /* Silly but valid. Just changes the local pointer */
  arr_param = NULL; 
}

void foo(int *arr_param) 
{

  /* ditto */
  arr_param = NULL; 
}
</pre>
<p><strong>Array parameters treated as pointers because of efficiency</strong>. It is inefficient to copy the array data in terms of both memory and time; and most of the times, when we pass an array our intention is to just tell the array we interested in, not to create a copy of the array.</p>
<p>Asked by Shobhit</p>
<p>References:<br />
<a>http://cslibrary.stanford.edu/101/EssentialC.pdf</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		