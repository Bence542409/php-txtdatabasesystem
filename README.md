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
<p>A record consists of 4 pieces of data:</p>
<ul>
<li>id number</li>
<li>contents of storage</li>
<li>type of storage</li>
<li>location of storage/li>
</ul>
<br />
<p>index.html --></p>
<ul>
<li>the homepage of the server</li>
<li>displays the last modified date of the id.txt</li>
<li>select the id-check button or press the 'C' key to redirect to id-check.php</li>
</ul>
<br />
<p>id.html --></p>
<ul>
<li>displays the informations stored in a record (if the record exists)</li>
<li>displays the picture associated with the record (if a picture is found for the record)</li>
<li>displays the picture associated with the record (if a picture isn't found for the record or the record does not exist)</li>
<li>option to display the QR code for the record</li>
<li>displays the last modified date of the id.txt</li>
<li>press the "backspace" key to navigate to the previous page</li>
<li>press the "esc" key to navigate to the parent directory</li>
</ul>
<br />
<p>id-check.php --></p>
<ul>
<li>option to lookup the id of a record</li>
<li>press the "enter' key to focus the search input (while textbox is inactive)</li>
<li>press the "enter' key to lookup the given id (while textbox is active)</li>
<li>press the "esc" key to navigate to the parent directory (while textbox is inactive)</li>
<li>press the "esc" key to defocus the textbox (while textbox is active)</li>
<li>press the "backspace" key to navigate to the previous page (while textbox is inactive)</li>
<li>press the "tab" key to clear the search input</li>
</ul>
<br />
<p>id-search.php --></p>
<ul>
<li>lists the contents of all the records</li>
<li>press the "enter' key to focus the search input (while textbox is inactive)</li>
<li>press the "enter' key to open the first result of search (while textbox is active)</li>
<li>press the numeric keys (1 - 9) to open the numbered result of the search (while textbox is inactive)</li>
<li>press the "esc" key to navigate to the parent directory (while textbox is inactive)</li>
<li>press the "esc" key to defocus the textbox (while textbox is active)</li>
<li>press the "backspace" key to navigate to the previous page (while textbox is inactive)</li>
<li>press the "tab" key to clear the search input</li>
</ul>
<br />
<p>id-add.php --></p>
<ul>
<li>option to add a record</li>
<li>option to upload a picture associated with the record</li>
<li>automatically gerenates a QR code to records when added</li>
<li>press the "enter' key to modify the given id (while textbox is active)</li>
<li>press the "esc" key to navigate to the parent directory (while textbox is inactive)</li>
<li>press the "backspace" key to navigate to the previous page (while textbox is inactive)</li>
</ul>
<br />
<p>id-modify.php --></p>
<ul>
<li>option to modify an existing record</li>
<li>option to replace a picture associated with the record</li>
<li>press the "enter' key to modify the given id (while textbox is active)</li>
<li>press the "esc" key to navigate to the parent directory (while textbox is inactive)</li>
<li>press the "backspace" key to navigate to the previous page (while textbox is inactive)</li>
</ul>
<br />
<p>id-remove.php --></p>
<ul>
<li>option to delete an existing record (removes all info, picture and barcode)</li>
<li>press the "enter' key to focus the search input (while textbox is inactive)</li>
<li>press the "enter' key to lookup the given id (while textbox is active)</li>
<li>press the "esc" key to navigate to the parent directory (while textbox is inactive)</li>
<li>press the "esc" key to defocus the textbox (while textbox is active)</li>
<li>press the "backspace" key to navigate to the previous page (while textbox is inactive)</li>
<li>press the "tab" key to clear the search input</li>
</ul>
<br />
<p>id-data.php --></p>
<ul>
<li>displays the number of records stored</li>
<li>displays the number of active records stored</li>
<li>displays the number of deleted records (counts the records where the content is "[RENDSZERBŐL TÖRÖLVE]")</li>
<li>displays the number of empty records (counts the records where the content is "[ÜRES]")</li>
<li>displays the number of records without a picture</li>
<li>displays the number of unknown records (counts the records where the content, type or location is "[NINCS INFORMÁCIÓ]")</li>
<li>displays the last modified date of the id.txt</li>
<li>press the "backspace" key to navigate to the previous page</li>
<li>press the "esc" key to navigate to the parent directory</li>
</ul>
<br />
<p>The records must be stored in a txt file in the same directory on a web server.</p>
<p>There are ten records added as an example.</p>
<p>The id.txt file works with my <a href="https://github.com/Bence542409/c-database-system">C# database system</a>.</p>
<p>The files are in Hungarian.</p>
<br />
<p>Built by me with the help of ChatGPT and Gemini.</p>
