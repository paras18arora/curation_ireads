<link rel='stylesheet' href='digital_ocean.css'><br><img src=https://community-cdn-digitalocean-com.global.ssl.fastly.net/assets/tutorials/images/large/5-Common-Turkey-Setups-Twitter.png?1426699768/> <br> 
      <h3 id="introduction">Introduction</h3>

<p>When deciding which turkey setup to use for your dinner, there are many factors to consider, such as flavor and texture scalability, equipment availability and reliability, cost, and preparation and cooking duration.</p>

<p>Here is a list of commonly used turkey setups, with a short description of each, including pros and cons. Keep in mind that every dinner has different requirements, so there is no single, correct turkey configuration. The only thing that is certain is that you must completely thaw any frozen turkeys before using them in these setups.</p>

<p><strong>Note:</strong> This is a Thanksgiving-themed parody of the <a href="https://indiareads/community/tutorials/5-common-server-setups-for-your-web-application">5 Common Server Setups For Your Web Application</a> article.</p>

<h2 id="1-roasted-in-an-oven">1. Roasted In An Oven</h2>

<p>The whole turkey is roasted in an oven. For a basic roasted turkey setup, the bird should be oiled, seasoned, then cooked and basted throughout the cooking process. It is fairly flexible, as the flavor and texture of your final product is scalable by making adjustments to spice allocations, and cooking temperatures and durations.</p>

<p><strong>Use Case</strong>:</p>

<p>As this is the most popular turkey cooking method, many recipes are available for roasting your turkey and it is an easy way to get your dinner up and running.</p>

<p><img src="https://assets.digitalocean.com/articles/turkey/Turkey-Setup-1-Roast.png" alt="Roasted" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Simple and traditional</li>
<li>Aromatic</li>
<li>Ovens are commonly available and reliable</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>Takes a long time to cook</li>
<li>Requires a roasting rack</li>
</ul>

<h2 id="2-smoked">2. Smoked</h2>

<p>The whole turkey is smoked in a smoker, using wood chips to generate the smoke. This setup is similar to roasting, except the smoke from the woodchips provides the heat and infuses the bird with its unique, smokey flavor. </p>

<p><strong>Use Case</strong>:</p>

<p>As the second-most popular turkey cooking method, smoking your bird is a good way to scale the flavor of your dinner in a different direction. It is also just as fancy as roasting.</p>

<p><img src="https://assets.digitalocean.com/articles/turkey/Turkey-Setup-2-Smoke.png" alt="Smoked" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Flexible flavor scalability, depending on the wood chips that are used</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>Can produce a dry texture</li>
<li>Slower than roasting</li>
<li>Requires wood chips and smoker hardware</li>
</ul>

<h2 id="3-deep-fried">3. Deep-fried</h2>

<p>The whole turkey is dipped into a vat of hot oil. This non-traditional setup allows for great flavor scaling and performance that even the most traditional dinner user can enjoy.</p>

<p><strong>Use Case</strong>:</p>

<p>Good if you're looking to increase your turkey's flavor to impress your dinner users.</p>

<p><img src="https://assets.digitalocean.com/articles/turkey/Turkey-Setup-3-Fry.png" alt="Deep-fried" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>The quickest whole bird cooking duration (45 minutes for 12 lbs)</li>
<li>Juicy texture</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>Potentially more dangerous than the other methods, if oil levels are not properly measured to account for the displacement that will occur when the turkey is lowered into the oil</li>
<li>The final dinner will contain more calories, due to cooking oil</li>
</ul>

<h2 id="4-high-moisture-availability-brined">4. High Moisture Availability (Brined)</h2>

<p>Brine can be added to a turkey setup to vertically scale flavor performance and availability of moistness. The whole turkey is soaked in a salt and seasoning bath for 8-16 hours before cooking. If using a long-duration cooking method, brining your turkey before cooking it will ensure that it not dry out before it is time to deploy your dinner.</p>

<p><strong>Use Case</strong>:</p>

<p>If you have enough time before you are required to deploy your dinner, you should almost definitely use this setup if you are planning on using a long-duration cooking method (roasting or smoking).</p>

<p><img src="https://assets.digitalocean.com/articles/turkey/Turkey-Setup-4-Brine.png" alt="Brined" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Enables high moisture availability</li>
<li>Vertically scales flavor capacity</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>The brining process takes 8 to 16 hours</li>
</ul>

<h3 id="beer-can-turkey">Beer Can Turkey</h3>

<p>An alternative way to ensure a high level of moisture availability is to use the beer can method. This involves stuffing a large, open beer can into a seasoned turkey before the cooking process, as shown here:</p>

<p><img src="https://assets.digitalocean.com/articles/turkey/Turkey-Setup-4-Beer.png" alt="Beer Can Turkey" /></p>

<h2 id="5-food-load-balancer-stuffing">5. Food Load Balancer (Stuffing)</h2>

<p>The cavity of your turkey is stuffed with starches, spices and herbs, vegetables, and other edible items before the cooking process. This setup can increase performance by balancing the load of your dinner users between stuffing and turkey requests.</p>

<p><strong>Use Case</strong>:</p>

<p>If you are roasting your turkey and want to provide another resource for your dinner users to consume, consider using this setup to increase your turkey-based performance.</p>

<p><img src="https://assets.digitalocean.com/articles/turkey/Turkey-Setup-5-Stuffing.png" alt="Stuffing" /></p>

<p><strong>Pros</strong>:</p>

<ul>
<li>Horizontally scales dinner's overall flavor capacity</li>
<li>Cheap way to get more out of your turkey</li>
</ul>

<p><strong>Cons</strong>:</p>

<ul>
<li>This setup is not compatible with every cooking method</li>
</ul>

<h2 id="example-combining-the-concepts">Example: Combining the Concepts</h2>

<p>It is possible to use high moisture availability, a food load balancer, and the roasted in an oven setup in a single dinner. The purpose of combining these techniques is to reap the benefits of each without introducting too many issues or complexity. Here is a diagram of what a turkey dinner environment could look like:</p>

<p><img src="https://assets.digitalocean.com/articles/turkey/Turkey-Setup-Combo.png" alt="Combined" /></p>

<p>Eventually, all of the output will make it to the dinner table, where it can be served up to your dinner users.</p>

<p>Here's a description of what would happen when a dinner user sends a dynamic food request to the dinner table:</p>

<ol>
<li>The dinner user sends its food request to the dinner host</li>
<li>The host sends the request to the turkey plate and the stuffing bowl</li>
<li>Whichever dish has the least number of connections returns itself to the user</li>
<li>The user will be satisfied but will probably send another request in 20 minutes </li>
</ol>

<h1 id="conclusion">Conclusion</h1>

<p>Now that you are familiar with some basic turkey setups, you should have a good idea of what kind of setup you would use for your own dinner(s). If you are working on improving your own dinner, remember that an iterative process is best to avoid introducing too many complexities too quickly. Don't forget to make gravy!</p>

<p>Let us know of any setups you recommend or would like to learn more about in the comments below!</p>

    