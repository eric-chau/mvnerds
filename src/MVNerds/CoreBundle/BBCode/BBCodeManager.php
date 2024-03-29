<?php

namespace MVNerds\CoreBundle\BBCode;

define ("EMOTICONS_DIR", "/markitup/images/");

class BBCodeManager
{
	// ----------------------------------------------------------------------------
	// markItUp! BBCode Parser
	// v 1.0.6
	// Dual licensed under the MIT and GPL licenses.
	// ----------------------------------------------------------------------------
	// Copyright (C) 2009 Jay Salvat
	// http://www.jaysalvat.com/
	// http://markitup.jaysalvat.com/
	// ----------------------------------------------------------------------------
	// Permission is hereby granted, free of charge, to any person obtaining a copy
	// of this software and associated documentation files (the "Software"), to deal
	// in the Software without restriction, including without limitation the rights
	// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	// copies of the Software, and to permit persons to whom the Software is
	// furnished to do so, subject to the following conditions:
	// 
	// The above copyright notice and this permission notice shall be included in
	// all copies or substantial portions of the Software.
	// 
	// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	// THE SOFTWARE.
	// ----------------------------------------------------------------------------
	// Thanks to Arialdo Martini, Mustafa Dindar for feedbacks.
	// ----------------------------------------------------------------------------
	private function escape($s) {
		global $text;
		$text = strip_tags($text);
		$code = $s[1];
		$code = htmlspecialchars($code);
		$code = str_replace("[", "&#91;", $code);
		$code = str_replace("]", "&#93;", $code);
		return '<pre><code>'.$code.'</code></pre>';
	}	
	private function removeBr($s) {
		return str_replace("<br />", "", $s[0]);
	}
	public function BBCode2Html($text) {
		$text = trim($text);
		
		$text = preg_replace_callback('/\[code\](.*?)\[\/code\]/ms',array($this, "escape"),$text);

		// Smileys to find...
		$in = array( 	 ':)', 	
						 ':d',
						 ':o',
						 ':p',
						 ':(',
						 ';)'
		);
		// And replace them by...
		$out = array(	 '<img class="smiley" alt=":)" src="'.EMOTICONS_DIR.'emoticon-smile.png" />',
						 '<img class="smiley" alt=":D" src="'.EMOTICONS_DIR.'emoticon-happy.png" />',
						 '<img class="smiley" alt=":o" src="'.EMOTICONS_DIR.'emoticon-surprised.png" />',
						 '<img class="smiley" alt=":p" src="'.EMOTICONS_DIR.'emoticon-tongue.png" />',
						 '<img class="smiley" alt=":(" src="'.EMOTICONS_DIR.'emoticon-unhappy.png" />',
						 '<img class="smiley" alt=";)" src="'.EMOTICONS_DIR.'emoticon-wink.png" />'
		);
		$text = str_replace($in, $out, $text);

		// BBCode to find...
		$in = array( 	 '/\[b\](.*?)\[\/b\]/ms',	
						 '/\[i\](.*?)\[\/i\]/ms',
						 '/\[u\](.*?)\[\/u\]/ms',
						 '/\[img\]\[size\="?(.*?)"?\](.*?)\[\/size\]\[\/img\]/ms',
						 '/\[img\](.*?)\[\/img\]/ms',
						 '/\[movie\](.*?)\[\/movie\]/ms',
						 '/\[email\](.*?)\[\/email\]/ms',
						 '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ms',
						 '/\[size\="?(.*?)"?\](.*?)\[\/size\]/ms',
						 '/\[color\="?(.*?)"?\](.*?)\[\/color\]/ms',
						 '/\[quote](.*?)\[\/quote\]/ms',
						 '/\[list\=(.*?)\](.*?)\[\/list\]/ms',
						 '/\[list\](.*?)\[\/list\]/ms',
						 '/\[\*\]\s?(.*?)\n/ms',
						 '/\[center](.*?)\[\/center]/ms',
						 '/\[h2\](.*?)\[\/h2\]/ms'
		);
		// And replace them by...
		$out = array(	 '<strong>\1</strong>',
						 '<em>\1</em>',
						 '<u>\1</u>',
						 '<img src="\2" alt="\2" style="width:\1px;"/>',
						 '<img src="\1" alt="\1" class="content-img" />',
						//'<div class="media-ressource"><iframe width="640" height="360" src="http://www.youtube.com/embed/\1" frameborder="0" allowfullscreen></iframe></div>',
						 '<div class="media-ressource"><object width="640" height="360"> <param name="movie" value="http://www.youtube.com/v/\1?version=3"></param><param name="allowScriptAccess" value="always"></param><embed src="http://www.youtube.com/v/\1?version=3" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="360"></embed></object></div>',
						 '<a href="mailto:\1">\1</a>',
						 '<a href="\1">\2</a>',
						 '<span style="font-size:\1%">\2</span>',
						 '<span style="color:\1">\2</span>',
						 '<blockquote><i class="icon-quote-left"></i> \1 <i class="icon-quote-right"></i></blockquote>',
						 '<ol start="\1">\2</ol>',
						 '<ul class="bbcode">\1</ul>',
						 '<li>\1</li>',
						 '<div class="center-content">\1</div>',
						 '<h2>\1</h2>'
		);
		$text = preg_replace($in, $out, $text);

		// paragraphs
		$text = str_replace("\r", "", $text);
		$text = "<p>".preg_replace("/(\n){2,}/", "</p><p>", $text)."</p>";
		//$text = nl2br($text);

		$text = preg_replace_callback('/<pre>(.*?)<\/pre>/ms', array($this, "removeBr"),$text);
		$text = preg_replace('/<p><pre>(.*?)<\/pre><\/p>/ms', "<pre>\\1</pre>", $text);

		$text = preg_replace_callback('/<ul>(.*?)<\/ul>/ms', array($this, "removeBr"),$text);
		$text = preg_replace('/<p><ul>(.*?)<\/ul><\/p>/ms', "<ul>\\1</ul>", $text);
		
		//Videos
//		$text = str_replace("http://www.youtube.com/watch?v=", "http://www.youtube.com/embed/", $text);
//		$text = preg_replace('/&feature=([a-z0-9-])*"/', '"', $text);
		
		return $text;
	}
}
