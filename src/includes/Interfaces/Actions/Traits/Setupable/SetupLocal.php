<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for signaling that some local setup needs to take place too.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Setupable
 */
trait SetupLocal {
	/**
	 * Using classes should define their local setup logic in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailure|null
	 */
	abstract protected function setup_local(): ?SetupFailure;
}
