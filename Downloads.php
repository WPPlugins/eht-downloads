<?php
/*
Plugin Name: EHT Downloads
Plugin URI: http://emiliogonzalez.sytes.net/index.php/2007/10/05/eht-downloads-plugin-para-wordpress/
Description: This plugin generates automatically downloads lists, just giving a path and URL the plugin will generate all the HTML code itself.
Author: Emilio Gonz&aacute;lez Monta&ntilde;a
Version: 0.3
Author URI: http://emiliogonzalez.sytes.net/

History:	0.1		First release.
			0.1.1	Not listing the files like beginning with a dot.
			0.2		Remove path parameter and compose it from the url parameter.
			0.3		Added options page into admin menu, for: colors (border, even and odd rows) and identation.

Plugin sintax:

[downloads url={1} recursive={2}]

Where:
   {1} this is the URL path of the downloads
   {2} yes|no to use a recursive file tree or a file list.

Example:

[donwloads url=/downloads recursive=yes]

*/

add_filter ("the_content", "EHTFilterTheContent");
add_action ("admin_menu", "EHTAdminAddPages");

define ("EHT_DOWNLOADS_SESSION_DOMAIN", "eht-downloads");
define ("EHT_DOWNLOADS_PLUGIN_BASE", "/wp-content/plugins/eht-downloads/");
define ("EHT_DOWNLOADS_PLUGIN_BASE_IMAGES", EHT_DOWNLOADS_PLUGIN_BASE . "images/");
define ("EHT_DOWNLOADS_OPTION_COLOR_BORDER", "eht-downloads-option-color-border");
define ("EHT_DOWNLOADS_OPTION_COLOR_EVEN", "eht-downloads-option-color-even");
define ("EHT_DOWNLOADS_OPTION_COLOR_ODD", "eht-downloads-option-color-odd");
define ("EHT_DOWNLOADS_OPTION_IDENTATION", "eht-downloads-option-identation");
define ("EHT_DOWNLOADS_FIELD_ACTION", "eht-downloads-field-action");
define ("EHT_DOWNLOADS_ACTION_UPDATE", "update");
define ("EHT_DOWNLOADS_DEFAULT_COLOR_BORDER", "#F6F6F6");
define ("EHT_DOWNLOADS_DEFAULT_COLOR_EVEN", "#F6F6F6");
define ("EHT_DOWNLOADS_DEFAULT_COLOR_ODD", "#FFFFFF");
define ("EHT_DOWNLOADS_DEFAULT_IDENTATION", 6);

require_once ("classes/Utilities.php");
require_once ("classes/Folder.php");

$ehtDownloadsCount = 0;

function EHTFilterTheContent ($content)
{
	global $ehtDownloadsCount;
	
	$search = "/\[downloads\s*url\s*=\s*([^\]]+)\s*recursive\s*=\s*([^\]]+)\s*\]/i";

	preg_match_all ($search, $content, $results);

	if (is_array ($results[1]))
	{
		$colorBorder = get_option (EHT_DOWNLOADS_OPTION_COLOR_BORDER);
		$colorEven = get_option (EHT_DOWNLOADS_OPTION_COLOR_EVEN);
		$colorOdd = get_option (EHT_DOWNLOADS_OPTION_COLOR_ODD);
		$identation = get_option (EHT_DOWNLOADS_OPTION_IDENTATION);
		if ($colorBorder == "")
		{
			$colorBorder = EHT_DOWNLOADS_DEFAULT_COLOR_BORDER;
		}
		if ($colorEven == "")
		{
			$colorEven = EHT_DOWNLOADS_DEFAULT_COLOR_EVEN;
		}
		if ($colorOdd == "")
		{
			$colorOdd = EHT_DOWNLOADS_DEFAULT_COLOR_ODD;
		}
		if ($identation == "")
		{
			$identation = EHT_DOWNLOADS_DEFAULT_IDENTATION;
		}
		
		for ($m = 0; $m < count ($results[0]); $m++)
		{
			if ($m == 0)
			{
				$url = trim ($results[1][$m]);
				if ($url[0] != '/')
				{
					$url = "/" . $url;
				}
				$recursive = (strcasecmp (trim ($results[2][$m]), "yes") == 0);
				$path = $_SERVER["DOCUMENT_ROOT"] . $url;
				
				$text = "";
				
				$text .= "<style type=\"text/css\">\n";
				$text .= "   table.eht-downloads";
				$text .= "   {";
				$text .= "      border-width: 1px;";
				$text .= "      border-color: $colorBorder;";
				$text .= "      border-spacing: 0px;";
				$text .= "      border-collapse: collapse;";
				$text .= "   }";
				$text .= "   table.eht-downloads tr.even";
				$text .= "   {";
				$text .= "      background-color: $colorEven;";
				$text .= "   }";
				$text .= "   table.eht-downloads tr.odd";
				$text .= "   {";
				$text .= "      background-color: $colorOdd;";
				$text .= "   }";
				$text .= "</style>";

				$text .= "<table class=\"eht-downloads\" border=\"0\" space=\"0\" width=\"100%\">\n";
				$ehtDownloadsCount = 0;
				$text .= EHTListFiles ($path, $url, 0, $recursive, $identation);
				$text .= "<tr><td class=\"" . (((($ehtDownloadsCount) % 2) == 0) ? "odd" : "even") . "\" colspan=\"2\"><center>Plugin <a href=\"http://emiliogonzalez.sytes.net/index.php/2007/10/05/eht-donwloads-plugin-para-wordpress/\" target=\"_blank\">EHT Downloads</a> - Created by <a href=\"http://emiliogonzalez.sytes.net\" target=\"_blank\">Emilio Gonz&aacute;lez Monta&ntilde;a</a></center></td></tr>\n";
				$text .= "</table><br>\n";
                
				$content = str_replace ($results[0][$m], $text, $content);
			}
		}
	}

	return ($content);
}

