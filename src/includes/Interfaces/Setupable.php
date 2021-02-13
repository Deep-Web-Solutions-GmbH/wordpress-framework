<?php

namespace DeepWebSolutions\Framework\Core\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Implementing classes need to define a setup logic.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces
 */
interface Setupable {
	/**
	 * Execute the setup logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function setup(): void;
}
