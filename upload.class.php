<?php
// Class uploadFile by Max Gramser, version 21 july 2016
// Use and config:

/*

$upload = new uploadFile;						// New instance of uploadFile

$upload -> type = 'image';						// Do you want to upload an image? or an other filetype? choose between image or file

$upload -> inputname = 'imagea'; 				// name of the form input

$upload -> resize = 100; 						// resize in pixels
												// the biggest side of the image is checked, if that side is bigger, the image wil be resized

$upload -> newfilename = 'hallo'; 				// specify the new name of the file, without the extension
												// Normally the extension of the file remains the original extension, only when it's an image;
												// an image is saved as a .jpg file, but if the original image is a PNG AND NO background color is set, it's gonna be saved as a png 													// with alpha

$upload -> maxsize = 1; 						// maximum size of the file or image in megabytes

$upload -> addDestination('/img/'); 			// add a path where to put the file

$upload -> extensions = array('jpg', 'gif');	// supported extensions, you can put as many as you want!

$upload -> gif = true;							// use this if you want upload gif as they are, no compression or conversion

$upload -> addDestination('/also/'); 			// adding a path to place the file can more than one times :)

$upload -> backgroundColor = '#FF4500';			// Background color in HEX format for the .gif and .png files
												// When you dont set the background color, the PNG wil be alpha

$upload -> succesmessage = 'yeah!';				// The message returned by upload() when succes! when there is an error, a friendly respons for the user wil be returnd

echo $upload -> upload(); 						// the actual upload function, returns 'succesmessage' or error with explanation
												
$bestandsnaam = $upload -> getName(); 			// Returns the filename with extension that is uploaded, for saving in the database etc.


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

*/


// The messages that are returned by $this -> upload(), for front end use:
define(ERRORTEXT_fileNotSupported, 'Bestandsextensie niet toegestaan, alleen toegestaan zijn:');
define(ERRORTEXT_noImage, 'Bestandstype niet herkend, geen afbeelding geselecteerd');
define(ERRORTEXT_tooBig, 'Bestand te groot, maximale grootte:');
define(ERRORTEXT_pathFail, 'Het is niet gelukt om het bestand te uploaden naar');
define(ERRORTEXT_noFileFound, 'Geen bestand gevonden om te uploaden..');
define(UPLOADCLASS_SUCCES, 'succes');



// BEGIN CODE CLASS --------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

class uploadFile {
	var $type = 'file';
	var $inputname;
	var $newfilename = '';
	var $resize = 0;
	var $maxsize = 10;
	var $destinations = array();
	var $extensions = array();
	var $succesmessage = UPLOADCLASS_SUCCES;
	var $backgroundColor = '';
	var $debug = array();
	var $gif = false;
	
	private $readytoUpload = false;
	
