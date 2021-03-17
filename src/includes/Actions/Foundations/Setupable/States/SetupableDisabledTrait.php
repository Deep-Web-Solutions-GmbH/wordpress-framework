<?php

namespace DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\States;

\defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that classes should use to denote that their setup routine should be called even if the class is disabled.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Foundations\Setupable\States
 */
trait SetupableDisabledTrait {
	/* empty on purpose */
}
