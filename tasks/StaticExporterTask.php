<?php
/**
 * @package staticpublisher
 */
class StaticExporterTask extends BuildTask {

	public function run($request) {
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

		if(!$quiet) printf("Completed. Website exported to %s. %s", $path, PHP_EOL);
	}
}