	// Basisfuncties
	function debug(){
		sort($this -> debug, 1);
		$newarray = array();
		
		foreach($this -> debug as $value){
			$number = substr($value, 0, strpos($value, ':'));
			$numberlength = strlen($number);
			$string = substr($value, strpos($value, $number.':') + $numberlength + 1);
			
			$newarray[$number] = $string;
		}
		echo '<table class="upload-class-debug-table">';
		
		echo "<tr><td><b>DEBUG TABLE</b></td><td>UPLOAD CLASS</td></tr>";
		
			foreach($newarray as $key => $value){
				echo '<tr><td>At Line ' . $key . '</td>';
				echo '<td>'.$value.'</td';
				echo '</tr>';
			}
		
		echo '</table>';
		
		
		
		echo 
		'<style>
		.upload-class-debug-table {
			margin: 10px;
		}
		
		.upload-class-debug-table td {
			padding: 3px;
		}
		
		.upload-class-debug-table tr:nth-child(odd) {
			background-color: #dddddd;
		}
		
		</style>';
	}
	function extension(){
		$tmpname = strtolower($_FILES[$this->inputname]['name']);
		$ext = pathinfo($tmpname, PATHINFO_EXTENSION);
		$this -> debug[] = __LINE__.': The extension seems to be: ' . $ext;
		return $ext;
	}
	function resizeInfo($img){
		// bereken het ratio van de image en hoe hij geresized moet worden
		
		$this -> debug[] = __LINE__.': Check the image data...';
		$width = imagesx($img);
		$height = imagesy($img);
	    $ratio = (float)$height / (float)$width;
	    $resizeMax = $this->resize;
	    
		$this -> debug[] = __LINE__.': Height of the original image = '.$height . 'px';
		$this -> debug[] = __LINE__.': Width of the original image = '.$width . 'px';
		
		if($width > $resizeMax){
			$newWidth = $resizeMax;
			$newHeight = $resizeMax * $ratio;
		} else if($height > $resizeMax){
			$newWidth = $resizeMax / $ratio;
			$newHeight = $resizeMax;
		} else {
		    $newWidth = $width;
		    $newHeight = $height;
		}
		
		$this -> debug[] = __LINE__.': New resized height of the original image = '.$newHeight. 'px';
		$this -> debug[] = __LINE__.': New resized width of the original image = '.$newWidth. 'px';
		
		return array('width' => $width, 'height' => $height, 'newwidth' => $newWidth, 'newheight' => $newHeight);
	}
	function isImage(){
		$ext = $this->extension();
		if($ext != 'jpg' && $ext != 'jpeg' && $ext != 'png' && $ext != 'gif'){
			$this -> debug[] = __LINE__.': file is not an image';
			return false;
		} else {
			$this -> debug[] = __LINE__.': file is an image';
			return true;
		}
	}
	function checkFileSize(){
		if((filesize($_FILES[$this->inputname]['tmp_name']) / 1000000) > $this->maxsize){
			return false;
		} else {
			return true;
		}
	}
	function addDestination($path){
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)){
			$this -> destinations[] = $path;
		} else {
			$this -> debug[] = __LINE__.': This destination does not exist: ' . $path .' the file wil not be placed there.. ' ;
		}
		
		
	}
	function BackgroundColor($hex, $image){
		// hex naar rgb
		list($width, $height) = getimagesize($_FILES[$this->inputname]['tmp_name']);
		list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
		$output = imagecreatetruecolor($width, $height);
		$color = imagecolorallocate($image,  $r, $g, $b);
		imagefilledrectangle($output, 0, 0, $width, $height, $color);
		imagecopy($output, $image, 0, 0, 0, 0, $width, $height);
		$this -> debug[] = __LINE__.': backgroundcolor becomes ' . $hex;
		return $output;
	}
	function destExtention(){
		// Welke extensie moet de file uiteindelijk hebben?
		if($this -> type == 'image'){
			if($this -> extension() == 'png' && $this -> backgroundColor == ''){
				$newext = '.png';
			} else {
				$newext = '.jpg';
			}
		} else if($this -> type == 'file'){
			$newext = '.' . $this -> extension();
		}
		
		$this -> debug[] = __LINE__.': So the new file extension is: ' . $newext;
		return $newext;
	}
	function getName(){
		if($this -> destExtention() != '' && $this -> readytoUpload == true){
			if($this -> newfilename === ''){
				$newFileName = pathinfo($_FILES[$this->inputname]['name'], PATHINFO_FILENAME);
			} else {
				$newFileName = $this -> newfilename;
			}
		return $newFileName . $this -> destExtention();
		}	
	}
	
	// File operations
	function makeImage(){
		$this -> debug[] = __LINE__.': trying to make and phpimage from the file';
		switch($this->extension()){
			case 'jpeg':
			case 'jpg':
				// if the photo is taken on a mobile phone in a rotated state, rotate back
				$image = imagecreatefromjpeg($_FILES[$this->inputname]['tmp_name']);
				
				$exif = exif_read_data($_FILES[$this->inputname]['tmp_name']);
				$orientation = $exif['Orientation'];
		
				if($orientation == 6){
					$image = imagerotate($image, -90, 0);
				} else if($orientation == 3){
					$image = imagerotate($image, 180, 0);
				} else if($orientation == 8){
					$image = imagerotate($image, 90, 0);
				}
				
				$this -> debug[] = __LINE__.': Image created from jpg';
				
			break;
			
			case 'png';
				$imageSRC = imagecreatefrompng($_FILES[$this->inputname]['tmp_name']);
				
				if($this -> backgroundColor == ''){
					$image = $imageSRC;
					$this -> debug[] = __LINE__.': Image created from png without background';
				} else {
					$image = $this->BackgroundColor($this->backgroundColor, $imageSRC);
					$this -> debug[] = __LINE__.': Image created from png with background';
				}
				
				
				
			break;
				
			case 'gif':
				$imageSRC = imagecreatefromgif($_FILES[$this->inputname]['tmp_name']);
				$image = $this->BackgroundColor($this->backgroundColor, $imageSRC);
				$this -> debug[] = __LINE__.': Image created from gif';
			break;
			
			default:
				$this -> debug[] = __LINE__.': Image format not reccognized...';
				return false;
			break;
		}
		return $image;
		
	}
	function imageresize(){
		$this -> debug[] = __LINE__.': Image resize function triggered';
		// Als we niet te maken hebben met een image, dan kan je niet eens resizen duh..
		if($this->isImage() == false){
			$this -> debug[] = __LINE__.': Image resize function -> this is no image!';
			return false;
		}
		
		// maak de image bruikbaar voor werken in PHP
		$src = $this->makeImage();
		
		// als er geen resize is gegeven, dan door
		if(!isset($this->resize)){
			$this -> debug[] = __LINE__.': Resize not set';
			return $src;
		}
		
		$this -> debug[] = __LINE__.': begin resizing...';
		// resizedata
		$resizeArray = $this->resizeInfo($src);
		
		// Als het een png is, krijgt hij een speciale alpha behandeling (ooohh!!! :D)
		if($this->extension() == 'png' && $this->backgroundColor == ''){
				$new_im = imagecreatetruecolor($resizeArray['newwidth'],$resizeArray['newheight']);
				imagealphablending($new_im, false);
				imagesavealpha($new_im,true);
				imagecolortransparent($new_im, imagecolorallocate($new_im, 0, 0, 0));
				imagecopyresampled($new_im,$src,0,0,0,0,$resizeArray['newwidth'],$resizeArray['newheight'],$resizeArray['width'],$resizeArray['height']);
				$this -> debug[] = __LINE__.': Image resized, with transparant background';
				return $new_im;
			
		} else {
				$dst = imagecreatetruecolor($resizeArray['newwidth'],$resizeArray['newheight']);
				imagecopyresampled($dst,$src,0,0,0,0,$resizeArray['newwidth'],$resizeArray['newheight'],$resizeArray['width'],$resizeArray['height']);
				$this -> debug[] = __LINE__.': Image resized, with background';
				return $dst;
		}
	
	}

	
	// Losse upload functies per filetype
	function uploadImage($temp){
		// Is het echt een foto? (jpg, jpeg, png, gif)
			if($this->isImage() === false){
				$this -> debug[] = __LINE__.': Trying to upload the image, but it is not an image..';
				return ERRORTEXT_noImage;
			}
			
			// Check of de extensie is toegestaan
			if(!empty($this->extensions)){
				if(!in_array($this->extension(), $this->extensions)){
					return ERRORTEXT_fileNotSupported .' '. implode(', ', $this->extensions);
				}
			}
			
			if($this->resize != 0){
				// check de grote van het bestand, vergelijk deze met de maximale grote
				if($this->checkFileSize() === false){
					return ERRORTEXT_tooBig .' '. $this->maxsize . ' Mb';
				}
				
				// Doe een resize als de resize grootte is gespecificeerd
				$image = $this->imageresize(); // in de resize zit makeImage() ingebakken		
									
				// Bestand uploaden zonder resize
				} else {
					$image = $this -> makeImage();
				}
				
				if($this -> newfilename === ''){
					$newFileName = pathinfo($_FILES[$this->inputname]['name'], PATHINFO_FILENAME);
				} else {
					$newFileName = $this -> newfilename;
				}
				
				// voor iedere map waar hij in moet, zet hem erin
				foreach((array)$this->destinations as $dest){
					
					
					$fileWdest = $_SERVER['DOCUMENT_ROOT'] . $dest . $newFileName . $this -> destExtention();
					$this -> debug[] = __LINE__.': New image is going to be placed as: "'. $dest . $newFileName . $this -> destExtention().'"';
					
					// Als het een png is, dan moet hij naar als PNG worden opgeslagen
					if($this -> destExtention() == '.png'){
						if(!imagepng($image, $fileWdest)){
							return ERRORTEXT_pathFail .' '.$dest;
						} else {
							$this -> debug[] = __LINE__.': HOOORRAY PNG image MADE!';
							$this -> debug[] = __LINE__.': Here is a link to check: <br><img src="'. $dest. $newFileName . $this -> destExtention().'?version='.rand().'">';
						}
					// Anders als jpg
					} else {
						if(!imagejpeg($image, $fileWdest)){
							return 'Het is niet gelukt om het bestand te uploaden naar '.$dest;
						} else {
							$this -> debug[] = __LINE__.': HOOORRAY JPG image MADE!';
							$this -> debug[] = __LINE__.': Here is a link to check: <br><img src="'. $dest. $newFileName . $this -> destExtention().'?version='.rand().'">';
						}
					}
					
				}
				// De afbeelding die is gemaakt om mee te knutselen in PHP is niet meer nodig
				imagedestroy($image);
				// Als dit dus allemaal gelukt is, succes!!!
				return $this->succesmessage;
				
			}
	function uploadOther($temp){
		$this -> debug[] = __LINE__.': Triggering file uploader';
		if($this -> newfilename == ''){
			$newFileName = pathinfo($_FILES[$this->inputname]['name'], PATHINFO_FILENAME);
			$this -> debug[] = __LINE__.': Newfilename not set so it becomes the original filename; "'.$newFileName.$this -> destExtention().'"';
		} else {
			$newFileName = $this -> newfilename;
			$this -> debug[] = __LINE__.': Newfilename set so it becomes: "'.$newFileName. $this -> destExtention().'"' ;
		}
		
		
		if(isset($_FILES[$this->inputname]['tmp_name'])){
				// Checken of de file niet te groot is
				if(!$this->checkFileSize()){
					return ERRORTEXT_tooBig.' '. $this->maxsize . ' Mb';
				}
				
				// Check of de extensie is toegestaan
				if(!empty($this->extensions)){
					if(!in_array($this->extension(), $this->extensions)){
						return ERRORTEXT_ERRORTEXT_fileNotSupported .' '. implode(', ', $this->extensions);
					}
				}
				
				// Voor iedere locatie de file plaatsen
					foreach($this->destinations as $dest){
						$fileWdest = $_SERVER['DOCUMENT_ROOT'].$dest.$newFileName . $this -> destExtention();
						if(!move_uploaded_file($temp, $fileWdest)){
							return ERRORTEXT_pathFail .' '. $dest;
						}
					}
					// Als de functie geen return heeft gedaan, dan zijn we klaar.
					return $this->succesmessage;
			
		} else {
			return ERRORTEXT_noFileFound;
		}

	}
	
	// voer de uiteindelijke actie uit
	function upload(){
		
		if(empty( $this -> destinations )) {
			$this -> debug[] = __LINE__.': No destination is given..';
			return;
		}
		
		if(isset($_FILES[$this->inputname]['tmp_name'])){
			// plaats en naam van de file in een
			$temp = $_FILES[$this->inputname]['tmp_name'];
			
			// Selecteer de uploadfunctie die bij het bestandstype hoort
			if($this->type == 'image'){
				$this -> debug[] = __LINE__ .  '_' . $this -> gif;
				// if the extension is gif, and the uploader wants the real image to be uploaded
				if($this -> gif && $this -> extension() == 'gif'){
					$this -> debug[] = __LINE__.'Gif image uploaded';
					$message = $this->uploadOther($temp);
					$this -> debug[] = __LINE__.': "'.$message.'"';
					$this -> readytoUpload = true;
					return $message;
				} else {
					// if the image is other or gif -> is false
					$message = $this->uploadImage($temp);
					$this -> debug[] = __LINE__.': "'.$message.'"';
					$this -> readytoUpload = true;
					return $message;
				}
				
			
			} else if($this->type == 'file'){
				$message = $this->uploadOther($temp);
				$this -> debug[] = __LINE__.': "'.$message.'"';
				$this -> readytoUpload = true;
				return $message;
			}
		} else {
			$this -> debug[] = __LINE__.': The file input is not set!';
		}
	}

}
?>