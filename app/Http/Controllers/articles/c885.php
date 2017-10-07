<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">How can I return multiple values from a function?</h1>
				
			
			<p>We all know that a function in C can return only one value. So how do we achieve the purpose of returning multiple values.<br />
Well, first take a look at the declaration of a function.<br />
<span id="more-955"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
  int foo(int arg1, int arg2);
</pre>
<p>So we can notice here that our interface to the function is through arguments and return value only. (Unless we talk about modifying the globals inside the function)</p>
<p>Let us take a deeper look…Even though a function can return only one value but that value can be of pointer type. That's correct, now you're speculating right!<br />
We can declare the function such that, it returns a structure type user defined variable or a pointer to it . And by the property of a structure, we know that a structure in C can hold multiple values of asymmetrical types (i.e. one int variable, four char variables, two float variables and so on…)</p>
<p>If we want the function to return multiple values of same data types, we could return the pointer to array of that data types.</p>
<p>We can also make the function return multiple values by using the arguments of the function. How? By providing the pointers as arguments.</p>
<p>Usually, when a function needs to return several values, we use one pointer in return instead of several pointers as argumentss.</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		