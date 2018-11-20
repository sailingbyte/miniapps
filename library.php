<?php

class SailingApps{
	
	private $_testmode = false;
	private $_apikey = '';
	private $_endpoint = 'https://logic.sailingbyte.com/api_v1/';
	private $_lastResponse = '';
	private $_imgCdn = 'https://cdn.sbyte.eu/';
	/**
	 *  @brief constructor
	 *  
	 *  @param [in] $apikey string API key
	 *  @param [in] $testmode bool if test all functions
	 *  @return null
	 *  
	 *  @details added 2018-06-06
	 */
	public function __construct($apikey, $testmode = false){
		$this->_apikey = $apikey;
		$this->_testmode = $testmode;
	}
	
	/**
	 *  @brief private CURL wrapper for easier CURL requests
	 *  
	 *  @param [in] $method API method which is being called
	 *  @param [in] $sendData data being forwarded to API
	 *  @return object or error
	 *  
	 *  @details added 2018-06-06
	 */
	private function _setCurl($method, $sendData){
		
		if($this->_testmode){
			print_r($sendData);
		}
		$sendData['key'] = $this->_apikey;
		
		$curl = curl_init($this->_endpoint.$method);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $sendData);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
		$response = curl_exec($curl);
		curl_close($curl);
		
		return $this->_jsonValidate($response);
	}
	
	/**
	 *  @brief validate JSON string reply from server
	 *  
	 *  @param [in] $string string reply from server
	 *  @return object or exception
	 *  
	 *  @details added 2018-06-06
	 */
	private function _jsonValidate($string){
		
		// decode the JSON data
		$result = json_decode($string);
		var_dump($string);

		// switch and check possible JSON errors
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				$error = ''; // JSON is valid // No error has occurred
				break;
			case JSON_ERROR_DEPTH:
				$error = 'The maximum stack depth has been exceeded.';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Invalid or malformed JSON.';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Control character error, possibly incorrectly encoded.';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON.';
				break;
			// PHP >= 5.3.3
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
				break;
			// PHP >= 5.5.0
			case JSON_ERROR_RECURSION:
				$error = 'One or more recursive references in the value to be encoded.';
				break;
			// PHP >= 5.5.0
			case JSON_ERROR_INF_OR_NAN:
				$error = 'One or more NAN or INF values in the value to be encoded.';
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$error = 'A value of a type that cannot be encoded was given.';
				break;
			default:
				$error = 'Unknown JSON error occured.';
				break;
		}

		if ($error !== '') {
			//throw Exception $e
			// throw the Exception or exit // or whatever :)
			//exit($error);
			throw new Exception($error);
		}

		// everything is OK
		return $result;
	}
	
	/**
	 *  @brief return last response
	 *  
	 *  @return object or exception
	 *  
	 *  @details added 2018-06-06
	 */
	public function response(){
		return $this->_lastResponse;
	}
	public function fullResponse(){
		return $this->_lastResponse;
	}
	
	/**
	 *  @brief Shorten URL
	 *  
	 *  @param [in] $url URL string to shorten
	 *  @return shortened url
	 *  
	 *  @details added 2018-06-06
	 */
	public function shorten($url){
		$this->_lastResponse = $this->_setCurl('set_url', ['url' => $url]);
		return $this->_lastResponse->data->url;
		//return $this->_lastResponse;
	}
	
	/**
	 *  @brief Paste caller
	 *  
	 *  @param [in] $data content being sent in string
	 *  @return shortened url
	 *  
	 *  @details added 2018-06-08
	 */
	public function paste($content, $options){
		$options['content'] = $content;
		$this->_lastResponse = $this->_setCurl('set_paste', $options);
		return $this->_lastResponse->data->url;
	}
	
	/**
	 *  @brief diff caller
	 *  
	 *  @param [in] $content1 left content
	 *  @param [in] $content2 right content
	 *  @return shortened url
	 *  
	 *  @details added 2018-06-08
	 */
	public function diff($content1, $content2){
		$this->_lastResponse = $this->_setCurl('set_diff', ['left' => $content1, 'right' => $content2]);
		return $this->_lastResponse->data->url;
	}
	
	/**
	 *  @brief Img optimizer wrapper
	 *  
	 *  @param [in] $imgUrl path to the file
	 *  @return shortened URL
	 *  
	 *  @details added 2018-06-08
	 */
	public function optimize($filepath){
		$this->_lastResponse = $this->_setCurl('optimize_image', [ 'photo' => new cURLFile($filepath) ]);
		return $this->_lastResponse->data->url;
	}
	
	/**
	 *  @brief optimize file from given URL
	 *  
	 *  @param [in] $imgUrl URL of the file
	 *  @return shortened URL
	 *  
	 *  @details added 2018-06-08
	 */
	public function optimizeUrl($imgUrl){
		$name = explode('.', $imgUrl);
		$extension = '.'.$name[count($name) -1 ];
		$tmpPath = uniqid().$extension;
		$content = file_get_contents($imgUrl);
		file_put_contents($tmpPath, $content);
		$this->_lastResponse = $this->_setCurl('optimize_image', [ 'photo' => new cURLFile($tmpPath) ]);
		unlink($tmpPath);
		return $this->_lastResponse->data->url;
		
	}
	
	/**
	 *  @brief Share caller
	 *  
	 *  @param [in] $data content being sent in string
	 *  @return generated share html
	 *  
	 *  @details added 2018-06-08
	 */
	public function share($url){
		$this->_lastResponse = $this->_setCurl('set_share', ['url' => $url]);
		return $this->_lastResponse->data->html;
	}
	
	/**
	 *  @brief Share caller
	 *  
	 *  @param [in] $data content being sent in string
	 *  @return generated share html
	 *  
	 *  @details added 2018-06-08
	 */
	public function cron($url, $frequency){
		$this->_lastResponse = $this->_setCurl('set_cron', ['url' => $url, 'frequency' => $frequency]);
		return $this->_lastResponse->data->url;
	}
	
	/**
	 *  @brief append Lazy Loader
	 *  
	 *  @param [in] $html html page
	 *  @return html string
	 *  
	 *  @details added 2018-06-14
	 */
	public function appendLazy($html, $host, $useCDN = false, $breakpoints = ['small' => 400]){
		 
		$lazyString = "<script>
  (function(q,m){\"function\"===typeof define&&define.amd?define(m):\"object\"===typeof exports?module.exports=m():q.Blazy=m()})(this,function(){function q(b){var c=b._util;c.elements=E(b.options);c.count=c.elements.length;c.destroyed&&(c.destroyed=!1,b.options.container&&l(b.options.container,function(a){n(a,\"scroll\",c.validateT)}),n(window,\"resize\",c.saveViewportOffsetT),n(window,\"resize\",c.validateT),n(window,\"scroll\",c.validateT));m(b)}function m(b){for(var c=b._util,a=0;a<c.count;a++){var d=c.elements[a],e;a:{var g=d;e=b.options;var p=g.getBoundingClientRect();if(e.container&&y&&(g=g.closest(e.containerClass))){g=g.getBoundingClientRect();e=r(g,f)?r(p,{top:g.top-e.offset,right:g.right+e.offset,bottom:g.bottom+e.offset,left:g.left-e.offset}):!1;break a}e=r(p,f)}if(e||t(d,b.options.successClass))b.load(d),c.elements.splice(a,1),c.count--,a--}0===c.count&&b.destroy()}function r(b,c){return b.right>=c.left&&b.bottom>=c.top&&b.left<=c.right&&b.top<=c.bottom}function z(b,c,a){if(!t(b,a.successClass)&&(c||a.loadInvisible||0<b.offsetWidth&&0<b.offsetHeight))if(c=b.getAttribute(u)||b.getAttribute(a.src)){c=c.split(a.separator);var d=c[A&&1<c.length?1:0],e=b.getAttribute(a.srcset),g=\"img\"===b.nodeName.toLowerCase(),p=(c=b.parentNode)&&\"picture\"===c.nodeName.toLowerCase();if(g||void 0===b.src){var h=new Image,w=function(){a.error&&a.error(b,\"invalid\");v(b,a.errorClass);k(h,\"error\",w);k(h,\"load\",f)},f=function(){g?p||B(b,d,e):b.style.backgroundImage='url(\"'+d+'\")';x(b,a);k(h,\"load\",f);k(h,\"error\",w)};p&&(h=b,l(c.getElementsByTagName(\"source\"),function(b){var c=a.srcset,e=b.getAttribute(c);e&&(b.setAttribute(\"srcset\",e),b.removeAttribute(c))}));n(h,\"error\",w);n(h,\"load\",f);B(h,d,e)}else b.src=d,x(b,a)}else\"video\"===b.nodeName.toLowerCase()?(l(b.getElementsByTagName(\"source\"),function(b){var c=a.src,e=b.getAttribute(c);e&&(b.setAttribute(\"src\",e),b.removeAttribute(c))}),b.load(),x(b,a)):(a.error&&a.error(b,\"missing\"),v(b,a.errorClass))}function x(b,c){v(b,c.successClass);c.success&&c.success(b);b.removeAttribute(c.src);b.removeAttribute(c.srcset);l(c.breakpoints,function(a){b.removeAttribute(a.src)})}function B(b,c,a){a&&b.setAttribute(\"srcset\",a);b.src=c}function t(b,c){return-1!==(\" \"+b.className+\" \").indexOf(\" \"+c+\" \")}function v(b,c){t(b,c)||(b.className+=\" \"+c)}function E(b){var c=[];b=b.root.querySelectorAll(b.selector);for(var a=b.length;a--;c.unshift(b[a]));return c}function C(b){f.bottom=(window.innerHeight||document.documentElement.clientHeight)+b;f.right=(window.innerWidth||document.documentElement.clientWidth)+b}function n(b,c,a){b.attachEvent?b.attachEvent&&b.attachEvent(\"on\"+c,a):b.addEventListener(c,a,{capture:!1,passive:!0})}function k(b,c,a){b.detachEvent?b.detachEvent&&b.detachEvent(\"on\"+c,a):b.removeEventListener(c,a,{capture:!1,passive:!0})}function l(b,c){if(b&&c)for(var a=b.length,d=0;d<a&&!1!==c(b[d],d);d++);}function D(b,c,a){var d=0;return function(){var e=+new Date;e-d<c||(d=e,b.apply(a,arguments))}}var u,f,A,y;return function(b){if(!document.querySelectorAll){var c=document.createStyleSheet();document.querySelectorAll=function(a,b,d,h,f){f=document.all;b=[];a=a.replace(/\[for\b/gi,\"[htmlFor\").split(\",\");for(d=a.length;d--;){c.addRule(a[d],\"k:v\");for(h=f.length;h--;)f[h].currentStyle.k&&b.push(f[h]);c.removeRule(0)}return b}}var a=this,d=a._util={};d.elements=[];d.destroyed=!0;a.options=b||{};a.options.error=a.options.error||!1;a.options.offset=a.options.offset||100;a.options.root=a.options.root||document;a.options.success=a.options.success||!1;a.options.selector=a.options.selector||\".b-lazy\";a.options.separator=a.options.separator||\"|\";a.options.containerClass=a.options.container;a.options.container=a.options.containerClass?document.querySelectorAll(a.options.containerClass):!1;a.options.errorClass=a.options.errorClass||\"b-error\";a.options.breakpoints=a.options.breakpoints||!1;a.options.loadInvisible=a.options.loadInvisible||!1;a.options.successClass=a.options.successClass||\"b-loaded\";a.options.validateDelay=a.options.validateDelay||25;a.options.saveViewportOffsetDelay=a.options.saveViewportOffsetDelay||50;a.options.srcset=a.options.srcset||\"data-srcset\";a.options.src=u=a.options.src||\"data-src\";y=Element.prototype.closest;A=1<window.devicePixelRatio;f={};f.top=0-a.options.offset;f.left=0-a.options.offset;a.revalidate=function(){q(a)};a.load=function(a,b){var c=this.options;void 0===a.length?z(a,b,c):l(a,function(a){z(a,b,c)})};a.destroy=function(){var a=this._util;this.options.container&&l(this.options.container,function(b){k(b,\"scroll\",a.validateT)});k(window,\"scroll\",a.validateT);k(window,\"resize\",a.validateT);k(window,\"resize\",a.saveViewportOffsetT);a.count=0;a.elements.length=0;a.destroyed=!0};d.validateT=D(function(){m(a)},a.options.validateDelay,a);d.saveViewportOffsetT=D(function(){C(a.options.offset)},a.options.saveViewportOffsetDelay,a);C(a.options.offset);l(a.options.breakpoints,function(a){if(a.width>=window.screen.width)return u=a.src,!1});setTimeout(function(){q(a)})}});
		/*BE LAZY*/

			var bLazy = new Blazy({ ";
			
		$breakpointsArr = [];
		foreach($breakpoints as $b => $v){
			
			$breakpointsArr[] = "
			breakpoints: [{
				width: $v,
				src: 'data-src-$b'
			}]
			";
		}
		$lazyString .= implode(',', $breakpointsArr);
		$lazyString .= "
			  ,success: function(element){
					setTimeout(function(){
						var parent = element.parentNode;
						parent.className = parent.className.replace(/\bloading\b/,'');
					}, 200);
				}
			});
			
		</script>
		";
		
		$html = str_ireplace("</body>", $lazyString."</body>", $html);
		
		//change image soruces
		$html = $this->_changeImgSrc($html, $host, $useCDN, $breakpoints);
		
		return $html;
		
	}
	/**
	 *  @brief change img src to data-src with loader
	 *  
	 *  @param [in] $html html string
	 *  @return html string
	 *  
	 *  @details added 2018-06-14
	 */
	private function _changeImgSrc($html, $host, $useCDN, $breakpoints){
		
		$loading = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
		
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$imgs = $doc->getElementsByTagName('img');
		foreach($imgs as $img) {
			
			$originalSource = $img->getAttribute('src');
			
			if($useCDN){
				//find original source full url
				//if URL contains http data
				if(stripos($originalSource, 'http:') !== false || stripos($originalSource, 'https:') !== false){
					$fullImageUrl = $originalSource;
				}else{
					$fullImageUrl = $host.$originalSource;
				}
				$ext = explode('.', $fullImageUrl);
				$ext = '.'.$ext[count($ext) -1 ];
				$srcUrl = $this->_imgCdn.base64_encode($fullImageUrl).$ext;
			}else{
				$srcUrl = $originalSource;
			}
			
			$img->setAttribute('data-src', $srcUrl);
			foreach($breakpoints as $b => $v){
				$img->setAttribute('data-src-'.$b, $srcUrl.'?'.$v);
			}
			$img->setAttribute('src', $loading);
			
			$class = $img->getAttribute('class');
			$img->setAttribute('class', $class.' b-lazy');
			
			//echo $n->nodeValue;
		}
		
		return $doc->saveHtml();
	}
}

//launch manual testing
$debug = false;
$app = new SailingApps('arosdhisrwoquhaoekterstwoqiu', $debug); //insert API key here

if($debug){

	echo "<pre>";

	echo '<h1>Shorten</h1>';
	$response = $app->shorten('http://your-very-long-link.com'); //your long link
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response

	//paste bin
	echo '<h1>Paste</h1>';
	$response = $app->paste('very long text that needs to be shared somewhere and I dont want to copy paste it somewhere anyway'); //your long text
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response
	
	//paste bin
	echo '<h1>Paste advanced</h1>';
	$response = $app->paste('very long text that needs to be shared somewhere and I dont want to copy paste it somewhere anyway', ['title' => 'Your title', 'syntax' => 'html', 'manual_expiration' => '2020-02-02']); //your long text
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response

	//diff
	echo '<h1>Diff</h1>';
	$response = $app->diff("abc\ndef", "abc\ndeg"); //your text to compare
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response
	
	//optimize
	echo '<h1>Optimize</h1>';
	$response = $app->optimize("image.png"); //your URL file to optimize
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response
	
	
	//optimize from URL
	echo '<h1>Optimize from URL</h1>';
	$response = $app->optimizeUrl("https://sbyte.eu/storage/2018-06-08-10-28-34-5b1a5a524b86c-optimized.png"); //your URL file to optimize
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response
	
	//share
	echo '<h1>Share</h1>';
	$response = $app->share("http://example.com"); //your text to compare
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response
	
	//share
	echo '<h1>CRON job</h1>';
	$response = $app->cron("http://example.com", '15m'); //your text to compare
	var_dump($response); //link only
	var_dump($app->fullResponse()); //full last JSON response

	echo "</pre>";

}