<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Arrays in C Language | Set 1 (Introduction)</h1>
				
			
			<p>An array is collection of items stored at continuous memory locations. The idea is to declare multiple items of same type together.<span id="more-16646"></span></p>
<p><strong>Array declaration:</strong><br />
In C, we can declare an array by specifying its and size or by initializing it or by both.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
 // Array declaration by specifying size
 int arr[10];
</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
 // Array declaration by initializing elements 
 int arr[] = {10, 20, 30, 40}
 
 // Compiler creates an array of size 4. 
 // above is same as  "int arr[4] = {10, 20, 30, 40}"
</pre>
<pre class="brush: cpp; title: ; notranslate" title="">
 // Array declaration by specifying size and initializing 
 // elements 
 int arr[6] = {10, 20, 30, 40}
 
 // Compiler creates an array of size 6, initializes first 
 // 4 elements as specified by user and rest two elements as 0.
 // above is same as  "int arr[] = {10, 20, 30, 40, 0, 0}"
</pre>
<p><strong>Accessing Array Elements:</strong><br />
Array elements are accessed by using an integer index.  Array index starts with 0 and goes till size of array minus 1.  Following are few examples.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{
  int arr[5];
  arr[0] = 5;
  arr[2] = -10;
  arr[3/2] = 2; // this is same as arr[1] = 2
  arr[3] = arr[0];

  printf("%d %d %d %d", arr[0], arr[1], arr[2], arr[3]);

  return 0;
}
</pre>
<p>Output:
</p><pre>5 2 -10 5</pre>
<p><strong>No Index Out of bound Checking:</strong><br />
There is no index out of bound checking in C, for example the following program compiles fine but may produce unexpected output when run.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// This C program compiles fine as index out of bound
// is not checked in C.
int main()
{
  int arr[2];

  printf("%d ", arr[3]);
  printf("%d ", arr[-2]);

  return 0;
}</pre>
<p>Also, In C, it is not compiler error to initialize an array with more elements than specified size. For example the below program compiles fine.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
int main()
{

  // Array declaration by initializing it with more
  // elements than specified size.
  int arr[2] = {10, 20, 30, 40, 50};

  return 0;
}
</pre>
<p>The program won't compile in C++.  If we save the above program as a .cpp, the program generates compiler error "error: too many initializers for ‘int [2]'"</p>
<p><strong>An Example to show that array elements are stored at contiguous locations</strong></p>
<pre class="brush: cpp; title: ; notranslate" title="">
// C program to demonstrate that array elements are stored
// contiguous locations
int main()
{
  // an array of 10 integers.  If arr[0] is stored at
  // address x, then arr[1] is stored at x + sizeof(int)
  // arr[2] is stored at x + sizeof(int) + sizeof(int)
  // and so on.
  int arr[5], i;

  printf("Size of integer in this compiler is %u\n", sizeof(int));

  for (i=0; i<5; i++)
    // The use of '&' before a variable name, yields
    // address of variable.
    printf("Address arr[%d] is %u\n", i, &arr[i]);

  return 0;
}
</pre>
<p>Output:
</p><pre>
Size of integer in this compiler is 4
Address arr[0] is 2686728
Address arr[1] is 2686732
Address arr[2] is 2686736
Address arr[3] is 2686740
Address arr[4] is 2686744
</pre>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		