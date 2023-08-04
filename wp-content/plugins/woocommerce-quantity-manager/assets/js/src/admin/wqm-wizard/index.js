import { addAction } from '@wordpress/hooks';

/**
 * Fires on wizard restart.
 *
 * @param {*} wizard
 */
function handleWizardRestart(wizard) {
	wizard.setState({ wizard_complete: true });
	wizard.setStepsCompleted(true);
}
addAction('barn2_wizard_on_restart', 'wqm-wizard', handleWizardRestart);