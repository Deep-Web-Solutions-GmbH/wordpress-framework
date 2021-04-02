<?php

namespace DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\States;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\SetupableTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that classes should use to denote that their setup routine should be called even if the class is inactive.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Foundations\Setupable\States
 */
trait SetupableInactiveTrait {
	// region TRAITS

	use SetupableTrait;

	// endregion
}
