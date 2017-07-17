<?php

require_once ("Utilities.php");

class EHTFolder
{

    public function __construct ($path)
    {
		$this->SetPath ($path);
    }

    public function GetPath ()
    {
		return ($this->path);
    }
    public function GetParentPath ()
    {
		$cleanPath = substr ($this->path, 0, strlen ($this->path) - 2);
		$position = strrpos ($cleanPath, DIRECTORY_SEPARATOR);
		if ($position === false)
		{
		    $parent = "/";
		}
		else
		{
		    $parent = substr ($this->path, 0, $position);
		}
		
		return ($parent);
    }
    public function SetPath ($path)
    {
		$this->path = $path;
		
		if ((strlen ($this->path) <= 0) ||
		    ($this->path{strlen ($this->path) - 1} != DIRECTORY_SEPARATOR))
		{
		    $this->path .= DIRECTORY_SEPARATOR;
		}
    }
    public function AddFolder ($folder, $create)
    {
    	if ($create)
    	{
    		$pieces = explode (DIRECTORY_SEPARATOR, $folder);
    		$path = $this->path;
    		if (file_exists ($path) && is_dir ($path) && is_array ($pieces))
    		{
    			for ($i = 0; $i < count ($pieces); $i++)
    			{
    				$path .= $pieces[$i] . DIRECTORY_SEPARATOR;
    				if (!file_exists ($path))
    				{
    					mkdir ($path);
    				}
    			}
    		}
    	}
		$this->SetPath (EHTFolder::Concat ($this->path, $folder));
    }
	    
    public function FilteredList ($filter = "*")
    {
		$folder = opendir ($this->path);
		$files[0] = array ();
		$files[1] = array ();
		while (false !== ($entry = readdir ($folder)))
		{
		    if (($entry != ".") && ($entry != ".."))
		    {
				if (is_dir ($this->path . $entry))
				{
				    $files[0][] = $entry;
				}
				else if (($filter == "*") ||
					 (preg_match ("/" . $filter . "/i", $entry)))
				{
				    $files[1][] = $entry;
				}
		    }
		}
		sort ($files[0]);
		sort ($files[1]);
		
		return ($files);
    }
    public function ExtensionsList ($extensions)
    {
	    if (count ($extensions) <= 0)
		{
		    $filter = "*";
		}
		else
		{
		    foreach ($extensions as $extension)
		    {
				$filter .= ((strlen ($filter) > 0) ? "|" : "");
				$filter .= "(.*\.$extension\Z)";
		    }
		}
	 
		return ($this->FilteredList ($filter));
	}

	public static function Concat ($left, $right)
	{
		$result = "";
		
		if (strlen ($left) > 0)
		{
			$result = $left;
			if (strlen ($right) > 0)
			{
				$result = EHTFolder::QuitEndingSlashes ($result);
				$result .= DIRECTORY_SEPARATOR;
				$result .= EHTFolder::QuitBeginningSlashes ($right);
				$result = EHTFolder::QuitEndingSlashes ($result);
			}
		}
		
		return ($result);
	}
	
	public static function QuitBeginningSlashes ($path)
	{
		$result = $path;
		while (substr ($result, 0, 1) == DIRECTORY_SEPARATOR)
		{
			$result = substr ($result, 1);
		}
		
		return ($result);
	}
	public static function QuitEndingSlashes ($path)
	{
		$result = $path;
		while (substr ($result, strlen ($result) - 1) == DIRECTORY_SEPARATOR)
		{
			$result = substr ($result, 0, strlen ($result) - 1);
		}
		
		return ($result);
	}

}

?>