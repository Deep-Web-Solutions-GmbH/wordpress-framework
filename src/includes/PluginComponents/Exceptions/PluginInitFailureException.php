<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents\Exceptions;

\defined( 'ABSPATH' ) || exit;

/**
 * An exception thrown when the plugin itself fails to initialize.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents\Exceptions
 */
class PluginInitFailureException extends FunctionalityInitFailureException {
	/* empty on purpose */
}
