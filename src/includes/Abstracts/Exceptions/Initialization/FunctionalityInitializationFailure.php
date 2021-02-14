<?php

namespace DeepWebSolutions\Framework\Core\Abstracts\Exceptions\Initialization;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\InitializationFailure;

defined( 'ABSPATH' ) || exit;

/**
 * An exception thrown when a functionality fails to initialize.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Abstracts\Exceptions\Initialization
 */
class FunctionalityInitializationFailure extends InitializationFailure {
	/* empty on purpose */
}
