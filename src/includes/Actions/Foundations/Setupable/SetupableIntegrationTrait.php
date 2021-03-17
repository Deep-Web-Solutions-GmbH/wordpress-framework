<?php

namespace DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable;

\defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their setup integration logic called
 * after a successful setup.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Foundations\Setupable
 */
trait SetupableIntegrationTrait {
	/* empty on purpose */
}
