<?php defined('_JEXEC') or die(file_get_contents('index.html'));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2015 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class FoxHtml
{
	const TAG_REGX = '/(<[^>]+ [^ =]*[ ]*=[ ]*["|\'][^\'"]*["|\'][^>]*>)/i';
	const SPLIT_TAG_REGX = '/([^<>\\/ ][^= ]+[ ]*=[ ]*["|\'][^\'"]*["|\'])[ ]*/i';
	const SPLIT_ATTRB_REGX = '/([ ]*=[ ]*)/i';
	
	public static function parseCss($css_text)
	{
		$css = array();
		if (!empty($css_text))
		{
			$css_text = self::removeComments($css_text);
			$css_text = str_replace(array('{', '}'), array(' { ', ' } '), $css_text);
			$css_text = self::getSpacedText($css_text);
			$matches = preg_split('/([a-z0-9-,\\. ]+)\\{[ ]*([^\\}]+)[ ]*\\}[ ]*/i', $css_text, null, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
			for ($i = 0, $len = count($matches); $i + 1 < $len; $i += 2)
			{
				$names = explode(',', $matches[$i]);
				$value = trim($matches[$i + 1]);
				if (!empty($value))
				{
					foreach ($names as $name)
					{
						$css[trim($name, '. ')] = $value;
					}
				
				}
			
			}
		
		}
		
		return $css;
	}
	
	
	private static function getSpacedText($text)
	{
		if (!empty($text))
		{
			$result = str_replace(array("\r", "\n", "\t"), array(' ', ' ', ' '), $text);
			while (strpos($result, '  ') !== false)
			{
				$result = str_replace('  ', ' ', $result);
			}
			
			return trim($result);
		}
		
		return '';
	}
	
	
	private static function removeComments($text)
	{
		return preg_replace('#/\\*(.*?)\\*/#s', '', $text);
	}
	
	
	public static function embedClassToStyles($html, $css)
	{
		$html = self::getSpacedText($html);
		if (!empty($html))
		{
			$matches_lev1 = preg_split(self::TAG_REGX, $html, null, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
			for ($i = 0, $len_i = count($matches_lev1); $i < $len_i; $i += 1)
			{
				if (preg_match(self::TAG_REGX, $matches_lev1[$i]))
				{
					$matches_lev1[$i] = self::embedClassToStylesOnTag($matches_lev1[$i], $css);
				}
			
			}
			
			return self::getSpacedText(implode('', $matches_lev1));
		}
		
		return '';
	}
	
	
	private static function embedClassToStylesOnTag($tag, $css)
	{
		$matches_lev2 = preg_split(self::SPLIT_TAG_REGX, $tag, null, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
		$open = rtrim($matches_lev2[0]);
		$style_from_class = '';
		$style_from_style = '';
		for ($j = 1, $len_j = count($matches_lev2) - 1; $j < $len_j; $j += 1)
		{
			$matches_lev3 = preg_split(self::SPLIT_ATTRB_REGX, $matches_lev2[$j], null, PREG_SPLIT_NO_EMPTY);
			if (count($matches_lev3) != 2)
			{
				$open .= ' ' . rtrim($matches_lev2[$j]);
				continue;
			}
			
			$attrb = strtolower($matches_lev3[0]);
			$quote = substr($matches_lev3[1], 0, 1);
			$value = trim($matches_lev3[1], $quote);
			switch ($attrb)
			{
				case 'style':
					$style_from_style = $value;
					break;
				case 'class':
					$style_from_class = self::convertClassToStyles($value, $css);
					break;
				default:
					$open .= ' ' . $attrb . '=' . $quote . $value . $quote;
					break;
			}
		
		}
		
		$style = self::notmalizeStyle($style_from_class . ';' . $style_from_style . ';');
		$close = self::normalizeClose($matches_lev2[$len_j]);
		return !empty($style) ? $open . ' style="' . $style . '"' . $close : $open . $close;
	}
	
	
	private static function notmalizeStyle($style)
	{
		while (strpos($style, ';;') !== false)
		{
			$style = str_replace(';;', ';', $style);
		}
		
		return ltrim($style, ';');
	}
	
	
	private static function normalizeClose($close)
	{
		$close = str_replace(array(' />', ' >'), array('/>', '>'), ltrim($close));
		$first_close_char = substr($close, 0, 1);
		return $first_close_char != '/' && $first_close_char != '>' ? " {$close}" : $close;
	}
	
	
	private static function convertClassToStyles($classes, $css)
	{
		$names = explode(' ', $classes);
		foreach ($names as &$name)
		{
			$name = isset($css[$name]) ? $css[$name] : '';
		}
		
		return implode(';', $names);
	}

}