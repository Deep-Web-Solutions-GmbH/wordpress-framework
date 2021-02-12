<?php

namespace DeepWebSolutions\Framework\Core\Interfaces;

use DI\Container;

defined( 'ABSPATH' ) || exit;

/**
 * Implementing classes need to define a logic for retrieving a DI container.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces
 */
interface Containerable {
	/**
	 * Gets an instance of the PHP-DI container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_container(): Container;
}
