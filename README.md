# upload.class

A Class to upload multiple sorts of files with PHP to a webserver

Usage in webdesign, html form

Use and config:

// New instance of uploadFile

$upload = new uploadFile;

// Do you want to upload an image? or an other filetype? choose between image or file

$upload -&gt; type = 'image';

// name of the form input

$upload -&gt; inputname = 'imagea'; 

// resize in pixels

// the biggest side of the image is checked, if that side is bigger, the image wil be resized

$upload -&gt; resize = 100; 

// specify the new name of the file, without the extension

// Normally the extension of the file remains the original extension, only when it's an image;

// an image is saved as a .jpg file, but if the original image is a PNG AND NO background color is set, it's gonna be saved as a png with alpha

$upload -&gt; newfilename = 'hallo'; 

// maximum size of the file or image in megabytes

$upload -&gt; maxsize = 1;

// add a path where to put the file

$upload -&gt; addDestination('/img/'); 

// supported extensions, you can put as many as you want!

$upload -&gt; extensions = array('jpg', 'gif');

// use this if you want upload gif as they are, no compression or conversion!

$upload -&gt; gif = true;

// adding a path to place the file can more than one times :)

$upload -&gt; addDestination('/also/'); 

// Background color in HEX format for the .gif and .png files

// When you dont set the background color, the PNG wil be alpha

$upload -&gt; backgroundColor = '#FF4500';

// The message returned by upload() when succes! when there is an error, a friendly respons for the user wil be returned

$upload -&gt; succesmessage = 'yeah!';

// the actual upload function, returns 'succesmessage' or error with explanation

echo $upload -&gt; upload(); 

// Returns the filename with extension that is uploaded, for saving in the database etc.

$bestandsnaam = $upload -&gt; getName(); 

copy/paste and edit :) --&gt;

if($_FILES[ [forminput] ]['name'] != '') {

$upload = new uploadFile;

$upload -&gt; type = 'image OR file';

$upload -&gt; inputname = '[forminput]';

$upload -&gt; resize = 1000; //px

$upload -&gt; newfilename = '[NEW FILENAME WITHOUD EXTENSION]';

$upload -&gt; maxsize = 10; //Mb

$upload -&gt; addDestination('/img/');

$upload -&gt; addDestination('/also/');

$upload -&gt; extensions = array('jpg', 'png');

$upload -&gt; backgroundColor = '#FFFFFF';

echo $upload -&gt; upload();

// $upload -&gt; debug();

// $filename = $upload -&gt; getName();

}
