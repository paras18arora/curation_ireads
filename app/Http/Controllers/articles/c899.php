<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">What are near, far and huge pointers?</h1>
				
			
			<p>These are some old concepts used in 16 bit intel architectures in the days of MS DOS, not much useful anymore. <span id="more-18309"></span></p>
<p><strong>Near pointer</strong> is used to store 16 bit addresses means within current segment on a 16 bit machine. The limitation is that we can only access 64kb of data at a time.</p>
<p><strong>A far pointer</strong> is typically 32 bit that can access memory outside current segment.  To use this, compiler allocates a segment register to store segment address, then another register to store offset within current segment.</p>
<p>Like far pointer, <strong>huge pointer</strong> is also typically 32 bit and can access outside segment. In case of far pointers, a segment is fixed.  A huge pointer can access multiple segments.  In far pointer, the segment part cannot be modified, but in far it can be.</p>
<p>See below links for more details.</p>
<p><a rel="nofollow">http://www.answers.com/Q/What_are_near_far_and_huge_pointers_in_C</a></p>
<p><a rel="nofollow">https://www.quora.com/What-is-the-difference-between-near-far-huge-pointers-in-C-C++</a></p>
<p><a rel="nofollow">http://stackoverflow.com/questions/8727122/explain-the-difference-between-near-far-and-huge-pointers-in-c</a></p>
<p>Source: <a>http://qa.geeksforgeeks.org/664/near-far-huge-pointers?show=664#q664</a></p>
<p>Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		