<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
 * Copyright (c) 2011-2012 [Ryan McCue](http://ryanmccue.info)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * <http://www.opensource.org/licenses/mit-license.php>
 */

namespace Vanity\Event\Console
{
	use Vanity,
	    Symfony\Component\EventDispatcher\Event,
	    Symfony\Component\EventDispatcher\EventDispatcher,
	    Symfony\Component\Process\Process,
	    Symfony\Component\Console\Formatter\OutputFormatterStyle as ConsoleFormat,
	    Vanity\Event\Console,
	    Vanity\Event\Dispatcher;

	class FetchEvent extends Event
	{
		/**
		 * Stores the output object.
		 */
		public $output;

		/**
		 * Stores the Console Output Formatter object.
		 */
		public $formatter;

		/**
		 * The PHP subversion repositories to pull from.
		 */
		private $repositories = array(
			VANITY_PHPREF_DIR        => array('http://svn.php.net/repository/phpdoc/en/trunk/reference/'),
			VANITY_ENTITY_GLOBAL_DIR => array('http://svn.php.net/repository/phpdoc/doc-base/trunk/entities/'),
			VANITY_ENTITY_LANG_DIR   => array('http://svn.php.net/repository/phpdoc/en/trunk/', ' --depth files')
		);

		/**
		 * Constructs a new instance of <Vanity\Event\Console\FetchEvent>.
		 */
		public function __construct($output)
		{
			$this->output = $output;

			$this->h1_formatter = new ConsoleFormat('yellow');
			$this->h1_formatter->setOption('bold');
			$this->h2_formatter = new ConsoleFormat('green');
		}

		/**
		 * Perform a Subversion checkout of the PHP documentation repositories.
		 */
		public function checkout()
		{
			$output = $this->output;

			$output->writeln('');
			$output->writeln($this->h1_formatter->apply('PHP DOCUMENTATION CHECKOUT'));
			$output->writeln('Downloading the PHP documentation for the first time. This may take a few minutes.');
			$output->writeln('');

			foreach ($this->repositories as $write_to => $repository)
			{
				$url = $repository[0];
				$append = isset($repository[1]) ? $repository[1] : '';
				$output->writeln($this->h2_formatter->apply($url));

				$process = new Process("svn co ${url} ${write_to}${append}");
				$process->run(function ($type, $buffer) use (&$output)
				{
					if ($type === 'err') { $output->writeln('ERR > ' . $buffer); }
					else { $output->writeln(TAB . trim($buffer)); }
				});

				unset($process);
				$output->writeln('');
			}
		}

		/**
		 * Perform a Subversion update of the PHP documentation repositories.
		 */
		public function update()
		{
			$output = $this->output;

			$output->writeln('');
			$output->writeln($this->h1_formatter->apply('PHP DOCUMENTATION UPDATE'));
			$output->writeln('Updating the PHP documentation.');
			$output->writeln('');

			foreach ($this->repositories as $write_to => $repository)
			{
				$url = $repository[0];
				$append = isset($repository[1]) ? $repository[1] : '';
				$output->writeln($this->h2_formatter->apply($url));

				$process = new Process("svn up ${write_to}${append}");
				$process->run(function ($type, $buffer) use (&$output)
				{
					if ($type === 'err') { $output->writeln('ERR > ' . $buffer); }
					else { $output->writeln(TAB . trim($buffer)); }
				});

				unset($process);
				$output->writeln('');
			}
		}
	}
}
