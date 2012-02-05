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

namespace Vanity\Event
{
	use Vanity,
	    Symfony\Component\EventDispatcher\Event,
	    Symfony\Component\EventDispatcher\EventDispatcher,
	    Symfony\Component\Process\Process,
	    Vanity\Event\Console,
	    Vanity\Event\Dispatcher;

	/**
	 * Internal event handlers to register.
	 */
	class Register
	{
		/**
		 * Register the event handlers for `vanity fetch`.
		 */
		public static function fetch()
		{
			// Vanity\Event\Console::FETCH_CHECKOUT
			Dispatcher::get_dispatcher()->addListener(Console::FETCH_CHECKOUT, function(Event $event)
			{
				return $event->checkout();
			});

			// Vanity\Event\Console::FETCH_UPDATE
			Dispatcher::get_dispatcher()->addListener(Console::FETCH_UPDATE, function(Event $event)
			{
				return $event->update();
			});
		}
	}
}
