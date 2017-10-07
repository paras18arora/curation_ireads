<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What is evaluation order of function parameters in C?</h1>
				
			
			<p>It is compiler dependent in C. It is never safe to depend on the order of evaluation of side effects. For example, a function call like below may very well behave differently from one compiler to another:<span id="more-8006"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
   void func (int, int);
     
   int i = 2;
   func (i++, i++);
</pre>
<p>There is no guarantee (in either the C or the C++ standard language definitions) that the increments will be evaluated in any particular order. Either increment might happen first. func might get the arguments `2, 3′, or it might get `3, 2′, or even `2, 2′. </p>
<p>Source: <a>http://gcc.gnu.org/onlinedocs/gcc/Non_002dbugs.html</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		