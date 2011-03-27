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
	 * Check if the path is a directory
	 *
	 * @param string $dir Path to check, relative to {@see $directory}
	 * @return bool
	 */
	public function is_dir($dir);

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

	/**
	 * Search for files matching a pattern
	 *
	 * @param string $pattern Standard glob pattern, relative to {@see $directory}
	 * @return array Relative file names matching pattern
	 */
	public function glob($pattern);

	/**
	 * Get the contents of a file
	 *
	 * @param string $file File to read, relative to {@see $directory}
	 * @return string Data from file
	 */
	public function get_contents($file);

	/**
	 * Write to a file
	 *
	 * @param string $file File to write, relative to {@see $directory}
	 * @param string $contents New contents of the file
	 * @return string Data from file
	 */
	public function put_contents($file, $contents);

	/**
	 * Copy files/directory from local path
	 *
	 * Copies a file, or a directory recursively, from a local path
	 * (i.e. the templates directory) to the filesystem
	 * @param string $localpath Local path to copy from
	 * @param string $file New path
	 */
	public function copy($localpath, $file);

	/**
	 * Copy a file from one part of the filesystem to another
	 *
	 * @param string $from Path to copy from, relative to {@see $directory}
	 * @param string $to Path to copy to, relative to {@see $directory}
	 */
	public function localcopy($from, $to)
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
			// Directory doesn't exist yet
			$this->directory = $directory;
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
		// "/a/b\c"
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		// "/a/./b/c"
		$path = str_replace(DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
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
	 * Check if the path is a directory
	 *
	 * @param string $dir Path to check, relative to {@see $directory}
	 * @return bool
	 */
	public function is_dir($dir)
	{
		$path = $this->realpath($dir);
		return is_dir($path);
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
		if ($this->exists($directory))
		{
			throw new Exception(sprintf('Directory %s already exists', $directory));
		}
		$path = $this->path($directory);
		return mkdir($path, 0755, $parents);
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
			$files = $this->glob($directory . DIRECTORY_SEPARATOR . '*');
			foreach ($files as $file)
			{
				if ($this->is_dir($file))
				{
					$this->rmdir($file, true);
				}
				else
				{
					$this->rm($file);
				}
			}
		}
		return rmdir($path);
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
		return unlink($path);
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

	/**
	 * Search for files matching a pattern
	 *
	 * @param string $pattern Standard glob pattern, relative to {@see $directory}
	 * @return array Relative file names matching pattern
	 */
	public function glob($pattern)
	{
		$return = array();
		$files = glob($this->path($pattern));
		foreach ($files as $file)
		{
			$return[] = str_replace($this->directory, '', $file);
		}
		return $return;
	}

	/**
	 * Get the contents of a file
	 *
	 * @param string $file File to read, relative to {@see $directory}
	 * @return string Data from file
	 */
	public function get_contents($file)
	{
		$path = $this->realpath($file);
		if (is_dir($path))
		{
			throw new Exception(sprintf('%s is a directory, not a file', $file));
		}
		return file_get_contents($path);
	}

	/**
	 * Write to a file
	 *
	 * @param string $file File to write, relative to {@see $directory}
	 * @param string $contents New contents of the file
	 * @return string Data from file
	 */
	public function put_contents($file, $contents)
	{
		$path = $this->path($file);
		if (is_dir($path))
		{
			throw new Exception(sprintf('%s is a directory, not a file', $file));
		}
		return file_put_contents($path, $contents);
	}

	/**
	 * Copy files/directory from local path
	 *
	 * Copies a file, or a directory recursively, from a local path
	 * (i.e. the templates directory) to the filesystem
	 * @param string $localpath Local path to copy from
	 * @param string $file New path
	 */
	public function copy($localpath, $file)
	{
		$local = realpath($localpath);
		if ($local === false)
		{
			throw new Exception(sprintf('%s does not exist', $localpath));
		}
		if (is_dir($local))
		{
			$newdir = $file . DIRECTORY_SEPARATOR . basename($local);
			if (!$this->exists($newdir))
			{
				$this->mkdir($newdir, true);
			}
			$files = glob($local . DIRECTORY_SEPARATOR . '*');
			foreach ($files as $old)
			{
				if (is_dir($old))
				{
					$this->copy($old, $file . DIRECTORY_SEPARATOR . basename(dirname($old)));
				}
				else
				{
					$newfile = $newdir . DIRECTORY_SEPARATOR . basename($old);
					copy($old, $this->path($newfile));
				}
			}
		}
		else
		{
			copy($local, $this->path($file));
		}
	}

	/**
	 * Copy a file from one part of the filesystem to another
	 *
	 * @param string $from Path to copy from, relative to {@see $directory}
	 * @param string $to Path to copy to, relative to {@see $directory}
	 */
	public function localcopy($from, $to)
	{
		$this->copy($this->realpath($from), $to);
	}
}