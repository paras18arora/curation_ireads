<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Structures in C</h1>
				
			
			<p><em><strong>What is a structure?</strong></em><br />
A structure is a user defined data type in C/C++.  A structure creates a data type that can be used to group items of possibly different types into a single type. <span id="more-15130"></span></p>
<p> <br />
<em><strong>How to create a structure?</strong></em><br />
‘struct' keyword is used to create a structure.  Following is an example.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct addrress
{
   char name[50];
   char street[100];
   char city[50];
   char state[20]
   int pin;
};</pre>
<p> <br />
<em><strong>How to declare structure variables?</strong></em><br />
A structure variable can either be declared with structure declaration or as a separate declaration like basic types.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
// A variable declaration with structure declaration.
struct Point
{
   int x, y;
} p1;  // The variable p1 is declared with 'Point'


// A variable declaration like basic data types
struct Point
{
   int x, y;
}; 

int main()
{
   struct Point p1;  // The variable p1 is declared like a normal variable
}</pre>
<p>Note: In C++, the struct keyword is optional before in declaration of variable. In C, it is mandatory.</p>
<p> <br />
<em><strong>How to initialize structure members?</strong></em><br />
Structure members <strong>cannot be</strong> initialized with declaration.  For example the following C program fails in compilation.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct Point
{
   int x = 0;  // COMPILER ERROR:  cannot initialize members here
   int y = 0;  // COMPILER ERROR:  cannot initialize members here
}; 
</pre>
<p>The reason for above error is simple, when a datatype is declared, no memory is allocated for it. Memory is allocated only when variables are created.</p>
<p>Structure members <strong>can be</strong> initialized using curly braces ‘{}'.  For example, following is a valid initialization.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct Point
{
   int x, y;
}; 

int main()
{
   // A valid initialization. member x gets value 0 and y
   // gets value 1.  The order of declaration is followed.
   struct Point p1 = {0, 1}; 
}</pre>
<p> <br />
<em><strong>How to access structure elements?</strong></em><br />
Structure members are accessed using dot (.) operator. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct Point
{
   int x, y;
};

int main()
{
   struct Point p1 = {0, 1};

   // Accesing members of point p1
   p1.x = 20;
   printf ("x = %d, y = %d", p1.x, p1.y);

   return 0;
}</pre>
<p>Output:
</p><pre>20  1</pre>
<p> <br />
<strong>What is designated Initialization?</strong><br />
Designated Initialization allows structure members to be initialized in any order.  This feature has been added in <a target="_blank">C99 standard</a>.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct Point
{
   int x, y, z;
};

int main()
{
   // Examples of initializtion using designated initialization
   struct Point p1 = {.y = 0, .z = 1, .x = 2};
   struct Point p2 = {.x = 20};

   printf ("x = %d, y = %d, z = %d\n", p1.x, p1.y, p1.z);
   printf ("x = %d", p2.x);
   return 0;
}
</pre>
<p>Output:
</p><pre>x = 2, y = 0, z = 1
x = 20</pre>
<p>This feature is not available in C++ and works only in C.</p>
<p> <br />
<em><strong>What is an array of structures?</strong></em><br />
Like other primitive data types, we can create an array of structures.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct Point
{
   int x, y;
};

int main()
{
   // Create an array of structures
   struct Point arr[10];

   // Access array members
   arr[0].x = 10;
   arr[0].y = 20;

   printf("%d %d", arr[0].x, arr[0].y);
   return 0;
}
</pre>
<p>Output:
</p><pre>10  20</pre>
<p> <br />
<em><strong>What is a structure pointer?</strong></em><br />
Like primitive types, we can have pointer to a structure. If we have a pointer to structure, members are accessed using arrow ( -> ) operator.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct Point
{
   int x, y;
};

int main()
{
   struct Point p1 = {1, 2};

   // p2 is a pointer to structure p1
   struct Point *p2 = &p1;

   // Accessing structure members using structure pointer
   printf("%d %d", p2->x, p2->y);
   return 0;
}</pre>
<p>Output:
</p><pre>1  2</pre>
<p><strong>What is structure member alignment?</strong><br />
See <a target="_blank">http://www.geeksforgeeks.org/structure-member-alignment-padding-and-data-packing/</a></p>
<p>We will soon be discussing union and other struct related topics in C.  Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		