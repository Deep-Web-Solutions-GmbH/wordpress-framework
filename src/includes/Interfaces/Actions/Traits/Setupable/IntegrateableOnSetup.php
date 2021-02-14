<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable;

use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their setup integration logic called
 * after a successful setup.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Setupable
 */
trait IntegrateableOnSetup {
	/**
	 * Executed after successful setup of classes that use an inheriting trait.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Exception|null
	 */
	abstract public function integrate(): ?Exception;
}
