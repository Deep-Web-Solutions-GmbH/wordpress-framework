<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable;

use DeepWebSolutions\Framework\Core\Exceptions\Initialization\InitializationFailure;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for signaling that some local initialization needs to take place too.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Traits\Initializable
 */
trait InitializeLocal {
	/**
	 * Using classes should define their local initialization logic in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailure|null
	 */
	abstract protected function initialize_local(): ?InitializationFailure;
}
