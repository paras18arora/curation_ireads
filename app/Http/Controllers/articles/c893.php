<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">An Uncommon representation of array elements</h1>
				
			
			<p>Consider the below program. <span id="more-5388"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main( )
{
  int arr[2] = {0,1};
  printf("First Element = %d\n",arr[0]);
  getchar();
  return 0;
}
</pre>
<p>Pretty Simple program.. huh… Output will be 0.</p>
<p>Now if you replace <em>arr[0] </em>with <em>0[arr]</em>, the output would be same. Because compiler converts the array operation in pointers before accessing the array elements.</p>
<p>e.g. <em>arr[0]</em> would be <em>*(arr + 0) </em>and therefore 0[arr] would be <em>*(0 + arr)</em> and you know that both <em>*(arr + 0)</em> and <em>*(0 + arr) </em>are same.</p>
<p>Please write comments if you find anything incorrect in the above article.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		