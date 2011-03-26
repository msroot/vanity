<?php

interface Vanity_Filesystem
{
	/**
	 * @param string $directory Base directory, All parameters are relative to this directory
	 */
	public function __construct($directory);

	/**
	 * Convert a relative path to an absolute path
	 *
	 * @see $directory
	 * @param string $file Path to make absolute
	 * @return string Real path
	 */
	public function realpath($file);

	/**
	 * Convert a relative path to an absolute path, hypothetically
	 *
	 * Used before a file is actually created
	 * @see $directory
	 * @param string $file Path to make absolute
	 * @return string Real path
	 */
	public function path($file);

	/**
	 * Check whether a file exists
	 *
	 * @param string $file File to check, relative to {@see $directory}
	 * @return bool
	 */
	public function exists($file);

	/**
	 * Create a directory
	 *
	 * @param string $directory Directory to create, relative to {@see $directory}
	 * @param bool $parents Create parent directories if needed
	 * @return bool Success status
	 */
	public function mkdir($directory, $parents = false);

	/**
	 * Remove a directory
	 *
	 * @param string $directory Directory to remove, relative to {@see $directory}
	 * @param bool $recursive Remove all files in the directory recursively
	 * @return bool Success status
	 */
	public function rmdir($directory, $recursive = false);

	/**
	 * Remove a file
	 *
	 * @param string $file File to remove, relative to {@see $directory}
	 * @return bool Success status
	 */
	public function rm($file);

	/**
	 * Move a file
	 *
	 * @param string $from File/directory to move from, relative to {@see $directory}
	 * @param string $to File/directory to move to, relative to {@see $directory}
	 * @return bool Success status
	 */
	public function mv($from, $to);
}

class Vanity_Filesystem_Direct implements Vanity_Filesystem
{
	protected $directory;

	/**
	 * @param string $directory Base directory, All parameters are relative to this directory
	 */
	public function __construct($directory, $create = false, $parents = false)
	{
		$this->directory = realpath($directory);
		if ($this->directory === false)
		{
			if ($create === true)
			{
				mkdir($directory, 0755, $parents);
				$this->directory = realpath($directory);
			}
			else
			{
				throw new Exception(sprintf('Directory %s does not exist', $directory));
			}
		}
		$this->directory .= DIRECTORY_SEPARATOR;
	}

	/**
	 * Convert a relative path to an absolute path
	 *
	 * @see $directory
	 * @param string $file Path to make absolute
	 * @return string Real path
	 */
	public function realpath($file)
	{
		$path = realpath($this->directory . $file);
		if ($path === false)
		{
			throw new Exception(sprintf('File %s does not exist', $this->directory . $file));
		}
		return $path;
	}

	public function path($file)
	{
		$path = $this->directory . $file;
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		return $path;
	}

	/**
	 * Check whether a file exists
	 *
	 * @param string $file File to check, relative to {@see $directory}
	 * @return bool
	 */
	public function exists($file)
	{
		echo "Checking if $file exists";
		try
		{
			$this->realpath($file);
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Create a directory
	 *
	 * @param string $directory Directory to create, relative to {@see $directory}
	 * @param bool $parents Create parent directories if needed
	 * @return bool Success status
	 */
	public function mkdir($directory, $parents = false)
	{
		$path = $this->realpath($directory);
		//return mkdir($path, 0755, $parents);
	}

	/**
	 * Remove a directory
	 *
	 * @param string $directory Directory to remove, relative to {@see $directory}
	 * @param bool $recursive Remove all files in the directory recursively
	 * @return bool Success status
	 */
	public function rmdir($directory, $recursive = false)
	{
		$path = $this->realpath($directory);
		if (!is_dir($path))
		{
			throw new Exception(sprintf('%s is a file, not a directory', $file));
		}
		if ($recursive)
		{
			$files = glob($path . DIRECTORY_SEPARATOR . '*');
			foreach ($files as $file)
			{
				$file = str_replace($this->directory, '', $file);
				$fpath = $this->realpath($file);
				if (is_dir($fpath))
				{
					$this->rmdir($file, true);
				}
				else
				{
					$this->rm($file);
				}
			}
		}
		echo $path . DIRECTORY_SEPARATOR . "\n";
		//return rmdir($path);
	}

	/**
	 * Remove a file
	 *
	 * @param string $file File to remove, relative to {@see $directory}
	 * @return bool Success status
	 */
	public function rm($file) {
		$path = $this->realpath($file);
		if (is_dir($path))
		{
			throw new Exception(sprintf('%s is a directory, not a file', $file));
		}
		echo $path . "\n";
		//return unlink($path);
	}

	/**
	 * Move a file
	 *
	 * @param string $from File/directory to move from, relative to {@see $directory}
	 * @param string $to File/directory to move to, relative to {@see $directory}
	 * @return bool Success status
	 */
	public function mv($from, $to) {
		$from = $this->realpath($from);
		$to = $this->realpath($to);
	}
}