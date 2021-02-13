<?php

namespace DeepWebSolutions\Framework\Core\Traits\IsActive;

use DeepWebSolutions\Framework\Utilities\Interfaces\Traits\Activeable\Activeable;

/**
 * Functionality trait for dependent setup of integration functionalities.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\IsActive
 */
trait Integration {
	use Activeable {
		is_active as is_active_integration;
	}
}
