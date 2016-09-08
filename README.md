# upload.class
A Class to upload multiple sorts of files with PHP to a webserver
Usage in webdesign, html forms

Use and config:

// New instance of uploadFile
$upload = new uploadFile;

// Do you want to upload an image? or an other filetype? choose between image or file
$upload -> type = 'image';						

// name of the form input
$upload -> inputname = 'imagea'; 				

// resize in pixels
// the biggest side of the image is checked, if that side is bigger, the image wil be resized
$upload -> resize = 100; 						

// specify the new name of the file, without the extension
// Normally the extension of the file remains the original extension, only when it's an image;
// an image is saved as a .jpg file, but if the original image is a PNG AND NO background color is set, it's gonna be saved as a png with alpha
$upload -> newfilename = 'hallo'; 				

// maximum size of the file or image in megabytes
$upload -> maxsize = 1;

// add a path where to put the file
$upload -> addDestination('/img/'); 		

	// supported extensions, you can put as many as you want!
$upload -> extensions = array('jpg', 'gif');

// use this if you want upload gif as they are, no compression or conversion!
$upload -> gif = true;							

// adding a path to place the file can more than one times :)
$upload -> addDestination('/also/'); 			

// Background color in HEX format for the .gif and .png files
// When you dont set the background color, the PNG wil be alpha
$upload -> backgroundColor = '#FF4500';			

// The message returned by upload() when succes! when there is an error, a friendly respons for the user wil be returned
$upload -> succesmessage = 'yeah!';				

// the actual upload function, returns 'succesmessage' or error with explanation
echo $upload -> upload(); 						

// Returns the filename with extension that is uploaded, for saving in the database etc.
$bestandsnaam = $upload -> getName(); 			


copy/paste and edit :) -->

if($_FILES[ [forminput] ]['name'] != '') {
	$upload = new uploadFile;
	$upload -> type = 'image OR file';
	$upload -> inputname = '[forminput]';
	$upload -> resize = 1000; //px
	$upload -> newfilename = '[NEW FILENAME WITHOUD EXTENSION]';
	$upload -> maxsize = 10; //Mb
	$upload -> addDestination('/img/');
	$upload -> addDestination('/also/');
	$upload -> extensions = array('jpg', 'png');
	$upload -> backgroundColor = '#FFFFFF';
	echo $upload -> upload();
	// $upload -> debug();
	// $filename = $upload -> getName();
}
