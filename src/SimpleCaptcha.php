<?php
namespace dubashi\SimpleCaptchaPhp;

/**
 * -= SimpleCaptcha =-
 *
 * Generate captcha image without using fonts,
 *	splitted captcha image
 *	or animated GIF captcha
 *
 * Public properties of Class for tuning Simple Captcha on construct Class
 *
 * @version 2.x
 *
 * @copyright Copyright (c) 2015 WebStudio 880
 * @author Gautam Dubashi
 * @contact qassabgautam at github.com
 *
 * @date 2015-10-01
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class SimpleCaptcha
{
	/*
	 * Public properties for tuning
	 */

	/**
	 * Width of result image(s)
	 *
	 * @var integer Pixels
	 */
	public $width			= 100;

	/**
	 * Height of result image(s)
	 *
	 * @var integer Pixels
	 */
	public $height			= 50;

	/**
	 * Type of result image(s)
	 *
	 * @var GD_const One of GD constants
	 */
	public $type			= IMAGETYPE_JPEG;

	/**
	 * Thinkness line of symbols in pixels
	 *
	 * @var integer Pixels
	 */
	public $thickness		= 2;

	/**
	 * Font color
	 *
	 * @var array [Red, Green, Blue] color decimal component
	 */
	public $colorFont = array( 30, 40, 50 );

	/**
	 * Background color
	 *
	 * @var array [Red, Green, Blue] color decimal component
	 */
	public $colorBackground = array( 255, 255, 255 );

	/**
	 * Flag for use dashed line style
	 *
	 * @var boolead
	 */
	public $dashed			= false;

	/**
	 * Flag for enable random position of verticies each one symbols
	 *
	 * @var boolean
	 */
	public $randPos			= false;

	/**
	 * Flag for enable random position of verticies point scheme
	 *
	 * @var boolean
	 */
	public $randPointPos	= false;

	/**
	 * Flag for use random symbols scheme
	 *
	 * @var boolean
	 */
	public $randSym			= false;

	/**
	 * Flag for use emboss filter for image
	 *
	 * @var boolean
	 */
	public $emboss			= false;

	/**
	 * Flag for added more nightmare for bots and your users
	 *
	 * @var boolean
	 */
	public $nightmare		= false;

	/**
	 * The duration for GIF frames (in 1/100s)
	 *
	 * @var integer
	 */
	public $duration		= 2;

	/**
	 * Loop GIF frames
	 *
	 * @var integer
	 */
	public $loop			= 0;

	/*
	 * Private properties
	 */

	/**
	 * GD Image(s)
	 *
	 * @var mixed
	 */
	private $_image;

	/**
	 * Animated GIF image
	 *
	 * @var mixed
	 */
	private $_gif;

	/**
	 * Gif transparent color index
	 *
	 * @var integer
	 */
	private $_transparentColor = -1;

	/**
	 * Count of point for X axe by symbol
	 *
	 * @var integer
	 */
	private $_pointsNumX = 2;

	/**
	 * Count of point for Y axe by symbol
	 *
	 * @var integer
	 */
	private $_pointsNumY = 3;

	/**
	 * Symbols scheme
	 *
	 * @var array
	 *
	 * Points-verticies for symbols scheme (example)
	 * 0___________1
	 * |  _  |  _  |
	 * | |_| | |_| |
	 * |_____|_____|
	 * |  _  |  _  |
	 * 2 |_| | |_| 3
	 * |_____|_____|
	 * |  _  |  _  |
	 * | |_| | |_| |
	 * |_____|_____|
	 * 4           5
	 */
	private $_symbols = array(
		'0' => array(
			array(0,1,5,4,0),
			array(0,1,3,5,4,2,0),
			array(0,1,3,5,4,0),
			array(0,1,5,4,2,0),
		),
		'1' => array(
			array(2,1,3,5),
			array(0,2,4),
			array(1,3,5),
		),
		'2' =>  array(
			array(0,1,3,2,4,5),
			array(0,1,2,4,5),
			array(0,1,3,4,5),
		),
		'3' =>  array(
			array(0,1,3,2,3,5,4),
			array(0,1,2,3,4),
			array(0,1,2,3,5,4),
		),
		'4' =>  array(
			array(5,3,1,3,2,0),
			array(5,3,1,2,3),
		),
		'5' =>  array(
			array(1,0,2,3,5,4),
			array(1,0,2,3,4),
		),
		'6' =>  array(
			array(1,0,2,4,5,3,2),
			array(1,2,4,5,3,2),
		),
		'7' =>  array(
			array(0,1,2,4),
			array(0,1,3,5),
			array(2,0,1,3,5),
		),
		'8' =>  array(
			array(1,0,4,5,3,2,3,1),
			array(1,0,2,4,5,3,2,1),
			array(1,0,2,4,5,3,1,3,2),
		),
		'9' =>  array(
			array(4,5,1,0,2,3),
			array(4,3,1,0,2,3),
			array(4,5,3,1,0,2,3),
		),
		'not found' => array(
			array(1,0,3,4,5,2,1),
		),
	);

	/**
	 * Constructor
	 *
	 * @param array $options Options values for tune Simple Captcha
	 * @description The every option name is equal public properties of Class
	 *
	 * @return self
	 */
	public function __construct (
		$options = array()
	) {

		foreach ( $options as $parameter=>$value )
		{
			if (
				isset($this->$parameter)
				&& substr($parameter, 0, 1) != '_'
			) {
				$this->$parameter = $value;
			}
		}

		return $this;
	}

	/**
	 * Destructor
	 */
	public function __destruct ()
	{
		if ( $this->_image )
		{
			foreach( (array)$this->_image as &$image ) {
				imagedestroy($image);
			}
		}
	}

	/**
	 * Set new symbols scheme, new points count for X and Y axes
	 *
	 * @param array $symbols New symbols scheme (will merged with current scheme)
	 * @param integer $pointsNumX Count of point for X axe by symbol
	 * @param integer $pointsNumY Count of point for Y axe by symbol
	 */
	public function setSymbolsScheme (
		$symbols,
		$pointsNumX = 2,
		$pointsNumY = 3
	) {

		$this->_symbols = array_merge( $this->_symbols, $symbols );

		$this->_pointsNumX = $pointsNumX;
		$this->_pointsNumY = $pointsNumY;
	}

	/**
	 * Output specified part of image into Browser
	 *
	 * @param integer $partNum Number part of image(s). Default: 0
	 *
	 * @return self
	 */
	public function outputIntoBrowser (
		$partNum = 0
	) {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sun, 22 Jul 2035 00:00:00 GMT");
		header("Content-Type: " . image_type_to_mime_type( $this->type ));

		$images = $this->_images();

		header("Content-Length: " . strlen( $images[ $partNum ] ));
		echo $images[ $partNum ];

		return $this;
	}

	/**
	 * Save Simple Captcha image(s) into file
	 *
	 * @param string $filename Filename with dirname path
	 * @param integer $partNum Number part of image(s). Default: null (all images)
	 *
	 * @return self
	 */
	public function outputIntoFile (
		$filename,
		$partNum = null
	) {
		$images = $this->_images();

		foreach( $images as $k=>$image )
		{
			if ( !($partNum === null || $partNum === $k) ) continue;

			file_put_contents(
					$filename
					. (
						($partNum === null && sizeof($images) > 1)
							? '.' . (string)$k
							: ''
					)
					. image_type_to_extension( $this->type ),
					$image
				);
		}
		return $this;
	}

	/**
	 * Returns base64 encoded data of specified part of image
	 *
	 * @param integer $partNum Number part of image(s). Default: 0
	 *
	 * @return string
	 */
	public function outputIntoDataURI (
		$partNum = 0
	) {
		$images = $this->_images();

		return
			'data:'
			. image_type_to_mime_type( $this->type )
			. ';base64,'
			. base64_encode( $images[ $partNum ] )
		;
	}

	/**
	 * Returns IMG tag with Simple Captcha image(s) using CSS3 backround property
	 *
	 * @return string The IMG tag for HTML page
	 */
	public function outputIntoImgHtml ()
	{

		$images = $this->_images();
		$count = sizeof( $images );
		$imagesDataUri = array();

		for( $i = 0; $i < $count; $i++ )
		{
			$imagesDataUri[] = ''
					. ( $i ? "url('" : "" )
					. 'data:'
						. image_type_to_mime_type( $this->type )
						. ';base64,'
						. base64_encode( $images[ $i ] )
					. ( $i ? "') left top no-repeat" : "" )
				;
		}

		return
			'<img src="'
			. array_shift($imagesDataUri)
			. '"'
			. ( $count > 1
					? ' style="background: '
						. implode(', ', $imagesDataUri)
						. '"'
					: ''
				)
			. ' alt="Simple Captcha"/>'
		;
	}

	/**
	 * Create Simple Captcha image
	 *
	 * @param string $stringCode The code for printing in captcha image
	 *
	 * @return self
	 */
	public function create (
		$stringCode
	) {
		$this->_image = $this->_createImage();

		$padding = 0;

		$areaWidth = $this->width - $padding * 2;
		$areaHeight = $this->height - $padding * 2;

		$symbols = str_split($stringCode);
		$countSym = sizeof($symbols);

		$charCellWidth = $areaWidth / $countSym;
		$charCellHeight = $areaHeight;

		$dX = $charCellWidth / $this->_pointsNumX - $this->thickness;
		$dY = $charCellHeight / $this->_pointsNumY - $this->thickness;

		$fX = $dX / 2;
		$fY = $dY / 2;

		$points = array();
		for( $kx = 0; $kx < $this->_pointsNumX; $kx++ )
		{
			for( $ky = 0; $ky < $this->_pointsNumY; $ky++ )
			{
				$sx = $this->randPointPos? mt_rand(0, $this->thickness) : 0;
				$sy = $this->randPointPos? mt_rand(0, $this->thickness) : 0;

				$points[ $kx + $ky*$this->_pointsNumX ] = array(
					$kx*$dX + $dX + $sx, // center of cell
					$ky*$dY + $dY + $sy,
				);
			}
		}

		$x = $padding + $this->thickness;
		$y = $padding + $this->thickness;

		foreach($symbols as $p=>$symbol)
		{
			$symbol = isset($this->_symbols[$symbol])? $symbol : 'not found';
			$key = $this->randSym? array_rand($this->_symbols[ $symbol ]) : 0;
			$sym = $this->_symbols[ $symbol ][ $key ];

			$polygon = array();
			foreach( $sym as $point )
			{
				$tx = $this->randPos? mt_rand(0, $fX ) : $fX/2;
				$ty = $this->randPos? mt_rand(0, $fY ) : $fY/2;

				$polygon[] = (int)($x + $points[$point][0] - $fX + $tx); // x
				$polygon[] = (int)($y + $points[$point][1] - $fY + $ty); // y
			}

			// returns path
			$polyRev = $polygon;

			array_pop($polyRev); // last point
			array_pop($polyRev);

			$polyRev = array_reverse($polyRev);

			foreach( $polyRev as $k=>$p )
			{
				if ( $k & 1 ) continue;
				$polygon[] = $polyRev[$k+1];
				$polygon[] = $polyRev[$k];
			}

			imagepolygon(
					$this->_image,
					$polygon,
					sizeof($polygon)/2,
					IMG_COLOR_STYLED
				);
			$x += $padding + $charCellWidth;
		}

		if ( $this->nightmare )
		{
			$size = $this->width > $this->height? $this->width : $this->height;
			for(
				$p = -$this->thickness/2, $i=0;
				$p < $size;
				$p += $this->thickness*2, $i++
			) {
				if ( !$i || $i&1 ) continue;

				$r = mt_rand(-1,1);
				imageline($this->_image,
						$p + $r,
						$points[0][1] - $fX/2,
						$p + $r,
						$points[4][1] - $fX/2,
						IMG_COLOR_STYLED
					);
			}
		}

		if ( $this->emboss )
		{
			$emboss = array(
				array(2, 0,  0),
				array(0, -1, 0),
				array(0, 0, -1),
			);
			imageconvolution(
					$this->_image,
					$emboss,
					1,
					255
				);
		}
		return $this;
	}

	/**
	 * Split source captcha image to parts
	 *
	 * @param integer $partsNum Number of parts for split image. Default: 2
	 * @param string $direct 'horisontal' or 'vertical' value. Default: vertical
	 *
	 * @return self
	 */
	public function split (
		$partsNum = 2,
		$direct = 'vertical'
	) {
		$count = sizeof( (array)$this->_image );
		if ( $count > 1 ) return $this;

		$images = array();

		for( $i = 0; $i < $partsNum; $i++ ) {

			$images[$i] = $this->_createImage();

			if ( $direct == 'vertical' )
			{
				$y = $i * $this->height / $partsNum;
				imagecopy(
						$images[$i],
						$this->_image,
						0,
						$y,
						0,
						$y,
						$this->width,
						$this->height / $partsNum
					);
			} else
			{
				$x = $i * $this->width / $partsNum;
				imagecopy(
						$images[$i],
						$this->_image,
						$x,
						0,
						$x,
						0,
						$this->width  / $partsNum,
						$this->height
					);
			}
		}
		imagedestroy($this->_image);

		$this->_image = $images;

		return $this;
	}

	// *** Private functions ***************************************************

	/**
	 * Create an GD image
	 *
	 * @return GD_image
	 */
	private function _createImage ()
	{
		$image = imagecreatetruecolor(
				$this->width,
				$this->height
			);

		imagealphablending($image, true);

		$backgroundColor = imagecolorallocatealpha(
				$image,
				$this->colorBackground[0],
				$this->colorBackground[1],
				$this->colorBackground[2],
				127
			);

		$fontColor = imagecolorallocate(
				$image,
				$this->colorFont[0],
				$this->colorFont[1],
				$this->colorFont[2]
			);

		imagefill(
				$image,
				0,
				0,
				$backgroundColor
			);

		imagesetstyle(
				$image,
				$this->dashed
					? array( $fontColor, $backgroundColor )
					: array( $fontColor )
			);

		imagesetthickness(
				$image,
				$this->thickness
			);

		imagesavealpha($image, true);

		return $image;
	}

	/**
	 * Returns array of image(s) source data or source GIF data
	 *
	 * @return array Source data of images
	 */
	private function _images ()
	{
		if ( $this->_possibleGifAnimation() )
		{
			$this->_makeAnimatedGif();
			$images = array( $this->_gif );
		} else
		{
			$images = $this->_raw();
		}
		return $images;
	}

	/**
	 * Returns array of image(s) source data
	 *
	 * @return array Source data of images
	 */
	private function _raw ()
	{
		$images = array();

		foreach( (array)$this->_image as &$image )
		{
			ob_start();
			switch( $this->type )
			{
				case IMAGETYPE_GIF:
						imagegif($image);
					break;
				case IMAGETYPE_PNG:
						imagepng(
								$image,
								null,
								9,
								PNG_ALL_FILTERS
							);
					break;
				case IMAGETYPE_JPEG:
				default:
						imagejpeg($image);
			}
			$images[] = ob_get_clean();
		}
		return $images;
	}

	/**
	 * Return true if image(s) can be GIF animated
	 *
	 * @return boolean
	 */
	private function _possibleGifAnimation()
	{
		return (
			sizeof( (array)$this->_image ) > 1
			&& $this->type == IMAGETYPE_GIF
		);
	}

	/**
	 * Make animated GIF
	 *
	 * @return $this
	 */
	private function _makeAnimatedGif ()
	{
		if ( ! $this->_possibleGifAnimation() ) return $this;

		$frames = $this->_raw();

		// Assemble the GIF header
		$this->_gif = 'GIF89a';

		if ( ord($frames[0] { 10 }) & 0x80 )
		{
			$cmap = 3 * (2 << (ord($frames[0] { 10 }) & 0x07));

			$this->_gif .=
				  substr($frames[0], 6, 7)
				. substr($frames[0], 13, $cmap)
			;

			if ($this->loop !== 1)
			{
				// Only add the looping extension if really looping
				$this->_gif .=
						"!\xFF\x0BNETSCAPE2.0\x03\x01"
						. $this->_word2bin( $this->loop==0 ? 0 : $this->loop-1 )
						. "\x0"
					;
			}
		}

		// Add GIF frames
		for ($i = 0; $i < sizeof($frames); $i++)
		{
			$this->_gifAddFrame( $frames, $i, $this->duration );
		}

		// Add the gif string footer char
		$this->_gif .= ';';

		return $this;
	}

	/**
	 * Add frame to the GIF data
	 *
	 * @param &array $frames
	 * @param integer $i index of frame source
	 * @param integer $d delay time (frame display duration)
	 */
	private function _gifAddFrame( &$frames, $i, $d)
	{
		$dis = 2; // "reset to bgnd." (http://www.matthewflickinger.com/lab/whatsinagif/animation_and_transparency.asp)

		$localsStr = 13 + 3 * (2 << (ord($frames[ $i ] { 10 }) & 0x07));

		$localsEnd = strlen($frames[$i]) - $localsStr - 1;
		$localsTmp = substr($frames[$i], $localsStr, $localsEnd);

		$globalLen = 2 << (ord($frames[0 ] { 10 }) & 0x07);
		$localsLen = 2 << (ord($frames[$i] { 10 }) & 0x07);

		$globalRGB = substr($frames[ 0], 13, 3 * (2 << (ord($frames[ 0] { 10 }) & 0x07)));
		$localsRGB = substr($frames[$i], 13, 3 * (2 << (ord($frames[$i] { 10 }) & 0x07)));

		$localsExt = "!\xF9\x04" . chr(($dis << 2) + 0) . $this->_word2bin($d) . "\x0\x0";

		if (
			$this->_transparentColor > -1
			&& ord($frames[$i] { 10 }) & 0x80
		) {
			for ( $j = 0; $j < (2 << (ord($frames[$i] { 10 } ) & 0x07)); $j++ )
			{
				if (
					ord($localsRGB { 3 * $j + 0 }) == (($this->_transparentColor >> 16) & 0xFF)
					&& ord($localsRGB { 3 * $j + 1 }) == (($this->_transparentColor >> 8) & 0xFF)
					&& ord($localsRGB { 3 * $j + 2 }) == (($this->_transparentColor >> 0) & 0xFF)
				) {
					$localsExt = "!\xF9\x04".chr(($dis << 2) + 1).chr(($d >> 0) & 0xFF).chr(($d >> 8) & 0xFF).chr($j)."\x0";
					break;
				}
			}
		}

		switch ( $localsTmp { 0 } )
		{
			case '!':
					$localsImg = substr($localsTmp, 8, 10);
					$localsTmp = substr($localsTmp, 18, strlen($localsTmp) - 18);
				break;
			case ',':
					$localsImg = substr($localsTmp, 0, 10);
					$localsTmp = substr($localsTmp, 10, strlen($localsTmp) - 10);
				break;
		}

		if (
			ord($frames[$i] { 10 }) & 0x80
			&& $i
		) {
			if ( $globalLen == $localsLen )
			{
				if ( $this->_gifBlockCompare($globalRGB, $localsRGB, $globalLen) )
				{
					$this->_gif .= $localsExt.$localsImg.$localsTmp;
				} else
				{
					$byte = ord($localsImg { 9 });
					$byte |= 0x80;
					$byte &= 0xF8;
					$byte |= (ord($frames[0] { 10 }) & 0x07);
					$localsImg { 9 } = chr($byte);

					$this->_gif .= $localsExt.$localsImg.$localsRGB.$localsTmp;
				}
			} else
			{
				$byte = ord($localsImg { 9 });
				$byte |= 0x80;
				$byte &= 0xF8;
				$byte |= (ord($frames[$i] { 10 }) & 0x07);
				$localsImg { 9 } = chr($byte);
				$this->_gif .= $localsExt.$localsImg.$localsRGB.$localsTmp;
			}
		} else
		{
			$this->_gif .= $localsExt.$localsImg.$localsTmp;
		}
	}

	/**
	 * Compare two blocks and return 1 if they are equal, 0 if differ.
	 *
	 * @param string $globalBlock
	 * @param string $localBlock
	 * @param integer $length
	 *
	 * @return integer
	 */
	private function _gifBlockCompare (
		$globalBlock,
		$localBlock,
		$length
	) {
		for ( $i = 0; $i < $length; $i++ )
		{
			if (
				$globalBlock [ 3 * $i + 0 ] != $localBlock [ 3 * $i + 0 ]
				|| $globalBlock [ 3 * $i + 1 ] != $localBlock [ 3 * $i + 1 ]
				|| $globalBlock [ 3 * $i + 2 ] != $localBlock [ 3 * $i + 2 ]
			) {
				return 0;
			}
		}
		return 1;
	}

    /**
	 * Convert an integer to 2-byte little-endian binary data
	 *
	 * @param integer $word Number to encode
	 *
	 * @return string of 2 bytes representing @word as binary data
	 */
	private function _word2bin (
		$word
	) {
		return (chr($word & 0xFF).chr(($word >> 8) & 0xFF));
	}
}
