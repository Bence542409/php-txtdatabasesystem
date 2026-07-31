# web-txt-database-system
<p>Suitable for personal warehouse use.</p>
<br />
<p>This program allows you to:</p>
<ul>
<li>create delete or modify records (id, content, type, place)</li>
<li>search for records</li>
<li>check your record data</li>
<li>password protect the modification tools (default: admin - admin)</li>
</ul>
<br />
<p>index.html --></p>
<ul>
<li>the homepage of the server</li>
<li>displays the last modified date of the id.txt</li>
</ul>
<br />
<p>id.html & id.php --></p>
<ul>
<li>displays the informations stored in a record</li>
<li>displays the picture associated with the record</li>
<li>button to display the QR code for the record</li>
<li>option to navigate to the previous page by pressing the 'backspace' key</li>
<li>displays the last modified date of the id.txt</li>
</ul>
<br />
<p>id-check.php & id-check.html --></p>
<ul>
<li>option to input the id of a record</li>
</ul>
<br />
<p>id-search.php & id-search.html --></p>
<ul>
<li>lists the contents of all the records</li>
<li>option to search</li>
</ul>
<br />
<p>id-add.php --></p>
<ul>
<li>option to add a record</li>
<li>option to upload a picture associated with the record</li>
<li>automatically gerenates a QR code to records when added</li>
</ul>
<br />
<p>id-modify.php --></p>
<ul>
<li>option to modify an existing record</li>
</ul>
<br />
<p>id-remove.php --></p>
<ul>
<li>option to delete an existing record (removes all info, picture and barcode)</li>
</ul>
<br />
<p>id-data.php --></p>
<ul>
<li>displays the number of records stored</li>
<li>displays the number of empty records</li>
<li>displays the number of unknown records</li>
<li>displays the last modified date of the id.txt</li>
</ul>
<br />
<p>The records must be stored in a txt file in the same directory on a web server.</p>
<p>There are ten records added as an example.</p>
<p>The id.txt file works with my <a href="https://github.com/Bence542409/c-database-system">C# database system</a>.</p>
<p>The files are in Hungarian, but you can change it as you like.</p>
<br />
<p>Built by me with the help of ChatGPT.</p>
