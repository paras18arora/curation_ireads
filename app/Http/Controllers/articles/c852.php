<link rel="stylesheet" type="text/css" href="abc.css"><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
						<h1 class="entry-title">Diffference between #define and const in C?</h1>
				
			
			<p><strong>#define</strong> is a <a target="_blank">preprocessor directive</a>. Things defined by #define are replaced by the preprocessor before compilation begins.  <span id="more-13014"></span></p>
<p><strong>const</strong> variables are actual variables like other normal variable.</p>
<p>The big advantage of const over #define is type checking.  We can also have poitners to const varaibles, we can pass them around, typecast them and any other thing that can be done with a normal variable.  One disadvantage that one could think of is extra space for variable which is immaterial due to optimizations done by compilers.</p>
<p>In general const is a better option if we have a choice. There are situations when #define cannot be replaced by const.  For example, #define can take parameters (See <a target="_blank">this </a>for example). #define can also be used to replace some text in a program with another text.</p>
<p>This article is contributed by <strong>Abhay Rathi</strong>. Please write comments if you find anything incorrect, or you want to share more information about the topic discussed above</p>

			

<!-- GQBottom -->


		