<?php

	function EHTArrayToString ($array, $depth = 0)
	{
		if ($depth > 0)
		{
			$tab = implode ('', array_fill (0, $depth, "\t"));	
		}
		$text .= "array (\n";
		$count = count ($array);
		
		$x = 0;
		foreach ($array as $key => $value)
		{
			$x++;
			
			if (is_array ($value))
			{
				if (substr ($text, -1, 1) == ')')
				{
					$text .= ',';
				}
				$text .= $tab . "\t" . '"' . $key . '"' . " => " . EHTArrayToString ($value, $depth + 1);
				continue;
			}
			
			$text .= $tab . "\t" . "\"$key\" => \"$value\"";
			
			if ($count != $x)
			{
				$text .= ",\n";
			}
		}
		
		$text .= "\n" . $tab . ")\n";
		
		if (substr ($text, -4, 4) == '),),')
		{
			$text .= '))';
		}
		
		return ($text);
	}
	
	function EHTGetVariable ($name)
	{
		if (isset ($_GET[$name]))
		{
			$variable = $_GET[$name];
		}
		else if (isset ($_POST[$name]))
		{
			$variable = $_POST[$name];
		}
		else
		{
			$variable = "";
		}
		
		return ($variable);
	}
	function EHTGetSession ($variable, $domain)
	{
		return (isset ($_SESSION[$domain . $variable]) ? $_SESSION[$domain . $variable] : "");
	} 
	function EHTSetSesion ($variable, $value = "")
	{
		$previousValue = $_SESSION[$domain . $variable];
		$_SESSION[$domain . $variable] = $value;
		
		return ($previousValue);
	}
	
	function EHTReadableFileSize ($size)
	{
		$sizes = array ("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
		$lastSizeString = end ($sizes);
		$result = $size;
		foreach ($sizes as $sizeString)
		{
			if ($result < 1024)
			{
				break;
			}
			if ($sizeString != $latSizeString)
			{
				$result /= 1024;
			}
		}
		$format = ($sizeString == $sizes[0]) ? "%01d%s" : "%01.2f%s";
	
		return (sprintf ($format, $result, $sizeString));
	}

?>