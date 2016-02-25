<?php
class MathCaptcha {
	//	settings
	protected $captcha_w		= 150;
	protected $captcha_h		= 65;
	protected $min_font_size	= 12;
	protected $max_font_size	= 20;
	protected $angle			= 30;
	protected $bg_size			= 13;
	protected $font_path		= '/var/www/clarkpacific/htdocs/gourd04/fonts/courbd.ttf';
	protected $operators		= array('+','-','*');
	protected $root_path		= '/var/www/clarkpacific/htdocs/clarkpacific-new/web/z/CAPTCHA';
	protected $root_url			= 'http://clarkpacific.sjc.io/z/CAPTCHA';
	//	members
	public $equation			= NULL;
	public $img_url				= NULL;
	public $answer_hash			= NULL;
	public function set($kv) {
		if (is_array($kv)) {
			foreach ($kv as $k => $v) {
				if (isset($this->$k)) {
					$this->$k	= $v;
				}
			}
		}
		return $this;
	}
	public function __construct() {
		$this->generateEquation()->createImage();
	}
	protected function generateEquation() {
		$first_num			= rand(1,10);
		shuffle($this->operators);
		$operator			= $this->operators[0];
		$second_num			= rand(1,10);
		$expression			= $second_num.$operator.$first_num;
		eval('$answer = ' . $expression . ';');		
		$equation 			= array(
			'first_num'		=> $first_num,
			'operator'		=> $operator,
			'second_num'	=> $second_num,
			'expression'	=> $expression,
			'answer'		=> $answer
		);
		$this->equation		= $equation;
		$this->answer_hash	= md5($answer);
		return $this;
	}
	protected function createImage() {
		function randLight() {
			return rand(200,255);
		}
		function randMid() {
			return rand(100,200);
		}
		function randDark() {
			return rand(0,100);
		}
		extract($this->equation);
		$captcha_w		= $this->captcha_w;
		$captcha_h		= $this->captcha_h;
		$max_font_size	= $this->max_font_size;
		$min_font_size	= $this->min_font_size;
		$angle			= $this->angle;
		$bg_size		= $this->bg_size;
		$font_path		= $this->font_path;
		$img			= imagecreate( $captcha_w, $captcha_h );
		#	Some colors. Text is $black, background is $white, grid is $grey
		$black1			= imagecolorallocate($img,randDark(),randDark(),randDark());
		$black2			= imagecolorallocate($img,randDark(),randDark(),randDark());
		$black3			= imagecolorallocate($img,randDark(),randDark(),randDark());
		$white			= imagecolorallocate($img,randLight(),randLight(),randLight());
		$grey			= imagecolorallocate($img,randMid(),randMid(),randMid());
		imagefill( $img, 0, 0, $white );	
		/* the background grid lines - vertical lines */
		for ($t = $bg_size; $t<$captcha_w; $t+=$bg_size){
			imageline($img, $t, 0, $t, $captcha_h, $grey);
		}
		/* background grid - horizontal lines */
		for ($t = $bg_size; $t<$captcha_h; $t+=$bg_size){
			imageline($img, 0, $t, $captcha_w, $t, $grey);
		}
		/*
			this determinates the available space for each operation element 
			it's used to position each element on the image so that they don't overlap
		*/
		$item_space = $captcha_w/3;
		/* first number */
		imagettftext(
			$img,
			rand(
				$min_font_size,
				$max_font_size
			),
			rand( -$angle , $angle ),
			rand( 10, $item_space-20 ),
			rand( 25, $captcha_h-25 ),
			$black1,
			$font_path,
			$second_num
		);
		/* operator */
		switch ($operator) {
			case '+':
			$operator_word = 'plus';
			break;
			case '*':
			$operator_word = 'times';
			break;
			case '-':
			$operator_word = 'minus';
			break;
			default:
			$operator_word = $operator;
		}
		imagettftext(
			$img,
			rand(
				$min_font_size,
				$max_font_size
			),
			rand( -10, 10 ),
			rand( $item_space, 2*$item_space-20 ),
			rand( 25, $captcha_h-25 ),
			$black2,
			$font_path,
			$operator
		);
		/* second number */
		imagettftext(
			$img,
			rand(
				$min_font_size,
				$max_font_size
			),
			rand( -$angle, $angle ),
			rand( 2*$item_space, 3*$item_space-20),
			rand( 25, $captcha_h-25 ),
			$black3,
			$font_path
 		);
		$img_file_name	= $this->answer_hash . '.jpg';
		$full_img_path	= $this->root_path . '/' . $img_file_name;
		$this->img_url	= $this->root_url . '/' . $img_file_name;
		imagejpeg($img,$full_img_path,50);
		imagedestroy($img);
		return $this;
	}
}
?>