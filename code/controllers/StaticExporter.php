<?php
/**
 * This class lets you export a static copy of your site either as an tar 
 * archive through the web browser or through the command line.
 *
 * The exporter will preserve the URL naming format of your pages by 
 * creating a number of subfolders folders each containing an index.html 
 * file.
 *
 * The bundled documentation in the 'docs' folder contains information
 * and usage instructions.
 * 
 * @see StaticPublisher
 *
 * @package staticpublisher
 */
class StaticExporter extends Controller {

	/**
	 * @config
	 *
	 * @var array $export_objects
	 */
	private static $export_objects = array();

	/**
	 * @config
	 *
	 * @var bool
	 */
	private static $disable_sitetree_export = false;

	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'index', 
		'export', 
		'StaticExportForm'
	);

	/**
	 * 
	 */
	public function __construct() {
		parent::__construct();

		if(class_exists('SiteTree')) {
			if(!$this->config()->get('disable_sitetree_export')) {
				$objs = $this->config()->export_objects;
				if (!is_array($objs)) {
					$objs = array($objs);
				}
				
				if(!in_array('SiteTree', $objs)) {
					$objs[] = "SiteTree";
				}
				
				$this->config()->export_objects = $objs;
			}
		}
	}

	/**
	 *
	 */
	public function init() {
		parent::init();
		
		$canAccess = (Director::isDev() || Director::is_cli());

		if(!Permission::check("ADMIN") && !$canAccess) {
			return Security::permissionFailure($this);
		}
	}
		
	/**
	 * @param string $action
	 *
	 * @return string 
	 */
	public function Link($action = null) {
		return "dev/staticexporter/$action";
	}

	/**
	 * @param string $action
	 *
	 * @return string
	 */
	public function AbsoluteLink($action = null) {
		return Director::absoluteURL($this->Link($action));
	}
	
	/**
	 * @return array
	 */
	public function index() {
		return array(
			'Title' => _t('StaticExporter.NAME','Static exporter'),
			'Form' => $this->StaticExportForm()->forTemplate()
		);
	}
	
	/**
	 * @return Form
	 */
	public function StaticExportForm() {
		$form = new Form($this, 'StaticExportForm', new FieldList(
			new TextField('baseurl', _t('StaticExporter.BASEURL','Base URL'))
		), new FieldList(
			new FormAction('export', _t('StaticExporter.EXPORT','Export'))
		));

		return $form;
	}


	public function export() {
		if(isset($_REQUEST['baseurl'])) {
			$base = $_REQUEST['baseurl'];

			if(substr($base,-1) != '/') $base .= '/';

			Config::inst()->update('Director', 'alternate_base_url', $base);
		}
		else {
			$base = Director::baseURL();
		}

		$folder = TEMP_FOLDER . '/static-export';
		$project = project();

		$exported = $this->doExport($base, $folder .'/'. $project, false);

		`cd $folder; tar -czhf $project-export.tar.gz $project`;

		$archiveContent = file_get_contents("$folder/$project-export.tar.gz");
		
		
		// return as download to the client
		$response = SS_HTTPRequest::send_file(
			$archiveContent, 
			"$project-export.tar.gz", 
			'application/x-tar-gz'
		);
		
		echo $response->output();
	}

	/**
	 * Exports the website with the given base url. Returns the path where the
	 * exported version of the website is located.
	 *
	 * @param string website base url
	 * @param string folder to export the site into
	 * @param bool symlink assets
	 * @param bool suppress output progress
	 *
	 * @return string path to export
	 */
	public function doExport($base, $folder, $symlink = true, $quiet = true) {
		ini_set('max_execution_time', 0);

		Config::inst()->update('Director', 'alternate_base_url', $base);

		if(is_dir($folder)) {
			Filesystem::removeFolder($folder);
		}

		Filesystem::makeFolder($folder);
		
		// symlink or copy /assets
		$f1 = ASSETS_PATH;
		$f2 = Director::baseFolder() . '/' . project();

		if($symlink) {
			`cd $folder; ln -s $f1; ln -s $f2`;
		}
		else {
			`cp -R $f1 $folder; cp -R $f2 $folder`;
		}

		// iterate through items we need to export
		$urls = $this->getExportUrls();

		if($urls) {
			$total = count($urls);
			$i = 1;

			foreach($urls as $url) {
				$subfolder   = "$folder/" . trim($url, '/');
				$contentfile = "$folder/" . trim($url, '/') . '/index.html';
				
				// Make the folder				
				if(!file_exists($subfolder)) {
					Filesystem::makeFolder($subfolder);
				}
				
				// Run the page
				Requirements::clear();
				DataObject::flush_and_destroy_cache();

				$response = Director::test($url);

				// Write to file
				if($fh = fopen($contentfile, 'w')) {
					if(!$quiet) {
						printf("-- (%s/%s) Outputting page (%s)%s", 
							$i, 
							$total, 
							$url, 
							PHP_EOL
						);
					}

					fwrite($fh, $response->getBody());
					fclose($fh);
				}

				$i++;
			}
		}

		return $folder;
	}

	/**
	 * Return an array of urls to publish
	 *
	 * @return array
	 */
	public function getExportUrls() {
		$classes = $this->config()->get('export_objects');
		$urls = array();

		foreach($classes as $obj) {
			if (!class_exists($obj)) {
				continue;
			}
			foreach ($obj::get() as $objInstance) {
				$link = $objInstance->Link();
				$urls[$link] = $link;
			}
		}

		$this->extend('alterExportUrls', $urls);

		// older api, keep around to ensure backwards compatibility
		$objs = new ArrayList();
		$this->extend('alterObjectsToExport', $objs);

		if($objs) {
			foreach($objs as $obj) {
				$link = $obj->Link;

				$urls[$link] = $link;
			}
		}

		return $urls;
	}
}
