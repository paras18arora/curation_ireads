<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Pointer vs Array in C</h1>
				
			
			<p>Most of the time, pointer and array accesses can be treated as acting the same, the major exceptions being:</p>
<p>1) the sizeof operator<br />
o sizeof(array) returns the amount of memory used by all elements in array<br />
o sizeof(pointer) only returns the amount of memory used by the pointer variable itself</p>
<p>2) the & operator<br />
o &array is an alias for &array[0] and returns the address of the first element in array<br />
o &pointer returns the address of pointer</p>
<p>3) a string literal initialization of a character array<br />
o char array[] = "abc" sets the first four elements in array to ‘a', ‘b', ‘c', and ‘\0′<br />
o char *pointer = "abc" sets pointer to the address of the "abc" string (which may be stored in read-only memory and thus unchangeable)</p>
<p>4) Pointer variable can be assigned a value whereas array variable cannot be.
</p><pre>
int a[10];
int *p; 
p=a; /*legal*/
a=p; /*illegal*/ </pre>
<p>5) Arithmetic on pointer variable is allowed.
</p><pre>
p++; /*Legal*/
a++; /*illegal*/ </pre>
<p>References: <a>http://icecube.wisc.edu/~dglo/c_class/array_ptr.html</a></p>

			
<!-- Big Rectangle Blog Bottom -->


<br></br>
		