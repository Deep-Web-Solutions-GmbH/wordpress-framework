<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for signaling that some local setup needs to take place too.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Traits\Setupable
 */
trait SetupLocal {
	/**
	 * Using classes should define their local setup logic in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract protected function setup_local(): void;
}
