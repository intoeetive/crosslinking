<h1>Crosslinking</h1>

<p>Crosslinking extension can automaticly turn the text into links using pre-defined set of rules. </p>

<p>Instead of manually creating and changing SEO links in your entries, just define the 'key phrases' and URL they should point to - and voila! you have keyword links all around your site. Works both for channel entries and forum threads.</p>

<p>You can also automatically make every mention of entry titles from selected channels a link.</p>

<p>The replacement is done for text displayed by &#123;exp:channel:entries&#125; loop and for thread replies on forums. You can disable one or both of these in settings.</p>

<p>You can use both full and relative URLs when creating keyword-link pairs, as well as EE 	&#123;path=&#125; valiables.</p>

<p>The extension is looking only in text, it will keep safe the headings, image titles, existing links, javascript code etc.</p>

<p>You can define how many times the replacement will be made per channel entries tag in extension setting (set to 0 for no limit). You can also exclude certain parts from replacement by wrapping them into <code>{no_crosslinking}{/no_crosslinking}</code> tag pair.</p>