function EHTListFiles ($path, $url, $deep, $recursive, $identation)
{
	$folder = new EHTFolder ($path);
	$result = $folder->FilteredList ();
	$text .= EHTListFileArray ($path, $url, $result[0], true, $deep, $recursive, $identation);
	$text .= EHTListFileArray ($path, $url, $result[1], false, $deep, $recursive, $identation);

	return ($text);
}

function EHTListFileArray ($path, $url, $files, $isFolder, $deep, $recursive, $identation)
{
	global $ehtDownloadsCount;

	if (is_array ($files))
	{
		for ($i = 0; $i < count ($files); $i++)
		{
			$newPath = new EHTFolder ($path);
			$newPath->AddFolder ($files[$i], false);
			$newUrl = new EHTFolder ($url);
			$newUrl->AddFolder ($files[$i], false);
			$filePath = EHTFolder::QuitEndingSlashes ($newPath->GetPath ());
			$urlPath = EHTFolder::QuitEndingSlashes ($newUrl->GetPath ());
			$info = pathinfo ($filePath);
			$extension = $info["extension"];
		
			if (($files[$i][0] != '.') && ($extension != "phps"))
			{
				$text .= "<tr valign=\"top\" class=\"" . (((($ehtDownloadsCount++) % 2) == 0) ? "even" : "odd") . "\"><td";
				if ($isFolder)
				{
					$text .= " colspan=\"2\"";
				}
				else
				{
					$text .= " width=\"100%\"";
				}
				$text .= ">\n";
				
				for ($j = 0; $j < ($deep * $identation); $j++)
				{
					$text .= "&nbsp;";
				}
				
				$icon = "<img src=\"" . EHT_DOWNLOADS_PLUGIN_BASE_IMAGES;
				if ($isFolder)
				{
					$icon .= "IconFolder.png";
				}
				else
				{
					switch ($extension)
					{
						case "ace":
						case "zip":
						case "arj":
						case "lzh":
						case "rar":
						case "cab":
						case "gz":
						case "tar":
						case "tgz":
						case "bz2":
							$icon .= "IconCompressed.png";
							break;
						case "exe":
						case "msi":
							$icon .= "IconExecutable.png";
							break;
						case "bmp":
						case "gif":
						case "jpeg":
						case "jpg":
						case "png":
							$icon .= "IconImage.png";
							break;
						case "pdf":
							$icon .= "IconPDF.png";
							break;
						case "asm":
						case "c":
						case "cpp":
						case "txt":
						case "java":
						case "vb":
						case "pas":
						case "bat":
						case "pl":
						case "py":
							$icon .= "IconSource.png";
							break;
						case "php":
							$icon .= "IconSource.png";
							copy ($filePath, $filePath . "s");
							$urlPath .= "s";
							break;
						default:
							$icon .= "IconFile.png";
							break;
					}
				}
				$icon .= "\">&nbsp;";
				
				if (!$isFolder)
				{
					$text .= "<a href=\"" . $urlPath . "\" target=\"_blank\">";
				}
				$text .= $icon;
				$text .= $files[$i];
				if (!$isFolder)
				{
					$text .= "</a>";
					$text .= "</td><td>";
					$fileSize = filesize ($filePath);
					$text .= EHTReadableFileSize ($fileSize);
				}
				$text .= "</td></tr>\n";
				
				if ($isFolder && $recursive)
				{
					$text .= EHTListFiles ($newPath->GetPath (), $newUrl->GetPath (), $deep + 1, $recursive, $identation);
				}
			}
		}
	}
	
	return ($text);
}

