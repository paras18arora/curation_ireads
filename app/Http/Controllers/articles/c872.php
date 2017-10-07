<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What are the data types for which it is not possible to create an array?</h1>
				
			
			<p>In C, it is possible to have array of all types except following.<br />
1) void.<br />
2) functions.<span id="more-16613"></span></p>
<p>For example, below program throws compiler error</p>
<pre>
int main()
{
    void arr[100];
}
</pre>
<p>Output:
</p><pre>error: declaration of 'arr' as array of voids </pre>
<p>But we can have array of void pointers and function pointers. The below program works fine.</p>
<pre>
int main()
{
    void *arr[100];
}
</pre>
<p>See <a>examples of function pointers</a> for details of array function pointers. </p>
<p>This article is contributed by <strong>Shiva</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		