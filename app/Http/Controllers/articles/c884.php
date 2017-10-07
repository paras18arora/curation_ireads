<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Does C support function overloading?</h1>
				
			
			<p>First of all, what is function overloading? Function overloading is a feature of a programming language that allows one to have many functions with same name but with different signatures.<span id="more-930"></span><br />
This feature is present in most of the Object Oriented Languages such as C++ and Java. But C (not Object Oriented Language) doesn't support this feature. However, one can achieve the similar functionality in C indirectly. One of the approach is as follows.</p>
<p>Have a void * type of pointer as an argument to the function. And another argument telling the actual data type of the first argument that is being passed.</p>
<pre>
   int foo(void * arg1, int arg2);
</pre>
<p>Suppose, arg2 can be interpreted as follows. 0 = Struct1 type variable, 1 = Struct2 type variable etc. Here Struct1 and Struct2 are user defined struct types.</p>
<p>While calling the function foo at different places…</p>
<pre>
    foo(arg1, 0);   /*Here, arg1 is pointer to struct type Struct1 variable*/
    foo(arg1, 1);    /*Here, arg1 is pointer to struct type Struct2 variable*/
</pre>
<p>Since the second argument of the foo keeps track the data type of the first type, inside the function foo, one can get the actual data type of the first argument by typecast accordingly. i.e. inside the foo function</p>
<pre class="brush: cpp; title: ; notranslate" title="">
if(arg2 == 0)
{
  struct1PtrVar = (Struct1 *)arg1;
}
else if(arg2 == 1)
{
  struct2PtrVar = (Struct2 *)arg1;
}
else
{
  /*Error Handling*/
}
</pre>
<p>There can be several other ways of implementing function overloading in C. But all of them will have to use pointers – the most powerful feature of C.<br />
In fact, it is said that without using the pointers, one can't use C efficiently & effectively in a real world program!</p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		