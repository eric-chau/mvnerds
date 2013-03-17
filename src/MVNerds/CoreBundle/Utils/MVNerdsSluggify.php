<?php

namespace MVNerds\CoreBundle\Utils;

/**
 * Classe qui permet de slugger une chaine caractères
 * 
 */
class MVNerdsSluggify
{
	public static function mvnerdsSluggify($v)
	{
		$in = array(
			'/[éèê]/u',
			'/[àâ]/u',
			'/[ïî]/u',
			'/[ç]/u',
			'/[öô]/u',
			'/[^\w]+/u'
		);

		$out = array(
			'e',
			'a',
			'i',
			'c',
			'o',
			'-'
		);

		$slug = preg_replace($in, $out, mb_strtolower($v, 'UTF-8'));
		
		return $slug;
	}
}