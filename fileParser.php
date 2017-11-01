<?php
/*
  Parser for record-structured files
*/



abstract class _fileBackend
{
	private $file;


	protected function &_fileObj()
	{
		return $this->file;
	}


	public function __construct($path)
	{
		$this->file = fopen($path, "r+");
	}
	public function __destruct()
	{
		fclose($this->file);
	}

	public function end()
	{
		return feof($this->file);
	}
	public function reset()
	{
		rewind($this->file);
	}


	abstract public function record_get();
	abstract public function record_put($rec);
}

final class CSVFile extends _fileBackend
{
	public function record_get()
	{
		if ($this->end()) return null;
		return fgetcsv($this->_fileObj());
	}

	public function record_put($rec)
	{
		fputcsv($this->_fileObj(), $rec);
	}
}



final class parser
{
	private $file, $struct;


	public function __construct(array $recStruct)
	{
		$this->struct = $recStruct;
	}

	public function openCSV($path)
	{
		$this->file = new CSVFile($path);
	}


	public function end()
	{
		return $file->end();
	}

	private function _join($struct, $rec, bool $left = true)
	{
		$r = array();
		foreach ($struct as $k => $v)
		{
			$_valid = false;
			$value = null;
			if (array_key_exists($k, $rec))
			{
				$value = $rec[$k];
				unset($rec[$k]);
				$_valid = true;
				goto doneWithValue;
			}
			$top = array_shift($rec);
			if ($top != null) $_valid = true;
			switch ($v)
			{
			case "int":
				$value = (int)$top;
				break;
			case "string":
				$value = (string)$top;
				break;
			case "bool":
				$value = (bool)$top;
				break;
			}

			doneWithValue:
			if ($left || $_valid) $r[$k] = $value;
		}
		return $r;
	}

	public function get()
	{
		$fetch = $this->file->record_get();
		if (!is_array($fetch)) return null;
		return $this->_join($this->struct, $fetch);
	}

	public function put($r)
	{
		if (!is_array($r)) return;
		$this->file->record_put($this->_join($this->struct, $r));
	}
}
?>