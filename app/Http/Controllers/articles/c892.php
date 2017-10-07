<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Output of the program | Dereference, Reference, Dereference, Reference….</h1>
				
			
			<p>Predict the output of below program<br />
<span id="more-442"></span></p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
 char *ptr = "geeksforgeeks";
 printf("%c\n", *&*&*ptr);
 
 getchar();
 return 0;
}
</pre>
<p>Output: g</p>
<p>Explanation:  The operator * is used for dereferencing and the operator & is used to get the address.  These operators cancel effect of each other when used one after another. We can apply them alternatively any no. of times.  For example *ptr gives us g,  &*ptr gives address of g, *&*ptr again g, &*&*ptr address of g, and finally *&*&*ptr gives ‘g'</p>
<p>Now try below</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
 char *ptr = "geeksforgeeks";
 printf("%s\n", *&*&ptr);
 
 getchar();
 return 0;
}
</pre>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		