<?php

namespace DeepWebSolutions\Framework\Core\States\Disableable;

use DeepWebSolutions\Framework\Foundations\States\Disableable\DisableableExtensionTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Abstract extension trait for dependent disablement of integration classes.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\States\Disableable
 */
trait DisabledIntegrationTrait {
	// region TRAITS

	use DisableableExtensionTrait;

	// endregion

	// region METHODS

	/**
	 * Using class should define the logic for determining whether the integration is applicable or not in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool    True if NOT applicable, for otherwise.
	 */
	abstract public function is_disabled_integration(): bool;

	// endregion
}
