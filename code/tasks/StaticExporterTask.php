<?php

/**
 * @package staticpublisher
 */
class StaticExporterTask extends BuildTask {

	public function run($request) {
		$now = microtime(true);
		$export = new StaticExporter();

		$url = $request->getVar('baseurl');
		$sym = $request->getVar('symlink');
		$quiet = $request->getVar('quiet');
		$folder = $request->getVar('path');

		if(!$folder) $folder = TEMP_FOLDER . '/static-export';

		$url = ($url) ? $url : Director::baseURL(); 
		$symlink = ($sym != "false");
		$quiet = ($quiet) ? $quiet : false;

		if(!$quiet) printf("Exporting website with %s base URL... %s", $url, PHP_EOL);
		$path = $export->doExport($url, $folder, $symlink, $quiet);

		if(!$quiet) {
			printf("\nWebsite exported to %s\nTotal time %s\nMemory used %s. %s", 
				$path,
				number_format(microtime(true) - $now, 2) . 's', 
				number_format(memory_get_peak_usage() / 1024 / 1024, 2) .'mb',
				PHP_EOL
			);
		}
	}
}