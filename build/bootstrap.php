<?php
/**
 * PHPUnit bootstrap (phing)
 */

// make sure we have Phing
if ( class_exists( 'Phing' ) ) {
	// try to get current project
	$project = Phing::getCurrentProject();
	// did we get a project?
	if ( $project instanceof Project ) {
		// yes, define paths as configured by phing
		define( 'WP_SDL_BOOTSTRAP_ENV', $project->getProperty( 'build.env' ) );
		define( 'WP_SDL_BOOTSTRAP_SRC', $project->getProperty( 'source.dir' ) );
		// load tests bootstrap
		require_once $project->getProperty( 'source.tests' ) . '/bootstrap.php';
		// all done
		return;
	} else {
		// no phing project
		throw new RuntimeException( 'Phing project not found.' );
	}
} else {
	// Phing not loaded
	throw new RuntimeException( 'Phing not found.' );
}