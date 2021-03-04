<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents\Exceptions;

use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;

defined( 'ABSPATH' ) || exit;

/**
 * An exception thrown when a functionality fails to initialize.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents\Exceptions
 */
class FunctionalityInitFailureException extends InitializationFailureException {
	/* empty on purpose */
}
