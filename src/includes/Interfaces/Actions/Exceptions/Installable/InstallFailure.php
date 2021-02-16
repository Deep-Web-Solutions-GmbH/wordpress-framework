<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\Installable;

use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * An exception thrown when an installable object fails to install.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Exceptions
 */
class InstallFailure extends Exception {
	/* empty on purpose */
}
