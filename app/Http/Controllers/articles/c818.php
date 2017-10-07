<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Returned values of printf() and scanf()</h1>
				
			
			<p>In C, printf() returns the number of <strong>characters </strong>successfully written on the output and scanf() returns number of <strong>items </strong>successfully read.<span id="more-5025"></span></p>
<p>For example, below program prints geeksforgeeks <strong>13</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  printf(" %d", printf("%s", "geeksforgeeks"));
  getchar();
}  
</pre>
<p>Irrespective of the string user enters, below program prints <strong>1</strong>.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  char a[50];  
  printf(" %d", scanf("%s", a));
  getchar();
}  
</pre>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		