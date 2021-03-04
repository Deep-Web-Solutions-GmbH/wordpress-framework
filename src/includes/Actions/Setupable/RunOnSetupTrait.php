<?php

namespace DeepWebSolutions\Framework\Core\Actions\Setupable;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Setupable\SetupableIntegrationTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Runnable\RunFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\RunnableInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for piping the 'setup' method at the end of the initialization routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions\Setupable
 */
trait RunOnSetupTrait {
	use SetupableIntegrationTrait;

	/**
	 * After successful setup, call the 'run' method of the using class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  RunFailureException|null
	 */
	public function integrate_run_on_setup(): ?RunFailureException {
		return ( $this instanceof RunnableInterface )
			? $this->run()
			: null;
	}
}