function EHTAdminAddPages ()
{
	add_options_page ('EHT Downloads', 'EHT Downloads', 8, 'eht-downloads-options', 'EHTAdminOptions');
}

function EHTAdminOptions ()
{
	$action = $_POST[EHT_DOWNLOADS_FIELD_ACTION];
	if ($action == EHT_DOWNLOADS_ACTION_UPDATE)
	{
		$colorBorder = $_POST[EHT_DOWNLOADS_OPTION_COLOR_BORDER];
		$colorEven = $_POST[EHT_DOWNLOADS_OPTION_COLOR_EVEN];
		$colorOdd = $_POST[EHT_DOWNLOADS_OPTION_COLOR_ODD];
		$identation = $_POST[EHT_DOWNLOADS_OPTION_IDENTATION];
	}
	else
	{
		$colorBorder = get_option (EHT_DOWNLOADS_OPTION_COLOR_BORDER);
		$colorEven = get_option (EHT_DOWNLOADS_OPTION_COLOR_EVEN);
		$colorOdd = get_option (EHT_DOWNLOADS_OPTION_COLOR_ODD);
		$identation = get_option (EHT_DOWNLOADS_OPTION_IDENTATION);
	}

	if ($colorBorder == "")
	{
		$colorBorder = EHT_DOWNLOADS_DEFAULT_COLOR_BORDER;
		$action = EHT_DOWNLOADS_ACTION_UPDATE;
	}
	if ($colorEven == "")
	{
		$colorEven = EHT_DOWNLOADS_DEFAULT_COLOR_EVEN;
		$action = EHT_DOWNLOADS_ACTION_UPDATE;
	}
	if ($colorOdd == "")
	{
		$colorOdd = EHT_DOWNLOADS_DEFAULT_COLOR_ODD;
		$action = EHT_DOWNLOADS_ACTION_UPDATE;
	}
	if ($identation == "")
	{
		$identation = EHT_DOWNLOADS_DEFAULT_IDENTATION;
		$action = EHT_DOWNLOADS_ACTION_UPDATE;
	}
	
	if ($action == EHT_DOWNLOADS_ACTION_UPDATE)
	{
        update_option (EHT_DOWNLOADS_OPTION_COLOR_BORDER, $colorBorder);
        update_option (EHT_DOWNLOADS_OPTION_COLOR_EVEN, $colorEven);
        update_option (EHT_DOWNLOADS_OPTION_COLOR_ODD, $colorOdd);
        update_option (EHT_DOWNLOADS_OPTION_IDENTATION, $identation);
		echo "<div class=\"updated\">The options have been updated.</div>\n";
	}

	echo "<div class=\"wrap\">\n";
	echo "<h2>EHT Downloads</h2>\n";
	echo "<form method=\"post\" action=\"" . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . "\">\n";
	echo "<input type=\"hidden\" name=\"" . EHT_DOWNLOADS_FIELD_ACTION . "\" value=\"" . EHT_DOWNLOADS_ACTION_UPDATE . "\">\n";
	echo "<p>Border color:<br>\n";
	echo "<input type=\"text\" name=\"" . EHT_DOWNLOADS_OPTION_COLOR_BORDER . "\" value=\"" . $colorBorder . "\"></p>\n";
	echo "<p>Even row color:<br>\n";
	echo "<input type=\"text\" name=\"" . EHT_DOWNLOADS_OPTION_COLOR_EVEN . "\" value=\"" . $colorEven . "\"></p>\n";
	echo "<p>Odd row color:<br>\n";
	echo "<input type=\"text\" name=\"" . EHT_DOWNLOADS_OPTION_COLOR_ODD . "\" value=\"" . $colorOdd . "\"></p>\n";
	echo "<p>Identation along tree nodes:<br>\n";
	echo "<input type=\"text\" name=\"" . EHT_DOWNLOADS_OPTION_IDENTATION . "\" value=\"" . $identation . "\"></p>\n";
	echo "<p class=\"submit\">\n";
	echo "<input type=\"submit\" value=\"Update Options\">\n";
	echo "</p>\n";
	echo "</form>\n";
	echo "</div>\n";
	echo "<p align=\"center\">Plugin <a href=\"http://emiliogonzalez.sytes.net/index.php/2007/10/05/eht-donwloads-plugin-para-wordpress/\" target=\"_blank\">EHT Downloads</a> - Created by <a href=\"http://emiliogonzalez.sytes.net\" target=\"_blank\">Emilio Gonz&aacute;lez Monta&ntilde;a</a></p>\n";
}

?>