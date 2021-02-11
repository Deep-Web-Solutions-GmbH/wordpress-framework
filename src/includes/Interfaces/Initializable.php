<?php

namespace DeepWebSolutions\Framework\Core\Interfaces;

use DeepWebSolutions\Framework\Core\Exceptions\Initialization\InitializationFailure;

defined( 'ABSPATH' ) || exit;

/**
 * Implementing classes need to define an initialization logic.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces
 */
interface Initializable {
	/**
	 * Execute the setup logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function initialize(): ?InitializationFailure;
}
