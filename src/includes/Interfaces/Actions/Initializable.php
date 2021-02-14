<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\InitializationFailure;

defined( 'ABSPATH' ) || exit;

/**
 * Implementing classes need to define an initialization logic.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions
 */
interface Initializable {
	/**
	 * Execute the initialization logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function initialize(): ?InitializationFailure;
}
