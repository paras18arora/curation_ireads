<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Union in C</h1>
				
			
			<p>Like <a target="_blank">Structures</a>, union is a user defined data type. In union, all members share the same memory location. <span id="more-15274"></span> For example in the following C program, both x and y share the same location.<!--more-->  If we change x, we can see the changes being reflected in y.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

// Declaration of union is same as structures
union test
{
   int x, y;
};

int main()
{
    // A union variable t
    union test t;

    t.x = 2; // t.y also gets value 2
    printf ("After making x = 2:\n x = %d, y = %d\n\n",
             t.x, t.y);

    t.y = 10;  // t.x is also updated to 10
    printf ("After making Y = 'A':\n x = %d, y = %d\n\n",
             t.x, t.y);
    return 0;
}
</pre>
<p>Output:
</p><pre>After making x = 2:
 x = 2, y = 2

After making Y = 'A':
 x = 10, y = 10</pre>
<p> <br />
<strong>How is the size of union decided by compiler?</strong><br />
Size of a union is taken according the size of largest member in union. </p>
<pre class="brush: cpp; title: ; notranslate" title="">
#include <stdio.h>

union test1
{
   int x;
   int y;
};

union test2
{
   int x;
   char y;
};

union test3
{
   int arr[10];
   char y;
};

int main()
{
    printf ("sizeof(test1) = %d, sizeof(test2) = %d,"
            "sizeof(test3) =  %d", sizeof(test1),
            sizeof(test2), sizeof(test3));
    return 0;
}
</pre>
<p>Output
</p><pre>sizeof(test1) = 4, sizeof(test2) = 4,sizeof(test3) =  40</pre>
<p> <br />
<strong>Pointers to unions?</strong><br />
Like structures, we can have pointers to unions and can access members using arrow operator (->).  The following example demonstrates the same.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
union test
{
   int x;
   char y;
};

int main()
{
   union test p1;
   p1.x = 65;

   // p2 is a pointer to union p1
   union test *p2 = &p1;

   // Accessing union members using pointer
   printf("%d %c", p2->x, p2->y);
   return 0;
}
</pre>
<pre>
65 A </pre>
<p> <br />
<strong>What are applications of union?</strong><br />
Unions can be useful in many situations where we want to use same memory for two ore more members.  For example, suppose we want to implement a binary tree data structure where each leaf node has a double data value, while each internal node has pointers to two children, but no data. If we declare this as:
</p><pre class="brush: cpp; title: ; notranslate" title="">
struct NODE {
  struct NODE *left;
  struct NODE *right;
  double data;
};</pre>
<p>then every node requires 16 bytes, with half the bytes wasted for each type of node. On the other hand, if we declare a node as following, then we can save space.</p>
<pre class="brush: cpp; title: ; notranslate" title="">
struct NODE
{
    bool is_leaf;
    union
    {
        struct
        {
            struct NODE *left;
            struct NODE *right;
        } internal;
        double data;
    } info;
};</pre>
<p>The above example is taken from <a target="_blank">Computer Systems : A Programmer's Perspective (English) 2nd Edition</a> book.</p>
<p><strong>References:</strong><br />
<a target="_blank">http://en.wikipedia.org/wiki/Union_type</a><br />
<a target="_blank">Computer Systems : A Programmer's Perspective (English) 2nd Edition</a> </p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		