<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What is return type of getchar(), fgetc() and getc() ?</h1>
				
			
			<p>In C, return type of getchar(), fgetc() and getc() is int (not char).  So it is recommended to assign the returned values of these functions to an integer type variable. <span id="more-5138"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
  char ch;  /* May cause problems */  
  while ((ch = getchar()) != EOF) 
  {
     putchar(ch);
  }
</pre>
<p>Here is a version that uses integer to compare the value of getchar().</p>
<pre class="brush: cpp; title: ; notranslate" title="">
  int in;  
  while ((in = getchar()) != EOF) 
  {
     putchar(in);
  }
</pre>
<p>See <a>this </a> for more details. </p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		